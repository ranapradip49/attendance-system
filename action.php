

<!DOCTYPE html>
<html>
<head>

<title>Attendance Action</title>

<style>
body {
    background: #222;
    color: white;
    text-align: center;
    font-family: Arial;
}

.btn {
    display: block;
    margin: 20px auto;
    width: 250px;
    padding: 15px;
    border-radius: 50px;
    background: white;
    color: black;
    text-decoration: none;
    font-size: 18px;
}
</style>

</head>

<body>



<p><?php echo date("Y-m-d H:i:s"); ?></p>

<a class="btn"
href="confirm.php?code=&type=出勤">出勤</a>

<a class="btn"
href="confirm.php?code=&type=休憩入り">休憩入り</a>

<a class="btn"
href="confirm.php?code=&type=休憩戻り">休憩戻り</a>

<a class="btn"
href="confirm.php?code=&type=退勤">退勤</a>

</body>
</html>