<?php

session_start();

include "db/connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer-Master/src/Exception.php";
require "PHPMailer-Master/src/PHPMailer.php";
require "PHPMailer-Master/src/SMTP.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: user.php");
    exit();
}

// Get form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Basic validation
if (empty($name) || empty($email) || empty($password)) {
    die("Please fill all fields.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate OTP
$otp = rand(100000, 999999);

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Email Exists</title>
        <style>
            body{
                background:#111;
                color:white;
                font-family:Arial;
                display:flex;
                justify-content:center;
                align-items:center;
                height:100vh;
            }
            .box{
                background:#222;
                padding:40px;
                border-radius:15px;
                text-align:center;
            }
            a{
                color:#00ff99;
                text-decoration:none;
            }
        </style>
    </head>
    <body>
        <div class='box'>
            <h2>Email already registered!</h2>
            <br>
            <a href='register.php'>Go Back</a>
        </div>
    </body>
    </html>";
    exit();
}

// Insert user into database
$symbol_no = rand(1000,9999);

$symbol_prefix = "EMP";

$photo = "";


$stmt = $conn->prepare(
"INSERT INTO users
(name,email,password,otp,is_verified,symbol_no,symbol_prefix,photo)
VALUES(?,?,?,?,0,?,?,?)"
);


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

if(!$stmt->execute()){
    die("Database Error : ".$stmt->error);
}

// Send Email
$mail = new PHPMailer(true);

$mail->SMTPDebug = 2;

try {

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";

    $mail->SMTPAuth = true;


    // YOUR GMAIL ACCOUNT
    $mail->Username = "pradipranamagar49@gmail.com";


    // YOUR GOOGLE APP PASSWORD
    $mail->Password = "wsttkmsbubejgrbl";


    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;


    $mail->setFrom(
        "yourgmail@gmail.com",
        "Attendance System"
    );


    $mail->addAddress(
        $email,
        $name
    );


    $mail->isHTML(true);


    $mail->Subject = "Attendance System OTP Verification";


    $mail->Body = "

    <h2>Hello $name</h2>

    <p>Your OTP verification code is:</p>

    <h1 style='color:green;'>
        $otp
    </h1>

    <p>
        Enter this OTP to activate your account.
    </p>

    ";


    if($mail->send()){
    echo "Email sent successfully";
}else{
    echo "Email failed";
}


    $_SESSION['email']=$email;


}

catch(Exception $e){

    echo "Email sending failed: ";
    echo $mail->ErrorInfo;

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Registration Successful</title>

<style>

body{
    background:#111;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:Arial;
}

.container{

    width:450px;
    background:#222;
    padding:40px;
    border-radius:15px;
    text-align:center;

}

h2{

    color:#00ff99;

}

p{

    margin:20px 0;

}

a{

    display:inline-block;
    margin-top:20px;
    background:#00cc66;
    color:white;
    padding:12px 30px;
    border-radius:30px;
    text-decoration:none;

}

a:hover{

    background:#00994d;

}

</style>

</head>

<body>

<div class="container">

<h2>Registration Successful</h2>

<p>
An OTP has been sent to your registered email address.
</p>

<p>
Please check your Inbox or Spam folder.
</p>

<a href="verify.php">Verify Account</a>

</div>

</body>

</html>