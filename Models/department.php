<?php
class Department {
    private $conn;
    private $table_name = "departments";

    public $id;
    public $name;
    public $department_head_id;
    public $type; // Add this if you are storing the type in the class

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $department_head_id, $type) {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, department_head_id=:department_head_id, type=:type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":department_head_id", $department_head_id);
        $stmt->bindParam(":type", $type);
        return $stmt->execute();
    }

    public function update($id, $name, $department_head_id, $type) {
        $query = "UPDATE " . $this->table_name . " SET name=:name, department_head_id=:department_head_id, type=:type WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":department_head_id", $department_head_id);
        $stmt->bindParam(":type", $type);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getDepartmentById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function getAllDepartments() {
    $query = "SELECT * FROM departments";  // Assuming the table name is 'departments'
    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    // Fetch all departments as an associative array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getDepartmentNameById($id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : null;
    }

    public function getDepartmentHeadId($id) {
        $query = "SELECT department_head_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['department_head_id'] : null;
    }

    public function getDepartmentTypeById($id) {
        $query = "SELECT type FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['type'] : null;
    }

    public function isHeadOfDepartment($user_id) {
        $query = "SELECT department_head_id FROM " . $this->table_name . " WHERE department_head_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
