<?php
require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userController = new UserController();
$ITController = new ITController();
$AdminController=new AdminController();


$settings=$AdminController->getSettings();
$MaxSize=$settings['max_attachment_size'];
$defaultTicketPriority=$settings['default_ticket_priority'];

$ticket_id = $_GET['ticket_id'] ?? null;
$user_id = $_SESSION['user_id'];

$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$assigned_to_filter = $_GET['assigned_to'] ?? '';
$category_filter = $_GET['category'] ?? '';
$sub_category_filter = $_GET['sub_category'] ?? '';
$selected_main_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_sub_category = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';


$tickets = $ITController->getAllTickets($status_filter, $priority_filter, $assigned_to_filter, $category_filter, $sub_category_filter, $user_id,1,'all');
$pending=$ITController->getAllTickets('', '', '', '', '', $user_id,null,'all');
$campus=$userController->getCampusbyUser($user_id);
$campus_id=$campus['campus_id'];
$all_it_personnel = $userController->getITPersonnel($campus_id);
$categories = $ITController->getMainCategories();
$ticket_history = $userController->getHistory($ticket_id);
$attachments = [];
$comments = [];
$isClosed = false;


$aproval=[];
$requests=[];

$ishead = $userController->isHead($user_id);

if ($ishead) {
    $depId = $userController->getDepartmentIdByUserId($user_id);
    $aproval = $userController->getTicketsRequiringApproval($depId);
    $requests=$userController->getUserRequests($user_id,'pending');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'getSubCategories') {
        $mainCategoryId = $_POST['category_id'];
        $subCategories = $ITController->getSubCategoriesByMainCategory($mainCategoryId);
        echo json_encode($subCategories);
        exit();
    }
    if (isset($_POST['submit'])) {
        $subject = $_POST['subject'];
        $description = $_POST['description'];
        $category_id = $_POST['sub_category'];
        $priority = $_POST['priority'];
        $created_by = $_SESSION['user_id'];
        $campus=$userController->getCampusbyUser($created_by);
        $campus_id=$campus['campus_id'];
        $attachments = [];

        $userController = new UserController();

        if (!empty($_FILES['attachments']['name'][0])) { // Check if at least one file is uploaded
            $attachments = $userController->handleFileUploads($_FILES['attachments'], $MaxSize, 'Ticket');
        } else {
            echo 'No files uploaded.';
        }
 

        $result = $userController->createTicket($subject, $description, $category_id, $priority, $created_by, $campus_id, $attachments);

        if ($result) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $result;
        }

    }
    if (isset($_POST['ticket_id'], $_POST['response'])) {
        $ticket_id = intval($_POST['ticket_id']);
        $action = $_POST['response'];
        $response = [];

        if ($action == 'solved') {
            // Update ticket status to "Closed"
            $userController->updateStatus($ticket_id, 'Closed');
            $userController->addHistory($ticket_id, 'Closed', $user_id);
            
            $response = [
                'status' => 'success',
                'message' => 'Ticket marked as Solved!'
            ];
        } elseif ($action == 'unsolved') {
            // Update ticket status to "In Progress"
            $userController->updateStatus($ticket_id, 'In Progress');
            $userController->addHistory($ticket_id, 'In Progress', $user_id);
            
            $response = [
                'status' => 'success',
                'message' => 'Ticket marked as Unsolved!'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Invalid action.'
            ];
        }

        // Send back the JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    
    }
    if (isset($_POST['action'])) {
        $ticket_id = $_POST['id'];
        $action = $_POST['action'];

        if ($action === 'approve') {
     
            $userController->updateApprovalStatus($ticket_id, 1);
        } elseif ($action === 'decline') {
      
            $userController->updateApprovalStatus($ticket_id, 0);
        }

        // Redirect or display a message after processing
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
     if (isset($_POST['confirm_ticket_id'])) {
        $ticket_id = $_POST['confirm_ticket_id'];
        $userController->updateStatus($ticket_id, 'Closed');
        $userController->addHistory($ticket_id, 'Closed', $user_id);
        header("Location: dashboard.php?ticket_id=$ticket_id");
        exit();
    }

    

    if (isset($_POST['approve']) || isset($_POST['decline'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $action = isset($_POST['approve']) ? 'approve' : 'decline';

        if ($id > 0) {
            // Call the function to approve or decline the request
            if ($action === 'approve') {
                $result = $userController->updateRequestStatus($id,'Approved'); // Implement this method in ITController
            } else {
                $result = $userController->updateRequestStatus($id,'Rejected'); // Implement this method in ITController
            }

            // Redirect to the same page to refresh the data
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

$ticket_details = null;

if ($ticket_id) {
    $ticket_details = $userController->getTicketById($ticket_id);

}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <style>
                
        .table-responsive {
            overflow-x: auto; /* Allows scrolling if necessary */
            width: 100%; /* Ensures container uses full width of parent */
        }

        /* Ensure the table fits within the container */
        .table {
            max-width: 95%; /* Ensures table uses full width of container */
           
            border-collapse: collapse; /* Ensures borders are not doubled up */
        }

        
        .table th, .table td {
            overflow: hidden; 
            text-overflow: ellipsis; /* Adds ellipsis to overflowing text */
            white-space: nowrap; /* Prevents text from wrapping */
        }

    .dashboard-wrapper {
        font-family: Arial, sans-serif;
       
    }

    .dashboard-wrapper .card-title {
        margin-bottom: 20px;
    }

    .dashboard-wrapper .table-responsive {
        margin-top: 20px;
    }

    .dashboard-wrapper .status {
        text-transform: capitalize;
    }

    .dashboard-wrapper .priority {
        text-transform: capitalize;
    }

    .dashboard-wrapper .table-success {
        background-color: #d4edda;
    }

    .dashboard-wrapper .badge {
        padding: 0.5em 0.75em;
        font-size: 0.875em;
    }

    .btn-group .btn:last-child {

            margin-left: -5px;
        }
        .request-list-wrapper {
        max-height: 290px; /* Adjust the height as needed */
        overflow-y: auto; /* Adds a scrollbar when content exceeds max height */
        padding-right: 15px; /* Prevent content from being hidden under the scrollbar */
    }

    .request-list .card {
        margin-bottom: 10px; /* Space between divs */
    }
    .full-width-separator {
    width: 100vw; /* 100% of the viewport width */
    margin-left: calc(-50vw + 50%); /* Centers the separator */
        }
        .bg-light-green {
    background-color: #d4edda !important; /* Ensure the background color is applied */
}





 


</style>
<head>        
<body>
    <?php include_once 'header.php'; ?>
    <div id="response-alert" class="alert d-none alert-dismissible fade show" role="alert">
    <span id="alert-message"></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

        <div class="container mt-4">
        <h2>User Dashboard</h2>
        <!-- Inside a container with full-width separator -->
<div class="container">
    <div class="full-width-separator">
        <hr class="my-4 border-primary">
    </div>
</div>

        <div class="container mt-4">
    <h3 class="mb-4">My Tickets</h3>

    <!-- Filter Tickets Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Tickets</h5>
            <a href="#addTicketModal" class="btn btn-success btn-sm" data-toggle="modal">
                <i class="fas fa-plus"></i> New Issue
            </a>
        </div>
        <div class="card-body">
            <form id="filter-form" method="get" action="">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="New" <?php echo ($status_filter == 'New') ? 'selected' : ''; ?>>New</option>
                            <option value="In Progress" <?php echo ($status_filter == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Resolved" <?php echo ($status_filter == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                            <option value="Closed" <?php echo ($status_filter == 'Closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Low" <?php echo ($priority_filter == 'Low') ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo ($priority_filter == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo ($priority_filter == 'High') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label for="assigned_to">Assigned To</label>
                        <select id="assigned_to" name="assigned_to" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="unassigned" <?php echo ($assigned_to_filter == 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                            <?php foreach ($all_it_personnel as $person): ?>
                                <option value="<?php echo htmlspecialchars($person['id']); ?>" <?php echo ($assigned_to_filter == $person['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($person['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label for="main_category">Main Category</label>
                        <select name="category" id="main_category" class="form-control form-control-sm">
                            <option value="">All</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($selected_main_category == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label for="sub_category">Sub-Category</label>
                        <select name="sub_category" id="sub_category" class="form-control form-control-sm">
                            <option value="">All</option>
                        </select>
                    </div>

                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="table-responsive">
    <table id='myTickets' class="table table-bordered mb-0">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Category</th>
                <th>Assigned To</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tickets)): ?>
                <?php foreach ($tickets as $ticket): ?>
                
                    <tr >
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?>">
                                <?php echo htmlspecialchars($ticket['id']); ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?>">
                                <?php echo htmlspecialchars($ticket['subject']); ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?> <?php echo 'status-' . strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                <?php echo htmlspecialchars($ticket['status']); ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?> <?php echo 'priority-' . strtolower(htmlspecialchars($ticket['priority'])); ?>">
                                <?php echo htmlspecialchars($ticket['priority']); ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?>">
                                <?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?>">
                                <?php 
                                if ($ticket['assigned_to'] == $user_id) {
                                    echo 'Myself';
                                } else {
                                    $user_name = $userController->getUserNameById($ticket['assigned_to']);
                                    echo htmlspecialchars($user_name ? $user_name : 'Pending');
                                }
                                ?>
                            </td>
                            <td class="<?php echo strtolower(trim($ticket['status'])) == 'resolved' ? 'bg-light-green' : ''; ?>">
                                <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id']); ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                 
                                <?php if (strtolower(trim($ticket['status'])) == 'resolved'): ?>
                                <!-- Form to handle Solved/Unsolved actions -->
                                <form method="post" action="">
                                <div class="btn-group" role="group" aria-label="Resolved Ticket Actions">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">

                                    <button type="submit" name="response" value="solved" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Close
                                    </button>

                                    <button type="submit" name="response" value="unsolved" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Progress
                                    </button>
                                </div>
                            </form>

                            <?php endif; ?>

                            </td>
                        </tr>

                
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<!-- Section: Requests List -->
<?php if ($ishead): ?>
   <!-- Inside a container with full-width separator -->
<div class="container">
    <div class="full-width-separator">
        <hr class="my-4 border-primary">
    </div>
</div>


    <div class="container mt-4 dashboard-wrapper">
        <!-- Section: Requests List -->
        <div class="section-title mb-4">
            <h2><i class="fas fa-list"></i> Requests List</h2>
        </div>
        <div class="request-list-wrapper">
            <?php if (!empty($requests)): ?>
                <div class="request-list">
                    <?php foreach ($requests as $request): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-primary">
                                            <i class="fas fa-file-alt"></i> Request ID: <?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?> |
                                            <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($request['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>" class="text-primary">
                                                <i class="fas fa-ticket-alt"></i> Ticket ID: <?php echo htmlspecialchars($request['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </h6>
                                        <p class="mb-2"><?php echo htmlspecialchars($request['request_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <div>
                                        <span class="badge <?php echo htmlspecialchars($request['status']) === 'Approved' ? 'bg-success' : (htmlspecialchars($request['status']) === 'Pending' ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                            <?php echo htmlspecialchars($request['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Requested By: <?php echo htmlspecialchars($userController->getUserNameById($request['requested_by']), ENT_QUOTES, 'UTF-8'); ?> |
                                        <i class="fas fa-calendar-alt"></i> Created At: <?php echo htmlspecialchars($request['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                                    </small>
                                    <form method="post" name='request' class="approval-form mb-0">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="btn-group" role="group">
                                            <button type="submit" name="approve" value="approve" class="btn btn-sm btn-success">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button type="submit" name="decline" value="decline" class="btn btn-sm btn-danger">
                                                <i class="fa fa-times"></i> Decline
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i> No requests found for this user.
                </div>
            <?php endif; ?>
        </div>

<div class="container">
    <div class="full-width-separator">
        <hr class="my-4 border-primary">
    </div>
</div>

</div>


        <!-- Section: Approvals -->
        <div class="section-title mb-4 mt-5">
            <h2><i class="fas fa-clipboard-list"></i> Approvals</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="aproval">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Created by</th>
                        <th>Created on</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($aproval)): ?>
                        <?php foreach ($aproval as $ticket): ?>
                            <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td class="<?php echo 'status-' . strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                </td>
                                <td class="<?php echo 'priority-' . strtolower(htmlspecialchars($ticket['priority'])); ?>">
                                    <?php echo htmlspecialchars($ticket['priority']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                                <td>
                                    <?php
                                        $user_name = $userController->getUserNameById($ticket['created_by']);
                                        echo $user_name ? htmlspecialchars($user_name) : 'Unassigned';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                                <td>
                                    <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-info-circle"></i> More
                                    </a>
                                    <div class="btn-group" role="group">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" style="border-radius: 0; margin-right: -1px;">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm" style="border-radius: 0;">
                                                <i class="fa fa-times"></i> Decline
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- You can add an empty state message here if needed -->
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

            
        


            <div class="modal fade" id="addTicketModal" tabindex="-1" role="dialog" aria-labelledby="addTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTicketModalLabel">Create New Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                <label for="ticketPriority">Priority</label>
                <select class="form-control" id="ticketPriority" name="priority">
                    <option value="Low" <?php if ($defaultTicketPriority == 'Low') echo 'selected'; ?>>Low</option>
                    <option value="Medium" <?php if ($defaultTicketPriority == 'Medium') echo 'selected'; ?>>Medium</option>
                    <option value="High" <?php if ($defaultTicketPriority == 'High') echo 'selected'; ?>>High</option>
                </select>
                </div>
                    <div class="form-group">
                                            <label for="category">Main Category</label>
                                            <select name="category" id="modal_category" class="form-control">
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sub_category">Sub-Category</label>
                                            <select name="sub_category" id="modal_sub_category" class="form-control"></select>
                                        </div>
                                        <div class="form-group">
                                            <label for="attachments">Attachments</label>
                                            <input type="file" id="attachments" name="attachments[]" class="form-control-file" multiple>
                                            <small id="attachmentHelp" class="form-text text-muted">
                                            Max attachment size: <?php echo $MaxSize / 1048576; ?> MB per file
                                            </small>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary">Create Ticket</button>
                                    </form>

            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closeTicketModalLabel">Close Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="confirm_ticket_id" id="confirm_ticket_id">
                    <p>Are you sure you want to close this ticket?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Close Ticket</button>
                </div>
            </form>
        </div>
    </div>
    </div>
    <?php if (!$ishead && !empty($pending)): ?>
        <div class="container">
    <div class="full-width-separator">
        <hr class="my-4 border-primary">
    </div>
    </div>
        <div class="card-header">
            <h3>Tickets Awaiting Approval</h3>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered table-hover" id="aproval">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Created on</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $ticket): ?>
                        <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                            <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td class="<?php echo 'status-' . strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                <?php echo htmlspecialchars($ticket['status']); ?>
                            </td>
                            <td class="<?php echo 'priority-' . strtolower(htmlspecialchars($ticket['priority'])); ?>">
                                <?php echo htmlspecialchars($ticket['priority']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                            <td>
                                <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-info-circle"></i> More
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
<?php endif; ?>
</div>

    


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="controllers/functions.js"></script>
<script src="js\dashboard.js"></script>
<script>
    $(document).ready(function() {
        var selectedMainCategory = $('#main_category').val();
        if (selectedMainCategory) {
            fetchSubCategories('dashboard.php', selectedMainCategory, '#sub_category', '<?php echo htmlspecialchars($selected_sub_category); ?>');
        }

        $('#main_category').change(function() {
            var mainCategoryId = $(this).val();
            fetchSubCategories('dashboard.php', mainCategoryId, '#sub_category', '');
        });

        $('#modal_category').change(function() {
            var mainCategoryId = $(this).val();
            fetchSubCategories('dashboard.php', mainCategoryId, '#modal_sub_category', '');
        });

        $('#addTicketModal').on('show.bs.modal', function() {
            var selectedMainCategory = $('#modal_category').val();
            fetchSubCategories('dashboard.php', selectedMainCategory, '#modal_sub_category', '');
        });

        $('#modal_category').change(function() {
            var mainCategoryId = $(this).val();
            fetchSubCategories('dashboard.php', mainCategoryId, '#modal_sub_category', '');
        });
  
});


 
</script>


</body>
</html>