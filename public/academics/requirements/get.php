<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include("../../connection.php");

try {

    if (isset($_GET['id'])) {

        $id = intval($_GET['id']);

        $stmt = $conn->prepare("SELECT * FROM requirement_levels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) {
            throw new Exception("Requirement not found");
        }

        // fetch URLs
        $urlStmt = $conn->prepare("SELECT url FROM requirement_urls WHERE requirement_id = ?");
        $urlStmt->bind_param("i", $id);
        $urlStmt->execute();

        $urlResult = $urlStmt->get_result();
        $urls = [];

        while ($row = $urlResult->fetch_assoc()) {
            $urls[] = $row['url'];
        }

        $data['urls'] = $urls;

        echo json_encode([
            "success" => true,
            "data" => $data
        ]);

    } else {

        $result = $conn->query("SELECT * FROM requirement_levels");

        $data = [];

        while ($row = $result->fetch_assoc()) {

            $id = $row['id'];

            $urlStmt = $conn->prepare("SELECT url FROM requirement_urls WHERE requirement_id = ?");
            $urlStmt->bind_param("i", $id);
            $urlStmt->execute();

            $urlResult = $urlStmt->get_result();
            $urls = [];

            while ($u = $urlResult->fetch_assoc()) {
                $urls[] = $u['url'];
            }

            $row['urls'] = $urls;

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