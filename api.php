<?php
/**
 * api.php — REST API cho upload và xoá (hỗ trợ single/bulk).
 *
 * Endpoints:
 *   POST /api.php?action=upload
 *      Content-Type: multipart/form-data
 *      Field: `files[]` (nhiều file) HOẶC `file` (1 file)
 *
 *   POST /api.php?action=delete
 *      Content-Type: application/json
 *      Body: { "names": ["img1.jpg", "img2.jpg"] }
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

/**
 * Chuẩn hoá $_FILES thành mảng các file objects dễ xử lý.
 * Hỗ trợ cả `files[]` (đa số) và `file` (đơn lẻ).
 */
function normalizeFiles(array $files): array
{
    $normalized = [];
    
    // Trường hợp 1: $_FILES['files'] (multiple file upload)
    // Structure: ['name' => [0 => 'a.jpg', 1 => 'b.jpg'], 'type' => [...], ...]
    if (isset($files['name']) && is_array($files['name'])) {
        foreach ($files['name'] as $idx => $name) {
            $normalized[] = [
                'name'     => $name,
                'type'     => $files['type'][$idx],
                'tmp_name' => $files['tmp_name'][$idx],
                'error'    => $files['error'][$idx],
                'size'     => $files['size'][$idx],
            ];
        }
    } 
    // Trường hợp 2: $_FILES['file'] (single file upload)
    // Structure: ['name' => 'a.jpg', 'type' => 'image/jpeg', ...]
    else {
        // Đảm bảo object có đủ key chuẩn
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
    // Tìm field upload hợp lệ
    // Ưu tiên 'files' (chuẩn multiple), fallback sang 'file' (legacy single)
    $inputFiles = $_FILES['files'] ?? ($_FILES['file'] ?? null);

    if (!$inputFiles) {
        jsonResponse(400, ['success' => false, 'error' => 'No files provided. Use field "files[]" or "file"']);
    }

    $fileList = normalizeFiles($inputFiles);
    $results = [];

    // Lấy URL base
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    // Xoá slash cuối nếu có để nối chuỗi cho đẹp
    $baseUrl = rtrim($baseUrl, '/');

    foreach ($fileList as $file) {
        // Init result object
        $fileResult = [
            'original_name' => $file['name'],
            'status'        => 'error', 
            'error'         => ''
        ];

        // 1. Check lỗi upload hệ thống
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $fileResult['error'] = 'Upload error code: ' . $file['error'];
            $results[] = $fileResult;
            continue;
        }

        // 2. Check kích thước
        if ($file['size'] > MAX_FILE_SIZE) {
            $fileResult['error'] = 'File too large (Max: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB)';
            $results[] = $fileResult;
            continue;
        }

        // 3. Check MIME type (dùng finfo)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);

        if (!isset(ALLOWED_MIME_TYPES[$detectedMime])) {
            $fileResult['error'] = 'Invalid file type (' . $detectedMime . ')';
            $results[] = $fileResult;
            continue;
        }

        // 4. Check ảnh thật sự (getimagesize)
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $fileResult['error'] = 'Not a valid image file';
            $results[] = $fileResult;
            continue;
        }

        // 5. Xử lý tên file và lưu
        $allowedExtensions = ALLOWED_MIME_TYPES[$detectedMime];
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Nếu ext gốc nằm trong list cho phép thì giữ nguyên, ngược lại lấy cái đầu tiên
        $finalExt = in_array($originalExt, $allowedExtensions) ? $originalExt : $allowedExtensions[0];

        $savedName = generateUniqueFileName($file['name'], $finalExt);
        $targetFile = UPLOAD_DIR . $savedName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $results[] = [
                'name'          => $savedName,          // Tên file mới trên disk
                'original_name' => $file['name'],       // Tên gốc user up lên
                'status'        => 'success',
                'url'           => $baseUrl . '/uploads/' . $savedName,
                'size'          => $file['size'],
                'mime'          => $detectedMime,
                'width'         => $imageInfo[0],
                'height'        => $imageInfo[1],
            ];
        } else {
            $fileResult['error'] = 'Failed to save file to disk';
            $results[] = $fileResult;
        }
    }

    jsonResponse(200, [
        'success' => true,
        'data'    => $results
    ]);
}

function handleDelete(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fallback: Check $_POST nếu gọi form-data delete (ít dùng nhưng phòng hờ)
        $input = $_POST;
    }

    $names = $input['names'] ?? (isset($input['name']) ? [$input['name']] : []);

    if (empty($names)) {
        jsonResponse(400, ['success' => false, 'error' => 'No image names provided.']);
    }

    $results = [];

    foreach ($names as $name) {
        if (empty($name)) continue;
        
        $imageName = basename($name); // Ngăn chặn directory traversal
        $filePath = UPLOAD_DIR . $imageName;

        // Check path traversal & extension (bảo mật)
        $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allExtensions = array_merge(...array_values(ALLOWED_MIME_TYPES));
        
        // Chỉ cho phép xoá file thuộc extension cho phép (tránh xoá bậy file .php hệ thống)
        if (!in_array($ext, $allExtensions)) {
            $results[] = ['name' => $imageName, 'status' => 'error', 'error' => 'Invalid extension'];
            continue;
        }

        if (!is_file($filePath)) {
            $results[] = ['name' => $imageName, 'status' => 'error', 'error' => 'File not found'];
            continue;
        }

        if (unlink($filePath)) {
            $results[] = ['name' => $imageName, 'status' => 'success'];
        } else {
            $results[] = ['name' => $imageName, 'status' => 'error', 'error' => 'Delete failed'];
        }
    }

    jsonResponse(200, [
        'success' => true,
        'data'    => $results
    ]);
}
