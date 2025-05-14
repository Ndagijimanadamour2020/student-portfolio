<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'];
    $academic_year = $_POST['academic_year'];

    // Handle logo upload
    $logo_path = $settings['logo_path'];
    if (!empty($_FILES['logo']['name'])) {
        $filename = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], "../uploads/$filename");
        $logo_path = "uploads/$filename";
    }

    $stmt = $pdo->prepare("UPDATE settings SET site_title = ?, academic_year = ?, logo_path = ? WHERE id = ?");
    $stmt->execute([$site_title, $academic_year, $logo_path, $settings['id']]);

    header("Location: site_settings.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3>Site Settings</h3>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Settings updated successfully!</div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Site Title</label>
                <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($settings['site_title']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Academic Year</label>
                <input type="text" name="academic_year" class="form-control" value="<?= htmlspecialchars($settings['academic_year']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Site Logo</label><br>
                <?php if ($settings['logo_path']): ?>
                    <img src="../<?= $settings['logo_path'] ?>" alt="Logo" height="60"><br>
                <?php endif; ?>
                <input type="file" name="logo" class="form-control mt-2">
            </div>
            <button class="btn btn-primary">Update Settings</button>
        </form>
    </div>
</body>
</html>
