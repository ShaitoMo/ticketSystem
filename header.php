<?php
require_once 'C:\xampp\htdocs\ticketSystem\controllers\UserController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$user = new UserController();
$name = $user->getUserNameById($user_id);
$unread_notifications = $user->countNotifications($user_id, '0');


if (isset($_POST['action']) && $_POST['action'] == 'fetch_notifications') {
    header('Content-Type: application/json'); 
    $notifications = $user->getNotifications($user_id,'0'); 
    echo json_encode([
        'count' => $unread_notifications,
        'notifications' => $notifications
    ]);
    exit;
} 


if ($user_role == 'IT Administrator') {
    $home_url = 'AdminDashboard.php';
} elseif ($user_role == 'IT Personnel' ||  $user_role == 'IT Coordinator'       ||$user_role == 'Sub-Admin') {
    $home_url = 'ItDashboard.php';
} else {
    // Redirect to a page or show an error message for unauthorized roles
    // You could redirect to an error page, a different dashboard, or back to login
    $home_url ='' ;// Ensure you have an unauthorized page
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blue Navbar with Sidebar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

            .custom-navbar {
                background-color: #007BFF;
                padding-bottom: 5px;
                position: fixed; /* This makes the navbar fixed at the top */
                top: 0; /* Align it to the top */
                left: 0; /* Align it to the left */
                width: 100%; /* Ensure it spans the full width of the page */
                z-index: 1000; /* Make sure it stays above other elements */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Add a subtle shadow for depth */
            }

            .custom-navbar .navbar-brand,
            .custom-navbar .nav-link {
                color: #fff;
            }

            .custom-navbar .nav-link:hover {
                color: #D3D3D3;
            }

            .custom-navbar .user-info {
                color: #fff;
                font-weight: bold;
            }

            .custom-navbar .offcanvas-header {
                background-color: #007BFF;
                color: #fff;
            }

            .custom-navbar .offcanvas-body .nav-link {
                color: #007BFF;
            }

            .custom-navbar .offcanvas-body .nav-link:hover {
                color: #0056b3;
            }

            /* Add some padding to the body content to account for the fixed navbar */
            body {
                padding-top: 70px; /* Adjust based on the height of your navbar */
            }

            /* Offcanvas custom width */
            .custom-offcanvas {
                width: 250px !important;
            }

            @media (min-width: 576px) {
                .custom-offcanvas {
                    width: 250px !important;
                }
            }

            .notifications-wrapper {
                position: relative;
            }

            .notification-list {
                display: none;
                position: fixed; /* Ensure notifications stay fixed with the navbar */
                background-color: #fff;
                border: 1px solid #ddd;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                max-height: 300px;
                overflow-y: auto;
                width: 250px;
                border-radius: 8px;
                top: 50px; /* Adjust this value as needed to place it below the navbar */
                right: 50px; /* Adjust to align with the notification icon */
                transform: translateY(10px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                opacity: 0;
            }

            .notification-list.show {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            .notification-item {
                padding: 8px 12px;
                border-bottom: 1px solid #ddd;
                cursor: pointer;
                transition: background-color 0.2s ease;
                font-size: 0.875rem;
            }

            .notification-item:last-child {
                border-bottom: none;
            }

            .notification-item:hover {
                background-color: #f0f0f0;
            }

            .notification-item.read {
                background-color: #f9f9f9;
            }

            .notification-item.unread {
                background-color: #e2e3e5;
            }


    </style>
</head>
<body>

<div class="custom-navbar">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <button class="btn btn-primary me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="d-flex align-items-center flex-grow-1">
                <a class="navbar-brand me-3" href="<?php echo htmlspecialchars($home_url); ?>">
                    <i class="fas fa-home"></i>
                </a>
                <span class="navbar-text me-3 user-info">
                    <?php echo htmlspecialchars($name); ?> | <?php echo htmlspecialchars($user_role); ?>
                </span>

                <div class="d-flex ms-auto align-items-center position-relative notifications-wrapper">
                    <a class="nav-link position-relative me-3" href="View_Notifications.php" id="notifications-icon">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-badge"></span>
                    </a>
                    <div class="notification-list" id="notification-list">
                        <!-- Notifications will be dynamically loaded here -->
                    </div>
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start custom-offcanvas" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="nav flex-column">
                <?php if ($user_role == 'IT Administrator') { ?>
                    <a class="nav-link" href="AdminDashboard.php"><i class="fas fa-home"></i> Home</a>
                    <a class="nav-link dropdown-toggle" href="#" id="ticketsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ticket-alt"></i> Tickets
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="ticketsDropdown">
                        <li><a class="dropdown-item" href="#new"><i class="fas fa-plus-circle"></i> New</a></li>
                        <li><a class="dropdown-item" href="#inprogress"><i class="fas fa-spinner"></i> In Progress</a></li>
                        <li><a class="dropdown-item" href="#resolved"><i class="fas fa-check-circle"></i> Resolved</a></li>
                        <li><a class="dropdown-item" href="#closed"><i class="fas fa-times-circle"></i> Closed</a></li>
                    </ul>
                    <a class="nav-link" href="categories.php"><i class="fas fa-layer-group"></i> Categories</a>
                    <a class="nav-link" href="roles.php"><i class="fas fa-users-cog"></i> Roles</a>
                    <a class="nav-link" href="Teams.php"><i class="fas fa-user-shield"></i> IT Team</a>
                    <a class="nav-link" href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
                    
                <?php } elseif ($user_role == 'IT Personnel') { ?>
                    <a class="nav-link" href="AdminDashboard.php"><i class="fas fa-home"></i> Home</a>
                    <a class="nav-link" href="allTickets.php"><i class="fas fa-ticket-alt"></i> All Tickets</a>
                    <a class="nav-link" href="myTickets.php"><i class="fas fa-user-tag"></i> My Tickets</a>
                    <a class="nav-link" href="newTicket.php"><i class="fas fa-plus-circle"></i> New Ticket</a>
                    <a class="nav-link" href="inProgressTickets.php"><i class="fas fa-spinner"></i> In Progress</a>
                    <a class="nav-link" href="resolvedTickets.php"><i class="fas fa-check-circle"></i> Resolved</a>
                    <a class="nav-link" href="archivedTickets.php"><i class="fas fa-archive"></i> Archived</a>
                <?php } ?>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="controllers/functions.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var notificationsIcon = document.getElementById('notifications-icon');
    var notificationList = document.getElementById('notification-list');
    var notificationBadge = document.getElementById('notification-badge');

   

    fetchNotifications();

    if (notificationsIcon && notificationList) {
        notificationsIcon.addEventListener('mouseover', function() {
            if (notificationList.children.length > 0) {
                notificationList.classList.add('show');
            }
        });

        notificationsIcon.addEventListener('mouseout', function() {
            notificationList.classList.remove('show');
        });
    }

    setInterval(fetchNotifications, 60000); // Fetch notifications every 60 seconds
});
</script>
</body>
</html>
