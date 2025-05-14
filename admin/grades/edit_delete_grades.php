<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch grades with joined student, course, term, and assessment info
$query = "
    SELECT grades.id, grades.grade, grades.assessment_type, students.first_name, students.last_name, 
           courses.course_name, terms.term_name 
    FROM grades
    JOIN students ON grades.student_id = students.id
    JOIN courses ON grades.course_id = courses.id
    JOIN terms ON grades.term_id = terms.id
    ORDER BY students.last_name, courses.course_name, terms.term_name
";
$grades = $pdo->query($query)->fetchAll();

// Handle grade deletion
if (isset($_GET['delete'])) {
    $grade_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->execute([$grade_id]);
    $_SESSION['success'] = "Marks successfully deleted.";
    header("Location: edit_delete_grades.php");
    exit;
}

// Handle grade update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_grade'])) {
    $grade_id = $_POST['grade_id'];
    $grade = $_POST['grade'];
    $assessment_type = $_POST['assessment_type'];
    $stmt = $pdo->prepare("UPDATE grades SET grade = ?, assessment_type = ? WHERE id = ?");
    $stmt->execute([$grade, $assessment_type, $grade_id]);
    $_SESSION['success'] = "Marks successfully updated.";
    header("Location: edit_delete_grades.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit/Delete Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            padding: 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #575d63;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <?php include '../inc/sidebar.php'; ?>
</div>

<div class="main-content">
    <h3 class="mb-4">Manage Student Marks</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Term</th>
                <th>Marks</th>
                <th>Assessment Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($grades as $grade): ?>
            <tr>
                <td><?= $grade['id'] ?></td>
                <td><?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></td>
                <td><?= htmlspecialchars($grade['course_name']) ?></td>
                <td><?= htmlspecialchars($grade['term_name']) ?></td>
                <td><?= htmlspecialchars($grade['grade']) ?></td>
                <td><?= htmlspecialchars($grade['assessment_type']) ?></td>
                <td>
                    <!-- View button -->
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewGradeModal<?= $grade['id'] ?>">View</button>
                    <!-- Edit button -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editGradeModal<?= $grade['id'] ?>">Edit</button>
                    <!-- Delete button -->
                    <a href="?delete=<?= $grade['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this grade?');">Delete</a>
                </td>
            </tr>

            <!-- View Modal -->
            <div class="modal fade" id="viewGradeModal<?= $grade['id'] ?>" tabindex="-1" aria-labelledby="viewLabel<?= $grade['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewLabel<?= $grade['id'] ?>">Grade Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Student:</strong> <?= htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) ?></p>
                            <p><strong>Course:</strong> <?= htmlspecialchars($grade['course_name']) ?></p>
                            <p><strong>Term:</strong> <?= htmlspecialchars($grade['term_name']) ?></p>
                            <p><strong>Marks:</strong> <?= htmlspecialchars($grade['grade']) ?></p>
                            <p><strong>Assessment Type:</strong> <?= htmlspecialchars($grade['assessment_type']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Grade Modal -->
            <div class="modal fade" id="editGradeModal<?= $grade['id'] ?>" tabindex="-1" aria-labelledby="editLabel<?= $grade['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editLabel<?= $grade['id'] ?>">Edit Student Marks</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST">
                                <input type="hidden" name="grade_id" value="<?= $grade['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Marks:</label>
                                    <input type="number" name="grade" class="form-control" value="<?= $grade['grade'] ?>" required min="0" max="100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Assessment Type:</label>
                                    <select name="assessment_type" class="form-control" required>
                                    <option value="#" >Choose Assessment Type</option>
                                        <option value="Formative Assessment" <?= $grade['assessment_type'] == 'Formative Assessment' ? 'selected' : '' ?>>Formative Assessment</option>
                                        <option value="Comprehensive Assessment" <?= $grade['assessment_type'] == 'Exam' ? 'selected' : '' ?>>Comprehensive Assessment</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_grade" class="btn btn-primary">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
