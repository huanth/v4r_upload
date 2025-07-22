<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title of the webpage -->
    <title>Upload Hình Ảnh Nhanh Chóng | Chia Sẻ Ảnh Dễ Dàng</title>

    <!-- Meta description for SEO -->
    <meta name="description" content="Nền tảng upload hình ảnh nhanh chóng và tiện lợi. Chia sẻ hình ảnh của bạn dễ dàng với chất lượng cao và tốc độ tải nhanh.">

    <!-- Meta keywords for SEO (Optional) -->
    <meta name="keywords" content="upload hình ảnh, chia sẻ ảnh, upload nhanh, tải hình ảnh, hình ảnh chất lượng cao, chia sẻ ảnh trực tuyến, upload ảnh miễn phí">

    <!-- Robots meta tag to control search engine crawling -->
    <meta name="robots" content="index, follow">

    <!-- Open Graph meta tags for better social sharing -->
    <meta property="og:title" content="Upload Hình Ảnh Nhanh Chóng | Chia Sẻ Ảnh Dễ Dàng">
    <meta property="og:description" content="Chia sẻ và upload hình ảnh với tốc độ nhanh, chất lượng cao. Dễ dàng tải ảnh lên và chia sẻ với bạn bè trên các nền tảng mạng xã hội.">
    <meta property="og:image" content="https://v4r.net/assets/site-image-ua62u0v5.png">
    <meta property="og:url" content="https://up.v4r.net">
    <meta property="og:type" content="website">

    <!-- Twitter card meta tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Upload Hình Ảnh Nhanh Chóng | Chia Sẻ Ảnh Dễ Dàng">
    <meta name="twitter:description" content="Chia sẻ và upload hình ảnh với tốc độ nhanh chóng. Upload ảnh lên và chia sẻ dễ dàng với chất lượng vượt trội.">
    <meta name="twitter:image" content="https://v4r.net/assets/site-image-ua62u0v5.png">

    <!-- Favicon for website icon -->
    <link rel="icon" href="https://v4r.net/assets/site-image-ua62u0v5.png" type="image/x-icon">

    <!-- Canonical URL to prevent duplicate content issues -->
    <link rel="canonical" href="https://up.v4r.net">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebPage",
            "url": "https://up.v4r.net/",
            "name": "Upload Hình Ảnh Nhanh Chóng | Chia Sẻ Ảnh Dễ Dàng",
            "description": "Nền tảng upload hình ảnh nhanh chóng và tiện lợi. Chia sẻ hình ảnh của bạn dễ dàng với chất lượng cao và tốc độ tải nhanh.",
            "publisher": {
                "@type": "Organization",
                "name": "Upload Hình Ảnh Nhanh Chóng",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://v4r.net/assets/site-image-ua62u0v5.png"
                },
                "sameAs": [
                    "https://www.facebook.com/v4r.net/"
                ]
            },
            "mainEntityOfPage": {
                "@type": "WebSite",
                "@id": "https://up.v4r.net/"
            },
            "potentialAction": {
                "@type": "SearchAction",
                "target": "https://up.v4r.net/search?q={search_term}",
                "query-input": "required name=search_term"
            },
            "founder": {
                "@type": "Person",
                "name": "V4R Team",
                "url": "https://v4r.net",
                "sameAs": [
                    "https://www.facebook.com/v4r.net/"
                ]
            },
            "foundingDate": "2025",
            "email": "dev@v4r.net",
            "legalName": "V4R Team",
        }
    </script>

    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <header class="header">
        <div class="header-container">
            <a href="/" id="home-link">
                <img src="https://v4r.net/assets/logo-tvzme3ed.png" alt="V4R.NET - Upload" class="Header-logo">
            </a>
        </div>
    </header>

    <div class="container">
        <div class="upload-container">
            <div class="upload-box" id="uploadBox">
                <div class="upload-content">
                    <div class="upload-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="upload-title">Drop files here, click to upload or paste image</h3>
                    <p class="upload-subtitle">Support for single or bulk uploads. Strictly PNG, JPG and GIF files only.</p>
                    <button class="upload-button" type="button">Choose Files</button>
                    <input type="file" id="fileInput" multiple accept="image/*" hidden>
                </div>

                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-circle">
                        <svg class="progress-ring" width="60" height="60">
                            <circle cx="30" cy="30" r="25" stroke="#4a5568" stroke-width="4" fill="none" />
                            <circle class="progress-bar" cx="30" cy="30" r="25" stroke="#f093fb" stroke-width="4" fill="none" stroke-dasharray="157" stroke-dashoffset="157" />
                        </svg>
                        <span class="progress-text">0%</span>
                    </div>
                    <p class="progress-label">Uploading files...</p>
                </div>
            </div>

            <div class="files-preview" id="filesPreview">
                <div class="preview-header">
                    <h4 class="preview-title">Selected Files</h4>
                    <button class="add-more-btn" id="addMoreBtn" style="display: none;">Add More Files</button>
                </div>
                <div class="files-list" id="filesList">
                    <!-- Files will be dynamically added here -->
                </div>
            </div>

            <div class="upload-complete" id="uploadComplete" style="display: none;">
                <div class="complete-header">
                    <div class="success-icon">✓</div>
                    <h3 class="complete-title">Upload Successful!</h3>
                    <p class="complete-subtitle">Your files have been uploaded successfully</p>
                </div>
                <div class="complete-actions">
                    <button class="new-upload-btn" id="newUploadBtn">Start New Upload</button>
                    <button class="view-files-btn" id="viewFilesBtn">View Uploaded Files</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <p class="footer-text">© 2025 <a href="https://v4r.net/" target="_blank">V4R Team.</a> All rights reserved.</p>
        </div>
    </footer>

    <script src="./script.js"></script>
</body>

</html>