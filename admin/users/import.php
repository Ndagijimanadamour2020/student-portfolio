<?php
require_once "../../includes/auth.php";
requireRole('admin');
require_once "../../config/database.php";
require_once "../../includes/User.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';
$error = '';
$import_result = null;

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {
    // Validate CSRF token
    if(!validate_csrf($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    // Check if file was uploaded
    if(empty($_FILES['import_file']['name'])) {
        $error = "Please select a file to import";
    } else {
        // Handle file upload
        list($success, $result) = handle_upload(
            $_FILES['import_file'],
            ['csv'],
            5 * 1024 * 1024 // 5MB max
        );
        
        if($success) {
            $import_result = $user->bulkImport("../../assets/uploads/" . $result);
            
            if($import_result['imported'] > 0) {
                $message = "Successfully imported {$import_result['imported']} users";
            }
            
            if(!empty($import_result['errors'])) {
                $error = "Some users were not imported:<br>" . implode("<br>", $import_result['errors']);
            }
            
            // Delete the temp file
            unlink("../../assets/uploads/" . $result);
        } else {
            $error = implode("<br>", $result);
        }
    }
}
?>
<?php include_once "../../includes/header.php"; ?>

<div class="container">
    <h2>Bulk Import Users</h2>
    
    <?php if($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            Import Instructions
        </div>
        <div class="card-body">
            <ol>
                <li>Download the <a href="sample_users.csv" download>sample CSV file</a></li>
                <li>Fill in the user data following the format</li>
                <li>Upload the file using the form below</li>
            </ol>
            <p class="mb-0"><strong>Note:</strong> The password for all imported users will be set to "default123". Users should change their password after first login.</p>
        </div>
    </div>
    
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="form-group">
            <label for="import_file">CSV File</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="import_file" name="import_file" accept=".csv" required>
                <label class="custom-file-label" for="import_file">Choose file</label>
            </div>
        </div>
        
        <button type="submit" name="import" class="btn btn-primary">Import Users</button>
        <a href="list.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
// Update file input label with selected filename
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : "Choose file";
    e.target.nextElementSibling.innerText = fileName;
});
</script>

<?php include_once "../../includes/footer.php"; ?>