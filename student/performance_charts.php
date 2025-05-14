<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

redirectIfNotLoggedIn();
if (!isStudent()) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$chart_data = [];

try {
    // Get student details
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    $student_id = $student['id'];

    // Get comprehensive marks for charts
    $stmt = $pdo->prepare("SELECT s.subject_name, ca.term, ca.academic_year, ca.marks, ca.max_marks
                          FROM comprehensive_assessments ca
                          JOIN subjects s ON ca.subject_id = s.id
                          WHERE ca.student_id = ?
                          ORDER BY ca.academic_year, ca.term, s.subject_name");
    $stmt->execute([$student_id]);
    $marks = $stmt->fetchAll();

    // Prepare chart data
    $subjects = [];
    $terms = [];
    $performance = [];

    foreach ($marks as $mark) {
        $key = $mark['subject_name'] . ' - Term ' . $mark['term'];
        if (!in_array($key, $subjects)) {
            $subjects[] = $key;
        }
        
        if (!in_array($mark['academic_year'], $terms)) {
            $terms[] = $mark['academic_year'];
        }
        
        $percentage = round(($mark['marks'] / $mark['max_marks']) * 100, 2);
        $performance[$mark['academic_year']][$key] = $percentage;
    }

    // Get class averages
    $stmt = $pdo->prepare("SELECT s.subject_name, ca.term, ca.academic_year, AVG(ca.marks/ca.max_marks)*100 as avg_percentage
                          FROM comprehensive_assessments ca
                          JOIN subjects s ON ca.subject_id = s.id
                          JOIN students st ON ca.student_id = st.id
                          WHERE st.class = (SELECT class FROM students WHERE id = ?)
                          AND st.stream = (SELECT stream FROM students WHERE id = ?)
                          GROUP BY s.subject_name, ca.term, ca.academic_year
                          ORDER BY ca.academic_year, ca.term, s.subject_name");
    $stmt->execute([$student_id, $student_id]);
    $averages = $stmt->fetchAll();

    foreach ($averages as $avg) {
        $key = $avg['subject_name'] . ' - Term ' . $avg['term'];
        $class_avg[$avg['academic_year']][$key] = round($avg['avg_percentage'], 2);
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Charts - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>My Performance Charts</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Subject Performance Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="subjectChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Comparison with Class Average</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="comparisonChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Term-wise Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="termChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Subject Performance Chart
        const subjectCtx = document.getElementById('subjectChart').getContext('2d');
        const subjectChart = new Chart(subjectCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($subjects); ?>,
                datasets: [
                    <?php foreach ($terms as $term): ?>
                    {
                        label: '<?php echo $term; ?>',
                        data: <?php echo json_encode(array_values($performance[$term])); ?>,
                        borderColor: getRandomColor(),
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    <?php endforeach; ?>
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });

        // Comparison Chart
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        const comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($subjects); ?>,
                datasets: [
                    {
                        label: 'My Performance',
                        data: <?php echo json_encode(array_values($performance[$terms[0]])); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Class Average',
                        data: <?php echo json_encode(array_values($class_avg[$terms[0]])); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });

        // Term Chart
        const termCtx = document.getElementById('termChart').getContext('2d');
        const termChart = new Chart(termCtx, {
            type: 'radar',
            data: {
                labels: <?php echo json_encode(array_unique(array_map(function($subj) { 
                    return explode(' - Term', $subj)[0]; 
                }, $subjects))); ?>,
                datasets: [
                    <?php foreach ($terms as $term): ?>
                    {
                        label: '<?php echo $term; ?>',
                        data: <?php echo json_encode(array_values(array_filter($performance[$term], function($key) use ($term) {
                            return strpos($key, $term) !== false;
                        }, ARRAY_FILTER_USE_KEY))); ?>,
                        backgroundColor: getRandomColor(0.2),
                        borderColor: getRandomColor(),
                        pointBackgroundColor: getRandomColor(),
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: getRandomColor()
                    },
                    <?php endforeach; ?>
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            }
        });

        function getRandomColor(alpha = 1) {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    </script>
</body>
</html>