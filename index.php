<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config/database.php';
require_once 'includes/auth_functions.php';

// Redirect logged-in users to their respective dashboards
redirectBasedOnRole();

// Initialize variables
$error = '';
$success = '';
$login_success = isset($_GET['login']) && $_GET['login'] === 'success';
$logout_success = isset($_GET['logout']) && $_GET['logout'] === 'success';

// Fetch some statistics for the public view
try {
    $pdo = getPDO();
    
    // Get counts
    $stmt = $pdo->query("SELECT COUNT(*) as total_students FROM students");
    $total_students = $stmt->fetch()['total_students'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_teachers FROM teachers");
    $total_teachers = $stmt->fetch()['total_teachers'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_parents FROM parents");
    $total_parents = $stmt->fetch()['total_parents'];
    
    // Get recent announcements
    $stmt = $pdo->query("SELECT title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 3");
    $announcements = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "We're experiencing technical difficulties. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portfolio System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/mains.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>Online Student Portfolio Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="auth/login.php?role=student">Student Login</a></li>
                            <li><a class="dropdown-item" href="auth/login.php?role=teacher">Teacher Login</a></li>
                            <li><a class="dropdown-item" href="auth/login.php?role=parent">Parent Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-warning" href="auth/admin_login.php?role=admin">Admin Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/register.php"><i class="fas fa-user-plus me-1"></i> Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero bg-light py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Online Student Portfolio Management System</h1>
            <p class="lead mb-4">Track student progress, manage assessments, and enhance learning outcomes</p>
            <div class="d-flex justify-content-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-primary btn-lg px-4 dropdown-toggle" type="button" id="loginMenu" data-bs-toggle="dropdown">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="auth/login.php?role=student">Student Login</a></li>
                        <li><a class="dropdown-item" href="auth/login.php?role=teacher">Teacher Login</a></li>
                        <li><a class="dropdown-item" href="auth/login.php?role=parent">Parent Login</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning fw-bold" href="auth/admin_login.php?role=admin">Admin Login</a></li>
                    </ul>
                </div>
                <a href="auth/register.php" class="btn btn-outline-primary btn-lg px-4">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Features Section -->
            <section class="mb-5">
                <h2 class="text-center mb-4">Key Features</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="fas fa-chart-line fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Performance Tracking</h5>
                                <p class="card-text">Monitor student progress with detailed analytics and visual reports.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="fas fa-clipboard-check fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Assessment Management</h5>
                                <p class="card-text">Record and manage formative and comprehensive assessments.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="fas fa-users fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Parent Portal</h5>
                                <p class="card-text">Parents can track their child's performance and communicate with teachers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Stats Section -->
            <section class="mb-5 py-4 bg-light rounded-3">
                <div class="container">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="display-4 fw-bold text-primary"><?php echo $total_students; ?></div>
                            <p class="mb-0 text-muted">Students</p>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="display-4 fw-bold text-primary"><?php echo $total_teachers; ?></div>
                            <p class="mb-0 text-muted">Teachers</p>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="display-4 fw-bold text-primary"><?php echo $total_parents; ?></div>
                            <p class="mb-0 text-muted">Parents</p>
                        </div>
                        <div class="col-md-3">
                            <div class="display-4 fw-bold text-primary">24/7</div>
                            <p class="mb-0 text-muted">Access</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Announcements Section -->
            <?php if (!empty($announcements)): ?>
            <section class="mb-5">
                <h2 class="text-center mb-4">Latest Announcements</h2>
                <div class="row g-4">
                    <?php foreach ($announcements as $announcement): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($announcement['content'], 0, 100)); ?>...</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <small class="text-muted">Posted on <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Call to Action -->
            <section class="text-center py-4 bg-primary text-white rounded-3">
                <h3 class="mb-4">Ready to get started?</h3>
                <a href="auth/register.php" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-user-plus me-2"></i>Create an Account
                </a>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Online Student Portfolio Management System</h5>
                    <p>A comprehensive platform for tracking and managing student assessments and performance.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="auth/login.php" class="text-white">Login</a></li>
                        <li><a href="auth/register.php" class="text-white">Register</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@studentportfolio.edu</li>
                        <li><i class="fas fa-phone me-2"></i> (+250) 784710788</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Online Student Portfolio Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>