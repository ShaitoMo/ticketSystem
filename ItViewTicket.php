<?php
    session_start();
    require_once 'controllers/UserController.php';
    require_once 'controllers/ITController.php';
    require_once 'controllers/AdminController.php';


    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $ITController = new ITController();
    $userController = new UserController();
    $AdminController = new AdminController();

    $ticket_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $ticket_details = $userController->getTicketById($ticket_id);
    $attachments = $userController->getAttachmentsByTicketId($ticket_id);
    $comments = $userController->getCommentsByTicketId($ticket_id,'0');
    $Privatecomments = $userController->getCommentsByTicketId($ticket_id,'1');

    $role = $_SESSION['role'];

    $isITAdmin = ($role == 'IT Administrator' ||$role == 'Sub-Admin' );
    $isCoordinator= ($role == 'IT Coordinator');
    $ticket_history = $userController->getHistory($ticket_id);
    $Teams = $ITController->getMainCategories();
    $isEditable = $isITAdmin || ($ticket_details['assigned_to'] == $user_id && $ticket_details['status'] != 'Closed');
    $itpersonnel=$userController->getITPersonnel(1);

    $HasRequest=$userController->hasRequestAccess($ticket_id, $user_id );
    $HasSubTask=$ITController->hasSubTaskAccess($ticket_id, $user_id );
    $solutionAttachments=$userController->getSolutionAttachments($ticket_id);

    $TicketTeam = $ITController->getUserTeam($ticket_details['created_by']);
    $TeamMembers=$ITController->getTeamMembersByTicketCat($ticket_details['category_id']);
    $isClosed = ($ticket_details['status'] === 'Closed') ? true : false;

    $campuses=$AdminController->getCampuses();

    $subTasks = $ITController->getSubTasksByTicketId($ticket_id);  
    $approvalRequests = $ITController->getRequestsByTicketId($ticket_id); 
    $isBlocked=$ITController->isResolutionBlocked($subTasks,$approvalRequests);

    $isLeader=$ITController->isTeamLeaderByCategory($ticket_details['category_id'],$user_id);

    $settings=$AdminController->getSettings();
    $MaxSize=$settings['max_attachment_size'];

    $statusClass = match (htmlspecialchars($ticket_details['status'])) {
        'Resolved' => 'bg-success',
        'On Hold' => 'bg-warning',
        'Closed' => 'bg-danger',
        'In Progress' => 'bg-info',
        'New' => 'bg-secondary'
    
    };


    $priorityClass = match (htmlspecialchars($ticket_details['priority'])) {
        'High' => 'bg-danger  text-dark',
        'Medium' => 'bg-warning text-dark',
        'Low' => 'bg-info  text-dark',
    
    };

    $forwarding=$ITController->getForwardingHistory($ticket_id);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'getSubCategories') {
            $mainCategoryId = $_POST['category_id'];
            $subCategories = $ITController->getSubCategoriesByMainCategory($mainCategoryId);
            echo json_encode($subCategories); // Return JSON response
            exit();
        }
        if ( isset($_POST['team_id']) && isset($_POST['category_id'])) {//team change

           
            $team_id = $_POST['team_id'];
            $category_id = $_POST['category_id'];
            
            
            $result = $ITController->changeTeam($ticket_id,  $category_id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Team changed successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to change team.']);
            }
            
            exit();
        }
        

        if (isset($_POST['take_ticket'])) {
            $ITController->assignTicketToUser($ticket_id, $user_id);
            $userController->addHistory($ticket_id, 'In Progress', $user_id);
            $ITController->updateTicketStatus($ticket_id, 'In Progress', $user_id);

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } if (isset($_POST['Force_close'])) {



            $ITController->updateTicketStatus($ticket_id, 'Closed', $user_id);
        
            echo json_encode(['status' => 'success']);
            exit();
        }
        
        if (isset($_POST['Force_assign'])) {
            $ticket_id = $_POST['ticket_id'];
            $assign_to = $_POST['assign_to'];
        
            // Assuming $ITController and $userController are properly initialized
            $result = $ITController->assignTicketToUser($ticket_id, $assign_to);
            $assigned_to_name = $userController->getUserNameById($assign_to);
        
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'assigned_to_name' => htmlspecialchars($assigned_to_name)
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to assign ticket'
                ]);
            }
            exit();
        } 

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
            header('Content-Type: application/json');
            $attachments = [];
            if (!empty($_FILES['attachments']['name'][0])) { // Check if at least one file is uploaded
                $attachments = $userController->handleFileUploads($_FILES['attachments'], $MaxSize, 'Comment');
            } 
            $comment = isset($_POST['comment']) ? htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8') : '';
            if ($ticket_id && $user_id && $comment !== '') {
                $result = $userController->addComment($ticket_id, $user_id, $comment, isset($_POST['private']) ? intval($_POST['private']) : 0, $attachments);
                
                if ($result) {
                    $newComment = [
                        'comment' => $comment,
                        'user_id' => $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'attachments' => $attachments 
                    ];
                    echo json_encode(['status' => 'success', 'comment' => $newComment]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add comment']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            }
            exit();
        }
        if ( isset($_POST['solution_update'])) {

                $solution = $_POST['solution'];
                $attachments = [];
        
                if (!empty($_FILES['solution_attachments']['name'][0])) {
                    $attachments = $userController->handleFileUploads($_FILES['solution_attachments'], $MaxSize, 'Solution');
                }
        
                $result = $ITController->updateSolution($ticket_id, $solution, $user_id, $attachments);
            
        
                if ($result) {
                
                    $response = [
                        'status' => 'success',
                        'solution' => htmlspecialchars($solution, ENT_QUOTES, 'UTF-8'),
                        'attachments' => []
                    ];

                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment) {
                            $response['attachments'][] = [
                                'id' =>  $result ,
                                'file_path' => htmlspecialchars($attachment['file_path'], ENT_QUOTES, 'UTF-8'),
                                'file_name' => htmlspecialchars(basename($attachment['file_path']), ENT_QUOTES, 'UTF-8')
                            ];
                        }
                    }
                    echo json_encode($response);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update solution']);
                }
                exit();
        }
        if (isset($_POST['approval_request_submit'])) {
            $approvalDescription = $_POST['approvalDescription'];
            $requestRecipient = $_POST['requestRecipient'];
        
            if ($requestRecipient == 'IT') {
                $requested_to = 3; 
            } else {
                $requested_to = $userController->HeadOfUser($ticket_details['created_by']); 
            }
        
            $result = $ITController->CreateAproval($ticket_id, $user_id, $requested_to, $approvalDescription);
        
            if ($result) {
                $approvalRequest = [
                    'request_description' => htmlspecialchars($approvalDescription),
                    'requested_by' => htmlspecialchars($userController->getUserNameById($user_id)),
                    'requested_to' => htmlspecialchars($userController->getUserNameById($requested_to)),
                    'status' => 'Pending' // Set default status as 'Pending'
                ];
        
                echo json_encode(['status' => 'success', 'approvalRequest' => $approvalRequest]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit the approval request']);
            }
            exit(); // Ensure no further output
        }
        if (isset($_POST['sub_task_submit'])) {
            $subTaskDescription = $_POST['subTaskDescription'] ?? '';
            $assignToSubTask = $_POST['assignToSubTask'] ?? '';
        
            // Call the SubTaskAssign function
            $userController->addHistory($ticket_id, 'Closed', $user_id);
            $result = $ITController->SubTaskAssign($ticket_id, $user_id, $assignToSubTask, $subTaskDescription);
        
            if ($result) {
                $subTask = [
                    'sub_task_description' => htmlspecialchars($subTaskDescription),
                    'assigned_to' => htmlspecialchars($userController->getUserNameById($assignToSubTask)),
                    'status' => 'In Progress' // Set default status as 'New'
                ];
        
                echo json_encode(['status' => 'success', 'subTask' => $subTask]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to assign the sub-task.']);
            }
            exit; // Make sure no additional output is sent
        
        
        
        }if (isset($_POST['action']) && $_POST['action'] === 'delete_attachment') {
            $attachmentId = intval($_POST['attachment_id']);
        
            $response = ['success' => false, 'message' => 'Failed to delete attachment.'];
        
            if ($userController->deleteSoultionAttachment($attachmentId)) {
                $response['success'] = true;
                $response['message'] = "Attachment deleted successfully.";
            }
        
            // Return JSON response
            echo json_encode($response);
            exit();
        }
        if (isset($_POST['action']) && $_POST['action'] === 'get_ticket_history' ) {
        
        
            $ticket_history = $userController->getHistory($ticket_id);
        
            $response = [];
        
            foreach ($ticket_history as $history) {
                $statusBadgeClass = match (htmlspecialchars($history['new_status'])) {
                    'Resolved' => 'bg-success',
                    'On Hold' => 'bg-warning',
                    'Closed' => 'bg-danger',
                    'In Progress' => 'bg-info',
                    'New' => 'bg-secondary'
                };
        
                $response[] = [
                    'ticketId'=>$ticket_id,
                    'status' => htmlspecialchars($history['new_status']),
                    'statusBadgeClass' => $statusBadgeClass,
                    'changed_by' => $userController->getUserNameById($history['changed_by']),
                    'changed_at' => htmlspecialchars($history['changed_at'])
                ];
            }
        
            echo json_encode($response);
            exit(); 
        }
        
if (isset($_POST['forward_to_campus'])) {


    $to_campus_id = htmlspecialchars($_POST['forward_to_campus']);
    $from_campus_id = $ticket_details['campus_id']; // Assuming this is fetched somewhere earlier



    $response = $AdminController->forwardTicket($ticket_id, $from_campus_id, $to_campus_id,$user_id);

    if ($response) {
        echo json_encode(['success' => true, 'message' => 'Ticket forwarded successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to forward the ticket.']);
    }

    exit; // Ensure no further PHP code is executed
}
if (isset($_POST['ticket_id'], $_POST['response'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $action = $_POST['response'];
    $response = [];

    if ($action == 'solved') {
        // Update ticket status to "Closed"
        $userController->updateStatus($ticket_id, 'Closed');
        $userController->addHistory($ticket_id, 'Closed', $user_id);
        
        $response = [
            'status' => 'success',
            'message' => 'Ticket marked as Solved!'
        ];
    } elseif ($action == 'unsolved') {
        // Update ticket status to "In Progress"
        $userController->updateStatus($ticket_id, 'In Progress');
        $userController->addHistory($ticket_id, 'In Progress', $user_id);
        
        $response = [
            'status' => 'success',
            'message' => 'Ticket marked as Unsolved!'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid action.'
        ];
    }

    // Send back the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();

}

        
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/ItViewTicket.css">
 
  
</head>
<body>
    <?php include_once('header.php'); ?>
    <?php if ($ticket_details['created_by'] == $user_id && $ticket_details['status']=='Resolved' ): ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <span class="font-weight-bold mr-4">You created this request. Please review and take action:</span>
        
        <form method="post" action="">
                                <div class="btn-group" role="group" aria-label="Resolved Ticket Actions">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">

                                    <button type="submit" name="response" value="solved" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Solved
                                    </button>

                                    <button type="submit" name="response" value="unsolved" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Unsolved
                                    </button>
                                </div>
                            </form>
    </div>
<?php endif; ?>
<div id="response-alert" class="alert d-none alert-dismissible fade show" role="alert">
    <span id="alert-message"></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>


    <div class="container ticket-container">
    <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Ticket ID and Subject -->
    <h2 class="mb-4 ticket-title">
        <i class="fas fa-ticket-alt me-2"></i> Ticket # <?php echo htmlspecialchars($ticket_details['id']); ?> - <?php echo htmlspecialchars($ticket_details['subject']); ?>
    </h2>

    <!-- Created on and Creator -->
    <p class="text-muted ticket-meta">
        <small>
            <i class="fas fa-calendar-alt me-2"></i> Created on: <?php echo htmlspecialchars($ticket_details['created_at']); ?> 
            <span class="ms-3"><i class="fas fa-user me-2"></i> by <?php echo htmlspecialchars($userController->getUserNameById($ticket_details['created_by'])); ?></span>
        </small>
    </p>

    <!-- Description -->
    <div class="ticket-description mb-4">
        <p><i class="fas fa-info-circle me-2"></i> <?php echo nl2br(htmlspecialchars($ticket_details['description'])); ?></p>
    </div>


        <div class="row">
        <div class="col-md-8 d-flex flex-column">
        <div class="ticket-details flex-grow-1">
    
  
    <dl class="row">
    <dt class="col-sm-3"><i class="fas fa-info-circle"></i> Status:</dt>
<dd class="col-sm-9 d-flex justify-content-between align-items-center">
    <span class="badge <?php echo $statusClass; ?>">
        <?php echo htmlspecialchars($ticket_details['status']); ?>
    </span>
    
    <?php if (($isITAdmin || $isLeader) && (!$isClosed )): ?>
        <form id="forceCloseForm" method="POST" style="display: inline-block; margin-left: auto;">
            <input type="hidden" name="Force_close" value="1"> <!-- Hidden field to ensure the correct value is sent -->
            <button id="forceCloseBtn" class="btn btn-danger btn-sm" style="height: 28px; padding: 0 10px; line-height: 1.5; font-size: 0.875rem;">
                <i class="fas fa-times-circle"></i> Force Close
            </button>
        </form>


    <?php endif; ?>
</dd>


        <dt class="col-sm-3"><i class="fas fa-exclamation-circle"></i> Priority:</dt>
        <dd class="col-sm-9">
            <span class="badge <?php echo $priorityClass; ?>">
                <?php echo htmlspecialchars($ticket_details['priority']); ?>
            </span>
        </dd>
        
        <dt class="col-sm-3">
        <i class="fas fa-university" style="margin-right: 5px;"></i> Main Campus:
    </dt>
    <dd class="col-sm-9">
        <span>
            <?php echo htmlspecialchars($ticket_details['campus_id']); ?>
        </span>
    </dd>

        <dt class="col-sm-3"><i class="fas fa-list-alt"></i> Category:</dt>
        <dd class="col-sm-9">
            <?php echo htmlspecialchars($ITController->getCategoryNameById(htmlspecialchars($ticket_details['category_id']))); ?>
        </dd>

        <dt class="col-sm-3"><i class="fas fa-user"></i> Assigned To:</dt>
<dd class="col-sm-9 d-flex justify-content-between align-items-center">
    <span id="assignedTo">
        <?php 
        if (empty($ticket_details['assigned_to'])) {
            echo 'Unassigned';
        } else {
            echo htmlspecialchars($userController->getUserNameById($ticket_details['assigned_to']));
        }
        ?>
    </span>
    
        <?php if ($isITAdmin || $isLeader): ?>
            <form id="assignForm" method="post" class="d-inline-flex">
                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
                <input type="hidden" name="Force_assign" value="1">
                <select name="assign_to" class="form-control form-control-sm w-auto">
                    <?php foreach ($TeamMembers as $person): ?>
                        <option value="<?php echo htmlspecialchars($person['id']); ?>">
                            <?php echo htmlspecialchars($person['first_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="submit" class="btn btn-success btn-sm ml-2">
                    <i class="fas fa-check"></i> Force Assign
                </button>
            </form>
        <?php endif; ?>
    </dd>

        <dt class="col-sm-3"><i class="fas fa-calendar-check"></i> Assigned At:</dt>
        <dd class="col-sm-9">
            <?php 
            if (empty($ticket_details['assigned_to'])) {
                echo 'Unassigned';
            } else {
                echo htmlspecialchars($ticket_details['assigned_at']);
            }
            ?>
        </dd>

        <dt class="col-sm-3"><i class="fas fa-tags"></i> Type:</dt>
        <dd class="col-sm-9"><?php echo htmlspecialchars($ticket_details['type']); ?></dd>

        <dt class="col-sm-3"><i class="fas fa-paperclip"></i> Attachments:</dt>
        <dd class="col-sm-9">
            <?php if (!empty($attachments)): ?>
                <?php foreach ($attachments as $attachment): ?>
                    <a href="../uploads/tickets/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank">
                        <i class="fas fa-paperclip"></i>
                        <?php echo htmlspecialchars(basename($attachment['file_path'])); ?>
                    </a><br>
                <?php endforeach; ?>
            <?php else: ?>
                No attachments.
            <?php endif; ?>
        </dd>
    </dl>
    <h4><i class="fas fa-edit"></i> Solution</h4>
    <div id="solutionContainer">
    <!-- Display solution form if editable -->
    <?php if ($isEditable): ?>
            <form id="solutionForm" method="POST" enctype="multipart/form-data" <?php if ($isBlocked) echo 'disabled'; ?>>
                <div class="form-group">
                    <textarea name="solution" id="solution" class="form-control small-textarea" rows="5" <?php if ($isBlocked) echo 'disabled'; ?>><?php echo htmlspecialchars($ticket_details['solution'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-group mt-2">
                    <label for="solution_attachments">Attach files:</label>
                    <input type="file" name="solution_attachments[]" id="solution_attachments" class="form-control" multiple <?php if ($isBlocked) echo 'disabled'; ?>>
                </div>
                <input type="hidden" name="solution_update" value="true">
                
                <button id='ResolveBtn'type="submit" class="btn btn-success mt-2" <?php if ($isBlocked) echo 'disabled'; ?>>
                    <i class="fas fa-check"></i> Resolve
                </button>

                <?php if ($isBlocked): ?>
                    <p class="text-danger mt-2">
                        <i class="fas fa-exclamation-triangle"></i> Resolution is blocked. Please check pending requests and in-progress subtasks.
                    </p>
                <?php endif; ?>
            </form>

    <?php else: ?>
        <p><?php echo nl2br(htmlspecialchars($ticket_details['solution'])); ?></p>
    <?php endif; ?>


   
    <?php foreach ($solutionAttachments as $attachment): ?>
    <div class="attachment-item position-relative mb-2">
        <a href="../uploads/solutions/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="attachment-link me-2">
            <i class="fas fa-file"></i> <?php echo htmlspecialchars(basename($attachment['file_path'])); ?>
        </a>
        <?php if ($isEditable): ?>
            <form method="POST" class="delete-attachment-form position-absolute">
                <input type="hidden" name="attachment_id" value="<?php echo htmlspecialchars($attachment['id']); ?>">
                <button  type="submit" class="btn btn-sm btn-gray p-1">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>



</div>
</div>

</div>



                
 <!--  Ticket History-->
<div class="col-md-4 d-flex flex-column">
    <div class="ticket-history flex-grow-1">
        <h4><i class="fas fa-history"></i> Ticket History</h4>
        <div id="history-items">
            <?php if (!empty($ticket_history)): ?>
                <?php foreach ($ticket_history as $history): ?>
                    <?php
                    $statusBadgeClass = match (htmlspecialchars($history['new_status'])) {
                        'Resolved' => 'bg-success',
                        'On Hold' => 'bg-warning',
                        'Closed' => 'bg-danger',
                        'In Progress' => 'bg-info',
                        'New' => 'bg-secondary'
                                        };
                    ?>
                    <div class="history-item rounded shadow-sm">
                        <p><strong>Status:</strong> 
                            <span class="badge <?php echo $statusBadgeClass; ?>">
                                <?php echo htmlspecialchars($history['new_status']); ?>
                            </span>
                        </p>
                        <p class="text-muted"><small>Changed by <?php echo $userController->getUserNameById($history['changed_by']); ?> on <?php echo htmlspecialchars($history['changed_at']); ?></small></p>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No history available for this ticket.</p>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Container for Comments and Forwarding History using Flexbox -->
<div class="d-flex flex-column flex-lg-row">
    <!-- Comment Section (Left Column - occupies more space) -->
    <div class="ticket-comments mb-4 col-lg-8">
        <h4><i class="fas fa-comments"></i> Comments</h4>
        <div id="comments-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item <?php echo $comment['user_id'] == $user_id ? 'my-comment' : ''; ?> rounded shadow-sm mb-3">
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="text-muted mb-0">By <?php echo htmlspecialchars($userController->getUserNameById($comment['user_id'])); ?> on <?php echo htmlspecialchars($comment['created_at']); ?></p>
                            <?php
                            $commentAttachments = $userController->GetCommentAttachments($comment['id']);
                            if (!empty($commentAttachments)): ?>
                                <div class="attachments ms-3">
                                    <?php foreach ($commentAttachments as $attachment): ?>
                                        <a href="../uploads/comments/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="attachment-link me-2">
                                            <i class="fas fa-file"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet.</p>
            <?php endif; ?>
        </div>

        <!-- Add Comment Form -->
        <form id="comment-form" class="comment-form mt-4" enctype="multipart/form-data" method="POST">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="comment" class="form-label">Add Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="2" required></textarea>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="w-100">
                        <label for="attachments" class="form-label">Attach Files</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Comment</button>
        </form>
    </div>

    <!-- Forwarding History Section (Right Column - narrower) -->
    <div class="forwarding-history col-lg-4">
        <h3>Forwarding History</h3>
        <?php if (empty($forwarding)) : ?>
            <p>No forwarding history available for this ticket.</p>
        <?php else : ?>
            <?php foreach ($forwarding as $history) : ?>
                <div class="card mb-3">
                    <div class="card-body">
                       
                        <p class="card-text">
                            <strong>From Campus:</strong> <?php echo $history['from_campus']; ?><br>
                            <strong>To Campus:</strong> <?php echo $history['to_campus']; ?><br>
                            <strong>Forwarded by:</strong> <?php echo $history['forwarded_by']; ?><br>
                            <strong>Forwarded at:</strong> <?php echo $history['forwarded_at']; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<div id="actionsContainer" class="mt-4">
    <h4><i class="fas fa-cogs"></i> Actions</h4>
    <div class="d-flex align-items-center action-bar">

        <!-- Reopen Ticket Button -->
        <?php if ($isClosed && ($isITAdmin || $isLeader)): ?>
            <div class="action-item">
                <form id="reopenForm" method="POST">
                    <input type="hidden" name="reopen_ticket" value="1">
                    <button type="submit" class="btn btn-warning btn-sm action-btn">
                        <i class="fas fa-redo"></i> Reopen Ticket
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Vertical Separator -->
        <div class="vertical-separator mx-3"></div>

       <!-- Forward Ticket Button -->
        <?php if ($isITAdmin || $isLeader): ?>
            <div class="action-item">
                <form id="forwardForm" method="POST" class="d-inline-flex">
                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
                    <select name="forward_to_campus" class="form-control form-control-sm action-select">
                        <option value="" disabled selected>Forward to Campus</option>
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?php echo htmlspecialchars($campus['id']); ?>">
                                <?php echo htmlspecialchars($campus['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm ml-2 action-btn">
                        <i class="fas fa-share"></i> Forward
                    </button>
                </form>
            </div>
        <?php endif; ?>


        <!-- Vertical Separator -->
        <div class="vertical-separator mx-3"></div>

        <?php if ($isITAdmin || $isLeader): ?>
        <div class="action-item">
            <form id="forwardMainCampusForm" method="POST" class="d-inline-flex">
                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
                <input type="hidden" name="forward_to_campus" value="1"> <!-- Fixed value for main campus -->
                <button type="submit" class="btn btn-info btn-sm ml-2 action-btn">
                    <i class="fas fa-university"></i> Forward to Main Campus
                </button>
            </form>
        </div>
    <?php endif; ?>

        <!-- Vertical Separator -->
        <div class="vertical-separator mx-3"></div>

        <!-- Change Team Button -->
        <?php if ($isITAdmin || $isLeader): ?>
            <div class="action-item">
    <form id="changeTeamForm" method="POST" class="d-inline-flex flex-column">
        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">

        <!-- Team Selection -->
        <div class="form-group mb-2">
            <select name="team_id" id="teamSelect" class="form-control form-control-sm action-select">
                <option value="" disabled selected>Select Team</option>
                <?php foreach ($Teams as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Category Selection -->
        <div class="form-group mb-3">
            <select name="category_id" id="categorySelect" class="form-control form-control-sm action-select" onchange="updateTeams()">
                <option value="" disabled selected>Select Category</option>
                <!-- fill sub-categories dynamically -->
            </select>
        </div>

        <!-- Change Team Button -->
        <button type="submit" class="btn btn-secondary btn-sm action-btn">
            <i class="fas fa-users"></i> Change Team
        </button>
    </form>
</div>

<?php endif; ?>



    </div>
</div>
          

<?php if($isEditable) : ?>
<div class="d-flex justify-content-between mb-3">
  
    <!-- Assign Sub-Task Section -->
    <div class="sub-task-container p-3 flex-grow-1 ms-2" style="flex: 1 1 48%; max-width: 48%; word-wrap: break-word;">
    <h6 class="text-secondary mb-3"><i class="fas fa-tasks"></i> Assign Sub-Task</h6>
    <form id="subTaskForm" method="POST">
        <div class="mb-3">
            <label for="subTaskDescription" class="form-label small">Sub-Task Description</label>
            <textarea name="subTaskDescription" id="subTaskDescription" class="form-control form-control-sm" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="assignToSubTask" class="form-label small">Assign To</label>
            <select name="assignToSubTask" id="assignToSubTask" class="form-select form-select-sm" required>
                <option value="" disabled selected>Select User</option>
                <?php foreach ($itpersonnel as $person): ?>
                    <option value="<?php echo htmlspecialchars($person['id']); ?>">
                        <?php echo htmlspecialchars($person['first_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">
        <i class="fas fa-tasks"></i> Assign
    </button>
    </form>
</div>


      <!-- Request for Approval Section -->
    <div class="approval-request-container p-3 flex-grow-1 me-2" style="flex: 1 1 48%; max-width: 48%; word-wrap: break-word;">
        <h6 class="text-secondary"><i class="fas fa-paper-plane"></i> Request for Approval</h6>
        <form id="approvalRequestForm" method="POST" class="mb-2">
            <div class="form-group mb-2">
                <label for="approvalDescription" class="form-label small">Approval Request Description</label>
                <textarea name="approvalDescription" id="approvalDescription" class="form-control form-control-sm" rows="2" required></textarea>
            </div>
            <div class="form-group mb-2">
                <label class="form-label small">Send Request To</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="requestRecipient" id="requestIT" value="IT" checked>
                    <label class="form-check-label small" for="requestIT">
                        IT Administration
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="requestRecipient" id="requestDepartment" value="Department">
                    <label class="form-check-label small" for="requestDepartment">
                        Ticket Department
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" name="approval_request_submit">
                <i class="fas fa-paper-plane"></i> Submit
            </button>
        </form>
    </div>
</div>
<?php endif; ?>





<div class="d-flex mb-4 flex-wrap">
    <!-- Display Sub-Tickets -->
    <?php if($isEditable || $HasRequest || $HasSubTask) : ?>
   <div class="sub-tasks" style="flex: 1 1 48%; max-width: 48%; word-wrap: break-word;">
    <h6 class="text-secondary mb-3"><i class="fas fa-tasks"></i> Sub-Tickets</h6>
   
        <ul class="list-group">
             <?php if (!empty($subTasks)): ?>
            <?php foreach ($subTasks as $subTask): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div style="max-width: 80%;">
                        <strong><?php echo htmlspecialchars($subTask['sub_task_description']); ?></strong>
                        <small class="text-muted d-block">
                            Assigned to: <?php echo htmlspecialchars($userController->getUserNameById($subTask['assigned_to'])); ?>
                        </small>
                    </div>
                    <span class="badge <?php echo htmlspecialchars($subTask['status']) === 'Completed' ? 'bg-success' : (htmlspecialchars($subTask['status']) === 'In Progress' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                        <?php echo htmlspecialchars($subTask['status']); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div id='task-alert' class="alert alert-info mt-3" role="alert">
            No sub-tickets available.
        </div>
    <?php endif; ?>
</div>



    <!-- Display Approval Requests -->
    
    <div class="approval-requests" style="flex: 1 1 48%; max-width: 48%; word-wrap: break-word; margin-left: 4%;">
        <h6 class="text-secondary"><i class="fas fa-paper-plane"></i> Approval Requests</h6>
      
            <ul class="list-group">
            <?php if (!empty($approvalRequests)): ?>
                <?php foreach ($approvalRequests as $request): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div style="max-width: 80%;">
                            <strong><?php echo htmlspecialchars($request['request_description']); ?></strong>
                            <small class="text-muted d-block">
                                Requested by: <?php echo htmlspecialchars($userController->getUserNameById($request['requested_by'])); ?> 
                                to:<?php echo htmlspecialchars($userController->getUserNameById($request['requested_to']) ?? 'Unknown'); ?>

                            </small>
                        </div>
                        <span class="badge badge-pill <?php echo htmlspecialchars($request['status']) === 'Approved' ? 'bg-success' : (htmlspecialchars($request['status']) === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                            <?php echo htmlspecialchars($request['status']); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div id='aproval-alert' class="alert alert-info mt-2" role="alert">
                No approval requests available.
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>



<?php if($isEditable || $HasRequest ||$HasSubTask): ?>
<!-- Private Comments Section -->
<div class="private-comments p-3 mb-3 bg-light rounded">
    <h5 class="mb-3"><i class="fas fa-lock"></i> Private Comments</h5>
    <div id="private-comments-list" class="overflow-auto" style="max-height: 250px;">
    <?php if (!empty($Privatecomments)): ?>
        <?php foreach ($Privatecomments as $comment): ?>
            <div class="comment-item <?php echo $comment['user_id'] == $user_id ? 'my-comment' : ''; ?> rounded shadow-sm">
                <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 small">By <?php echo htmlspecialchars($userController->getUserNameById($comment['user_id'])); ?> on <?php echo htmlspecialchars($comment['created_at']); ?></p>
                    <?php
                    $privateCommentAttachments = $userController->GetCommentAttachments($comment['id']);
                    if (!empty($privateCommentAttachments)): ?>
                        <div class="attachments ms-3">
                            <?php foreach ($privateCommentAttachments as $attachment): ?>
                                <a href="../uploads/comments/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="me-2 text-decoration-none">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No private comments yet.</p>
    <?php endif; ?>
</div>


    <!-- Add Private Comment Form -->
    <form id="Private-comment-form" class="comment-form mt-4" enctype="multipart/form-data" method="POST">
        <div class="row mb-3">
            <div class="col-md-8">
                <label for="Private-comment" class="form-label small">Add Comment</label>
                <textarea class="form-control form-control-sm" id="Private-comment" name="comment" rows="2" required></textarea>
            </div>
            <div class="col-md-4">
                <label for="Private-attachments" class="form-label small">Attach Files</label>
                <input type="file" class="form-control form-control-sm" id="Private-attachments" name="attachments[]" multiple>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Comment</button>
    </form>
</div>
<?php endif; ?>

</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="controllers\functions.js"></script>
    <script src="js\ItViewTicket.js"></script>


    <script>
        submitComment('#Private-comment-form', '#Private-comment', '#private-comments-list', '<?php echo htmlspecialchars($userController->getUserNameById($user_id)); ?>', '<?php echo $user_id; ?>', true);
        submitComment('#comment-form', '#comment', '#comments-list', '<?php echo htmlspecialchars($userController->getUserNameById($user_id)); ?>', '<?php echo $user_id; ?>', false);
        requestApproval('approvalRequestForm', 'approval-requests');
        assignSubTask('subTaskForm', 'sub-tasks');
        submitSolutionForm('solutionForm', 'solutionContainer');
        submitAssignForm('assignForm', 'assignedTo');
        document.getElementById('forceCloseBtn').addEventListener('click', function() {
        const ticketId = <?php echo json_encode($ticket_id); ?>;
        forceCloseTicket(ticketId);
      
    });
    $('#teamSelect').change(function() {
    var team = $('#teamSelect').val();
   
    fetchSubCategories('<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>', team , '#categorySelect', '');
});

  
    

</script>

</body>
</html>