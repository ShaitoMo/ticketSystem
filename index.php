<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'controllers/AdminController.php';

$Admincontroller = new AdminController();
$settings= $Admincontroller->getSettings();

if (isset($settings['ticket_auto_close'])) {
    $Admincontroller->autoCloseResolvedTickets($settings['ticket_auto_close']);
}else{echo'hi';}
if($settings['ticket_assignment']==="Auto"){
    $Admincontroller->AutoAssignTickets();
    

}

$user_role = $_SESSION['role'];

if ($user_role == 'IT Administrator') {

    header("Location: AdminDashboard.php");
} elseif ($user_role == 'IT Personnel' || $user_role=='IT Coordinator' ||$user_role=='Sub-Admin') {
    header("Location: ItDashboard.php");
} else {
    header("Location: dashboard.php");

}

?>
