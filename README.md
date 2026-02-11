# Ứng Dụng Upload Hình Ảnh

Ứng dụng web upload hình ảnh (PNG, JPG, GIF, WEBP) hiện đại, hỗ trợ kéo thả, dán từ clipboard và upload hàng loạt. File được đổi tên an toàn, có thể xoá từ giao diện và server.

## Tính năng
- Kéo thả, click hoặc dán để upload
- Upload hàng loạt
- Kiểm tra định dạng file và kích thước (tối đa 10MB)
- Đổi tên file an toàn (slug, ASCII, không ký tự đặc biệt)
- Sao chép link: URL, BBCode, HTML, Markdown
- Xoá file từ giao diện và server
- Giao diện responsive, hiện đại
- **REST API** cho developer (upload/xoá đơn lẻ hoặc hàng loạt)
- Link không tồn tại tự chuyển về trang chủ (`.htaccess`)

## Bảo mật
- CSRF token cho request upload/xoá (web UI)
- Validate MIME type thực sự bằng `finfo` + `getimagesize`
- Chỉ cho phép file ảnh (JPG, PNG, GIF, WEBP)
- Tên file được sanitize và chuyển sang slug an toàn
- Escape HTML chống XSS khi hiển thị tên file
- Xoá hàng loạt yêu cầu mật khẩu (xác thực server-side)

## Hướng dẫn sử dụng
1. Clone/download dự án vào web server (Laragon/XAMPP).
2. Đảm bảo thư mục `uploads/` có quyền ghi.
3. Mở `index.php` trên trình duyệt.
4. Upload ảnh bằng cách kéo thả, click hoặc dán.
5. Sao chép link hoặc xoá file tuỳ ý.

## Cấu trúc thư mục
- `index.php` — Giao diện chính, tạo CSRF token
- `script.js` — Logic frontend (upload, preview, copy, xoá)
- `style.css` — Giao diện CSS
- `upload.php` — Xử lý upload file từ web UI
- `delete.php` — Xử lý xoá file từ web UI
- `api.php` — REST API cho developer (hỗ trợ bulk)
- `config.php` — Cấu hình chung (MIME types, giới hạn file)
- `stats.php` — Thống kê và quản lý ảnh
- `.htaccess` — Redirect link không tồn tại về trang chủ
- `uploads/` — Thư mục lưu ảnh

---

## REST API

Không cần API key. Hỗ trợ CORS. Trả về JSON chuẩn với danh sách kết quả cho từng item.

### 1. Upload ảnh (1 hoặc nhiều file)

```
POST /api.php?action=upload
Content-Type: multipart/form-data
```

**Field:** `files[]` (nhiều file) hoặc `file` (1 file)

**cURL (upload hàng loạt):**
```bash
curl -X POST "https://up.v4r.net/api.php?action=upload" \
  -F "files[]=@image1.jpg" \
  -F "files[]=@image2.png"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "name": "abc123_image1.jpg",
      "original_name": "image1.jpg",
      "status": "success",
      "url": "https://up.v4r.net/uploads/abc123_image1.jpg",
      "size": 123456,
      "mime": "image/jpeg",
      "width": 1920,
      "height": 1080
    },
    {
      "name": "image2.png",
      "status": "error",
      "error": "File too large"
    }
  ]
}
```

### 2. Xoá ảnh (1 hoặc nhiều file)

```
POST /api.php?action=delete
Content-Type: application/json
```

**Body:** truyền mảng tên file `names` (hoặc `name` nếu xoá 1 file)

**cURL (xoá hàng loạt):**
```bash
curl -X POST "https://up.v4r.net/api.php?action=delete" \
  -H "Content-Type: application/json" \
  -d '{"names": ["abc123_image1.jpg", "xyz789_image2.png"]}'
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "name": "abc123_image1.jpg",
      "status": "success"
    },
    {
      "name": "xyz789_image2.png",
      "status": "error",
      "error": "File not found"
    }
  ]
}
```

### Mã lỗi HTTP

| Code | Ý nghĩa |
|------|----------|
| 200 | Request được xử lý (kết quả chi tiết trong `data`) |
| 400 | Request thiếu tham số |
| 405 | Method không đúng (chỉ POST) |
| 500 | Lỗi server nghiêm trọng |

## Giấy phép
MIT
