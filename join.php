<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HELP BANK</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <!-- TITLE -->
    <h1 class="title">ATTENDANCE SYSTEM</h1>

    <!-- LOGO -->
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>

    <!-- BUTTONS -->
<div class="buttons">
    <button onclick="goToUser()">SIGN IN</button>
    <button onclick="goToLogin()">LOG IN</button>
</div>

    <!-- DISCLAIMER -->
    <p class="disclaimer">
        Disclaimer: This platform is for authorized users only.
    </p>

    <!-- FOOTER -->
    <footer>
        © 2026 ATTENDANCE SYSTEM. All rights reserved.
    </footer>

</div>

<script>
function goToUser() {
    window.location.href = "user.php";
}

function goToLogin() {
    window.location.href = "scan.php";
}

function toggleForm() {
    var form = document.getElementById("signupForm");

    if (form.style.display === "block") {
        form.style.display = "none";
    } else {
        form.style.display = "block";
    }
}

function createProfile() {
    let email = document.getElementById("email").value;

    let password = document.getElementById("password").value;
    let confirm = document.getElementById("confirmPassword").value;

    if (!email || !country || !password || !confirm) {
        alert("Please fill all fields");
        return;
    }

    if (password !== confirm) {
        alert("Passwords do not match!");
        return;
    }

    alert("Profile Created Successfully!");

    // redirect after signup
    window.location.href = "user.php";
}
</script>

<style>
/* Google Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    background:#050505;
    overflow:hidden;
}

/* Animated RGB Background */
body::before{
    content:"";
    position:fixed;
    width:500px;
    height:500px;
    background:#00ffff;
    border-radius:50%;
    filter:blur(180px);
    animation:move1 8s infinite alternate;
}

body::after{
    content:"";
    position:fixed;
    width:450px;
    height:450px;
    background:#ff00ff;
    border-radius:50%;
    filter:blur(180px);
    animation:move2 10s infinite alternate;
}

@keyframes move1{
    from{
        top:-100px;
        left:-100px;
    }
    to{
        top:250px;
        left:350px;
    }
}

@keyframes move2{
    from{
        bottom:-100px;
        right:-100px;
    }
    to{
        bottom:250px;
        right:350px;
    }
}

.container{
    position:relative;
    z-index:2;
    width:420px;
    padding:40px;
    text-align:center;
    background:rgba(0,0,0,.65);
    backdrop-filter:blur(20px);
    border-radius:25px;
    border:2px solid rgba(255,255,255,.15);
    box-shadow:0 0 50px cyan;
}

.title{
    color:white;
    font-size:40px;
    margin-bottom:25px;
    text-shadow:
        0 0 10px cyan,
        0 0 20px cyan,
        0 0 40px cyan;
}

.logo img{
    width:150px;
    height:150px;
    border-radius:50%;
    border:4px solid cyan;
    object-fit:cover;
    margin-bottom:35px;
    animation:spinBorder 5s linear infinite;
    box-shadow:
        0 0 20px cyan,
        0 0 40px blue,
        0 0 60px magenta;
}

@keyframes spinBorder{
    100%{
        transform:rotate(360deg);
    }
}

/* Buttons Up and Down */
.buttons{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:25px;
}

/* RGB Button */
.buttons button{
    width:260px;
    padding:16px;
    border:none;
    outline:none;
    color:white;
    font-size:18px;
    font-weight:bold;
    letter-spacing:2px;
    border-radius:50px;
    cursor:pointer;
    position:relative;
    background:#111;
    overflow:hidden;
    transition:.4s;
}

.buttons button::before{
    content:"";
    position:absolute;
    top:-2px;
    left:-2px;
    width:calc(100% + 4px);
    height:calc(100% + 4px);

    background:linear-gradient(
        45deg,
        red,
        orange,
        yellow,
        lime,
        cyan,
        blue,
        violet,
        red
    );

    background-size:400%;
    z-index:-1;
    border-radius:50px;
    animation:rgb 5s linear infinite;
}

.buttons button::after{
    content:"";
    position:absolute;
    inset:3px;
    background:#111;
    border-radius:50px;
    z-index:-1;
}

@keyframes rgb{
    0%{
        background-position:0%;
    }
    100%{
        background-position:400%;
    }
}

.buttons button:hover{
    transform:scale(1.08);
    box-shadow:
        0 0 20px cyan,
        0 0 40px magenta,
        0 0 60px lime;
}

.disclaimer{
    margin-top:35px;
    color:#ddd;
    font-size:14px;
    line-height:1.6;
}

footer{
    margin-top:25px;
    color:#aaa;
    font-size:13px;
}

</style>

</body>
</html>
 