<?php
class SubTask {
    private $conn;
    private $table_name = "sub_tasks";

    public $id;
    public $ticket_id;
    public $assigned_by;
    public $assigned_to;
    public $sub_task_description;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new sub-task
    public function create($ticket_id, $assigned_by, $assigned_to, $sub_task_description) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET ticket_id=:ticket_id, 
                      assigned_by=:assigned_by, 
                      assigned_to=:assigned_to, 
                      sub_task_description=:sub_task_description,
                      status='In Progress'";
                      
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":assigned_by", $assigned_by);
        $stmt->bindParam(":assigned_to", $assigned_to);
        $stmt->bindParam(":sub_task_description", $sub_task_description);

        return $stmt->execute();
    }

    // Retrieve sub-tasks by ticket ID
    public function getSubTasksByTicketId($ticket_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update the status of a sub-task
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status=:status, updated_at=CURRENT_TIMESTAMP WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

   
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

   
    public function countSubTasksByTicketId($ticket_id) {
        $query = "SELECT COUNT(*) as sub_task_count FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['sub_task_count'];
    }

    
    public function getSubTasksByAssignedTo($assigned_to) {
    $query = "SELECT * FROM " . $this->table_name . " WHERE assigned_to = :assigned_to";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":assigned_to", $assigned_to);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function countSubTasksByAssignedTo($assigned_to) {
    $query = "SELECT COUNT(*) as sub_task_count FROM " . $this->table_name . " WHERE assigned_to = :assigned_to";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":assigned_to", $assigned_to);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['sub_task_count'];
    }
    public function userHasSubTaskForTicket($ticket_id, $assigned_to) {
        $query = "SELECT COUNT(*) as sub_task_count FROM " . $this->table_name . " 
                  WHERE ticket_id = :ticket_id AND assigned_to = :assigned_to";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":assigned_to", $assigned_to);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return true if count is greater than 0, otherwise false
        return $row['sub_task_count'] > 0;
    }


}
?>
