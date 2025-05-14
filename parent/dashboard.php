<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

redirectIfNotLoggedIn();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../index.php");
    exit();
}

$children = [];
$notifications = [];

try {
    // Get parent's children
    $stmt = $pdo->prepare("SELECT s.id, s.first_name, s.last_name, s.admission_number, s.class, s.stream
                          FROM students s
                          JOIN parent_student ps ON s.id = ps.student_id
                          JOIN parents p ON ps.parent_id = p.id
                          WHERE p.user_id = ?
                          ORDER BY s.class, s.stream, s.first_name");
    $stmt->execute([$_SESSION['user_id']]);
    $children = $stmt->fetchAll();
    
    // Get notifications
    $stmt = $pdo->prepare("SELECT n.id, n.title, n.message, n.created_at, n.is_read
                          FROM notifications n
                          JOIN parents p ON n.recipient_id = p.id
                          WHERE p.user_id = ? AND n.recipient_type = 'parent'
                          ORDER BY n.created_at DESC
                          LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Parent Dashboard</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>My Children</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($children)): ?>
                            <p>No children registered under your account.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Admission No</th>
                                            <th>Class</th>
                                            <th>Stream</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($children as $child): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($child['admission_number']); ?></td>
                                                <td><?php echo htmlspecialchars($child['class']); ?></td>
                                                <td><?php echo htmlspecialchars($child['stream']); ?></td>
                                                <td>
                                                    <a href="view_child.php?id=<?php echo $child['id']; ?>" class="btn btn-sm btn-primary">View Performance</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p>No new notifications.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($notifications as $notification): ?>
                                    <a href="#" class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'list-group-item-primary'; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                            <small><?php echo time_elapsed_string($notification['created_at']); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($notification['message'], 0, 50) . '...'); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-2">
                                <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>