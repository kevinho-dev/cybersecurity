<?php
$host = "database-5020698827.webspace-host.com";
$dbname = "dbs15789224";
$user = "dbu629505";
$pass = "Cyb3rS3c^r!tyPr0j3ct2026!";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'];
    $uploadsDir = __DIR__ . '/uploads/';
    $filename = basename($file['name']);
    $destination = $uploadsDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $stmt = $pdo->prepare("INSERT INTO images (filename) VALUES (?)");
        $stmt->execute([$filename]);

        // Redirect so a page refresh doesn't re-submit the form
        header("Location: index.php?uploaded=" . urlencode($filename));
        exit;
    } else {
        $error = "Upload failed. Make sure uploads/ exists and is writable (chmod 755).";
    }
}

$images = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; }
        img { max-width: 200px; margin: 8px; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Upload Image</h2>

    <?php if (isset($_GET['uploaded'])): ?>
        <p class="success">Uploaded: <?= htmlspecialchars($_GET['uploaded']) ?></p>
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
        <div>
            <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" alt="<?= htmlspecialchars($img['filename']) ?>">
            <p><?= htmlspecialchars($img['filename']) ?> — <?= $img['uploaded_at'] ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>