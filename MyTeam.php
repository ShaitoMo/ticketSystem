<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$ITController = new ITController();
$AdminController = new AdminController();
$UserController = new UserController();

$user_id = $_SESSION['user_id'];

// Check if teamId is provided in the URL query parameters
// Check if 'teamId' is present in the URL
if (isset($_GET['teamId']) && !empty($_GET['teamId'])) {
    // Use the 'teamId' from the URL
    $teamId = (int)$_GET['teamId'];
    
    // Fetch team details by 'teamId'
    $teamName = $ITController->getCategoryNameById($teamId);
} else {
    // Fetch team details for the logged-in user
    $team = $ITController->getUserTeam($user_id);
    $teamId = $team['id'];
    $teamName = $team['name'];
}




$TeamUsers = $AdminController->getTeamMembers($teamId);
$TeamRoles = $ITController->getTeamRoles($teamId);
$TeamTickets = $ITController->getTeamTickets($teamId, '', '', 'assigned', '', '');
$TeamAvg = $ITController->AvgResolutionByTeam($teamId);

$teamLeader = $AdminController->getTeamLeader($teamId);
$teamLeaderId = $teamLeader[0]['user_id'] ?? null;
$teamLeaderName = $teamLeaderId ? $UserController->getUserNameById($teamLeaderId) : 'No Team Leader';


$resolvedTicketsCount = 0;
$nonResolvedTicketsCount = 0;

foreach ($TeamTickets as $ticket) {
    if ($ticket['status'] === 'Resolved') {
        $resolvedTicketsCount++;
    } else {
        $nonResolvedTicketsCount++;
    }
}

// Initialize arrays to store ticket data
$assignedTickets = array_fill(0, 7, 0);
$resolvedTickets = array_fill(0, 7, 0);
$dates = [];

// Get current date and last 6 days
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Filter tickets and count them
foreach ($TeamTickets as $ticket) {
    // Handle assignment dates
    if (!empty($ticket['assigned_at'])) {
        $assignedDate = date('Y-m-d', strtotime($ticket['assigned_at']));
        if (in_array($assignedDate, $dates)) {
            $index = array_search($assignedDate, $dates);
            $assignedTickets[$index]++;
        }
    }
    
    // Handle resolution dates
    if (!empty($ticket['resolved_at'])) {
        $resolvedDate = date('Y-m-d', strtotime($ticket['resolved_at']));
        if (in_array($resolvedDate, $dates)) {
            $index = array_search($resolvedDate, $dates);
            $resolvedTickets[$index]++;
        }
    }
}

// Create Chart.js data format
$chartData = [
    'labels' => $dates,
    'datasets' => [
        [
            'label' => 'Assigned Tickets',
            'data' => $assignedTickets,
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'borderWidth' => 1,
            'pointBackgroundColor' => 'rgba(75, 192, 192, 1)',
            'pointBorderColor' => '#fff',
            'pointHoverBackgroundColor' => '#fff',
            'pointHoverBorderColor' => 'rgba(75, 192, 192, 1)'
        ],
        [
            'label' => 'Resolved Tickets',
            'data' => $resolvedTickets,
            'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
            'borderColor' => 'rgba(153, 102, 255, 1)',
            'borderWidth' => 1,
            'pointBackgroundColor' => 'rgba(153, 102, 255, 1)',
            'pointBorderColor' => '#fff',
            'pointHoverBackgroundColor' => '#fff',
            'pointHoverBorderColor' => 'rgba(153, 102, 255, 1)'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Overview</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .ticket-link {
            text-decoration: none;
            color: #007bff;
        }
        .ticket-link:hover {
            text-decoration: underline;
            color: #0056b3;
        }
        .chart-container {
            width: 100%;
            height: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .chart-container-pie {
            width: 100%;
            height: 400px; /* Adjusted for better fit */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .team-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .team-section h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .team-section ul {
            list-style-type: none;
            padding: 0;
        }
        .team-section ul li {
            padding: 5px 0;
            font-size: 1rem;
            color: #495057;
        }
        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #007bff;
            color: #fff;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        #resolvedVsNonResolvedChart {
            margin-top: -18px;
            
         
        }

    </style>
</head>
<body>
<?php include_once('header.php'); ?>

    <div class="container mt-5">
        <!-- Page Description -->
     
        <h3 class="display-4"><i class="fas fa-users"></i> Team Overview - <?php echo htmlspecialchars($teamName); ?></h3>
        <p><strong>Team Leader:</strong> <?php echo htmlspecialchars($teamLeaderName); ?></p>

            <p>Welcome to the team overview page. Here you can find key metrics and visualizations related to your team's performance over the past week. Analyze ticket resolution times, compare assigned versus resolved tickets, and review team roles and members.</p>
       

        
        <!-- Charts Section -->
        <div class="row mb-5">
            <!-- Assigned vs Resolved Tickets Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="text-center"><i class="fas fa-chart-line"></i> Assigned vs Resolved Tickets (Last 7 Days)</h3>
                    <canvas id="assignedVsResolvedChart"></canvas>
                </div>
            </div>
            <!-- Resolved vs Non-Resolved Tickets Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h3 class="text-center"><i class="fas fa-chart-pie"></i> Resolved vs Non-Resolved Tickets</h3>
                    <canvas id="resolvedVsNonResolvedChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Roles Section -->
        <div class="team-section mb-4">
            <h3><i class="fas fa-user-shield"></i> Team Roles</h3>
            <ul>
                <?php foreach ($TeamRoles as $role): ?>
                    <li><i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($role['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <p><strong>Average Resolution Time:</strong> <?php echo number_format($TeamAvg / 3600, 2); ?> hours</p>

        <!-- Team Members Table -->
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th><i class="fas fa-user"></i> Name</th>
                    <th><i class="fas fa-id-badge"></i> Role</th>
                    <th><i class="fas fa-envelope"></i> Contact</th>
                    <th><i class="fas fa-clock"></i> Average Resolution</th>
                    <th><i class="fas fa-tasks"></i> Current Tickets</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($TeamUsers as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                        <td>
                            <?php 
                                $roles = $AdminController->getUserRoles($member['id']);
                                $roleNames = [];
                                foreach ($roles as $role) {
                                    $roleNames[] = htmlspecialchars($AdminController->getRoleNameById($role));
                                }
                                echo implode(', ', $roleNames);
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td>
                            <?php echo htmlspecialchars(number_format($ITController->AvgResolutionByUser($member['id']) / 3600, 2)); ?>
                        </td>
                        <td>
                            <?php
                                $tickets = $ITController->getAllTickets('', '', $member['id'],'','','',1);
                                if (!empty($tickets)) {
                                    $ticketLinks = [];
                                    foreach ($tickets as $ticket) {
                                        $ticketLinks[] = '<a href="ItViewTicket.php?id=' . htmlspecialchars($ticket['id']) . '" class="ticket-link">Ticket ' . htmlspecialchars($ticket['id']) . '</a>';
                                    }
                                    echo implode(', ', $ticketLinks);
                                } else {
                                    echo 'No tickets assigned.';
                                }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Chart for Assigned vs Resolved Tickets
        var ctx1 = document.getElementById('assignedVsResolvedChart').getContext('2d');
        var assignedVsResolvedChart = new Chart(ctx1, {
            type: 'line',
            data: <?php echo json_encode($chartData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'category',
                        labels: <?php echo json_encode($dates); ?>,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Tickets'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Chart for Resolved vs Non-Resolved Tickets
        var ctx2 = document.getElementById('resolvedVsNonResolvedChart').getContext('2d');
        var resolvedVsNonResolvedChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Resolved', 'Non-Resolved'],
                datasets: [{
                    label: 'Tickets',
                    data: [<?php echo $resolvedTicketsCount; ?>, <?php echo $nonResolvedTicketsCount; ?>],
                    backgroundColor: ['#007bff', '#6c757d'],
                    borderColor: ['#007bff', '#6c757d'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            }
        });
    </script>
</body>
</html>
