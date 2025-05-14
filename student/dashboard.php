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
$student = [];
$recent_marks = [];
$announcements = [];
$upcoming_tests = [];

try {
    // Get student details
    $stmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.user_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    // Get recent marks
    $stmt = $pdo->prepare("SELECT s.subject_name, fa.marks, fa.max_marks, 
                          ROUND((fa.marks/fa.max_marks)*100, 2) as percentage,
                          fa.assessment_date, t.first_name as teacher_first_name, 
                          t.last_name as teacher_last_name
                          FROM formative_assessments fa
                          JOIN subjects s ON fa.subject_id = s.id
                          JOIN teachers t ON fa.teacher_id = t.id
                          WHERE fa.student_id = ?
                          ORDER BY fa.assessment_date DESC LIMIT 5");
    $stmt->execute([$student['id']]);
    $recent_marks = $stmt->fetchAll();

    // Get announcements
    $stmt = $pdo->prepare("SELECT a.title, a.content, a.created_at 
                          FROM announcements a
                          WHERE a.target_audience IN ('all', 'students')
                          ORDER BY a.created_at DESC LIMIT 3");
    $stmt->execute();
    $announcements = $stmt->fetchAll();

    // Get upcoming tests
    $stmt = $pdo->prepare("SELECT s.subject_name, t.test_name, t.test_date 
                          FROM tests t
                          JOIN subjects s ON t.subject_id = s.id
                          WHERE t.class = ? AND t.stream = ? AND t.test_date >= CURDATE()
                          ORDER BY t.test_date LIMIT 5");
    $stmt->execute([$student['class'], $student['stream']]);
    $upcoming_tests = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-user-graduate me-2"></i>Student Profile</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($student['class'] . ' ' . $student['stream']); ?></p>
                        <p class="mb-1"><strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_number']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <p>No announcements available.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($announcements as $announcement): ?>
                                <div class="list-group-item">
                                    <h6><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                    <p><?php echo htmlspecialchars(substr($announcement['content'], 0, 100) . '...'); ?></p>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-clipboard-list me-2"></i>Recent Marks</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_marks)): ?>
                            <p>No recent marks available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Marks</th>
                                            <th>%</th>
                                            <th>Teacher</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_marks as $mark): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['marks'] . '/' . $mark['max_marks']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['percentage']); ?>%</td>
                                            <td><?php echo htmlspecialchars($mark['teacher_first_name'][0] . '. ' . $mark['teacher_last_name']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Upcoming Tests</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_tests)): ?>
                            <p>No upcoming tests scheduled.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Test</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming_tests as $test): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($test['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                            <td><?php echo date('M j', strtotime($test['test_date'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-tachometer-alt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <a href="view_marks.php" class="btn btn-primary me-md-2">
                                <i class="fas fa-chart-line me-1"></i> View All Marks
                            </a>
                            <a href="performance_charts.php" class="btn btn-outline-primary me-md-2">
                                <i class="fas fa-chart-pie me-1"></i> Performance Charts
                            </a>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Portfolio Upload & Status -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-upload me-2"></i>Upload Portfolio</h5>
            </div>
            <div class="card-body">
                <form action="upload_portfolio.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="extra_curricular" class="form-label">Extra-Curricular Activities</label>
                        <input type="text" name="extra_curricular" id="extra_curricular" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File (PDF, DOC, ZIP)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".pdf,.doc,.docx,.zip" required>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i>Submit</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-folder-open me-2"></i>Your Portfolios</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM portfolios WHERE student_id = ? ORDER BY created_at DESC");
                $stmt->execute([$student['id']]);
                $student_portfolios = $stmt->fetchAll();
                ?>

                <?php if (empty($student_portfolios)): ?>
                    <p>You haven't submitted any portfolios yet.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($student_portfolios as $portfolio): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($portfolio['title']) ?></strong><br>
                                <small><?= date('M j, Y', strtotime($portfolio['created_at'])) ?></small><br>
                                <span>Status:
                                    <?php
                                    if ($portfolio['status'] === 'approved') {
                                        echo '<span class="badge bg-success">Approved</span>';
                                    } elseif ($portfolio['status'] === 'rejected') {
                                        echo '<span class="badge bg-danger">Rejected</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Pending</span>';
                                    }
                                    ?>
                                </span><br>
                                <a href="../uploads/portfolio/<?= htmlspecialchars($portfolio['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-download me-1"></i>View File
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>