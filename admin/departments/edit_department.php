<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: departments.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->execute([$id]);
$department = $stmt->fetch();

if (!$department) {
    header("Location: departments.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name']);
    if (!empty($newName)) {
        $updateStmt = $pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
        $updateStmt->execute([$newName, $id]);
        header("Location: departments.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">

    <style>
        .sidebar {
            width: 220px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #1e1e2f;
            color: white;
            padding: 20px 10px;
        }
        .main-content {
            margin-left: 220px;
            width: calc(100% - 220px);
            padding: 40px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../inc/sidebar.php'; ?>

    <div class="main-content">
        <h3>Edit Department</h3>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="name" class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($department['name']) ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="departments.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
