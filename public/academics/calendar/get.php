<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include("../../connection.php");

try {

    if (isset($_GET['id'])) {

        $id = intval($_GET['id']);

        $stmt = $conn->prepare("SELECT * FROM calendar_terms WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) {
            throw new Exception("Calendar term not found");
        }

        // Convert 0/1 → boolean
        $data['isHoliday'] = (bool)$data['isHoliday'];

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);

        $stmt->close();

    } else {

        $result = $conn->query("SELECT * FROM calendar_terms");

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $row['isHoliday'] = (bool)$row['isHoliday'];
            $data[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

} catch (Exception $e) {

    http_response_code(404);

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