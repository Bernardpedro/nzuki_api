<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

$sql = "SELECT id, title, description, date, time, location, type, status, organizer, youtubeLink, image, imageType, images FROM events ORDER BY date DESC";
// $sql = "SELECT * FROM events ORDER BY date DESC";

$stmt = mysqli_prepare($conn, $sql);
// $result = mysqli_query($conn, $sql);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $id,
    $title,
    $description,
    $date,
    $time,
    $location,
    $type,
    $status,
    $organizer,
    $youtubeLink,
    $image,
    $imageType,
    $images
);

// $result = mysqli_stmt_get_result($stmt);

$events = [];

while (mysqli_stmt_fetch($stmt)) {
    $events[] = [
            "id" => $id,
            "title" => $title,
            "description" => $description,
            "date" => $date,
            "time" => $time,
            "location" => $location,
            "type" => $type,
            "status" => $status,
            "organizer" => $organizer,
            "youtubeLink" => $youtubeLink,
            "image" => $image,
            "imageType" => $imageType,
            "images" => json_decode($images, true)
        ];
}

echo json_encode([
    "success" => true,
    "data" => $events
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
