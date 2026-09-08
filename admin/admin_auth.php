<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = (int) $_SESSION['admin_id'];

$admin_username = $_SESSION['admin_username'] ?? '';