<?php 
include_once "../../config/database.php";
include_once "../../includes/User.php";
include_once "../../includes/header.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$stmt = $user->readAll();

// Display success/error messages
if(isset($_GET['message'])) {
    echo "<div class='alert alert-success'>".$_GET['message']."</div>";
}
if(isset($_GET['error'])) {
    echo "<div class='alert alert-danger'>".$_GET['error']."</div>";
}
?>

<div class="container">
    <h2>User Management</h2>
    <a href="create.php" class="btn btn-primary mb-3">Create New User</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo ucfirst($row['role']); ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include_once "../../includes/footer.php"; ?>