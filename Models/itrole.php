<?php
class Role {
    private $conn;
    private $table_name = "roles";

    public $id;
    public $name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name) {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        return $stmt->execute();
    }
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        // Fetch all results
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $roles;
    }
    
    public function getRoleById($id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : null; 
    }
    
    

    public function countTicketsByItRole($role_id) {
        $query = "SELECT COUNT(t.id) AS ticket_count FROM tickets t JOIN users u ON t.assigned_to = u.id WHERE u.itrole_id = :role_id" ;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['ticket_count'] : 0; 
    }
}
?>
