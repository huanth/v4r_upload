<?php
/**
 * delete.php — Handles single file deletion with security validation.
 * 
 * Validates CSRF token, ensures file is within uploads directory,
 * and only deletes actual image files.
 */

header('Content-Type: application/json');

// CSRF token validation
session_start();
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrfHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfHeader)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['url'])) {
    echo json_encode(['success' => false, 'message' => 'No file url provided']);
    exit;
}

$url = $input['url'];

// Only allow deleting files within the uploads directory
$uploadsDir = realpath(__DIR__ . '/uploads');
$filePath = realpath(__DIR__ . '/' . ltrim($url, '/'));

if (!$filePath || strpos($filePath, $uploadsDir) !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file path']);
    exit;
}

// Verify it's actually an image file before deleting
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Not an image file']);
    exit;
}

if (is_file($filePath)) {
    if (unlink($filePath)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found']);
}
