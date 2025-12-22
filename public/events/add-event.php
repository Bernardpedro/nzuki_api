<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}

require __DIR__ . "/../../middleware/auth.php";

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get raw JSON input
$json = file_get_contents("php://input");

if (!$json) {
    die("No JSON received");
}

// Decode JSON to associative array
$data = json_decode($json, true);

if (!$data) {
    die("Invalid JSON");
}

// Prepare values
$title        = mysqli_real_escape_string($conn, $data['title']);
$description  = mysqli_real_escape_string($conn, $data['description']);
$date         = $data['date'];
$time         = date("Y-m-d H:i:s", strtotime($data['time']));
// $time      = date("Y-m-d H:i:s", strtotime($data['time']));
$location     = mysqli_real_escape_string($conn, $data['location']);
$type         = mysqli_real_escape_string($conn, $data['type']);
$status       = mysqli_real_escape_string($conn, $data['status']);
$organizer    = mysqli_real_escape_string($conn, $data['organizer']);
$youtubeLink  = mysqli_real_escape_string($conn, $data['youtubeLink']);
$image        = mysqli_real_escape_string($conn, $data['image']);
$imageType    = mysqli_real_escape_string($conn, $data['imageType']);
// $images       = mysqli_real_escape_string($conn, json_encode($data['images']));
$images = json_encode($data['images']);






// Insert query
$sql = "
INSERT INTO events (
    title, description, date, time, location,
    type, status, organizer, youtubeLink, image, imageType, images
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "ssssssssssss",
    $data['title'],
    $data['description'],
    $data['date'],
    $time,
    // date("Y-m-d H:i:s", strtotime($data['time'])),
    $data['location'],
    $data['type'],
    $data['status'],
    $data['organizer'],
    $data['youtubeLink'],
    $data['image'],
    $data['imageType'],
    $images
    // json_encode($data['images'])
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Event saved successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
