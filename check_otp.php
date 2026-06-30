<?php
include "db/connect.php";
session_start();

$email = $_SESSION['email'];
$otp = $_POST['otp'];

$stmt = $conn->prepare("SELECT otp FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && $row['otp'] == $otp) {

    $update = $conn->prepare("UPDATE users SET is_verified=1 WHERE email=?");
    $update->bind_param("s", $email);
    $update->execute();

    echo "<h2>Account Verified Successfully</h2>";
    echo "<a href='login.php'>Go to Login</a>";

} else {
    echo "<h2>Invalid OTP</h2>";
    echo "<a href='verify.php'>Try Again</a>";
}
?>