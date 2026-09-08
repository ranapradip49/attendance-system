<?php

require_once "admin_auth.php";
require_once "../db/connect.php";

$admin_username = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin';

$error = '';
$success = '';

/*
=========================================================
ADD EMPLOYEE
=========================================================
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $symbol_prefix = trim($_POST['symbol_prefix'] ?? '');
    $symbol_no     = trim($_POST['symbol_no'] ?? '');
    $password      = $_POST['password'] ?? '';

    /*
    -----------------------------------------------------
    VALIDATION
    -----------------------------------------------------
    */

    if ($name === '' || $symbol_no === '' || $password === '') {

        $error = "Name, Symbol Number, and Password are required.";

    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /*
        -------------------------------------------------
        CHECK DUPLICATE SYMBOL NUMBER
        -------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE symbol_no = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {

            $stmt->bind_param("s", $symbol_no);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $error = "This Symbol Number already exists.";

            } else {

                /*
                -----------------------------------------
                INSERT EMPLOYEE
                -----------------------------------------
                */

                $stmt->close();

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (
                        name,
                        email,
                        symbol_prefix,
                        symbol_no,
                        password,
                        is_verified,
                        is_active
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        1,
                        1
                    )"
                );

                if (!$stmt) {

                    $error = "Database error: " . $conn->error;

                } else {

                    $stmt->bind_param(
                        "sssss",
                        $name,
                        $email,
                        $symbol_prefix,
                        $symbol_no,
                        $password
                    );

                    if ($stmt->execute()) {

                        header(
                            "Location: employees.php?success=" .
                            urlencode("Employee added successfully")
                        );

                        exit;

                    } else {

                        $error = "Failed to add employee: " . $stmt->error;
                    }
                }
            }

            if ($stmt) {
                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Employee | YSE Attendance Admin</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

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

    color: white;

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

    box-shadow:
        0 0 20px rgba(0,255,255,0.08);
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

    transition: 0.25s;

    border: 1px solid transparent;
}

.nav a:hover,
.nav a.active {

    color: #00ffff;

    border-color: rgba(0,255,255,0.35);

    background: rgba(0,255,255,0.06);

    box-shadow:
        0 0 12px rgba(0,255,255,0.12);
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

    max-width: 1000px;
}


/* =====================================================
   HEADER
===================================================== */

.page-header {

    margin-bottom: 25px;
}

.page-header h1 {

    font-size: 30px;

    margin-bottom: 7px;
}

.page-header p {

    color: #888;
}


/* =====================================================
   FORM CARD
===================================================== */

.form-card {

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.20);

    border-radius: 14px;

    padding: 30px;

    box-shadow:
        0 0 25px rgba(0,255,255,0.05);
}


/* =====================================================
   FORM GROUP
===================================================== */

.form-group {

    margin-bottom: 22px;
}

.form-group label {

    display: block;

    margin-bottom: 8px;

    color: #00ffff;

    font-size: 14px;

    font-weight: bold;
}

.required {

    color: #ff5577;
}

.form-input {

    width: 100%;

    padding: 13px 15px;

    background: #111;

    border: 1px solid #333;

    border-radius: 8px;

    color: white;

    font-size: 15px;

    outline: none;
}

.form-input:focus {

    border-color: #00ffff;

    box-shadow:
        0 0 10px rgba(0,255,255,0.15);
}

.form-help {

    margin-top: 6px;

    color: #666;

    font-size: 12px;
}


/* =====================================================
   TWO COLUMN
===================================================== */

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding: 13px 16px;

    border-radius: 8px;

    margin-bottom: 22px;

    font-size: 14px;
}

.alert-error {

    color: #ff5577;

    background: rgba(255,50,80,0.08);

    border: 1px solid rgba(255,50,80,0.3);
}


/* =====================================================
   BUTTONS
===================================================== */

.form-actions {

    display: flex;

    gap: 12px;

    margin-top: 30px;
}

.save-btn {

    border: none;

    padding: 13px 25px;

    border-radius: 8px;

    background: #00ffff;

    color: #000;

    font-weight: bold;

    cursor: pointer;

    font-size: 14px;

    box-shadow:
        0 0 12px rgba(0,255,255,0.4);

    transition: 0.25s;
}

.save-btn:hover {

    box-shadow:
        0 0 22px rgba(0,255,255,0.7);

    transform: translateY(-1px);
}

.cancel-btn {

    text-decoration: none;

    padding: 13px 25px;

    border-radius: 8px;

    border: 1px solid #444;

    color: #aaa;

    font-weight: bold;
}

.cancel-btn:hover {

    color: white;

    border-color: #777;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 800px) {

    .sidebar {

        width: 190px;
    }

    .main {

        margin-left: 190px;

        padding: 20px;
    }

    .form-row {

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

    <div class="page-header">

        <h1>Add Employee</h1>

        <p>
            Create a new employee account.
        </p>

    </div>


    <?php if ($error !== ''): ?>

        <div class="alert alert-error">
            ❌ <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="form-card">

        <form method="POST" action="employee_add.php">


            <!-- NAME -->

            <div class="form-group">

                <label>
                    Employee Name
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-input"
                    placeholder="Enter employee name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email
                    <span class="required">*</span>
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-input"
                    placeholder="employee@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >

            </div>


            <!-- SYMBOL -->

            <div class="form-row">

                <div class="form-group">

                    <label>
                        Symbol Prefix
                    </label>

                    <input
                        type="text"
                        name="symbol_prefix"
                        class="form-input"
                        placeholder="Example: YSE-"
                        value="<?= htmlspecialchars($_POST['symbol_prefix'] ?? '') ?>"
                    >

                    <div class="form-help">
                        Optional. Example: YSE-
                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Symbol Number
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="symbol_no"
                        class="form-input"
                        placeholder="Example: 1001"
                        value="<?= htmlspecialchars($_POST['symbol_no'] ?? '') ?>"
                        required
                    >

                    <div class="form-help">
                        Must be unique.
                    </div>

                </div>

            </div>

            <!-- PASSWORD -->

<div class="form-group">

    <label>
        Password
        <span class="required">*</span>
    </label>

    <input
        type="password"
        name="password"
        class="form-input"
        placeholder="Enter employee password"
        minlength="6"
        required
    >

    <div class="form-help">
        Minimum 6 characters.
    </div>

</div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="save-btn"
                >
                    ＋ Add Employee
                </button>

                <a
                    href="employees.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>

</body>

</html>