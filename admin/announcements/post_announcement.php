<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Insert announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $target = $_POST['target_role'];

    $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target_role, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $content, $target, $_SESSION['admin_id']]);
    header("Location: post_announcement.php?success=1");
    exit;
}

// Fetch announcements
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">

    <?php include '../inc/sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        <h3>📢 Announcements</h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Announcement posted successfully!</div>
        <?php endif; ?>

        <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addModal">
            Post Announcement
        </button>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Target</th>
                    <th>Posted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $ann): ?>
                    <tr>
                        <td><?= htmlspecialchars($ann['title']) ?></td>
                        <td><?= htmlspecialchars($ann['target_role']) ?></td>
                        <td><?= date("Y-m-d H:i", strtotime($ann['created_at'])) ?></td>
                        <td>
                            <a href="edit_announcement.php?id=<?= $ann['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="announcement_actions.php?action=delete&id=<?= $ann['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="post_announcement.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Message</label>
                    <textarea name="content" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Target Role</label>
                    <select name="target_role" class="form-select" required>
                        <option value="All">All</option>
                        <option value="Admin">Admin</option>
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button name="add_announcement" type="submit" class="btn btn-primary">Post</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
