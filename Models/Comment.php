<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $ticket_id;
    public $user_id;
    public $comment;
    public $private;  // New attribute
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($ticket_id, $user_id, $comment, $private = 0) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET ticket_id=:ticket_id, user_id=:user_id, comment=:comment, private=:private";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":private", $private);
        
        if ($stmt->execute()) {
            // Return the ID of the newly created comment
            return $this->conn->lastInsertId();
        } else {
            // Return false if the insert fails
            return false;
        }
    }
    

    public function getCommentsByTicketId($ticket_id, $private) {
        // Start building the query
        $query = "SELECT * FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        
        // Check if the $private parameter should filter results
        if ($private !== null) {
            $query .= " AND private = :private";
        }
        
        // Add the ORDER BY clause
        $query .= " ORDER BY created_at DESC";
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        
        // Bind the $private parameter if it's provided
        if ($private !== null) {
            $stmt->bindParam(":private", $private);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function countCommentsByTicketId($ticket_id) {
        $query = "SELECT COUNT(*) as comment_count FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['comment_count'];
    }

}
?>
