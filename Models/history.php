<?php
class TicketHistory {
    private $db;
    private $table_name = "ticket_history";

    public function __construct($db) {
        $this->db = $db;
    }

    public function addHistory($ticketId, $newStatus, $changedBy) {
        $query = $this->db->prepare("INSERT INTO " . $this->table_name . " (ticket_id, new_status, changed_by, changed_at) VALUES (?, ?, ?, NOW())");
        if ($query->execute([$ticketId, $newStatus, $changedBy])) {
            return true;
        } else {
            return false;
        }
    }

    public function getHistoryByTicketId($ticketId) {
        $query = "SELECT th.id, th.ticket_id, th.new_status, th.changed_by, th.changed_at, u.first_name AS changed_by_username
                  FROM ticket_history th
                  JOIN users u ON th.changed_by = u.id
                  WHERE th.ticket_id = ?
                  ORDER BY th.changed_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLatestHistory($limit) {
        $query = "SELECT th.id, th.ticket_id, th.new_status, th.changed_by, th.changed_at, u.first_name AS changed_by_username
                  FROM ticket_history th
                  JOIN users u ON th.changed_by = u.id
                  ORDER BY th.changed_at DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
    
        // Bind the parameter
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
