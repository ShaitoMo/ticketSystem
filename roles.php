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
    <style>
        body {
            background-color: #f8f9fa; /* Light background */
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
        }
        h2 {
            text-align: center;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-size: 1rem;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
        .btn-group {
            margin-bottom: 20px;
            float: right; /* Align buttons to the right */
        }
        table {
            margin-top: 20px;
        }
        thead {
            background-color: #343a40; /* Dark header */
            color: white;
        }
        tbody tr:hover {
            background-color: #f1f1f1; /* Highlight rows on hover */
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .form-control {
            border-radius: 5px;
        }
        .note {
            font-size: 0.9rem;
            color: #dc3545; /* Red color for the note */
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            .btn-group {
                float: none;
                text-align: center;
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Roles List</h2>
        
        <!-- Note for main campus -->
        <div class="note">
            Note: These actions are only applicable to the main campus.
        </div>

        <!-- Button Group for Assign Teams and Categories -->
        <div class="btn-group" role="group" aria-label="Actions">
            <a href="teams.php" class="btn btn-info">Assign Teams</a>
            <a href="categories.php" class="btn btn-secondary">Assign Categories</a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRoleModal">
                Add Role
            </button>
        </div>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th># Active Tickets</th>
                    <th>Users</th>
                    <th>Categories</th> 
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roles)): ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($role['id']); ?></td>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td><?php echo htmlspecialchars($Admin->countTicketsByItRole($role['id'])); ?></td>
                            <td>
                                <?php
                                    $userNames = $Admin->getUserNamesByRoleId($role['id']);
                                    if (!empty($userNames)):
                                        foreach ($userNames as $user):
                                            echo htmlspecialchars($user['full_name']) . '<br>';
                                        endforeach;
                                    else:
                                        echo 'No users assigned';
                                    endif;
                                ?>
                            </td>
                            <td>
                                <?php
                                    $categoryNames = $Admin->getCategoryNamesByRoleId($role['id']);
                                    if (!empty($categoryNames)):
                                        foreach ($categoryNames as $categoryName):
                                            echo htmlspecialchars($categoryName) . '<br>';
                                        endforeach;
                                    else:
                                        echo 'No categories linked';
                                    endif;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No roles found</td>
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
