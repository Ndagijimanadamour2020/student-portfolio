<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // In admin/includes/User.php

    // ... existing properties and constructor ...

    // Read single user
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Read all users with pagination
    public function readAll($page = 1, $records_per_page = 10) {
        $offset = ($page - 1) * $records_per_page;
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY created_at DESC 
                  LIMIT :offset, :records_per_page";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, first_name = :first_name, 
                  last_name = :last_name, role = :role, status = :status, 
                  updated_at = :updated_at 
                  WHERE id = :id";
                  
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind params
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            $this->logActivity('user_update', "Updated user #{$this->id}");
            return true;
        }
        return false;
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            $this->logActivity('user_delete', "Deleted user #{$this->id}");
            return true;
        }
        return false;
    }

    // Bulk import users from CSV
    public function bulkImport($file_path) {
        $imported = 0;
        $errors = [];
        
        if(($handle = fopen($file_path, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while(($data = fgetcsv($handle)) !== FALSE) {
                if(count($data) < 6) continue;
                
                list($username, $email, $first_name, $last_name, $role, $status) = $data;
                
                // Validate data
                if(empty($username) || empty($email) || empty($role)) {
                    $errors[] = "Missing required fields in row: " . implode(',', $data);
                    continue;
                }
                
                // Check if user exists
                $check_query = "SELECT id FROM " . $this->table_name . " 
                               WHERE username = ? OR email = ? LIMIT 1";
                $check_stmt = $this->conn->prepare($check_query);
                $check_stmt->execute([$username, $email]);
                
                if($check_stmt->rowCount() > 0) {
                    $errors[] = "User {$username} or email {$email} already exists";
                    continue;
                }
                
                // Create user
                $this->username = $username;
                $this->email = $email;
                $this->first_name = $first_name;
                $this->last_name = $last_name;
                $this->role = $role;
                $this->status = $status;
                $this->password = password_hash('default123', PASSWORD_BCRYPT);
                
                if($this->create()) {
                    $imported++;
                } else {
                    $errors[] = "Failed to create user {$username}";
                }
            }
            fclose($handle);
        }
        
        $this->logActivity('bulk_import', "Imported {$imported} users");
        return ['imported' => $imported, 'errors' => $errors];
    }
    // Add this authenticate method
    public function authenticate($username, $password) {
        $query = "SELECT id, username, password, role, status 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if(password_verify($password, $row['password'])) {
                // Password is correct
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                $this->status = $row['status'];
                return true;
            }
        }

        // Authentication failed
        return false;
    }
}

?>