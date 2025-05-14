<?php 
include_once "../../config/database.php";
include_once "../../includes/header.php";

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$records_per_page = 20;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM audit_logs";
$stmt = $db->prepare($total_query);
$stmt->execute();
$total_row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get logs
$query = "SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT :offset, :records_per_page";
$stmt = $db->prepare($query);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
?>

<div class="container">
    <h2>Audit Logs</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['action']; ?></td>
                <td><?php echo $row['details']; ?></td>
                <td><?php echo $row['ip_address']; ?></td>
                <td><?php echo $row['timestamp']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php if($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a></li>
            <?php endif; ?>
            
            <?php for($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php include_once "../../includes/footer.php"; ?>