function submitComment(formSelector, commentSelector, commentsListSelector, userName, userId, isPrivate) {
    $(formSelector).on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var ticketId = $(this).find('input[name="ticket_id"]').val();
        var comment = $(commentSelector).val();
        
        // Append additional data to FormData
        formData.append('add_comment', true);
        formData.append('ticket_id', ticketId);
        formData.append('comment', comment);
        formData.append('private', isPrivate ? 1 : 0);

        $.ajax({
            type: 'POST',
            url: '', // Add the URL where your PHP script is located
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var newComment = response.comment;
                    var isMyComment = newComment.user_id == userId;
                    var commentHtml = `<div class="comment-item ${isMyComment ? 'my-comment' : ''} rounded shadow-sm">`;
                    commentHtml += '<p>' + newComment.comment + '</p>';
                    
                    // Create a flex container for the date and attachments
                    commentHtml += '<div class="comment-meta d-flex justify-content-between align-items-center">';
                    commentHtml += '<p class="text-muted mb-0">By ' + userName + ' on ' + newComment.created_at + '</p>';

                    if (newComment.attachments && newComment.attachments.length > 0) {
                        commentHtml += '<div class="attachments d-flex align-items-center">';
                        newComment.attachments.forEach(function(attachment) {
                            commentHtml += '<a href="../uploads/comments/' + attachment.file_path + '" target="_blank" class="attachment-link me-2">';
                            commentHtml += '<i class="fas fa-file"></i>';
                            commentHtml += '</a>';
                        });
                        commentHtml += '</div>';
                    }

                    commentHtml += '</div>'; // Close the flex container for date and attachments
                    commentHtml += '</div>'; // Close the comment-item div
                    
                    $(commentsListSelector).prepend(commentHtml);
                    $(commentSelector).val('');
                    $(formSelector).find('input[type="file"]').val('');
                } else {
                    alert(response.message);
                }
            }
        });
    });
}
function assignSubTask(subTaskFormId, subTasksListClass) {
    $(document).ready(function () {
        $('#' + subTaskFormId).on('submit', function (e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = {
                subTaskDescription: $('#' + subTaskFormId + ' #subTaskDescription').val(),
                assignToSubTask: $('#' + subTaskFormId + ' #assignToSubTask').val(),
                sub_task_submit: true // Add a flag to check in PHP
            };

            $.ajax({
                url: '', // Use the current page URL
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Hide the alert if it exists
                        $('#task-alert').remove();

                        // Append the new sub-task to the sub-task list
                        var subTask = response.subTask;
                        var subTaskHtml = `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div style="max-width: 80%;">
                                    <strong>${subTask.sub_task_description}</strong>
                                    <small class="text-muted d-block">
                                        Assigned to: ${subTask.assigned_to}
                                    </small>
                                </div>
                                <span class="badge bg-warning text-dark">
                                    ${subTask.status}
                                </span>
                            </li>`;
                        $('.' + subTasksListClass + ' ul.list-group').append(subTaskHtml);
                        $('#' + subTaskFormId)[0].reset(); // Reset the form
                    } else {
                        alert('Failed to assign the sub-task: ' + response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while assigning the sub-task.');
                }
            });
        });
    });
}

function requestApproval(approvalRequestFormId, approvalRequestsListClass) {
    $(document).ready(function () {
        $('#' + approvalRequestFormId).on('submit', function (e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = {
                approvalDescription: $('#' + approvalRequestFormId + ' #approvalDescription').val(),
                requestRecipient: $('#' + approvalRequestFormId + ' input[name="requestRecipient"]:checked').val(),
                approval_request_submit: true // Add a flag to check in PHP
            };

            $.ajax({
                url:'', // Use the current page URL
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#aproval-alert').remove();
                        // Append the new approval request to the list
                        var request = response.approvalRequest;
                        var requestHtml = `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div style="max-width: 80%;">
                                    <strong>${request.request_description}</strong>
                                    <small class="text-muted d-block">
                                        Requested by: ${request.requested_by} to: ${request.requested_to}
                                    </small>
                                </div>
                                <span class="badge badge-pill ${request.status === 'Approved' ? 'bg-success' : (request.status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary')}">
                                    ${request.status}
                                </span>
                            </li>`;
                        $('.' + approvalRequestsListClass + ' ul.list-group').append(requestHtml);
                        $('#' + approvalRequestFormId)[0].reset(); // Reset the form
                    } else {
                        alert('Failed to submit the approval request: ' + response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while submitting the approval request.');
                }
            });
        });
    });
}
function submitSolutionForm(formId, containerId) {
    $(document).ready(function () {
        $(`#${formId}`).on('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '', // Replace with the correct path to your PHP handler
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Debugging: Log the response
                        console.log('Response:', response);

                        // Optionally show a success message or perform additional actions
                        alert('Solution updated successfully.');

                        // Reload the page after success
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while updating the solution.');
                }
            });
        });
    });
}

    function submitAssignForm(formId, assignedToId) {

        $(`#${formId}`).on('submit', function (e) {
            e.preventDefault(); // Prevent the form from submitting normally
     
            var formData = new FormData(this);
     
            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    console.log('AJAX success:', response); // Debugging log
     
                    if (response.status === 'success') {
                        $(`#${assignedToId}`).text(response.assigned_to_name);
                      
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX error:', status, error);
                    alert('An error occurred while assigning the ticket.');
                }
            });
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        const forceCloseButton = document.querySelector('#force-close-button');
        
        if (forceCloseButton) {
            forceCloseButton.addEventListener('click', function() {
                const ticketId = document.querySelector('#ticket-id').value; // Ensure you have the ticket ID
    
                if (ticketId) {
                    forceCloseTicket(ticketId);
                } else {
                    alert('Ticket ID not found.');
                }
            });
        }
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-attachment-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission
    
                const formData = new FormData(this);
                formData.append('action', 'delete_attachment'); // Add an action to differentiate AJAX requests
    
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If successful, remove the attachment item from the DOM
                        this.closest('.attachment-item').remove();
                       
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

      
    });
  
    document.addEventListener('DOMContentLoaded', function() {
        // Function to get query parameters from the URL
        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }
    
        const ticketId = getQueryParam('id'); // Get the ticket_id from URL
    
        if (!ticketId) {
            console.error('Error: Ticket ID not found in URL.');
            return; // Stop execution if the ticket ID is missing
        }
    
        function fetchTicketHistory() {
            const formData = new FormData();
            formData.append('action', 'get_ticket_history');
            formData.append('ticket_id', ticketId);
    
            fetch('', { // '' refers to the current page
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const historyContainer = document.querySelector('#history-items');
                historyContainer.innerHTML = ''; // Clear existing history
    
                if (data.length > 0) {
                    data.forEach(history => {
                        const historyItem = document.createElement('div');
                        historyItem.classList.add('history-item', 'rounded', 'shadow-sm');
                        historyItem.innerHTML = `
                            <p><strong>Status:</strong> 
                                <span class="badge ${history.statusBadgeClass}">
                                    ${history.status}
                                </span>
                            </p>
                            <p class="text-muted"><small>Changed by ${history.changed_by} on ${history.changed_at}</small></p>
                        `;
                        historyContainer.appendChild(historyItem);
                    });
                } else {
                    historyContainer.innerHTML = '<p>No history available for this ticket.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        }
    

    
        // Fetch history after resolving or force closing
        const resolveButton = document.querySelector('#ResolveBtn');
     
    
        if (resolveButton) {
            resolveButton.addEventListener('click', function() {
                // You might want to add a delay or wait for the action to complete
                fetchTicketHistory();
            });
        }
    
        
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('changeTeamForm');
    
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission
    
            const formData = new FormData(form);
            
            fetch(window.location.href, { // Assuming the PHP code is on the same page
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload the page after successful submission
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const forwardForm = document.getElementById('forwardForm');
        const forwardMainCampusForm = document.getElementById('forwardMainCampusForm');
    
        // Handle general and main campus forward form submissions
        [forwardForm, forwardMainCampusForm].forEach(function(form) {
            if (form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission
    
                    const formData = new FormData(form);
    
                    fetch(window.location.href, { // Assuming the PHP handler is on the same page
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload(); // Reload page after successful forward
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
        });
    });
    $(document).ready(function() {
        $('button[name="response"]').on('click', function(e) {
            e.preventDefault();
    
            // Get ticket ID and action from the clicked button
            var ticket_id = $('input[name="ticket_id"]').val();
            var action = $(this).val();
    
            // Send AJAX request
            $.ajax({
                url: '', // The current file (or specify if needed)
                type: 'POST',
                data: {
                    ticket_id: ticket_id,
                    response: action
                },
                dataType: 'json',
                success: function(response) {
                    // Store the message and type in sessionStorage to show after reload
                    sessionStorage.setItem('alertMessage', response.message);
                    sessionStorage.setItem('alertType', response.status === 'success' ? 'alert-success' : 'alert-danger'); // Bootstrap success or error class
                    location.reload(); // Reload the page
                },
                error: function(xhr, status, error) {
                    sessionStorage.setItem('alertMessage', 'An error occurred while processing the request.');
                    sessionStorage.setItem('alertType', 'alert-danger'); // Bootstrap error class
                    location.reload(); // Reload the page
                }
            });
        });
    
        // Show alert after page reload if there is a message stored in sessionStorage
        var alertMessage = sessionStorage.getItem('alertMessage');
        var alertType = sessionStorage.getItem('alertType'); // This could be 'alert-success' or 'alert-danger'
    
        if (alertMessage) {
            var alertDiv = $('#response-alert');
            $('#alert-message').text(alertMessage); // Set the text of the alert message span
            alertDiv.removeClass('d-none'); // Show the div
            alertDiv.addClass(alertType); // Add success or error class
    
            sessionStorage.removeItem('alertMessage'); // Clear the stored message after showing it
            sessionStorage.removeItem('alertType');    // Clear the stored type as well
        }
    });
    