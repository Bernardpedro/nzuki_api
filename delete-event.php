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

$sql = "DELETE FROM events WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Event deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => mysqli_error($conn)
    ]);
}

mysqli_close($conn);
