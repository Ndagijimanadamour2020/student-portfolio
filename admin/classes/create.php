<?php include_once "../../config/database.php"; ?>
<?php include_once "../../includes/header.php"; ?>

<div class="container">
    <h2>Create New Class</h2>
    <form action="process_create.php" method="post">
        <div class="form-group">
            <label>Class Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Stream</label>
            <input type="text" name="stream" class="form-control">
        </div>
        <div class="form-group">
            <label>Class Teacher</label>
            <select name="teacher_id" class="form-control">
                <option value="">Select Teacher</option>
                <?php
                $teacher_query = "SELECT id, username FROM users WHERE role='teacher'";
                $stmt = $db->prepare($teacher_query);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='".$row['id']."'>".$row['username']."</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Class</button>
    </form>
</div>

<?php include_once "../../includes/footer.php"; ?>