<?php
$pdo = new PDO(
    "mysql:host=database-5020698827.webspace-host.com;dbname=dbs15789224;charset=utf8",
    "dbu629505",
    "Cyb3rS3c^r!tyPr0j3ct2026!"
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// View image by hash
if (isset($_GET['hash'])) {
    $row = $pdo->prepare("SELECT filename FROM images WHERE hash = ?");
    $row->execute([$_GET['hash']]);
    $img = $row->fetch();

    if ($img && file_exists(__DIR__ . '/uploads/' . $img['filename'])) {
        $ext = strtolower(pathinfo($img['filename'], PATHINFO_EXTENSION));
        $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        header("Content-Type: " . ($mime[$ext] ?? 'application/octet-stream'));
        readfile(__DIR__ . '/uploads/' . $img['filename']);
    } else {
        http_response_code(404);
        echo "Image not found.";
    }
    exit;
}

// Handle upload
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($file['size'] > 5 * 1024 * 1024) {
        $error = "File too large. Max 5MB.";
    } elseif (!in_array(mime_content_type($file['tmp_name']), $allowedMimes)) {
        $error = "Invalid file type. Only JPG, PNG, GIF and WEBP allowed.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $hash = md5(uniqid($filename, true));

        if (move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $filename)) {
            $pdo->prepare("INSERT INTO images (filename, hash) VALUES (?, ?)")->execute([$filename, $hash]);
            header("Location: index.php?uploaded=" . $hash);
            exit;
        } else {
            $error = "Upload failed. Check that uploads/ exists and is writable.";
        }
    }
}

$images = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Uploader</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; color: #111; }
        .success { color: green; }
        .error { color: red; }
        input[type="file"] { display: block; margin-bottom: 10px; }
        input[type="submit"] { padding: 6px 16px; cursor: pointer; }
        .item { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .hash { font-size: 0.75rem; color: #999; font-family: monospace; }
        .date { color: #999; font-size: 0.8rem; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Image Uploader</h2>

    <?php if (isset($_GET['uploaded'])): ?>
        <p class="success">Uploaded! — <a href="?hash=<?= htmlspecialchars($_GET['uploaded']) ?>" target="_blank">View image</a></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*">
        <input type="submit" value="Upload">
    </form>

    <h3>Uploaded images</h3>

    <?php if (empty($images)): ?>
        <p class="date">No images uploaded yet.</p>
    <?php else: ?>
        <?php foreach ($images as $img): ?>
            <div class="item">
                <div>
                    <div><?= htmlspecialchars($img['filename']) ?></div>
                    <div class="hash"><?= htmlspecialchars($img['hash']) ?></div>
                </div>
                <span class="date"><?= htmlspecialchars($img['uploaded_at']) ?></span>
                <a href="?hash=<?= htmlspecialchars($img['hash']) ?>" target="_blank">View →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>