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
        empty($input['activity']) ||
        empty($input['startDate']) ||
        empty($input['endDate']) ||
        empty($input['duration'])
    ) {
        throw new Exception("All fields are required");
    }

    // Convert boolean → 0/1
    $isHoliday = !empty($input['isHoliday']) ? 1 : 0;

    $stmt = $conn->prepare("
        INSERT INTO calendar_terms (activity, startDate, endDate, duration, isHoliday)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssi",
        $input['activity'],
        $input['startDate'],
        $input['endDate'],
        $input['duration'],
        $isHoliday
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Calendar term created successfully"
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