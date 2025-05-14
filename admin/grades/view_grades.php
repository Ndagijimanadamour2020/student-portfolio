<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch all grades with student, course, and term info
$stmt = $pdo->query("
    SELECT 
        g.id, g.grade, g.assessment_type,
        s.first_name, s.last_name,
        c.course_name,
        t.term_name
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN courses c ON g.course_id = c.id
    JOIN terms t ON g.term_id = t.id
    ORDER BY g.id DESC
");
$grades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Grades</title>
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
        <h3 class="mb-4">All Submitted Grades</h3>

        <?php if (count($grades) === 0): ?>
            <div class="alert alert-warning">No grades recorded yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Term</th>
                            <th>Assessment Type</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $index => $grade): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></td>
                                <td><?= htmlspecialchars($grade['course_name']) ?></td>
                                <td><?= htmlspecialchars($grade['term_name']) ?></td>
                                <td><?= htmlspecialchars($grade['assessment_type']) ?></td>
                                <td><?= htmlspecialchars($grade['grade']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
