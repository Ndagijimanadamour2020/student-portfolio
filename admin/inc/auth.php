<?php
session_start();

// Redirect to login if not authenticated
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Check if user has required role (for role-based access control)
function requireRole($requiredRole) {
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        header("HTTP/1.1 403 Forbidden");
        die("You don't have permission to access this page");
    }
}

// CSRF protection for forms
function csrf_token() {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitize_input($data) {
    if(is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Secure file upload handling
function handle_upload($file, $allowed_types, $max_size = 1048576) {
    $errors = [];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Check for upload errors
    if($file_error !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error: " . $file_error;
        return [false, $errors];
    }
    
    // Check file size
    if($file_size > $max_size) {
        $errors[] = "File is too large (max " . ($max_size/1024/1024) . "MB allowed)";
    }
    
    // Check file type
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if(!in_array($file_ext, $allowed_types)) {
        $errors[] = "File type not allowed (allowed: " . implode(', ', $allowed_types) . ")";
    }
    
    if(!empty($errors)) {
        return [false, $errors];
    }
    
    // Generate unique filename
    $new_filename = uniqid('', true) . '.' . $file_ext;
    $upload_path = '../../assets/uploads/' . $new_filename;
    
    if(move_uploaded_file($file_tmp, $upload_path)) {
        return [true, $new_filename];
    } else {
        $errors[] = "Failed to move uploaded file";
        return [false, $errors];
    }
}
?>