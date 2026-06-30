<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 300px;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Login</h2>

    <form action="login_check.php" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

</div>

</body>
</html>