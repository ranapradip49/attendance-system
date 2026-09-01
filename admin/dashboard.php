<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    header("Location: login.php");
    exit();

}

include "../db/connect.php";


// Total users

$userQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE is_verified = 1
");

$totalUsers = $userQuery->fetch_assoc()['total'];


// Today's attendance

$todayQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM attendance
    WHERE DATE(date_time) = CURDATE()
");

$todayAttendance = $todayQuery->fetch_assoc()['total'];


// Today's check-in

$checkinQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM attendance
    WHERE DATE(date_time) = CURDATE()
    AND action = '出勤'
");

$todayCheckin = $checkinQuery->fetch_assoc()['total'];


// Today's checkout

$checkoutQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM attendance
    WHERE DATE(date_time) = CURDATE()
    AND action = '退勤'
");

$todayCheckout = $checkoutQuery->fetch_assoc()['total'];


// Recent attendance

$recentQuery = $conn->query("
    SELECT
        attendance.*,
        users.name,
        users.symbol_no,
        users.photo
    FROM attendance
    JOIN users
        ON attendance.user_id = users.id
    ORDER BY attendance.date_time DESC
    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="ja">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<style>

*{

    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;

}

body{

    min-height:100vh;

    background:#050505;

    color:white;

}


/* =========================
   HEADER
========================= */

.header{

    height:80px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 35px;

    background:#0d0e15;

    border-bottom:2px solid #00ffff;

    box-shadow:0 0 20px rgba(0,255,255,.3);

}

.logo{

    font-size:24px;

    font-weight:bold;

    color:#00ffff;

    letter-spacing:2px;

    text-shadow:0 0 10px cyan;

}

.admin-info{

    display:flex;

    align-items:center;

    gap:20px;

}

.admin-name{

    color:#00ff99;

}

.logout{

    padding:10px 20px;

    border:1px solid #ff0055;

    border-radius:20px;

    color:#ff0055;

    text-decoration:none;

    transition:.3s;

}

.logout:hover{

    background:#ff0055;

    color:#000;

    box-shadow:0 0 20px #ff0055;

}


/* =========================
   MAIN
========================= */

.container{

    padding:35px;

}


/* =========================
   TITLE
========================= */

.title{

    margin-bottom:30px;

}

.title h1{

    color:#fff;

    font-size:32px;

    text-shadow:0 0 15px cyan;

}

.title p{

    margin-top:8px;

    color:#888;

}


/* =========================
   STAT CARDS
========================= */

.stats{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:20px;

    margin-bottom:35px;

}

.card{

    padding:25px;

    background:#0d0e15;

    border:1px solid #00ffff;

    border-radius:18px;

    box-shadow:0 0 15px rgba(0,255,255,.2);

    transition:.3s;

}

.card:hover{

    transform:translateY(-5px);

    box-shadow:0 0 30px rgba(0,255,255,.5);

}

.card-title{

    color:#aaa;

    margin-bottom:15px;

}

.card-number{

    font-size:35px;

    font-weight:bold;

    color:#00ffff;

    text-shadow:0 0 10px cyan;

}

.green{

    color:#00ff99;

    text-shadow:0 0 10px #00ff99;

}

.pink{

    color:#ff0055;

    text-shadow:0 0 10px #ff0055;

}


/* =========================
   ACTIONS
========================= */

.actions{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:15px;

    margin-bottom:35px;

}

.action-btn{

    padding:18px;

    text-align:center;

    text-decoration:none;

    color:#00ffff;

    border:1px solid #00ffff;

    border-radius:15px;

    background:#0d0e15;

    transition:.3s;

}

.action-btn:hover{

    background:#00ffff;

    color:#000;

    box-shadow:0 0 25px cyan;

}


/* =========================
   TABLE
========================= */

.table-box{

    background:#0d0e15;

    border:1px solid #00ffff;

    border-radius:18px;

    padding:20px;

    overflow-x:auto;

}

.table-title{

    color:#00ffff;

    font-size:22px;

    margin-bottom:20px;

}

table{

    width:100%;

    border-collapse:collapse;

}

th{

    padding:14px;

    background:#111;

    color:#00ffff;

    border-bottom:1px solid #00ffff;

}

td{

    padding:14px;

    text-align:center;

    border-bottom:1px solid #222;

}

tr:hover{

    background:rgba(0,255,255,.05);

}

.photo{

    width:45px;

    height:45px;

    border-radius:50%;

    object-fit:cover;

    border:1px solid #00ffff;

}

.action{

    color:#00ff99;

    font-weight:bold;

}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:900px){

    .stats{

        grid-template-columns:
            repeat(2,1fr);

    }

    .actions{

        grid-template-columns:
            repeat(2,1fr);

    }

}

@media(max-width:600px){

    .header{

        padding:0 15px;

    }

    .container{

        padding:20px;

    }

    .stats{

        grid-template-columns:1fr;

    }

    .actions{

        grid-template-columns:1fr;

    }

    .logo{

        font-size:18px;

    }

}

</style>

</head>

<body>


<header class="header">

    <div class="logo">
        ADMIN PANEL
    </div>

    <div class="admin-info">

        <span class="admin-name">
            👤 <?= htmlspecialchars($_SESSION['admin_username']) ?>
        </span>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</header>


<main class="container">


<div class="title">

    <h1>Attendance Dashboard</h1>

    <p>
        Welcome to the attendance management system.
    </p>

</div>


<!-- STATISTICS -->

<div class="stats">


    <div class="card">

        <div class="card-title">
            Verified Users
        </div>

        <div class="card-number">
            <?= $totalUsers ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Today's Attendance
        </div>

        <div class="card-number">
            <?= $todayAttendance ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Today's Check-in
        </div>

        <div class="card-number green">
            <?= $todayCheckin ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Today's Check-out
        </div>

        <div class="card-number pink">
            <?= $todayCheckout ?>
        </div>

    </div>


</div>


<!-- ADMIN ACTIONS -->

<div class="actions">

    <a href="users.php" class="action-btn">
        👥 Manage Users
    </a>

    <a href="attendance.php" class="action-btn">
        📋 Attendance Records
    </a>

    <a href="register.php" class="action-btn">
        ➕ Add User
    </a>

    <a href="index.php" class="action-btn">
        🏠 Attendance Screen
    </a>

</div>


<!-- RECENT ATTENDANCE -->

<div class="table-box">

    <div class="table-title">
        Recent Attendance
    </div>


    <table>

        <thead>

            <tr>

                <th>Photo</th>

                <th>Name</th>

                <th>Symbol No</th>

                <th>Date & Time</th>

                <th>Action</th>

            </tr>

        </thead>


        <tbody>

        <?php while($row = $recentQuery->fetch_assoc()): ?>

            <tr>

                <td>

                    <?php if(!empty($row['photo'])): ?>

                        <img
                            src="uploads/<?= htmlspecialchars($row['photo']) ?>"
                            class="photo"
                        >

                    <?php else: ?>

                        👤

                    <?php endif; ?>

                </td>


                <td>
                    <?= htmlspecialchars($row['name']) ?>
                </td>


                <td>
                    <?= htmlspecialchars($row['symbol_no']) ?>
                </td>


                <td>
                    <?= htmlspecialchars($row['date_time']) ?>
                </td>


                <td class="action">
                    <?= htmlspecialchars($row['action']) ?>
                </td>

            </tr>

        <?php endwhile; ?>

        <?php
echo password_hash("admin123", PASSWORD_DEFAULT);
?>

        </tbody>

    </table>

</div>


</main>

</body>

</html>