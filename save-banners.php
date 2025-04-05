<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? ($_SERVER['CONTENT_TYPE'] === 'application/json' ? json_decode(file_get_contents('php://input'), true)['csrf_token'] ?? '' : '');
if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

file_put_contents("banners.json", json_encode($data, JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
?>
