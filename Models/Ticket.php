<?php
class Ticket {
    private $conn;
    private $table_name = "tickets";

    public $id;
    public $subject;
    public $description;
    public $category_id;
    public $priority;
    public $status;
    public $created_by;
    public $assigned_to;
    public $assigned_at;
    public $created_at;
    public $updated_at;
    public $resolved_at;
    public $campus_id;
    public $forwarded_to_campus_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new ticket
    public function create($subject, $description, $category_id, $priority, $created_by, $ticket_type, $campus_id, $attachments = []) {
        // Insert the ticket details, including campus_id
        $query = "INSERT INTO " . $this->table_name . " 
                  SET subject=:subject, description=:description, category_id=:category_id, 
                      priority=:priority, created_by=:created_by, type=:type, campus_id=:campus_id";
                      
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":priority", $priority);
        $stmt->bindParam(":created_by", $created_by);
        $stmt->bindParam(":type", $ticket_type);
        $stmt->bindParam(":campus_id", $campus_id);
    
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Insert attachments related to the created ticket
            foreach ($attachments as $attachment) {
                $query = "INSERT INTO attachments SET ticket_id=:ticket_id, file_path=:file_path, related_type=:related_type";
                $stmt = $this->conn->prepare($query);
                
                $stmt->bindParam(":ticket_id", $this->id);
                $stmt->bindParam(":file_path", $attachment['file_path']);
                $stmt->bindParam(":related_type", $attachment['related_type']);
                
                // Execute and check for errors
                if (!$stmt->execute()) {
                    // Fetch and display the error
                    $error = $stmt->errorInfo();
                    echo "Error inserting attachment: " . $error[2];
                    return false; // Stop execution if there is an error
                }
            }
            return $this->id; 
        } else {
            // Fetch and display the error
            $error = $stmt->errorInfo();
            echo "Error creating ticket: " . $error[2];
            return false;
        }
    }
    
    
    public function updateTicket($ticket_id, $status, $priority, $assigned_to) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, priority=:priority, assigned_to=:assigned_to
                  WHERE id=:ticket_id";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":priority", $priority);
       
        $stmt->bindParam(":assigned_to", $assigned_to);
        $stmt->bindParam(":ticket_id", $ticket_id);
    
        return $stmt->execute();
    }
    
    
    
    
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getITPersonnel($campusId = null) {
        $query = "SELECT id, first_name,last_name, email, role FROM users WHERE role IN ('IT Personnel', 'IT Coordinator', 'Sub-Admin')";
    
        // If a campusId is provided, add a condition to the query
        if ($campusId !== null) {
            $query .= " AND campus_id = :campusId";
        }
    
        $stmt = $this->conn->prepare($query);
    
        // If a campusId is provided, bind it to the query
        if ($campusId !== null) {
            $stmt->bindParam(':campusId', $campusId, PDO::PARAM_INT);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
         $stmt->execute();
         return true;

    }
    public function updateCategory($id, $category_id) {
        $query = "UPDATE " . $this->table_name . " SET category_id=:category_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":id", $id);
        
         $stmt->execute();
         return true;

    }


    
    
    
    public function getTicketsByStatusAndUser($status, $user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status AND created_by = :user_id ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
        public function countAllTickets() {
            $query = "SELECT COUNT(*) as total_tickets FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total_tickets'];
        }
        public function countTicketsByStatus($status) {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = :status ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
    
        public function countItClosedTickets($assigned_to) {
            $query = "SELECT COUNT(*) as closed_tickets FROM " . $this->table_name . " WHERE status = 'Closed' AND assigned_to = :assigned_to";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":assigned_to", $assigned_to);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['closed_tickets'];
        }
    

        public function getActiveTickets($user_id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE status != 'Closed' AND created_by = :user_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        }
        
        public function countItProgressTickets($assigned_to) {
            $query = "SELECT COUNT(*) as in_progress_tickets 
                      FROM " . $this->table_name . " 
                      WHERE status != 'Closed' 
                      AND assigned_to = :assigned_to 
                      AND approved = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":assigned_to", $assigned_to);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['in_progress_tickets'];
        }
        
        public function getItTickets($userId, $approved = 1) {
            // SQL query to get tickets assigned to a specific user and approved
            $query = "
                SELECT *
                FROM tickets
                WHERE assigned_to = :userId
                AND approved = :approved
            ";
            
            // Prepare the SQL statement
            $stmt = $this->conn->prepare($query);
            
            // Bind the parameters
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':approved', $approved, PDO::PARAM_INT);
            
            // Execute the statement
            $stmt->execute();
            
            // Fetch all results as an associative array
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Close the cursor
            $stmt->closeCursor();
            
            // Return the array of tickets
            return $tickets;
        }
        
        
    
    
        public function getItProgressedTickets($assigned_to, $status = '', $priority = '', $assigned_to_filter = '', $mainCategory = '', $subCategory = '') {
            // Start building the query
            $query = "SELECT * FROM " . $this->table_name . " WHERE status != 'Closed'";
        
            // Add condition for non-null assigned_to
            if ($assigned_to !== 'all') {
                $query .= " AND assigned_to IS NOT NULL";
                if ($assigned_to !== '') {
                    $query .= " AND assigned_to = :assigned_to";
                }
            } else {
                $query .= " AND assigned_to IS NOT NULL"; // Ensure assigned_to is not null
            }
            
            // Add other conditions based on provided parameters
            if ($status !== '') {
                $query .= " AND status = :status";
            }
            if ($priority !== '') {
                $query .= " AND priority = :priority";
            }
            if ($assigned_to_filter !== '') {
                $query .= " AND assigned_to = :assigned_to_filter";
            }
            if ($mainCategory !== '') {
                // Filter by main category
                $query .= " AND category_id IN (SELECT id FROM categories WHERE parent_id = :mainCategory OR id = :mainCategory)";
            }
            if ($subCategory !== '') {
                $query .= " AND category_id = :subCategory";
            }
            
            // Order the results
            $query .= " ORDER BY created_at DESC";
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            if ($assigned_to !== 'all') {
                if ($assigned_to !== '') {
                    $stmt->bindParam(":assigned_to", $assigned_to);
                }
            }
            if ($status !== '') {
                $stmt->bindParam(":status", $status);
            }
            if ($priority !== '') {
                $stmt->bindParam(":priority", $priority);
            }
            if ($assigned_to_filter !== '') {
                $stmt->bindParam(":assigned_to_filter", $assigned_to_filter);
            }
            if ($mainCategory !== '') {
                $stmt->bindParam(":mainCategory", $mainCategory);
            }
            if ($subCategory !== '') {
                $stmt->bindParam(":subCategory", $subCategory);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        




    public function getTicketById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getStatusTicketById($id) {
            $query = "SELECT status FROM " . $this->table_name . " WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT); // Ensure parameter is an integer
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['status'];
            } else {
                return null; 
            }
        }
        public function getCampusTicketById($id) {
            $query = "SELECT campus_id FROM " . $this->table_name . " WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT); // Ensure parameter is an integer
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['campus_id'];
            } else {
                return null; 
            }
        }

        public function getTicketsByUserId($user_id, $approved = 1) {
            // SQL query to get tickets created by a specific user and approved
            $query = "
                SELECT *
                FROM " . $this->table_name . "
                WHERE created_by = :user_id
                AND approved = :approved
            ";
            
            // Prepare the SQL statement
            $stmt = $this->conn->prepare($query);
            
            // Bind the parameters
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":approved", $approved, PDO::PARAM_INT);
            
            // Execute the statement
            $stmt->execute();
            
            // Fetch all results as an associative array
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return the array of tickets
            return $tickets;
        }
        

  
    
    public function assignTicket($ticket_id, $assigned_to) {
        $query = "UPDATE " . $this->table_name . " SET assigned_to=:assigned_to WHERE id=:ticket_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":assigned_to", $assigned_to);
        $stmt->bindParam(":ticket_id", $ticket_id);
        return $stmt->execute();
    }
    public function getAllTickets($status = '', $priority = '', $assigned_to_filter = '', $mainCategory = '', $subCategory = '', $created_by = '', $approved = null, $campus_id = 'all', $offset = 0, $limit = 50) {
        // Base query
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        
        // Add conditions based on provided parameters
        if ($status === 'closed') {
            $query .= " AND status = 'closed'";
        } elseif ($status !== '') {
            $query .= " AND status = :status";
        } else {
            $query .= " AND status != 'closed'";
        }
        
        if ($priority !== '') {
            $query .= " AND priority = :priority";
        }
        if ($assigned_to_filter === 'unassigned') {
            $query .= " AND assigned_to IS NULL";
        } elseif ($assigned_to_filter !== '') {
            $query .= " AND assigned_to = :assigned_to_filter";
        }
        if ($mainCategory !== '') {
            $query .= " AND category_id IN (SELECT id FROM categories WHERE parent_id = :mainCategory OR id = :mainCategory)";
        }
        if ($subCategory !== '') {
            $query .= " AND category_id = :subCategory";
        }
        if ($created_by !== '') {
            $query .= " AND created_by = :created_by";
        }
        
        // Add approved filter
        if ($approved === null) {
            $query .= " AND approved IS NULL";
        } else {
            $query .= " AND approved = :approved";
        }
        
        // Add campus filter, unless 'all' is selected
        if ($campus_id !== 'all') {
            $query .= " AND campus_id = :campus_id";
        }
        
        // Add ordering and limit (Newest to Oldest)
        $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters based on their presence
        if ($status !== '' && $status !== 'closed') {
            $stmt->bindParam(":status", $status);
        }
        if ($priority !== '') {
            $stmt->bindParam(":priority", $priority);
        }
        if ($assigned_to_filter !== '' && $assigned_to_filter !== 'unassigned') {
            $stmt->bindParam(":assigned_to_filter", $assigned_to_filter);
        }
        if ($mainCategory !== '') {
            $stmt->bindParam(":mainCategory", $mainCategory);
        }
        if ($subCategory !== '') {
            $stmt->bindParam(":subCategory", $subCategory);
        }
        if ($created_by !== '') {
            $stmt->bindParam(":created_by", $created_by);
        }
        if ($approved !== null) {
            $stmt->bindParam(":approved", $approved, PDO::PARAM_INT);
        }
        
        // Bind campus_id unless 'all' is selected
        if ($campus_id !== 'all') {
            $stmt->bindParam(":campus_id", $campus_id, PDO::PARAM_INT);
        }
        
        // Bind offset and limit
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute the statement and fetch results
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    
    public function getTeamTickets($mainCategory, $status = '', $priority = '', $assigned_to_filter = 'assigned', $subCategory = '', $created_by = '', $approved = 1, $offset = 0, $limit = 50) {
        // Base query
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id IN (SELECT id FROM categories WHERE parent_id = :mainCategory OR id = :mainCategory) AND campus_id = 1";
        
        // Status filter
        if ($status === 'closed') {
            $query .= " AND status = 'closed'";
        } elseif ($status === 'all') {
            // Do not add a status filter for 'all'
        } elseif ($status !== '') {
            $query .= " AND status = :status";
        } else {
            $query .= " AND status != 'closed'";
        }
        
        // Priority filter
        if ($priority !== '') {
            $query .= " AND priority = :priority";
        }
        
        // Assigned to filter
        if ($assigned_to_filter === 'unassigned') {
            $query .= " AND assigned_to IS NULL";
        } elseif ($assigned_to_filter !== '' && $assigned_to_filter !== 'assigned') {
            $query .= " AND assigned_to = :assigned_to_filter";
        } else {
            $query .= " AND assigned_to IS NOT NULL";
        }
        
        // Subcategory filter
        if ($subCategory !== '') {
            $query .= " AND category_id = :subCategory";
        }
        
        // Created by filter
        if ($created_by !== '') {
            $query .= " AND created_by = :created_by";
        }
        
        // Approved filter
        $query .= " AND approved = :approved";
        
        // Ordering and limit
        $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":mainCategory", $mainCategory, PDO::PARAM_INT);
        
        if ($status !== '' && $status !== 'closed' && $status !== 'all') {
            $stmt->bindParam(":status", $status);
        }
        if ($priority !== '') {
            $stmt->bindParam(":priority", $priority);
        }
        if ($assigned_to_filter !== '' && $assigned_to_filter !== 'unassigned' && $assigned_to_filter !== 'assigned') {
            $stmt->bindParam(":assigned_to_filter", $assigned_to_filter);
        }
        if ($subCategory !== '') {
            $stmt->bindParam(":subCategory", $subCategory);
        }
        if ($created_by !== '') {
            $stmt->bindParam(":created_by", $created_by);
        }
        $stmt->bindParam(":approved", $approved, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute the statement and fetch results
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function countTeamTickets($mainCategory, $status = '', $priority = '', $assigned_to_filter = 'assigned', $subCategory = '', $created_by = '') {
        $query = "SELECT COUNT(*) as ticket_count FROM " . $this->table_name . " WHERE category_id IN (SELECT id FROM categories WHERE parent_id = :mainCategory OR id = :mainCategory)";
        
        if ($status === 'closed') {
            $query .= " AND status = 'closed'";
        } elseif ($status !== '') {
            $query .= " AND status = :status";
        } else {
            $query .= " AND status != 'closed'";
        }
    
        // Priority filter
        if ($priority !== '') {
            $query .= " AND priority = :priority";
        }
    
        // Assigned to filter
        if ($assigned_to_filter === 'unassigned') {
            $query .= " AND assigned_to IS NULL";
        } elseif ($assigned_to_filter !== '' && $assigned_to_filter !== 'assigned') {
            $query .= " AND assigned_to = :assigned_to_filter";
        } else {
            $query .= " AND assigned_to IS NOT NULL";
        }
    
        // Subcategory filter
        if ($subCategory !== '') {
            $query .= " AND category_id = :subCategory";
        }
    
        // Created by filter
        if ($created_by !== '') {
            $query .= " AND created_by = :created_by";
        }
        
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":mainCategory", $mainCategory, PDO::PARAM_INT);
    
        if ($status !== '' && $status !== 'closed') {
            $stmt->bindParam(":status", $status);
        }
        if ($priority !== '') {
            $stmt->bindParam(":priority", $priority);
        }
        if ($assigned_to_filter !== '' && $assigned_to_filter !== 'unassigned' && $assigned_to_filter !== 'assigned') {
            $stmt->bindParam(":assigned_to_filter", $assigned_to_filter);
        }
        if ($subCategory !== '') {
            $stmt->bindParam(":subCategory", $subCategory);
        }
        if ($created_by !== '') {
            $stmt->bindParam(":created_by", $created_by);
        }
        
        // Execute the query and return the ticket count
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['ticket_count'];
    }
    
    

    public function getAssignedTickets($user_id) {
        // SQL query to get tickets assigned to a specific user with approved status as 1
        $query = "SELECT * FROM " . $this->table_name . " WHERE assigned_to = :user_id AND approved = 1";
        
        // Prepare the SQL statement
        $stmt = $this->conn->prepare($query);
        
        // Bind the user ID parameter
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        
        // Execute the statement
        $stmt->execute();
        
        // Fetch all results as an associative array
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Close the cursor
        $stmt->closeCursor();
        
        // Return the array of tickets
        return $tickets;
    }
    
    public function getUserTickets($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE created_by=:user_id ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countTickets($status = '', $priority = '', $assigned_to = '', $category = '') {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE 1=1";
    
        if ($status !== '') {
            $query .= " AND status = :status";
        }
        if ($priority !== '') {
            $query .= " AND priority = :priority";
        }
        if ($assigned_to !== '') {
            $query .= " AND assigned_to = :assigned_to";
        }
        if ($category !== '') {
            $query .= " AND category_id = :category";
        }
    
        $stmt = $this->conn->prepare($query);
    
        if ($status !== '') {
            $stmt->bindParam(":status", $status);
        }
        if ($priority !== '') {
            $stmt->bindParam(":priority", $priority);
        }
        if ($assigned_to !== '') {
            $stmt->bindParam(":assigned_to", $assigned_to);
        }
        if ($category !== '') {
            $stmt->bindParam(":category", $category);
        }
    
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function updateSolution($ticket_id, $solution) {
        $query = "UPDATE " . $this->table_name . " 
                  SET solution=:solution
                  WHERE id=:ticket_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":solution", $solution);
        $stmt->bindParam(":ticket_id", $ticket_id);

        return $stmt->execute();
    }
    public function AutoAssign($ticket_id) {
        try {
            $this->conn->beginTransaction();
    
            // Fetch the ticket details
            $ticket = $this->getTicketById($ticket_id);
            if (!$ticket) {
                throw new Exception("Ticket not found.");
            }
            $categoryId = $ticket['category_id'];
            $createdBy = $ticket['created_by'];
    
            // Query to find the least busy eligible user based on the category's required role
            $query = "
                SELECT u.id, COUNT(t.id) AS ticket_count
                FROM users u
                LEFT JOIN tickets t ON u.id = t.assigned_to AND t.status != 'Closed'
                JOIN it_roles ir ON u.id = ir.user_id
                WHERE u.role = 'IT Personnel'
                AND ir.role_id = (
                    SELECT c.itrole_id
                    FROM categories c
                    WHERE c.id = :category_id
                )
                GROUP BY u.id
                ORDER BY COUNT(t.id) ASC
                LIMIT 1
            ";
    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->execute();
            $eligibleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Check if there are any eligible users
            if (empty($eligibleUsers)) {
                throw new Exception("No eligible users found for the category.");
            }
    
            // Assign the ticket to the least busy user
            $assignedUser = $eligibleUsers[0]['id'];
    
            $query = "UPDATE tickets SET assigned_to = :assigned_to WHERE id = :ticket_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':assigned_to', $assignedUser);
            $stmt->bindParam(':ticket_id', $ticket_id);
            $stmt->execute();
    
            $this->conn->commit();
    
            // Notify the assigned user
            $notification = new Notification($this->conn);
            $messageAssigned = "You have been automatically assigned a new ticket with ID {$ticket_id}.";
            $notification->create($assignedUser, $ticket_id, 'email', $messageAssigned);
    
            // Notify the creator of the ticket
            $messageCreated = "Ticket with ID {$ticket_id} has been automatically assigned to '{$assignedUser}'.";
            $notification->create($createdBy, $ticket_id, 'email', $messageCreated);
    
            return $assignedUser;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
        
            
            public function TicketsForAutoClose($Period) {
                
                $selectQuery = "SELECT id 
                                FROM tickets 
                                WHERE status = 'Resolved' AND DATE_ADD(updated_at, INTERVAL :Period DAY) < NOW()";
                $selectStmt = $this->conn->prepare($selectQuery);
                $selectStmt->bindParam(':Period', $Period, PDO::PARAM_INT);
                $selectStmt->execute();
            
   
                $eligibleTickets = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
            
                
                if (!empty($eligibleTickets)) {
                    echo count($eligibleTickets) . " tickets are eligible to be closed.";
                } else {
                    echo "No tickets are eligible to be closed.";
                }
            
                return $eligibleTickets;
            }

            public function getRecommendedTickets($user_id) {
                $query = "
                    SELECT 
                        t.id, 
                        t.subject, 
                        t.description,
                        t.category_id,
                        t.assigned_to, 
                        t.type, 
                        t.priority, 
                        t.status, 
                        t.created_at
                    FROM 
                        tickets t
                    JOIN 
                        categories c ON t.category_id = c.id
                    JOIN 
                        it_roles ir ON ir.role_id = c.itrole_id
                    WHERE 
                        t.assigned_to IS NULL
                        AND ir.user_id = :user_id 
                          AND approved = 1
                    ORDER BY 
                        t.priority DESC, 
                        t.created_at DESC
                ";
            
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
            
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            public function isTicketInMainCategory($ticketId, $mainCategoryId) {
                $sql = "
                    SELECT c.parent_id 
                    FROM tickets t
                    INNER JOIN categories c ON t.category_id = c.id
                    WHERE t.id = :ticket_id AND c.parent_id = :main_category_id
                ";
                
                
                $stmt = $this->conn->prepare($sql);
            
                
                $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
                $stmt->bindParam(':main_category_id', $mainCategoryId, PDO::PARAM_INT);
            
                
                $stmt->execute();
                
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                
                if ($result) {
                    return true; 
                } else {
                    return false; 
                }
            }

            public function getResolutionTimeByTicketId($ticketId) {
                // SQL query to calculate the resolution time from assigned_at to resolved_at
                $query = "
                    SELECT 
                        TIMESTAMPDIFF(MINUTE, assigned_at, resolved_at) AS resolution_time
                    FROM tickets
                    WHERE id = :ticketId  AND approved = 1
                ";
                
                // Prepare the SQL statement
                $stmt = $this->conn->prepare($query);
                
                // Bind the ticket ID parameter
                $stmt->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                
                // Execute the statement
                $stmt->execute();
                
                // Fetch the result
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Close the cursor
                $stmt->closeCursor();
                
                // Return the resolution time or default to 0 if no data is found or resolution_time is NULL
                return isset($data['resolution_time']) ? $data['resolution_time'] : 0;
            }

            public function getRecentTickets($user_id = '', $campus_id = 'all', $days_interval = 14, $offset = 0, $limit = 100) {
                // Specify the columns you want to retrieve
                $columns = "
                    category_id,
                    priority,
                    status,
                    created_by,
                    assigned_to,
                    assigned_at,
                    created_at,
                    updated_at,
                    resolved_at
                ";
                
                // Base query with specified columns, using the editable days_interval
                $query = "SELECT $columns FROM " . $this->table_name . " WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days_interval DAY) AND approved = 1";
                
                // Filter by user_id if provided and not 'all'
                if ($user_id !== '' && $user_id !== 'all') {
                    $query .= " AND assigned_to = :user_id";
                }
                
                // Filter by campus_id if provided and not 'all'
                if ($campus_id !== 'all') {
                    $query .= " AND campus_id = :campus_id";
                }
                
                // Add ordering and pagination
                $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
                
                // Prepare the statement
                $stmt = $this->conn->prepare($query);
                
                // Bind parameters
                $stmt->bindParam(":days_interval", $days_interval, PDO::PARAM_INT);
            
                if ($user_id !== '' && $user_id !== 'all') {
                    $stmt->bindParam(":user_id", $user_id);
                }
            
                if ($campus_id !== 'all') {
                    $stmt->bindParam(":campus_id", $campus_id, PDO::PARAM_INT);
                }
            
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
                
                // Execute and fetch results
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            
            
            public function getTicketsRequiringApproval($department_id) {
                $query = "
                SELECT id, subject, description, category_id, priority, status, created_by, created_at
                FROM " . $this->table_name . " 
                WHERE approved IS NULL
                AND created_by IN (
                    SELECT id 
                    FROM users 
                    WHERE department_id = :department_id
                )
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Fetch all results as an associative array
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Close the cursor
            $stmt->closeCursor();
            
            // Return the array of tickets
            return $tickets;
            }
            public function updateApprovalStatus($ticket_id, $approval_status) {
                // Ensure the approval status is valid (e.g., "1" for approved, "0" for not approved, or other appropriate values)
                $valid_statuses = [0, 1];
                if (!in_array($approval_status, $valid_statuses)) {
                    throw new InvalidArgumentException('Invalid approval status');
                }
            
                // Prepare the SQL query to update the approval status
                $query = "UPDATE " . $this->table_name . " 
                          SET approved = :approval_status 
                          WHERE id = :ticket_id";
            
                // Prepare and execute the SQL statement
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':approval_status', $approval_status, PDO::PARAM_INT);
                $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
            
                // Execute the statement
                if ($stmt->execute()) {
                    return true; // Return true if the update was successful
                } else {
                    return false; // Return false if there was an error
                }
            }
            
            public function calculateAverageAssignmentTime($days_span = null) {
                // Default to no time span if not provided
                $time_span_condition = '';
                if ($days_span) {
                    $time_span_condition = "AND created_at >= NOW() - INTERVAL :days_span DAY";
                }
                
                $query = "
                    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) as average_assignment_time
                    FROM " . $this->table_name . "
                    WHERE assigned_at IS NOT NULL
                    AND created_at IS NOT NULL
                    $time_span_condition
                ";
                
                $stmt = $this->conn->prepare($query);
                
                // Bind the parameter if a time span is provided
                if ($days_span) {
                    $stmt->bindParam(':days_span', $days_span, PDO::PARAM_INT);
                }
                
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['average_assignment_time'];
            }
            public function calculateAverageResolutionTime($days_span = null) {
                // Default to no time span if not provided
                $time_span_condition = '';
                if ($days_span) {
                    $time_span_condition = "AND assigned_at >= NOW() - INTERVAL :days_span DAY";
                }
                
                $query = "
                    SELECT AVG(TIMESTAMPDIFF(HOUR, assigned_at, resolved_at)) as average_resolution_time
                    FROM " . $this->table_name . "
                    WHERE resolved_at IS NOT NULL
                    AND assigned_at IS NOT NULL
                    $time_span_condition
                ";
                
                $stmt = $this->conn->prepare($query);
                
                // Bind the parameter if a time span is provided
                if ($days_span) {
                    $stmt->bindParam(':days_span', $days_span, PDO::PARAM_INT);
                }
                
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['average_resolution_time'];
            }
            

            public function assignTicketBasedOnCampus($ticket_id, $campus_id) {//to auto assign in campuses that are not beirut 
                try {
                    // Start the transaction
                    $this->conn->beginTransaction();
            
                    // Fetch the ticket details
                    $ticket = $this->getTicketById($ticket_id);
                    if (!$ticket) {
                        throw new Exception("Ticket not found.");
                    }
            
                    // Query to find the IT personnel or coordinator with the least number of open tickets
                    $query = "
                        SELECT u.id, COUNT(t.id) AS ticket_count
                        FROM users u
                        LEFT JOIN tickets t ON u.id = t.assigned_to AND t.status != 'Closed'
                        WHERE (u.role = 'IT Personnel' OR u.role = 'IT Coordinator')
                        AND u.campus_id = :campus_id
                        GROUP BY u.id
                        ORDER BY ticket_count ASC
                        LIMIT 1
                    ";
            
                    // Prepare the statement
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':campus_id', $campus_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $eligibleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    // Check if there are any eligible users
                    if (count($eligibleUsers) === 0) {
                        throw new Exception("No IT personnel or coordinators available for this campus.");
                    }
            
                    // Get the user with the least number of tickets
                    $assignedUser = $eligibleUsers[0]['id'];
            
                    // Update the ticket to assign it to the selected user and set the status to 'In Progress'
                    $updateQuery = "
                        UPDATE " . $this->table_name . "
                        SET assigned_to = :assigned_to, status = 'In Progress'
                        WHERE id = :ticket_id
                    ";
                    $stmt = $this->conn->prepare($updateQuery);
                    $stmt->bindParam(':assigned_to', $assignedUser, PDO::PARAM_INT);
                    $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
                    $stmt->execute();
            
                    // Commit the transaction
                    $this->conn->commit();
            
                    // Optionally, send notifications (if you have a notification system)
                    $notification = new Notification($this->conn);
                    $messageAssigned = "You have been assigned a new ticket with ID {$ticket_id}.";
                    $notification->create($assignedUser, $ticket_id, 'email', $messageAssigned);
            
                    return $assignedUser;
                } catch (Exception $e) {
                    // Rollback the transaction in case of error
                    $this->conn->rollBack();
                    throw $e;
                }
            }

            public function updateForwardingCampus($ticket_id, $forwarded_to_campus_id) {
                $query = "UPDATE " . $this->table_name . " 
                          SET forwarded_to_campus_id=:forwarded_to_campus_id
                          WHERE id=:ticket_id";
                $stmt = $this->conn->prepare($query);
                
                $stmt->bindParam(":forwarded_to_campus_id", $forwarded_to_campus_id);
                $stmt->bindParam(":ticket_id", $ticket_id);
                
                return $stmt->execute();
            }
            public function resetTicketAssignment($ticket_id) {
                // SQL query to update the ticket fields
                $query = "UPDATE " . $this->table_name . " 
                          SET assigned_to = NULL, 
                              resolved_at = NULL, 
                              assigned_at = NULL, 
                              status = 'In Progress'
                          WHERE id = :ticket_id";
            
                // Prepare the query
                $stmt = $this->conn->prepare($query);
            
                // Bind the ticket ID parameter
                $stmt->bindParam(":ticket_id", $ticket_id);
            
                // Execute the query
                if ($stmt->execute()) {
                    return true; // Success
                } else {
                    return false; // Failure
                }
            }
            
            
            
            public function getUnassignedForwardedTickets($campus_id = null) {
                // Define the columns to retrieve
                $columns = "id, subject,  category_id, priority, status, created_by, assigned_to, created_at,campus_id, forwarded_to_campus_id";
            
                if ($campus_id === null) {
                    // Get all forwarded tickets with assigned_to as null
                    $query = "SELECT " . $columns . " 
                              FROM " . $this->table_name . " 
                              WHERE forwarded_to_campus_id IS NOT NULL AND assigned_to IS NULL";
                } else {
                    // Get forwarded tickets for a specific campus ID with assigned_to as null
                    $query = "SELECT " . $columns . " 
                              FROM " . $this->table_name . " 
                              WHERE forwarded_to_campus_id = :campus_id AND assigned_to IS NULL";
                }
            
                $stmt = $this->conn->prepare($query);
            
                if ($campus_id !== null) {
                    $stmt->bindParam(":campus_id", $campus_id);
                }
            
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            public function getAssignedForwardedTickets($campus_id = null) {
                // Define the columns to retrieve
                $columns = "id, subject, category_id, priority, status, created_by, assigned_to, created_at, campus_id, forwarded_to_campus_id";
            
                if ($campus_id === 'other') {
                    // Get forwarded tickets where forwarded_to_campus_id is NOT Beirut (campus_id != 1)
                    $query = "SELECT " . $columns . " 
                              FROM " . $this->table_name . " 
                              WHERE forwarded_to_campus_id != 1 AND assigned_to IS NOT NULL";
                } elseif ($campus_id === null) {
                    // Get all forwarded tickets with assigned_to not null
                    $query = "SELECT " . $columns . " 
                              FROM " . $this->table_name . " 
                              WHERE forwarded_to_campus_id IS NOT NULL AND assigned_to IS NOT NULL";
                } else {
                    // Get forwarded tickets for a specific campus ID with assigned_to not null
                    $query = "SELECT " . $columns . " 
                              FROM " . $this->table_name . " 
                              WHERE forwarded_to_campus_id = :campus_id AND assigned_to IS NOT NULL";
                }
            
                $stmt = $this->conn->prepare($query);
            
                if ($campus_id !== null && $campus_id !== 'other') {
                    $stmt->bindParam(":campus_id", $campus_id);
                }
            
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            
            
            
            
            


            
       
            
            }
            
            
?>
