<?php
class Portfolio {
    private $conn;
    private $table_name = "student_portfolios";

    public $id;
    public $student_id;
    public $title;
    public $description;
    public $file_path;
    public $subject_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET student_id=:student_id, title=:title, description=:description, 
                  file_path=:file_path, subject_id=:subject_id, created_at=:created_at";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":subject_id", $this->subject_id);
        $stmt->bindParam(":created_at", $this->created_at);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readByStudent($student_id) {
        $query = "SELECT p.*, s.name as subject_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN subjects s ON p.subject_id = s.id
                  WHERE p.student_id = ?
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            // Delete the associated file
            if($this->file_path && file_exists("../../assets/uploads/" . $this->file_path)) {
                unlink("../../assets/uploads/" . $this->file_path);
            }
            return true;
        }
        return false;
    }
}
?>