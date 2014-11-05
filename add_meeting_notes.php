<?php

include_once 'phplib/base.php';

if (!db::connect()) {
    echo "Database connection failed, cannot continue. ";
} else {
    $range_start = db::escape($_POST['range_start']);
    $range_end = db::escape($_POST['range_end']);
    $report_id = generateMeetingNotesID($range_start, $range_end);

    $query = "INSERT INTO meeting_notes (report_id, range_start, range_end, timestamp, user, notes)
      VALUES :reportId, :rs, :re, :ts, :username, :notes)";

    $vals = array(
        ':reportId' => generateMeetingNotesID($range_start, $range_end),
        ':rs' => $range_start,
        ':re' => $range_end,
        ':ts' => time(),
        ':username' => getUsername(),
        ':notes' => $_POST['weeklynotes']
    );
    $stmt = db::prepare($query);
    db::execute($stmt, $vals);
    if (db::error()['code'] != 0) {
        echo "<pre>Database update failed, error: " . print_r(db::error(), true)."</pre>";
    } else {
        Header("Location: {$ROOT_URL}/index.php?meeting_done=hellyeah");
    }
}

