<?php
/**
 * api.php — REST API cho upload và xoá (hỗ trợ single/bulk).
 *
 * Endpoints:
 *   POST /api.php?action=upload  — Upload 1 hoặc nhiều ảnh
 *      Content-Type: multipart/form-data
 *      Field: `files[]` (nhiều file) HOẶC `file` (1 file)
 *
 *   POST /api.php?action=delete  — Xoá 1 hoặc nhiều ảnh
 *      Content-Type: application/json
 *      Body: { "names": ["img1.jpg", "img2.jpg"] } HOẶC { "name": "img1.jpg" }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config.php';

function jsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}



// Chuẩn hoá $_FILES thành mảng các file objects dễ xử lý
function normalizeFiles(array $files): array
{
    $normalized = [];
    if (isset($files['name']) && is_array($files['name'])) {
        // Dạng files[] upload nhiều file
        foreach ($files['name'] as $idx => $name) {
            $normalized[] = [
                'name'     => $name,
                'type'     => $files['type'][$idx],
                'tmp_name' => $files['tmp_name'][$idx],
                'error'    => $files['error'][$idx],
                'size'     => $files['size'][$idx],
            ];
        }
    } else {
        // Dạng file upload 1 file
        $normalized[] = $files;
    }
    return $normalized;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['success' => false, 'error' => 'Method must be POST']);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        handleUpload();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        jsonResponse(400, ['success' => false, 'error' => 'Unknown action']);
}

// ──────────────────────────────────────────────

function handleUpload(): void
{
    // Hỗ trợ cả `files` (số nhiều) và `file` (số ít)
    $inputFiles = $_FILES['files'] ?? ($_FILES['file'] ?? null);

    if (!$inputFiles) {
        jsonResponse(400, ['success' => false, 'error' => 'No files provided. Use field "files[]" or "file"']);
    }

    $fileList = normalizeFiles($inputFiles);
    $results = [];
    $hasError = false;

    // Lấy URL base
    $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME']), '/');

    foreach ($fileList as $file) {
        // Check từng file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $results[] = [
                'name'   => $file['name'],
                'status' => 'error',
                'error'  => 'Upload error code: ' . $file['error']
            ];
            $hasError = true;
            continue;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $results[] = [
                'name'   => $file['name'],
                'status' => 'error',
                'error'  => 'File too large'
            ];
            $hasError = true;
            continue;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);

        if (!isset(ALLOWED_MIME_TYPES[$detectedMime])) {
            $results[] = [
                'name'   => $file['name'],
                'status' => 'error',
                'error'  => 'Invalid file type'
            ];
            $hasError = true;
            continue;
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $results[] = [
                'name'   => $file['name'],
                'status' => 'error',
                'error'  => 'Not a valid image'
            ];
            $hasError = true;
            continue;
        }

        // Save
        $allowedExtensions = ALLOWED_MIME_TYPES[$detectedMime];
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finalExt = in_array($originalExt, $allowedExtensions) ? $originalExt : $allowedExtensions[0];

        $savedName = generateUniqueFileName($file['name'], $finalExt);
        $targetFile = UPLOAD_DIR . $savedName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $results[] = [
                'name'   => $savedName,
                'original_name' => $file['name'],
                'status' => 'success',
                'url'    => $baseUrl . '/uploads/' . $savedName,
                'size'   => $file['size'],
                'mime'   => $detectedMime,
                'width'  => $imageInfo[0],
                'height' => $imageInfo[1],
            ];
        } else {
            $results[] = [
                'name'   => $file['name'],
                'status' => 'error',
                'error'  => 'Failed to save file'
            ];
            $hasError = true;
        }
    }

    // Nếu chỉ upload 1 file và thành công, trả thẳng object (backward compatibility + tiện dụng)
    // Nhưng user yêu cầu support "lưu ý có thể upload nhiều ảnh... response api trả lại phải chuẩn"
    // -> Nên trả về mảng kết quả thì đồng nhất hơn. 
    // Tuy nhiên để dễ dùng, ta trả về structure: { success: bool, data: [ ... ] }

    jsonResponse(200, [
        'success' => true, // Luôn true nếu request xử lý xong, client tự check từng item trong data
        'data'    => $results
    ]);
}

function handleDelete(): void
{
    $input = json_decode(file_get_contents('php://input'), true);

    // Hỗ trợ `names` (mảng) hoặc `name` (đơn lẻ)
    $names = $input['names'] ?? (isset($input['name']) ? [$input['name']] : []);

    if (empty($names)) {
        jsonResponse(400, ['success' => false, 'error' => 'No image names provided. Use field "names[]" or "name"']);
    }

    $results = [];

    foreach ($names as $name) {
        if (empty($name)) continue;
        
        $imageName = basename($name); // Sanitize path
        $filePath = UPLOAD_DIR . $imageName;

        // Check extension
        $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allExtensions = array_merge(...array_values(ALLOWED_MIME_TYPES));
        
        if (!in_array($ext, $allExtensions)) {
            $results[] = [
                'name'   => $imageName,
                'status' => 'error',
                'error'  => 'Invalid extension'
            ];
            continue;
        }

        if (!is_file($filePath)) {
            $results[] = [
                'name'   => $imageName,
                'status' => 'error',
                'error'  => 'File not found'
            ];
            continue;
        }

        if (unlink($filePath)) {
            $results[] = [
                'name'   => $imageName,
                'status' => 'success'
            ];
        } else {
            $results[] = [
                'name'   => $imageName,
                'status' => 'error',
                'error'  => 'Delete failed'
            ];
        }
    }

    jsonResponse(200, [
        'success' => true,
        'data'    => $results
    ]);
}
