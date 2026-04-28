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
        empty($input['code']) ||
        empty($input['name']) ||
        empty($input['description'])
    ) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("
        INSERT INTO requirement_levels (code, name, description)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "sss",
        $input['code'],
        $input['name'],
        $input['description']
    );

    $stmt->execute();

    $requirementId = $conn->insert_id;

    // Insert URLs
    if (!empty($input['urls'])) {
        foreach ($input['urls'] as $url) {

            if (empty(trim($url))) continue;

            $urlStmt = $conn->prepare("
                INSERT INTO requirement_urls (requirement_id, url)
                VALUES (?, ?)
            ");

            $urlStmt->bind_param("is", $requirementId, $url);
            $urlStmt->execute();
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Requirement created successfully"
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