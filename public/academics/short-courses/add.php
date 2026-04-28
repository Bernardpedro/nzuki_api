<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include("../../connection.php");

try {

    $input = json_decode(file_get_contents("php://input"), true);

    if (
        empty($input['name']) ||
        empty($input['skills']) ||
        empty($input['facilities']) ||
        empty($input['careers'])
    ) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("
        INSERT INTO short_courses (name, skills, facilities, careers)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssss",
        $input['name'],
        $input['skills'],
        $input['facilities'],
        $input['careers']
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Short course created successfully"
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

} catch (mysqli_sql_exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
}

$conn->close();