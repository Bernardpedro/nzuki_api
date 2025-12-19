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

$sql = "DELETE FROM events WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Event deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
