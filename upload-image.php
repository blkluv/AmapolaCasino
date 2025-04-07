<?php
session_start();
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/images/banners/';
$csrf_token = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
  echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
  exit;
}

if (!isset($_FILES['file'])) {
  echo json_encode(['success' => false, 'message' => 'No file uploaded']);
  exit;
}

$file = $_FILES['file'];
$filename = basename($file['name']); // Get the filename (with extension)
$targetPath = $uploadDir . $filename; // Full server path to store the file

// Make sure the uploaded file is not already in the target directory
if (file_exists($targetPath)) {
  echo json_encode(['success' => false, 'message' => 'File already exists']);
  exit;
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
  // Assuming the file is uploaded to the "images/banners/" folder
  $webPath = 'images/banners/' . $filename; // Relative path to the image
  echo json_encode(['success' => true, 'url' => $webPath]); // Return the correct relative URL
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
