<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch departments
$deptStmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses
$query = "SELECT c.*, d.name AS department_name FROM courses c
          JOIN departments d ON c.department_id = d.id
          ORDER BY c.course_name ASC";
$stmt = $pdo->query($query);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            flex-shrink: 0;
            background-color: #343a40;
            color: white;
        }
        .main {
            flex-grow: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<?php include '../inc/sidebar.php'; ?>

<div class="main">
    <h3>Courses</h3>
    <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Course</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Code</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                    <td><?= htmlspecialchars($course['department_name']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $course['id'] ?>">Edit</button>
                        <a href="course_actions.php?action=delete&id=<?= $course['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this course?')">Delete</a>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $course['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $course['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="course_actions.php" method="POST" class="modal-content">
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Course</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label>Course Name</label>
                                <input type="text" name="course_name" class="form-control mb-2" value="<?= htmlspecialchars($course['course_name']) ?>" required>

                                <label>Course Code</label>
                                <input type="text" name="course_code" class="form-control mb-2" value="<?= htmlspecialchars($course['course_code']) ?>" required>

                                <label>Department</label>
                                <select name="department_id" class="form-control" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $course['department_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_course" class="btn btn-success">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="course_actions.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Course Name</label>
                <input type="text" name="course_name" class="form-control mb-2" required>

                <label>Course Code</label>
                <input type="text" name="course_code" class="form-control mb-2" required>

                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_course" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
