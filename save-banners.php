<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = file_get_contents("php://input");
if ($data && json_decode($data) !== null) {
    file_put_contents("banners.json", $data);
    echo json_encode(["success" => true]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
}
