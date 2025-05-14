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
$upcoming_classes = [];
$recent_marks = [];
$announcements = [];

try {
    // Get teacher details
    $stmt = $pdo->prepare("SELECT t.* FROM teachers t WHERE t.user_id = ?");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch();

    // Get upcoming classes (example query - adjust based on your schema)
    $stmt = $pdo->prepare("SELECT s.subject_name, sc.class, sc.stream, sc.schedule_time 
                          FROM teacher_subjects ts
                          JOIN subjects s ON ts.subject_id = s.id
                          JOIN schedules sc ON ts.subject_id = sc.subject_id
                          WHERE ts.teacher_id = ? AND sc.schedule_date >= CURDATE()
                          ORDER BY sc.schedule_date LIMIT 5");
    $stmt->execute([$teacher['id']]);
    $upcoming_classes = $stmt->fetchAll();

    // Get recently entered marks
    $stmt = $pdo->prepare("SELECT s.subject_name, st.first_name, st.last_name, 
                          fa.marks, fa.max_marks, fa.assessment_date
                          FROM formative_assessments fa
                          JOIN subjects s ON fa.subject_id = s.id
                          JOIN students st ON fa.student_id = st.id
                          WHERE fa.teacher_id = ?
                          ORDER BY fa.assessment_date DESC LIMIT 5");
    $stmt->execute([$teacher['id']]);
    $recent_marks = $stmt->fetchAll();

    // Get announcements
    $stmt = $pdo->prepare("SELECT a.title, a.content, a.created_at 
                          FROM announcements a
                          WHERE a.target_audience IN ('all', 'teachers')
                          ORDER BY a.created_at DESC LIMIT 3");
    $stmt->execute();
    $announcements = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Student Portfolio</title>
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
                        <h5><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Profile</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($teacher['department'] ?? 'Department'); ?></p>
                        <p class="mb-1"><strong>Staff ID:</strong> <?php echo htmlspecialchars($teacher['staff_id']); ?></p>
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
                        <h5><i class="fas fa-calendar-alt me-2"></i>Upcoming Classes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_classes)): ?>
                            <p>No upcoming classes scheduled.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Class</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming_classes as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($class['class'] . ' ' . $class['stream']); ?></td>
                                            <td><?php echo htmlspecialchars($class['schedule_time']); ?></td>
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
                        <h5><i class="fas fa-clipboard-list me-2"></i>Recent Marks Entered</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_marks)): ?>
                            <p>No recent marks entered.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Subject</th>
                                            <th>Marks</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_marks as $mark): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mark['first_name'] . ' ' . $mark['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['marks'] . '/' . $mark['max_marks']); ?></td>
                                            <td><?php echo date('M j', strtotime($mark['assessment_date'])); ?></td>
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
                            <a href="enter_marks.php" class="btn btn-primary me-md-2">
                                <i class="fas fa-edit me-1"></i> Enter Marks
                            </a>
                            <a href="view_students.php" class="btn btn-outline-primary me-md-2">
                                <i class="fas fa-users me-1"></i> View Students
                            </a>
                            <a href="bulk_marks.php" class="btn btn-outline-primary">
                                <i class="fas fa-file-import me-1"></i> Bulk Upload
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>