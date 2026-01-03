<?php

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db="nzuki_db";                                                                                               

try {
  $conn = mysqli_connect($host, $user, $pass, $db);
} catch (Exception $e) {

  echo json_encode(["error" => "Database connection failed ",
  "message"=> $e->getMessage()]);
    die();
}
?>