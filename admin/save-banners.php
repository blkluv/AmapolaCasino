<?php
session_start();
header('Content-Type: application/json');

// Ensure user is authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF validation
$input = json_decode(file_get_contents("php://input"), true);
$csrfToken = $input['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check for valid banner data
if (!isset($input['banners']) || !is_array($input['banners'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid banner data']);
    exit;
}

// Sanitize banner input
$cleanedBanners = array_map(function ($banner) {
    return [
        'image' => filter_var($banner['image'], FILTER_SANITIZE_URL),
        'title' => htmlspecialchars($banner['title'] ?? '', ENT_QUOTES, 'UTF-8'),
        'subtitle' => htmlspecialchars($banner['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'),
        'link' => filter_var($banner['link'], FILTER_SANITIZE_URL),
        'button_text' => htmlspecialchars($banner['button_text'] ?? '', ENT_QUOTES, 'UTF-8'),
    ];
}, $input['banners']);

$json_path = dirname(__DIR__) . '/banners.json';
$backupDir = dirname(__DIR__) . '/backups';

// Make sure backups directory exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Create timestamped backup
if (file_exists($json_path)) {  // Use $json_path here instead of $filePath
    $timestamp = date('Ymd-His');
    $backupPath = $backupDir . "/banners-$timestamp.json";
    copy($json_path, $backupPath);  // Use $json_path here as well
}

// Save new banners
if (file_put_contents($json_path, json_encode(['banners' => $cleanedBanners], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}
