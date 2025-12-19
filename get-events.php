<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

$sql = "SELECT * FROM events ORDER BY date DESC";
$result = mysqli_query($conn, $sql);

$events = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Convert images JSON back to array
    $row['images'] = json_decode($row['images'], true);
    $events[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $events
]);

mysqli_close($conn);
