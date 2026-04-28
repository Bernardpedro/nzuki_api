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

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID is required");
    }

    $id = intval($_GET['id']);

    $input = json_decode(file_get_contents("php://input"), true);

    if (
        empty($input['type']) ||
        empty($input['startDate']) ||
        empty($input['endDate']) ||
        empty($input['duration'])
    ) {
        throw new Exception("All fields are required");
    }

    // Check existence
    $check = $conn->prepare("SELECT id FROM exams WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Exam not found");
    }

    $check->close();

    $stmt = $conn->prepare("
        UPDATE exams
        SET type = ?, startDate = ?, endDate = ?, duration = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $input['type'],
        $input['startDate'],
        $input['endDate'],
        $input['duration'],
        $id
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Exam updated successfully"
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