<?php

$employee_no = "EMP0001";

$qr =
"https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
. urlencode($employee_no);

?>

<!DOCTYPE html>

<html>

<head>

<title>Employee QR Code</title>

<style>

body{

    font-family:Arial;
    text-align:center;
    background:#f5f5f5;

}

.card{

    width:400px;

    margin:40px auto;

    background:white;

    padding:30px;

    border-radius:15px;

    box-shadow:0 0 10px gray;

}

img{

    width:250px;

}

</style>

</head>

<body>

<div class="card">

<h2>ABC COMPANY</h2>

<h3>Employee Card</h3>

<p><strong>Employee No:</strong> <?php echo $employee_no; ?></p>

<img src="<?php echo $qr; ?>">

</div>

</body>

</html>