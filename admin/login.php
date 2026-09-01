<?php

session_start();

include "../db/connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === "" || $password === "") {

        $error = "Please enter username and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT id, username, password
            FROM admins
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                header("Location: dashboard.php");
                exit();

            } else {

                $error = "Incorrect password.";

            }

        } else {

            $error = "Admin account not found.";

        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{

    min-height:100vh;

    background:#050505;

    display:flex;

    justify-content:center;

    align-items:center;

    color:white;

}

.login-box{

    width:400px;

    padding:40px;

    background:#0d0e15;

    border:2px solid #00ffff;

    border-radius:25px;

    box-shadow:
        0 0 20px #00ffff,
        0 0 50px rgba(255,0,255,.3);

}

h1{

    text-align:center;

    margin-bottom:10px;

    color:#00ffff;

    text-shadow:
        0 0 10px cyan,
        0 0 25px cyan;

}

.subtitle{

    text-align:center;

    color:#aaa;

    margin-bottom:30px;

}

label{

    display:block;

    margin-bottom:8px;

    color:#00ffff;

}

input{

    width:100%;

    padding:14px;

    margin-bottom:20px;

    border:1px solid #00ffff;

    border-radius:10px;

    background:#050505;

    color:white;

    outline:none;

    font-size:16px;

}

input:focus{

    box-shadow:0 0 15px #00ffff;

}

button{

    width:100%;

    padding:15px;

    border:2px solid #00ff99;

    border-radius:30px;

    background:transparent;

    color:#00ff99;

    font-size:18px;

    font-weight:bold;

    cursor:pointer;

    transition:.3s;

}

button:hover{

    background:#00ff99;

    color:#000;

    box-shadow:0 0 25px #00ff99;

}

.error{

    margin-bottom:20px;

    padding:12px;

    text-align:center;

    border:1px solid #ff0055;

    border-radius:10px;

    color:#ff0055;

    background:rgba(255,0,85,.1);

}

.back{

    display:block;

    text-align:center;

    margin-top:25px;

    color:#aaa;

    text-decoration:none;

}

.back:hover{

    color:#00ffff;

}

</style>

</head>

<body>

<div class="login-box">

    <h1>ADMIN LOGIN</h1>

    <div class="subtitle">
        Attendance Management System
    </div>

    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter admin username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            required
        >

        <button type="submit">
            LOGIN
        </button>

    </form>

    <a href="index.php" class="back">
        ← Back to Attendance System
    </a>

</div>

</body>

</html>