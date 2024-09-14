<?php
class UserRole {
    private $conn;
    private $table_name = "it_roles";

    public $role_id;

    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }


    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function readOne($role_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE role_id = :role_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":role_id", $role_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function readByUserId($user_id) {
 
            // Define the query to fetch only role IDs for the given user ID
            $query = "SELECT role_id FROM " . $this->table_name . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
        
            // Bind the user_id parameter to the query
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
        
            // Fetch all role IDs as an associative array
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            // Extract role IDs from the result
            $roleIds = [];
            foreach ($result as $row) {
                $roleIds[] = $row['role_id'];
            }
        
            return $roleIds;
        }
        


  
        public function updateRoles($user_id, $role_ids) {
            // Delete existing roles for the user
            $stmt = $this->conn->prepare("DELETE FROM it_roles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute() === false) {
                return false; // Return false if delete fails
            }
        
            // Insert new roles for the user
            $stmt = $this->conn->prepare("INSERT INTO it_roles (user_id, role_id) VALUES (:user_id, :role_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
            foreach ($role_ids as $role_id) {
                $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
                if ($stmt->execute() === false) {
                    return false; // Return false if any insert fails
                }
            }
        
            return true; // Return true if all operations are successful
        }
        

  
    public function delete($role_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE role_id = :role_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":role_id", $role_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function countByRoleId($role_id) {
        $query = "SELECT COUNT(*) as user_count FROM " . $this->table_name . " WHERE role_id = :role_id";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":role_id", $role_id);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['user_count'];
    }
    
    public function countTicketsByRoleId($role_id) {
        $query = "
            SELECT COUNT(tickets.id) as ticket_count
            FROM tickets
            INNER JOIN users ON tickets.assigned_to = users.id
            INNER JOIN it_roles ON users.id = it_roles.user_id
            INNER JOIN roles ON it_roles.role_id = roles.id
            WHERE roles.id = :role_id
        ";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role_id", $role_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['ticket_count'];
    }
    
    
    
}
?>
