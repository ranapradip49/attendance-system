<?php

session_start();

include "db/connect.php";


/* =========================================
   GET VALUES
========================================= */

$symbol_no = $_GET['code'] ?? '';
$action    = $_GET['type'] ?? '';


/* =========================================
   VALIDATE
========================================= */

$allowed_actions = [
    "出勤",
    "休憩入り",
    "休憩戻り",
    "退勤"
];


if ($symbol_no === '') {
    exit("Symbol number is missing. Please select a user from action.php.");
}


if (!in_array($action, $allowed_actions, true)) {
    exit("Invalid action.");
}


/* =========================================
   FIND USER
========================================= */

$user_sql = $conn->prepare("
    SELECT id, symbol_no, name
    FROM users
    WHERE symbol_no = ?
    AND is_verified = 1
    LIMIT 1
");

$user_sql->bind_param(
    "s",
    $symbol_no
);

$user_sql->execute();

$result = $user_sql->get_result();

$user = $result->fetch_assoc();


if (!$user) {
    exit("User not found or not verified.");
}


/* =========================================
   STORE PENDING ATTENDANCE IN SESSION
========================================= */

$_SESSION['pending_attendance'] = [
    'user_id'   => $user['id'],
    'symbol_no' => $user['symbol_no'],
    'name'      => $user['name'],
    'action'    => $action
];


/* =========================================
   TITLE
========================================= */

switch ($action) {

    case "休憩入り":
        $title = "顔認証休憩入り";
        break;

    case "休憩戻り":
        $title = "顔認証休憩戻り";
        break;

    case "退勤":
        $title = "顔認証退勤";
        break;

    default:
        $title = "顔認証出勤";
}

?>


<!DOCTYPE html>

<html lang="ja">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Face Attendance Scan</title>

<script src="face-api.js-master/dist/face-api.min.js"></script>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial;
}


body{

    height:100vh;

    background:#050505;

    display:flex;

    justify-content:center;

    align-items:center;

    color:white;

}


.box{

    text-align:center;

    padding:40px;

    border-radius:25px;

    border:2px solid cyan;

    box-shadow:
        0 0 20px cyan,
        0 0 50px magenta;

    background:rgba(0,0,0,.8);

}


h1{

    margin-bottom:20px;

    text-shadow:
        0 0 20px cyan;

}


video{

    border-radius:20px;

    border:3px solid #00ffff;

    box-shadow:
        0 0 20px cyan;

}


#status{

    margin-top:20px;

    font-size:20px;

    color:#00ff99;

}


.back-btn{

    display:inline-block;

    margin-top:20px;

    padding:10px 20px;

    border:1px solid cyan;

    border-radius:10px;

    color:white;

    text-decoration:none;

}


</style>

</head>


<body>


<div class="box">


<h1>

<?= htmlspecialchars($title) ?>

</h1>


<video
    id="video"
    width="400"
    height="300"
    autoplay
    muted>
</video>


<div id="status">

顔を確認しています...

</div>


<a
    href="action.php"
    class="back-btn"
>
    ← 戻る
</a>


</div>


<script>


const video =
document.getElementById("video");


const status =
document.getElementById("status");


let successCount = 0;

let scanInterval;


/* =========================================
   LOAD FACE MODELS
========================================= */

Promise.all([

    faceapi.nets.faceRecognitionNet.loadFromUri(
        'face-api.js-master/weights'
    ),

    faceapi.nets.faceLandmark68Net.loadFromUri(
        'face-api.js-master/weights'
    ),

    faceapi.nets.ssdMobilenetv1.loadFromUri(
        'face-api.js-master/weights'
    )

]).then(startCamera);


/* =========================================
   CAMERA
========================================= */

function startCamera(){

    navigator.mediaDevices
    .getUserMedia({
        video:true
    })

    .then(stream => {

        video.srcObject = stream;

        startScan();

    })

    .catch(error => {

        status.innerHTML =
            "カメラを使用できません";

        console.error(error);

    });

}


/* =========================================
   FACE SCAN
========================================= */

function startScan(){

    scanInterval = setInterval(async () => {


        try {

            const detection =
                await faceapi
                .detectSingleFace(video)
                .withFaceLandmarks()
                .withFaceDescriptor();


            if(!detection){

                status.innerHTML =
                    "顔が見つかりません";

                return;

            }


            status.innerHTML =
                "確認中...";


            const descriptor =
                Array.from(
                    detection.descriptor
                );


            fetch("verify_face.php", {

                method:"POST",

                headers:{
                    "Content-Type":
                    "application/json"
                },

                body:JSON.stringify({

                    descriptor: descriptor,

                    symbol_no:
                    "<?= htmlspecialchars($symbol_no) ?>"

                })

            })


            .then(res => res.text())


            .then(data => {


                if(data.trim() === "success"){

                    successCount++;


                    status.innerHTML =
                        "認証成功 (" +
                        successCount +
                        "/2)";


                    if(successCount >= 2){

                        clearInterval(
                            scanInterval
                        );


                        status.innerHTML =
                            "認証完了";


                        /*
                         * IMPORTANT:
                         * Save attendance only
                         * after face verification.
                         */

                        window.location =
                            "save_attendance.php";

                    }


                }else{

                    successCount = 0;

                    status.innerHTML =
                        "認証失敗";

                }

            });


        }

        catch(error){

            console.error(error);

            status.innerHTML =
                "認証エラー";

        }


    },3000);

}

</script>


</body>

</html>