<?php
include "db/connect.php";
session_start();

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$otp = rand(100000, 999999);

// check email
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("Email already exists");
}

// insert user
$stmt = $conn->prepare("INSERT INTO users (name,email,password,otp,is_verified) VALUES (?,?,?,?,0)");
$stmt->bind_param("ssss", $name, $email, $password, $otp);
$stmt->execute();

$_SESSION['email'] = $email;

// TEST MODE OTP OUTPUT
echo "<h2>OTP Sent Successfully</h2>";
echo "<h1>Your OTP: $otp</h1>";
echo "<a href='verify.php'>Go to Verify Page</a>";
?>