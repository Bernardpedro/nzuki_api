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
requireRole(['admin']);

include("../connection.php");

error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// id validation
if (empty($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Event ID is required"]);
    exit;
}

$id = (int) $_POST['id'];

// prepare values
$title        = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
$description  = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$date         = $_POST['date'] ?? '';
$time         = $_POST['time'] ?? '';
$location     = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
$type         = mysqli_real_escape_string($conn, $_POST['type'] ?? '');
$status       = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
$organizer    = mysqli_real_escape_string($conn, $_POST['organizer'] ?? '');
$youtubeLink  = mysqli_real_escape_string($conn, $_POST['youtubeLink'] ?? '');


// images user decided to keep
$existingImages = $_POST['existingImages'] ?? null;

if ($existingImages === null) {
    // frontend did not send existingImages → keep old ones
    $res = mysqli_query($conn, "SELECT images FROM events WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $imagesArray = json_decode($row['images'], true) ?? [];
    } else {
        $imagesArray = [];
    }
} else {
    // frontend sent images → clean them
    $imagesArray = [];
    foreach ($existingImages as $imgUrl) {
        $path = str_replace("http://localhost/nzuki-api/", "", $imgUrl);
        $imagesArray[] = $path;
    }
}

// Add new uploaded images
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $uploadDir = __DIR__ . "/../../uploads/events/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['images']['error'][$index] !== 0) {
            continue;
        }

        $extension = pathinfo($_FILES['images']['name'][$index], PATHINFO_EXTENSION);
        $fileName  = uniqid("event_gallery_", true) . "." . $extension;
        $filePath  = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            $imagesArray[] = "uploads/events/" . $fileName;
        }
    }
}

$images = json_encode($imagesArray);

error_log("Updating event ID: $id");
error_log("Images array: " . print_r($imagesArray, true));

$sql = "
UPDATE events SET
    title=?,
    description=?,
    date=?,
    time=?,
    location=?,
    type=?,
    status=?,
    organizer=?,
    youtubeLink=?,
    images=?
WHERE id=?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die(mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssssi",
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
    $id
);

if (mysqli_stmt_execute($stmt)) {

    $affected = mysqli_stmt_affected_rows($stmt);

    error_log("Affected rows: $affected");

    if ($affected > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Event updated hereee successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No changes detected"
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);