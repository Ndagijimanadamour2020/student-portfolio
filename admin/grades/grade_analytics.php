<?php
require_once '../config/database.php';
$pageTitle = "Grade Analytics";

// Fetch all terms
$termsStmt = $pdo->query("SELECT * FROM terms ORDER BY id DESC");
$terms = $termsStmt->fetchAll();

// Get selected term
$termId = $_GET['term'] ?? $terms[0]['id'] ?? null;

if ($termId) {
    // Fetch grade data
    $stmt = $pdo->prepare("
        SELECT students.first_name, students.last_name, grades.grade
        FROM grades
        JOIN students ON grades.student_id = students.id
        WHERE grades.term_id = ?
    ");
    $stmt->execute([$termId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $grades = array_column($data, 'grade');
    $average = $grades ? round(array_sum($grades) / count($grades), 2) : 0;
    $passCount = count(array_filter($grades, fn($g) => $g >= 50));
    $failCount = count($grades) - $passCount;

    // Top performers
    usort($data, fn($a, $b) => $b['grade'] <=> $a['grade']);
    $topPerformers = array_slice($data, 0, 5);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding-top: 70px;
            padding-left: 220px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 220px;
            background-color: #343a40;
            padding-top: 70px;
            color: #fff;
        }

        .sidebar h4 {
            padding: 15px;
        }

        .sidebar a {
            color: #ccc;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }

        .header {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            height: 70px;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }

        .content {
            padding: 20px;
        }

        canvas {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

 <!-- Sidebar -->
 <div class="sidebar">
        <?php include '../inc/sidebar.php'; ?>
    </div>

<div class="header">
    <h4 class="mb-0"><?= $pageTitle ?></h4>
</div>

<div class="content">
    <div class="container-fluid">
        <form method="get" class="mb-3 text-center">
            <label for="term" class="me-2">Select Term:</label>
            <select name="term" id="term" onchange="this.form.submit()" class="form-select d-inline w-auto">
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term['id'] ?>" <?= $termId == $term['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($term['term_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($termId): ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="bg-success text-white p-3 rounded">
                        <h5>Average Grade</h5>
                        <p class="display-6"><?= $average ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-primary text-white p-3 rounded">
                        <h5>Pass Count</h5>
                        <p class="display-6"><?= $passCount ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-danger text-white p-3 rounded">
                        <h5>Fail Count</h5>
                        <p class="display-6"><?= $failCount ?></p>
                    </div>
                </div>
            </div>

            <h4 class="text-center mt-4">Grade Distribution</h4>
            <canvas id="gradeChart" height="100"></canvas>

            <h4 class="mt-5">Top 5 Performers</h4>
            <ul class="list-group">
                <?php foreach ($topPerformers as $student): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) ?></span>
                        <strong class="<?= $student['grade'] >= 50 ? 'text-success' : 'text-danger' ?>">
                            <?= $student['grade'] ?>
                        </strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-warning">No term selected or data found.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($grades)): ?>
    const ctx = document.getElementById('gradeChart').getContext('2d');
    const gradeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($s) => $s['first_name'] . ' ' . $s['last_name'], $data)) ?>,
            datasets: [{
                label: 'Grades',
                data: <?= json_encode(array_column($data, 'grade')) ?>,
                backgroundColor: <?= json_encode(array_map(fn($g) => $g >= 50 ? 'rgba(40, 167, 69, 0.7)' : 'rgba(220, 53, 69, 0.7)', array_column($data, 'grade'))) ?>,
                borderColor: 'rgba(0, 0, 0, 0.8)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
<?php endif; ?>
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
