<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Student Portfolio'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-graduation-cap me-2"></i>Student Portfolio
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto">
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../admin/dashboard.php">Admin Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../admin/manage_users.php">Manage Users</a>
                                </li>
                            <?php elseif (isTeacher()): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../teacher/dashboard.php">Teacher Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../teacher/enter_marks.php">Enter Marks</a>
                                </li>
                            <?php elseif (isStudent()): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../student/dashboard.php">My Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../student/view_marks.php">My Marks</a>
                                </li>
                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'parent'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../parent/dashboard.php">Parent Dashboard</a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <span class="d-inline d-lg-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../auth/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container my-4">