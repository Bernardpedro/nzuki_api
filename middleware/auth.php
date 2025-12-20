<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../config/jwt.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    die(json_encode(["error" => "Token missing"]));
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

try {
    $decoded = JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    die(json_encode(["error" => "Invalid or expired token"]));
}