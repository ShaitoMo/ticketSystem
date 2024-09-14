<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'];

require_once 'controllers/ITController.php';
$ITController = new ITController();

// Fetch all sub-tasks assigned to the logged-in user
$allTasks = $ITController->getSubTasksByAssignedTo($user_id);

// Separate tasks into "In Progress" and "Archived"
$inProgressTasks = array_filter($allTasks, function($task) {
    return $task['status'] === 'In Progress';
});
$archivedTasks = array_filter($allTasks, function($task) {
    return $task['status'] === 'Completed' || $task['status'] === 'Cancelled';
});

// If the form is submitted, update the sub-task status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sub_task_id = $_POST['sub_task_id'];
    $status = $_POST['status'];
    $ITController->updateSubTaskStatus($sub_task_id, $status);
    
    // Reload the tasks after the update
    $allTasks = $ITController->getSubTasksByAssignedTo($user_id);
    $inProgressTasks = array_filter($allTasks, function($task) {
        return $task['status'] === 'In Progress';
    });
    $archivedTasks = array_filter($allTasks, function($task) {
        return $task['status'] === 'Completed' || $task['status'] === 'Cancelled';
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sub-Tasks</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css"> <!-- Font Awesome -->
    <style>
        .sub-task-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        .sub-task-header {
            font-weight: bold;
            font-size: 1.25rem;
        }
        .badge-status {
            font-size: 0.9rem;
        }
        .badge-status.in-progress {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-status.completed {
            background-color: #28a745;
        }
        .badge-status.cancelled {
            background-color: #dc3545;
        }
        .created-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .btn-group-status {
            display: inline-flex;
        }
        .btn-group-status .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0;
        }
        .ticket-id {
            font-size: 0.85rem;
            color: #007bff;
        }
        .comment-icon {
            color: #007bff;
            font-size: 1.25rem;
            margin-left: 0.5rem;
        }
        .comment-icon:hover {
            color: #0056b3;
        }
        .archive-icon {
            color: #6c757d;
            font-size: 1.25rem;
            margin-left: 0.5rem;
        }
        .archive-icon:hover {
            color: #495057;
        }
        .section-header {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .section-description {
            font-size: 1rem;
            color: #6c757d;
        }
        .table-icon {
            font-size: 1.1rem;
            color: #007bff;
        }
        .table-icon:hover {
            color: #0056b3;
        }
        table {
            text-align: center;
        }

    </style>
</head>
<body>
    <?php include_once 'header.php'?>
<div class="container mt-4">
    <h2 class="section-header">My Sub-Tasks</h2>
    <p class="section-description">
        Here you can view and manage your sub-tasks. Use the buttons below each task to update its status.
    </p>

    <!-- In Progress Tasks -->
    <h3 class="mt-4">
        <i class="fas fa-hourglass-half"></i> In Progress
    </h3>
    <?php if (!empty($inProgressTasks)): ?>
        <div class="row">
            <?php foreach ($inProgressTasks as $task): ?>
                <div class="col-md-6">
                    <div class="sub-task-card">
                        <div class="sub-task-header d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($task['sub_task_description']); ?>
                            <span class="badge badge-status in-progress">
                                <?php echo htmlspecialchars($task['status']); ?>
                            </span>
                        </div>
                        <p class="ticket-id">Ticket ID: 
                            <a href="ticketDetails.php?ticket_id=<?php echo htmlspecialchars($task['ticket_id']); ?>">
                                <?php echo htmlspecialchars($task['ticket_id']); ?>
                            </a>
                            <a href="comments.php?sub_task_id=<?php echo htmlspecialchars($task['id']); ?>" class="comment-icon">
                                <i class="fas fa-comments"></i>
                            </a>
                        </p>
                        <p class="created-time">Created on: <?php echo date("F j, Y, g:i a", strtotime($task['created_at'])); ?></p>
                        
                        <!-- Button group for status update -->
                        <form method="POST" action="">
                            <input type="hidden" name="sub_task_id" value="<?php echo $task['id']; ?>">
                            <div class="btn-group-status">
                                <button type="submit" name="status" value="Completed" class="btn btn-success" <?php echo $task['status'] === 'Completed' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-check"></i> Completed
                                </button>
                                <button type="submit" name="status" value="Cancelled" class="btn btn-danger" <?php echo $task['status'] === 'Cancelled' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-2" role="alert">
            No sub-tasks available.
        </div>
    <?php endif; ?>

    <!-- Archived Tasks -->
    <h3 class="mt-4">
        <i class="fas fa-archive"></i> Archived
    </h3>
    <?php if (!empty($archivedTasks)): ?>
        <table id='archives' class="table table-bordered mt-3">
            <thead>
                <tr>
                <th>ID</th>
                    <th>Task</th>
                    <th>Ticket ID</th>
                    <th>Status</th>
                    <th>Created On</th>
                    <th>Last Updated</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivedTasks as $task): ?>
                    <tr>
                    <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['sub_task_description']); ?></td>
                        <td>
                            <a href="ticketDetails.php?ticket_id=<?php echo htmlspecialchars($task['ticket_id']); ?>">
                                <?php echo htmlspecialchars($task['ticket_id']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-status 
                                <?php 
                                    echo $task['status'] === 'Completed' ? 'completed' : 
                                         'cancelled'; 
                                ?>">
                                <?php echo htmlspecialchars($task['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($task['created_at'])); ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($task['updated_at'])); ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info mt-2" role="alert">
            No archived sub-tasks available.
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script>
 $('#archives').DataTable()
</script>
</body>
</html>
