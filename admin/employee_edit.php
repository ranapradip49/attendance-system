<?php

require_once "admin_auth.php";
require_once "../db/connect.php";

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

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
        id,
        name,
        email,
        symbol_prefix,
        symbol_no,
        photo,
        is_verified
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: employees.php");
    exit();
}

$employee = $result->fetch_assoc();

$stmt->close();


/*
=========================================================
UPDATE EMPLOYEE
=========================================================
*/

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $symbol_prefix = trim($_POST['symbol_prefix'] ?? '');
    $symbol_no = trim($_POST['symbol_no'] ?? '');
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;


    /*
    -----------------------------------------------------
    VALIDATION
    -----------------------------------------------------
    */

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($symbol_no === '') {
        $errors[] = "Symbol number is required.";
    }


    /*
    -----------------------------------------------------
    CHECK DUPLICATE EMAIL
    -----------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "si",
            $email,
            $user_id
        );

        $stmt->execute();

        $duplicate_email = $stmt->get_result();

        if ($duplicate_email->num_rows > 0) {
            $errors[] = "This email is already being used by another employee.";
        }

        $stmt->close();
    }


    /*
    -----------------------------------------------------
    CHECK DUPLICATE SYMBOL
    -----------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE symbol_prefix = ?
            AND symbol_no = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ssi",
            $symbol_prefix,
            $symbol_no,
            $user_id
        );

        $stmt->execute();

        $duplicate_symbol = $stmt->get_result();

        if ($duplicate_symbol->num_rows > 0) {
            $errors[] = "This symbol number is already being used by another employee.";
        }

        $stmt->close();
    }


    /*
    -----------------------------------------------------
    PHOTO UPLOAD
    -----------------------------------------------------
    */

    $new_photo = $employee['photo'];

    if (
        isset($_FILES['photo']) &&
        $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = "There was a problem uploading the photo.";

        } else {

            $allowed_types = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $file_type = mime_content_type(
                $_FILES['photo']['tmp_name']
            );

            if (!in_array($file_type, $allowed_types, true)) {

                $errors[] = "Only JPG, PNG or WEBP images are allowed.";

            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {

                $errors[] = "Photo must be smaller than 5MB.";

            } else {

                /*
                -------------------------------------------------
                CREATE PHOTO DIRECTORY
                -------------------------------------------------
                */

                $photo_dir = "../uploads/";

                if (!is_dir($photo_dir)) {
                    mkdir($photo_dir, 0755, true);
                }


                /*
                -------------------------------------------------
                GENERATE UNIQUE FILE NAME
                -------------------------------------------------
                */

                $extension = strtolower(
                    pathinfo(
                        $_FILES['photo']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                $filename =
                    'employee_' .
                    $user_id .
                    '_' .
                    time() .
                    '_' .
                    bin2hex(random_bytes(4)) .
                    '.' .
                    $extension;

                $destination = $photo_dir . $filename;


                /*
                -------------------------------------------------
                MOVE FILE
                -------------------------------------------------
                */

                if (move_uploaded_file(
                    $_FILES['photo']['tmp_name'],
                    $destination
                )) {

                    $new_photo = "uploads/" . $filename;

                } else {

                    $errors[] = "Could not save the uploaded photo.";

                }

            }
        }
    }


    /*
    -----------------------------------------------------
    UPDATE DATABASE
    -----------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                email = ?,
                symbol_prefix = ?,
                symbol_no = ?,
                photo = ?,
                is_verified = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            $errors[] = "Database error: " . $conn->error;
        } else {

            $stmt->bind_param(
                "sssssii",
                $name,
                $email,
                $symbol_prefix,
                $symbol_no,
                $new_photo,
                $is_verified,
                $user_id
            );

            if ($stmt->execute()) {

                $success = "Employee information updated successfully.";

                /*
                -------------------------------------------------
                UPDATE DISPLAYED DATA
                -------------------------------------------------
                */

                $employee['name'] = $name;
                $employee['email'] = $email;
                $employee['symbol_prefix'] = $symbol_prefix;
                $employee['symbol_no'] = $symbol_no;
                $employee['photo'] = $new_photo;
                $employee['is_verified'] = $is_verified;

            } else {

                $errors[] = "Could not update employee: " . $stmt->error;

            }

            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Edit Employee | YSE Admin
</title>

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

.nav a:hover,
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

    max-width: 1100px;

}


/* =====================================================
   BACK
===================================================== */

.back-btn {

    display: inline-block;

    text-decoration: none;

    color: #00ffff;

    border: 1px solid rgba(0,255,255,0.3);

    padding: 9px 15px;

    border-radius: 7px;

    margin-bottom: 25px;

}

.back-btn:hover {

    background: rgba(0,255,255,0.06);

}


/* =====================================================
   HEADER
===================================================== */

h1 {

    font-size: 30px;

    margin-bottom: 7px;

}

.subtitle {

    color: #777;

    margin-bottom: 25px;

}


/* =====================================================
   FORM CARD
===================================================== */

.card {

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.18);

    border-radius: 14px;

    padding: 30px;

}


/* =====================================================
   PROFILE
===================================================== */

.profile-preview {

    display: flex;

    align-items: center;

    gap: 25px;

    padding-bottom: 25px;

    margin-bottom: 25px;

    border-bottom: 1px solid #222;

}

.profile-photo {

    width: 110px;

    height: 110px;

    object-fit: cover;

    border-radius: 50%;

    border: 3px solid #00ffff;

    box-shadow:
        0 0 15px rgba(0,255,255,0.35);

}

.no-photo {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #151515;

    border: 2px solid #444;

    font-size: 35px;

    color: #666;

}


/* =====================================================
   FORM
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;

}

.form-group {

    display: flex;

    flex-direction: column;

    gap: 8px;

}

.form-group.full {

    grid-column: 1 / -1;

}

label {

    color: #aaa;

    font-size: 13px;

}

input[type="text"],
input[type="email"],
input[type="file"] {

    width: 100%;

    padding: 13px 14px;

    border-radius: 7px;

    border: 1px solid #333;

    background: #111;

    color: white;

    outline: none;

}

input:focus {

    border-color: #00ffff;

    box-shadow:
        0 0 10px rgba(0,255,255,0.12);

}


/* =====================================================
   VERIFY
===================================================== */

.verify-box {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 15px;

    border: 1px solid #252525;

    border-radius: 8px;

    background: #0c0c0c;

}

.verify-box input {

    width: 18px;

    height: 18px;

    accent-color: #00ffff;

}

.verify-box label {

    color: #ddd;

}


/* =====================================================
   ALERTS
===================================================== */

.alert {

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

}

.error {

    color: #ff6688;

    background: rgba(255,50,80,0.08);

    border: 1px solid rgba(255,50,80,0.3);

}

.success {

    color: #00ff99;

    background: rgba(0,255,153,0.07);

    border: 1px solid rgba(0,255,153,0.3);

}

.error ul {

    margin-left: 18px;

}


/* =====================================================
   BUTTONS
===================================================== */

.buttons {

    display: flex;

    gap: 12px;

    margin-top: 25px;

}

.save-btn {

    border: none;

    cursor: pointer;

    padding: 13px 25px;

    border-radius: 8px;

    background: #00ffff;

    color: #000;

    font-weight: bold;

    box-shadow:
        0 0 12px rgba(0,255,255,0.35);

}

.cancel-btn {

    text-decoration: none;

    padding: 13px 25px;

    border-radius: 8px;

    border: 1px solid #444;

    color: #aaa;

}

.save-btn:hover {

    box-shadow:
        0 0 22px rgba(0,255,255,0.6);

}

.cancel-btn:hover {

    color: white;

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

}

@media(max-width:600px) {

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

    .form-grid {

        grid-template-columns: 1fr;

    }

    .form-group.full {

        grid-column: auto;

    }

    .profile-preview {

        flex-direction: column;

        align-items: flex-start;

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

    <a
        href="employee_view.php?id=<?= $user_id ?>"
        class="back-btn"
    >
        ← Back to Employee
    </a>


    <h1>
        Edit Employee
    </h1>

    <p class="subtitle">
        Update employee information.
    </p>


    <div class="card">


        <!-- =================================================
             ALERTS
        ================================================= -->

        <?php if (!empty($errors)): ?>

            <div class="alert error">

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <?php if ($success !== ''): ?>

            <div class="alert success">

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             CURRENT PHOTO
        ================================================= -->

        <div class="profile-preview">

            <?php if (!empty($employee['photo'])): ?>

                <img
                    src="../<?= htmlspecialchars($employee['photo']) ?>"
                    class="profile-photo"
                    alt="Employee photo"
                >

            <?php else: ?>

                <div class="no-photo">
                    👤
                </div>

            <?php endif; ?>


            <div>

                <h2>
                    <?= htmlspecialchars($employee['name']) ?>
                </h2>

                <p style="color:#777; margin-top:6px;">
                    Employee ID #<?= (int)$employee['id'] ?>
                </p>

            </div>

        </div>


        <!-- =================================================
             FORM
        ================================================= -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($employee['name']) ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($employee['email']) ?>"
                        required
                    >

                </div>


                <!-- PREFIX -->

                <div class="form-group">

                    <label for="symbol_prefix">
                        Symbol Prefix
                    </label>

                    <input
                        type="text"
                        id="symbol_prefix"
                        name="symbol_prefix"
                        value="<?= htmlspecialchars($employee['symbol_prefix'] ?? '') ?>"
                    >

                </div>


                <!-- SYMBOL -->

                <div class="form-group">

                    <label for="symbol_no">
                        Symbol Number
                    </label>

                    <input
                        type="text"
                        id="symbol_no"
                        name="symbol_no"
                        value="<?= htmlspecialchars($employee['symbol_no'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- PHOTO -->

                <div class="form-group full">

                    <label for="photo">
                        Change Profile Photo
                    </label>

                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp"
                    >

                </div>


                <!-- VERIFICATION -->

                <div class="form-group full">

                    <div class="verify-box">

                        <input
                            type="checkbox"
                            id="is_verified"
                            name="is_verified"
                            value="1"
                            <?= ((int)$employee['is_verified'] === 1) ? 'checked' : '' ?>
                        >

                        <label for="is_verified">
                            Account is verified
                        </label>

                    </div>

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="save-btn"
                >
                    SAVE CHANGES
                </button>


                <a
                    href="employee_view.php?id=<?= $user_id ?>"
                    class="cancel-btn"
                >
                    CANCEL
                </a>

            </div>

        </form>

    </div>

</div>


</body>

</html>