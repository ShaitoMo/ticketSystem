<?php
require_once 'controllers/UserController.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$UserController = new UserController();
$user_id = $_SESSION['user_id']; // Make sure the user ID is available in the session
$requests = $UserController->getUserRequests($user_id, 'pending');
$archived = $UserController->getUserRequests($user_id, 'archived');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve']) || isset($_POST['decline'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
        $action = isset($_POST['approve']) ? 'approve' : 'decline';

        if ($id > 0) {
            // Call the function to approve or decline the request
            if ($action === 'approve') {
                $result = $UserController->updateRequestStatus($id, 'Approved'); // Ensure this method is implemented in ITController
            } else {
                $result = $UserController->updateRequestStatus($id, 'Rejected'); // Ensure this method is implemented in ITController
            }

            // Redirect to the same page to refresh the data
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Requests</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        /* Custom styles */
        .dashboard-wrapper {
            padding: 20px;
        }

        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .request-list .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .request-list .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 0.5em 0.75em;
        }

        .table thead th {
            background-color: #007bff;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }

        .approval-form .btn-group button {
            margin-left: 5px;
        }
        .ajax-loader {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'?>
    <div class="container mt-4 dashboard-wrapper">
        <!-- Section: Requests List -->
        <div class="ajax-loader">
            <img src="loader.gif" alt="Loading...">
        </div>
        <div class="section-title mb-4">
            <h2><i class="fas fa-list"></i> Requests List</h2>
        </div>
        <div class="request-list-wrapper">
            <?php if (!empty($requests)): ?>
                <div class="request-list">
                <?php foreach ($requests as $request): ?>
                <div class="card mb-3 shadow-sm rounded">
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
                                <i class="fas fa-user"></i> Requested By: <?php echo htmlspecialchars($UserController->getUserNameById($request['requested_by']), ENT_QUOTES, 'UTF-8'); ?> |
                                <i class="fas fa-calendar-alt"></i> Created At: <?php echo htmlspecialchars($request['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </small>
                            <form method="post" class="approval-form mb-0 ajax-form" data-id="<?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="btn-group" role="group">
                                    <button type="submit" name="approve" value="approve" class="btn btn-sm btn-success shadow-sm approve-btn">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button type="submit" name="decline" value="decline" class="btn btn-sm btn-danger shadow-sm decline-btn">
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

        <!-- Section: Archived Requests -->
        <div class="section-title mt-5 mb-4">
            <h2><i class="fas fa-archive"></i> Archived Requests</h2>
        </div>
        <div class="table-responsive">
    <table id="archived-requests-table" class="table table-striped table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Ticket ID</th>
                <th>Requested By</th>

                <th>Description</th>
                <th>Status</th>
                <th>Created On</th>
                <th>Last Updated</th>

            </tr>
        </thead>
        <tbody>
            <?php if (!empty($archived)): ?>
                <?php foreach ($archived as $ticket): ?>
                    <tr class="<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                        <td><?php echo htmlspecialchars($ticket['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                                <a href="ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($ticket['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>

                              <td><?php echo htmlspecialchars($UserController->getUserNameById($ticket['requested_by']), ENT_QUOTES, 'UTF-8'); ?></td>
                       
                        <td><?php echo htmlspecialchars($ticket['request_description'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="<?php echo 'status-' . strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?>">
                            <?php echo htmlspecialchars($ticket['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($ticket['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($ticket['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No archived requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#archived-requests-table').DataTable({
            "paging": true, // Enable pagination
            "searching": true, // Enable search
            "info": false, // Hide info (optional)
            "lengthChange": false, // Disable the ability to change the number of records per page
            "pageLength": 10, // Set default number of rows per page
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search requests...",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            }
        });
        ('.approval-form button').click(function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            var action = $(this).attr('name');
            var requestId = form.find('input[name="id"]').val();

            $.ajax({
                type: 'POST',
                url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                data: {
                    id: requestId,
                    [action]: action // Dynamic action field
                },
                beforeSend: function() {
                    $('.ajax-loader').show(); // Show loader
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        form.closest('.card').find('.badge').removeClass('bg-warning text-dark bg-danger').addClass(action === 'approve' ? 'bg-success' : 'bg-danger').text(data.status);
                    }
                },
                complete: function() {
                    $('.ajax-loader').hide(); // Hide loader
                }
            });
        });
    });
    
</script>
</body>


</html>
