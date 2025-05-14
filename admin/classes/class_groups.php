<?php
require_once '../config/database.php';

$groups = $pdo->query("SELECT * FROM class_groups ORDER BY group_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HTML -->
<div class="main-content">
    <h3>Class Groups</h3>
    <form action="group_action.php" method="POST" class="row g-3 mb-4">
        <div class="col-md-5">
            <input type="text" name="group_name" class="form-control" placeholder="e.g. P1, S2" required>
        </div>
        <div class="col-md-5">
            <select name="level" class="form-select" required>
                <option value="">Select Level</option>
                <option value="Nursery">Nursery</option>
                <option value="Primary">Primary</option>
                <option value="Secondary">Secondary</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add_group" class="btn btn-success w-100">Add Group</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Group Name</th>
                <th>Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group): ?>
            <tr>
                <td><?= htmlspecialchars($group['group_name']) ?></td>
                <td><?= $group['level'] ?></td>
                <td>
                    <a href="group_action.php?action=delete&id=<?= $group['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this group?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
