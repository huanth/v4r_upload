<?php
/**
 * stats.php — Upload statistics dashboard with server-side authentication.
 * 
 * Displays uploaded images list with pagination and provides
 * authenticated bulk delete functionality.
 */

session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Bulk delete password — stored server-side only
const BULK_DELETE_PASSWORD = 'admin2026';

$dir = __DIR__ . '/uploads';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $perPage;

// Handle bulk delete (server-side password check)
$deleteMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $deleteMessage = '<div class="delete-msg error">Invalid CSRF token.</div>';
    } elseif (!isset($_POST['password']) || $_POST['password'] !== BULK_DELETE_PASSWORD) {
        $deleteMessage = '<div class="delete-msg error">Incorrect password.</div>';
    } else {
        $deleted = 0;
        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    $filePath = $dir . '/' . $file;
                    if (is_file($filePath)) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            if (unlink($filePath)) {
                                $deleted++;
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        $deleteMessage = '<div class="delete-msg success">Deleted ' . $deleted . ' images. Reloading page after 3s...</div>';
        // Regenerate CSRF token after successful action
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Reload page after 3s second
        echo '<script>setTimeout(function(){ window.location.href = "stats.php"; }, 3000);</script>';
    }
}

// Scan uploads directory
$totalFiles = 0;
$totalSize = 0;
$imagesPage = [];
$currentIndex = 0;

if (is_dir($dir)) {
    $dh = opendir($dir);
    if ($dh) {
        while (($file = readdir($dh)) !== false) {
            $filePath = $dir . '/' . $file;
            if (is_file($filePath)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $totalFiles++;
                    $size = filesize($filePath);
                    $totalSize += $size;
                    if ($currentIndex >= $start && count($imagesPage) < $perPage) {
                        $imagesPage[] = [
                            'name' => $file,
                            'size' => $size,
                            'url'  => 'uploads/' . $file,
                        ];
                    }
                    $currentIndex++;
                }
            }
        }
        closedir($dh);
    }
}

function formatSize($bytes)
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 2) . ' MB';
    return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
}

$totalPages = $totalFiles ? ceil($totalFiles / $perPage) : 1;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Upload Statistics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stats-box {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 700px;
            margin: 3rem auto;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.12);
        }

        .stats-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #764ba2;
            letter-spacing: 1px;
            text-align: center;
        }

        .stats-list {
            margin-bottom: 2.5rem;
            display: flex;
            gap: 2.5rem;
            justify-content: center;
            list-style: none;
        }

        .stats-list li {
            font-size: 1.15rem;
            margin-bottom: 0.5rem;
            background: #f8f8fc;
            border-radius: 8px;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px #eee;
            color: #333;
            min-width: 180px;
            text-align: center;
        }

        .images-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px #eee;
        }

        .images-table th,
        .images-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
        }

        .images-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
        }

        .images-table td a {
            color: #667eea;
            text-decoration: underline;
            font-weight: 500;
        }

        .images-table tr:last-child td {
            border-bottom: none;
        }

        .images-table td {
            font-size: 0.98rem;
        }

        .no-images {
            text-align: center;
            color: #764ba2;
            font-size: 1.2rem;
            margin-top: 2rem;
            background: #f8f8fc;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px #eee;
        }

        /* Bulk delete */
        .bulk-delete-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .btn-delete-all {
            background: #f5576c;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            box-shadow: 0 2px 8px #eee;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete-all:hover {
            background: #e04458;
            transform: translateY(-1px);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.25);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(118, 75, 162, 0.3);
            max-width: 350px;
            margin: auto;
            text-align: center;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #764ba2;
            margin-bottom: 1rem;
        }

        .modal-input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #eee;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .modal-error {
            color: #f5576c;
            font-size: 0.98rem;
            margin-bottom: 1rem;
            display: none;
        }

        .btn-confirm {
            background: #764ba2;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            margin-right: 8px;
            cursor: pointer;
        }

        .btn-cancel {
            background: #eee;
            color: #764ba2;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            cursor: pointer;
        }

        /* Delete messages */
        .delete-msg {
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
        }

        .delete-msg.error {
            color: #f5576c;
            background: #fff5f5;
        }

        .delete-msg.success {
            color: #48bb78;
            background: #f0fff4;
        }

        /* Pagination */
        .pagination {
            margin: 2rem 0;
            text-align: center;
        }

        .page-link {
            display: inline-block;
            margin: 0 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px #eee;
            transition: all 0.3s ease;
        }

        .page-link--default {
            background: #f8f8fc;
            color: #764ba2;
        }

        .page-link--active {
            background: #764ba2;
            color: #fff;
        }

        .page-ellipsis {
            margin: 0 6px;
            color: #aaa;
        }

        @media (max-width: 700px) {
            .stats-box {
                padding: 1rem;
            }

            .stats-title {
                font-size: 1.5rem;
            }

            .stats-list li {
                padding: 0.5rem 1rem;
                min-width: 120px;
            }

            .images-table th,
            .images-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>

<body>
    <div class="stats-box">
        <div class="stats-title">Upload Statistics</div>
        <ul class="stats-list">
            <li><b>Total images:</b> <?php echo $totalFiles; ?></li>
            <li><b>Total size:</b> <?php echo formatSize($totalSize); ?></li>
        </ul>

        <?php echo $deleteMessage; ?>

        <?php if ($totalFiles > 0) { ?>
            <div class="bulk-delete-section">
                <button type="button" id="bulkDeleteBtn" class="btn-delete-all">Delete All Images</button>
            </div>

            <!-- Password modal -->
            <div class="modal-overlay" id="passwordModal">
                <div class="modal-box">
                    <div class="modal-title">Enter password to delete all images</div>
                    <form id="bulkDeleteForm" method="post" action="stats.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="delete_all" value="1">
                        <input type="password" name="password" id="deletePassword" class="modal-input" placeholder="Password" autocomplete="off">
                        <div class="modal-error" id="passwordError"></div>
                        <button type="submit" class="btn-confirm">Confirm</button>
                        <button type="button" id="cancelDeleteBtn" class="btn-cancel">Cancel</button>
                    </form>
                </div>
            </div>

            <script>
                document.getElementById('bulkDeleteBtn').onclick = function () {
                    document.getElementById('passwordModal').style.display = 'flex';
                    document.getElementById('deletePassword').value = '';
                    document.getElementById('passwordError').style.display = 'none';
                };
                document.getElementById('cancelDeleteBtn').onclick = function () {
                    document.getElementById('passwordModal').style.display = 'none';
                };
            </script>

            <table class="images-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imagesPage as $i => $img) { ?>
                        <tr>
                            <td><?php echo $start + $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($img['name']); ?></td>
                            <td><?php echo formatSize($img['size']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($img['url']); ?>" target="_blank">View</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1) { ?>
                <div class="pagination">
                    <?php
                    $maxLinks = 1;
                    $startPage = max(1, $page - $maxLinks);
                    $endPage = min($totalPages, $page + $maxLinks);

                    if ($startPage > 1) {
                        echo '<a href="?page=1" class="page-link page-link--default">1</a>';
                        if ($startPage > 2) echo '<span class="page-ellipsis">...</span>';
                    }
                    for ($p = $startPage; $p <= $endPage; $p++) {
                        $activeClass = ($p == $page) ? 'page-link--active' : 'page-link--default';
                        echo '<a href="?page=' . $p . '" class="page-link ' . $activeClass . '">' . $p . '</a>';
                    }
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span class="page-ellipsis">...</span>';
                        echo '<a href="?page=' . $totalPages . '" class="page-link page-link--default">' . $totalPages . '</a>';
                    }
                    ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="no-images">No images uploaded yet.</div>
        <?php } ?>
    </div>
</body>

</html>