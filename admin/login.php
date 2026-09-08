<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Login</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#050505;

    color:white;

    overflow:hidden;
}


/* ================================
   RGB BACKGROUND
================================ */

body::before{

    content:"";

    position:fixed;

    width:500px;
    height:500px;

    background:#00ffff;

    border-radius:50%;

    filter:blur(180px);

    opacity:.15;

    top:-150px;
    left:-150px;

    animation:move1 8s infinite alternate;
}


body::after{

    content:"";

    position:fixed;

    width:500px;
    height:500px;

    background:#ff00ff;

    border-radius:50%;

    filter:blur(180px);

    opacity:.15;

    bottom:-150px;
    right:-150px;

    animation:move2 8s infinite alternate;
}


@keyframes move1{

    from{
        transform:translate(0,0);
    }

    to{
        transform:translate(250px,200px);
    }

}


@keyframes move2{

    from{
        transform:translate(0,0);
    }

    to{
        transform:translate(-250px,-200px);
    }

}


/* ================================
   LOGIN BOX
================================ */

.login-box{

    position:relative;

    z-index:2;

    width:400px;

    padding:40px;

    background:rgba(0,0,0,.75);

    border:2px solid #00ffff;

    border-radius:25px;

    text-align:center;

    box-shadow:

        0 0 20px #00ffff,

        0 0 50px #ff00ff;

    backdrop-filter:blur(15px);
}


.logo{

    font-size:60px;

    margin-bottom:15px;

    color:#00ffff;

    text-shadow:

        0 0 10px cyan,

        0 0 30px cyan,

        0 0 50px blue;
}


h1{

    font-size:30px;

    margin-bottom:8px;

    text-shadow:

        0 0 10px cyan;
}


.subtitle{

    color:#aaa;

    margin-bottom:30px;

    font-size:14px;
}


/* ================================
   INPUT
================================ */

.input-group{

    text-align:left;

    margin-bottom:20px;
}


label{

    display:block;

    margin-bottom:8px;

    color:#00ffff;

    font-weight:bold;
}


input{

    width:100%;

    padding:14px;

    background:#0b0b0b;

    border:1px solid #00ffff;

    border-radius:10px;

    color:white;

    font-size:16px;

    outline:none;
}


input:focus{

    box-shadow:

        0 0 15px cyan;

}


/* ================================
   BUTTON
================================ */

button{

    width:100%;

    padding:15px;

    margin-top:10px;

    border:none;

    border-radius:50px;

    background:#00ffff;

    color:#000;

    font-size:18px;

    font-weight:bold;

    cursor:pointer;

    transition:.3s;

    box-shadow:

        0 0 15px cyan;
}


button:hover{

    transform:scale(1.05);

    box-shadow:

        0 0 20px cyan,

        0 0 40px cyan;
}


/* ================================
   BACK
================================ */

.back{

    display:block;

    margin-top:25px;

    color:#aaa;

    text-decoration:none;

    font-size:14px;
}


.back:hover{

    color:#00ffff;
}

</style>

</head>


<body>


<div class="login-box">

    <div class="logo">
        🔐
    </div>

    <h1>
        ADMIN LOGIN
    </h1>

    <div class="subtitle">
        YSE Attendance System
    </div>


    <form
        action="login_check.php"
        method="POST"
    >


        <div class="input-group">

            <label>
                Username
            </label>

            <input
                type="text"
                name="username"
                placeholder="Enter username"
                required
            >

        </div>


        <div class="input-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Enter password"
                required
            >

        </div>


        <button type="submit">

            LOGIN

        </button>


    </form>


    <a
        href="../index.php"
        class="back"
    >

        ← Back to Attendance System

    </a>


</div>


</body>

</html>