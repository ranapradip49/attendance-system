<?php

session_start();

include "db/connect.php";


$otp = $_POST['otp'];

$email = $_SESSION['email'];


// Check OTP

$sql = $conn->prepare(
"SELECT id FROM users 
WHERE email=? AND otp=?"
);

$sql->bind_param(
"ss",
$email,
$otp
);

$sql->execute();

$result = $sql->get_result();



if($result->num_rows > 0){


    $user = $result->fetch_assoc();

    $user_id = $user['id'];



    // verify account

    // verify account

$update = $conn->prepare(
"UPDATE users 
SET is_verified=1 
WHERE id=?"
);

$update->bind_param(
"i",
$user_id
);

$update->execute();


// login session

$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;


// go to face registration

header("Location: scan_face.php");
exit();

//     // create profile

//     $prefix = "USR";


// // generate symbol number
// $symbol_no = rand(1000,9999);


// $profile = $conn->prepare(
// "INSERT INTO profiles(user_id, symbol_prefix, symbol_no)
//  VALUES(?,?,?)"
// );


// $profile->bind_param(
// "isi",
// $user_id,
// $prefix,
// $symbol_no
// );


//     if(!$profile->execute()){

//     echo $profile->error;

//     exit();

// }
}

else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OTP Verification Failed</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#050505;
    overflow:hidden;
}

/* Animated Background */

body::before{
    content:"";
    position:absolute;
    width:500px;
    height:500px;
    background:#00f7ff;
    filter:blur(180px);
    opacity:.15;
    top:-120px;
    left:-120px;
    animation:move1 8s infinite alternate;
}

body::after{
    content:"";
    position:absolute;
    width:500px;
    height:500px;
    background:#ff00ff;
    filter:blur(180px);
    opacity:.15;
    bottom:-120px;
    right:-120px;
    animation:move2 8s infinite alternate;
}

@keyframes move1{
    from{transform:translate(0,0);}
    to{transform:translate(80px,80px);}
}

@keyframes move2{
    from{transform:translate(0,0);}
    to{transform:translate(-80px,-80px);}
}

/* Card */

.box{
    position:relative;
    z-index:10;
    width:420px;
    padding:40px;
    border-radius:20px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);
    backdrop-filter:blur(15px);
    text-align:center;
    box-shadow:
    0 0 15px #00ffff,
    0 0 35px #00ffff;
}

.icon{
    font-size:70px;
    color:#ff4444;
    text-shadow:
    0 0 10px #ff0000,
    0 0 25px #ff0000;
    margin-bottom:20px;
}

h2{
    color:#ffffff;
    margin-bottom:15px;
    text-shadow:0 0 12px #00ffff;
}

p{
    color:#bbbbbb;
    margin-bottom:30px;
}

a{
    display:inline-block;
    padding:14px 35px;
    color:#00ffff;
    text-decoration:none;
    border:2px solid #00ffff;
    border-radius:50px;
    font-size:18px;
    font-weight:bold;
    transition:.4s;
    box-shadow:0 0 10px #00ffff;
}

a:hover{
    background:#00ffff;
    color:#000;
    box-shadow:
    0 0 20px #00ffff,
    0 0 50px #00ffff;
    transform:scale(1.05);
}

</style>

</head>

<body>

<div class="box">

<div class="icon">✖</div>

<h2>OTPが違います</h2>

<p>Please enter the correct verification code.</p>

<a href="verify.php">もう一度</a>

</div>

</body>
</html>
<?php
}
?>