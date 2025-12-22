<?php
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../config/jwt.php";

use Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}


$conn = mysqli_connect("localhost", "root", "", "nzuki_db");
if (!$conn) {
    die(json_encode(["error" => "DB connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];
$password = $data['password'];

$sql = "SELECT id, password FROM users WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt, $id, $hashedPassword);

if (mysqli_stmt_fetch($stmt) && password_verify($password, $hashedPassword)){
    $payload = [
        "iss" => $JWT_ISSUER,
        "iat" => time(),
        "exp" => $JWT_EXPIRE,
        "user_id" => $id
    ];

     $token = JWT::encode($payload, $JWT_SECRET, 'HS256');

     echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $token
    ]);

  }else {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
}