<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Ticket.php';
require_once dirname(__DIR__) . '/models/Category.php';
require_once dirname(__DIR__) . '/models/Attachment.php';
require_once dirname(__DIR__) . '/models/Comment.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/itrole.php';
require_once dirname(__DIR__) . '/models/Setting.php';
require_once dirname(__DIR__) . '/models/userRoles.php';
require_once dirname(__DIR__) . '/models/TeamLeader.php';
require_once dirname(__DIR__) . '/models/campuses.php';
require_once dirname(__DIR__) . '/models/ticket_forwarding.php';
require_once dirname(__DIR__) . '/models/department.php';
require_once 'controllers/ITController.php';
require_once 'controllers/UserController.php';


class AdminController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();

        
    }



    public function assignTicket($ticket_id, $assigned_to) {
        $ticket = new Ticket($this->db);
        if ($ticket->assignTicket($ticket_id, $assigned_to)) {
            header("Location: dashboard.php");
        } else {
            echo "Error assigning ticket.";
        }
    }

    public function createCategory($name, $parent_id = null) {
        $category = new Category($this->db);
        if ($category->create($name, $parent_id)) {
            header("Location: categories.php");
        } else {
            echo "Error creating category.";
        }
    }
    public function getMainCategories(){
        $category = new Category($this->db);
        return $category->getMainCategories();

    }
   
    public function updateTicketDetails($ticket_id, $status, $priority, $assigned_to) {
        try {
            $this->db->beginTransaction();
            
            $ticket = new Ticket($this->db);
            $ticketDetails = $ticket->getTicketById($ticket_id);
            if (!$ticketDetails) {
                throw new Exception("Ticket not found.");
            }
    
            $createdBy = $ticketDetails['created_by'];
            $currentAssignedTo = $ticketDetails['assigned_to'];
            $currentStatus = $ticketDetails['status'];
            $currentPriority = $ticketDetails['priority'];
    

    
            $statusChanged = $status !== $currentStatus;
            $priorityChanged = $priority !== $currentPriority;
            $assignedChanged = (string)$assigned_to !== (string)$currentAssignedTo; // Ensure type consistency
    
            // Initialize notification
            $notification = new Notification($this->db);
    
            // Prepare messages for notifications
            $messages = [];
            if ($statusChanged || $priorityChanged) {
                $message = "Ticket with ID {$ticket_id} has been updated by adminstartion .";
                if ($statusChanged) {
                    $message .= " Status: '{$status}'.";
                }
                if ($priorityChanged) {
                    $message .= " Priority: '{$priority}'.";
                }
                $messages[] = $message;
            }
    
            // Update ticket details
            if ($ticket->updateTicket($ticket_id, $status, $priority, $assigned_to)) {
                // Notifications for assignment changes
                if ($assignedChanged) {
                    if ($assigned_to) {
                        $messageAssigned = "You have been assigned a new ticket with ID {$ticket_id} by adminstartion.";
                        $notification->create($assigned_to, $ticket_id, 'email', $messageAssigned);
                    }
    
                    if ($currentAssignedTo) {
                        $messageUnassigned = "The ticket with ID {$ticket_id} has been reassigned by adminstartion.";
                        $notification->create($currentAssignedTo, $ticket_id, 'email', $messageUnassigned);
                    }
                }
    
                
                foreach ($messages as $message) {
                    $notification->create($createdBy, $ticket_id, 'email', $message);
                    if ($assigned_to && !$assignedChanged) {
                        $notification->create($assigned_to, $ticket_id, 'email', $message);
                    }
                }
    
                $this->db->commit();
                return true;
            } else {
                throw new Exception("Error updating ticket details.");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    
    
    
    
    

    public function getitRoles() {
        $role = new Role($this->db);
        return $role->read();
    }
    public function updateitRole($user_id, $role_id) {
        $role = new UserRole($this->db);
        return $role->updateRoles($user_id, $role_id);
    }
    public function updateCategory($id, $name, $parentId, $itroleId,$requires_approval){
        $category = new Category($this->db);    
        return $category->updateCategory($id, $name, $parentId, $itroleId,$requires_approval);
    }
    public function AddCategory( $name, $parentId, $itroleId){
        $category = new Category($this->db);    
        return $category->AddCategory( $name, $parentId, $itroleId);
    }
    public function getRoleNameById($id) {
        $role = new Role($this->db);
        return $role->getRoleById($id);
    }
    public function getUserRoles($user){
        $UserRoles= new UserRole($this->db);
        return $UserRoles->readByUserId($user);

    }
    public function countUsersByRole($role) {
        $UserRoles= new UserRole($this->db);
        return $UserRoles->countByRoleId($role);
    }
    public function countTicketsByItRole($role_id) {
        $UserRoles= new UserRole($this->db);
        return $UserRoles->countTicketsByRoleId($role_id);
    }
    public function addRole($roleName){
        $role = new Role($this->db);
        return $role->create($roleName);
    }
    public function getSettings(){
        $settings = new Setting($this->db);
        return $settings->readAll();}
    
     public function update( $name, $value) {
         $settings = new Setting($this->db);
         return $settings->update($name, $value);}

    public function AutoAssignTickets() {
            $ticket = new Ticket($this->db);
            $unassignedTickets = $ticket->getAllTickets('','','unassigned','','','',1,1);
    
            if (count($unassignedTickets) > 0) {
                foreach ($unassignedTickets as $ticketData) {
                $ticket->AutoAssign($ticketData['id']);
            }
    }}
    public function autoCloseResolvedTickets($autoClose) {
        $it = new ITController();
        $user = new UserController();
        $ticket = new Ticket($this->db);
        
        
        $toClose = $ticket->TicketsForAutoClose($autoClose);
    
       
        foreach ($toClose as $ticketData) {
            $user->addHistory($ticketData['id'], 'Closed','12');
           
            $it->updateTicketStatus($ticketData['id'], 'Closed', '12');
            
          
            
        }
    
      
        return count($toClose);
    }

    public function getTeamMembers($id){
        $category = new Category($this->db);
        return $category-> getTeamMembers($id) ;

    }
    public function getTeamMembersByTicketCategory($id){
        $category = new Category($this->db);
        $team=$category->getParentIdByCategoryId($id);
        return $category-> getTeamMembers( $team) ;

    }

    public function getTeamLeader($id){
        $team=new TeamLeader($this->db);
        return  $team->readByCategoryId($id);
    }

    public function updateTeamLeader($team_id, $teamLeaderId){
        $team=new TeamLeader($this->db);
        return  $team->InsertTeamLeader($team_id, $teamLeaderId);

    }
    public function AverageResolutionTime($days){
        $ticket = new Ticket($this->db);
       return $ticket->calculateAverageResolutionTime($days);

       
    }

    public function AverageAssignmentTime($days){
        $ticket = new Ticket($this->db);
        return $ticket->calculateAverageAssignmentTime($days);
    }
    public function getLatestHistory($limit) {
        $history= new TicketHistory( $this->db);
        return $history->getLatestHistory($limit);}
     public function getCampuses(){
        $campus=new Campus($this->db);
        return $campus->getAll();

     }
     public function forwardTicket($ticket_id, $from_campus_id, $to_campus_id, $byId) {
        // Initialize the Ticket and TicketForwarding models
        $ticket = new Ticket($this->db);
        $forward = new TicketForwarding($this->db);
    
        // Reset the ticket assignment
        if (!$ticket->resetTicketAssignment($ticket_id)) {
            return false;
        }
    
        // Update the forwarding campus of the ticket
        if (!$ticket->updateForwardingCampus($ticket_id, $to_campus_id)) {
            return false;
        }
    
        // Log the forwarding action
        if (!$forward->forwardTicket($ticket_id, $from_campus_id, $to_campus_id, $byId)) {
            return false;
        }
    
        // If not forwarding to Beirut, assign the ticket to a campus
        if ($to_campus_id != 1) { // Assuming 1 is the ID for Beirut
         
            if (!$ticket->assignTicketBasedOnCampus($ticket_id, $to_campus_id )) {
                return false;
            }
        }
    
        return true;
    }
    public function getAssignedForwardedTickets($campus_id ) {
        $ticket = new Ticket($this->db);
        return $ticket->getAssignedForwardedTickets($campus_id );}
        
        
    public function getUnAssignedForwardedTickets($campus_id ) {
        $ticket = new Ticket($this->db);
         return $ticket->getUnAssignedForwardedTickets($campus_id );}

         public function getRolesOfUsers(){
            $user=new User($this->db);
            return $user->getRoleValues() ;
    
    
        }
        public function AddUser($first_name, $last_name, $password, $email, $role, $department_id, $phone_number, $campus_id){
            $user=new User($this->db);
            return $user->create($first_name, $last_name, $password, $email, $role, $department_id, $phone_number, $campus_id) ;
        }
        public function getAllDepartments() {
        $dep=new Department($this->db);
        return $dep->getAllDepartments();  }

        public function getAllCampuses(){
            $campus=new Campus($this->db);
            return $campus->getAll();

        }
        public function addBulkUsers($users) {
            try {
                $user = new User($this->db);
                foreach ($users as $userData) {
                    $first_name = $userData['first_name'];
                    $last_name = $userData['last_name'];
                    $email = $userData['email'];
                    $password =$userData['password'];
                    $role = $userData['role'];
                    $department_id = $userData['department_id'];
                    $phone_number = $userData['phone_number'];
                    $campus_id = $userData['campus_id'];
        
                    // Add user
                    if (!$user->create($first_name, $last_name, $password, $email, $role, $department_id, $phone_number, $campus_id)) {
                        // If user creation fails, return false
                        return false;
                    }
                }
                // All users added successfully
                return true;
            } catch (Exception $e) {
                // Handle exceptions and return false
                return false;
            }
        }

        public function getUserNamesByRoleId($role_id){
            $role = new UserRole($this->db);
            return $role->getUserNamesByRoleId($role_id); }


        public function getCategoryNamesByRoleId($role_id) {
                $category = new Category($this->db);
                return $category->getCategoryNamesByRoleId($role_id);}

        
        

 
}
?>
