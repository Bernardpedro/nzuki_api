<?php

include("../connection.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die(json_encode(["success" => false, "error" => "Event ID is required"]));
}

$id = (int) $_GET['id'];

$sql = "
SELECT
    id, title, description, date, time, location,
    type, status, organizer, youtubeLink, images
FROM events
WHERE id = ?
LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $eventId,
    $title,
    $description,
    $date,
    $time,
    $location,
    $type,
    $status,
    $organizer,
    $youtubeLink,
    $images
);

if (mysqli_stmt_fetch($stmt)) {

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $eventId,
            "title" => $title,
            "description" => $description,
            "date" => $date,
            "time" => $time,
            "location" => $location,
            "type" => $type,
            "status" => $status,
            "organizer" => $organizer,
            "youtubeLink" => $youtubeLink,
            "images" => json_decode($images, true)
        ]
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Event not found"
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);