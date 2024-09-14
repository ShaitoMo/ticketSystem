<?php
class ApprovalRequest {
    private $conn;
    private $table_name = "approval_requests";

    public $id;
    public $ticket_id;
    public $requested_by;
    public $requested_to;
    public $request_description;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new approval request
    public function create($ticket_id, $requested_by, $requested_to, $request_description) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET ticket_id=:ticket_id, 
                      requested_by=:requested_by, 
                      requested_to=:requested_to, 
                      request_description=:request_description,
                      status='Pending'";
                      
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":requested_by", $requested_by);
        $stmt->bindParam(":requested_to", $requested_to);
        $stmt->bindParam(":request_description", $request_description);

        return $stmt->execute();
    }

    // Retrieve approval requests by ticket ID
    public function getRequestsByTicketId($ticket_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update the status of an approval request
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status=:status, updated_at=CURRENT_TIMESTAMP WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Delete an approval request by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Count approval requests by ticket ID
    public function countRequestsByTicketId($ticket_id) {
        $query = "SELECT COUNT(*) as request_count FROM " . $this->table_name . " WHERE ticket_id = :ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['request_count'];
    }
    // Retrieve approval requests by requested_to user ID
    public function getRequestsByRequestedTo($requested_to, $status = '') {
        // Base query to get requests
        $query = "SELECT * FROM " . $this->table_name . " WHERE requested_to = :requested_to";
    
       
        if ($status !== '') {
           
            if ($status === 'archived') {
                $query .= " AND status <> 'Pending'";
            } else {
                $query .= " AND status = :status";
            }
        }
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":requested_to", $requested_to);
    
      
        if ($status !== '' && $status !== 'archived') {
            $stmt->bindParam(":status", $status);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function userHasApprovalRequestForTicket($ticket_id, $requested_to) {
        $query = "SELECT COUNT(*) as request_count FROM " . $this->table_name . " 
                  WHERE ticket_id = :ticket_id AND requested_to = :requested_to";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":requested_to", $requested_to);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return true if count is greater than 0, otherwise false
        return $row['request_count'] > 0;
    }
    
}
?>
