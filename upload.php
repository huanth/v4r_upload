<?php
/**
 * upload.php — Handles image file upload with security validation.
 * 
 * Validates MIME type, file extension, and size before saving.
 * Returns JSON response with upload status and file URL.
 */

header('Content-Type: application/json');

// CSRF token validation
session_start();
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_SERVER['HTTP_X_CSRF_TOKEN'])
        || !isset($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $_SERVER['HTTP_X_CSRF_TOKEN']))
) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Allowed MIME types and corresponding extensions
const ALLOWED_MIME_TYPES = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png'  => ['png'],
    'image/gif'  => ['gif'],
    'image/webp' => ['webp'],
];

const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

if (!isset($_FILES["file"])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES["file"];

// Check for upload errors
if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file["error"]]);
    exit;
}

// Validate file size
if ($file["size"] > MAX_FILE_SIZE) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
    exit;
}

// Validate MIME type using finfo (reads actual file content, not just extension)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedMime = $finfo->file($file["tmp_name"]);

if (!isset(ALLOWED_MIME_TYPES[$detectedMime])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
    exit;
}

// Double-check with getimagesize
$imageInfo = getimagesize($file["tmp_name"]);
if ($imageInfo === false) {
    echo json_encode(['success' => false, 'message' => 'File is not a valid image']);
    exit;
}

// Sanitize file name
$fileName = basename($file["name"]);
if (function_exists('transliterator_transliterate')) {
    $asciiName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9._-] remove', $fileName);
} else {
    $asciiName = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
    if ($asciiName === false) {
        $asciiName = $fileName;
    }
}

$asciiName = str_replace(' ', '-', $asciiName);
$asciiName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $asciiName);

// Use detected MIME to determine safe extension (ignore user-provided extension)
$allowedExtensions = ALLOWED_MIME_TYPES[$detectedMime];
$userExt = strtolower(pathinfo($asciiName, PATHINFO_EXTENSION));
$safeExt = in_array($userExt, $allowedExtensions) ? $userExt : $allowedExtensions[0];

$base = pathinfo($asciiName, PATHINFO_FILENAME);
if (empty($base)) {
    $base = 'file';
}

$safeName = strtolower($base) . '.' . $safeExt;
$safeName = str_replace(['../', './', '/'], '', $safeName);
$targetFile = $targetDir . uniqid() . "_" . $safeName;

if (move_uploaded_file($file["tmp_name"], $targetFile)) {
    echo json_encode([
        'success' => true,
        'url'     => $targetFile,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save uploaded file',
    ]);
}
