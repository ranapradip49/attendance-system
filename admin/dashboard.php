<?php

session_start();

include "../db/connect.php";


/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: login.php");

    exit();

}


$admin_username =
    $_SESSION['admin_username'];


/* =========================================
   TOTAL USERS
========================================= */

$result = $conn->query("

    SELECT COUNT(*) AS total

    FROM users

");

$total_users =
    $result->fetch_assoc()['total'];


/* =========================================
   VERIFIED USERS
========================================= */

$result = $conn->query("

    SELECT COUNT(*) AS total

    FROM users

    WHERE is_verified = 1

");

$verified_users =
    $result->fetch_assoc()['total'];


/* =========================================
   TODAY'S ATTENDANCE
========================================= */

$result = $conn->query("

    SELECT COUNT(DISTINCT user_id) AS total

    FROM attendance

    WHERE DATE(date_time) = CURDATE()

    AND action = '出勤'

");

$today_attendance =
    $result->fetch_assoc()['total'];


/* =========================================
   CURRENTLY WORKING
========================================= */

$result = $conn->query("

    SELECT COUNT(*) AS total

    FROM (

        SELECT
            user_id,
            MAX(date_time) AS last_time

        FROM attendance

        WHERE DATE(date_time) = CURDATE()

        GROUP BY user_id

    ) AS latest

    INNER JOIN attendance a

        ON a.user_id = latest.user_id

        AND a.date_time = latest.last_time

    WHERE a.action IN (
        '出勤',
        '休憩戻り'
    )

");

$currently_working =
    $result->fetch_assoc()['total'];


/* =========================================
   TOTAL ATTENDANCE RECORDS TODAY
========================================= */

$result = $conn->query("

    SELECT COUNT(*) AS total

    FROM attendance

    WHERE DATE(date_time) = CURDATE()

");

$today_records =
    $result->fetch_assoc()['total'];


/* =========================================
   GET USERS
========================================= */

$users = $conn->query("

    SELECT

        id,
        symbol_no,
        name,
        email,
        is_verified,
        photo

    FROM users

    ORDER BY id DESC

");


/* =========================================
   TODAY'S RECORDS
========================================= */

$attendance = $conn->query("

    SELECT

        symbol_no,
        name,
        action,
        date_time

    FROM attendance

    WHERE DATE(date_time) = CURDATE()

    ORDER BY date_time DESC

    LIMIT 20

");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Admin Dashboard
</title>


<style>

/* =========================================
   GLOBAL
========================================= */

*{

    margin:0;

    padding:0;

    box-sizing:border-box;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


body{

    min-height:100vh;

    background:

        radial-gradient(
            circle at top left,
            #17243a,
            #080b12 45%,
            #030406
        );

    color:white;

}


/* =========================================
   SIDEBAR
========================================= */

.sidebar{

    position:fixed;

    left:0;

    top:0;

    width:240px;

    height:100vh;

    padding:25px 15px;

    background:
        rgba(5,8,15,.97);

    border-right:
        1px solid
        rgba(0,243,255,.2);

    box-shadow:
        10px 0 30px
        rgba(0,0,0,.3);

}


.logo{

    text-align:center;

    color:#00f3ff;

    font-size:26px;

    font-weight:bold;

    padding:20px 0;

    text-shadow:
        0 0 15px cyan;

}


.logo span{

    display:block;

    color:#77859a;

    font-size:11px;

    margin-top:7px;

    letter-spacing:2px;

}


.nav{

    margin-top:35px;

}


.nav a{

    display:block;

    padding:15px;

    margin-bottom:10px;

    color:#aab4c3;

    text-decoration:none;

    border-radius:10px;

    transition:.25s;

}


.nav a:hover,
.nav a.active{

    background:
        rgba(0,243,255,.1);

    color:#00f3ff;

    box-shadow:
        inset 3px 0 0 #00f3ff;
}


/* =========================================
   MAIN
========================================= */

.main{

    margin-left:240px;

    padding:25px;

}


/* =========================================
   HEADER
========================================= */

.header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:25px;

}


.header h1{

    font-size:28px;

    letter-spacing:2px;

}


.header p{

    color:#718096;

    margin-top:5px;

}


.admin-info{

    display:flex;

    align-items:center;

    gap:15px;

}


.admin-name{

    padding:10px 15px;

    border-radius:10px;

    background:
        rgba(0,243,255,.07);

    border:
        1px solid
        rgba(0,243,255,.2);

    color:#00f3ff;

}


.logout{

    padding:10px 18px;

    border-radius:10px;

    color:#ff0055;

    text-decoration:none;

    border:
        1px solid
        rgba(255,0,85,.4);

    transition:.25s;

}


.logout:hover{

    background:#ff0055;

    color:white;

    box-shadow:
        0 0 15px #ff0055;

}


/* =========================================
   STAT CARDS
========================================= */

.stats{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:18px;

    margin-bottom:25px;

}


.card{

    padding:22px;

    border-radius:16px;

    background:
        rgba(15,20,32,.9);

    border:
        1px solid
        rgba(0,243,255,.12);

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.3);

}


.card-label{

    color:#7f8da3;

    font-size:13px;

    margin-bottom:10px;

}


.card-value{

    font-size:32px;

    font-weight:bold;

}


.card.cyan
.card-value{

    color:#00f3ff;

}


.card.green
.card-value{

    color:#39ff14;

}


.card.pink
.card-value{

    color:#ff0055;

}


.card.orange
.card-value{

    color:#ffaa00;

}


/* =========================================
   CONTENT GRID
========================================= */

.content-grid{

    display:grid;

    grid-template-columns:
        1fr 1fr;

    gap:20px;

    margin-bottom:20px;

}


.panel{

    background:
        rgba(10,13,20,.95);

    border:
        1px solid
        rgba(0,243,255,.13);

    border-radius:16px;

    overflow:hidden;

}


.panel-header{

    padding:18px 20px;

    border-bottom:
        1px solid
        rgba(255,255,255,.06);

    display:flex;

    justify-content:space-between;

    align-items:center;

}


.panel-header h2{

    font-size:17px;

}


.panel-header span{

    color:#00f3ff;

    font-size:12px;

}


/* =========================================
   TABLE
========================================= */

.table-wrapper{

    overflow-x:auto;

}


table{

    width:100%;

    border-collapse:collapse;

}


th{

    padding:14px;

    text-align:left;

    font-size:12px;

    color:#00f3ff;

    background:
        rgba(0,243,255,.05);

}


td{

    padding:13px 14px;

    font-size:13px;

    color:#d4dbe5;

    border-top:
        1px solid
        rgba(255,255,255,.04);

}


tr:hover{

    background:
        rgba(0,243,255,.04);

}


/* =========================================
   STATUS
========================================= */

.status{

    display:inline-block;

    padding:5px 10px;

    border-radius:20px;

    font-size:11px;

}


.verified{

    color:#39ff14;

    background:
        rgba(57,255,20,.08);

}


.unverified{

    color:#ff0055;

    background:
        rgba(255,0,85,.08);

}


/* =========================================
   ACTION
========================================= */

.action{

    display:inline-block;

    padding:5px 10px;

    border-radius:20px;

    font-size:11px;

    font-weight:bold;

}


.in{

    color:#39ff14;

    background:
        rgba(57,255,20,.08);

}


.break-in{

    color:#ffaa00;

    background:
        rgba(255,170,0,.08);

}


.break-out{

    color:#00f3ff;

    background:
        rgba(0,243,255,.08);

}


.out{

    color:#ff0055;

    background:
        rgba(255,0,85,.08);

}


/* =========================================
   EMPTY
========================================= */

.empty{

    text-align:center;

    padding:35px;

    color:#66748a;

}


/* =========================================
   RESPONSIVE
========================================= */

@media(max-width:1000px){

    .stats{

        grid-template-columns:
            repeat(2,1fr);

    }

    .content-grid{

        grid-template-columns:1fr;

    }

}


@media(max-width:700px){

    .sidebar{

        position:static;

        width:100%;

        height:auto;

    }

    .main{

        margin-left:0;

    }

    .header{

        flex-direction:column;

        align-items:flex-start;

        gap:15px;

    }

    .stats{

        grid-template-columns:1fr;

    }

}

</style>

</head>


<body>


<!-- =====================================
     SIDEBAR
===================================== -->

<div class="sidebar">

    <div class="logo">

        YSE

        <span>
            ATTENDANCE ADMIN
        </span>

    </div>


    <div class="nav">

        <a
            href="dashboard.php"
            class="active"
        >
            📊 Dashboard
        </a>

        <a href="../action.php">
            🕒 Attendance System
        </a>

        <a href="../history.php">
            📋 Attendance History
        </a>

        <a href="../index.php">
            🏠 Main Website
        </a>

        <a href="employees.php">
    👥 Employees
</a>

    </div>

</div>


<!-- =====================================
     MAIN
===================================== -->

<div class="main">


<!-- HEADER -->

<div class="header">

    <div>

        <h1>
            ADMIN DASHBOARD
        </h1>

        <p>
            Attendance System Management
        </p>

    </div>


    <div class="admin-info">

        <div class="admin-name">

            👤
            <?= htmlspecialchars(
                $admin_username
            ) ?>

        </div>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>


<!-- =====================================
     STATISTICS
===================================== -->

<div class="stats">


    <div class="card">

        <div class="card-label">
            TOTAL EMPLOYEES
        </div>

        <div class="card-value">
            <?= $total_users ?>
        </div>

    </div>


    <div class="card">

        <div class="card-label">
            VERIFIED EMPLOYEES
        </div>

        <div class="card-value">
            <?= $verified_users ?>
        </div>

    </div>


    <div class="card">

        <div class="card-label">
            TODAY'S ATTENDANCE
        </div>

        <div class="card-value">
            <?= $today_attendance ?>
        </div>

    </div>


    <div class="card">

        <div class="card-label">
            CURRENTLY WORKING
        </div>

        <div class="card-value">
            <?= $currently_working ?>
        </div>

    </div>


</div>


<!-- =====================================
     TABLES
===================================== -->

<div class="content-grid">


<!-- EMPLOYEES -->

<div class="panel">

    <div class="panel-header">

        <h2>
            Employees
        </h2>

        <span>
            <?= $total_users ?> USERS
        </span>

    </div>


    <div class="table-wrapper">

    <table>

        <thead>

        <tr>

            <th>
                Symbol
            </th>

            <th>
                Name
            </th>

            <th>
                Email
            </th>

            <th>
                Status
            </th>

        </tr>

        </thead>


        <tbody>

        <?php

        if ($users->num_rows > 0) {

            while (
                $user =
                $users->fetch_assoc()
            ) {

        ?>

        <tr>

            <td>
                <?= htmlspecialchars(
                    $user['symbol_no']
                ) ?>
            </td>


            <td>
                <?= htmlspecialchars(
                    $user['name']
                ) ?>
            </td>


            <td>
                <?= htmlspecialchars(
                    $user['email']
                ) ?>
            </td>


            <td>

            <?php

            if (
                $user['is_verified'] == 1
            ) {

            ?>

                <span
                    class="status verified"
                >
                    VERIFIED
                </span>

            <?php

            } else {

            ?>

                <span
                    class="status unverified"
                >
                    UNVERIFIED
                </span>

            <?php

            }

            ?>

            </td>

        </tr>

        <?php

            }

        } else {

        ?>

        <tr>

            <td
                colspan="4"
                class="empty"
            >

                No employees found.

            </td>

        </tr>

        <?php

        }

        ?>

        </tbody>

    </table>

    </div>

</div>


<!-- TODAY ATTENDANCE -->

<div class="panel">

    <div class="panel-header">

        <h2>
            Today's Attendance
        </h2>

        <span>
            <?= $today_records ?> RECORDS
        </span>

    </div>


    <div class="table-wrapper">

    <table>

        <thead>

        <tr>

            <th>
                Symbol
            </th>

            <th>
                Name
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

        if (
            $attendance->num_rows > 0
        ) {

            while (
                $row =
                $attendance->fetch_assoc()
            ) {


                $action_class = "";


                if (
                    $row['action'] === "出勤"
                ) {

                    $action_class = "in";

                }

                elseif (
                    $row['action'] === "休憩入り"
                ) {

                    $action_class =
                        "break-in";

                }

                elseif (
                    $row['action'] === "休憩戻り"
                ) {

                    $action_class =
                        "break-out";

                }

                elseif (
                    $row['action'] === "退勤"
                ) {

                    $action_class = "out";

                }

        ?>

        <tr>

            <td>

                <?= htmlspecialchars(
                    $row['symbol_no']
                ) ?>

            </td>


            <td>

                <?= htmlspecialchars(
                    $row['name']
                ) ?>

            </td>


            <td>

                <span
                    class="action
                    <?= $action_class ?>"
                >

                    <?= htmlspecialchars(
                        $row['action']
                    ) ?>

                </span>

            </td>


            <td>

                <?= date(
                    "H:i:s",
                    strtotime(
                        $row['date_time']
                    )
                ) ?>

            </td>

        </tr>

        <?php

            }

        } else {

        ?>

        <tr>

            <td
                colspan="4"
                class="empty"
            >

                No attendance records today.

            </td>

        </tr>

        <?php

        }

        ?>

        </tbody>

    </table>

    </div>

</div>


</div>


<!-- =====================================
     FOOTER INFORMATION
===================================== -->

<div class="panel">

    <div class="panel-header">

        <h2>
            System Information
        </h2>

    </div>


    <div style="
        padding:25px;
        display:grid;
        grid-template-columns:
        repeat(3,1fr);
        gap:20px;
    ">


        <div>

            <div class="card-label">
                TODAY'S RECORDS
            </div>

            <strong
                style="
                font-size:25px;
                color:#00f3ff;
                "
            >

                <?= $today_records ?>

            </strong>

        </div>


        <div>

            <div class="card-label">
                SYSTEM DATE
            </div>

            <strong
                style="
                font-size:20px;
                "
            >

                <?= date("Y-m-d") ?>

            </strong>

        </div>


        <div>

            <div class="card-label">
                SERVER TIME
            </div>

            <strong
                id="clock"
                style="
                font-size:20px;
                color:#39ff14;
                "
            >

                <?= date("H:i:s") ?>

            </strong>

        </div>


    </div>

</div>


</div>


<script>

function updateClock(){

    const now = new Date();

    const time =
        now.toLocaleTimeString(
            'ja-JP'
        );

    document.getElementById(
        "clock"
    ).textContent = time;

}


setInterval(
    updateClock,
    1000
);

</script>


</body>

</html>