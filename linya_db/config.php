<?php
$server = "localhost";
$user   = "root";
$pass   = "";
$dbname = "linya_db";

$conn = new mysqli($server, $user, $pass, $dbname);

if ($conn->connect_error) {
  // JSON-safe error response for API endpoints
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "status"  => "error",
    "message" => "Unable to connect to the server: " . $conn->connect_error
  ]);
  exit;
}
