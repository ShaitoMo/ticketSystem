<?php
class Attachment {
    private $conn;
    private $table_name = "attachments";

    public $id;
    public $ticket_id;
    public $comment_id;
    public $file_path;
    public $related_type; // 'Ticket', 'Comment', 'Solution'
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create an attachment
    public function addAttachments($ticket_id, $comment_id, $file_path, $related_type) {
        $query = "INSERT INTO " . $this->table_name . " SET 
                  ticket_id = :ticket_id, 
                  comment_id = :comment_id, 
                  file_path = :file_path, 
                  related_type = :related_type";
    
        $stmt = $this->conn->prepare($query);
    
        // Bind parameters
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":comment_id", $comment_id);
        $stmt->bindParam(":file_path", $file_path);
        $stmt->bindParam(":related_type", $related_type);
    
        // Execute the statement
        if ($stmt->execute()) {
            // Return the last inserted ID
            return $this->conn->lastInsertId();
        } else {
            return false; // or handle the error as needed
        }
    }
    

    // Read attachments by ticket ID
    public function readByTicketId($ticket_id) {
        $query = "SELECT file_path,related_type FROM " . $this->table_name . " WHERE ticket_id = :ticket_id AND related_type = 'Ticket'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function SolutionByTicketId($ticket_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ticket_id = :ticket_id AND related_type = 'Solution'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Read attachments by comment ID
    public function readByCommentId($comment_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE comment_id = :comment_id AND related_type = 'Comment'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":comment_id", $comment_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteById($id) {
        // Get the file path before deleting the record
        $query = "SELECT file_path FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attachment) {
            // File path of the attachment to be deleted
            $file_path = "../uploads/solutions/" . $attachment['file_path']; // Adjust path as needed

            // Delete the attachment record from the database
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if ($stmt->execute()) {
                // Optionally delete the file from the server
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                return true;
            }
        }
        return false;
    }
}
?>
