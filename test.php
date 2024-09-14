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
$approvalTickets=[];
// Assuming $userController is an instance of your UserController class
$ishead = $userController->isHead($user_id);

if ($ishead) {
    // Get department ID for the head
    $depId = $userController->getDepartmentIdByUserId($user_id);
    
    // Fetch tickets requiring approval by department ID
    $approvalTickets = $userController->getTicketsRequiringApproval($depId);
    
    // Do something with $approvalTickets if needed
    print_r($approvalTickets); // or any other logic to handle the tickets
}

// Output whether the user is a head
echo $ishead ? 'true' : 'false';

$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$assigned_to_filter = $_GET['assigned_to'] ?? '';
$category_filter = $_GET['category'] ?? '';
$sub_category_filter = $_GET['sub_category'] ?? '';
$selected_main_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_sub_category = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';


$tickets = $ITController->getAllTickets($status_filter, $priority_filter, $assigned_to_filter, $category_filter, $sub_category_filter, $user_id);
$all_it_personnel = $userController->getITPersonnel();
$categories = $ITController->getMainCategories();
//$ticket_history = $userController->getHistory($ticket_id);
$attachments = [];
$comments = [];
$isClosed = false;

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
        $category_id = $_POST['sub_category']; // Use 'sub_category' here
        $priority = $_POST['priority'];
        $created_by = $_SESSION['user_id'];
        $attachments = [];
    
        $userController = new UserController();
        $user_role = $userController->getUserRole($created_by);
    
        if (!empty($_FILES['attachments']['name'][0])) {
            $attachments = $userController->handleFileUploads($_FILES['attachments'],$MaxSize);
        }
    
        $result = $userController->createTicket($subject, $description, $category_id, $priority, $created_by, $user_role, $attachments);
    
        if ($result) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $result;
        }
    }
     if (isset($_POST['confirm_ticket_id'])) {
        $ticket_id = $_POST['confirm_ticket_id'];
        $userController->updateStatus($ticket_id, 'Closed');
        $userController->addHistory($ticket_id, 'Closed', $user_id);
        header("Location: dashboard.php?ticket_id=$ticket_id");
        exit();
    }
    if (isset($_POST['reset_ticket_id'])) {
        $ticket_id = $_POST['reset_ticket_id'];
        $userController->updateStatus($ticket_id, 'In Progress');
        $userController->addHistory($ticket_id, 'In Progress', $user_id);
        header("Location: dashboard.php?ticket_id=$ticket_id");
        exit();
    }
    if (isset($_POST['add_comment']) && isset($_POST['ticket_id']) && !$isClosed) {
        header('Content-Type: application/json');
        $comment = $_POST['comment'];
        $ticket_id = $_POST['ticket_id']; // Ensure ticket_id is fetched from POST data
        $result = $userController->addComment($ticket_id, $user_id, $comment);
        
        if ($result) {
            $newComment = [
                'comment' => $comment,
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
            echo json_encode(['status' => 'success', 'comment' => $newComment]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add comment']);
        }
        exit();
    }
    
     elseif (isset($_POST['close_ticket'])) {
        $ticket_id = $_POST['ticket_id'];
        $userController->updateStatus($ticket_id, 'Closed');
        $userController->addHistory($ticket_id, 'Closed', $user_id);
        header("Location: dashboard.php?ticket_id=$ticket_id");
        exit();
    }
}

$ticket_details = null;

if ($ticket_id) {
    $ticket_details = $userController->getTicketById($ticket_id);
    if ($ticket_details) {
        $attachments = $userController->getAttachmentsByTicketId($ticket_id);
        $comments = $userController->getCommentsByTicketId($ticket_id);
        $isClosed = ($ticket_details['status'] == 'Closed');
    }
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
    .dashboard-wrapper {
        font-family: Arial, sans-serif;
        padding: 20px;
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

    .dashboard-wrapper .comment-item {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 10px;
        word-wrap: break-word; /* Add this to ensure long words break */
    }

    .dashboard-wrapper .my-comment {
        background-color: #f0f8ff; /* Light blue background for current user's comments */
    }

    .dashboard-wrapper .comments-list {
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }

    .dashboard-wrapper .modal-body p strong {
        display: inline-block;
        width: 150px;
    }

    .modal-body {
        word-wrap: break-word; /* Add this to ensure long words break */
    }

    .modal-body p, .modal-body ul {
        word-wrap: break-word; /* Add this to ensure long words break */
    }

    .ticket-comments {
        word-wrap: break-word; /* Add this to ensure long words break */
    }

    .ticket-comments .comment-item {
        word-wrap: break-word; /* Add this to ensure long words break */
    }
    /* Special styling for the solution section in the view ticket modal */
    .dashboard-wrapper .solution-section {
            background-color: #f8f9fa; /* Light background color */
            border-left: 5px solid #007bff; /* Blue left border */
            padding: 15px; /* Padding around the text */
            border-radius: 5px; /* Rounded corners */
            margin-bottom: 20px; /* Space below the section */
        }

    .dashboard-wrapper .solution-section p {
            margin: 0;
            color: #343a40; /* Dark text color */
            font-weight: 500; /* Medium font weight */
        }

</style>
<head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="dashboard-wrapper">
        <div class="container mt-4">
            <h2>User Dashboard</h2>
            <div class="card mb-4">
                <div class="card-header">
                <form method="get" class="form-inline mb-4">
      

            <label class="mr-2" for="status">Status:</label>
            <select name="status" id="status" class="form-control mr-2" >
                <option value="">All</option>

                    <option value="New" <?php echo $status_filter == 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="On Hold" <?php echo $status_filter == 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="Closed" <?php echo $status_filter == 'Closed' ? 'selected' : ''; ?>>Closed</option>

            </select>

            <label class="mr-2" for="priority">Priority:</label>
            <select name="priority" id="priority" class="form-control mr-2">
                <option value="">All</option>
                <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>High</option>
                <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Low</option>
            </select>

            <label class="mr-2" for="assigned_to">Assigned To:</label>
        
             <select id="assigned_to" name="assigned_to" class="form-control">
                <option value="">All</option>
                             
                 <option value="unassigned" <?php echo ($assigned_to_filter == 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                 <?php foreach ($all_it_personnel as $person): ?>
                                    <option value="<?php echo htmlspecialchars($person['id']); ?>" <?php echo ($assigned_to_filter == $person['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($person['first_name']); ?></option>
                 <?php endforeach; ?>
            </select>

            <label class="mr-2" for="category">Main Category:</label>
            <select name="category" id="main_category" class="form-control mr-2">
                <option value="">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($selected_main_category == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label class="mr-2" for="sub_category"></label>
            <select name="sub_category" id="sub_category" class="form-control mr-2">
                <option value="">Select Sub-Category</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

            <a href="#addTicketModal" class="btn btn-success mb-4" data-toggle="modal">New Issue</a>
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
<?php if ($ishead): ?>
    <h4>Recommended Available Tickets</h4>
    <div class="table-responsive mb-4">
        <table class="table table-striped table-bordered table-hover" id="recomended">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Assigned To</th>
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
                                if ($ticket['assigned_to'] == $user_id) {
                                    echo 'Myself';
                                } else {
                                    $user_name = $UserController->getUserNameById($ticket['assigned_to']);
                                    echo $user_name ? htmlspecialchars($user_name) : 'Unassigned';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-info-circle"></i> More
                                    </a>
                                    <a href="approveTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success btn-sm">
                                        <i class="fa fa-check"></i> Approve
                                    </a>
                                    <a href="declineTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-sm">
                                        <i class="fa fa-times"></i> Decline
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Optionally handle the case where there are no tickets -->
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<h4>My Tickets Tickets</h4>
    <div class="table-responsive">
    <table class="table table-striped table-bordered " id='myTickets'>
        <thead>
            <tr>
                <th>Ticket ID</th>
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
                            if ($ticket['assigned_to'] == $user_id) {
                                echo 'Myself';
                            } else {
                                $user_name = $userController->getUserNameById($ticket['assigned_to']);
                                echo htmlspecialchars($user_name ? $user_name : 'Pending');
                            }
                            ?>
                        </td>
                        <td>
                            <a href="dashboard.php?ticket_id=<?php echo htmlspecialchars($ticket['id']); ?>" class="btn btn-primary btn-sm">View</a>
                            <?php if ($ticket['status'] != 'Closed'): ?>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#closeTicketModal" data-ticket-id="<?php echo htmlspecialchars($ticket['id']); ?>">Close</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
    

            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($ticket_details): ?>
    <div class="modal fade" id="viewTicketModal" tabindex="-1" role="dialog" aria-labelledby="viewTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketDetailsModalLabel">Ticket Details - #<?php echo htmlspecialchars($ticket_details['id']); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Ticket details content -->
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($ticket_details['subject']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($ticket_details['description']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket_details['status']); ?></p>
                    <p><strong>Priority:</strong> <?php echo htmlspecialchars($ticket_details['priority']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($ITController->getCategoryNameById($ticket_details['category_id'])); ?></p>
                    <p><strong>Assigned To:</strong> 
                        <?php 
                            $user_name = $userController->getUserNameById($ticket_details['assigned_to']);
                            echo htmlspecialchars($user_name ? $user_name : 'Pending');
                        ?>
                    </p>
                    <div class="mb-3 solution-section">
                        <strong>Solution:</strong>
                        <?php if (!empty($ticket_details['solution'])): ?>
                            <p><?php echo htmlspecialchars($ticket_details['solution']); ?></p>
                        <?php else: ?>
                            <p>Not solved yet</p>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Attachments:</strong>
                        <p>
                            <?php if (!empty($attachments)): ?>
                                <?php foreach ($attachments as $attachment): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank"><?php echo htmlspecialchars(basename($attachment['file_path'])); ?></a><br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                No attachments.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="ticket-comments mb-4">
                        <h5>Comments:</h5>
                        <div id="comments-list">
                            <?php if (!empty($comments)): ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item <?php echo $comment['user_id'] == $user_id ? 'my-comment' : ''; ?>">
                                        <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                        <small>By <?php echo htmlspecialchars($userController->getUserNameById($comment['user_id'])); ?> on <?php echo htmlspecialchars($comment['created_at']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No comments</p>
                            <?php endif; ?>
                        </div>

                        <h4>Ticket History</h4>
                        <?php if (!empty($ticket_history)): ?>
                            <ul class="list-group">
                                <?php foreach ($ticket_history as $history): ?>
                                    <li class="list-group-item">
                                        <p><strong>Status:</strong> <?php echo htmlspecialchars($history['new_status']); ?></p>
                                        <p><strong>Changed by:</strong> <?php echo htmlspecialchars($history['changed_by']); ?></p>
                                        <p><strong>Date:</strong> <?php echo htmlspecialchars($history['changed_at']); ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No history available for this ticket.</p>
                        <?php endif; ?>
                        <?php if (!$isClosed): ?>
                            <div class="comment-form mb-4">
                                <form id="comment-form" action="dashboard.php?ticket_id=<?php echo $ticket_id; ?>" method="POST">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                                    <div class="form-group">
                                        <label for="comment">Add a comment:</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                                    </div>
                                    <button type="submit" name="add_comment" class="btn btn-primary">Add Comment</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?> 

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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="controllers/functions.js"></script>
<script>
    $(document).ready(function() {
     
        try {
        $('#recomended').DataTable({
            "language": {
                "emptyTable": "No Pending Tickets tickets available."
            }
        });
    } catch (error) {
        console.error('Error initializing DataTables for #recomended:', error);
    }

    try {
        $('#myTickets').DataTable({
            "language": {
                "emptyTable": "You Have no Tickets."
            }
        });
    } catch (error) {
        console.error('Error initializing DataTables for #Tickets:', error);
    }
        submitComment('#comment-form', '#comment', '#comments-list', '<?php echo htmlspecialchars($userController->getUserNameById($user_id)); ?>', '<?php echo $user_id; ?>');

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

        $('#closeTicketModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var ticketId = button.data('ticket-id');
            var modal = $(this);
            modal.find('#confirm_ticket_id').val(ticketId);
        });

        
        <?php if ($ticket_details): ?>
            $('#viewTicketModal').modal('show');
        <?php endif; ?>

        // Remove redirection when the modal is closed
        // $('#viewTicketModal').on('hidden.bs.modal', function() {
        //     window.location.href = 'dashboard.php';
        // });

        $('.modal .close').click(function() {
            $(this).closest('.modal').modal('hide');
        });
    });
</script>


</body>
</html>