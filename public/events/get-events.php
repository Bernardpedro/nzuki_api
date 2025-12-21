<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

$page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

if ($page < 1)  $page = 1;
if ($limit < 1) $limit = 5;

$offset = ($page - 1) * $limit;

// total event
$countSql = "SELECT COUNT(*) FROM events";
$countStmt = mysqli_prepare($conn, $countSql);

mysqli_stmt_execute($countStmt);  

mysqli_stmt_bind_result($countStmt, $totalEvents);
mysqli_stmt_fetch($countStmt);
mysqli_stmt_close($countStmt);

$sql = "SELECT
          id, title, description, date, time, location,
           type, status, organizer, youtubeLink,
            image, imageType, images 
        FROM events 
        ORDER BY date DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
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

// pagination information
$totalPages = ceil($totalEvents / $limit);

echo json_encode([
    "success" => true,
    "data" => $events,
    // "data" => $events,
    "pagination" => [
        "currentPage" => $page,
        "limit" => $limit,
        "totalPages" => $totalPages,
        "totalEvents" => $totalEvents
    ]
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);