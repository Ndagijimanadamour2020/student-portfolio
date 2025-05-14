<?php 
include_once "../../config/database.php";
include_once "../../includes/header.php";

// Handle form submission
if($_POST){
    $current_term = $_POST['current_term'];
    $current_year = $_POST['current_year'];
    $term_start = $_POST['term_start'];
    $term_end = $_POST['term_end'];
    
    $query = "UPDATE system_settings SET 
              current_term = ?, current_year = ?, term_start = ?, term_end = ? 
              WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$current_term, $current_year, $term_start, $term_end]);
    
    $message = "Term settings updated successfully";
}

// Get current settings
$query = "SELECT * FROM system_settings WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Academic Term Settings</h2>
    
    <?php if(isset($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Current Term</label>
            <select name="current_term" class="form-control" required>
                <option value="1" <?php echo ($settings['current_term'] == 1) ? 'selected' : ''; ?>>Term 1</option>
                <option value="2" <?php echo ($settings['current_term'] == 2) ? 'selected' : ''; ?>>Term 2</option>
                <option value="3" <?php echo ($settings['current_term'] == 3) ? 'selected' : ''; ?>>Term 3</option>
            </select>
        </div>
        <div class="form-group">
            <label>Current Year</label>
            <input type="number" name="current_year" class="form-control" 
                   value="<?php echo $settings['current_year']; ?>" required>
        </div>
        <div class="form-group">
            <label>Term Start Date</label>
            <input type="date" name="term_start" class="form-control" 
                   value="<?php echo $settings['term_start']; ?>" required>
        </div>
        <div class="form-group">
            <label>Term End Date</label>
            <input type="date" name="term_end" class="form-control" 
                   value="<?php echo $settings['term_end']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php include_once "../../includes/footer.php"; ?>