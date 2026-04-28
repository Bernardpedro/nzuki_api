<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include("../../connection.php");

try {

    if (!isset($_GET['id'])) {
        throw new Exception("ID is required");
    }

    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM requirement_levels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Requirement deleted successfully"
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