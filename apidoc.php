<?php
/**
 * apidoc.php — API Documentation page.
 */
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation | Upload Hình Ảnh Nhanh Chóng</title>
    <meta name="description" content="Tài liệu API cho V4R Upload. Hướng dẫn tích hợp upload và xoá ảnh cho developer.">
    <link rel="icon" href="https://v4r.net/assets/site-image-ua62u0v5.png" type="image/x-icon">
    <link rel="stylesheet" href="./style.css">
    <style>
        .api-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            /* background: rgba(255, 255, 255, 0.9); */
            border-radius: 12px;
            /* box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); */
        }
        .api-section {
            margin-bottom: 40px;
        }
        .api-title {
            font-size: 2rem;
            color: #f7fafc;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
        .api-subtitle {
            font-size: 1.5rem;
            color: #f7fafc;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .endpoint {
            background: #f7fafc;
            border-left: 4px solid #4299e1;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-family: monospace;
            font-size: 1.1em;
            color: #2b6cb0;
        }
        .method {
            font-weight: bold;
            color: #fff;
            background: #4299e1;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 10px;
        }
        .code-block {
            background: #1a202c;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.9em;
            margin: 10px 0;
            line-height: 1.5;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #edf2f7;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="/" id="home-link">
                <img src="https://v4r.net/assets/logo-tvzme3ed.png" alt="V4R.NET - Upload" class="Header-logo">
            </a>
            <div class="nav-links">
                <a href="/">Trang chủ</a>
                <a href="apidoc.php" class="active">API Doc</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="api-container">
            <h1 class="api-title">REST API Documentation</h1>
            <p>Tài liệu tích hợp upload và xoá ảnh (đơn & bulk) cho developer. API hỗ trợ CORS và không yêu cầu API key.</p>

            <div class="api-section">
                <h2 class="api-subtitle">1. Upload Ảnh (Bulk Support)</h2>
                <div class="endpoint">
                    <span class="method">POST</span> /api.php?action=upload
                </div>
                <p>Upload một hoặc nhiều ảnh cùng lúc.</p>

                <p style="margin: 10px 0;background:#fffbeb; border-left:4px solid #f59e0b; padding:10px 15px; border-radius:4px; color:#92400e;">⚠️ <strong>Lưu ý:</strong> Khi upload nhiều file, field <strong>BẮT BUỘC</strong> phải là <code>files[]</code> (có <code>[]</code>). Nếu dùng <code>files</code> (không có <code>[]</code>), PHP chỉ nhận file cuối cùng.</p>
                
                <h3>Tham số</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>files[]</code></td>
                            <td>Mảng các file ảnh cần upload (Hỗ trợ nhiều file). <strong>Bắt buộc có <code>[]</code></strong>.</td>
                        </tr>
                        <tr>
                            <td><code>file</code></td>
                            <td>File ảnh đơn lẻ (nếu chỉ upload 1 file).</td>
                        </tr>
                    </tbody>
                </table>

                <h3>cURL — Upload nhiều file</h3>
                <pre class="code-block">
curl -X POST "https://up.v4r.net/api.php?action=upload" \
  -F "files[]=@image1.jpg" \
  -F "files[]=@image2.png"
                </pre>

                <h3>cURL — Upload 1 file</h3>
                <pre class="code-block">
curl -X POST "https://up.v4r.net/api.php?action=upload" \
  -F "file=@image1.jpg"
                </pre>

                <h3>Response Thành Công</h3>
                <pre class="code-block">
{
  "success": true,
  "data": [
    {
      "name": "a1b2c3..._1739245678_image1.jpg",
      "original_name": "image1.jpg",
      "status": "success",
      "url": "https://up.v4r.net/uploads/a1b2c3..._1739245678_image1.jpg",
      "size": 123456,
      "mime": "image/jpeg",
      "width": 1920,
      "height": 1080
    }
  ]
}
                </pre>
            </div>

            <div class="api-section">
                <h2 class="api-subtitle">2. Xoá Ảnh (Bulk Support)</h2>
                <div class="endpoint">
                    <span class="method">POST</span> /api.php?action=delete
                </div>
                <p>Xoá một hoặc nhiều ảnh.</p>

                <h3 style="margin: 10px 0;">Body JSON</h3>
                <pre class="code-block">
{
  "names": [
    "a1b2c3..._1739245678_image1.jpg",
    "xyz789..._1739245679_image2.png"
  ]
}
                </pre>

                <h3>cURL Example</h3>
                <pre class="code-block">
curl -X POST "https://up.v4r.net/api.php?action=delete" \
  -H "Content-Type: application/json" \
  -d '{"names": ["filename_to_delete.jpg"]}'
                </pre>

                <h3>Response</h3>
                <pre class="code-block">
{
  "success": true,
  "data": [
    {
      "name": "filename_to_delete.jpg",
      "status": "success"
    }
  ]
}
                </pre>
            </div>

            <div class="api-section">
                <h2 class="api-subtitle">Mã Lỗi HTTP</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>200</td>
                            <td>Thành công (Chi tiết trong <code>data</code>)</td>
                        </tr>
                        <tr>
                            <td>400</td>
                            <td>Request thiếu tham số hoặc sai format</td>
                        </tr>
                        <tr>
                            <td>405</td>
                            <td>Method không đúng (Chỉ dùng POST)</td>
                        </tr>
                        <tr>
                            <td>413</td>
                            <td>File quá lớn (>100MB)</td>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>Lỗi server nội bộ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <p class="footer-text">© 2026 <a href="https://v4r.net/" target="_blank">V4R Team.</a> All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
