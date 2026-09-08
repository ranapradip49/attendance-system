<?php

include "../db/connect.php";

$username = "admin";
$password = "admin123";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "UPDATE admins SET password = ? WHERE username = ?"
);

$stmt->bind_param(
    "ss",
    $hashed_password,
    $username
);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {
        echo "Admin password reset successfully!<br><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br><br>";
        echo '<a href="login.php">Go to Admin Login</a>';
    } else {
        echo "Admin username not found.";
    }

} else {

    echo "Error: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>