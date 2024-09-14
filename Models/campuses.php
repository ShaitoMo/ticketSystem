<?php
class Campus {
    private $conn;
    private $table_name = "campuses"; // Name of the table in your database

    public $id;
    public $name; 

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new campus
    public function create($name) {
        $query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":name", $name);

        return $stmt->execute();
    }

    // Retrieve all campuses
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve a campus by ID
    public function getCampusNameById($id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
    
        // Bind the ID as an integer (or the appropriate type)
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    
        // Execute the query
        $stmt->execute();
    
        // Fetch only the 'name' field directly
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Return the 'name' if found, otherwise return null or a default value
        return $row ? $row['name'] : null;
    }
    

    // Update a campus
    public function update($id, $name) {
        $query = "UPDATE " . $this->table_name . " SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Delete a campus
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    public function isBeirutCampus($id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $campus = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($campus && strtolower($campus['name']) === 'beirut') {
            return true;
        }
        return false;
    }
}
?>
