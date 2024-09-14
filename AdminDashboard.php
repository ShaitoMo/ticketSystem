<?php 
require_once 'controllers/ITController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/UserController.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
$ITController = new ITController();
$UserController = new UserController();
$AdminController = new AdminController();

$user_id = $_SESSION['user_id'];

$all_it_personnel = $UserController->getITPersonnel(1);


$recentTickets = $ITController->getRecentTickets('all');
$assignedTickets = array_fill(0, 14, 0);
$resolvedTickets = array_fill(0, 14, 0);
$dates = [];

// Get current date and last 13 days
for ($i = 13; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}
// Initialize arrays to count tickets
$createdTickets = array_fill(0, count($dates), 0);
$closedTickets = array_fill(0, count($dates), 0);

foreach ($recentTickets as $ticket) {
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

    // Handle creation dates
    if (!empty($ticket['created_at'])) {
        $createdDate = date('Y-m-d', strtotime($ticket['created_at']));
        if (in_array($createdDate, $dates)) {
            $index = array_search($createdDate, $dates);
            $createdTickets[$index]++;
        }
    }

    // Handle closure dates
    if (!empty($ticket['closed_at'])) {
        $closedDate = date('Y-m-d', strtotime($ticket['closed_at']));
        if (in_array($closedDate, $dates)) {
            $index = array_search($closedDate, $dates);
            $closedTickets[$index]++;
        }
    }
}

$AllchartData = [
    'labels' => $dates,
    'datasets' => [
        [
            'label' => 'Created Tickets',
            'data' => $createdTickets,
            'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
            'borderColor' => 'rgba(255, 159, 64, 1)',
            'borderWidth' => 1,
            'pointBackgroundColor' => 'rgba(255, 159, 64, 1)',
            'pointBorderColor' => '#fff',
            'pointHoverBackgroundColor' => '#fff',
            'pointHoverBorderColor' => 'rgba(255, 159, 64, 1)'
        ],
        [
            'label' => 'Closed Tickets',
            'data' => $closedTickets,
            'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
            'borderColor' => 'rgba(255, 99, 132, 1)',
            'borderWidth' => 1,
            'pointBackgroundColor' => 'rgba(255, 99, 132, 1)',
            'pointBorderColor' => '#fff',
            'pointHoverBackgroundColor' => '#fff',
            'pointHoverBorderColor' => 'rgba(255, 99, 132, 1)'
        ],
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
// Fetch categories
$categories = $ITController->getsubCategories();

// Initialize category counts
$categoryCounts = array_fill_keys(array_column($categories, 'id'), 0);

// Count tickets by category
foreach ($recentTickets as $ticket) {
    if (isset($categoryCounts[$ticket['category_id']])) {
        $categoryCounts[$ticket['category_id']]++;
    }
}

// Prepare chart data
$chartDataByCategory = [
    'labels' => array_column($categories, 'name'),
    'datasets' => [
        [
            'label' => 'Tickets by Category',
            'data' => array_values($categoryCounts),
            'backgroundColor' => [
                'rgba(255, 99, 132, 0.2)',   // Red
                'rgba(54, 162, 235, 0.2)',   // Blue
                'rgba(255, 206, 86, 0.2)',   // Yellow
                'rgba(75, 192, 192, 0.2)',   // Teal
                'rgba(153, 102, 255, 0.2)',   // Purple
                'rgba(255, 159, 64, 0.2)',   // Orange
                'rgba(199, 199, 199, 0.2)',   // Light Gray
                'rgba(83, 102, 255, 0.2)',   // Light Blue
                'rgba(255, 105, 180, 0.2)',   // Hot Pink
                'rgba(255, 215, 0, 0.2)',     // Gold
                'rgba(0, 128, 128, 0.2)',     // Teal
                'rgba(139, 0, 139, 0.2)',     // Dark Violet
                'rgba(0, 255, 127, 0.2)'      // Spring Green
            ],
            'borderColor' => [
                'rgba(255, 99, 132, 1)',     // Red
                'rgba(54, 162, 235, 1)',     // Blue
                'rgba(255, 206, 86, 1)',     // Yellow
                'rgba(75, 192, 192, 1)',     // Teal
                'rgba(153, 102, 255, 1)',    // Purple
                'rgba(255, 159, 64, 1)',     // Orange
                'rgba(199, 199, 199, 1)',    // Light Gray
                'rgba(83, 102, 255, 1)',     // Light Blue
                'rgba(255, 105, 180, 1)',    // Hot Pink
                'rgba(255, 215, 0, 1)',      // Gold
                'rgba(0, 128, 128, 1)',      // Teal
                'rgba(139, 0, 139, 1)',      // Dark Violet
                'rgba(0, 255, 127, 1)'       // Spring Green
            ],
            'borderWidth' => 1
        ]
    ]
];




$ticketCounts = [];
$personNames = [];

// Initialize ticket counts for each IT personnel
foreach ($all_it_personnel  as $person) {
    $ticketCounts[$person['id']] = 0;
    $personNames[$person['id']] = $person['first_name'];
}

// Count tickets assigned to each IT person
foreach ($recentTickets as $ticket) {
    if (isset($ticket['assigned_to']) && isset($ticketCounts[$ticket['assigned_to']])) {
        $ticketCounts[$ticket['assigned_to']]++;
    }
}

// Calculate average number of tickets per person
$averageTickets = array_sum($ticketCounts) / count($ticketCounts);

// Prepare data for Chart.js
$chartData = [
    'labels' => array_values($personNames),
    'datasets' => [
        [
            'label' => 'Tickets Assigned',
            'data' => array_values($ticketCounts),
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'borderWidth' => 1
        ],
        [
            'label' => 'Average Tickets',
            'data' => array_fill(0, count($ticketCounts), $averageTickets),
            'type' => 'line',
            'borderColor' => 'rgba(255, 99, 132, 1)',
            'borderWidth' => 2,
            'fill' => false
        ]
    ]
];




$AllTimeAvgResolution=$AdminController->AverageResolutionTime(null);
$spanTimeAvgResolution=$AdminController->AverageResolutionTime(14);



$AllTimeAvgAssignment=$AdminController->AverageAssignmentTime(null);
$spanTimeAvgAssignment=$AdminController->AverageAssignmentTime(14);

$latestHistory = $AdminController->getLatestHistory('5');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="css\AdminDashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="admin-dashboard">
    <?php include 'header.php'; ?>
    <div class="container my-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <a href="AdminTickets.php?type=all" class="card-link">
                <div class="card card-custom bg-dark text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">All Tickets</span>
                        <i class="material-icons card-icon">assignment</i>
                    </div>
                    <p class="card-text">View all tickets</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="AdminTickets.php?type=new" class="card-link">
                <div class="card card-custom bg-primary text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">New Tickets</span>
                        <i class="material-icons card-icon">fiber_new</i>
                    </div>
                    <p class="card-text">View new tickets</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="AdminTickets.php?type=inprogress" class="card-link">
                <div class="card card-custom bg-info text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">In Progress</span>
                        <i class="material-icons card-icon">hourglass_empty</i>
                    </div>
                    <p class="card-text">In progress tickets</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4 special-width">
            <a href="AdminRequests.php" class="card-link">
                <div class="card card-custom bg-secondary text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">Approval Requests</span>
                        <i class="material-icons card-icon">approval</i>
                    </div>
                    <p class="card-text">Approval requests</p>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4 special-width">
            <a href="forwarded.php" class="card-link">
                <div class="card card-custom bg-success text-white p-4 rounded-lg shadow">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="card-title">Forwarded Tickets</span>
                        <i class="material-icons card-icon">forward</i>
                    </div>
                    <p class="card-text">Tickets that have been forwarded</p>
                </div>
            </a>
        </div>
    </div>
</div>



        <div class="container">
        <div class="row mb-4">
    <!-- Latest Ticket History -->
    <div class="col-md-4">
        
            <h4 class="card-title">Latest Ticket History</h4>
            <div class="history-container">
<?php foreach ($latestHistory as $record): ?>
    <?php
    // Calculate time difference in minutes
    $changedAt = new DateTime($record['changed_at']);
    $now = new DateTime();
    $interval = $now->diff($changedAt);
    $minutesAgo = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
    ?>
    <div class="history-item">
        <a href="ItViewTicket.php?id=<?php echo urlencode($record['ticket_id']); ?>" class="history-link">
            <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($record['ticket_id']); ?> was set to <strong><?php echo htmlspecialchars($record['new_status']); ?></strong> by <strong><?php echo htmlspecialchars($record['changed_by_username']); ?></strong> <strong><?php echo $minutesAgo; ?> minutes ago</strong></p>
        </a>
    </div>
<?php endforeach; ?>

        
        </div>
    </div>

    <!-- Tickets Overview Chart -->
    <div class="col-md-8">
        <div class="chart-container">
            <div class="chart-title">
                <h3>Tickets Overview</h3>
            </div>
            <canvas id="ticketsChart"></canvas>
        </div>
    </div>
</div>

    <!-- Additional Charts -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="chart-container">
            <div class="chart-title">
                <h3>Resolution and Assignment Time Overview</h3>
            </div>
            <canvas id="combinedChart" width="400" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-container">
            <div class="chart-title">
                <h3>Status Distribution</h3>
            </div>
            <canvas id="statusPieChart"></canvas>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="chart-container">
            <div class="chart-title">
                <h3>Category Distribution</h3>
            </div>
            <canvas id="categoryPieChart"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-container">
            <div class="chart-title">
                <h3>Tickets by IT Personnel</h3>
            </div>
            <canvas id="personnelChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
   
var ctx1 = document.getElementById('ticketsChart').getContext('2d');
var assignedVsResolvedChart = new Chart(ctx1, {
    type: 'line',
    data: <?php echo json_encode($AllchartData); ?>,
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

document.addEventListener("DOMContentLoaded", function() {
    // Data for ticket statuses
    var statusData = {
        labels: ["New", "In Progress", "Resolved", "On Hold", "Closed"],
        datasets: [{
            data: [5, 10, 3, 7, 2], // Example data, replace with actual numbers
            backgroundColor: ["#007bff", "#17a2b8", "#28a745", "#ffc107", "#dc3545"]
        }]
    };

    // Render Pie Chart
    var ctx = document.getElementById('statusPieChart').getContext('2d');
    var statusPieChart = new Chart(ctx, {
        type: 'pie',
        data: statusData,
        options: {
            responsive: true,
            legend: {
                position: 'bottom'
            }
        }
    });
});
var ctx3 = document.getElementById('categoryPieChart').getContext('2d');
var categoryPieChart = new Chart(ctx3, {
    type: 'pie',
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
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            label += context.parsed + ' tickets';
                        }
                        return label;
                    }
                }
            }
        }
    }
});
var ctx = document.getElementById('personnelChart').getContext('2d');
        var personnelChart = new Chart(ctx, {
            type: 'bar',
            data: <?php echo json_encode($chartData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'IT Personnel'
                        }
                    },
                    y: {
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
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            }
        });

        var ctx = document.getElementById('combinedChart').getContext('2d');
var combinedChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['All Time', 'Last 14 Days'],
        datasets: [{
            label: 'Resolution Time (hours)',
            data: [
                <?php echo number_format($AllTimeAvgResolution, 2); ?>, 
                <?php echo number_format($spanTimeAvgResolution, 2); ?>
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.8)', // Professional blue color
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            barThickness: 20
        }, {
            label: 'Assignment Time (hours)',
            data: [
                <?php echo number_format($AllTimeAvgAssignment, 2); ?>, 
                <?php echo number_format($spanTimeAvgAssignment, 2); ?>
            ],
            backgroundColor: 'rgba(255, 99, 132, 0.8)', // Professional pink color
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            barThickness: 20
        }]
    },
    options: {
        indexAxis: 'y', // Horizontal bar chart
        scales: {
            x: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Time (hours)'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Metrics'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' hours';
                    }
                }
            }
        }
    }
});

</script>

</script>
</body>
</html>
