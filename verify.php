<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: linear-gradient(135deg, #141e30, #243b55);
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
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #0b7dda;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Verify OTP</h2>

    <form action="check_otp.php" method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify</button>
    </form>

</div>

</body>
</html>