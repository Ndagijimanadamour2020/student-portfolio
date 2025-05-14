<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Fetch all terms
$terms = $pdo->query("SELECT * FROM terms ORDER BY term_name ASC")->fetchAll();

// Handle the addition of a new term
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_term'])) {
    $term_name = $_POST['term_name'];

    $stmt = $pdo->prepare("INSERT INTO terms (term_name) VALUES (?)");
    $stmt->execute([$term_name]);

    $_SESSION['success'] = "Term successfully added.";
    header("Location: manage_terms.php");
    exit;
}

// Handle term deletion
if (isset($_GET['delete'])) {
    $term_id = $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM terms WHERE id = ?");
    $stmt->execute([$term_id]);

    $_SESSION['success'] = "Term successfully deleted.";
    header("Location: manage_terms.php");
    exit;
}

// Handle term update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_term'])) {
    $term_id = $_POST['term_id'];
    $term_name = $_POST['term_name'];

    $stmt = $pdo->prepare("UPDATE terms SET term_name = ? WHERE id = ?");
    $stmt->execute([$term_name, $term_id]);

    $_SESSION['success'] = "Term successfully updated.";
    header("Location: manage_terms.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Terms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background-color: #007bff;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .modal-content {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include '../inc/sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content">
        <h3 class="mb-4">Manage Terms</h3>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <!-- Form to add a new term -->
        <form method="POST" class="card p-4 shadow-sm mb-4">
            <h5>Add New Term</h5>
            <div class="mb-3">
                <label class="form-label">Term Name</label>
                <input type="text" name="term_name" class="form-control" required>
            </div>
            <button type="submit" name="add_term" class="btn btn-primary">Add Term</button>
        </form>

        <!-- Terms table -->
        <h5>Existing Terms</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Term Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($terms as $term): ?>
                    <tr>
                        <td><?= $term['id'] ?></td>
                        <td><?= htmlspecialchars($term['term_name']) ?></td>
                        <td>
                            <!-- Edit button (opens modal for editing) -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTermModal<?= $term['id'] ?>">Edit</button>
                            
                            <!-- Delete button -->
                            <a href="?delete=<?= $term['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this term?');">Delete</a>
                        </td>
                    </tr>

                    <!-- Modal for editing a term -->
                    <div class="modal fade" id="editTermModal<?= $term['id'] ?>" tabindex="-1" aria-labelledby="editTermModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editTermModalLabel">Edit Term</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="term_id" value="<?= $term['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Term Name</label>
                                            <input type="text" name="term_name" class="form-control" value="<?= htmlspecialchars($term['term_name']) ?>" required>
                                        </div>
                                        <button type="submit" name="update_term" class="btn btn-primary">Update Term</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
