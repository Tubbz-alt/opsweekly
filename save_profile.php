<?php

include_once 'phplib/base.php';
global $ROOT_URL;

if (!db::connect()) {
    echo "Database connection failed, cannot continue. ";
} else {

    $query = "REPLACE INTO user_profile (ldap_username, full_name, timezone,
      sleeptracking_provider, sleeptracking_settings, team)
      VALUES (:username, :fullname, :tz, :sleep_provider, :sleep_settings, :team)";
    $stmt = db::prepare($query);

    $vars = array(
        ':username' => getUsername(),
        ':fullname' => $_POST['full_name'],
        ':tz' => $_POST['timezone'],
        ':sleep_provider' => $_POST['sleeptracking_provider'],
        ':sleep_settings' => json_encode($_POST['sleeptracking']),
        ':team' => $_POST['team']
    );
    try {
        db::execute($stmt, $vars);
    } catch (PDOException $e) {
        echo "<pre>Database update failed, error: " . print_r(db::error(), true) . "</pre>";
        exit;
    }
    Header("Location: {$ROOT_URL}/edit_profile.php?succ=hellyeah");
}
