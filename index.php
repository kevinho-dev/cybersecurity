<?php
$host = "database-5020698827.webspace-host.com";
$dbname = "dbs15789224";
$user = "dbu629505";
$pass = "Cyb3rS3c^r!tyPr0j3ct2026!";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

// ==========================================
// 1. IMAGE VIEWER MODE (Intercepts ?id=X requests)
// ==========================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $image = $stmt->fetch();

    if ($image) {
        $filePath = __DIR__ . '/uploads/' . $image['filename'];

        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp'
            ];

            $contentType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
            
            header("Content-Type: " . $contentType);
            readfile($filePath);
            exit; // Stops the rest of the file from rendering so no HTML corrupts the image data
        }
    }
    
    header("HTTP/1.0 404 Not Found");
    echo "Image not found.";
    exit;
}

// ==========================================
// 2. FILE UPLOAD HANDLING (Intercepts POST requests)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'];
    $uploadsDir = __DIR__ . '/uploads/';
    $filename = basename($file['name']);
    $destination = $uploadsDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $stmt = $pdo->prepare("INSERT INTO images (filename) VALUES (?)");
        $stmt->execute([$filename]);

        $lastId = $pdo->lastInsertId();

        // Redirect back to this same file with the uploaded ID in the URL
        header("Location: index.php?uploaded=" . urlencode($lastId));
        exit;
    } else {
        $error = "Upload failed. Make sure uploads/ exists and is writable (chmod 755).";
    }
}

// Fetch all entries for the gallery list
$images = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; }
        .success { color: green; }
        .error { color: red; }
        .image-link { font-weight: bold; text-decoration: none; color: #0066cc; }
        .image-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Upload Image</h2>

    <?php if (isset($_GET['uploaded'])): ?>
        <p class="success">
            Uploaded successfully! 
            — <a class="image-link" href="index.php?id=<?= htmlspecialchars($_GET['uploaded']) ?>" target="_blank">View Image #<?= htmlspecialchars($_GET['uploaded']) ?></a>
        </p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form action="index.php" method="post" enctype="multipart/form-data">
        <label for="image">Image:</label><br>
        <input type="file" id="image" name="image" accept="image/*">
        <br><br>
        <input type="submit" value="Upload">
    </form>

    <h3>Uploaded images</h3>
    <?php foreach ($images as $img): ?>
        <div style="margin-bottom: 10px;">
            <a class="image-link" href="index.php?id=<?= htmlspecialchars($img['id']) ?>" target="_blank">
                Image #<?= htmlspecialchars($img['id']) ?>
            </a> 
            — Uploaded at: <?= $img['uploaded_at'] ?>
        </div>
    <?php endforeach; ?>
</body>
</html>