<?php
require_once 'controllers/AdminController.php';
session_start(); 
$admin = new AdminController();
$role = $_SESSION['role'];
$isITAdmin = ($role == 'IT Administrator');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isITAdmin) {
    $support_email = $_POST['support_email'];
    $default_ticket_priority = $_POST['default_ticket_priority'];
    $notification_mode = $_POST['notification_mode'];
    $ticket_auto_close = $_POST['ticket_auto_close'];
    $max_attachment_size = $_POST['max_attachment_size'];
    $ticket_assignment = $_POST['ticket_assignment'];

    $admin->update('support_email', $support_email);
    $admin->update('default_ticket_priority', $default_ticket_priority);
    $admin->update('notification_mode', $notification_mode);
    $admin->update('ticket_auto_close', $ticket_auto_close);
    $admin->update('max_attachment_size', $max_attachment_size);
    $admin->update('ticket_assignment', $ticket_assignment);

    
    header('Location: settings.php');
    exit();
}


$settings = $admin->getSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <style>
        .container {
            max-width: 800px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            margin-top: 10px;
        }
        .form-group i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include_once("header.php"); ?>
    <div class="container">
        <h2 class="mb-4">Settings</h2>
        <form id="settingsForm" method="POST" action="">
            <!-- Support Email -->
            <div class="form-group">
                <label for="support_email"><i class="fas fa-envelope"></i>Support Email</label>
                <input type="email" class="form-control" id="support_email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email']); ?>" <?php echo !$isITAdmin ? 'readonly' : ''; ?> required>
            </div>

            <!-- Default Ticket Priority -->
            <div class="form-group">
                <label for="default_ticket_priority"><i class="fas fa-flag"></i>Default Ticket Priority</label>
                <select class="form-control" id="default_ticket_priority" name="default_ticket_priority" <?php echo !$isITAdmin ? 'disabled' : ''; ?> required>
                    <option value="Low" <?php echo ($settings['default_ticket_priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo ($settings['default_ticket_priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo ($settings['default_ticket_priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                </select>
            </div>

            <!-- Notification Mode -->
            <div class="form-group">
                <label for="notification_mode"><i class="fas fa-bell"></i>Notification Mode</label>
                <select class="form-control" id="notification_mode" name="notification_mode" <?php echo !$isITAdmin ? 'disabled' : ''; ?> required>
                    <option value="Email" <?php echo ($settings['notification_mode'] == 'Email') ? 'selected' : ''; ?>>Email</option>
                    <option value="SMS" <?php echo ($settings['notification_mode'] == 'SMS') ? 'selected' : ''; ?>>SMS</option>
                </select>
            </div>

            <!-- Ticket Auto Close Days -->
            <div class="form-group">
                <label for="ticket_auto_close_days"><i class="fas fa-calendar-day"></i>Ticket Auto Close Days</label>
                <input type="number" class="form-control" id="ticket_auto_close_days" name="ticket_auto_close" min="1" value="<?php echo htmlspecialchars($settings['ticket_auto_close']); ?>" <?php echo !$isITAdmin ? 'readonly' : ''; ?> required>
            </div>

            <!-- Max Attachment Size -->
            <div class="form-group">
                <label for="max_attachment_size"><i class="fas fa-file-upload"></i>Max Attachment Size</label>
                <select class="form-control" id="max_attachment_size" name="max_attachment_size" <?php echo !$isITAdmin ? 'disabled' : ''; ?> required>
                    <option value="1048576" <?php echo ($settings['max_attachment_size'] == '1048576') ? 'selected' : ''; ?>>1 MB</option>
                    <option value="5242880" <?php echo ($settings['max_attachment_size'] == '5242880') ? 'selected' : ''; ?>>5 MB</option>
                    <option value="10485760" <?php echo ($settings['max_attachment_size'] == '10485760') ? 'selected' : ''; ?>>10 MB</option>
                    <option value="20971520" <?php echo ($settings['max_attachment_size'] == '20971520') ? 'selected' : ''; ?>>20 MB</option>
                </select>
            </div>

            <!-- Ticket Assignment -->
            <div class="form-group">
                <label for="ticket_assignment"><i class="fas fa-random"></i>Ticket Assignment</label>
                <select class="form-control" id="ticket_assignment" name="ticket_assignment" <?php echo !$isITAdmin ? 'disabled' : ''; ?> required>
                    <option value="Manual" <?php echo ($settings['ticket_assignment'] == 'Manual') ? 'selected' : ''; ?>>Manual</option>
                    <option value="Auto" <?php echo ($settings['ticket_assignment'] == 'Auto') ? 'selected' : ''; ?>>Auto</option>
                    <option value="Locked" <?php echo ($settings['ticket_assignment'] == 'Locked') ? 'selected' : ''; ?>>Locked</option>
                </select>
            </div>

            <?php if ($isITAdmin): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModal">Save Settings</button>
            <?php endif; ?>
        </form>
        <?php if (!$isITAdmin): ?>
            <p>You do not have permission to edit settings. Contact an IT Administrator for assistance.</p>
        <?php endif; ?>
    </div>

    <!-- Confirmation Modal -->
    <?php if ($isITAdmin): ?>
        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel">Confirm Changes</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to save these changes?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSave">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        <?php if ($isITAdmin): ?>
            // Handle form submission with confirmation modal
            $('#confirmSave').on('click', function() {
                $('#settingsForm').submit();
            });
        <?php endif; ?>
    </script>
</body>
</html>
