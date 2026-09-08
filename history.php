<?php

session_start();

include "db/connect.php";


/* =========================================
   LOGIN CHECK
========================================= */

if (
    !isset($_SESSION['user_id'])
) {

    exit(
        "Login required"
    );

}


$user_id =
    (int)$_SESSION['user_id'];


/* =========================================
   GET CURRENT USER
========================================= */

$user_sql = $conn->prepare("

    SELECT

        symbol_no,

        name,

        photo

    FROM users

    WHERE id = ?

    LIMIT 1

");


$user_sql->bind_param(
    "i",
    $user_id
);


$user_sql->execute();


$user_result =
    $user_sql->get_result();


$user =
    $user_result->fetch_assoc();


if (!$user) {

    exit(
        "User not found"
    );

}


$symbol_no =
    $user['symbol_no'];


$name =
    $user['name'];


$photo =
    trim(
        $user['photo'] ?? ''
    );


/* =========================================
   SELECTED MONTH
========================================= */

$selected_month =
    $_GET['month']
    ?? date("Y-m");


if (
    !preg_match(
        '/^\d{4}-\d{2}$/',
        $selected_month
    )
) {

    $selected_month =
        date("Y-m");

}


$selected_date =
    DateTime::createFromFormat(
        'Y-m',
        $selected_month
    );


if (!$selected_date) {

    $selected_month =
        date("Y-m");

    $selected_date =
        new DateTime();

}


$year =
    (int)$selected_date->format("Y");


$month =
    (int)$selected_date->format("m");


/* =========================================
   GET ATTENDANCE FOR CALCULATION
========================================= */

$calc_sql = $conn->prepare("

    SELECT

        action,

        date_time

    FROM attendance

    WHERE user_id = ?

    AND MONTH(date_time) = ?

    AND YEAR(date_time) = ?

    ORDER BY date_time ASC

");


$calc_sql->bind_param(

    "iii",

    $user_id,

    $month,

    $year

);


$calc_sql->execute();


$calc_result =
    $calc_sql->get_result();


/* =========================================
   WORKING HOURS
========================================= */

$work_seconds = 0;

$start_time = null;

$break_start = null;

$break_seconds = 0;


while (
    $row =
    $calc_result->fetch_assoc()
) {


    $action =
        $row['action'];


    $time =
        strtotime(
            $row['date_time']
        );


    /* ================================
       出勤
    ================================= */

    if (
        $action === "出勤"
    ) {


        $start_time =
            $time;


        $break_start =
            null;


        $break_seconds =
            0;

    }


    /* ================================
       休憩入り
    ================================= */

    elseif (
        $action === "休憩入り"
    ) {


        if (

            $start_time !== null

            &&

            $break_start === null

        ) {

            $break_start =
                $time;

        }

    }


    /* ================================
       休憩戻り
    ================================= */

    elseif (
        $action === "休憩戻り"
    ) {


        if (

            $start_time !== null

            &&

            $break_start !== null

        ) {


            $break_seconds +=

                $time
                -
                $break_start;


            $break_start =
                null;

        }

    }


    /* ================================
       退勤
    ================================= */

    elseif (
        $action === "退勤"
    ) {


        if (
            $start_time !== null
        ) {


            /*
             * If break is still open,
             * count it until退勤.
             */

            if (
                $break_start !== null
            ) {


                $break_seconds +=

                    $time
                    -
                    $break_start;


                $break_start =
                    null;

            }


            /*
             * Total session time
             */

            $session_seconds =

                $time
                -
                $start_time;


            /*
             * Remove break time
             */

            $session_work =

                $session_seconds
                -
                $break_seconds;


            /*
             * Prevent negative
             */

            if (
                $session_work > 0
            ) {

                $work_seconds +=
                    $session_work;

            }


            /*
             * Reset
             */

            $start_time =
                null;

            $break_seconds =
                0;

        }

    }

}


/* =========================================
   CONVERT WORK TIME
========================================= */

if (
    $work_seconds < 0
) {

    $work_seconds = 0;

}


$hours =
    floor(
        $work_seconds / 3600
    );


$minutes =
    floor(
        ($work_seconds % 3600) / 60
    );


$total_work_time =

    $hours .
    "時間 " .
    $minutes .
    "分";


/* =========================================
   GET HISTORY
========================================= */

/*
 * IMPORTANT:
 *
 * photo comes from attendance table.
 *
 * Therefore each attendance record
 * displays the photo saved when that
 * attendance was created.
 */

$history_sql = $conn->prepare("

    SELECT

        symbol_no,

        name,

        photo,

        action,

        date_time

    FROM attendance

    WHERE user_id = ?

    AND YEAR(date_time) = ?

    AND MONTH(date_time) = ?

    ORDER BY date_time ASC

");


$history_sql->bind_param(

    "iii",

    $user_id,

    $year,

    $month

);


$history_sql->execute();


$history =
    $history_sql->get_result();

?>


<!DOCTYPE html>

<html lang="ja">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Attendance History</title>


<style>

/* =========================================
   GLOBAL
========================================= */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

}


body {

    min-height: 100vh;

    background:

        radial-gradient(

            circle at top left,

            #17243a,

            #080b12 45%,

            #030406

        );

    color: white;

    font-family:

        "Segoe UI",

        Arial,

        sans-serif;

    padding: 25px;

}


/* =========================================
   MAIN
========================================= */

.container {

    width: 100%;

    max-width: 1100px;

    margin: auto;

}


/* =========================================
   HEADER
========================================= */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 20px 25px;

    margin-bottom: 20px;

    border-radius: 18px;

    background:

        rgba(15, 20, 32, 0.95);

    border:

        1px solid

        rgba(0, 243, 255, 0.25);

    box-shadow:

        0 15px 40px

        rgba(0,0,0,.35);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 15px;

}


.logo {

    width: 55px;

    height: 55px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:

        linear-gradient(

            135deg,

            #00f3ff,

            #0066ff

        );

    color: #001014;

    font-size: 25px;

    font-weight: bold;

    box-shadow:

        0 0 20px

        rgba(0,243,255,.35);

}


.title h1 {

    font-size: 25px;

    letter-spacing: 2px;

}


.title p {

    color: #8491a5;

    font-size: 13px;

    margin-top: 5px;

}


.back-btn {

    text-decoration: none;

    color: white;

    border:

        1px solid

        rgba(0,243,255,.4);

    padding:

        10px 18px;

    border-radius: 10px;

    transition: .25s;

}


.back-btn:hover {

    background: #00f3ff;

    color: #001014;

}


/* =========================================
   USER CARD
========================================= */

.user-card {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 20px;

    margin-bottom: 20px;

    border-radius: 18px;

    background:

        linear-gradient(

            135deg,

            rgba(0,243,255,.07),

            rgba(255,0,85,.04)

        );

    border:

        1px solid

        rgba(0,243,255,.18);

}


.user-info {

    display: flex;

    align-items: center;

    gap: 15px;

}


/* =========================================
   PROFILE IMAGE
========================================= */

.profile {

    width: 65px;

    height: 65px;

    border-radius: 50%;

    object-fit: cover;

    border:

        2px solid #00f3ff;

    box-shadow:

        0 0 15px

        rgba(0,243,255,.4);

}


.profile-placeholder {

    width: 65px;

    height: 65px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #111827;

    color: #00f3ff;

    border:

        2px solid #00f3ff;

    font-size: 24px;

    font-weight: bold;

}


/* =========================================
   USER
========================================= */

.user-name {

    font-size: 20px;

    font-weight: bold;

}


.symbol {

    margin-top: 5px;

    color: #7f8da3;

    font-size: 13px;

}


/* =========================================
   WORK TIME
========================================= */

.work-time {

    text-align: right;

}


.work-label {

    color: #7f8da3;

    font-size: 12px;

    margin-bottom: 5px;

}


.work-value {

    color: #39ff14;

    font-size: 23px;

    font-weight: bold;

    text-shadow:

        0 0 10px

        rgba(57,255,20,.35);

}


/* =========================================
   MONTH BAR
========================================= */

.month-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 15px 20px;

    margin-bottom: 15px;

    border-radius: 14px;

    background:

        rgba(15,20,32,.95);

    border:

        1px solid

        rgba(255,255,255,.06);

}


.month {

    font-size: 18px;

    font-weight: bold;

}


.month span {

    color: #00f3ff;

}


.records {

    color: #7f8da3;

    font-size: 13px;

}


/* =========================================
   TABLE
========================================= */

.table-wrapper {

    overflow-x: auto;

    border-radius: 16px;

    background:

        rgba(10,13,20,.96);

    border:

        1px solid

        rgba(0,243,255,.15);

    box-shadow:

        0 15px 50px

        rgba(0,0,0,.35);

}


table {

    width: 100%;

    min-width: 780px;

    border-collapse: collapse;

}


thead {

    background:

        linear-gradient(

            90deg,

            rgba(0,243,255,.15),

            rgba(0,102,255,.12)

        );

}


th {

    padding: 16px;

    text-align: left;

    color: #00f3ff;

    font-size: 13px;

    letter-spacing: 1px;

    border-bottom:

        1px solid

        rgba(0,243,255,.2);

}


td {

    padding: 15px 16px;

    color: #d9e0eb;

    font-size: 14px;

    border-bottom:

        1px solid

        rgba(255,255,255,.05);

}


tbody tr {

    transition: .2s;

}


tbody tr:hover {

    background:

        rgba(0,243,255,.06);

}


/* =========================================
   NUMBER
========================================= */

.no {

    width: 70px;

    color: #68768b;

    font-family: monospace;

}


/* =========================================
   DATE
========================================= */

.date {

    color: #b9c5d5;

    white-space: nowrap;

    font-family: monospace;

    font-size: 14px;

}


.day {

    color: #00f3ff;

    margin-left: 5px;

}


/* =========================================
   HISTORY IMAGE
========================================= */

.history-photo {

    width: 48px;

    height: 48px;

    border-radius: 50%;

    object-fit: cover;

    border:

        2px solid

        #00f3ff;

    box-shadow:

        0 0 10px

        rgba(0,243,255,.35);

    display: block;

}


.history-photo-placeholder {

    width: 48px;

    height: 48px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #111827;

    border:

        2px solid

        #3b4555;

    color: #68768b;

    font-size: 9px;

    text-align: center;

}


/* =========================================
   TIME
========================================= */

.time {

    color: white;

    font-family: monospace;

    font-size: 16px;

    white-space: nowrap;

}


/* =========================================
   ACTION
========================================= */

.action {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 7px 14px;

    border-radius: 30px;

    font-size: 13px;

    font-weight: bold;

}


.action::before {

    content: "";

    width: 7px;

    height: 7px;

    border-radius: 50%;

    background: currentColor;

}


/* 出勤 */

.clock-in {

    color: #39ff14;

    background:

        rgba(57,255,20,.08);

    border:

        1px solid

        rgba(57,255,20,.25);

}


/* 休憩入り */

.break-start {

    color: #ffaa00;

    background:

        rgba(255,170,0,.08);

    border:

        1px solid

        rgba(255,170,0,.25);

}


/* 休憩戻り */

.break-end {

    color: #00f3ff;

    background:

        rgba(0,243,255,.08);

    border:

        1px solid

        rgba(0,243,255,.25);

}


/* 退勤 */

.clock-out {

    color: #ff0055;

    background:

        rgba(255,0,85,.08);

    border:

        1px solid

        rgba(255,0,85,.25);

}


/* =========================================
   EMPTY
========================================= */

.empty {

    text-align: center;

    padding: 50px;

    color: #66748a;

}


/* =========================================
   FOOTER
========================================= */

.footer {

    text-align: center;

    color: #4e596a;

    font-size: 12px;

    margin-top: 20px;

}


/* =========================================
   MOBILE
========================================= */

@media(max-width:700px) {


    body {

        padding: 12px;

    }


    .header {

        flex-direction: column;

        align-items: stretch;

        gap: 15px;

    }


    .back-btn {

        text-align: center;

    }


    .user-card {

        flex-direction: column;

        align-items: flex-start;

    }


    .work-time {

        text-align: left;

    }


    .title h1 {

        font-size: 20px;

    }

}

</style>

</head>


<body>


<div class="container">


<!-- =========================================
     HEADER
========================================= -->

<div class="header">


    <div class="header-left">


        <div class="logo">
            Y
        </div>


        <div class="title">

            <h1>
                ATTENDANCE HISTORY
            </h1>

            <p>
                打刻履歴 / Attendance Records
            </p>

        </div>


    </div>


    <a
        href="action.php"
        class="back-btn"
    >
        ← 戻る
    </a>


</div>


<!-- =========================================
     USER INFORMATION
========================================= -->

<div class="user-card">


    <div class="user-info">


        <?php

        /*
         * users.photo contains the complete
         * path such as:
         *
         * uploads/employee_38_xxx.jpg
         */

        if (

            $photo !== ''

            &&

            $photo !== 'default.png'

        ):

        ?>


            <img

                src="<?= htmlspecialchars(
                    $photo,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"

                class="profile"

                alt="Profile"

                onerror="
                    this.style.display='none';
                    this.nextElementSibling
                    .style.display='flex';
                "
            >


            <div
                class="profile-placeholder"
                style="display:none;"
            >

                <?= htmlspecialchars(
                    mb_substr(
                        $name,
                        0,
                        1
                    )
                ) ?>

            </div>


        <?php else: ?>


            <div class="profile-placeholder">

                <?= htmlspecialchars(
                    mb_substr(
                        $name,
                        0,
                        1
                    )
                ) ?>

            </div>


        <?php endif; ?>


        <div>


            <div class="user-name">

                <?= htmlspecialchars(
                    $name
                ) ?>

            </div>


            <div class="symbol">

                Symbol No.

                <?= htmlspecialchars(
                    $symbol_no
                ) ?>

            </div>


        </div>


    </div>


    <div class="work-time">


        <div class="work-label">

            THIS MONTH'S WORK TIME

        </div>


        <div class="work-value">

            <?= htmlspecialchars(
                $total_work_time
            ) ?>

        </div>


    </div>


</div>


<!-- =========================================
     MONTH
========================================= -->

<div class="month-bar">


    <div class="month">

        <?= $year ?>

        /

        <span>

            <?= str_pad(
                $month,
                2,
                "0",
                STR_PAD_LEFT
            ) ?>

        </span>

    </div>


    <div class="records">

        <?= $history->num_rows ?>

        records

    </div>


</div>


<!-- =========================================
     HISTORY TABLE
========================================= -->

<div class="table-wrapper">


<table>


<thead>

<tr>

    <th>
        No.
    </th>

    <th>
        Date
    </th>

    <th>
        Name
    </th>

    <th>
        Image
    </th>

    <th>
        Action
    </th>

    <th>
        Time
    </th>

</tr>

</thead>


<tbody>


<?php


$count = 1;


/* Japanese weekdays */

$weekdays = [

    "日",

    "月",

    "火",

    "水",

    "木",

    "金",

    "土"

];


if (
    $history->num_rows > 0
):


    while (
        $row =
        $history->fetch_assoc()
    ):


        /* =================================
           GET RECORD DATA
        ================================= */

        $symbol_no =
            $row['symbol_no'];


        $record_name =
            $row['name'];


        /*
         * IMPORTANT:
         *
         * This photo comes from
         * attendance.photo.
         */

        $record_photo =
            trim(
                $row['photo'] ?? ''
            );


        $action =
            $row['action'];


        $timestamp =
            strtotime(
                $row['date_time']
            );


        /* =================================
           ACTION COLOR
        ================================= */

        if (
            $action === "出勤"
        ) {

            $action_class =
                "clock-in";

        }

        elseif (
            $action === "休憩入り"
        ) {

            $action_class =
                "break-start";

        }

        elseif (
            $action === "休憩戻り"
        ) {

            $action_class =
                "break-end";

        }

        elseif (
            $action === "退勤"
        ) {

            $action_class =
                "clock-out";

        }

        else {

            $action_class = "";

        }


        /* =================================
           DATE
        ================================= */

        $display_date =
            date(
                "Y/m/d",
                $timestamp
            );


        /* =================================
           WEEKDAY
        ================================= */

        $weekday =
            $weekdays[
                (int)date(
                    "w",
                    $timestamp
                )
            ];


        /* =================================
           TIME
        ================================= */

        $display_time =
            date(
                "H:i:s",
                $timestamp
            );

?>


<tr>


    <!-- NUMBER -->

    <td class="no">

        <?= str_pad(
            $count,
            3,
            "0",
            STR_PAD_LEFT
        ) ?>

    </td>


    <!-- DATE -->

    <td class="date">

        <?= htmlspecialchars(
            $display_date
        ) ?>


        <span class="day">

            (
            <?= htmlspecialchars(
                $weekday
            ) ?>
            )

        </span>

    </td>


    <!-- NAME -->

    <td>

        <?= htmlspecialchars(
            $record_name
        ) ?>

    </td>


    <!-- IMAGE -->

    <td>


        <?php if (

            $record_photo !== ''

            &&

            $record_photo !== 'default.png'

        ): ?>


            <img

                src="<?= htmlspecialchars(
                    $record_photo,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"

                class="history-photo"

                alt="Employee photo"

                onerror="
                    this.style.display='none';
                    this.nextElementSibling
                    .style.display='flex';
                "
            >


            <div
                class="history-photo-placeholder"
                style="display:none;"
            >
                No Image
            </div>


        <?php else: ?>


            <div class="history-photo-placeholder">

                No Image

            </div>


        <?php endif; ?>


    </td>


    <!-- ACTION -->

    <td>

        <span
            class="
                action
                <?= htmlspecialchars(
                    $action_class
                ) ?>
            "
        >

            <?= htmlspecialchars(
                $action
            ) ?>

        </span>

    </td>


    <!-- TIME -->

    <td class="time">

        <?= htmlspecialchars(
            $display_time
        ) ?>

    </td>


</tr>


<?php


        $count++;


    endwhile;


else:

?>


<tr>

    <td
        colspan="6"
        class="empty"
    >

        今月の打刻履歴はありません。

    </td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>


<!-- =========================================
     FOOTER
========================================= -->

<div class="footer">

    YSE Attendance System

    © <?= date("Y") ?>

</div>


</div>


</body>

</html>
