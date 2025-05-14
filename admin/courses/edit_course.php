<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['course_name']);
    $code = trim($_POST['course_code']);
    $dept_id = (int)$_POST['department_id'];

    $update = $pdo->prepare("UPDATE courses SET course_name=?, course_code=?, department_id=? WHERE id=?");
    $update->execute([$name, $code, $dept_id, $id]);

    header("Location: courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Edit Course</h3>
    <form method="POST">
        <div class="mb-3">
            <label>Course Name</label>
            <input type="text" name="course_name" class="form-control" value="<?= htmlspecialchars($course['course_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Course Code</label>
            <input type="text" name="course_code" class="form-control" value="<?= htmlspecialchars($course['course_code']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Department</label>
            <select name="department_id" class="form-select">
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $course['department_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="courses.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
