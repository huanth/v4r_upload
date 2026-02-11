<?php
/**
 * config.php — Cấu hình chung cho ứng dụng upload.
 *
 * Chứa API keys và các hằng số dùng chung giữa web UI và API.
 */

// API keys cho developer (thêm key mới vào mảng)
const API_KEYS = [
    'v4r-dev-key-2026-change-me',
];

// Allowed MIME types và extension tương ứng
const ALLOWED_MIME_TYPES = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png'  => ['png'],
    'image/gif'  => ['gif'],
    'image/webp' => ['webp'],
];

const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const UPLOAD_DIR = __DIR__ . '/uploads/';
