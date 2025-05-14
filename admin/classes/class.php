<?php
class SchoolClass {
    private $conn;
    private $table_name = "classes";

    public $id;
    public $name;
    public $stream;
    public $teacher_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, stream=:stream, teacher_id=:teacher_id, created_at=:created_at";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->stream = htmlspecialchars(strip_tags($this->stream));
        $this->teacher_id = htmlspecialchars(strip_tags($this->teacher_id));
        $this->created_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":stream", $this->stream);
        $stmt->bindParam(":teacher_id", $this->teacher_id);
        $stmt->bindParam(":created_at", $this->created_at);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>