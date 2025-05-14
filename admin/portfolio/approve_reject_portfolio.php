<?php
require_once '../../config/database.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $portfolioId = intval($_GET['id']);
    $action = $_GET['action'];

    if (!in_array($action, ['approve', 'reject'])) {
        die("Invalid action specified.");
    }

    // Update status in the database
    $stmt = $pdo->prepare("UPDATE portfolios SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $action,
        ':id' => $portfolioId
    ]);

    // Optionally, you can send an email notification to the student (ask if you'd like this feature)
    // Redirect back to manage_portfolio.php
    header("Location: manage_portfolio.php");
    exit;
} else {
    die("Missing required parameters.");
}
