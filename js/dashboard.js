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
                if (response.status === 'success') {
                    // Store the message and type in sessionStorage to show after reload
                    sessionStorage.setItem('alertMessage', response.message);
                    sessionStorage.setItem('alertType', 'alert-success'); // Bootstrap success class
                    location.reload(); // Reload the page
                } else {
                    sessionStorage.setItem('alertMessage', response.message);
                    sessionStorage.setItem('alertType', 'alert-danger'); // Bootstrap error class
                    location.reload(); // Reload the page
                }
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
$(document).ready(function() {
    $('#myTickets').DataTable({
        "pagingType": "simple", // Use simple pagination with next and previous buttons
        "pageLength": 10, // Number of rows per page
        "lengthChange": false, // Disable the dropdown for changing the number of entries
        "language": {
            "emptyTable": "You Have no Tickets.",
            "info": "Page _PAGE_ of _PAGES_",
            "infoEmpty": "No entries available",
            "paginate": {
                "previous": "<i class='fas fa-chevron-left'></i>", // Left arrow
                "next": "<i class='fas fa-chevron-right'></i>" // Right arrow
            }
        }
    });
    
    $('#aproval').DataTable({
        "pagingType": "simple", // Use simple pagination with next and previous buttons
        "pageLength": 10, // Number of rows per page
        "lengthChange": false, // Disable the dropdown for changing the number of entries
        "language": {
            "emptyTable": "You Have no Tickets.",
            "info": "Page _PAGE_ of _PAGES_",
            "infoEmpty": "No entries available",
            "paginate": {
                "previous": "<i class='fas fa-chevron-left'></i>", // Left arrow
                "next": "<i class='fas fa-chevron-right'></i>" // Right arrow
            }
        }
    });
});
