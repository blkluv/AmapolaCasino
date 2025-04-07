<?php
session_start();

// Allow only logged-in users
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$csrf_token = $input['csrf_token'] ?? '';

if ($csrf_token !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Sanitize function
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get banners data
$banners = $input['banners'] ?? null;

if (!is_array($banners)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid banners format.']);
    exit;
}

// Sanitize each banner item
$cleaned_banners = [];
foreach ($banners as $banner) {
    $cleaned_banners[] = [
        'image' => sanitize($banner['image'] ?? ''),
        'title' => sanitize($banner['title'] ?? ''),
        'subtitle' => sanitize($banner['subtitle'] ?? ''),
        'link' => sanitize($banner['link'] ?? ''),
        'button_text' => sanitize($banner['button_text'] ?? '')
    ];
}

// Backup mechanism: Create a backup of the current banners.json file
$backupDir = __DIR__ . '/backups/';

// Create the backup directory if it doesn't exist
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Read the current banners.json content to back it up
$json_path = __DIR__ . '/banners.json';
if (file_exists($json_path)) {
    $currentBanners = file_get_contents($json_path);
    $timestamp = date("Y-m-d_H-i-s");
    $backupFile = $backupDir . "banners_backup_" . $timestamp . ".json";

    // Backup the current banners.json file
    file_put_contents($backupFile, $currentBanners);
}

// Save the banners with CSRF token
$data = [
    'csrf_token' => $_SESSION['csrf_token'],
    'banners' => $cleaned_banners
];

// Update the banners.json file with new data
if (file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX)) {
    echo json_encode(['status' => 'success', 'message' => 'Banners updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save banners']);
}
