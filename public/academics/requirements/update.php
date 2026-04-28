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

    if (!isset($_GET['id'])) {
        throw new Exception("ID is required");
    }

    $id = intval($_GET['id']);
    $input = json_decode(file_get_contents("php://input"), true);

    $stmt = $conn->prepare("
        UPDATE requirement_levels
        SET code = ?, name = ?, description = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssi",
        $input['code'],
        $input['name'],
        $input['description'],
        $id
    );

    $stmt->execute();

    // 🔴 Reset URLs (simple strategy)
    $conn->query("DELETE FROM requirement_urls WHERE requirement_id = $id");

    if (!empty($input['urls'])) {
        foreach ($input['urls'] as $url) {

            if (empty(trim($url))) continue;

            $urlStmt = $conn->prepare("
                INSERT INTO requirement_urls (requirement_id, url)
                VALUES (?, ?)
            ");

            $urlStmt->bind_param("is", $id, $url);
            $urlStmt->execute();
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Requirement updated successfully"
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