# Image Upload Web App

A simple, modern web app for uploading images (PNG, JPG, GIF, WEBP) with drag & drop, clipboard paste, and bulk upload support. Uploaded files are renamed safely and can be deleted from both UI and server.

## Features
- Drag & drop, click, or paste to upload images
- Bulk upload support
- File type and size validation (max 10MB)
- Safe file renaming (slug, ASCII, no special chars)
- Copy direct URL, BBCode, HTML, Markdown after upload
- Delete files from UI and server
- Responsive, modern UI
- All invalid links redirect to homepage (via `.htaccess`)

## Usage
1. Clone/download the project to your web server (tested with Laragon/XAMPP).
2. Make sure the `uploads/` folder is writable by the web server.
3. Open `index.php` in your browser.
4. Upload images by drag & drop, click, or paste.
5. Copy links or delete files as needed.

## Security
- Uploaded file names are sanitized and converted to safe slugs.
- Only image files are allowed.
- Deleting a file removes it from the server.
- All non-existent URLs redirect to homepage.

## File Structure
- `index.php` — Main UI
- `script.js` — Frontend logic
- `style.css` — Styles
- `upload.php` — Handles file upload
- `delete.php` — Handles file deletion
- `.htaccess` — Redirects all invalid links to homepage
- `uploads/` — Stores uploaded images

## License
MIT
