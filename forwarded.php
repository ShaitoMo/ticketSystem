<?php 
require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$UserController = new UserController();
$ITController = new ITController();
$user_id = $_SESSION['user_id'];
$admin = new AdminController();
$unassigned = $admin->getUnAssignedForwardedTickets(1);
$assigned = $admin->getAssignedForwardedTickets(1);
$otherCampuses = $admin->getAssignedForwardedTickets('other');
$it_personnel = $UserController->getITPersonnel(1);

if (isset($_POST['assign'])) {
    $ticket_id = $_POST['ticket_id'];
    $assign_to = $_POST['assign_to'];
    $ITController->assignTicketToUser($ticket_id, $assign_to);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forwarded Tickets Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #e9ecef; /* Light gray background */
        }
        .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }
        .table-header {
            margin-bottom: 20px;
        }
        .table-icon {
            font-size: 1.5rem;
            vertical-align: middle;
            margin-right: 10px;
        }
        .table-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .table-actions .form-control {
            margin-right: 5px;
        }
        .table-actions a {
            margin-right: 5px;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .table {
            background-color: #fff; /* Table background color */
            border-radius: 0.5rem; /* Rounded corners for table */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for table */
            max-width: 97%;
        }
        .table thead th {
            background-color: #343a40; /* Dark header background */
            color: #fff; /* White text color */
            white-space: nowrap;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin-bottom: 15px;
        }
        .info-section p {
            margin-bottom: 10px;
        }
        .section-divider {
            margin: 40px 0; /* Spacing between sections */
            border-top: 1px solid #dee2e6; /* Light border to separate sections */
        }
    </style>
</head>
<body>
<?php include_once('header.php');?>

<div class="container-fluid my-4">
    <!-- Introduction Section -->
    <div class="info-section muted">
        <h3>Welcome to the Forwarded Tickets Management Page</h3>
        <p>This page allows you to manage and track forwarded tickets. You can view unassigned tickets, assign them to IT personnel, and check the status of already assigned tickets.</p>
        <p>Use the tables below to see the details of unassigned and assigned tickets. You can also assign unassigned tickets to available IT personnel.</p>
    </div>

    <!-- Unassigned Tickets -->
    <div class="table-header">
        <h2><i class="bi bi-exclamation-diamond table-icon"></i>Unassigned Forwarded Tickets</h2>
    </div>
    <div class="table-responsive">
        <table id="unassignedTicketsTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Campus</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unassigned as $ticket): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCampusNameById($ticket['campus_id'])); ?></td>
                        <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['created_by'])); ?></td>
                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        <td class="table-actions">
                            <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm" title="View ticket details">
                                <i class="bi bi-eye"></i> Details
                            </a>
                            <!-- Assignment Form -->
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                <select name="assign_to" class="form-control form-control-sm d-inline" style="width: 150px;" title="Select IT personnel to assign">
                                    <?php foreach ($it_personnel as $person): ?>
                                        <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="assign" class="btn btn-primary btn-sm" title="Assign this ticket">Assign</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Assigned Tickets -->
    <div class="table-header">
        <h2><i class="bi bi-check-circle table-icon"></i>Assigned Forwarded Tickets</h2>
    </div>
    <div class="table-responsive">
        <table id="assignedTicketsTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Campus</th>
                    <th>Assigned To</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned as $ticket): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCampusNameById($ticket['campus_id'])); ?></td>
                        <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['assigned_to'])); ?></td>
                        <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['created_by'])); ?></td>
                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        <td class="table-actions">
                            <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm" title="View ticket details">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Other Campuses Tickets -->
    <div class="table-header">
        <h2><i class="bi bi-building table-icon"></i>Forwarded Tickets to Other Campuses</h2>
    </div>
    <div class="table-responsive">
        <table id="otherCampusesTicketsTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Forwarded To Campus</th>
                    <th>Assigned To</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($otherCampuses as $ticket): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCategoryNameById($ticket['category_id'])); ?></td>
                        <td><?php echo htmlspecialchars($ITController->getCampusNameById($ticket['forwarded_to_campus_id'])); ?></td>
                        <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['assigned_to'])); ?></td>
                        <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['created_by'])); ?></td>
                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                        <td class="table-actions">
                            <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm" title="View ticket details">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#unassignedTicketsTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true
    });
    $('#assignedTicketsTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true
    });
    $('#otherCampusesTicketsTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true
    });
});
</script>
</body>
</html>
