<!DOCTYPE html>
<html>
<head>
    <title>Register</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #2c3e50);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background: #ffffff;
            padding: 30px;
            width: 320px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        .box h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .box input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
        }

        .box input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.5);
        }

        .box button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .box button:hover {
            background: #45a049;
        }

        .title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #222;
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