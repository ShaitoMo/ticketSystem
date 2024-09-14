<?php
class TeamLeader {
    private $conn;
    private $table_name = "team_leaders";

    public $id;
    public $category_id;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

  
    public function create($category_id, $user_id) {
        $query = "INSERT INTO " . $this->table_name . " SET category_id = :category_id, user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

  
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Read team leaders by category ID
    public function readByCategoryId($category_id) {
        $query = "SELECT user_id  FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update a team leader
    public function InsertTeamLeader($category_id, $user_id) {
        // Check if a record exists with the given category_id
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE category_id = :category_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":category_id", $category_id);
        $checkStmt->execute();
    
        if ($checkStmt->rowCount() > 0) {
            // If record exists, update the user_id
            $updateQuery = "UPDATE " . $this->table_name . " SET user_id = :user_id WHERE category_id = :category_id";
            $updateStmt = $this->conn->prepare($updateQuery);
    
            $updateStmt->bindParam(":category_id", $category_id);
            $updateStmt->bindParam(":user_id", $user_id);
    
            if ($updateStmt->execute()) {
                return true;
            }
        } else {
            // If no record exists, insert a new team leader
            $insertQuery = "INSERT INTO " . $this->table_name . " (category_id, user_id) VALUES (:category_id, :user_id)";
            $insertStmt = $this->conn->prepare($insertQuery);
    
            $insertStmt->bindParam(":category_id", $category_id);
            $insertStmt->bindParam(":user_id", $user_id);
    
            if ($insertStmt->execute()) {
                return true;
            }
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
  /*  public function getUsersByCategoryParentId($parent_id) {

        $categoryQuery = "SELECT id FROM " . $this->table_name . " WHERE parent_id = :parent_id";
        $categoryStmt = $this->conn->prepare($categoryQuery);
        $categoryStmt->bindParam(":parent_id", $parent_id);
        $categoryStmt->execute();
        $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($categories)) {
            return []; 
        }

        
        $roleQuery = "SELECT DISTINCT role_id FROM roles WHERE id IN (SELECT itrole_id FROM categories WHERE id IN (" . implode(',', array_fill(0, count($categories), '?')) . "))";
        $roleStmt = $this->conn->prepare($roleQuery);
        $roleStmt->execute($categories);
        $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($roles)) {
            return []; 
        }

        
        $userQuery = "SELECT DISTINCT u.id, u.first_name, u.email
                      FROM users u
                      JOIN it_roles ir ON u.id = ir.user_id
                      WHERE ir.role_id IN (" . implode(',', array_fill(0, count($roles), '?')) . ")";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->execute($roles);

        return $userStmt->fetchAll(PDO::FETCH_ASSOC);
    }*/
    public function isUserLeader($user_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":user_id", $user_id);
    
        $stmt->execute();
    
        
        $count = $stmt->fetchColumn();
    
        // If count is greater than 0, the user is a leader for at least one category
        return $count > 0;
    }
    public function getUserTeam($user_id) {
    
            // SQL query to get the first parent category of a user's role
            $query = "
                SELECT c2.id , c2.name 
                FROM categories c1
                JOIN categories c2 ON c1.parent_id = c2.id
                JOIN it_roles ir ON c1.itrole_id = ir.role_id
                WHERE ir.user_id = :user_id
                LIMIT 1
            ";
        
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
        
            // Fetch the first result
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function getTeamsLedByUser($user_id) {
            $query = "SELECT category_id as id FROM " . $this->table_name . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
        
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
        
            // Fetch all results
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public function isUserLeaderForCategory($category_id, $user_id) {
            // SQL query to check if the user is a leader for the specified category
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE category_id = :category_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
        
            $stmt->bindParam(":category_id", $category_id);
            $stmt->bindParam(":user_id", $user_id);
        
            $stmt->execute();
        
            $count = $stmt->fetchColumn();
        
            // If count is greater than 0, the user is a leader for the specified category
            return $count > 0;
        }
        
    
    
}
?>
