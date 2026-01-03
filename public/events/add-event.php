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

include("../connection.php");


// Prepare values

$title        = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$description  = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$date         = $_POST['date'] ?? null;
$time = $_POST['time'] ?? '';
$location     = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
$type         = mysqli_real_escape_string($conn, $_POST['type'] ?? '');
$status       = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
$organizer    = mysqli_real_escape_string($conn, $_POST['organizer'] ?? '');
$youtubeLink  = mysqli_real_escape_string($conn, $_POST['youtubeLink'] ?? '');


// Handle multiple images

$imagesArray = [];

if (isset($_FILES['images'])) {

    $uploadDir = __DIR__ . "/../../uploads/events/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        
        if ($_FILES['images']['error'][$index] !== 0) {
            continue;
        }

        $mimeType = mime_content_type($tmpName);
        $extension = pathinfo($_FILES['images']['name'][$index], PATHINFO_EXTENSION);

        $fileName = uniqid("event_gallery_", true) . "." . $extension;
        $filePath = $uploadDir . $fileName;

         if (move_uploaded_file($tmpName, $filePath)) {
             $imagesArray[] = "uploads/events/" . $fileName;
         }
    }
}

$images = json_encode($imagesArray);

// Handle multiple videos
$videosArray = [];

if (isset($_FILES['videos'])) {

    $videoUploadDir = __DIR__ . "/../../uploads/events/videos/";
    if (!is_dir($videoUploadDir)) {
        mkdir($videoUploadDir, 0755, true);
    }

    foreach ($_FILES['videos']['tmp_name'] as $index => $tmpName) {

        if ($_FILES['videos']['error'][$index] !== 0) {
            continue;
        }

        // Validate MIME type
        $allowedVideos = ['video/mp4', 'video/webm', 'video/ogg'];
        $mimeType = mime_content_type($tmpName);

        if (!in_array($mimeType, $allowedVideos)) {
            continue;
        }

        $extension = pathinfo($_FILES['videos']['name'][$index], PATHINFO_EXTENSION);
        $fileName = uniqid("event_video_", true) . "." . $extension;
        $filePath = $videoUploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            $videosArray[] = "uploads/events/videos/" . $fileName;
        }
    }
}

$videos = json_encode($videosArray);


// Insert query
$sql = "
INSERT INTO events (
    title, description, date, time, location,
    type, status, organizer, youtubeLink, images, videos
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(

    $stmt,
    "sssssssssss",
    $title,
    $description,
    $date,
    $time,
    $location,
    $type,
    $status,
    $organizer,
    $youtubeLink,
    $images,
    $videos
);
try {
    mysqli_stmt_execute($stmt);

    echo json_encode([
        "success" => true,
        "message" => "Event saved successfully"
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}


mysqli_stmt_close($stmt);
mysqli_close($conn);
