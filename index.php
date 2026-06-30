<!DOCTYPE html>
<html>

<head>

<title>Attendance System</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="overlay">

<h1>Attendance System</h1>

<h2>Scan your QR Code
    to mark your attendance.
</h2>

<h3>Touch Anywhere to Continue</h3>

</div>

<div id="address">
Tokyo, Japan
</div>

<div id="clock"></div>

<script>

function updateClock(){

const now = new Date();

document.getElementById("clock").innerHTML =
now.toLocaleString();

}

setInterval(updateClock,1000);

updateClock();

document.addEventListener("click",function(){

window.location.href="join.php";

});

</script>

</body>

</html>