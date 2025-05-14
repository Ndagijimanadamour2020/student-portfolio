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

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, email=:email, 
                  role=:role, status=:status, created_at=:created_at";
        
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->created_at = date('Y-m-d H:i:s');
        
        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":created_at", $this->created_at);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function authenticate($password) {
        $query = "SELECT id, username, password, role, status FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if(password_verify($password, $row['password'])) {
                if($row['status'] !== 'active') {
                    throw new Exception("Your account is " . $row['status']);
                }
                
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                $this->status = $row['status'];
                
                return true;
            }
        }
        
        return false;
    }
    
    public function logActivity($action, $details = '') {
        $query = "INSERT INTO audit_logs 
                  SET user_id=:user_id, action=:action, details=:details, 
                  ip_address=:ip_address, user_agent=:user_agent, timestamp=:timestamp";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":details", $details);
        $stmt->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(":user_agent", $_SERVER['HTTP_USER_AGENT']);
        $stmt->bindParam(":timestamp", date('Y-m-d H:i:s'));
        
        return $stmt->execute();
    }
    // Add other CRUD methods (read, update, delete) here
}
?>