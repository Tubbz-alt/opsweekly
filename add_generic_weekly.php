<?php

include_once 'phplib/base.php';
global $ROOT_URL;


if (!db::connect()) {
    echo "Database connection failed, cannot continue. ";
} else {
    $username = getUsername();
    $range_start = $_POST['range_start'];
    $range_end = $_POST['range_end'];
    $report = db::escape($_POST['weeklyupdate']);

    $query = "INSERT INTO generic_weekly (report_id, range_start, range_end, timestamp, user, state, report)
      VALUES (:reportId, :rs, :re, :ts, :username, :state, :report)";
    $vars = array(
        ':reportId' => generateWeeklyReportID($username, $range_start, $range_end),
        ':rs' => $range_start,
        ':re' => $range_end,
        ':ts' => time(),
        ':username' => $username,
        ':state' => "final",
        ':report' => $report
    );

    $stmt = db::prepare($query);
    db::execute($stmt, $vars);

    if (db::error()['code'] != 0) {
        echo "<pre>Database update failed, error: " . print_r(db::error(), true)."</pre>";
    } else {
        if (isset($_POST['do_email'])) {
            # The user clicked the email button so also email a copy of the report
            if (sendEmailReport($username, $report, $range_start, $range_end)) {
                Header("Location: {$ROOT_URL}/add.php?weekly_succ_email=hellyeah");
            }
        } else {
            Header("Location: {$ROOT_URL}/add.php?weekly_succ=hellyeah");
        }
    }
}

