<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: post_announcement.php");
    exit;
}

// Fetch announcement
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->execute([$id]);
$announcement = $stmt->fetch();

if (!$announcement) {
    header("Location: post_announcement.php");
    exit;
}

// Update announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $target_role = $_POST['target_role'];

    $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, target_role = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $content, $target_role, $id]);

    header("Location: post_announcement.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3>Edit Announcement</h3>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($announcement['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Message</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($announcement['content']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Target Role</label>
                <select name="target_role" class="form-select" required>
                    <option value="All" <?= $announcement['target_role'] === 'All' ? 'selected' : '' ?>>All</option>
                    <option value="Admin" <?= $announcement['target_role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Student" <?= $announcement['target_role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                    <option value="Teacher" <?= $announcement['target_role'] === 'Teacher' ? 'selected' : '' ?>>Teacher</option>
                </select>
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="post_announcement.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
