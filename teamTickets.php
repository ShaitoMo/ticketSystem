<?php
// Start session and include necessary controllers
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

$ITController = new ITController();
$AdminController = new AdminController();
$UserController = new UserController();

$user_id = $_SESSION['user_id'];
$isLeader = $ITController->isUserLeader($user_id);
$teamId = null;
$TeamUsers = [];
$teamName = null;

if ($isLeader) {
    $teams = $ITController->getTeamsLedByUser($user_id);
    $currentTeamId = isset($_GET['team_id']) ? $_GET['team_id'] : $teams[0]['id']; // Default to the first team
    $teamName = $ITController->getCategoryNameById($currentTeamId);
    $TeamUsers = $AdminController->getTeamMembers($currentTeamId);
    
    
} else {
    header("Location: ItDashboard.php");
    exit();
}

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$assigned_to_filter = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$subCategories = $ITController->getSubCategoriesByMainCategory($currentTeamId);

// Handle ticket assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $ticket_id = $_POST['ticket_id'];
    $assign_to = $_POST['assign_to'];
    $ITController->assignTicketToUser($ticket_id, $assign_to);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}


// Fetch tickets for the selected team
$Unassigned = $ITController->getTeamTickets($currentTeamId, 'New', '', 'unassigned', '', '');
$assigned = $ITController->getTeamTickets($currentTeamId, $status_filter, $priority_filter, $assigned_to_filter, $category_filter, '');
$resolved_tickets = $ITController->getTeamTickets($currentTeamId, 'Resolved', '', '', '', '');
$closed_tickets = $ITController->getTeamTickets($currentTeamId, 'closed', '', '', '', '');
$unassigned_count = count($Unassigned);
$assigned_count = count($assigned);
$resolved_count = count($resolved_tickets);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Tickets</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-sm {
            margin: 0 2px;
        }
        .input-group .form-control {
            max-width: 150px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filter-form select,
        .filter-form button {
            flex: 1;
            min-width: 150px;
        }
        .team-overview {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .team-overview h4 {
            margin-bottom: 10px;
        }
        .team-overview .overview-item {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #ccc;
        }
        .team-overview .overview-item:last-child {
            border-right: none;
        }
        .table {
            max-width: 95%;
        }
        #archivedTickets {
            display: none;
        }
    </style>
</head>
<body>
    <?php include_once('header.php'); ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle"></i> You are currently viewing the tickets for the <strong><?php echo htmlspecialchars($teamName); ?></strong> team. 
    <?php if (count($teams) > 1): ?>
        You are also leading other teams. Use the dropdown above to switch between teams.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

    <div class="container mt-5">
        <h1 class="mb-4"><?php echo htmlspecialchars($teamName); ?> Team Tickets</h1>
        <p class="text-muted">
            <i class="bi bi-info-circle"></i> This page displays all tickets related to your team. As a team leader, you can view unassigned tickets and assign them to team members. You can also view all assigned tickets within your team.
        </p>
        <div class="team-selector mb-3">
    <form method="GET" action="" class="form-inline">
        <label for="team_id" class="mr-2">Select Team:</label>
        <select name="team_id" id="team_id" class="form-control">
            <?php foreach ($teams as $team): ?>
                <option value="<?php echo htmlspecialchars($team['id']); ?>" <?php echo $currentTeamId == $team['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars( $ITController->getCategoryNameById($team['id'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary ml-2">Switch Team</button>
    </form>
</div>

        <div class="team-overview">
            <h4>
                <i class="bi bi-graph-up"></i> Team Overview
            </h4>
            <div class="overview-item">
                <strong><?php echo $unassigned_count; ?></strong><br>
                <i class="bi bi-exclamation-circle"></i> Unassigned Tickets
            </div>
            <div class="overview-item">
                <strong><?php echo $assigned_count; ?></strong><br>
                <i class="bi bi-person-check"></i> Assigned Tickets
            </div>
            <div class="overview-item">
                <strong><?php echo $resolved_count; ?></strong><br>
                <i class="bi bi-check-circle"></i> Resolved Tickets
            </div>
        </div>

        <h2 class="mt-5 mb-4">
            <i class="bi bi-hourglass-split"></i> Unassigned Tickets
        </h2>
        <div class="table-responsive">
            <table id="unassignedTickets" class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
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
                    <?php if (!empty($Unassigned)): ?>
                        <?php foreach ($Unassigned as $ticket): ?>
                            <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($ticket['status']); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($ticket['priority']); ?></td>
                                <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                                <td><?php echo $ticket['assigned_to'] ? htmlspecialchars($UserController->getUserNameById($ticket['assigned_to'])) : 'Unassigned'; ?></td>
                                <td class="d-flex align-items-center">
                                    <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm mr-2">
                                        <i class="bi bi-card-text"></i> Details
                                    </a>
                                    <?php if ($isLeader && $ITController->isTicketForTeam($ticket['id'], $currentTeamId)) { ?>
                                        <form method="post" action="" class="d-inline-flex align-items-center assign-form">
                                            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                            <div class="input-group input-group-sm">
                                                <select name="assign_to" class="form-control selectpicker" data-live-search="true">
                                                    <?php foreach ($TeamUsers as $person): ?>
                                                        <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-success ml-2 assign-btn">
                                                        <i class="bi bi-person-plus"></i> Assign
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5 mb-4">
            <i class="bi bi-list-check"></i> Assigned Tickets
        </h2>

        <!-- Filters Form -->
        <form method="GET" action="" class="filter-form">
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="New" <?php echo $status_filter == 'New' ? 'selected' : ''; ?>>
                    <i class="bi bi-star"></i> New
                </option>
                <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>
                    <i class="bi bi-hourglass-split"></i> In Progress
                </option>
                <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>
                    <i class="bi bi-check-circle"></i> Resolved
                </option>
                <option value="Closed" <?php echo $status_filter == 'Closed' ? 'selected' : ''; ?>>
                    <i class="bi bi-x-circle"></i> Closed
                </option>
            </select>

            <select name="priority" id="priority" class="form-control">
                <option value="">All Priorities</option>
                <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>
                    <i class="bi bi-arrow-down-circle"></i> Low
                </option>
                <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>
                    <i class="bi bi-arrow-right-circle"></i> Medium
                </option>
                <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>
                    <i class="bi bi-arrow-up-circle"></i> High
                </option>
                <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>
                    <i class="bi bi-exclamation-triangle"></i> Urgent
                </option>
            </select>

            <select name="category" id="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($subCategories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="assigned_to" id="assigned_to" class="form-control">
                <option value="">All Users</option>
                <?php foreach ($TeamUsers as $person): ?>
                    <option value="<?php echo htmlspecialchars($person['id']); ?>" <?php echo $assigned_to_filter == $person['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($person['first_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <button type="button" id="archive-toggle" class="btn btn-secondary">
                <i class="bi bi-archive"></i> Show Archived
            </button>
        </form>

        <div class="table-responsive">
            <table id="assignedTickets" class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
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
                    <?php if (!empty($assigned)): ?>
                        <?php foreach ($assigned as $ticket): ?>
                            <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($ticket['status']); ?></td>
                                <td class="text-capitalize"><?php echo htmlspecialchars($ticket['priority']); ?></td>
                                <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                                <td><?php echo $ticket['assigned_to'] ? htmlspecialchars($UserController->getUserNameById($ticket['assigned_to'])) : 'Unassigned'; ?></td>
                                <td class="d-flex align-items-center">
                                    <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm mr-2">
                                        <i class="bi bi-card-text"></i> Details
                                    </a>
                                    <?php if ($isLeader && $ITController->isTicketForTeam($ticket['id'], $teamId)) { ?>
                                        <form method="post" action="" class="d-inline-flex align-items-center assign-form">
                                            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                            <div class="input-group input-group-sm">
                                                <select name="assign_to" class="form-control selectpicker" data-live-search="true">
                                                    <?php foreach ($TeamUsers as $person): ?>
                                                        <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="submit" name="assign" class="btn btn-success ml-2">
                                                    <i class="bi bi-person-plus"></i> Re-Assign
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Archived Tickets Section -->
        <div id="archivedTickets">
            <h2 class="mt-5 mb-4">
                <i class="bi bi-archive"></i> Archived Tickets
            </h2>
            <div class="table-responsive">
                <table id="archivedTicketsTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
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
                        <?php if (!empty($closed_tickets)): ?>
                            <?php foreach ($closed_tickets as $ticket): ?>
                                <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                                    <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td class="text-capitalize"><?php echo htmlspecialchars($ticket['status']); ?></td>
                                    <td class="text-capitalize"><?php echo htmlspecialchars($ticket['priority']); ?></td>
                                    <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                                    <td><?php echo $ticket['assigned_to'] ? htmlspecialchars($UserController->getUserNameById($ticket['assigned_to'])) : 'Unassigned'; ?></td>
                                    <td class="d-flex align-items-center">
                                        <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm mr-2">
                                            <i class="bi bi-card-text"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Confirmation Modal -->
<div class="modal fade" id="assignConfirmModal" tabindex="-1" role="dialog" aria-labelledby="assignConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignConfirmModalLabel">Confirm Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to assign this ticket?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
           // Initialize the 'unassignedTickets' table with an empty table message
$('#unassignedTickets').DataTable({
    language: {
        emptyTable: "No unassigned tickets available"
    }
});

// Initialize the 'assignedTickets' table with an empty table message
$('#assignedTickets').DataTable({
    language: {
        emptyTable: "No assigned tickets available"
    }
});

// Initialize the 'archivedTicketsTable' with an empty table message
$('#archivedTicketsTable').DataTable({
    language: {
        emptyTable: "No archived tickets available"
    }
});

            $('#archive-toggle').click(function() {
                $('#archivedTickets').toggle();
            });
            var formToSubmit;

// Trigger the modal when the Assign button is clicked
        $('.assign-btn').on('click', function() {
            formToSubmit = $(this).closest('form');
            $('#assignConfirmModal').modal('show');
        });

        // Submit the form when the Confirm button in the modal is clicked
        $('#confirmAssignBtn').on('click', function() {
            formToSubmit.submit();
                });});
    </script>
</body>
</html>
