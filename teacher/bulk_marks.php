<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

redirectIfNotLoggedIn();
if (!isTeacher()) {
    header("Location: ../index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$classes = [];
$subjects = [];
$error = '';
$success = '';

try {
    // Get teacher details
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        throw new Exception("Teacher profile not found");
    }
    
    $teacher_id = $teacher['id'];
    
    // Get classes and subjects
    $stmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class");
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY subject_name");
    $subjects = $stmt->fetchAll();
    
    // Process bulk upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['marks_file'])) {
        $class = $_POST['class'];
        $stream = $_POST['stream'];
        $subject_id = $_POST['subject_id'];
        $assessment_type = $_POST['assessment_type'];
        $assessment_name = $_POST['assessment_name'];
        $max_marks = $_POST['max_marks'];
        
        // Validate inputs
        if (empty($class) || empty($stream) || empty($subject_id) || empty($assessment_name) || empty($max_marks)) {
            throw new Exception("All fields are required");
        }
        
        // Validate file
        $file = $_FILES['marks_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        // Check file type
        $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_type !== 'csv') {
            throw new Exception("Only CSV files are allowed");
        }
        
        // Parse CSV with improved error handling
        $students_data = [];
        $row = 0;
        $skipped_rows = [];
        
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            while (($data = fgetcsv($handle)) {
                $row++;
                
                // Skip empty rows or invalid data
                if (!is_array($data) || count($data) < 2 || empty(trim($data[0]))) {
                    $skipped_rows[] = $row;
                    continue;
                }
                
                // Validate admission number
                $admission_number = trim($data[0]);
                if (empty($admission_number)) {
                    $skipped_rows[] = $row;
                    continue;
                }
                
                // Validate marks
                $marks = isset($data[1]) ? trim($data[1]) : '';
                if (!is_numeric($marks)) {
                    $skipped_rows[] = $row;
                    continue;
                }
                
                $students_data[] = [
                    'admission_number' => $admission_number,
                    'marks' => floatval($marks),
                    'comments' => isset($data[2]) ? trim($data[2]) : ''
                ];
            }
            fclose($handle);
        }
        
        if (empty($students_data)) {
            throw new Exception("No valid student records found in the CSV file");
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get all students in the class/stream
        $stmt = $pdo->prepare("SELECT id, admission_number FROM students 
                              WHERE class = ? AND stream = ?");
        $stmt->execute([$class, $stream]);
        $students = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Insert marks
        $inserted = 0;
        $missing_students = [];
        
        foreach ($students_data as $data) {
            if (!isset($students[$data['admission_number']])) {
                $missing_students[] = $data['admission_number'];
                continue;
            }
            
            $student_id = $students[$data['admission_number']];
            
            if ($assessment_type === 'formative') {
                $stmt = $pdo->prepare("INSERT INTO formative_assessments 
                                     (student_id, subject_id, teacher_id, assessment_name, marks, max_marks, assessment_date, comments) 
                                     VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)");
                $stmt->execute([$student_id, $subject_id, $teacher_id, $assessment_name, $data['marks'], $max_marks, $data['comments']]);
            } else {
                $term = $_POST['term'];
                $academic_year = $_POST['academic_year'];
                $stmt = $pdo->prepare("INSERT INTO comprehensive_assessments 
                                     (student_id, subject_id, teacher_id, assessment_name, marks, max_marks, assessment_date, term, academic_year, comments) 
                                     VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?)");
                $stmt->execute([$student_id, $subject_id, $teacher_id, $assessment_name, $data['marks'], $max_marks, $term, $academic_year, $data['comments']]);
            }
            
            $inserted++;
        }
        
        $pdo->commit();
        
        // Prepare success message with details
        $success = "Successfully uploaded marks for $inserted students";
        
        if (!empty($missing_students)) {
            $success .= ". Missing students: " . implode(', ', array_unique($missing_students));
        }
        
        if (!empty($skipped_rows)) {
            $success .= ". Skipped rows: " . implode(', ', array_unique($skipped_rows));
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_template'])) {
        // Generate and download CSV template
        $class = $_POST['class'];
        $stream = $_POST['stream'];
        
        if (empty($class) || empty($stream)) {
            throw new Exception("Class and stream are required to download template");
        }
        
        $stmt = $pdo->prepare("SELECT admission_number, first_name, last_name 
                              FROM students 
                              WHERE class = ? AND stream = ?
                              ORDER BY admission_number");
        $stmt->execute([$class, $stream]);
        $students = $stmt->fetchAll();
        
        if (empty($students)) {
            throw new Exception("No students found in the selected class and stream");
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="marks_template_'.$class.'_'.$stream.'.csv"');
        
        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Admission Number', 'Marks', 'Comments']);
        
        foreach ($students as $student) {
            fputcsv($output, [
                $student['admission_number'],
                '',
                ''
            ]);
        }
        
        fclose($output);
        exit();
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Marks Upload - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2><i class="fas fa-upload me-2"></i>Bulk Marks Upload</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-file-download me-2"></i>Download Template</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="templateForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="class_template" class="form-label">Class</label>
                            <select class="form-select" id="class_template" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class); ?>">
                                        <?php echo htmlspecialchars($class); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="stream_template" class="form-label">Stream</label>
                            <input type="text" class="form-control" id="stream_template" name="stream" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="download_template" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-file-upload me-2"></i>Upload Marks</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="class" class="form-label">Class</label>
                            <select class="form-select" id="class" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class); ?>">
                                        <?php echo htmlspecialchars($class); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="stream" class="form-label">Stream</label>
                            <input type="text" class="form-control" id="stream" name="stream" required>
                        </div>
                        <div class="col-md-4">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="assessment_type" class="form-label">Assessment Type</label>
                            <select class="form-select" id="assessment_type" name="assessment_type" required>
                                <option value="formative">Formative Assessment</option>
                                <option value="comprehensive">Comprehensive Assessment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="assessment_name" class="form-label">Assessment Name</label>
                            <input type="text" class="form-control" id="assessment_name" name="assessment_name" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="max_marks" class="form-label">Maximum Marks</label>
                            <input type="number" step="0.01" min="1" class="form-control" id="max_marks" name="max_marks" required>
                        </div>
                        <div class="col-md-4">
                            <label for="marks_file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="marks_file" name="marks_file" accept=".csv" required>
                            <small class="text-muted">Max 2MB</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CSV Format</label>
                            <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#formatHelp">
                                <i class="fas fa-info-circle me-1"></i> View Format
                            </button>
                        </div>
                        
                        <div id="comprehensive_fields" class="row g-3" style="display: none;">
                            <div class="col-md-6">
                                <label for="term" class="form-label">Term</label>
                                <select class="form-select" id="term" name="term">
                                    <option value="1">Term 1</option>
                                    <option value="2">Term 2</option>
                                    <option value="3">Term 3</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <input type="text" class="form-control" id="academic_year" name="academic_year" 
                                       placeholder="e.g., <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload Marks
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Format Help Modal -->
    <div class="modal fade" id="formatHelp" tabindex="-1" aria-labelledby="formatHelpLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="formatHelpLabel"><i class="fas fa-file-csv me-2"></i>CSV File Format</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Your CSV file should have the following format:
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Admission Number</th>
                                    <th>Marks Obtained</th>
                                    <th>Comments (Optional)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ADM001</td>
                                    <td>85</td>
                                    <td>Good performance</td>
                                </tr>
                                <tr>
                                    <td>ADM002</td>
                                    <td>72</td>
                                    <td>Needs improvement</td>
                                </tr>
                                <tr>
                                    <td>ADM003</td>
                                    <td>91</td>
                                    <td>Excellent work</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <h6><i class="fas fa-lightbulb me-2"></i>Tips:</h6>
                        <ul>
                            <li>The header row is optional</li>
                            <li>Empty rows will be automatically skipped</li>
                            <li>Only the first 3 columns will be processed</li>
                            <li>Maximum file size: 2MB</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle comprehensive fields
            const assessmentType = document.getElementById('assessment_type');
            const compFields = document.getElementById('comprehensive_fields');
            
            assessmentType.addEventListener('change', function() {
                compFields.style.display = this.value === 'comprehensive' ? 'flex' : 'none';
            });
            
            // File size validation
            const fileInput = document.getElementById('marks_file');
            fileInput.addEventListener('change', function() {
                if (this.files[0] && this.files[0].size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                }
            });
            
            // Form validation
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Please select a CSV file to upload');
                    fileInput.focus();
                }
            });
        });
    </script>
</body>
</html>