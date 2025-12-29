<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}

require __DIR__ . "/../../vendor/autoload.php";

$conn = mysqli_connect("localhost", "root", "", "nzuki_db");
if (!$conn) {
    die(json_encode(["error" => "DB connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    die(json_encode(["error" => "Invalid JSON"]));
}


$name  = $data['name'];
$email = $data['email'];
$pass = password_hash($data['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "sss", $name, $email, $pass);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true, "message" => "User registered successfully"]);
} else {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
}
