<?php

require_once "admin_auth.php";
include "../db/connect.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: employees.php");
    exit();
}

/* Get current employee status */
$stmt = $conn->prepare("
    SELECT id, name, is_active
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    header("Location: employees.php");
    exit();
}

/* Toggle status */
$new_status = $user['is_active'] ? 0 : 1;

$stmt = $conn->prepare("
    UPDATE users
    SET is_active = ?
    WHERE id = ?
");

$stmt->bind_param("ii", $new_status, $id);
$stmt->execute();

$stmt->close();
$conn->close();

/* Return to employee list */
header("Location: employees.php");
exit();
?>