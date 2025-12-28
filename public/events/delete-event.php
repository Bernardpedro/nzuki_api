<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}

require __DIR__ . "/../../middleware/auth.php";

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nzuki_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Event ID is required"]);
    exit;
}

$id = (int) $_POST['id'];

    //Get images paths BEFORE deleting event
    $sql = "SELECT images FROM events WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
        
    $event = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$event) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Event not found"]);
    exit;
}

//delete images from files 
if (!$event) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Event not found"]);
    exit;
}

// Delete event row:
// Prepared statement 
$sql = "DELETE FROM events WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Event deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
