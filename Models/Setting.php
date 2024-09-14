<?php
class Setting {
    private $conn;
    private $table_name = "settings";

    public $id;
    public $name;
    public $value;

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function create($name, $value) {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, value=:value";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":value", $value);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['name']] = $row['value'];
        }

        return $settings;
    }
    

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

 
    public function update( $name, $value) {
        $query = "UPDATE " . $this->table_name . " SET  value = :value WHERE name = :name";
        $stmt = $this->conn->prepare($query);

       
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":value", $value);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

   
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
