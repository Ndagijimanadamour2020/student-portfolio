<?php
require_once "../../includes/auth.php";
requireRole('admin');
require_once "../../config/database.php";
require_once "../../includes/User.php";
$first_name='';
$last_name='';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$term = isset($_GET['term']) ? (int)$_GET['term'] : 1;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Build base query
$query = "SELECT 
            u.id as student_id, 
            u.first_name, 
            u.last_name,
            c.name as class_name,
            s.name as subject_name,
            AVG(g.score) as average_score,
            COUNT(g.id) as assignments_count
          FROM users u
          JOIN classes c ON u.class_id = c.id
          JOIN grades g ON u.id = g.student_id
          JOIN subjects s ON g.subject_id = s.id
          WHERE u.role = 'student' AND g.term = :term AND g.academic_year = :year";

// Add filters
$params = [
    ':term' => $term,
    ':year' => $year
];

if($class_id > 0) {
    $query .= " AND u.class_id = :class_id";
    $params[':class_id'] = $class_id;
}

if($subject_id > 0) {
    $query .= " AND g.subject_id = :subject_id";
    $params[':subject_id'] = $subject_id;
}

// Complete query with grouping
$query .= " GROUP BY u.id, c.name, s.name
            ORDER BY c.name, u.last_name, u.first_name, s.name";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

// Get classes and subjects for filters
$classes = $db->query("SELECT id, name FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $db->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include_once "../../includes/header.php"; ?>

<div class="container">
    <h2>Student Performance Reports</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            Filter Options
        </div>
        <div class="card-body">
            <form method="get" class="form-inline">
                <div class="form-group mr-3">
                    <label for="class_id" class="mr-2">Class:</label>
                    <select name="class_id" id="class_id" class="form-control">
                        <option value="0">All Classes</option>
                        <?php foreach($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $class_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="subject_id" class="mr-2">Subject:</label>
                    <select name="subject_id" id="subject_id" class="form-control">
                        <option value="0">All Subjects</option>
                        <?php foreach($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo $subject['id'] == $subject_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="term" class="mr-2">Term:</label>
                    <select name="term" id="term" class="form-control">
                        <option value="1" <?php echo $term == 1 ? 'selected' : ''; ?>>Term 1</option>
                        <option value="2" <?php echo $term == 2 ? 'selected' : ''; ?>>Term 2</option>
                        <option value="3" <?php echo $term == 3 ? 'selected' : ''; ?>>Term 3</option>
                    </select>
                </div>
                
                <div class="form-group mr-3">
                    <label for="year" class="mr-2">Year:</label>
                    <input type="text" name="year" id="year" class="form-control" value="<?php echo htmlspecialchars($year); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                <a href="student_performance.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Report Results</span>
            <a href="export_performance.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
                Export to Excel
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Average Score</th>
                            <th>Assignments Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . 
                            ' ' .  htmlspecialchars($row['last_name']);      ?></td>
                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo number_format($row['average_score'], 2); ?></td>
                            <td><?php echo $row['assignments_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once "../../includes/footer.php"; ?>