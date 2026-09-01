descriptor:descriptor

<?php

session_start();

include "db/connect.php";


$data=json_decode(
file_get_contents("php://input"),
true
);


$inputFace=$data['descriptor'];

$user_id=$_SESSION['user_id'];



$sql=$conn->prepare(

"SELECT face_encoding 
FROM face_data
WHERE user_id=?"

);


$sql->bind_param(
"i",
$user_id
);


$sql->execute();


$result=$sql->get_result();


if($result->num_rows==0){

echo "no_face";

exit();

}


$row=$result->fetch_assoc();



$savedFace=json_decode(
$row['face_encoding']
);



$distance=0;


for($i=0;$i<count($inputFace);$i++){

$distance += 
pow(
$inputFace[$i]-$savedFace[$i],
2
);

}


$distance=sqrt($distance);



if($distance < 0.6){

echo "success";

}

else{

echo "failed";

}


?>