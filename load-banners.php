<?php
session_start();

// Allow only logged-in users
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$json_path = __DIR__ . '/banners.json';

if (!file_exists($json_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Banners file not found']);
    exit;
}

$data = json_decode(file_get_contents($json_path), true);

if ($data === null) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse banners file']);
    exit;
}

// Include the CSRF token in the response
echo json_encode([
    'status' => 'success',
    'csrf_token' => $_SESSION['csrf_token'], // Send the CSRF token to the client
    'banners' => $data['banners'] ?? []
]);
