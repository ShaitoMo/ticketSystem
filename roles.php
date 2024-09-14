<?php
session_start();
require_once 'controllers/AdminController.php';

$Admin = new AdminController();
$roles = $Admin->getitRoles();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['roleName'])) {
        $roleName = $_POST['roleName'];
        $Admin->addRole($roleName);
        header('Location: roles.php'); // Redirect after adding the role
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles List</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Roles List</h2>
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addRoleModal">
            Add Role
        </button>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th># Teams</th>
                    <th># Tickets</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roles)): ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($role['id']); ?></td>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td><?php echo htmlspecialchars($Admin->countUsersByRole($role['id'])); ?></td>
                            <td><?php echo htmlspecialchars($Admin->countTicketsByItRole($role['id'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No roles found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="roleName">Role Name</label>
                            <input type="text" class="form-control" id="roleName" name="roleName" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
