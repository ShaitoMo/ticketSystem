<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Ticket.php';
require_once dirname(__DIR__) . '/models/Category.php';
require_once dirname(__DIR__) . '/models/Attachment.php';
require_once dirname(__DIR__) . '/models/Comment.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/history.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/department.php';
require_once dirname(__DIR__) . '/models/ApprovalRequest.php';
require_once dirname(__DIR__) . '/models/campuses.php';



class UserController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function dashboard() {
        


        
        include 'dashboard.php';
    }

    
    public function createTicket($subject, $description, $category_id, $priority, $created_by, $campus_id, $attachments = []) {
      
        $ticket_type = $this->TypeByUserId($created_by);
        $ticket = new Ticket($this->db);
        $ticket_id = $ticket->create($subject, $description, $category_id, $priority, $created_by, $ticket_type,$campus_id, $attachments);
        
        
        $isHead = $this->isHead($created_by);
        $category = new Category($this->db);
       $approval=$category->requiresApproval($category_id);
       if ($ticket_id) {
        // Check if the ticket is approved or if the user is a department head
        if (!$approval || $isHead) {
            // Update the approval status of the ticket to approved (1)
            $this->updateApprovalStatus($ticket_id, 1);
    
            // Create a notification message about ticket creation
            $message = "Your ticket with ID {$ticket_id} has been created. You will be kept in touch for further updates.";
    
            // Create a notification with the message
            $notification = new Notification($this->db);
            $notification->create($created_by, $ticket_id, 'email', $message);
    
            // Check if the campus is Beirut
            $camp = new Campus($this->db);
            $isBeirut = $camp->isBeirutCampus($campus_id);
    
            // If the campus is not Beirut, assign the ticket based on the campus
            if (!$isBeirut) {
                $ticket = new Ticket($this->db);
                $ticket->assignTicketBasedOnCampus($ticket_id, $campus_id);
            }
    
        } else {
            // If approval is required but not yet given, notify the user about the pending approval
            $message = "Your ticket with ID {$ticket_id} has been created and is currently waiting for approval.";
    
            // Create a notification about the pending approval
            $notification = new Notification($this->db);
            $notification->create($created_by, $ticket_id, 'email', $message);
        }
    
        return true;
    } else {
        return false;
    }
    
    }
    

    public function handleFileUploads($files, $maxSize, $related_type) {
        $base_upload_dir = dirname(__DIR__) . '/uploads/';
        
        // Define subdirectories based on the related_type
        $upload_dirs = [
            'Comment' => $base_upload_dir . 'comments/',
            'Solution' => $base_upload_dir . 'solutions/',
            'Ticket' => $base_upload_dir . 'tickets/'
        ];
    
        // Check if the provided related_type is valid
        if (!isset($upload_dirs[$related_type])) {
            die("Invalid related_type provided.");
        }
    
        $upload_dir = $upload_dirs[$related_type];
    
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
    
        $uploaded_files = [];
        
        foreach ($files['name'] as $key => $name) {
            $tmp_name = $files['tmp_name'][$key];
            $file_size = $files['size'][$key];
            $file_name = basename($name);
            $target_file = $upload_dir . $file_name;
    
            if ($file_size > $maxSize) {
                die("File $file_name exceeds the maximum allowed size of " . ($maxSize / 1048576) . " MB.");
            }
    
            if (move_uploaded_file($tmp_name, $target_file)) {
                // Store file information in an array for further processing
                $uploaded_files[] = [
                    'file_path' => $file_name,
                    'related_type' => $related_type
                ];
            } else {
                // Handle upload error
                echo "Error uploading file: " . $name;
            }
        }
    
        return $uploaded_files;
    }
    
    
    
    public function getITPersonnel($id) {
        $ticket = new Ticket($this->db);
        return $ticket->getITPersonnel($id);
    }
    
    
    public function getUserById($user_id) {
        $user=new User($this->db);
        return $user->getUserById($user_id);}
        public function getDepartmentIdByUserId($user_id) {
            $user=new User($this->db);
            return $user->getDepartmentIdByUserId($user_id);}
    

        
    public function getUserTickets($user_id) {
        $ticket = new Ticket($this->db);
        return $ticket->getUserTickets($user_id);
    }
    public function getUserNameById($user_id) {
        $user=new User($this->db);
        return $user->getUsernameById($user_id);}

    public function getCategories() {
        $category = new Category($this->db);
        return $category->read();
    }
    public function getTicketById($ticket_id){
        $ticket = new Ticket($this->db);
        return $ticket-> getTicketById($ticket_id);
    }

    public function getTicketsByStatus($status, $user_id) {
        $ticket = new Ticket($this->db);
        return $ticket-> getTicketsByStatusAndUser($status, $user_id) ;
    }
    public function getActiveTickets($user_id){
        $ticket = new Ticket($this->db);
        return $ticket-> getActiveTickets($user_id);
    }

    public function getCommentsByTicketId($ticket_id,$privacy){
        $c = new Comment($this->db);
        return $c-> getCommentsByTicketId($ticket_id,$privacy);
    }
    public function getAttachmentsByTicketId($ticket_id){
        $attachments = new Attachment($this->db);
        return $attachments->readByTicketId($ticket_id);
    }
    public function addComment($ticket_id, $user_id, $comment,$privacy,$attachments) {
        $commentModel = new Comment($this->db);
        $comment_id = $commentModel->create($ticket_id, $user_id, $comment, $privacy);
    
        if ($comment_id) {
            // If comment creation is successful and there are attachments
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    // Call the method to add each attachment
                    $this->addAttachments($ticket_id, $comment_id, $attachment['file_path'], 'Comment');
                }
            }
            $ticket = new Ticket($this->db);
            $ticketDetails = $ticket->getTicketById($ticket_id);
            $createdBy = $ticketDetails['created_by'];
            $assignedTo = $ticketDetails['assigned_to'];
            $userRole = $this->getUserRole($user_id);
    
            $notification = new Notification($this->db);
            $message = "A new comment has been added to the ticket with ID {$ticket_id}.";
    
            if ($user_id === $createdBy) {
              if($assignedTo){
                $notification->create($assignedTo, $ticket_id, 'email', $message);}
            } elseif ($user_id === $assignedTo) {
            
                $notification->create($createdBy, $ticket_id, 'email', $message);
            } elseif ($userRole === 'IT Administrator') {
                
                $notification->create($createdBy, $ticket_id, 'email', $message);
                $notification->create($assignedTo, $ticket_id, 'email', $message);
            }
    
            return true;
        } else {
            return false;
        }
    }
    public function GetCommentAttachments($id){
        $attachments = new Attachment($this->db);
        return $attachments->readByCommentId($id);

    }
    

    public function updateStatus($ticket_id, $status) {
        
        $ticket = new Ticket($this->db);
        return $ticket->updateStatus($ticket_id, $status);}
    
        public function getUserDetailsById($user_id){
        $user=new User($this->db);
        return $user->getUserById($user_id);


    }
    public function addHistory($ticketId, $newStatus, $changedBy) {
        $ticket = new Ticket($this->db);
        $currentStatus = $ticket->getStatusTicketById($ticketId);
        
       
        if ($newStatus !== $currentStatus) {
            $history = new TicketHistory($this->db);
            return $history->addHistory($ticketId, $newStatus, $changedBy);
        } else {
        
            return false;
        }
    }
    
    
    public function getHistory($ticketId) {
        $history= new TicketHistory( $this->db);
        return $history->getHistoryByTicketId($ticketId);
    }
    public function getUserRole($user_id){
        $user=new User($this->db);
        return $user->getUserRole($user_id);


    }
    public function countNotifications($user_id, $status) {
        $notification=new Notification($this->db);
        return $notification->countNotifications($user_id, $status);}
    
    public function getNotifications($user_id,$status){
        $notification=new Notification($this->db);
        return $notification->read($user_id,$status);
    }
    public function  MarkAsRead($user_id){
        $notification= new Notification($this->db);
        return $notification->markAsRead($user_id);

    }
    public function isHead($user_id) {

        $department = new Department($this->db);
        return $department->isHeadOfDepartment($user_id);
    }
    public function getTicketsRequiringApproval($department_id){
        $ticketModel = new Ticket($this->db);
        

        return $ticketModel->getTicketsRequiringApproval($department_id);

    }
    public function updateApprovalStatus($ticket_id, $approval_status) {
        $ticketModel = new Ticket($this->db);
        
        // Update the approval status of the ticket
        $approved = $ticketModel->updateApprovalStatus($ticket_id, $approval_status);
        
        if ($approved && $approval_status == 1) {
            // Get the campus ID for the ticket
            $camp = new Campus($this->db);
            $campus_id = $ticketModel->getCampusTicketById($ticket_id);
            
            // Check if the campus is Beirut
            $isBeirut = $camp->isBeirutCampus($campus_id);
            
            // If the campus is not Beirut, assign the ticket based on the campus
            if (!$isBeirut) {
                $ticketModel->assignTicketBasedOnCampus($ticket_id, $campus_id);
            }
        }
    }
    

    public function   TypeByUserId($user_id){
        $user=new User($this->db);
        $depId= $user-> getDepartmentIdByUserId($user_id);
        $department = new Department($this->db);
        return $department->getDepartmentTypeById($depId);
       

    }
    public function HeadOfUser($userId){
        $user=new User($this->db);
        $depId= $user-> getDepartmentIdByUserId($userId);
        $department = new Department($this->db);
        return $department->getDepartmentHeadId($depId);

    }// Retrieve approval requests by requested_to user ID
public function getUserRequests($requested_to, $status ) {
    $request=new ApprovalRequest( $this->db  );
    return  $request->getRequestsByRequestedTo($requested_to, $status);
   
}
public function updateRequestStatus($id, $status){
    $request=new ApprovalRequest( $this->db  );
    return  $request->updateStatus($id, $status);

}
public function hasRequestAccess($ticket_id, $requested_to){

    $request=new ApprovalRequest( $this->db  );
    return  $request->userHasApprovalRequestForTicket($ticket_id, $requested_to);

}
public function addAttachments($ticket_id, $comment_id, $file_path, $related_type) {
    $attachments = new Attachment($this->db);
    return $attachments->addAttachments($ticket_id, $comment_id, $file_path, $related_type);
}
public function getSolutionAttachments($id){
    $attachments = new Attachment($this->db);
    return $attachments->SolutionByTicketId($id);

}
public function deleteSoultionAttachment($id){
    $attachments = new Attachment($this->db);
    return $attachments->deleteById($id);

}

public function getCampusbyUser($id){
    $user=new User($this->db);
return $user->getCampusByUserId($id);
}
public function assignTicketBasedOnCampus($ticket_id, $campus_id) {
    $ticket = new Ticket($this->db);
    return $ticket->assignTicketBasedOnCampus($ticket_id, $campus_id) ;

}

}


    
    
?>