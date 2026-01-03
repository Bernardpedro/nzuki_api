<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../config/jwt.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$authHeader = null;

if (isset($_SERVER['HTTP_AUTHORIZATION'])){
     $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])){
     $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} else {
        $headers = getallheaders();
        if (isset($headers['Authorization'])){
            $authHeader = $headers['Authorization'];
        } else if (isset($headers['authorization'])){
            $authHeader = $headers['authorization'];
        }
}

if (!$authHeader) {
    echo json_encode(["error" => "Token j missing"]);
    exit;
}

$token = str_replace("Bearer ", "", $authHeader);

try {
    $decoded = JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
    $userId = $decoded->user_id ?? null;
    $userRole = $decoded->role ?? null;

    if (!$userId || !$userRole) {
    http_response_code(401);
      echo json_encode(["error" => "Invalid token"]);
    exit;
}

} catch (Exception $e) {
    die(json_encode(["error" => "Invalid or expired token"]));
}

function requireRole(array $allowedRoles) {
    global $userRole;

    if (!in_array($userRole, $allowedRoles)) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Access denied"
        ]);
        exit;
    }
}


