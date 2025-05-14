<?php
require_once '../../config/database.php';
$pageTitle = "Manage Portfolios";

// Fetch all portfolio entries
$stmt = $pdo->query("
    SELECT p.*, s.first_name, s.last_name
    FROM portfolios p
    JOIN students s ON p.student_id = s.id
    ORDER BY p.created_at DESC
");
$portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/fontawesome/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            background-color: #f8f9fa;
            width: 100%;
        }

        .table thead th {
            background-color: #0d6efd;
            color: white;
            vertical-align: middle;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .card-header {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include '../inc/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <?= $pageTitle ?>
        </div>
        <div class="card-body">
            <?php if ($portfolios): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>File</th>
                                <th>Extra-Curricular</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portfolios as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <a href="../../uploads/portfolio/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['extra_curricular']) ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($row['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="approve_reject_portfolio.php?id=<?= $row['id'] ?>&action=approve" class="btn btn-sm btn-success mb-1">Approve</a>
                                            <a href="approve_reject_portfolio.php?id=<?= $row['id'] ?>&action=reject" class="btn btn-sm btn-danger">Reject</a>
                                        <?php else: ?>
                                            <span class="text-muted">No action</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">No portfolio entries found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
