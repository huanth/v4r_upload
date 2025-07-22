<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['url'])) {
    echo json_encode(['success' => false, 'message' => 'No file url provided']);
    exit;
}

$url = $input['url'];
// Chỉ cho phép xóa file trong thư mục uploads
$uploadsDir = realpath(__DIR__ . '/uploads');
$filePath = realpath(__DIR__ . '/' . ltrim($url, '/'));

if (!$filePath || strpos($filePath, $uploadsDir) !== 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid file path']);
    exit;
}

if (is_file($filePath)) {
    if (unlink($filePath)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File not found']);
}
