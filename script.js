class FileUploadComponent {
    constructor() {
        this.uploadBox = document.getElementById("uploadBox");
        this.fileInput = document.getElementById("fileInput");
        this.filesPreview = document.getElementById("filesPreview");
        this.filesList = document.getElementById("filesList");
        this.uploadProgress = document.getElementById("uploadProgress");
        this.uploadComplete = document.getElementById("uploadComplete");
        this.addMoreBtn = document.getElementById("addMoreBtn");
        this.newUploadBtn = document.getElementById("newUploadBtn");
        this.viewFilesBtn = document.getElementById("viewFilesBtn");

        this.files = [];
        this.maxFileSize = 10 * 1024 * 1024; // 10MB
        this.allowedTypes = ["image/jpg", "image/jpeg", "image/png", "image/gif", "image/webp"];

        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Upload box events
        this.uploadBox.addEventListener("click", () => {
            this.fileInput.click();
        });

        this.fileInput.addEventListener("change", (e) => {
            this.handleFiles(e.target.files);
        });

        // Drag and drop events
        this.uploadBox.addEventListener("dragover", (e) => {
            e.preventDefault();
            this.uploadBox.classList.add("dragover");
        });

        this.uploadBox.addEventListener("dragleave", (e) => {
            e.preventDefault();
            this.uploadBox.classList.remove("dragover");
        });

        this.uploadBox.addEventListener("drop", (e) => {
            e.preventDefault();
            this.uploadBox.classList.remove("dragover");
            this.handleFiles(e.dataTransfer.files);
        });

        // Action buttons
        this.addMoreBtn.addEventListener("click", () => {
            this.fileInput.click();
        });

        this.newUploadBtn.addEventListener("click", () => {
            this.startNewUpload();
        });

        this.viewFilesBtn.addEventListener("click", () => {
            this.viewUploadedFiles();
        });

        // Copy from clipboard
        document.addEventListener("paste", (e) => {
            if (e.clipboardData && e.clipboardData.files.length > 0) {
                this.handleFiles(e.clipboardData.files);
            }
        });

        // Prevent default drag behaviors
        ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
            document.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });
    }

    handleFiles(fileList) {
        const newFiles = Array.from(fileList);

        newFiles.forEach((file) => {
            if (this.validateFile(file)) {
                this.addFile(file);
                const fileObj = this.files[this.files.length - 1];
                this.uploadFileToServer(fileObj);
            }
        });

        if (this.files.length > 0) {
            this.showPreview();
            this.simulateUpload();
        }
    }

    validateFile(file) {
        // Check file type
        if (!this.allowedTypes.includes(file.type)) {
            this.showError(`${file.name}: Only JPG, PNG, and GIF files are allowed.`);
            return false;
        }

        // Check file size
        if (file.size > this.maxFileSize) {
            this.showError(`${file.name}: File size must be less than 10MB.`);
            return false;
        }

        // Check if file already exists
        if (this.files.some((f) => f.name === file.name && f.size === file.size)) {
            this.showError(`${file.name}: File already selected.`);
            return false;
        }

        return true;
    }

    addFile(file) {
        const fileObj = {
            file: file,
            id: Date.now() + Math.random(),
            name: file.name,
            size: this.formatFileSize(file.size),
            status: "pending",
            progress: 0,
        };

        this.files.push(fileObj);
        this.renderFile(fileObj);
    }

    renderFile(fileObj) {
        const fileElement = document.createElement("div");
        fileElement.className = "file-item";
        fileElement.setAttribute("data-file-id", fileObj.id);

        // Create preview image
        const reader = new FileReader();
        reader.onload = (e) => {
            fileElement.innerHTML = `
                <img src="${e.target.result}" alt="${fileObj.name}" class="file-preview">
                <div class="file-info">
                    <div class="file-name">${fileObj.name}</div>
                    <div class="file-size">${fileObj.size}</div>
                </div>
                <div class="file-status">
                    <div class="status-icon status-uploading">⏳</div>
                </div>
                <div class="file-actions">
                    <button class="file-action delete" onclick="fileUpload.removeFile('${fileObj.id}')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(fileObj.file);

        this.filesList.appendChild(fileElement);
    }

    showPreview() {
        this.filesPreview.classList.add("show");
        this.addMoreBtn.style.display = "inline-block";
    }

    simulateUpload() {
        this.uploadBox.classList.add("uploading");
        let completedFiles = 0;
        const totalFiles = this.files.length;
        const uploadPromises = this.files.map((fileObj) => {
            return this.uploadFileToServer(fileObj).then(() => {
                completedFiles++;
                if (completedFiles === totalFiles) {
                    this.completeUpload();
                }
            });
        });
    }

    getOverallProgress() {
        if (!this.files.length) return 0;
        const total = this.files.reduce((sum, f) => sum + (f.progress || 0), 0);
        return total / this.files.length;
    }

    // uploadFile(fileObj, progressCallback) {
    //     let progress = 0;
    //     const fileElement = document.querySelector(`[data-file-id="${fileObj.id}"]`);

    //     const uploadInterval = setInterval(() => {
    //         progress += Math.random() * 15;
    //         if (progress >= 100) {
    //             progress = 100;
    //             clearInterval(uploadInterval);

    //             fileObj.status = "success";
    //             const statusIcon = fileElement.querySelector(".status-icon");
    //             statusIcon.className = "status-icon status-success";
    //             statusIcon.textContent = "✓";
    //         }

    //         progressCallback(progress);
    //     }, 100 + Math.random() * 200);
    // }

    async uploadFileToServer(fileObj) {
        const formData = new FormData();
        formData.append("file", fileObj.file);
        const fileElement = document.querySelector(`[data-file-id="${fileObj.id}"]`);
        return new Promise((resolve) => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "upload.php", true);
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    fileObj.progress = percent;
                    if (fileElement) {
                        const statusIcon = fileElement.querySelector(".status-icon");
                        statusIcon.textContent = percent + "%";
                        statusIcon.className = "status-icon status-uploading";
                    }
                    this.updateProgress(this.getOverallProgress());
                }
            };
            xhr.onload = () => {
                let result = {};
                try {
                    result = JSON.parse(xhr.responseText);
                } catch (e) {
                    result = { success: false, message: "Upload failed" };
                }
                if (result.success) {
                    fileObj.status = "success";
                    fileObj.url = result.url;
                    if (fileElement) {
                        const statusIcon = fileElement.querySelector(".status-icon");
                        statusIcon.className = "status-icon status-success";
                        statusIcon.textContent = "✓";
                    }
                    this.showFileUrl(fileObj);
                } else {
                    fileObj.status = "error";
                    if (fileElement) {
                        const statusIcon = fileElement.querySelector(".status-icon");
                        statusIcon.className = "status-icon status-error";
                        statusIcon.textContent = "✗";
                    }
                    this.showError(result.message || "Upload failed");
                }
                resolve();
            };
            xhr.onerror = () => {
                fileObj.status = "error";
                if (fileElement) {
                    const statusIcon = fileElement.querySelector(".status-icon");
                    statusIcon.className = "status-icon status-error";
                    statusIcon.textContent = "✗";
                }
                this.showError("Upload failed");
                resolve();
            };
            xhr.send(formData);
        });
    }

    showFileUrl(fileObj) {
        const fileElement = document.querySelector(`[data-file-id="${fileObj.id}"]`);
        if (fileObj.url && fileElement) {
            const infoDiv = fileElement.querySelector(".file-info");
            const urlDiv = document.createElement("div");
            urlDiv.className = "file-url";
            urlDiv.innerHTML = `<a href="${fileObj.url}" target="_blank">View file</a>`;
            infoDiv.appendChild(urlDiv);
        }
    }

    updateProgress(progress) {
        const progressBar = document.querySelector(".progress-bar");
        const progressText = document.querySelector(".progress-text");

        const circumference = 2 * Math.PI * 25;
        const offset = circumference - (progress / 100) * circumference;

        progressBar.style.strokeDashoffset = offset;
        progressText.textContent = Math.round(progress) + "%";
    }

    completeUpload() {
        setTimeout(() => {
            this.uploadBox.style.display = "none";
            this.uploadComplete.style.display = "block";

            const completeTitle = this.uploadComplete.querySelector(".complete-title");
            const completeSubtitle = this.uploadComplete.querySelector(".complete-subtitle");

            completeTitle.textContent = "Upload Successful!";
            completeSubtitle.textContent = `${this.files.length} file(s) uploaded successfully`;

            this.updateUploadedUrlList();
        }, 500);
    }

    startNewUpload() {
        // Reset all states
        this.files = [];

        // Clear files list
        this.filesList.innerHTML = "";

        // Reset displays
        this.uploadComplete.style.display = "none";
        this.uploadBox.style.display = "block";
        this.uploadBox.classList.remove("uploading", "success");
        this.filesPreview.classList.remove("show");
        this.addMoreBtn.style.display = "none";

        // Reset progress
        const progressBar = document.querySelector(".progress-bar");
        const progressText = document.querySelector(".progress-text");
        progressBar.style.strokeDashoffset = "157";
        progressText.textContent = "0%";

        // Re-setup file input
        this.fileInput.value = "";
    }

    showError(message) {
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-notification";
        errorDiv.style.animation = "slideInRight 0.3s ease";
        errorDiv.textContent = message;

        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.style.animation = "slideOutRight 0.3s ease";
            setTimeout(() => errorDiv.remove(), 300);
        }, 4000);
    }

    viewUploadedFiles() {
        // Show the uploaded files by hiding complete screen and showing files
        this.uploadComplete.style.display = "none";
        this.filesPreview.classList.add("show");
        this.addMoreBtn.style.display = "inline-block";

        // Update preview title
        const previewTitle = this.filesPreview.querySelector(".preview-title");
        previewTitle.textContent = "Uploaded Files";
    }

    formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";

        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }

    updateUploadedUrlList() {
        // Hiển thị lại danh sách url sau khi xoá hoặc upload
        const urlListDiv = this.uploadComplete.querySelector(".uploaded-url-list");
        if (urlListDiv) urlListDiv.remove();
        const div = document.createElement("div");
        div.className = "uploaded-url-list";
        div.style.marginTop = "24px";
        div.style.padding = "8px 0";
        div.style.borderTop = "1px solid #eee";
        const origin = window.location.origin + "/";
        this.files.forEach((fileObj, idx) => {
            if (fileObj.url) {
                const fullUrl = fileObj.url.startsWith("http") ? fileObj.url : origin + fileObj.url.replace(/^\/+/, "");
                div.innerHTML += `
                    <div style="margin-bottom:24px; background:transparent; border-radius:8px; box-shadow:0 2px 8px #eee; padding:16px;">
                        <div style="font-weight:600; margin-bottom:8px; color:#333;">Image ${idx + 1}</div>
                        <div style="margin-bottom:15px"><b>URL:</b> <span class="copy-text" data-copy="${fullUrl}" style="cursor:pointer; background:#48bb78; padding:2px 6px; border-radius:4px; text-decoration:none;">${fullUrl}</span></div>
                        <div style="margin-bottom:15px"><b>BBCode:</b> <span class="copy-text" data-copy="[img]${fullUrl}[/img]" style="cursor:pointer; background:#48bb78; padding:2px 6px; border-radius:4px;">[img]${fullUrl}[/img]</span></div>
                        <div style="margin-bottom:15px"><b>HTML:</b> <span class="copy-text" data-copy="<img src='${fullUrl}' alt='image'>" style="cursor:pointer; background:#48bb78; padding:2px 6px; border-radius:4px;">&lt;img src='${fullUrl}' alt='image'&gt;</span></div>
                        <div style="margin-bottom:15px"><b>Markdown:</b> <span class="copy-text" data-copy="![](${fullUrl})" style="cursor:pointer; background:#48bb78; padding:2px 6px; border-radius:4px;">![](${fullUrl})</span></div>
                    </div>
                `;
            }
        });
        this.uploadComplete.appendChild(div);

        // Thêm sự kiện copy cho các text
        setTimeout(() => {
            document.querySelectorAll(".copy-text").forEach((el) => {
                el.addEventListener("click", function () {
                    const val = this.getAttribute("data-copy");
                    navigator.clipboard.writeText(val).then(() => {
                        el.style.background = "#d4edda";
                        el.style.color = "#155724";
                        el.textContent = "Copied!";
                        setTimeout(() => {
                            el.textContent = val;
                            el.style.background = "";
                            el.style.color = "";
                        }, 1200);
                    });
                });
            });
        }, 100);
    }

    removeFile(fileId) {
        // Tìm fileObj để lấy url file
        const fileObj = this.files.find((f) => f.id == fileId);
        // Gửi yêu cầu xoá file trên server nếu có url
        if (fileObj && fileObj.url) {
            fetch("delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ url: fileObj.url }),
            });
        }

        // Xoá khỏi danh sách files
        this.files = this.files.filter((f) => f.id != fileId);
        const fileElement = document.querySelector(`[data-file-id="${fileId}"]`);

        if (fileElement) {
            fileElement.style.animation = "slideOut 0.3s ease forwards";
            setTimeout(() => {
                fileElement.remove();

                // Nếu không còn file nào, ẩn preview, nút thêm, trạng thái upload
                if (this.files.length === 0) {
                    this.filesPreview.classList.remove("show");
                    this.addMoreBtn.style.display = "none";
                    this.uploadBox.classList.remove("uploading");

                    // Nếu đang ở màn hình uploadComplete, xoá danh sách url và về giao diện upload file
                    if (this.uploadComplete.style.display === "block") {
                        const urlListDiv = this.uploadComplete.querySelector(".uploaded-url-list");
                        if (urlListDiv) urlListDiv.remove();
                        const completeSubtitle = this.uploadComplete.querySelector(".complete-subtitle");
                        if (completeSubtitle) completeSubtitle.textContent = "No files left.";
                        // Quay về giao diện upload file
                        this.uploadComplete.style.display = "none";
                        this.uploadBox.style.display = "block";
                        this.uploadBox.classList.remove("uploading", "success");
                        const progressBar = document.querySelector(".progress-bar");
                        const progressText = document.querySelector(".progress-text");
                        if (progressBar) progressBar.style.strokeDashoffset = "157";
                        if (progressText) progressText.textContent = "0%";
                        this.fileInput.value = "";
                    }
                } else {
                    // Nếu đang ở màn hình uploadComplete, cập nhật lại danh sách url
                    if (this.uploadComplete.style.display === "block") {
                        this.updateUploadedUrlList();
                        const completeSubtitle = this.uploadComplete.querySelector(".complete-subtitle");
                        if (completeSubtitle) completeSubtitle.textContent = `${this.files.length} file(s) uploaded successfully`;
                    }
                }
            }, 300);
        }
    }
}

// Add slide animations for notifications
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
`;

document.head.appendChild(style);

// Initialize the component
let fileUpload;
document.addEventListener("DOMContentLoaded", () => {
    fileUpload = new FileUploadComponent();
});

// Export for potential module use
if (typeof module !== "undefined" && module.exports) {
    module.exports = FileUploadComponent;
}
