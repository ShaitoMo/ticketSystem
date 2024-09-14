<?php
require_once 'controllers/UserController.php';
require_once 'controllers/ITController.php';

$user = new UserController();
$IT = new ITController();

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $person = $user->getUserById($user_id);

    if ($person) {
        $allTickets = $IT->countAllTickets($user_id);
       
        $closedTickets = $IT->countTickets('Closed', '', $user_id, '');
        $onHoldTickets = $IT->countTickets('On Hold', '', $user_id, '');
        $resolvedTickets = $IT->countTickets('Resolved', '', $user_id, '');
        $ProgressTickets = $IT->countTickets('In progress', '', $user_id, '');

        echo '<p><strong>ID:</strong> ' . htmlspecialchars($person['id']) . '</p>';
        echo '<p><strong>Username:</strong> ' . htmlspecialchars($person['username']) . '</p>';
        echo '<p><strong>Email:</strong> ' . htmlspecialchars($person['email']) . '</p>';
        echo '<p><strong>Role:</strong> ' . ($person['itrole_id'] ? htmlspecialchars($person['itrole_id']) : 'No role assigned') . '</p>';
        echo '<p><strong>All Tickets:</strong> ' . $allTickets . '</p>';
        echo '<p><strong>Active Tickets:</strong> ' . $activeTickets . '</p>';
        echo '<p><strong>Closed Tickets:</strong> ' . $closedTickets . '</p>';
        echo '<p><strong>On Hold Tickets:</strong> ' . $onHoldTickets . '</p>';
        echo '<p><strong>Resolved Tickets:</strong> ' . $ProgressTickets . '</p>';
    } else {
        echo '<p>User not found.</p>';
    }
} else {
    echo '<p>Invalid request.</p>';
}
