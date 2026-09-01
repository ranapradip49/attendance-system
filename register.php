<!DOCTYPE html>
<html>
<head>
    <title>Register</title>

    <style>

*{
    box-sizing:border-box;
}


body {

    margin:0;
    padding:0;

    font-family: 'Arial', sans-serif;

    background:#050505;

    height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    color:white;

}


/* Neon Box */

.box {

    width:350px;

    padding:35px;

    background:rgba(10,10,10,0.9);

    border-radius:20px;

    text-align:center;


    border:2px solid #00ffff;


    box-shadow:

    0 0 10px #00ffff,

    0 0 30px #00ffff,

    inset 0 0 20px rgba(0,255,255,0.2);


    animation: glow 2s infinite alternate;

}



@keyframes glow{

    from{

        box-shadow:

        0 0 10px cyan,

        0 0 30px cyan;

    }


    to{

        box-shadow:

        0 0 20px #ff00ff,

        0 0 50px #ff00ff;

    }

}



/* Title */

.title {

    font-size:30px;

    font-weight:bold;

    margin-bottom:25px;


    color:#00ffff;


    text-shadow:

    0 0 10px cyan,

    0 0 20px cyan;

}



.box h2{

    color:white;

}



/* Input */

.box input {


    width:100%;


    padding:14px;


    margin:10px 0;


    background:#111;


    color:white;


    border:none;


    border-radius:10px;


    outline:none;


    border:2px solid #333;


    font-size:16px;


    transition:0.3s;

}



.box input::placeholder{

    color:#aaa;

}



.box input:focus{


    border-color:#00ffff;


    box-shadow:

    0 0 10px cyan,

    0 0 20px cyan;


}



/* Button */

.box button {


    width:100%;


    padding:14px;


    margin-top:20px;


    background:black;


    color:#00ffff;


    border:2px solid #00ffff;


    border-radius:30px;


    font-size:18px;


    font-weight:bold;


    cursor:pointer;


    transition:0.3s;


    text-transform:uppercase;


    box-shadow:

    0 0 10px cyan;



}



.box button:hover{


    background:#00ffff;


    color:black;


    box-shadow:

    0 0 20px cyan,

    0 0 40px cyan;


    transform:scale(1.05);


}



</style>
</head>

<body>

<div class="box">

    <div class="title">Create Account</div>

    <form action="send_otp.php" method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Register</button>
    </form>

</div>

</body>
</html>