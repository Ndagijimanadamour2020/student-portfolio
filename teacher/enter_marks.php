<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

redirectIfNotLoggedIn();
if (!isTeacher()) {
    header("Location: ../index.php");
    exit();
}

// Get teacher's students and subjects
$teacher_id = $_SESSION['user_id'];
$students = [];
$subjects = [];

try {
    // Get teacher details
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        die("Teacher profile not found");
    }
    
    $teacher_id = $teacher['id'];
    
    // Get subjects taught by this teacher
    $stmt = $pdo->prepare("SELECT * FROM subjects");
    $subjects = $stmt->fetchAll();
    
    // Get students
    $stmt = $pdo->prepare("SELECT * FROM students ORDER BY class, stream, last_name");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $student_id = $_POST['student_id'];
        $subject_id = $_POST['subject_id'];
        $assessment_type = $_POST['assessment_type'];
        $marks = $_POST['marks'];
        $max_marks = $_POST['max_marks'];
        $assessment_name = $_POST['assessment_name'];
        $comments = $_POST['comments'];
        
        if ($assessment_type === 'formative') {
            $stmt = $pdo->prepare("INSERT INTO formative_assessments 
                                 (student_id, subject_id, teacher_id, assessment_name, marks, max_marks, assessment_date, comments) 
                                 VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?)");
        } else {
            $term = $_POST['term'];
            $academic_year = $_POST['academic_year'];
            $stmt = $pdo->prepare("INSERT INTO comprehensive_assessments 
                                 (student_id, subject_id, teacher_id, assessment_name, marks, max_marks, assessment_date, term, academic_year, comments) 
                                 VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?)");
            $stmt->execute([$student_id, $subject_id, $teacher_id, $assessment_name, $marks, $max_marks, $term, $academic_year, $comments]);
        }
        
        $success = "Marks entered successfully!";
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
    <title>Enter Marks - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Enter Student Marks</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Student</label>
                    <select class="form-select" id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['admission_number'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
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
            </div>
            
            <div class="row mb-3">
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
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="marks" class="form-label">Marks Obtained</label>
                    <input type="number" step="0.01" class="form-control" id="marks" name="marks" required>
                </div>
                <div class="col-md-4">
                    <label for="max_marks" class="form-label">Maximum Marks</label>
                    <input type="number" step="0.01" class="form-control" id="max_marks" name="max_marks" required>
                </div>
                <div class="col-md-4">
                    <label for="comments" class="form-label">Comments</label>
                    <input type="text" class="form-control" id="comments" name="comments">
                </div>
            </div>
            
            <div id="comprehensive_fields" class="row mb-3" style="display: none;">
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
                    <input type="text" class="form-control" id="academic_year" name="academic_year" placeholder="e.g., 2023-2024">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit Marks</button>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('assessment_type').addEventListener('change', function() {
            const comprehensiveFields = document.getElementById('comprehensive_fields');
            if (this.value === 'comprehensive') {
                comprehensiveFields.style.display = 'flex';
            } else {
                comprehensiveFields.style.display = 'none';
            }
        });
    </script>
</body>
</html>