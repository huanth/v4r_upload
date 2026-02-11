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
- Link không tồn tại tự chuyển về trang chủ (`.htaccess`)

## Bảo mật
- CSRF token cho tất cả request upload/xoá
- Validate MIME type thực sự bằng `finfo` + `getimagesize`
- Chỉ cho phép file ảnh (JPG, PNG, GIF, WEBP)
- Tên file được sanitize và chuyển sang slug an toàn
- Escape HTML chống XSS khi hiển thị tên file
- Xoá hàng loạt yêu cầu mật khẩu (xác thực server-side)
- Tất cả URL không tồn tại redirect về trang chủ

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
- `upload.php` — Xử lý upload file (validate MIME, CSRF)
- `delete.php` — Xử lý xoá file (CSRF, whitelist extension)
- `stats.php` — Thống kê và quản lý ảnh (xoá hàng loạt)
- `.htaccess` — Redirect link không tồn tại về trang chủ
- `uploads/` — Thư mục lưu ảnh

## Giấy phép
MIT
