<?php
require_once 'controllers/UserController.php';
session_start();

$user_id = $_SESSION['user_id'];

// Handle AJAX request to mark notifications as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_as_read') {
    $User = new UserController();
    $User->MarkAsRead($user_id);
    echo json_encode(['status' => 'success']);
    exit();
}

$User = new UserController();
$notifications = $User->getNotifications($user_id, null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .notification-item {
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .notification-item:last-child {
            margin-bottom: 0;
        }
        .notification-item.read {
            background-color: #f9f9f9;
        }
        .notification-item.unread {
            background-color: #e2e3e5;
        }
        .notification-item .title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        .notification-item .message {
            margin-bottom: 10px;
        }
        .notification-item .footer {
            font-size: 0.875rem;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
        }
        .notification-item .type {
            color: #495057;
        }
        .no-notifications {
            text-align: center;
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <div class="container mt-5">
        <h1 class="mb-4">Notifications</h1>
        <?php if (empty($notifications)) { ?>
            <div class="no-notifications">
                <p>No notifications available.</p>
            </div>
        <?php } else { ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification) { ?>
                    <div class="notification-item list-group-item <?php echo $notification['status'] == 1 ? 'read' : 'unread'; ?>">
                        <div class="title">
                            <?php echo htmlspecialchars($notification['title']); ?>
                        </div>
                        <div class="message">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                        <div class="footer">
                            <div class="type">
                                via <?php echo htmlspecialchars($notification['type']); ?>
                            </div>
                            <div class="timestamp">
                                <?php echo htmlspecialchars($notification['created_at']); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // AJAX request to mark notifications as read
            $.ajax({
                url: '', // Current page URL
                type: 'POST',
                data: { action: 'mark_as_read' },
                success: function(response) {
                    // Optionally handle success if needed
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', status, error);
                }
            });
        });

        // Optionally, you can also send the AJAX request when the user is about to leave the page
        window.addEventListener('beforeunload', function() {
            navigator.sendBeacon('', new URLSearchParams({ action: 'mark_as_read' }));
        });
    </script>
</body>
</html>
