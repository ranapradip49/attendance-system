<?php

include "db/connect.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['descriptor'])) {
    exit("failed");
}

if (!isset($data['symbol_no'])) {
    exit("no_user");
}

$inputFace = $data['descriptor'];
$symbol_no = $data['symbol_no'];


// ------------------------------------
// Validate camera face descriptor
// ------------------------------------

if (!is_array($inputFace) || count($inputFace) != 128) {
    exit("camera_error");
}


// ------------------------------------
// Find selected user
// ------------------------------------

$stmt = $conn->prepare("
    SELECT id, name
    FROM users
    WHERE symbol_no = ?
    AND is_verified = 1
");

$stmt->bind_param("s", $symbol_no);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit("user_not_found");
}

$user = $result->fetch_assoc();

$user_id = $user['id'];


// ------------------------------------
// Get saved face
// ------------------------------------

$stmt = $conn->prepare("
    SELECT face_encoding
    FROM face_data
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit("no_face");
}

$row = $result->fetch_assoc();

$savedFace = json_decode($row['face_encoding'], true);


// ------------------------------------
// Validate saved face
// ------------------------------------

if (!is_array($savedFace) || count($savedFace) != 128) {
    exit("database_error");
}


// ------------------------------------
// Compare faces
// ------------------------------------

$distance = 0;

for ($i = 0; $i < 128; $i++) {

    $distance += pow(
        $inputFace[$i] - $savedFace[$i],
        2
    );

}

$distance = sqrt($distance);


// ------------------------------------
// Face recognition threshold
// ------------------------------------

$threshold = 0.6;


if ($distance <= $threshold) {

    echo "success";

} else {

    echo "failed";

}

?>