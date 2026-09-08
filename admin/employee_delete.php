<?php

require_once "admin_auth.php";
require_once "../db/connect.php";

/*
=========================================================
VALIDATE EMPLOYEE ID
=========================================================
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: employees.php");
    exit;
}


/*
=========================================================
GET EMPLOYEE
=========================================================
*/

$stmt = $conn->prepare("
    SELECT id, name, photo
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    header("Location: employees.php");
    exit;
}


/*
=========================================================
DELETE EMPLOYEE
=========================================================
*/

$conn->begin_transaction();

try {

    /*
    -----------------------------------------------------
    DELETE FACE DATA
    -----------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM face_data
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();


    /*
    -----------------------------------------------------
    DELETE ATTENDANCE HISTORY
    -----------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM attendance
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();


    /*
    -----------------------------------------------------
    DELETE USER
    -----------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM users
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();


    /*
    -----------------------------------------------------
    DELETE EMPLOYEE PHOTO FROM UPLOADS
    -----------------------------------------------------
    */

    if (!empty($employee['photo'])) {

        $photo = $employee['photo'];

        // Remove leading slash if present
        $photo = ltrim($photo, '/');

        $photo_path = dirname(__DIR__) . "/" . $photo;

        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }


    /*
    -----------------------------------------------------
    COMMIT
    -----------------------------------------------------
    */

    $conn->commit();

    header("Location: employees.php?deleted=1");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    die(
        "Employee could not be deleted.<br><br>" .
        htmlspecialchars($e->getMessage())
    );
}