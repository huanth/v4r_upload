<?php
$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (isset($_FILES["file"])) {
    $file = $_FILES["file"];
    $fileName = basename($file["name"]);
    // Chuyển Unicode sang ASCII không dấu
    if (function_exists('transliterator_transliterate')) {
        $asciiName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9._-] remove', $fileName);
    } else {
        $asciiName = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
        if ($asciiName === false) $asciiName = $fileName;
    }
    // Thay dấu cách bằng gạch ngang
    $asciiName = str_replace(' ', '-', $asciiName);
    // Loại bỏ ký tự đặc biệt, chỉ giữ lại a-z, 0-9, dấu gạch ngang, dấu chấm, dấu gạch dưới
    $asciiName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $asciiName);
    // Tránh tên file rỗng hoặc chỉ có đuôi
    $ext = pathinfo($asciiName, PATHINFO_EXTENSION);
    $base = pathinfo($asciiName, PATHINFO_FILENAME);
    if (empty($base)) $base = 'file';
    $safeName = strtolower($base . ($ext ? '.' . $ext : ''));
    // Đảm bảo không có ../ hay / trong tên file
    $safeName = str_replace(['../', './', '/'], '', $safeName);
    $targetFile = $targetDir . uniqid() . "_" . $safeName;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $url = $targetFile;
        echo json_encode([
            "success" => true,
            "url" => $url
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Upload failed"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded"
    ]);
}
