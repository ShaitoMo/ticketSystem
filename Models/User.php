<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $password;
    public $email;
    public $role;
    public $department_id;
    public $phone_number;
    public $campus_id; // New property for campus ID

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new user with campus_id
    public function create($first_name, $last_name, $password, $email, $role, $department_id, $phone_number, $campus_id) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, password=:password, email=:email, 
                      role=:role, department_id=:department_id, phone_number=:phone_number, campus_id=:campus_id";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":department_id", $department_id);
        $stmt->bindParam(":phone_number", $phone_number);
        $stmt->bindParam(":campus_id", $campus_id); // Bind campus_id

        return $stmt->execute();
    }

    // Update an existing user with campus_id
    public function update($id, $first_name, $last_name, $email, $role, $department_id, $phone_number, $campus_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, email=:email, role=:role, 
                      department_id=:department_id, phone_number=:phone_number, campus_id=:campus_id 
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":department_id", $department_id);
        $stmt->bindParam(":phone_number", $phone_number);
        $stmt->bindParam(":campus_id", $campus_id); // Bind campus_id

        return $stmt->execute();
    }



    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getUserRole($user_id) {
        $query = "SELECT role FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['role'];
        }
        return null;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserNameById($id) {
        $query = "SELECT first_name, last_name FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['first_name'] . ' ' . $result['last_name'] : null;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function authenticate($id, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($password== $row['password']) {
                return $row; 
            } else {
                return false; 
            }
        } else {
            return false; 
        }
    }

    public function getDepartmentIdByUserId($user_id) {
        $query = "SELECT department_id FROM " . $this->table_name . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['department_id'] : null;
    }
    public function getCampusByUserId($user_id) {
        // Define the query to join the users table with the campuses table
        $query = "SELECT campuses.id AS campus_id, campuses.name AS campus_name 
                  FROM users 
                  JOIN campuses ON users.campus_id = campuses.id 
                  WHERE users.id = :user_id";
        
        // Prepare and execute the statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch the result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null; 
    }
    public function getRoleValues() {
        $query = "SELECT COLUMN_TYPE 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME = :table_name 
                  AND COLUMN_NAME = 'role' 
                  AND TABLE_SCHEMA = DATABASE()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":table_name", $this->table_name);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $enumString = $result['COLUMN_TYPE'];
            // Extract the enum values from the string
            preg_match("/^enum\('(.*)'\)$/", $enumString, $matches);
            if (isset($matches[1])) {
                // Split the values into an array
                $enumValues = explode("','", $matches[1]);
                return $enumValues;
            }
        }

        return [];
    }
    
}

?>
