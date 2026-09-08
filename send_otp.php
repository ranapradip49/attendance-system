<?php

session_start();

include "db/connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer-Master/src/Exception.php";
require "PHPMailer-Master/src/PHPMailer.php";
require "PHPMailer-Master/src/SMTP.php";


/* =========================================
   ONLY POST REQUEST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: register.php");
    exit();

}


/* =========================================
   GET FORM DATA
========================================= */

$name = trim($_POST['name'] ?? '');

$email = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';


/* =========================================
   VALIDATION
========================================= */

if (
    $name === '' ||
    $email === '' ||
    $password === ''
) {

    die("Please fill all fields.");

}


/* =========================================
   EMAIL VALIDATION
========================================= */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die("Please enter a valid email address.");

}


/* =========================================
   CHECK EXISTING EMAIL
========================================= */

$check = $conn->prepare("
    SELECT id, is_verified
    FROM users
    WHERE email = ?
    LIMIT 1
");

if (!$check) {

    die(
        "Database error: " .
        $conn->error
    );

}

$check->bind_param(
    "s",
    $email
);

$check->execute();

$result = $check->get_result();

$existing_user = $result->fetch_assoc();


/*
 * If already verified,
 * don't allow another account
 * with the same Gmail.
 */

if (
    $existing_user
    &&
    (int)$existing_user['is_verified'] === 1
) {

    echo "
    <!DOCTYPE html>

    <html>

    <head>

        <title>Email Already Registered</title>

        <style>

            body {

                background:#111;

                color:white;

                font-family:Arial;

                display:flex;

                justify-content:center;

                align-items:center;

                height:100vh;

                margin:0;

            }

            .box {

                background:#222;

                padding:40px;

                border-radius:15px;

                text-align:center;

                border:2px solid #00ffff;

                box-shadow:
                    0 0 20px
                    rgba(0,255,255,.3);

            }

            a {

                display:inline-block;

                margin-top:20px;

                color:#00ffff;

                text-decoration:none;

            }

        </style>

    </head>

    <body>

        <div class='box'>

            <h2>
                Email already registered!
            </h2>

            <p>
                This Gmail address is already verified.
            </p>

            <a href='register.php'>
                Go Back
            </a>

        </div>

    </body>

    </html>
    ";

    exit();

}


/* =========================================
   GENERATE OTP
========================================= */

$otp = random_int(
    100000,
    999999
);


/* =========================================
   HASH PASSWORD
========================================= */

$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


/* =========================================
   GENERATE EMPLOYEE NUMBER
========================================= */

$symbol_no = random_int(
    1000,
    9999
);

$symbol_prefix = "EMP";

$photo = "";


/* =========================================
   IF UNVERIFIED ACCOUNT EXISTS
   UPDATE IT INSTEAD OF CREATING DUPLICATE
========================================= */

if ($existing_user) {

    $user_id =
        (int)$existing_user['id'];


    $stmt = $conn->prepare("

        UPDATE users

        SET
            name = ?,
            password = ?,
            otp = ?,
            is_verified = 0

        WHERE id = ?

    ");


    if (!$stmt) {

        die(
            "Database error: " .
            $conn->error
        );

    }


    $stmt->bind_param(

        "sssi",

        $name,

        $hashedPassword,

        $otp,

        $user_id

    );


} else {


    /* =====================================
       CREATE NEW ACCOUNT
    ===================================== */

    $stmt = $conn->prepare("

        INSERT INTO users

        (
            name,
            email,
            password,
            otp,
            is_verified,
            symbol_no,
            symbol_prefix,
            photo
        )

        VALUES

        (
            ?,
            ?,
            ?,
            ?,
            0,
            ?,
            ?,
            ?
        )

    ");


    if (!$stmt) {

        die(
            "Database error: " .
            $conn->error
        );

    }


    $stmt->bind_param(

        "ssssiss",

        $name,

        $email,

        $hashedPassword,

        $otp,

        $symbol_no,

        $symbol_prefix,

        $photo

    );

}


/* =========================================
   SAVE USER
========================================= */

if (!$stmt->execute()) {

    die(
        "Database Error: " .
        $stmt->error
    );

}


/* =========================================
   SEND OTP EMAIL
========================================= */

$mail = new PHPMailer(true);


try {


    /* =====================================
       SMTP
    ===================================== */

    $mail->isSMTP();


    $mail->Host =
        "smtp.gmail.com";


    $mail->SMTPAuth =
        true;


    /*
     * YOUR SENDER GMAIL
     *
     * Use the Gmail account that
     * owns the App Password.
     */

    $sender_email =
        "pradipranamagar49@gmail.com";


    $mail->Username =
        $sender_email;


    /*
     * IMPORTANT:
     *
     * Put your NEW Google App Password here.
     *
     * Do NOT use your normal Gmail password.
     */

    $mail->Password =
        "xyon hahp vuxj mkir";


    /*
     * STARTTLS
     */

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;


    $mail->Port =
        587;


    /*
     * Character encoding
     */

    $mail->CharSet =
        "UTF-8";


    /*
     * Production mode:
     * don't show SMTP debugging
     */

    $mail->SMTPDebug = 0;


    /* =====================================
       FROM
    ===================================== */

    /*
     * IMPORTANT:
     *
     * The From address MUST be the
     * authenticated Gmail account.
     */

    $mail->setFrom(

        $sender_email,

        "YSE Attendance System"

    );


    /* =====================================
       RECIPIENT
    ===================================== */

    /*
     * THIS is the user's Gmail.
     *
     * It can be:
     *
     * abc@gmail.com
     * xyz@gmail.com
     * example@gmail.com
     *
     * etc.
     */

    $mail->addAddress(

        $email,

        $name

    );


    /* =====================================
       EMAIL FORMAT
    ===================================== */

    $mail->isHTML(true);


    $mail->Subject =
        "Attendance System - OTP Verification";


    /* =====================================
       EMAIL BODY
    ===================================== */

    $safe_name =
        htmlspecialchars(
            $name,
            ENT_QUOTES,
            "UTF-8"
        );


    $mail->Body = "

    <!DOCTYPE html>

    <html>

    <body style='
        margin:0;
        padding:0;
        background:#0b0f17;
        font-family:Arial,sans-serif;
    '>

        <div style='
            max-width:500px;
            margin:40px auto;
            background:#151b27;
            border-radius:15px;
            padding:35px;
            text-align:center;
            color:white;
        '>

            <h2 style='
                color:#00eaff;
            '>
                YSE Attendance System
            </h2>


            <p>
                Hello {$safe_name},
            </p>


            <p>
                Your OTP verification code is:
            </p>


            <div style='
                font-size:36px;
                font-weight:bold;
                letter-spacing:8px;
                color:#39ff14;
                margin:25px 0;
            '>

                {$otp}

            </div>


            <p style='
                color:#aaa;
            '>

                Enter this code on the
                verification page to
                activate your account.

            </p>


            <p style='
                color:#666;
                font-size:12px;
                margin-top:30px;
            '>

                If you did not request
                this registration, you can
                ignore this email.

            </p>

        </div>

    </body>

    </html>

    ";


    /* =====================================
       SEND
    ===================================== */

    $mail->send();


    /* =====================================
       SAVE EMAIL IN SESSION
    ===================================== */

    $_SESSION['email'] =
        $email;


    /*
     * Redirect to OTP page.
     */

    header(
        "Location: verify.php"
    );

    exit();


}


/* =========================================
   EMAIL ERROR
========================================= */

catch (Exception $e) {


    /*
     * If email could not be sent,
     * remove the newly created/unverified
     * account so the user can try again.
     */

    $delete = $conn->prepare("

        DELETE FROM users

        WHERE email = ?

        AND is_verified = 0

    ");


    if ($delete) {

        $delete->bind_param(
            "s",
            $email
        );

        $delete->execute();

    }


    echo "

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset='UTF-8'>

        <title>OTP Error</title>

        <style>

            body {

                margin:0;

                background:#050505;

                color:white;

                font-family:Arial;

                min-height:100vh;

                display:flex;

                align-items:center;

                justify-content:center;

            }

            .box {

                width:450px;

                max-width:90%;

                padding:35px;

                background:#151515;

                border:

                    2px solid

                    #ff0055;

                border-radius:20px;

                text-align:center;

                box-shadow:

                    0 0 25px

                    rgba(255,0,85,.3);

            }

            h2 {

                color:#ff0055;

            }

            .error {

                margin-top:20px;

                padding:15px;

                background:#0b0b0b;

                border-radius:10px;

                color:#ff7777;

                text-align:left;

                word-break:break-word;

            }

            a {

                display:inline-block;

                margin-top:20px;

                padding:12px 25px;

                color:#00ffff;

                border:

                    1px solid

                    #00ffff;

                border-radius:25px;

                text-decoration:none;

            }

        </style>

    </head>


    <body>

        <div class='box'>

            <h2>
                OTP could not be sent
            </h2>


            <p>
                The account was not created.
                Please check the email settings
                and try again.
            </p>


            <div class='error'>

                " .

                htmlspecialchars(
                    $mail->ErrorInfo
                )

                . "

            </div>


            <a href='register.php'>
                Try Again
            </a>

        </div>

    </body>

    </html>

    ";

}

?>