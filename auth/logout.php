<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Record logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO user_logs (user_id, activity_type, ip_address, user_agent) 
                              VALUES (?, 'logout', ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (PDOException $e) {
        // Silently fail logging if there's an error
        error_log("Logout logging failed: " . $e->getMessage());
    }
}

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit();
?>