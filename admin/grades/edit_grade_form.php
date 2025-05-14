<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: edit_delete_grades.php");
    exit;
}

$id = $_GET['id'];

// Fetch grade
$stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
$stmt->execute([$id]);
$grade = $stmt->fetch();

if (!$grade) {
    header("Location: edit_delete_grades.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newGrade = $_POST['grade'];
    $pdo->prepare("UPDATE grades SET grade = ? WHERE id = ?")->execute([$newGrade, $id]);
    $_SESSION['success'] = "Grade updated successfully.";
    header("Location: edit_delete_grades.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Grade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Edit Grade</h3>
    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Grade</label>
            <input type="number" name="grade" class="form-control" min="0" max="100" value="<?= $grade['grade'] ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Update Grade</button>
        <a href="edit_delete_grades.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
