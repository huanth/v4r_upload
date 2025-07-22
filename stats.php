<?php
// stats.php - Image upload statistics
$dir = __DIR__ . '/uploads';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $perPage;

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
                    // Chỉ lưu file thuộc trang hiện tại
                    if ($currentIndex >= $start && count($imagesPage) < $perPage) {
                        $imagesPage[] = [
                            'name' => $file,
                            'size' => $size,
                            'url' => 'uploads/' . $file
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
<html>

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
        <?php if ($totalFiles > 0) { ?>
            <form method="post" onsubmit="return confirm('Are you sure you want to delete ALL images?');" style="text-align:center; margin-bottom:2rem;">
                <button type="submit" name="delete_all" style="background:#f5576c; color:#fff; font-weight:600; border:none; border-radius:8px; padding:0.75rem 2rem; font-size:1rem; box-shadow:0 2px 8px #eee; cursor:pointer;">Delete All Images</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
                $deleted = 0;
                $dh = opendir($dir);
                if ($dh) {
                    while (($file = readdir($dh)) !== false) {
                        $filePath = $dir . '/' . $file;
                        if (is_file($filePath)) {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                if (unlink($filePath)) $deleted++;
                            }
                        }
                    }
                    closedir($dh);
                }
                echo '<div style="color:#f5576c; font-weight:600; text-align:center; margin-bottom:1rem;">Deleted ' . $deleted . ' images. Please refresh the page.</div>';
            }
            ?>
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
                <div style="margin:2rem 0; text-align:center;">
                    <?php
                    $maxLinks = 1; // số link hiển thị tối đa gần trang hiện tại
                    $startPage = max(1, $page - $maxLinks);
                    $endPage = min($totalPages, $page + $maxLinks);
                    if ($startPage > 1) {
                        echo '<a href="?page=1" style="display:inline-block; margin:0 6px; padding:8px 16px; border-radius:6px; background:#f8f8fc; color:#764ba2; font-weight:600; text-decoration:none; box-shadow:0 2px 8px #eee;">1</a>';
                        if ($startPage > 2) echo '<span style="margin:0 6px; color:#aaa;">...</span>';
                    }
                    for ($p = $startPage; $p <= $endPage; $p++) {
                        echo '<a href="?page=' . $p . '" style="display:inline-block; margin:0 6px; padding:8px 16px; border-radius:6px; background:' . ($p == $page ? '#764ba2' : '#f8f8fc') . '; color:' . ($p == $page ? '#fff' : '#764ba2') . '; font-weight:600; text-decoration:none; box-shadow:0 2px 8px #eee;">' . $p . '</a>';
                    }
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span style="margin:0 6px; color:#aaa;">...</span>';
                        echo '<a href="?page=' . $totalPages . '" style="display:inline-block; margin:0 6px; padding:8px 16px; border-radius:6px; background:#f8f8fc; color:#764ba2; font-weight:600; text-decoration:none; box-shadow:0 2px 8px #eee;">' . $totalPages . '</a>';
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