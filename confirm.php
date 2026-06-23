<?php

$code = $_GET['code'];
$type = $_GET['type'];

$time = date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Confirm</title>

<style>

body{
    background:#111;
    color:white;
    text-align:center;
    font-family:Arial;
}

.box{
    margin-top:100px;
}

.btn{
    padding:15px 40px;
    border-radius:50px;
    background:white;
    color:black;
    text-decoration:none;
    margin:10px;
}

</style>

</head>

<body>

<div class="box">

<h2><?php echo $type; ?> ボタンを押しました</h2>

<p>時刻</p>

<h3><?php echo $time; ?></h3>

<a class="btn"
href="save_attendance.php?code=<?php echo urlencode($code); ?>&type=<?php echo urlencode($type); ?>">
確認
</a>

<a class="btn"
href="action.php?code=<?php echo urlencode($code); ?>">
戻る
</a>

</div>

</body>
</html>