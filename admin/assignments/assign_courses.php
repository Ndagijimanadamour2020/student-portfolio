<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$teacherStmt = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name ASC");
$teachers = $teacherStmt->fetchAll(PDO::FETCH_ASSOC);

$courseStmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

$assignStmt = $pdo->query("
    SELECT ct.id, t.first_name, t.last_name, c.course_name
    FROM course_teacher ct
    JOIN teachers t ON ct.teacher_id = t.id
    JOIN courses c ON ct.course_id = c.id
    ORDER BY t.first_name ASC
");
$assignments = $assignStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Courses to Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            background-color: #f8f9fa;
        }
        .nav-link {
            color: white;
        }
        .nav-link:hover {
            background-color: #495057;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a href="../departments/departments.php" class="nav-link"><i class="fas fa-building me-2"></i>Departments</a></li>
        <li class="nav-item"><a href="../courses/courses.php" class="nav-link"><i class="fas fa-book me-2"></i>Courses</a></li>
        <li class="nav-item"><a href="assign_course_teacher.php" class="nav-link active"><i class="fas fa-link me-2"></i>Assign Course</a></li>
        <li class="nav-item"><a href="../grades/grades.php" class="nav-link"><i class="fas fa-chart-line me-2"></i>Grades</a></li>
        <li class="nav-item"><a href="../students/students.php" class="nav-link"><i class="fas fa-user-graduate me-2"></i>Students</a></li>
        <li class="nav-item"><a href="../parents/parents.php" class="nav-link"><i class="fas fa-users me-2"></i>Parents</a></li>
        <li class="nav-item"><a href="../teachers/teachers.php" class="nav-link"><i class="fas fa-chalkboard-teacher me-2"></i>Teachers</a></li>
        <li class="nav-item"><a href="../announcements/announcements.php" class="nav-link"><i class="fas fa-bullhorn me-2"></i>Announcements</a></li>
        <li class="nav-item"><a href="../users/manage_users.php" class="nav-link"><i class="fas fa-users-cog me-2"></i>Users</a></li>
        <li class="nav-item"><a href="../reports/term1_reports.php" class="nav-link"><i class="fas fa-file-alt me-2"></i>Term 1 Reports</a></li>
        <li class="nav-item"><a href="../reports/term2_reports.php" class="nav-link"><i class="fas fa-file-alt me-2"></i>Term 2 Reports</a></li>
        <li class="nav-item"><a href="../reports/term3_reports.php" class="nav-link"><i class="fas fa-file-alt me-2"></i>Term 3 Reports</a></li>
        <li class="nav-item mt-3"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h3 class="mb-4">Assign Courses to Teachers</h3>
    <form action="assign_course_action.php" method="POST" class="row g-3 mb-5">
        <div class="col-md-5">
            <label for="teacher_id" class="form-label">Teacher</label>
            <select name="teacher_id" id="teacher_id" class="form-select" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['id'] ?>">
                        <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label for="course_id" class="form-label">Course</label>
            <select name="course_id" id="course_id" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="assign_course" class="btn btn-primary w-100">Assign</button>
        </div>
    </form>

    <h4>Current Assignments</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Teacher</th>
                <th>Course</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?= htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']) ?></td>
                    <td><?= htmlspecialchars($assignment['course_name']) ?></td>
                    <td>
                        <a href="assign_course_action.php?action=delete&id=<?= $assignment['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
