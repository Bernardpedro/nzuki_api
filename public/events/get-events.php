<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}

include("../connection.php");

$page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;

if ($page < 1)  $page = 1;
if ($limit < 1) $limit = 100;

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
            imageType, images 
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