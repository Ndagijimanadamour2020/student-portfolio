<?php
require_once '../config/database.php';

if (!isset($_GET['student_id'])) {
    die("Student ID required.");
}

$student_id = $_GET['student_id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT g.*, c.course_name FROM grades g
                       JOIN courses c ON g.course_id = c.id
                       WHERE g.student_id = ?");
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Printable Student Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="p-4">
    <h4>Student Grade Report</h4>
    <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
    <p><strong>Student ID:</strong> <?= $student['id'] ?></p>

    <table class="table table-bordered">
        <thead>
            <tr><th>Course</th><th>Term</th><th>Grade</th></tr>
        </thead>
        <tbody>
            <?php foreach ($grades as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['course_name']) ?></td>
                    <td><?= htmlspecialchars($g['term']) ?></td>
                    <td><?= htmlspecialchars($g['grade']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button class="btn btn-primary no-print" onclick="window.print()">Print Report</button>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
