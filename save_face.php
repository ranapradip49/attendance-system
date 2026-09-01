<?php

session_start();

include "db/connect.php";


// Check login

if(!isset($_SESSION['user_id'])){

    echo "User not logged in";
    exit();

}



$user_id = $_SESSION['user_id'];



// Receive JSON data

$data = json_decode(
    file_get_contents("php://input"),
    true
);



if(!isset($data['descriptor'])){

    echo "Face data missing";
    exit();

}



$descriptor = $data['descriptor'];



// Convert array into string

$face_encoding = json_encode($descriptor);





// Check if face already registered

$check = $conn->prepare(
"SELECT id FROM face_data WHERE user_id=?"
);


$check->bind_param(
"i",
$user_id
);


$check->execute();


$result=$check->get_result();



if($result->num_rows > 0){


    // Update existing face


    $update=$conn->prepare(
    "UPDATE face_data 
     SET face_encoding=? 
     WHERE user_id=?"
    );


    $update->bind_param(
    "si",
    $face_encoding,
    $user_id
    );


    $update->execute();



    echo "Face updated successfully";



}

else{


    // Save new face


    $insert=$conn->prepare(
    "INSERT INTO face_data
    (user_id,face_encoding)
    VALUES(?,?)"
    );



    $insert->bind_param(
    "is",
    $user_id,
    $face_encoding
    );



    if($insert->execute()){


        echo "Face registered successfully";


    }

    else{


        echo "Database error";


    }



}



?>