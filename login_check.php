<?php
include "db/connect.php";

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT password,is_verified FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    if ($user['is_verified'] == 1) {
        echo "<h2>Login Successful</h2>";
    } else {
        echo "<h2>Please verify OTP first</h2>";
    }

} else {
    echo "<h2>Invalid Login</h2>";
}
?>