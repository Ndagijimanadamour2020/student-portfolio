<?php
require_once '../config/database.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Invalid request.");
}

$id = $_GET['id'];
$status = $_GET['status'];

if (!in_array($status, ['Approved', 'Rejected'])) {
    die("Invalid status.");
}

$stmt = $pdo->prepare("UPDATE portfolio_entries SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("Location: view_portfolios.php");
exit;
