<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'controllers/ITController.php';
$ITController = new ITController();
$user_id = $_SESSION['user_id'];
$tickets = $ITController->ForwardedTicketsByUser($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Forwarded Tickets</title>

    <!-- Bootstrap 4 CDN -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            padding: 0;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table {
            max-width: 95%;
            table-layout: auto;
        }

        .ticket-header {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .ticket-description {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
        }

        .ticket-row {
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .ticket-row:hover {
            background-color: #f1f1f1;
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .no-tickets {
            font-size: 1.5rem;
            color: #666;
            margin-top: 20px;
            text-align: center;
        }

        /* Full-width table adjustments */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 1rem;
        }

        /* Add space between navbar and title */
        .content-section {
            margin-top: 20px; /* Adjust the value to increase or decrease space */
        }
    </style>
</head>
<body>
<?php include_once('header.php');?>

<div class="container-fluid content-section">
    <div class="row justify-content-between align-items-center">
        <div class="col">
            <h1 class="ticket-header"><i class="material-icons" style="vertical-align: middle;">forward</i> My Forwarded Tickets</h1>
            <p class="ticket-description">Here you can track all the tickets you have forwarded. Use the search functionality to quickly find specific tickets.</p>
        </div>
    </div>

    <div class="table-responsive">
        <?php if (!empty($tickets)) : ?>
            <table id="ticketTable" class="table table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>From Campus</th>
                        <th>To Campus</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Forwarded On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket) : ?>
                        <tr class="ticket-row" onclick="window.location.href='ItViewTicket.php?id=<?php echo htmlspecialchars($ticket['ticket_id'], ENT_QUOTES, 'UTF-8'); ?>';">
                            <td><?php echo htmlspecialchars($ticket['ticket_id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['from_campus']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['to_campus']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $ticket['status'] == 'Resolved' ? 'success' : 'warning'; ?> status-badge">
                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['priority']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($ticket['forwarded_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-tickets">
                <i class="material-icons" style="vertical-align: middle;">info</i> No forwarded tickets found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- jQuery, Bootstrap JS, and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#ticketTable').DataTable({
            paging: true,
            searching: true,
            info: false,
            lengthChange: false,
            ordering: true
        });
    });
</script>

</body>
</html>
