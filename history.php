<?php

session_start();

include "db/connect.php";

if (!isset($_SESSION['user_id'])) {
    exit("Login required");
}

$user_id = $_SESSION['user_id'];


/* =========================================
   GET USER
========================================= */

$user_sql = $conn->prepare("
    SELECT symbol_no, name, photo
    FROM users
    WHERE id = ?
    LIMIT 1
");

$user_sql->bind_param("i", $user_id);
$user_sql->execute();

$user_result = $user_sql->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    exit("User not found");
}

$symbol_no = $user['symbol_no'];
$name      = $user['name'];
$photo     = $user['photo'];


/* =========================================
   SELECTED MONTH
========================================= */

$selected_month = $_GET['month'] ?? date("Y-m");

if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date("Y-m");
}

$selected_date = DateTime::createFromFormat(
    'Y-m',
    $selected_month
);

if (!$selected_date) {
    $selected_month = date("Y-m");
    $selected_date = new DateTime();
}

$year  = $selected_date->format("Y");
$month = $selected_date->format("m");


/* =========================================
   GET ATTENDANCE FOR CALCULATION
   ASCENDING ORDER IS IMPORTANT
========================================= */

$calc_sql = $conn->prepare("
    SELECT action, date_time
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

$calc_result = $calc_sql->get_result();


/* =========================================
   WORKING HOURS CALCULATION
========================================= */

$work_seconds = 0;

$start_time = null;
$break_start = null;
$break_seconds = 0;

while ($row = $calc_result->fetch_assoc()) {

    $action = $row['action'];
    $time   = strtotime($row['date_time']);


    /* -------------------------------
       出勤
    -------------------------------- */

    if ($action === "出勤") {

        // Start a new work session
        $start_time = $time;

        // Reset break time for this session
        $break_start = null;
        $break_seconds = 0;
    }


    /* -------------------------------
       休憩入り
    -------------------------------- */

    elseif ($action === "休憩入り") {

        // Only start break if currently working
        if ($start_time !== null && $break_start === null) {

            $break_start = $time;
        }
    }


    /* -------------------------------
       休憩戻り
    -------------------------------- */

    elseif ($action === "休憩戻り") {

        if (
            $start_time !== null &&
            $break_start !== null
        ) {

            $break_seconds +=
                ($time - $break_start);

            $break_start = null;
        }
    }


    /* -------------------------------
       退勤
    -------------------------------- */

    elseif ($action === "退勤") {

        if ($start_time !== null) {

            /*
             * If employee forgot to press
             * 休憩戻り, calculate the break
             * until 退勤.
             */

            if ($break_start !== null) {

                $break_seconds +=
                    ($time - $break_start);

                $break_start = null;
            }


            /*
             * Total session time
             */

            $session_seconds =
                ($time - $start_time);


            /*
             * Remove break time
             */

            $session_work =
                $session_seconds - $break_seconds;


            /*
             * Prevent negative value
             */

            if ($session_work > 0) {

                $work_seconds += $session_work;
            }


            /*
             * Reset session
             */

            $start_time = null;
            $break_seconds = 0;
        }
    }
}


/* =========================================
   CONVERT WORK TIME
========================================= */

if ($work_seconds < 0) {
    $work_seconds = 0;
}

$hours = floor($work_seconds / 3600);

$minutes = floor(
    ($work_seconds % 3600) / 60
);

$total_work_time =
    $hours . "時間 " .
    $minutes . "分";


/* =========================================
   GET HISTORY
========================================= */

$history_sql = $conn->prepare("
    SELECT
        symbol_no,
        name,
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

$history = $history_sql->get_result();

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


.user-name {

    font-size: 20px;

    font-weight: bold;
}


.symbol {

    margin-top: 5px;

    color: #7f8da3;

    font-size: 13px;
}


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

    min-width: 650px;

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

        <?php if (!empty($photo)) { ?>

            <img
                src="uploads/<?=
                    htmlspecialchars($photo)
                ?>"
                class="profile"
                alt="Profile"
            >

        <?php } else { ?>

            <div
                class="profile"
                style="
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    background:#111827;
                    color:#00f3ff;
                    font-size:24px;
                    font-weight:bold;
                "
            >

                <?= htmlspecialchars(
                    mb_substr($name, 0, 1)
                ) ?>

            </div>

        <?php } ?>


        <div>

            <div class="user-name">

                <?= htmlspecialchars($name) ?>

            </div>

            <div class="symbol">

                Symbol No.
                <?= htmlspecialchars($symbol_no) ?>

            </div>

        </div>

    </div>


    <div class="work-time">

        <div class="work-label">
            THIS MONTH'S WORK TIME
        </div>

        <div class="work-value">

            <?= $total_work_time ?>

        </div>

    </div>

</div>


<!-- =========================================
     MONTH
========================================= -->

<div class="month-bar">

    <div class="month">

        <?= $year ?> /

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
     HISTORY
========================================= -->

<div class="table-wrapper">

<table>

<thead>

<tr>

    <th>No.</th>

    <th>Date</th>

    <th>Name</th>

    <th>Action</th>

    <th>Time</th>

</tr>

</thead>


<tbody>


<?php

$count = 1;


/* Japanese weekday */

$weekdays = [
    "日",
    "月",
    "火",
    "水",
    "木",
    "金",
    "土"
];


if ($history->num_rows > 0) {

    while ($row = $history->fetch_assoc()) {

       $symbol_no = $row['symbol_no'];
        $name = $row['name'];
        $action = $row['action'];

        $timestamp = strtotime($row['date_time']);


        /* Action color */

        if ($action === "出勤") {

            $action_class =
                "clock-in";

        }

        elseif ($action === "休憩入り") {

            $action_class =
                "break-start";

        }

        elseif ($action === "休憩戻り") {

            $action_class =
                "break-end";

        }

        elseif ($action === "退勤") {

            $action_class =
                "clock-out";

        }

        else {

            $action_class = "";

        }


        /* Date */

        $display_date =
            date("Y/m/d", $timestamp);


        /* Weekday */

        $weekday =
            $weekdays[
                date("w", $timestamp)
            ];


        /* Time */

        $display_time =
            date("H:i:s", $timestamp);

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

        <?= date("Y/m/d", $timestamp) ?>

        <span class="day">

            (<?= $weekdays[
                date("w", $timestamp)
            ] ?>)

        </span>

    </td>


    <!-- NAME -->

    <td>

        <?= htmlspecialchars($name) ?>

    </td>


    <!-- ACTION -->

    <td>

        <span class="action <?= $action_class ?>">

            <?= htmlspecialchars($action) ?>

        </span>

    </td>


    <!-- TIME -->

    <td class="time">

        <?= date("H:i:s", $timestamp) ?>

    </td>

</tr>

        <?php

        $count++;

    }

}

else {

?>

<tr>

    <td
        colspan="5"
        class="empty"
    >

        今月の打刻履歴はありません。

    </td>

</tr>

<?php

}

?>


</tbody>

</table>

</div>


<div class="footer">

    YSE Attendance System
    © <?= date("Y") ?>

</div>


</div>


</body>

</html>