<?php
require_once '../config/database.php';

if (isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $department_id = (int)$_POST['department_id'];

    if (!empty($course_name) && !empty($course_code)) {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code, department_id) VALUES (?, ?, ?)");
        $stmt->execute([$course_name, $course_code, $department_id]);
    }
    header("Location: courses.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);
    header("Location: courses.php");
    exit;
}
