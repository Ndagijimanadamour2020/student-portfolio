<?php
session_start();
require_once '../config/database.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Handle course assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_course'])) {
    $teacher_id = $_POST['teacher_id'];
    $course_id = $_POST['course_id'];

    // Check if the assignment already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM course_teacher WHERE teacher_id = ? AND course_id = ?");
    $checkStmt->execute([$teacher_id, $course_id]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        // Assignment already exists
        header("Location: assign_courses.php?error=exists");
        exit;
    }

    // Insert new assignment
    $insertStmt = $pdo->prepare("INSERT INTO course_teacher (teacher_id, course_id) VALUES (?, ?)");
    $insertStmt->execute([$teacher_id, $course_id]);

    header("Location: assign_courses.php?success=assigned");
    exit;
}

// Handle assignment deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the assignment
    $deleteStmt = $pdo->prepare("DELETE FROM course_teacher WHERE id = ?");
    $deleteStmt->execute([$id]);

    header("Location: assign_courses.php?success=deleted");
    exit;
}

// Redirect to the assignment page if no valid action is specified
header("Location: assign_courses.php");
exit;
