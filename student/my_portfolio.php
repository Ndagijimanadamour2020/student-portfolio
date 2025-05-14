<?php
session_start();
require_once '../config/database.php';

// Make sure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit;
}

$studentId = $_SESSION['student_id'];

$stmt = $pdo->prepare("
    SELECT * FROM portfolio_entries 
    WHERE student_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$studentId]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Portfolio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h3 class="mb-4">My Portfolio Entries</h3>

    <a href="upload_portfolio.php" class="btn btn-primary mb-3">+ Submit New Portfolio</a>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Extra-Curricular</th>
                <th>File</th>
                <th>Status</th>
                <th>Submitted On</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['title']) ?></td>
                <td><?= htmlspecialchars($entry['description']) ?></td>
                <td><?= htmlspecialchars($entry['extra_curricular']) ?></td>
                <td>
                    <?php if (!empty($entry['file_path'])): ?>
                        <a href="<?= $entry['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Download</a>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-<?= 
                        $entry['status'] === 'Approved' ? 'success' : 
                        ($entry['status'] === 'Rejected' ? 'danger' : 'secondary') ?>">
                        <?= $entry['status'] ?>
                    </span>
                </td>
                <td><?= date('d M Y', strtotime($entry['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
