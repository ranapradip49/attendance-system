<?php

session_start();

include "../db/connect.php";


/* =====================================
   ONLY POST REQUEST
===================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit();

}


/* =====================================
   GET FORM DATA
===================================== */

$username = trim(
    $_POST['username'] ?? ''
);

$password = $_POST['password'] ?? '';


/* =====================================
   VALIDATION
===================================== */

if ($username === '' || $password === '') {

    exit("Username and password are required.");

}


/* =====================================
   FIND ADMIN
===================================== */

$stmt = $conn->prepare("

    SELECT
        id,
        username,
        password

    FROM admins

    WHERE username = ?

    LIMIT 1

");


$stmt->bind_param(
    "s",
    $username
);


$stmt->execute();


$result = $stmt->get_result();


$admin = $result->fetch_assoc();


/* =====================================
   CHECK LOGIN
===================================== */

if (
    $admin &&
    password_verify(
        $password,
        $admin['password']
    )
) {


    /* =================================
       CREATE ADMIN SESSION
    ================================= */

    $_SESSION['admin_id'] =
        $admin['id'];

    $_SESSION['admin_username'] =
        $admin['username'];


    /* =================================
       REDIRECT DASHBOARD
    ================================= */

    header(
        "Location: dashboard.php"
    );

    exit();

}


/* =====================================
   LOGIN FAILED
===================================== */

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Login Failed</title>

<style>

body{

    background:#050505;

    color:white;

    height:100vh;

    display:flex;

    align-items:center;

    justify-content:center;

    font-family:Arial;

}

.box{

    width:400px;

    padding:40px;

    text-align:center;

    background:#111;

    border:2px solid #ff0055;

    border-radius:20px;

    box-shadow:

        0 0 20px #ff0055;

}

h2{

    color:#ff0055;

}

a{

    display:inline-block;

    margin-top:20px;

    padding:12px 30px;

    border:1px solid cyan;

    border-radius:30px;

    color:cyan;

    text-decoration:none;

}

a:hover{

    background:cyan;

    color:black;

}

</style>

</head>

<body>

<div class="box">

    <h2>
        Invalid Admin Login
    </h2>

    <p>
        Username or password is incorrect.
    </p>

    <a href="login.php">
        Try Again
    </a>

</div>

</body>

</html>