<?php
require_once '../config/database.php';

$message = ''; // Initialize message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if student_id is set
    if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
        $message = "<div class='alert alert-danger'>Student ID is missing.</div>";
    } else {
        $studentId = $_POST['student_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $extra = $_POST['extra_curricular'];

        // Handle file upload
        if (isset($_FILES['portfolio_file']) && $_FILES['portfolio_file']['error'] === 0) {
            $targetDir = "../uploads/portfolio/";
            $fileName = basename($_FILES["portfolio_file"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            // Validate file type
            $validTypes = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            if (!in_array(strtolower($fileType), $validTypes)) {
                $message = "<div class='alert alert-danger'>Invalid file format. Only PDF, DOCX, JPG, JPEG, PNG are allowed.</div>";
            } else {
                if (move_uploaded_file($_FILES["portfolio_file"]["tmp_name"], $targetFilePath)) {
                    // Insert into database
                    $stmt = $pdo->prepare("INSERT INTO student_portfolios (student_id, title, description, file_path, extra_curricular) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$studentId, $title, $description, $targetFilePath, $extra])) {
                        $message = "<div class='alert alert-success'>Portfolio submitted successfully!</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Something went wrong. Portfolio not submitted.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>File upload failed. Please try again.</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>No file uploaded. Please choose a file to upload.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Submit Portfolio</h2>

    <?php echo isset($message) ? $message : ''; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control" value="<?php echo isset($studentId) ? htmlspecialchars($studentId) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Portfolio Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Portfolio Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="extra_curricular" class="form-label">Extra-Curricular Activities</label>
            <textarea name="extra_curricular" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="portfolio_file" class="form-label">Upload Portfolio File</label>
            <input type="file" name="portfolio_file" class="form-control" accept=".pdf,.docx,.jpg,.jpeg,.png" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit Portfolio</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="card-footer text-center bg-light mt-3">
            <a href="dashboard.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
