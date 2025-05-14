<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

$selectedTerm = $_POST['term_id'] ?? '';
$reportData = [];

$terms = $pdo->query("SELECT * FROM terms ORDER BY term_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($selectedTerm)) {
    $stmt = $pdo->prepare("
        SELECT students.id AS student_id, students.first_name,students.last_name, courses.course_name, grades.grade
        FROM grades
        JOIN students ON grades.student_id = students.id
        JOIN courses ON grades.course_id = courses.id
        WHERE grades.term_id = ?
        ORDER BY students.first_name, courses.course_name
    ");
    $stmt->execute([$selectedTerm]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $reportData[$row['student_id']]['name'] = $row['first_name'];
        $reportData[$row['student_id']]['grades'][] = [
            'course' => $row['course_name'],
            'grade' => $row['grade'],
            'remark' => $row['grade'] >= 50 ? 'Pass' : 'Fail',
            'color' => $row['grade'] >= 50 ? 'text-success' : 'text-danger'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Fixed Sidebar */
        body {
            display: flex;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            padding-left: 10px;
        }

        .sidebar a {
            color: #fff;
            padding: 15px;
            text-decoration: none;
            display: block;
        }

        .sidebar a:hover {
            background-color: #575d63;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }

        /* Hide print buttons when printing */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php include '../inc/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h3 class="mb-4">Generate Student Reports</h3>

        <form method="POST" class="mb-4 d-flex align-items-end gap-3 no-print">
            <div>
                <label>Select Term:</label>
                <select name="term_id" class="form-select" required>
                    <option value="">-- Choose Term --</option>
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= $term['id'] ?>" <?= $term['id'] == $selectedTerm ? 'selected' : '' ?>>
                            <?= htmlspecialchars($term['term_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary">Generate</button>
        </form>

        <?php if (!empty($reportData)): ?>
            <div class="d-flex justify-content-end gap-2 mb-3 no-print">
                <a href="export_reports.php?term=<?= $selectedTerm ?>&type=pdf" class="btn btn-outline-danger btn-sm">Export PDF</a>
                <a href="export_reports.php?term=<?= $selectedTerm ?>&type=excel" class="btn btn-outline-success btn-sm">Export Excel</a>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">Print Report</button>
            </div>

            <?php foreach ($reportData as $student): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= htmlspecialchars($student['name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Course</th>
                                    <th>Marks</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $total = 0; 
                                    $count = count($student['grades']); 
                                    foreach ($student['grades'] as $g): 
                                        $total += $g['grade'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($g['course']) ?></td>
                                        <td><?= $g['grade'] ?></td>
                                        <td class="<?= $g['color'] ?> fw-bold"><?= $g['remark'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="fw-bold">
                                    <td colspan="2">Average</td>
                                    <td><?= round($total / $count, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-warning">No data found for the selected term.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
