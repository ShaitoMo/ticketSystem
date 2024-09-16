<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$isSub=($user_role=='Sub-Admin');
$ticket_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$assigned_to_filter = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sub_category_filter = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';
$selected_main_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_sub_category = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';

$AdminController=new AdminController();
$UserController = new UserController();
$ITController = new ITController();

$settings=$AdminController->getSettings();
$assign=$settings['ticket_assignment'];
$disabledTakeTicket = ($assign === 'Auto' || $assign === 'Locked') ? 'disabled' : '';
$disabledTakeTicketIcon = ($assign === 'Auto' || $assign === 'Locked') ? 'bi bi-lock' : 'bi bi-check-circle';
$disabledTakeTicketTitle = ($assign === 'Auto' || $assign === 'Locked') ? 'Taking tickets is not allowed with current settings.' : 'Take Ticket';


$tickets = [];
$recomnded=[];
$TeamUsers=[];

$teamFound = false;
$all_it_personnel = $UserController->getITPersonnel(1);

$categories = $ITController->getMainCategories();
$Team = $ITController->getUserTeam($user_id);
$isLeader= $ITController->isUserLeader($user_id);
$teamId;
if ($isLeader){
    $teamId=$ITController->getTeamsLedByUser($user_id);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'getSubCategories') {
        $mainCategoryId = $_POST['category_id'];
        $subCategories = $ITController->getSubCategoriesByMainCategory($mainCategoryId);
        echo json_encode($subCategories);
        exit();
    }
    if (isset($_POST['assign'])) {
        $ticket_id = $_POST['ticket_id'];
        $assign_to = $_POST['assign_to'];
        $ITController->assignTicketToUser($ticket_id, $assign_to);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    if (isset($_POST['take_ticket'])) {
        $ticket_id = $_POST['ticket_id'];
        $action = $_POST['submit'];
        $ITController->assignTicketToUser($ticket_id, $user_id);
        $ITController->updateTicketStatus($ticket_id, $action, $user_id);
        
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } elseif (isset($_POST['on_hold'])) {
        $ticket_id = $_POST['ticket_id'];

        $ITController->updateTicketStatus($ticket_id, 'On Hold', $user_id);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } elseif (isset($_POST['resolved'])) {
        $ticket_id = $_POST['ticket_id'];
        $ITController->updateTicketStatus($ticket_id, 'Resolved', $user_id);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } elseif (isset($_POST['in_progress'])) {
        $ticket_id = $_POST['ticket_id'];

        $ITController->updateTicketStatus($ticket_id, 'In Progress', $user_id);
  
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}
$campuses=$AdminController->getCampuses();
$campus=$UserController->getCampusbyUser($user_id);
$campus_id=$campus['campus_id'];
$isBeirut=($campus_id=='1');

$campus_filter = isset($_GET['campus']) ? $_GET['campus'] : 'all';
if ($ticket_type == 'all'){
    $tickets = $ITController->getAllTickets('', $priority_filter, $priority_filter, $category_filter, $sub_category_filter, '',1,$campus_filter);
}
elseif ($ticket_type == 'new') {
    $tickets = $ITController->getAllTickets('new', $priority_filter, 'unassigned', $category_filter, $sub_category_filter, '',1,$campus_id);
    $recomnded=$ITController->getRecommendedTickets($user_id);
} elseif ($ticket_type == 'progressed') {
    $tickets = $ITController->getItProgressedTickets($user_id, $status_filter, $priority_filter, $user_id, $category_filter, $sub_category_filter);
} elseif ($ticket_type == 'closed') {
    $tickets = $ITController->getAllTickets('closed', $priority_filter, $user_id, $category_filter, $sub_category_filter, '',1,'all');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket System Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">

    <style>
        .dashboard .status-new {
                color: green;
            }

            .dashboard .status-in-progress {
                color: orange;
            }

            .dashboard .status-on-hold {
                color: blue;
            }

            .dashboard .status-resolved {
                color: green;
            }

            .dashboard .status-closed {
                color: red;
            }

            .dashboard .priority-high {
                font-weight: bold;
            }

            .dashboard .priority-low {
                color: grey;
            }

            .dashboard .priority-medium {
                color: orange;
            }

            .dashboard .back-button {
                margin-bottom: 20px;
            }

            .modal-footer .btn {
                margin-right: 10px;
            }

            /* Adjust table max width for better layout */
            .table {
                max-width: 95%;
                margin: 0 auto; /* Center the table */
            }

            /* Dashboard card styling */
            .dashboard .card {
                border: 1px solid #007bff;
                border-radius: 0.25rem; /* Add subtle rounding to corners */
            }

            .dashboard .card-header {
                background-color: #007bff;
                color: white;
                border-bottom: 1px solid #0056b3;
            }

            .dashboard .card-body {
                padding: 20px;
            }

            /* Form Group Spacing */
            .form-group {
                margin-bottom: 1rem;
            }

            /* Align the submit button to the bottom of the row */
            .form-group.d-flex {
                justify-content: flex-end;
                align-items: flex-end;
            }

            /* Flex and responsiveness for form row */
            .form-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            /* Input field height adjustment for consistency */
            .form-control {
                height: calc(1.5em + 0.75rem + 2px);
            }

            /* Responsive adjustments for smaller screens */
            @media (max-width: 768px) {
                .form-group {
                    flex: 0 0 100%;
                }

                .form-group.d-flex {
                    justify-content: center;
                }

                .dashboard .card-body {
                    padding: 15px; /* Reduce padding on smaller screens */
                }

                .table {
                    max-width: 100%; /* Ensure the table fits smaller screens */
                }
            }

            /* Reduce modal button margin on smaller screens */
            @media (max-width: 576px) {
                .modal-footer .btn {
                    margin-right: 5px;
                }
            }


    </style>
</head>
<body>
    <?php include_once('header.php');?>

    <div class="container my-4 dashboard">
    <div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Tickets</h5>
    </div>
    <div class="card-body">
        <form method="get" action="">
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
                <?php if($isSub): ?>
                <div class="form-group col-md-2">
                <label for="campus">Campus</label>
                <select id="campus" name="campus" class="form-control form-control-sm">
                    <option value="all">All Campuses</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?php echo htmlspecialchars($campus['id']); ?>" <?php echo (isset($_GET['campus']) && $_GET['campus'] == $campus['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($campus['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>  
            <?php endif ?>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>
</div>

    <?php if ($ticket_type == 'new'): ?>
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
                <?php if (!empty($recomnded)): ?>
                    <?php foreach ($recomnded as $ticket):    
                    $teamFound = false;
                                foreach ($teamId as $team) {
                                    if ($ITController->isTicketForTeam(htmlspecialchars($ticket['id']), $team['id'])) {
                                        $teamFound = true;
                                        $TeamUsers = $AdminController->getTeamMembers($team['id']); // Get members of the specific team
                                        break; // Stop the loop once the team is found
                                    }
                                }
                            ?>
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
                                <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-info-circle"></i> more
                                </a>
                                <?php if ($ticket['status'] != 'Closed'|| $ticket['status'] != 'Resolved'): ?>
                                    <?php if (empty($ticket['assigned_to'])): ?>
                                        <button type="button" class="btn btn-success btn-sm <?php echo $disabledTakeTicket; ?>" onclick="showTakeTicketModal(<?php echo htmlspecialchars($ticket['id']); ?>)" title="<?php echo $disabledTakeTicketTitle; ?>">
                                            <i class="<?php echo $disabledTakeTicketIcon; ?>"></i> Take Ticket
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($ticket['status'] != 'In Progress' && $ticket['assigned_to'] == $user_id): ?>
                                        <button type="button" class="btn btn-info btn-sm" onclick="showActionModal('in_progress', <?php echo htmlspecialchars($ticket['id']); ?>)">Progress</button>
                                    <?php endif; ?>
                                    <?php if ($ticket['status'] != 'On Hold' && $ticket['assigned_to'] == $user_id): ?>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="showActionModal('on_hold', <?php echo htmlspecialchars($ticket['id']); ?>)">Hold</button>
                                    <?php endif; ?>
                                    
                                <?php endif; ?>
                                <?php if ($isLeader) {
                                    if ($ITController->isTicketForTeam(htmlspecialchars($ticket['id']), $teamId)) { ?>
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                            <select name="assign_to" class="form-control form-control-sm d-inline w-auto">
                                                <?php foreach ($TeamUsers as $person): ?>
                                                    <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign" class="btn btn-success btn-sm">Assign</button>
                                        </form>
                                    <?php 
                                    }
                                } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

        <h4>Available Tickets</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="Tickets">
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
                    <?php if (!empty($tickets)): ?>
                        <?php 
                            // Iterate over each ticket
                            foreach ($tickets as $ticket):
                                // Initialize flag and retrieve team information for each ticket
                                $teamFound = false;
                                foreach ($teamId as $team) {
                                    if ($ITController->isTicketForTeam(htmlspecialchars($ticket['id']), $team['id'])) {
                                        $teamFound = true;
                                        $TeamUsers = $AdminController->getTeamMembers($team['id']); // Get members of the specific team
                                        break; // Stop the loop once the team is found
                                    }
                                }
                            ?>
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
                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        <td>
                        <div class="btn-group btn-group-sm" role="group">
                <!-- Details Button -->
                <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
                    <i class="bi bi-card-text"></i> Details
                </a>

                <!-- Take Ticket Button -->
                <?php if ($ticket['status'] != 'Closed'): ?>
                    <?php if (empty($ticket['assigned_to'])): ?>
                        <button type="button" class="btn btn-success btn-sm<?php echo $disabledTakeTicket; ?>" onclick="showTakeTicketModal(<?php echo htmlspecialchars($ticket['id']); ?>)" title="<?php echo htmlspecialchars($disabledTakeTicketTitle, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="<?php echo htmlspecialchars($disabledTakeTicketIcon, ENT_QUOTES, 'UTF-8'); ?>"></i> Take Ticket
                        </button>
                    <?php endif; ?>

                    <!-- Progress Button -->
                    <?php if ($ticket['status'] != 'In Progress' && $ticket['assigned_to'] == $user_id): ?>
                        <button type="button" class="btn btn-primary" onclick="showActionModal('in_progress', <?php echo htmlspecialchars($ticket['id']); ?>)">
                            <i class="fa fa-spinner"></i> Progress
                        </button>
                    <?php endif; ?>

                    <!-- Hold Button -->
                    <?php if ($ticket['status'] != 'On Hold' && $ticket['assigned_to'] == $user_id): ?>
                        <button type="button" class="btn btn-warning" onclick="showActionModal('on_hold', <?php echo htmlspecialchars($ticket['id']); ?>)">
                            <i class="fa fa-pause-circle"></i> Hold
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if($isSub): ?>
                <form method="post" action="" class="d-flex align-items-center mt-2">
                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                    <div class="form-group mb-0 mr-2">
                        <select name="assign_to" class="form-control form-control-sm">
                            <option value="" disabled selected>Force to</option> <!-- Disabled option -->

                           
                                <?php $it_personnel = $UserController->getITPersonnel($ticket['campus_id']);
                                        foreach ($it_personnel as $person): ?>
                                            <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                        <?php endforeach; ?>
                           
                        </select>
                    </div>
                    <button type="submit" name="assign" class="btn btn-success btn-sm">
                        <i class="bi bi-person-plus"></i> Assign
                    </button>
                </form>
            <?php elseif ($isLeader && $teamFound): ?>
                <form method="post" action="" class="d-flex align-items-center mt-2">
                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                    <div class="form-group mb-0 mr-2">
                        <select name="assign_to" class="form-control form-control-sm">
                            <option value="" disabled selected>Force to</option> <!-- Disabled option -->
                            <?php foreach ($TeamUsers as $person): ?>
                                <option value="<?php echo htmlspecialchars($person['id']); ?>">
                                    <?php echo htmlspecialchars($person['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="assign" class="btn btn-success btn-sm">
                        <i class="bi bi-person-plus"></i> Assign
                    </button>
                </form>
            <?php endif; ?>

                        </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="actionForm">
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="actionTicketId">
                    <input type="hidden" name="action" id="actionType">
                    Are you sure you want to change the status of this ticket to <span id="actionStatus"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="takeTicketModal" tabindex="-1" role="dialog" aria-labelledby="takeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="takeTicketModalLabel">Take Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>What action would you like to take?</p>
                <form method="post" id="takeTicketForm">
                    <input type="hidden" name="ticket_id" id="takeTicketId">
                    <input type="hidden" name="take_ticket" value="true">
                    <div class="form-group text-center">
                        <button type="submit" name="submit" value="In Progress" class="btn btn-custom btn-info" title="Mark as In Progress">
                            <i class="bi bi-play-circle"></i> Progress
                        </button>
                        <button type="submit" name="submit" value="On Hold" class="btn btn-custom btn-warning" title="Put on Hold">
                            <i class="bi bi-pause-circle"></i> Hold
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

   
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="controllers/functions.js"></script>

    <script>
      $(document).ready(function() {
        try {
        $('#recomended').DataTable({
            "language": {
                "emptyTable": "No recommended tickets available."
            }
            ,
            "order": [[6, "desc"]]
        });
    } catch (error) {
        console.error('Error initializing DataTables for #recomended:', error);
    }

    try {
        $('#Tickets').DataTable({
            "language": {
                "emptyTable": "No tickets available."
            },
            "order": [[6, "desc"]] // Replace with the index of the created_at column
        });
       
    } catch (error) {
        console.error('Error initializing DataTables for #Tickets:', error);
    }
    
    const assignmentSetting = '<?php echo $assign; ?>';
    const selectedMainCategory = "<?php echo $selected_main_category; ?>";
    const selectedSubCategory = "<?php echo $selected_sub_category; ?>";

    if (selectedMainCategory) {
        fetchSubCategories('itTickets.php', selectedMainCategory, '#sub_category', selectedSubCategory);
    }

    $('#main_category').change(function() {
        const mainCategoryId = $(this).val();
        fetchSubCategories('itTickets.php', mainCategoryId, '#sub_category', '');
    });

    window.showTakeTicketModal = function(ticketId) {
        if (assignmentSetting === 'Auto' || assignmentSetting === 'Locked') {
            alert('You cannot take tickets when the assignment setting is set to Auto or Locked.');
        } else {
            $('#takeTicketId').val(ticketId);
            $('#takeTicketModal').modal('show');
        }
    };

    window.showActionModal = function(action, ticketId) {
    $('#actionTicketId').val(ticketId);
    $('#actionType').attr('name', action);
    
    let actionMessage = {
        'on_hold': 'On Hold',
        'resolved': 'Resolved',
        'in_progress': 'In Progress'
    };

    $('#actionStatus').text(actionMessage[action]);
    $('#actionModal').modal('show');
};

    // Event handlers for closing modals
    $(document).on('click', '#actionForm .btn-secondary, #actionModal .close', function() {
        $('#actionModal').modal('hide');
    });
});

      
    </script>
</body>
</html>
