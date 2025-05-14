<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/student_login.php");
    exit;
}

$studentId = $_SESSION['student_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $extra = $_POST['extra_curricular'];
    $file = $_FILES['portfolio_file'];

    if ($file['error'] === 0) {
        $uploadDir = "../uploads/portfolios/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filePath = $uploadDir . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $filePath);

        $stmt = $pdo->prepare("INSERT INTO portfolio_entries (student_id, title, description, file_path, extra_curricular) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$studentId, $title, $desc, $filePath, $extra]);

        $message = "Portfolio entry uploaded successfully!";
    } else {
        $message = "File upload failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Portfolio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h3>Upload Portfolio Entry</h3>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded">
        <div class="mb-3">
            <label>Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description:</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label>Upload File (PDF/Image):</label>
            <input type="file" name="portfolio_file" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Extra-Curricular Activities:</label>
            <textarea name="extra_curricular" class="form-control" rows="2"></textarea>
        </div>
        <button class="btn btn-primary">Submit Portfolio</button>
    </form>
</body>
</html>
