<?php

session_start();

include "db/connect.php";


/* =========================================
   CHECK PENDING ATTENDANCE
========================================= */

if (!isset($_SESSION['pending_attendance'])) {

    exit("No attendance data found.");

}


$data = $_SESSION['pending_attendance'];


$user_id   = $data['user_id'];
$symbol_no = $data['symbol_no'];
$name      = $data['name'];
$action    = $data['action'];


/* =========================================
   SAVE ATTENDANCE
========================================= */

$attendance_sql = $conn->prepare("
    INSERT INTO attendance
    (
        user_id,
        symbol_no,
        name,
        action,
        date_time
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        NOW()
    )
");


$attendance_sql->bind_param(
    "isss",
    $user_id,
    $symbol_no,
    $name,
    $action
);


if (!$attendance_sql->execute()) {

    exit(
        "Failed to save attendance: "
        . $attendance_sql->error
    );

}


/* =========================================
   SET LOGIN USER
========================================= */

$_SESSION['user_id'] = $user_id;


/* =========================================
   REMOVE PENDING DATA
========================================= */

unset(
    $_SESSION['pending_attendance']
);


/* =========================================
   GO TO HISTORY
========================================= */

header(
    "Location: history.php"
);

exit;

?>