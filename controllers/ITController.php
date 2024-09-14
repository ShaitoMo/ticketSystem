<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Ticket.php';
require_once dirname(__DIR__) . '/models/Category.php';
require_once dirname(__DIR__) . '/models/Attachment.php';
require_once dirname(__DIR__) . '/models/Comment.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Notification.php';
require_once dirname(__DIR__) . '/models/TeamLeader.php';
require_once dirname(__DIR__) . '/models/ApprovalRequest.php';
require_once dirname(__DIR__) . '/models/SubTasks.php';
require_once dirname(__DIR__) . '/models/campuses.php';
require_once dirname(__DIR__) . '/models/ticket_forwarding.php';
require_once 'controllers/UserController.php';

class ITController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function dashboard() {
        
    
    

        include 'ItDashboard.php';
    }

    public function getCategories() {
        $category = new Category($this->db);
        return $category->read();
    }
    public function getSubCategories() {
        $category = new Category($this->db);
        return $category->getSubCategories();
    }
    public function getMainCategories() {
        $category = new Category($this->db);
        return $category->getMainCategories();
    }
    public function changeTeam($ticket_id,$category_id){
        $ticket = new Ticket($this->db);
        return $ticket->updateCategory($ticket_id, $category_id);
    }

    public function updateTicketStatus($ticket_id, $status, $user_id) {
     
        $ticket = new Ticket($this->db);
        $UserController = new UserController($this->db);
        $UserController->addHistory($ticket_id, $status, $user_id);
        
        
        if ($ticket->updateStatus($ticket_id, $status)) {
            
            $notification = new Notification($this->db);
            $user = new User($this->db);
            $ticketDetails = $ticket->getTicketById($ticket_id);
            $createdBy = $ticketDetails['created_by'];
    
            $userName = $user->getUserNameById($user_id);
            $message = "The status of your ticket with ID {$ticket_id} has been updated to '{$status}' by '{$userName}'.";
    
            $notification->create($createdBy, $ticket_id, 'email', $message);
            header("Location: " . $_SERVER['REQUEST_URI']);
    
           
           
            exit();
        } else {
            echo "Error updating ticket status.";
        }
    }
    
    

    public function assignTicketToUser($ticket_id, $assigned_to) {
        $ticket = new Ticket($this->db);
        
    
        if ($ticket->assignTicket($ticket_id, $assigned_to)) {
            
            
            $notification = new Notification($this->db);
            $user = new User($this->db);
            $ticketDetails = $ticket->getTicketById($ticket_id);
            $createdBy = $ticketDetails['created_by'];
            
            $assignedUserName = $user->getUserNameById($assigned_to);
    
            // Create notifications
            $messageAssigned = "You have been assigned a ticket with ID {$ticket_id}. Please take Action '.";
            $messageCreated = "The ticket with ID {$ticket_id} has been assigned to '{$assignedUserName}'.";
    
            $notification->create($assigned_to, $ticket_id, 'email', $messageAssigned);
            $notification->create($createdBy, $ticket_id, 'email', $messageCreated);
            return  true;
      
        } else {
            echo "Error assigning ticket.";
            return  false;
        }
    }
    


    public function getAllTickets($status = '', $priority = '', $assigned_to_filter = '', $mainCategory = '', $subCategory = '', $created_by = '',$aproved,$campus_id) {
    
        $ticket = new Ticket($this->db);
            return $ticket->getAllTickets($status  , $priority , $assigned_to_filter , $mainCategory , $subCategory , $created_by,$aproved,$campus_id );
        }
    
    public function getAssignedTickets($user_id ){
        $ticket = new Ticket($this->db);
        return $ticket->getAssignedTickets($user_id);
    }
    public function getTeamTickets($mainCategory, $status = '', $priority = '', $assigned_to_filter = '', $subCategory = '', $created_by = '') {
        $ticket = new Ticket($this->db);
        return $ticket->getTeamTickets($mainCategory, $status, $priority, $assigned_to_filter, $subCategory, $created_by);
    }
    

    public function getItProgressedTickets($assigned_to, $status = '', $priority = '', $assigned_to_filter = '', $mainCategory = '', $subCategory = '') {

        $ticket = new Ticket($this->db);
        return $ticket->getItProgressedTickets($assigned_to, $status, $priority, $assigned_to_filter,  $mainCategory , $subCategory );
    }

    public function getCategoryNameById($category_id) {
        $category= new Category($this->db); 
        return $category->getCategoryNameById($category_id);
    }
    public function getCategoryIdByName($name) {
        $category= new Category($this->db); 
        return $category->getCategoryIdByName($name);
    }



    public function countAllTickets() {
        $ticket = new Ticket($this->db);
        return $ticket->countAllTickets();

    }
    public function getRecommendedTickets($user_id) {
        $ticket = new Ticket($this->db);
            return $ticket->getRecommendedTickets($user_id);

    }
    public function countTeamTickets($mainCategory, $status = '', $priority = '', $assigned_to_filter = 'assigned', $subCategory = '', $created_by = '') {
        $ticket = new Ticket($this->db);
        return $ticket->countTeamTickets($mainCategory, $status, $priority, $assigned_to_filter, $subCategory, $created_by);
    }


   

    public function countITActiveTickets($user_id) {
            $ticket = new Ticket($this->db);
            return $ticket->countITProgressTickets($user_id);

    }



    public function countTickets($status = '', $priority = '', $assigned_to = '', $category = '') {
        $ticket = new Ticket($this->db);
        return $ticket->countTickets($status, $priority, $assigned_to, $category);
    }
    public function getSubCategoriesByMainCategory($category_id) {
        $category= new Category($this->db); 
            return $category->getSubCategoriesByMainCategory($category_id);
    }

    public function updateSolution($ticket_id, $solution, $updatedBy, $attachments) {
        $ticket = new Ticket($this->db);
        $userController = new UserController($this->db);
        $notification = new Notification($this->db);
    
        $ticketDetails = $ticket->getTicketById($ticket_id);
        $currentStatus = $ticketDetails['status'];
        $createdBy = $ticketDetails['created_by'];
        $assignedTo = $ticketDetails['assigned_to'];
        $currentSolution = $ticketDetails['solution'];
    
        $isUpdated = $ticket->updateSolution($ticket_id, $solution);
        $id = null; // Initialize $id with a default value
    
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                // Call the method to add each attachment and store the last attachment ID
                $id = $userController->addAttachments($ticket_id, null, $attachment['file_path'], 'Solution');
            }
        }
    
        if ($isUpdated) {
            $updatedByName = $userController->getUserNameById($updatedBy);
            if (empty($currentSolution) || $currentStatus !== 'Closed') {
                $userController->addHistory($ticket_id, 'Resolved', $updatedBy);
                $userController->updateStatus($ticket_id, 'Resolved');
    
                $message = "The solution for ticket ID {$ticket_id} has been updated and the status set to 'Resolved' by {$updatedByName}.";
            } else {
                $message = "The solution for ticket ID {$ticket_id} has been updated.";
            }
    
            if ($assignedTo === $updatedBy) {
                $notification->create($createdBy, $ticket_id, 'email', $message);
            } else {
                if ($assignedTo) {
                    $notification->create($assignedTo, $ticket_id, 'email', $message);
                }
                $notification->create($createdBy, $ticket_id, 'email', $message);
            }
    
            return $id !== null ? $id : true; // Return $id if it was set, otherwise return true
        }
    
        return false;
    }
    

    public function isUserLeader($user_id){
        $team=new TeamLeader($this->db);
        return  $team->isUserLeader($user_id);

    }
    public function getUserTeam($user_id) {
        $team=new TeamLeader($this->db);
        return  $team->getUserTeam($user_id);

    }
    public function isTicketForTeam($ticketId, $mainCategoryId){
        $ticket = new Ticket($this->db);
        return  $ticket->isTicketInMainCategory($ticketId, $mainCategoryId);

    }

    public function getTeamsLedByUser($user_id){
        $team=new TeamLeader($this->db);
        return  $team->getTeamsLedByUser($user_id);

    
    }
    public function isTeamLeaderByCategory($category_id,$user_id){
        $TicketMainCategory=$this->getParentIdByCategoryId($category_id);
        $team=new TeamLeader($this->db);
        return  $team->isUserLeaderForCategory($TicketMainCategory, $user_id);
    }
    public function getTeamRoles($id){
        $category= new Category($this->db);
        return $category->getRolesByParentId($id);

    }
    public function getParentIdByCategoryId($category_id) {
        $category= new Category($this->db);
        return $category->getParentIdByCategoryId($category_id);}

    public function getRecentTickets($userId){
        $ticket = new Ticket($this->db);
            return $ticket->getRecentTickets($userId);

    }
    public function AvgResolutionByUser($userId) {
        // Instantiate the Ticket model
        $ticketModel = new Ticket($this->db);
        
        // Get all tickets assigned to the specified user
        $tickets = $ticketModel->getItTickets($userId);
        
        // Initialize variables to calculate the average
        $totalResolutionTime = 0;
        $ticketCount = 0;
        
        // Iterate over each ticket and accumulate the resolution time
        foreach ($tickets as $ticket) {
            // Ensure the ticket has valid resolution and assigned times
            if ($ticket['assigned_at'] !== null && $ticket['resolved_at'] !== null) {
                // Calculate the resolution time for each ticket
                $assignedAt = new DateTime($ticket['assigned_at']);
                $resolvedAt = new DateTime($ticket['resolved_at']);
                $resolutionTime = $resolvedAt->getTimestamp() - $assignedAt->getTimestamp();
                
                // Add the resolution time to the total
                $totalResolutionTime += $resolutionTime;
                $ticketCount++;
            } 
        }
        
        // Calculate the average resolution time
        $averageResolutionTime = $ticketCount > 0 ? $totalResolutionTime / $ticketCount : 0;
        
        // Return the average resolution time in seconds
        return $averageResolutionTime;
    }
    public function AvgResolutionByTeam($teamId) {
        // Instantiate the Ticket model
        $ticketModel = new Ticket($this->db);
        
        // Get all tickets assigned to the specified team
        $tickets = $ticketModel->getTeamTickets($teamId, 'all');
        
        // Initialize variables to calculate the average
        $totalResolutionTime = 0;
        $ticketCount = 0;
        
        // Iterate over each ticket and accumulate the resolution time
        foreach ($tickets as $ticket) {
            // Ensure the ticket has valid resolution and assigned times
            if ($ticket['assigned_at'] !== null && $ticket['resolved_at'] !== null) {
                // Calculate the resolution time for each ticket
                $assignedAt = new DateTime($ticket['assigned_at']);
                $resolvedAt = new DateTime($ticket['resolved_at']);
                $resolutionTime = $resolvedAt->getTimestamp() - $assignedAt->getTimestamp();
                
                // Add the resolution time to the total
                $totalResolutionTime += $resolutionTime;
                $ticketCount++;
            }
        }
        
        // Calculate the average resolution time
        $averageResolutionTime = $ticketCount > 0 ? $totalResolutionTime / $ticketCount : 0;
        
        // Return the average resolution time in seconds
        return $averageResolutionTime;
    }
    
    public function CreateAproval($ticket_id, $requested_by, $requested_to, $request_description) {
        $request=new ApprovalRequest( $this->db  );
        return  $request->create($ticket_id, $requested_by, $requested_to, $request_description);
    }

    public function SubTaskAssign($ticket_id, $assigned_by, $assigned_to, $sub_task_description){
        $subTask=new SubTask($this->db  );
        return  $subTask->create($ticket_id, $assigned_by, $assigned_to, $sub_task_description);
    }
    public function getRequestsByTicketId($ticket_id){
        $request=new ApprovalRequest( $this->db  );
        return  $request->getRequestsByTicketId($ticket_id);
        
    }
    public function getSubTasksByTicketId($ticket_id) {
        $subTask=new SubTask($this->db  );
        return  $subTask->getSubTasksByTicketId($ticket_id);
    }
    public function getSubTasksByAssignedTo($assigned_to) {
        $subTask=new SubTask($this->db  );
        return  $subTask->getSubTasksByAssignedTo($assigned_to);
    }
    public function updateSubTaskStatus($sub_task_id, $status){
        $subTask=new SubTask($this->db  );
        return  $subTask->updateStatus($sub_task_id, $status);


        
    }

    public function hasSubTaskAccess($ticket_id,$assigned_to){
        $subTask=new SubTask($this->db  );
        return  $subTask->userHasSubTaskForTicket($ticket_id, $assigned_to);
    }

    public function getTeamMembersByTicketCat($category_id){
        $category = new Category($this->db);
       $team= $category->getParentIdByCategoryId($category_id);
        return $category->getTeamMembers($team);

    }
    function isResolutionBlocked($subTasks, $approvalRequests) {
        foreach ($approvalRequests as $request) {
            if ($request['status'] === 'Pending') {
                return true;
            }
        }
    
        foreach ($subTasks as $subTask) {
            if ($subTask['status'] === 'In Progress') {
                return true;
            }
        }
    
        return false;
    }
    
    public function getCampusNameById($id) {
        $camp=new Campus($this->db);
        return $camp->getCampusNameById($id);}

    public function ForwardedTicketsByUser($id){
        $forward= new TicketForwarding($this->db);
        return $forward->getDistinctForwardedTicketsByUser($id);

    }
    public function getForwardingHistory($ticket_id) {
        $forward= new TicketForwarding($this->db);
        return $forward->getForwardingHistory($ticket_id);
    }

    
    
}

?>
