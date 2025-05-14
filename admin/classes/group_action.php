<?php
require_once '../config/database.php';

if (isset($_POST['add_group'])) {
    $group_name = trim($_POST['group_name']);
    $level = $_POST['level'];

    if (!empty($group_name) && !empty($level)) {
        // Prevent duplicate group names
        $check = $pdo->prepare("SELECT COUNT(*) FROM class_groups WHERE group_name = ?");
        $check->execute([$group_name]);
        if ($check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO class_groups (group_name, level) VALUES (?, ?)");
            $stmt->execute([$group_name, $level]);
        }
    }

    header("Location: class_groups.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM class_groups WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: class_groups.php");
    exit;
}
