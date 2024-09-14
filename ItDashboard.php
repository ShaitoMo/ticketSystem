<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'controllers/ITController.php';
require_once 'controllers/UserController.php';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$ITController = new ITController();
$UserController = new UserController();
$RecenTickets=$ITController->getRecentTickets($user_id);

$categories = $ITController->getsubCategories();


$isSub=($user_role=='Sub-Admin');
$campus=$UserController->getCampusbyUser($user_id);
$campus_id=$campus['campus_id'];
$isBeirut=($campus_id=='1');

$isLeader= $ITController->isUserLeader($user_id);
$resolvedTicketsCount = 0;
$nonResolvedTicketsCount = 0;

foreach ($RecenTickets as $ticket) {
    if ($ticket['status'] === 'Resolved') {
        $resolvedTicketsCount++;
    } else {
        $nonResolvedTicketsCount++;
    }
}

// Initialize arrays to store ticket data
$assignedTickets = array_fill(0, 14, 0);
$resolvedTickets = array_fill(0, 14, 0);
$dates = [];

// Get current date and last 6 days
for ($i = 13; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Filter tickets and count them
foreach ($RecenTickets as $ticket) {
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
// Initialize arrays to store category data
$categoryNames = [];
$categoryTicketCounts = [];

// Assuming the $categories array has the structure ['id' => category_id, 'name' => category_name]
foreach ($categories as $category) {
    $categoryNames[$category['id']] = $category['name'];
    $categoryTicketCounts[$category['id']] = 0; // Initialize with 0
}

// Count the number of tickets per category
foreach ($RecenTickets as $ticket) {
    $category_id = $ticket['category_id'];
    if (isset($categoryTicketCounts[$category_id])) {
        $categoryTicketCounts[$category_id]++;
    }
}

// Prepare data for the chart
$chartDataByCategory = [
    'labels' => array_values($categoryNames), // Use category names as labels
    'datasets' => [
        [
            'label' => 'Tickets by Category',
            'data' => array_values($categoryTicketCounts),
            'backgroundColor' => [
                '#FF6384', // Solid red
                '#36A2EB', // Solid blue
                '#FFCE56', // Solid yellow
                '#4BC0C0', // Solid teal
                '#9966FF', // Solid purple
                '#FF9F40', // Solid orange
                // Add more solid colors if needed
            ],
            'borderColor' => [
                '#FF6384', // Solid red border
                '#36A2EB', // Solid blue border
                '#FFCE56', // Solid yellow border
                '#4BC0C0', // Solid teal border
                '#9966FF', // Solid purple border
                '#FF9F40', // Solid orange border
                // Add more border colors if needed
            ],
            'borderWidth' => 1
        ]
    ]
];
$statuses = [ "In Progress", "Resolved", "On Hold", "Closed"];
$statusCounts = array_fill_keys($statuses, 0); // Initialize count for each status

// Count tickets by status
foreach ($RecenTickets as $ticket) {
    $status = $ticket['status'];
    if (in_array($status, $statuses)) {
        $statusCounts[$status]++;
    }
}

// Prepare chart data
$chartDataByStatus = [
    'labels' => array_keys($statusCounts),
    'datasets' => [
        [
            'label' => 'Tickets by Status',
            'data' => array_values($statusCounts),
            'backgroundColor' => [
                '#FF6384', // Solid red
                '#36A2EB', // Solid blue
                '#FFCE56', // Solid yellow
                '#4BC0C0', // Solid teal
                '#9966FF', // Solid purple
            ],
            'borderColor' => [
                '#FF6384', // Solid red border
                '#36A2EB', // Solid blue border
                '#FFCE56', // Solid yellow border
                '#4BC0C0', // Solid teal border
                '#9966FF', // Solid purple border
            ],
            'borderWidth' => 1
        ]
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-dashboard .card-custom {
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .admin-dashboard .card-icon {
            font-size: 2rem;
        }

        .admin-dashboard .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .admin-dashboard .card-link {
            text-decoration: none;
            color: inherit;
        }

        .admin-dashboard .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .admin-dashboard .card-text {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Chart container adjustments */
        .chart-container {
            width: 100%;
            height: 400px;
            margin: 0 auto; /* Center the chart */
        }

        /* Adjusting the chart to fit properly */
        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
            }
        }

        @media (max-width: 576px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
<?php include_once('header.php');?>

<div class="container my-4 admin-dashboard">
    
    <div class="row justify-content-center">
    <?php if($isSub):?>
    <div class="col-md-3 col-sm-6 mb-4">
            <a href="itTickets.php?type=all" class="card-link">
                <div class="card card-custom bg-primary text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">All Tickets</span>
                        <i class="material-icons card-icon">list_alt</i>
                    </div>
                    <p class="card-text">View all tickets in the system</p>
                </div>
            </a>
        </div>
        <?php endif?>
        <!-- New Tickets Card -->
        <?php if ($isBeirut): ?>
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="itTickets.php?type=new" class="card-link">
                <div class="card card-custom bg-success text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">New Tickets</span>
                        <i class="material-icons card-icon">fiber_new</i>
                    </div>
                    <p class="card-text">Available Tickets</p>
                </div>
            </a>
        </div>
        <?php endif?>

        <!-- My Progressed Tickets Card -->
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="itTickets.php?type=progressed" class="card-link">
                <div class="card card-custom bg-warning text-dark p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">My Progressed</span>
                        <i class="material-icons card-icon">autorenew</i>
                    </div>
                    <p class="card-text">My Progressed Tickets</p>
                </div>
            </a>
        </div>

        <!-- Team Tickets Card (only visible if leader) -->
        <?php if ($isLeader): ?>
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="teamTickets.php" class="card-link">
                <div class="card card-custom bg-white text-dark border-dark p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title text-dark">Team Tickets</span>
                        <i class="material-icons card-icon text-dark">group</i>
                    </div>
                    <p class="card-text text-dark">View team tickets</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <!-- My Forwarded Tickets Card -->
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="Myforwarded.php" class="card-link">
                <div class="card card-custom bg-info text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">My Forwarded Tickets</span>
                        <i class="material-icons card-icon">forward</i>
                    </div>
                    <p class="card-text">Tickets that have been forwarded</p>
                </div>
            </a>
        </div>
<!-- My SubTasks Card -->
<div class="col-md-3 col-sm-6 mb-4">
            <a href="MySubTasks.php" class="card-link">
                <div class="card card-custom bg-dark text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">My SubTasks</span>
                        <i class="material-icons card-icon">assignment_ind</i>
                    </div>
                    <p class="card-text">SubTickets Assigned to Me</p>
                </div>
            </a>
        </div>

        <?php if($isSub):?>
        <!-- All Forwarded Tickets Card -->
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="forwarded.php" class="card-link">
                <div class="card card-custom bg-danger text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">All Forwarded Tickets</span>
                        <i class="material-icons card-icon">forward_to_inbox</i>
                    </div>
                    <p class="card-text">View all forwarded tickets</p>
                </div>
            </a>
        </div>
        <?php endif?>
     
    </div>
   
<div class="row justify-content-center chart-row">
    <div class="col-md-8">
        <div class="chart-container">
            <h3 class="text-center"><i class="fas fa-chart-line"></i> Assigned vs Resolved Tickets (Last 14 Days)</h3>
            <canvas id="assignedVsResolvedChart"></canvas>
        </div>
    </div>
</div>

<div class="row justify-content-center chart-row">
    <div class="col-md-8">
        <div class="chart-container">
            <h3 class="text-center"><i class="fas fa-chart-pie"></i> Tickets by Category</h3>
            <canvas id="ticketsByCategoryChart"></canvas>
        </div>
    </div>
</div>

<div class="row justify-content-center chart-row">
    <div class="col-md-8">
        <div class="chart-container">
            <h3 class="text-center"><i class="fas fa-chart-bar"></i> Tickets by Status</h3>
            <canvas id="ticketsByStatusChart"></canvas>
        </div>
    </div>
</div>


</div>



<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="controllers/functions.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function() {
    fetchNotifications();
    setInterval(fetchNotifications, 60000); 
});

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
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Tickets'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw;
                    }
                }
            }
        }
    }
});
var ctx2 = document.getElementById('ticketsByCategoryChart').getContext('2d');
var ticketsByCategoryChart = new Chart(ctx2, {
    type: 'doughnut', // You can also use 'bar' or 'pie' if preferred
    data: <?php echo json_encode($chartDataByCategory); ?>,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.raw;
                    }
                }
            }
        }
    }
});
var ctx2 = document.getElementById('ticketsByStatusChart').getContext('2d');
var ticketsByStatusChart = new Chart(ctx2, {
    type: 'bar',
    data: <?php echo json_encode($chartDataByStatus); ?>,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                type: 'category',
                labels: <?php echo json_encode(array_keys($statusCounts)); ?>,
                title: {
                    display: true,
                    text: 'Ticket Status'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Tickets'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw;
                    }
                }
            }
        }
    }
});



</script>
</body>
</html>
