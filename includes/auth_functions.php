<?php
/**
 * Authentication Functions for Student Portfolio System
 * Includes support for Admin, Teacher, Student, and Parent roles
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function isParent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'parent';
}

function redirectIfNotLoggedIn($redirect_url = '../auth/login.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit();
    }
}

function redirectBasedOnRole() {
    if (isLoggedIn()) {
        $redirect_url = '../dashboard.php'; // Default fallback
        
        if (isAdmin()) {
            $redirect_url = '../admin/dashboard.php';
        } elseif (isTeacher()) {
            $redirect_url = '../teacher/dashboard.php';
        } elseif (isStudent()) {
            $redirect_url = '../student/dashboard.php';
        } elseif (isParent()) {
            $redirect_url = '../parent/dashboard.php';
        }

        // Prevent redirect loops
        if (basename($_SERVER['PHP_SELF']) !== basename($redirect_url)) {
            header("Location: $redirect_url");
            exit();
        }
    }
}

function requireRole($required_roles) {
    if (!isLoggedIn()) {
        redirectIfNotLoggedIn();
    }

    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }

    $has_role = false;
    foreach ($required_roles as $role) {
        switch ($role) {
            case 'admin':
                $has_role = $has_role || isAdmin();
                break;
            case 'teacher':
                $has_role = $has_role || isTeacher();
                break;
            case 'student':
                $has_role = $has_role || isStudent();
                break;
            case 'parent':
                $has_role = $has_role || isParent();
                break;
        }
    }

    if (!$has_role) {
        header('HTTP/1.0 403 Forbidden');
        include '../errors/403.php';
        exit();
    }
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAuthorizedForStudent($student_id, $pdo) {
    if (isAdmin()) {
        return true;
    }

    if (isTeacher()) {
        // Teachers can access their own students
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_students 
                              WHERE teacher_id = (SELECT id FROM teachers WHERE user_id = ?) 
                              AND student_id = ?");
        $stmt->execute([$_SESSION['user_id'], $student_id]);
        return $stmt->fetchColumn() > 0;
    }

    if (isParent()) {
        // Parents can access their own children
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM parent_student 
                              WHERE parent_id = (SELECT id FROM parents WHERE user_id = ?) 
                              AND student_id = ?");
        $stmt->execute([$_SESSION['user_id'], $student_id]);
        return $stmt->fetchColumn() > 0;
    }

    if (isStudent()) {
        // Students can access their own data
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students 
                              WHERE user_id = ? AND id = ?");
        $stmt->execute([$_SESSION['user_id'], $student_id]);
        return $stmt->fetchColumn() > 0;
    }

    return false;
}
?>