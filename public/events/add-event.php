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

// Prepare values

$title        = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$description  = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$date         = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$location     = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
$type         = mysqli_real_escape_string($conn, $_POST['type'] ?? '');
$status       = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
$organizer    = mysqli_real_escape_string($conn, $_POST['organizer'] ?? '');
$youtubeLink  = mysqli_real_escape_string($conn, $_POST['youtubeLink'] ?? '');

// $imagePath = null;
// $imageType = null;

// if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

//        $uploadDir = __DIR__ . "/../../uploads/events/";

//          if (!is_dir($uploadDir)) {
//               mkdir($uploadDir, 0755, true);
//          }

         
//         $imageType = mime_content_type($_FILES['image']['tmp_name']);
//         $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

//         $fileName = uniqid("event_", true) . "." . $extension;
//         $filePath = $uploadDir . $fileName;

//           if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
//         $imagePath = "uploads/events/" . $fileName;
//     }
// }

// $images = json_encode([]);

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



// Insert query
$sql = "
INSERT INTO events (
    title, description, date, time, location,
    type, status, organizer, youtubeLink, imageType, images
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
    // $imagePath,
    $imageType,
    $images
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Event saved fff successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
