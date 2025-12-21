<?php

require __DIR__ . "/../../middleware/auth.php";

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data || empty($data['id'])) {
    die(json_encode(["error" => "Event ID is required"]));
}

$id = (int) $data['id'];

$time        = date("Y-m-d H:i:s", strtotime($data['time']));
$imageType   = mysqli_real_escape_string($conn, $data['imageType']);

$sql = "
UPDATE events SET
    title=?, description=?, date=?, time=?, location=?,
    type=?, status=?, organizer=?, youtubeLink=?,
    image=?, imageType=?, images=?
WHERE id=?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
   $stmt,
    "ssssssssssssi",
    $data['title'],
    $data['description'],
    $data['date'],
    $time,
    $data['location'],
    $data['type'],
    $data['status'],
    $data['organizer'],
    $data['youtubeLink'],
    $data['image'],
    $data['imageType'],
    $images,
    $data['id']);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Event updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
