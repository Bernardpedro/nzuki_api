<?php

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

$title       = mysqli_real_escape_string($conn, $data['title']);
$description = mysqli_real_escape_string($conn, $data['description']);
$date        = $data['date'];
$time        = date("Y-m-d H:i:s", strtotime($data['time']));
$location    = mysqli_real_escape_string($conn, $data['location']);
$type        = mysqli_real_escape_string($conn, $data['type']);
$status      = mysqli_real_escape_string($conn, $data['status']);
$organizer   = mysqli_real_escape_string($conn, $data['organizer']);
$youtubeLink = mysqli_real_escape_string($conn, $data['youtubeLink']);
$image       = mysqli_real_escape_string($conn, $data['image']);
$imageType   = mysqli_real_escape_string($conn, $data['imageType']);
$images      = mysqli_real_escape_string($conn, json_encode($data['images']));

$sql = "
UPDATE events SET
    title='$title',
    description='$description',
    date='$date',
    time='$time',
    location='$location',
    type='$type',
    status='$status',
    organizer='$organizer',
    youtubeLink='$youtubeLink',
    image='$image',
    imageType='$imageType',
    images='$images'
WHERE id=$id
";

if (mysqli_query($conn, $sql)) {
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

mysqli_close($conn);
