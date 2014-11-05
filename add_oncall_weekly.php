<?php

include_once 'phplib/base.php';

if (!db::connect()) {
    echo "Database connection failed, cannot continue. ";
} else {
    $username = getUsername();
    $range_start = $_POST['oncall']['range_start'];
    $range_end = $_POST['oncall']['range_end'];

    logline("Started adding a new oncall update for {$username} with range_start: {$range_start} and range_end: {$range_end}...");

    if (count($_POST['oncall']['notifications']) > 0) {
        // See if this user is enrolled in sleep tracking
        $profile_data = checkForUserProfile($username);
        if ($profile_data && $profile_data['sleeptracking_provider'] != "none" && $profile_data['sleeptracking_provider'] != "") {
            $sleep_provider = $profile_data['sleeptracking_provider'];
            logline("Sleeptracking enabled: {$sleep_provider}");
            $sleep = true;

            // Get the user settings into an array by decoding the JSON we put in the database for them
            $sleeptracking_settings = json_decode($profile_data['sleeptracking_settings'], 1);
            // Get the provider settings from the config array
            $sleepprovider_settings = $sleep_providers[$sleep_provider];
            // Load the sleeptracking provider's PHP code
            include_once($sleepprovider_settings['lib']);

        } else {
            logline("No need to do sleep tracking because {$username} has no provider chosen");
        }

        $query = "INSERT INTO oncall_weekly (alert_id, range_start, range_end, timestamp, hostname, service, state, contact, output, tag, sleep_state, mtts, sleep_level, sleep_confidence, notes) VALUES
                (:alertId, :rs, :re, :ts, :hostname, :service, :state, :username, :output, :tag, :sleep_state, :mtts, :sleep_level, :confidence,:notes)";

        $stmt = db::prepare($query);

        foreach($_POST['oncall']['notifications'] as $id => $n) {
            $sleep_state = -1;
            $mtts = -1;
            $sleep_level = -1;
            $confidence = -1;
            $timestamp = $n['time'];
            $hostname = $n['hostname'];
            $output = $n['output'];
            $service = $n['service'];
            $state = $n['state'];
            $tag = $n['tag'];
            $notes = htmlentities($n['notes'], ENT_QUOTES);

            if ($sleep) {
                // Run the sleep tracking provider for this alert
                $sleep_info = getSleepDetailAtTimestamp($timestamp, $sleeptracking_settings[$sleep_provider], $sleepprovider_settings);
                if ($sleep_info) {
                    $sleep_state = $sleep_info['sleep_state'];
                    $mtts = $sleep_info['mtts'];
                    $sleep_level = $sleep_info['sleep_level'];
                    $confidence = $sleep_info['confidence'];
                }
            }

            $vals = array(
                ':alertId' => generateOnCallAlertID($timestamp, $hostname, $service),
                ':rs' => $range_start,
                ':re' => $range_end,
                ':ts' => $timestamp,
                ':hostname' => $hostname,
                ':service' => $service,
                ':state' => $state,
                ':username' => getUsername(),
                ':output' => $output,
                ':tag' => $tag,
                ':sleep_state' => $sleep_state,
                ':mtts' => $mtts,
                ':sleep_level' => $sleep_level,
                ':confidence' => $confidence,
                ':notes' => $notes
            );

            logline("Processing on call line with data: ".print_r($vals, true));
            try {
                db::execute($stmt, $vals);
            } catch (PDOException $e) {
                echo "<pre>Database update failed, error: " . print_r(db::error(), true)."</pre>";
                logline("Database update failed, error: " . $e->getMessage());
            }
        }
        logline("Everything worked great, redirecting the user with success");
        Header('Location: add.php?oncall_succ=hellyeah');
    } else {
        logline("We didn't find any notifications to process, redirect user back to add page");
        Header('Location: add.php');
    }
}

