<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location:index.php");
    exit();
}

?>


<!DOCTYPE html>
<html>

<head>

<title>Face Registration</title>


<script src="face-api.js-master/dist/face-api.min.js"></script>


<style>

body{

background:#050505;
color:white;
font-family:Arial;
text-align:center;

}


video{

margin-top:30px;
border:3px solid cyan;
border-radius:20px;
box-shadow:
0 0 20px cyan;

}


button{

padding:15px 40px;
margin-top:20px;
border-radius:30px;
border:none;
background:#00ffff;
font-size:18px;

}


</style>


</head>


<body>


<h1>
FACE REGISTRATION
</h1>


<p>
Please show your face clearly
</p>


<video id="video"
width="400"
height="300"
autoplay>
</video>


<br>


<button onclick="captureFace()">
SAVE FACE
</button>


<script>
const video=document.getElementById("video");



Promise.all([

faceapi.nets.faceRecognitionNet.loadFromUri('face-api.js-master/weights'),

faceapi.nets.faceLandmark68Net.loadFromUri('face-api.js-master/weights'),

faceapi.nets.ssdMobilenetv1.loadFromUri('face-api.js-master/weights')
])
.then(()=>{

console.log("Models loaded");

startCamera();

})
.catch(error=>{

console.log(error);

alert("Face AI models failed to load");

});



function startCamera(){


navigator.mediaDevices.getUserMedia({

video:true

})

.then(stream=>{

video.srcObject=stream;

})
.catch(error=>{

console.log(error);

alert("Camera permission denied");

});
}

async function captureFace(){
    
const detection =
await faceapi
.detectSingleFace(video)
.withFaceLandmarks()
.withFaceDescriptor();



console.log("Checking face...");

if(!detection){

    console.log("No face found");

    alert("Face not detected");

    return;

}



let descriptor=
Array.from(detection.descriptor);



fetch("save_face.php",{

method:"POST",

headers:{

"Content-Type":"application/json"

},

body:JSON.stringify({

descriptor:descriptor

})


})
.then(response=>response.text())
.then(data=>{


alert(data);

window.location="action.php";


});

}



</script>


</body>

</html>