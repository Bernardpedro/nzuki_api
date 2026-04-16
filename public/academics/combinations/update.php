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

    // 🔴 ID from QUERY PARAM (NOT BODY)
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID is required in query parameter");
    }

    $id = intval($_GET['id']);

    $input = json_decode(file_get_contents("php://input"), true);

    if (
        empty($input['name']) ||
        empty($input['focus']) ||
        empty($input['facilities']) ||
        empty($input['careers'])
    ) {
        throw new Exception("All fields are required");
    }

    // Check existence
    $check = $conn->prepare("SELECT id FROM combinations WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Combination not found");
    }

    $check->close();

    // Update
    $stmt = $conn->prepare("
        UPDATE combinations
        SET name = ?, focus = ?, facilities = ?, careers = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $input['name'],
        $input['focus'],
        $input['facilities'],
        $input['careers'],
        $id
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Combination updated successfully"
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