<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Upload Image</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

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

    <script src="./script.js"></script>
</body>

</html>