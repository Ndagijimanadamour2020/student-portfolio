<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch students, courses, and terms
$students = $pdo->query("SELECT id, first_name, last_name FROM students ORDER BY id ASC")->fetchAll();
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC")->fetchAll();
$terms = $pdo->query("SELECT id, term_name FROM terms ORDER BY id ASC")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_grade'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $term_id = $_POST['term_id'];
    $grade = $_POST['grade'];
    $assessment_type = $_POST['assessment_type'];

    $stmt = $pdo->prepare("INSERT INTO grades (student_id, course_id, term_id, grade, assessment_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $course_id, $term_id, $grade, $assessment_type]);

    $_SESSION['success'] = "Grade successfully recorded.";
    header("Location: input_grades.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Input Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php include '../inc/sidebar.php'; ?>

    <div class="main-content">
        <h3 class="mb-4">Enter Student Grades</h3>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="" disabled selected>-- Choose Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="" disabled selected>-- Choose Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Term</label>
                <select name="term_id" class="form-select" required>
                    <option value="" disabled selected>-- Choose Term --</option>
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= $term['id'] ?>"><?= htmlspecialchars($term['term_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Assessment Type</label>
                <select name="assessment_type" class="form-select" required>
                    <option value="" disabled selected>-- Choose Assessment Type --</option>
                    <option value="Formative Assessment">Formative Assessment</option>
                    <option value="Comprehensive Assessment">Comprehensive Assessment</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Grade</label>
                <input type="number" name="grade" class="form-control" min="0" max="100" required>
            </div>

            <button type="submit" name="submit_grade" class="btn btn-success">Submit Marks</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
