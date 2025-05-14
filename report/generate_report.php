<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

redirectIfNotLoggedIn();
if (!isTeacher() && !isAdmin()) {
    header("Location: ../index.php");
    exit();
}

$report_types = [
    'term' => 'Term Report',
    'progress' => 'Progress Report',
    'subject' => 'Subject Analysis'
];

$classes = [];
$streams = [];
$terms = ['1', '2', '3'];
$current_year = date('Y');
$academic_years = [
    ($current_year - 1) . '-' . $current_year,
    $current_year . '-' . ($current_year + 1)
];

try {
    // Get classes and streams
    $stmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class");
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->query("SELECT DISTINCT stream FROM students ORDER BY stream");
    $streams = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Process report generation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $report_type = $_POST['report_type'];
        $class = $_POST['class'];
        $stream = $_POST['stream'];
        $term = $_POST['term'];
        $academic_year = $_POST['academic_year'];
        $format = $_POST['format'];
        
        // Validate inputs
        if (!in_array($report_type, array_keys($report_types))) {
            throw new Exception("Invalid report type");
        }
        
        // Generate report based on type
        switch ($report_type) {
            case 'term':
                $report_data = generateTermReport($pdo, $class, $stream, $term, $academic_year);
                $filename = "Term_Report_{$class}_{$stream}_Term{$term}_{$academic_year}";
                break;
                
            case 'progress':
                $report_data = generateProgressReport($pdo, $class, $stream, $academic_year);
                $filename = "Progress_Report_{$class}_{$stream}_{$academic_year}";
                break;
                
            case 'subject':
                $report_data = generateSubjectReport($pdo, $class, $stream, $academic_year);
                $filename = "Subject_Analysis_{$class}_{$stream}_{$academic_year}";
                break;
        }
        
        // Export based on format
        if ($format === 'pdf') {
            exportAsPDF($report_data, $filename);
        } else {
            exportAsExcel($report_data, $filename);
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

function generateTermReport($pdo, $class, $stream, $term, $academic_year) {
    $stmt = $pdo->prepare("SELECT s.first_name, s.last_name, s.admission_number,
                          sub.subject_name, ca.marks, ca.max_marks, 
                          ROUND((ca.marks/ca.max_marks)*100, 2) as percentage,
                          ca.comments
                          FROM comprehensive_assessments ca
                          JOIN students s ON ca.student_id = s.id
                          JOIN subjects sub ON ca.subject_id = sub.id
                          WHERE s.class = ? AND s.stream = ? 
                          AND ca.term = ? AND ca.academic_year = ?
                          ORDER BY s.first_name, s.last_name, sub.subject_name");
    $stmt->execute([$class, $stream, $term, $academic_year]);
    $results = $stmt->fetchAll();
    
    // Calculate averages and rankings
    $students = [];
    foreach ($results as $row) {
        $adm_no = $row['admission_number'];
        if (!isset($students[$adm_no])) {
            $students[$adm_no] = [
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'admission_number' => $adm_no,
                'subjects' => [],
                'total' => 0,
                'count' => 0
            ];
        }
        
        $students[$adm_no]['subjects'][$row['subject_name']] = [
            'marks' => $row['marks'],
            'max' => $row['max_marks'],
            'percentage' => $row['percentage'],
            'comments' => $row['comments']
        ];
        
        $students[$adm_no]['total'] += $row['percentage'];
        $students[$adm_no]['count']++;
    }
    
    // Calculate averages and rankings
    foreach ($students as &$student) {
        $student['average'] = $student['total'] / $student['count'];
    }
    unset($student);
    
    // Sort by average
    usort($students, function($a, $b) {
        return $b['average'] <=> $a['average'];
    });
    
    // Add rank
    foreach ($students as $rank => &$student) {
        $student['rank'] = $rank + 1;
    }
    
    return [
        'type' => 'term',
        'class' => $class,
        'stream' => $stream,
        'term' => $term,
        'academic_year' => $academic_year,
        'students' => $students,
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

function exportAsPDF($data, $filename) {
    // In a real implementation, you would use a library like TCPDF or Dompdf
    // This is a simplified example
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    // For actual implementation, you would generate a PDF file here
    echo "PDF generation would be implemented here with a proper library\n\n";
    print_r($data);
    exit();
}

function exportAsExcel($data, $filename) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo "<table border='1'>";
    
    if ($data['type'] === 'term') {
        echo "<tr><th colspan='5'>Term Report - {$data['class']} {$data['stream']} - Term {$data['term']} {$data['academic_year']}</th></tr>";
        echo "<tr><th>Rank</th><th>Admission No</th><th>Student Name</th><th>Average %</th><th>Subjects</th></tr>";
        
        foreach ($data['students'] as $student) {
            echo "<tr>";
            echo "<td>{$student['rank']}</td>";
            echo "<td>{$student['admission_number']}</td>";
            echo "<td>{$student['name']}</td>";
            echo "<td>" . number_format($student['average'], 2) . "%</td>";
            echo "<td>";
            
            foreach ($student['subjects'] as $subject => $details) {
                echo "{$subject}: {$details['marks']}/{$details['max']} ({$details['percentage']}%)<br>";
                if (!empty($details['comments'])) {
                    echo "Comment: {$details['comments']}<br>";
                }
                echo "<br>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Generate Reports</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5>Report Options</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <?php foreach ($report_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="format" class="form-label">Export Format</label>
                            <select class="form-select" id="format" name="format" required>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="class" class="form-label">Class</label>
                            <select class="form-select" id="class" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="stream" class="form-label">Stream</label>
                            <select class="form-select" id="stream" name="stream" required>
                                <option value="">Select Stream</option>
                                <?php foreach ($streams as $stream): ?>
                                    <option value="<?php echo $stream; ?>"><?php echo $stream; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <select class="form-select" id="academic_year" name="academic_year" required>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3" id="term_field">
                        <div class="col-md-12">
                            <label for="term" class="form-label">Term</label>
                            <select class="form-select" id="term" name="term">
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?php echo $term; ?>">Term <?php echo $term; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide term field based on report type
        document.getElementById('report_type').addEventListener('change', function() {
            const termField = document.getElementById('term_field');
            if (this.value === 'term') {
                termField.style.display = 'block';
                document.getElementById('term').required = true;
            } else {
                termField.style.display = 'none';
                document.getElementById('term').required = false;
            }
        });
        
        // Trigger change event on load
        document.getElementById('report_type').dispatchEvent(new Event('change'));
    </script>
</body>
</html>