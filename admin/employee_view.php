<?php

require_once "admin_auth.php";
require_once "../db/connect.php";

$admin_username = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin';


/*
=========================================================
GET EMPLOYEE ID
=========================================================
*/

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header("Location: employees.php");
    exit();
}


/*
=========================================================
GET EMPLOYEE
=========================================================
*/

$stmt = $conn->prepare("
    SELECT
        u.id,
        u.name,
        u.email,
        u.symbol_prefix,
        u.symbol_no,
        u.photo,
        u.is_verified,

        CASE
            WHEN fd.user_id IS NOT NULL THEN 1
            ELSE 0
        END AS face_registered

    FROM users u

    LEFT JOIN (
        SELECT DISTINCT user_id
        FROM face_data
    ) fd ON fd.user_id = u.id

    WHERE u.id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$employee_result = $stmt->get_result();

if ($employee_result->num_rows === 0) {
    header("Location: employees.php");
    exit();
}

$employee = $employee_result->fetch_assoc();

$stmt->close();


/*
=========================================================
ATTENDANCE SUMMARY
=========================================================
*/

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_records,
        COUNT(DISTINCT DATE(date_time)) AS attendance_days
    FROM attendance
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$stmt->close();


/*
=========================================================
GET ATTENDANCE HISTORY
=========================================================
*/

$stmt = $conn->prepare("
    SELECT
        id,
        action,
        date_time
    FROM attendance
    WHERE user_id = ?
    ORDER BY date_time DESC
    LIMIT 100
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$attendance_result = $stmt->get_result();


/*
=========================================================
HELPER
=========================================================
*/

function action_label($action)
{
    switch ($action) {

        case '出勤':
            return 'Check In';

        case '休憩入り':
            return 'Break Start';

        case '休憩戻り':
            return 'Break Return';

        case '退勤':
            return 'Check Out';

        default:
            return $action;
    }
}


function action_class($action)
{
    switch ($action) {

        case '出勤':
            return 'checkin';

        case '休憩入り':
            return 'break-start';

        case '休憩戻り':
            return 'break-end';

        case '退勤':
            return 'checkout';

        default:
            return '';
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?= htmlspecialchars($employee['name']) ?> | YSE Admin
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


/* =====================================================
   BODY
===================================================== */

body {

    font-family: Arial, Helvetica, sans-serif;

    background:
        radial-gradient(
            circle at top left,
            rgba(0,255,255,0.08),
            transparent 35%
        ),

        radial-gradient(
            circle at bottom right,
            rgba(255,0,255,0.08),
            transparent 35%
        ),

        #050505;

    color: #fff;

    min-height: 100vh;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 240px;

    height: 100vh;

    background: #090909;

    border-right: 1px solid rgba(0,255,255,0.25);

    padding: 25px 15px;

}


.logo {

    text-align: center;

    font-size: 24px;

    font-weight: bold;

    letter-spacing: 3px;

    color: #00ffff;

    text-shadow:
        0 0 5px #00ffff,
        0 0 15px #00ffff;

    margin-bottom: 35px;

}


.admin-name {

    text-align: center;

    color: #aaa;

    font-size: 13px;

    margin-bottom: 25px;

}


.nav {

    display: flex;

    flex-direction: column;

    gap: 8px;

}


.nav a {

    text-decoration: none;

    color: #bbb;

    padding: 13px 15px;

    border-radius: 8px;

    border: 1px solid transparent;

    transition: 0.25s;

}


.nav a:hover {

    color: #00ffff;

    border-color: rgba(0,255,255,0.35);

    background: rgba(0,255,255,0.06);

}


.nav a.active {

    color: #00ffff;

    border-color: rgba(0,255,255,0.35);

    background: rgba(0,255,255,0.06);

}


.nav a.logout {

    color: #ff4dff;

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 240px;

    padding: 35px;

}


/* =====================================================
   BACK BUTTON
===================================================== */

.back-btn {

    display: inline-block;

    margin-bottom: 20px;

    text-decoration: none;

    color: #00ffff;

    border: 1px solid rgba(0,255,255,0.3);

    padding: 9px 15px;

    border-radius: 7px;

}


.back-btn:hover {

    background: rgba(0,255,255,0.06);

}


/* =====================================================
   PROFILE
===================================================== */

.profile-card {

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.2);

    border-radius: 14px;

    padding: 30px;

    display: flex;

    gap: 30px;

    align-items: center;

    margin-bottom: 25px;

}


.profile-photo {

    width: 130px;

    height: 130px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid #00ffff;

    box-shadow:
        0 0 15px rgba(0,255,255,0.35);

}


.no-photo {

    width: 130px;

    height: 130px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #151515;

    border: 2px solid #444;

    color: #666;

    font-size: 40px;

}


.profile-info h1 {

    font-size: 30px;

    margin-bottom: 8px;

}


.symbol {

    display: inline-block;

    color: #00ffff;

    border: 1px solid rgba(0,255,255,0.35);

    padding: 6px 12px;

    border-radius: 20px;

    margin-bottom: 15px;

}


.email {

    color: #999;

    margin-bottom: 15px;

}


/* =====================================================
   BADGES
===================================================== */

.badges {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

}


.badge {

    display: inline-block;

    padding: 6px 11px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


.green {

    color: #00ff99;

    background: rgba(0,255,153,0.08);

    border: 1px solid rgba(0,255,153,0.3);

}


.red {

    color: #ff5577;

    background: rgba(255,50,80,0.08);

    border: 1px solid rgba(255,50,80,0.3);

}


.cyan {

    color: #00ffff;

    background: rgba(0,255,255,0.08);

    border: 1px solid rgba(0,255,255,0.3);

}


/* =====================================================
   STATS
===================================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;

    margin-bottom: 25px;

}


.stat-card {

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.16);

    border-radius: 12px;

    padding: 22px;

}


.stat-title {

    color: #888;

    font-size: 13px;

    margin-bottom: 10px;

}


.stat-value {

    font-size: 28px;

    font-weight: bold;

    color: #00ffff;

}


/* =====================================================
   SECTION
===================================================== */

.section {

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.16);

    border-radius: 12px;

    overflow: hidden;

}


.section-header {

    padding: 20px;

    border-bottom: 1px solid #222;

}


.section-header h2 {

    font-size: 20px;

}


.section-header p {

    color: #777;

    font-size: 13px;

    margin-top: 5px;

}


/* =====================================================
   TABLE
===================================================== */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 650px;

}


th {

    padding: 14px 18px;

    text-align: left;

    color: #00ffff;

    font-size: 12px;

    border-bottom: 1px solid rgba(0,255,255,0.2);

}


td {

    padding: 14px 18px;

    border-bottom: 1px solid #1c1c1c;

    color: #ddd;

    font-size: 14px;

}


tr:hover td {

    background: rgba(255,255,255,0.02);

}


/* =====================================================
   ACTION BADGES
===================================================== */

.action {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

}


.checkin {

    color: #00ff99;

    border: 1px solid rgba(0,255,153,0.3);

    background: rgba(0,255,153,0.07);

}


.break-start {

    color: #ffff00;

    border: 1px solid rgba(255,255,0,0.3);

    background: rgba(255,255,0,0.06);

}


.break-end {

    color: #00ffff;

    border: 1px solid rgba(0,255,255,0.3);

    background: rgba(0,255,255,0.06);

}


.checkout {

    color: #ff4dff;

    border: 1px solid rgba(255,0,255,0.3);

    background: rgba(255,0,255,0.06);

}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    text-align: center;

    padding: 45px;

    color: #777;

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:800px) {

    .sidebar {

        width: 190px;

    }

    .main {

        margin-left: 190px;

        padding: 20px;

    }

    .profile-card {

        flex-direction: column;

        align-items: flex-start;

    }

}


@media(max-width:550px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        border-right: none;

        border-bottom: 1px solid rgba(0,255,255,0.2);

    }

    .main {

        margin-left: 0;

    }

    .stats {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

    <div class="logo">
        YSE ADMIN
    </div>


    <div class="admin-name">

        👤 <?= htmlspecialchars($admin_username) ?>

    </div>


    <div class="nav">

        <a href="dashboard.php">
            📊 Dashboard
        </a>

        <a href="employees.php" class="active">
            👥 Employees
        </a>

        <a href="attendance.php">
            📋 Attendance
        </a>

        <a href="monthly_report.php">
            📅 Monthly Report
        </a>

        <a href="correction_history.php">
            📝 Correction History
        </a>

        <a href="../index.php">
            🌐 Main Website
        </a>

        <a href="logout.php" class="logout">
            🚪 Logout
        </a>

    </div>

</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


    <a href="employees.php" class="back-btn">
        ← Back to Employees
    </a>


    <!-- =================================================
         PROFILE
    ================================================= -->

    <div class="profile-card">


        <?php if (!empty($employee['photo'])): ?>

            <img
                src="../<?= htmlspecialchars($employee['photo']) ?>"
                class="profile-photo"
                alt="Employee Photo"
            >

        <?php else: ?>

            <div class="no-photo">
                👤
            </div>

        <?php endif; ?>


        <div class="profile-info">

            <h1>
                <?= htmlspecialchars($employee['name']) ?>
            </h1>


            <div class="symbol">

                <?= htmlspecialchars(
                    ($employee['symbol_prefix'] ?? '') .
                    ($employee['symbol_no'] ?? '')
                ) ?>

            </div>


            <div class="email">

                <?= htmlspecialchars($employee['email']) ?>

            </div>


            <div class="badges">

                <?php if ((int)$employee['is_verified'] === 1): ?>

                    <span class="badge green">
                        ✓ Account Verified
                    </span>

                <?php else: ?>

                    <span class="badge red">
                        ✕ Account Unverified
                    </span>

                <?php endif; ?>


                <?php if ((int)$employee['face_registered'] === 1): ?>

                    <span class="badge green">
                        ✓ Face Registered
                    </span>

                <?php else: ?>

                    <span class="badge red">
                        ✕ Face Not Registered
                    </span>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <!-- =================================================
         STATS
    ================================================= -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-title">
                Total Attendance Records
            </div>

            <div class="stat-value">

                <?= (int)$summary['total_records'] ?>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Attendance Days
            </div>

            <div class="stat-value">

                <?= (int)$summary['attendance_days'] ?>

            </div>

        </div>


    </div>


    <!-- =================================================
         ATTENDANCE HISTORY
    ================================================= -->

    <div class="section">


        <div class="section-header">

            <h2>
                Attendance History
            </h2>

            <p>
                Latest attendance records for this employee.
            </p>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($attendance_result->num_rows > 0): ?>


                    <?php while ($row = $attendance_result->fetch_assoc()): ?>


                        <tr>


                            <td>

                                #<?= (int)$row['id'] ?>

                            </td>


                            <td>

                                <?= date(
                                    'Y/m/d',
                                    strtotime($row['date_time'])
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'H:i:s',
                                    strtotime($row['date_time'])
                                ) ?>

                            </td>


                            <td>

                                <span class="action <?= action_class($row['action']) ?>">

                                    <?= htmlspecialchars(
                                        $row['action']
                                    ) ?>

                                    ·

                                    <?= htmlspecialchars(
                                        action_label($row['action'])
                                    ) ?>

                                </span>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td colspan="4" class="empty">

                            No attendance records found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>