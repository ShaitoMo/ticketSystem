<?php
session_start();
require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ticket_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$assigned_to_filter = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sub_category_filter = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';
$selected_main_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_sub_category = isset($_GET['sub_category']) ? $_GET['sub_category'] : '';

$UserController = new UserController();
$ITController = new ITController();
$admin = new AdminController();
$campuses=$admin->getCampuses();
$categories = $ITController->getMainCategories();
$tickets = [];
$campus_filter = isset($_GET['campus']) ? $_GET['campus'] : 'all';

if ($ticket_type == 'all') {
    $tickets = $ITController->getAllTickets($status_filter, $priority_filter, $assigned_to_filter, $category_filter, $sub_category_filter, '', 1,$campus_filter);
    $title = 'All Active Tickets';
    $description = 'View and manage all tickets.';
    $icon = 'bi-ticket';
} elseif ($ticket_type == 'new') {
    $tickets = $ITController->getAllTickets('New', $priority_filter, 'unassigned', $category_filter, $sub_category_filter, '', 1,$campus_filter);
    $title = 'New Tickets';
    $description = 'View all newly created tickets.';
    $icon = 'bi-plus-circle';
} elseif ($ticket_type == 'Closed') {
    $tickets = $ITController->getAllTickets('Closed', $priority_filter, $assigned_to_filter, $category_filter, $sub_category_filter, '', 1,$campus_filter);
    $title = 'Archived Tickets';
    $description = 'Tickets that have been closed.';
    $icon = 'bi-x-circle';
} elseif ($ticket_type == 'inprogress') {
    $tickets = $ITController->getItProgressedTickets('all', $status_filter, $priority_filter, $assigned_to_filter, $category_filter, $sub_category_filter);
    $title = 'In-Progress Tickets';
    $description = 'Tickets currently in progress.';
    $icon = 'bi-hourglass-split';
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <style>
  body.it-dashboard {
            background-color: #ffffff;
            color: #333333;
        }
        .it-dashboard .container {
            max-width: 1200px;
        }
        .it-dashboard .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .it-dashboard .btn-custom:hover {
            background-color: #0056b3;
            color: white;
        }
        .it-dashboard .card {
            border: 1px solid #007bff;
        }
        .it-dashboard .card-header {
            background-color: #007bff;
            color: white;
            border-bottom: 1px solid #0056b3;
        }
        .it-dashboard .card-body {
            padding: 20px;
        }
        .it-dashboard .table th, .it-dashboard .table td {
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .it-dashboard .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .it-dashboard .status-new {
            color: #dc3545; /* red */
        }
        .it-dashboard .status-in-progress {
            color: #ffc107; /* yellow */
        }
        .it-dashboard .status-resolved {
            color: #28a745; /* green */
        }
        .it-dashboard .status-closed {
            color: #6c757d; /* gray */
        }
        .it-dashboard .priority-low {
            color: #17a2b8; /* teal */
        }
        .it-dashboard .priority-medium {
            color: #ffc107; /* yellow */
        }
        .it-dashboard .priority-high {
            color: #dc3545; /* red */
        }
    </style>
</head>
<body class="it-dashboard">
    <?php require_once 'header.php'; ?>
    <div class="container mt-5">
        <a href="AdminDashboard.php" class="btn btn-secondary mb-4">Back</a>
        <div class="d-flex align-items-center mb-4">
        <i class="<?php echo $icon; ?> mr-2" style="font-size: 2rem;"></i>
        <div>
            <h2 class="mb-0"><?php echo $title; ?></h2>
            <p class="text-muted"><?php echo $description; ?></p>
        </div>
    </div>
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

                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>
</div>

        <div class="table-responsive">
            <table id="ticketsTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Assigned To</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
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
                                            echo htmlspecialchars($user_name ? $user_name : 'Pending');
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>
                                <td>
                                    <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['id']); ?>" class="bi bi-info-square"></a>
                                   
                                    <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                    <select name="assign_to" class="form-control form-control-sm d-inline" style="width: 150px;">
                                        <?php $all_it_personnel = $UserController->getITPersonnel($ticket['campus_id']);
                                        foreach ($all_it_personnel as $person): ?>
                                            <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['first_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="assign" class="btn btn-primary btn-sm">Assign</button>
                                </form>

                                   
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No tickets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="controllers/functions.js"></script>

    <script>
        $(document).ready(function() {
            const selectedMainCategory = "<?php echo $selected_main_category; ?>";
            const selectedSubCategory = "<?php echo $selected_sub_category; ?>";

            if (selectedMainCategory) {
                fetchSubCategories('AdminTickets.php', selectedMainCategory, '#sub_category', selectedSubCategory);
            }

            $('#main_category').change(function() {
                const mainCategoryId = $(this).val();
                fetchSubCategories('AdminTickets.php', mainCategoryId, '#sub_category', '');
            });

            // Initialize DataTable
            $('#ticketsTable').DataTable({
                "pagingType": "full_numbers",
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
