<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Attendance Scanner</title>

<script src="https://unpkg.com/html5-qrcode"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#0f172a;
    color:white;
    font-family:Arial,sans-serif;
    height:100vh;
}

.container{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    height:100vh;
}

.company-name{
    font-size:48px;
    font-weight:bold;
    margin-bottom:20px;
}

.subtitle{
    font-size:24px;
    margin-bottom:30px;
}

#reader{
    width:450px;
    max-width:90%;
    background:white;
    border-radius:15px;
    padding:15px;
}

#status{
    margin-top:20px;
    font-size:22px;
    color:#facc15;
}

.cancel-btn{
    margin-top:30px;
    text-decoration:none;
    color:white;
    background:#dc2626;
    padding:15px 40px;
    border-radius:50px;
    font-size:20px;
}

.clock{
    position:absolute;
    right:30px;
    bottom:20px;
    font-size:22px;
}

</style>

</head>

<body>

<div class="container">

<div class="company-name">
ABC COMPANY
</div>

<div class="subtitle">
Please Scan Employee Card
</div>

<div id="reader"></div>

<div id="status">
Waiting for camera permission...
</div>

<a href="index.php" class="cancel-btn">
Cancel
</a>

<a href="action.php" class="action-btn">
        Open Action Page (Test)
    </a>

</div>

<div class="clock" id="clock"></div>

<script>

// Live Clock
function updateClock(){

    const now = new Date();

    document.getElementById("clock").innerHTML =
    now.toLocaleString();

}

setInterval(updateClock,1000);
updateClock();

const statusText =
document.getElementById("status");

let scanned = false;

function onScanSuccess(decodedText) {

    if (scanned) return;

    scanned = true;

    // alert("Scanned Data: " + decodedText);

    window.location.href = "action.php?code=" + encodeURIComponent(decodedText);

}

function onScanFailure(error){

}

const scanner =
new Html5QrcodeScanner(
    "reader",
    {
        fps:10,
        qrbox:{width:250,height:250},
        rememberLastUsedCamera:true
    }
);

async function startScanner(){

    try{

        await navigator.mediaDevices.getUserMedia({
            video:true
        });

        statusText.innerHTML =
        "Camera Ready. Scan QR or Barcode.";

        scanner.render(
            onScanSuccess,
            onScanFailure
        );

    }

    catch(error){

        statusText.innerHTML =
        "Camera permission denied. Please allow camera access.";

    }

}

startScanner();

</script>

</body>

</html>