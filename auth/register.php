<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_functions.php';

// Redirect logged in users away from registration page
if (isLoggedIn()) {
    redirectBasedOnRole();
}

$errors = [];
$success = '';
$username = '';
$email = '';
$role = 'student'; // Default role
$first_name = '';
$last_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $errors['general'] = 'Username or email already exists';
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                $user_id = $pdo->lastInsertId();

                // Insert role-specific data
                if ($role === 'student') {
                    $admission_number = 'ADM' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("INSERT INTO students (user_id, first_name, last_name, admission_number) 
                                          VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $first_name, $last_name, $admission_number]);
                } elseif ($role === 'teacher') {
                    $staff_id = 'TCH' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("INSERT INTO teachers (user_id, first_name, last_name, staff_id) 
                                          VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $first_name, $last_name, $staff_id]);
                } elseif ($role === 'parent') {
                    $stmt = $pdo->prepare("INSERT INTO parents (user_id, first_name, last_name) 
                                          VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $first_name, $last_name]);
                }

                $pdo->commit();
                $success = 'Registration successful! You can now login.';
                
                // Clear form
                $username = $email = $first_name = $last_name = '';
                $role = 'student';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['general'] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="text-center"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <a href="login.php" class="alert-link">Click here to login</a>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-12">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                               id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                        <?php if (isset($errors['username'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                               id="password" name="password">
                                        <?php if (isset($errors['password'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                               id="confirm_password" name="confirm_password">
                                        <?php if (isset($errors['confirm_password'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="role" class="form-label">Account Type</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                        <option value="parent" <?php echo $role === 'parent' ? 'selected' : ''; ?>>Parent</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Register
                                    </button>
                                </div>
                                
                                <div class="col-12 text-center">
                                    <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                document.getElementById('confirm_password').focus();
            }
        });
    </script>
</body>
</html>