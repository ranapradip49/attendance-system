<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location:index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>

<meta charset="UTF-8">

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


</style>


</head>



<body>



<div class="box">


<h1>
顔認証出勤
</h1>


<video 
id="video"
width="400"
height="300"
autoplay>
</video>


<div id="status">

顔を確認しています...

</div>


</div>





<script>


const video =
document.getElementById("video");


const status =
document.getElementById("status");





Promise.all([


faceapi.nets.faceRecognitionNet.loadFromUri(
'face-api.js-master/weights'
)

faceapi.nets.faceLandmark68Net.loadFromUri(
'face-api.js-master/weights'
)

faceapi.nets.ssdMobilenetv1.loadFromUri(
'face-api.js-master/weights'
)



]).then(startCamera);





function startCamera(){


navigator.mediaDevices
.getUserMedia({

video:true

})


.then(stream=>{

video.srcObject=stream;


startScan();


});


}






function startScan(){



setInterval(async()=>{



const detection =

await faceapi
.detectSingleFace(video)
.withFaceLandmarks()
.withFaceDescriptor();





if(!detection){


status.innerHTML=
"顔が見つかりません";


return;


}





status.innerHTML=
"確認中...";





let descriptor =

Array.from(
detection.descriptor
);





fetch("verify_face.php",{


method:"POST",


headers:{


"Content-Type":
"application/json"


},


body:JSON.stringify({

descriptor:descriptor

})



})


.then(res=>res.text())


.then(data=>{



if(data=="success"){


status.innerHTML=
"認証成功";


window.location=
"history.php";


}

else{


status.innerHTML=
"認証失敗";


}



});





},3000);



}



</script>


</body>

</html>