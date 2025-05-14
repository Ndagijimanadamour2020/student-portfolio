<?php 
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; }
        .main-content { flex-grow: 1; padding: 20px; background: #f8f9fa; }
    </style>
</head>
<body>

    <?php include 'inc/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
            <p class="lead">This is your admin dashboard for managing the online student portfolio system.</p>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <i class="fas fa-building fa-2x float-end"></i>
                            <h6>Departments</h6>
                            <p><a href="departments/departments.php" class="text-white text-decoration-none">Manage Departments</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <i class="fas fa-book fa-2x float-end"></i>
                            <h6>Courses</h6>
                            <p><a href="courses/courses.php" class="text-white text-decoration-none">Manage Courses</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-info">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x float-end"></i>
                            <h6>Grades</h6>
                            <p><a href="grades/input_grades.php" class="text-white text-decoration-none">Manage Grades</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-warning">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-2x float-end"></i>
                            <h6>Students</h6>
                            <p><a href="students/students.php" class="text-white text-decoration-none">Manage Students</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-secondary">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x float-end"></i>
                            <h6>Parents</h6>
                            <p><a href="parents/parents.php" class="text-white text-decoration-none">Manage Parents</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-dark">
                        <div class="card-body">
                            <i class="fas fa-chalkboard-teacher fa-2x float-end"></i>
                            <h6>Teachers</h6>
                            <p><a href="teachers/teachers.php" class="text-white text-decoration-none">Manage Teachers</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-danger">
                        <div class="card-body">
                            <i class="fas fa-bullhorn fa-2x float-end"></i>
                            <h6>Announcements</h6>
                            <p><a href="announcements/announcements.php" class="text-white text-decoration-none">View Announcements</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <i class="fas fa-users-cog fa-2x float-end"></i>
                            <h6>Users</h6>
                            <p><a href="users/manage_users.php" class="text-dark text-decoration-none">Manage Users</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6><i class="fas fa-file-alt me-2"></i>Term 1 Reports</h6>
                            <a href="terms/term1_reports.php" class="btn btn-outline-primary btn-sm">View Reports</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6><i class="fas fa-file-alt me-2"></i>Term 2 Reports</h6>
                            <a href="terms/term2_reports.php" class="btn btn-outline-success btn-sm">View Reports</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6><i class="fas fa-file-alt me-2"></i>Term 3 Reports</h6>
                            <a href="terms/term3_reports.php" class="btn btn-outline-warning btn-sm">View Reports</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
