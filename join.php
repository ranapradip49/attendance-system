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
    body {
    margin: 0;
    padding: 0;
    background: black;
    color: white;
    font-family: Arial;
    text-align: center;
}

.container {
    padding: 20px;
}

.title {
    font-size: 60px;
    font-weight: bold;
    margin-top: 20px;
}

.logo img {
    width: 120px;
    height: 120px;
    margin-top: 10px;
}

.buttons button{
    display:block;
    width:200px;
    margin: 20px auto;
}

button {
    padding: 12px 25px;
    margin: 60px;
    width: 200px;
    border: none;
    cursor: pointer;
    font-size: 18px;
    border-radius: 8px;
    color: rgb(146, 160, 88);
}

button:hover {
    opacity: 0.8;
}

.form-box {
    margin-top: 20px;
    display: none;
}

input {
    display: block;
    margin: 10px auto;
    padding: 12px;
    width: 250px;
    border-radius: 5px;
    border: none;
}

.disclaimer {
    margin-top: 40px;
    font-size: 14px;
    color: lightgray;
    padding: 10px;
}

footer {
    margin-top: 40px;
    font-size: 16px;
}
</style>

</body>
</html>
 