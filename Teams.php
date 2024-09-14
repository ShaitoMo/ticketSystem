<?php
session_start();

require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';

$user = new UserController();
$IT = new ITController();
$admin = new AdminController();

$roles = $admin->getitRoles();
$itPersonnel = $user->getITPersonnel(1);




if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['ajax'])) {
    if ($_GET['ajax'] == 'getUserRoles') {
        $user_id = $_GET['user_id'];
        $userRoles = $admin->getUserRoles($user_id);
        echo json_encode($userRoles);
        exit();
    } elseif ($_GET['ajax'] == 'getTeamMembers') {
        $team_id = $_GET['team_id'];
        $teamMembers = $admin->getTeamMembers($team_id);
        echo json_encode($teamMembers);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_roles'])) {
        $user_id = $_POST['user_id'];
        $role_ids = $_POST['role_ids'];
        $admin->updateitRole($user_id, $role_ids);
        header("Location: Teams.php");
        exit();
    } elseif (isset($_POST['update_team_leader'])) {
        $team_id = $_POST['teamId'];
        $teamLeaderId = $_POST['teamLeaderId'];
        echo $team_id;

        if ($teamLeaderId !== '') {
            $admin->updateTeamLeader($team_id, $teamLeaderId);
        }

        header("Location: Teams.php");
        exit();
    }
}


$categories = $admin->getMainCategories();




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Personnel and Ticket Statistics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <style>
        .team-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .team-title a {
            color: #007bff;
        }
        .table-responsive {
            margin-bottom: 2rem;
        }
        .team-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.view-team-icon {
    margin-left: 0.5rem;
    color: #007bff;
    font-size: 1.25rem;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}

.view-team-icon:hover {
    color: #0056b3;
}

    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
    <?php foreach ($categories as $Team): ?>
        <div class="team-title">
    <span><?php echo htmlspecialchars($Team['name']); ?></span>
    <a href="MyTeam.php?teamId=<?php echo htmlspecialchars($Team['id']); ?>" class="view-team-icon">
        <i class="bi bi-box-arrow-up-right"></i>
    </a>
</div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th colspan="8">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>
                                    <?php 
                                        $teamLeader = $admin->getTeamLeader($Team['id']);
                                        if (!empty($teamLeader)) {
                                            $teamLeaderId = $teamLeader[0]['user_id'];
                                            $teamLeaderName = $user->getUserNameById($teamLeaderId); 
                                            echo 'Team Leader: ' . htmlspecialchars($teamLeaderName);
                                        } else {
                                            echo 'No Team Leader';
                                        }
                                    ?>
                                </span>
                                <button type="button" class="btn btn-link" onclick="showEditTeamLeaderModal(<?php echo $Team['id']; ?>)">
                                    <i class="bi bi-pencil-square"></i> Edit Leader
                                </button>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>All Tickets</th>
                        <th>In Progress Tickets</th>
                        <th>Closed Tickets</th>
                        <th>Role</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php  
                        $member = $admin->getTeamMembers($Team['id']);
                        foreach ($member as $person): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($person['id']); ?></td>
                                <td><?php echo htmlspecialchars($person['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($person['email']); ?></td>
                                <td><?php echo $IT->countTickets('', '', $person['id'], ''); ?></td>
                                <td><?php echo $IT->countITActiveTickets($person['id']); ?></td>
                                <td><?php echo $IT->countTickets('Closed', '', $person['id'], ''); ?></td>
                                <td>
                                    <?php
                                        $userRoles = $admin->getUserRoles($person['id']);
                                        if (empty($userRoles)) {
                                            echo 'none';
                                        } else {
                                            $roleNames = [];
                                            foreach ($userRoles as $roleId) {
                                                $roleName = $admin->getRoleNameById($roleId);
                                                if ($roleName) {
                                                    $roleNames[] = htmlspecialchars($roleName);
                                                }
                                            }
                                            echo implode('<br>', $roleNames);
                                        }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                                        <div class="btn-group mr-2" role="group" aria-label="First group">
                                            <button type="button" class="btn btn-info" onclick="showUserDetails(<?php echo htmlspecialchars($person['id']); ?>)">
                                                <i class="bi bi-info-circle"></i> Info
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="showEditRolesModal(<?php echo htmlspecialchars($person['id']); ?>)">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
    </div>

    <!-- Edit Team Leader Modal -->
    <div class="modal fade" id="editTeamLeaderModal" tabindex="-1" aria-labelledby="editTeamLeaderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeamLeaderModalLabel">Edit Team Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTeamLeaderForm" method="POST">
                    <input type="hidden" name="teamId" id="teamIdInput">
                    <div class="mb-3">
                        <label for="teamLeaderSelect" class="form-label">Select Team Leader</label>
                        <select class="form-select" id="teamLeaderSelect" name="teamLeaderId" required>
                        <option value="" disabled selected>Choose a team leader</option>

                            <?php foreach ($itPersonnel as $person): ?>
                                <option value="<?= htmlspecialchars($person['id']) ?>">
                                    <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_team_leader" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="user-details-content">
                    <!-- User details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Roles Modal -->
    <div class="modal fade" id="editRolesModal" tabindex="-1" role="dialog" aria-labelledby="editRolesModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRolesModalLabel">Edit User Roles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRolesForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="form-group">
                            <label for="role_ids">Roles</label>
                            <div id="roleCheckboxes" class="form-check">
                                <?php foreach ($roles as $role): ?>
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox" name="role_ids[]" id="role-<?php echo htmlspecialchars($role['id']); ?>" value="<?php echo htmlspecialchars($role['id']); ?>" 
                                            <?php echo in_array($role['id'], array_column($userRoles, 'role_id')) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="role-<?php echo htmlspecialchars($role['id']); ?>">
                                            <?php echo htmlspecialchars($role['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" onclick="confirmRoleChange('editRolesForm')">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let formToSubmit;

     
        $(document).ready(function () {
            $('#userDetailsModal').on('hidden.bs.modal', function () {
                $('#user-details-content').html('');
            });

            $('#editRolesModal').on('hidden.bs.modal', function () {
                $('#roleCheckboxes input').prop('checked', false);
            });
        });

        function showUserDetails(userId) {
            $('#userDetailsModal').modal('show');
            $.ajax({
                url: 'get_user_details.php',
                type: 'GET',
                data: { user_id: userId },
                success: function (data) {
                    $('#user-details-content').html(data);
                }
            });
        }
        function showEditTeamLeaderModal(teamId) {
    document.getElementById('teamIdInput').value = teamId;

   

    $('#editTeamLeaderModal').modal('show');
}

        function showEditRolesModal(userId) {
       
            $('#editUserId').val(userId);

           
            $('#roleCheckboxes input').prop('checked', false);

            $.ajax({
                url: 'Teams.php', 
                type: 'GET',
                dataType: 'json',
                data: {
                    ajax: 'getUserRoles',
                    user_id: userId
                },
                success: function(roleIds) {
               

                    roleIds.forEach(function(roleId) {
                        $('#role-' + roleId).prop('checked', true);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching user roles:', error);
                    console.log('Response Text:', xhr.responseText);
                }
            });



            $('#editRolesModal').modal('show');
        }

      

    </script>
</body>
</html>
