<!DOCTYPE html>
<html lang="ja">

<head>

<meta charset="UTF-8">

<title>OTP認証</title>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial;
}


body{

    height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#050505;

    overflow:hidden;

}


/* RGB light */

body::before{

    content:"";

    position:absolute;

    width:400px;

    height:400px;

    background:cyan;

    filter:blur(180px);

    top:-100px;

    left:-100px;

}


body::after{

    content:"";

    position:absolute;

    width:400px;

    height:400px;

    background:magenta;

    filter:blur(180px);

    bottom:-100px;

    right:-100px;

}



.box{

    position:relative;

    z-index:2;

    width:350px;

    padding:40px;

    background:rgba(0,0,0,.8);

    border-radius:25px;

    border:2px solid cyan;

    text-align:center;


    box-shadow:

    0 0 20px cyan,

    0 0 50px magenta;

}



h2{

    color:white;

    margin-bottom:30px;

    text-shadow:

    0 0 20px cyan;

}



input{

    width:100%;

    padding:15px;

    background:black;

    color:white;

    border:2px solid cyan;

    border-radius:10px;

    outline:none;

}



input:focus{

    box-shadow:

    0 0 20px cyan;

}



button{

    width:100%;

    margin-top:25px;

    padding:15px;

    border:none;

    border-radius:50px;

    background:#00ff99;

    font-size:18px;

    font-weight:bold;

    cursor:pointer;

    box-shadow:

    0 0 20px #00ff99;

}



button:hover{

    transform:scale(1.05);

}


</style>


</head>


<body>


<div class="box">


<h2>
OTP認証
</h2>


<form action="check_otp.php" method="POST">


<input 
type="text"
name="otp"
placeholder="OTPを入力"
required>


<button>
確認する
</button>


</form>


</div>


</body>

</html>