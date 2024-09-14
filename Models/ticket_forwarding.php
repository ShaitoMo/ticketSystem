<?php
class TicketForwarding {
    private $conn;
    private $table_name = "ticket_forwarding"; // Name of the forwarding table

    public $id;
    public $ticket_id;
    public $from_campus_id;
    public $to_campus_id;
    public $forwarded_at;
    public $forwarded_by_user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Forward a ticket to another campus
    public function forwardTicket($ticket_id, $from_campus_id, $to_campus_id, $forwarded_by_user_id) {
        $query = "INSERT INTO " . $this->table_name . " (ticket_id, forwarded_from_campus_id, forwarded_to_campus_id, forwarded_by_user_id, forwarded_at) 
                  VALUES (:ticket_id, :forwarded_from_campus_id, :forwarded_to_campus_id, :forwarded_by_user_id, NOW())";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":forwarded_from_campus_id", $from_campus_id);
        $stmt->bindParam(":forwarded_to_campus_id", $to_campus_id);
        $stmt->bindParam(":forwarded_by_user_id", $forwarded_by_user_id);

        // Execute and return the result
        return $stmt->execute();
    }

    // Get the forwarding history of a ticket
    public function getForwardingHistory($ticket_id) {
        $query = "SELECT tf.*, c1.name as from_campus, c2.name as to_campus, u.first_name as forwarded_by 
                  FROM " . $this->table_name . " tf
                  JOIN campuses c1 ON tf.forwarded_from_campus_id = c1.id
                  JOIN campuses c2 ON tf.forwarded_to_campus_id = c2.id
                  JOIN users u ON tf.forwarded_by_user_id = u.id
                  WHERE tf.ticket_id = :ticket_id
                  ORDER BY tf.forwarded_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get the current campus of a ticket
    public function getCurrentCampus($ticket_id) {
        $query = "SELECT forwarded_to_campus_id FROM " . $this->table_name . " 
                  WHERE ticket_id = :ticket_id 
                  ORDER BY forwarded_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['forwarded_to_campus_id'] : null; // Return null if no forwarding exists
    }
    public function getDistinctForwardedTicketsByUser($user_id) {
        $query = "SELECT DISTINCT tf.ticket_id,tf.forwarded_at, t.subject, t.status, t.priority, c1.name as from_campus, c2.name as to_campus
                  FROM " . $this->table_name . " tf
                  JOIN tickets t ON tf.ticket_id = t.id
                  JOIN campuses c1 ON tf.forwarded_from_campus_id = c1.id
                  JOIN campuses c2 ON tf.forwarded_to_campus_id = c2.id
                  WHERE tf.forwarded_by_user_id = :user_id
                  ORDER BY tf.forwarded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
