```php
<?php

session_start();

include "db/connect.php";


/* =========================================
   CHECK PENDING ATTENDANCE
========================================= */

if (
    !isset(
        $_SESSION['pending_attendance']
    )
) {

    exit(
        "No attendance data found."
    );

}


$data =
    $_SESSION['pending_attendance'];


/* =========================================
   GET SESSION DATA
========================================= */

$user_id =
    (int)(
        $data['user_id']
        ?? 0
    );


$symbol_no =
    $data['symbol_no']
    ?? '';


$name =
    $data['name']
    ?? '';


$action =
    $data['action']
    ?? '';


/* =========================================
   VALIDATE
========================================= */

$allowed_actions = [

    "出勤",

    "休憩入り",

    "休憩戻り",

    "退勤"

];


if ($user_id <= 0) {

    exit(
        "Invalid user."
    );

}


if (
    $symbol_no === ''
) {

    exit(
        "Symbol number is missing."
    );

}


if (
    $name === ''
) {

    exit(
        "Employee name is missing."
    );

}


if (
    !in_array(
        $action,
        $allowed_actions,
        true
    )
) {

    exit(
        "Invalid attendance action."
    );

}


/* =========================================
   GET EMPLOYEE FROM DATABASE
========================================= */

$user_sql = $conn->prepare("

    SELECT

        id,

        symbol_no,

        name,

        photo

    FROM users

    WHERE id = ?

    AND is_verified = 1

    AND is_active = 1

    LIMIT 1

");


if (!$user_sql) {

    exit(
        "Database error: "
        . $conn->error
    );

}


$user_sql->bind_param(
    "i",
    $user_id
);


if (
    !$user_sql->execute()
) {

    exit(
        "Failed to find employee."
    );

}


$user_result =
    $user_sql->get_result();


$user =
    $user_result->fetch_assoc();


if (!$user) {

    exit(
        "Employee not found, inactive, " .
        "or not verified."
    );

}


/* =========================================
   GET PHOTO
========================================= */

/*
 * IMPORTANT:
 *
 * The users table already stores
 * the complete relative path:
 *
 * uploads/employee_38_xxx.jpg
 *
 * Therefore we save this exact value
 * into attendance.photo.
 */

$photo =
    trim(
        $user['photo'] ?? ''
    );


/*
 * Convert empty/default photo to NULL.
 *
 * This is optional, but keeps the database
 * clean when the employee has no photo.
 */

if (
    $photo === ''
    ||
    $photo === 'default.png'
) {

    $photo = null;

}


/* =========================================
   USE DATABASE VALUES
========================================= */

/*
 * Do not blindly trust the values stored
 * in the session.
 *
 * Get the latest employee information
 * directly from the database.
 */

$symbol_no =
    $user['symbol_no'];

$name =
    $user['name'];


/* =========================================
   SAVE ATTENDANCE
========================================= */

$attendance_sql = $conn->prepare("

    INSERT INTO attendance

    (
        user_id,
        name,
        symbol_no,
        photo,
        action,
        date_time
    )

    VALUES

    (
        ?,
        ?,
        ?,
        ?,
        ?,
        NOW()
    )

");


if (!$attendance_sql) {

    exit(
        "Failed to prepare attendance " .
        "query: " .
        $conn->error
    );

}


/*
 * Types:
 *
 * i = user_id
 * s = name
 * s = symbol_no
 * s = photo
 * s = action
 */

$attendance_sql->bind_param(

    "issss",

    $user_id,

    $name,

    $symbol_no,

    $photo,

    $action

);


/* =========================================
   EXECUTE
========================================= */

if (
    !$attendance_sql->execute()
) {

    exit(

        "Failed to save attendance: "
        .
        $attendance_sql->error

    );

}


/* =========================================
   SET USER SESSION
========================================= */

$_SESSION['user_id'] =
    $user_id;


/* =========================================
   REMOVE PENDING DATA
========================================= */

unset(
    $_SESSION['pending_attendance']
);


/* =========================================
   REDIRECT
========================================= */

header(
    "Location: history.php"
);

exit;

?>
```
