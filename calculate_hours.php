<?php

session_start();

include "db/connect.php";


/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {

    exit("Login required");

}

$user_id = $_SESSION['user_id'];


/* =========================================
   CURRENT MONTH
========================================= */

$month = date("m");
$year  = date("Y");


/* =========================================
   GET ATTENDANCE
========================================= */

$sql = $conn->prepare("

    SELECT action, date_time

    FROM attendance

    WHERE user_id = ?

    AND MONTH(date_time) = ?

    AND YEAR(date_time) = ?

    ORDER BY date_time ASC

");

$sql->bind_param(
    "iii",
    $user_id,
    $month,
    $year
);

$sql->execute();

$result = $sql->get_result();


/* =========================================
   VARIABLES
========================================= */

$work_seconds = 0;

$start_time = null;

$break_start = null;

$break_seconds = 0;


/* =========================================
   PROCESS RECORDS
========================================= */

while ($row = $result->fetch_assoc()) {

    $action = $row['action'];

    $time =
        strtotime($row['date_time']);


    /* ================================
       出勤
    ================================= */

    if ($action === "出勤") {

        $start_time = $time;

        $break_start = null;

        $break_seconds = 0;

    }


    /* ================================
       休憩入り
    ================================= */

    elseif ($action === "休憩入り") {

        if (
            $start_time !== null &&
            $break_start === null
        ) {

            $break_start = $time;

        }

    }


    /* ================================
       休憩戻り
    ================================= */

    elseif ($action === "休憩戻り") {

        if (
            $start_time !== null &&
            $break_start !== null
        ) {

            $break_seconds +=
                $time - $break_start;

            $break_start = null;

        }

    }


    /* ================================
       退勤
    ================================= */

    elseif ($action === "退勤") {

        if ($start_time !== null) {


            /*
             * If break is still open
             */

            if ($break_start !== null) {

                $break_seconds +=
                    $time - $break_start;

                $break_start = null;

            }


            /*
             * Calculate session
             */

            $session_seconds =
                $time - $start_time;


            /*
             * Remove break
             */

            $session_work =
                $session_seconds -
                $break_seconds;


            if ($session_work > 0) {

                $work_seconds +=
                    $session_work;

            }


            /*
             * Reset
             */

            $start_time = null;

            $break_seconds = 0;

        }

    }

}


/* =========================================
   PREVENT NEGATIVE
========================================= */

if ($work_seconds < 0) {

    $work_seconds = 0;

}


/* =========================================
   CONVERT
========================================= */

$hours =
    floor($work_seconds / 3600);


$minutes =
    floor(
        ($work_seconds % 3600) / 60
    );


echo
    $hours .
    "時間 " .
    $minutes .
    "分";

?>