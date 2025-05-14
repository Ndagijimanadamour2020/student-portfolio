<?php
require_once "../../includes/auth.php";
requireRole('admin');
require_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

// Get filter parameters from URL
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$term = isset($_GET['term']) ? (int)$_GET['term'] : 1;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Build the same query as in the report
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

$query .= " GROUP BY u.id, c.name, s.name
            ORDER BY c.name, u.last_name, u.first_name, s.name";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="student_performance_'.date('Ymd').'.xls"');
header('Cache-Control: max-age=0');

// Start Excel content
echo "<table border='1'>";
echo "<tr>
        <th>Student</th>
        <th>Class</th>
        <th>Subject</th>
        <th>Average Score</th>
        <th>Assignments Count</th>
      </tr>";

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>";
    echo "<td>".htmlspecialchars($row['class_name'])."</td>";
    echo "<td>".htmlspecialchars($row['subject_name'])."</td>";
    echo "<td>".number_format($row['average_score'], 2)."</td>";
    echo "<td>".$row['assignments_count']."</td>";
    echo "</tr>";
}

echo "</table>";
exit;
?>