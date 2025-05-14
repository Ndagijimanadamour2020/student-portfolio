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
$formative_marks = [];
$comprehensive_marks = [];

try {
    // Get student details
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        die("Student profile not found");
    }
    
    $student_id = $student['id'];
    
    // Get formative assessments
    $stmt = $pdo->prepare("SELECT fa.*, s.subject_name, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name 
                          FROM formative_assessments fa
                          JOIN subjects s ON fa.subject_id = s.id
                          JOIN teachers t ON fa.teacher_id = t.id
                          WHERE fa.student_id = ?
                          ORDER BY fa.assessment_date DESC");
    $stmt->execute([$student_id]);
    $formative_marks = $stmt->fetchAll();
    
    // Get comprehensive assessments
    $stmt = $pdo->prepare("SELECT ca.*, s.subject_name, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name 
                          FROM comprehensive_assessments ca
                          JOIN subjects s ON ca.subject_id = s.id
                          JOIN teachers t ON ca.teacher_id = t.id
                          WHERE ca.student_id = ?
                          ORDER BY ca.academic_year DESC, ca.term DESC");
    $stmt->execute([$student_id]);
    $comprehensive_marks = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marks - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>My Assessment Records</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="marksTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="formative-tab" data-bs-toggle="tab" data-bs-target="#formative" type="button" role="tab">Formative Assessments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comprehensive-tab" data-bs-toggle="tab" data-bs-target="#comprehensive" type="button" role="tab">Comprehensive Assessments</button>
            </li>
        </ul>
        
        <div class="tab-content" id="marksTabContent">
            <div class="tab-pane fade show active" id="formative" role="tabpanel">
                <h4 class="mt-3">Formative Assessments</h4>
                <?php if (empty($formative_marks)): ?>
                    <p>No formative assessment records found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Assessment</th>
                                    <th>Date</th>
                                    <th>Marks</th>
                                    <th>Percentage</th>
                                    <th>Teacher</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($formative_marks as $mark): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['assessment_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['assessment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['marks'] . '/' . $mark['max_marks']); ?></td>
                                        <td><?php echo round(($mark['marks'] / $mark['max_marks']) * 100, 2); ?>%</td>
                                        <td><?php echo htmlspecialchars($mark['teacher_first_name'] . ' ' . $mark['teacher_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['comments']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-pane fade" id="comprehensive" role="tabpanel">
                <h4 class="mt-3">Comprehensive Assessments</h4>
                <?php if (empty($comprehensive_marks)): ?>
                    <p>No comprehensive assessment records found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Assessment</th>
                                    <th>Term</th>
                                    <th>Year</th>
                                    <th>Marks</th>
                                    <th>Percentage</th>
                                    <th>Teacher</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comprehensive_marks as $mark): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['assessment_name']); ?></td>
                                        <td>Term <?php echo htmlspecialchars($mark['term']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['academic_year']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['marks'] . '/' . $mark['max_marks']); ?></td>
                                        <td><?php echo round(($mark['marks'] / $mark['max_marks']) * 100, 2); ?>%</td>
                                        <td><?php echo htmlspecialchars($mark['teacher_first_name'] . ' ' . $mark['teacher_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($mark['comments']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>