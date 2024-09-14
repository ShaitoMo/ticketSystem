function fetchSubCategories(path, mainCategoryId, subCategoryElementId, selectedSubCategory) {
    console.log('Fetching sub-categories:', path, mainCategoryId, subCategoryElementId, selectedSubCategory); // Debugging statement
    $.ajax({
        type: 'POST',
        url: path,
        data: {
            action: 'getSubCategories',
            category_id: mainCategoryId
        },
        dataType: 'json',
        success: function(response) {
            console.log('Sub-categories response:', response);
            var subCategoryElement = $(subCategoryElementId);
            subCategoryElement.empty();
            
            // Only append the default option if the element is not '#modal_sub_category'
            if (subCategoryElementId !== '#modal_sub_category') {
                subCategoryElement.append('<option value="">Select Sub-Category</option>');
            }

            $.each(response, function(index, subCategory) {
                subCategoryElement.append('<option value="' + subCategory.id + '">' + subCategory.name + '</option>');
            });

            if (selectedSubCategory) {
                subCategoryElement.val(selectedSubCategory);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

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
                    var commentHtml = '<div class="comment-item' + (isMyComment ? ' my-comment' : '') + '">';
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







function fetchNotifications() {
    $.ajax({
        type: 'POST',
        url: 'header.php', 
        data: { action: 'fetch_notifications' },
        dataType: 'json',
        success: function(response) {
            if (response) {
                $('#notification-badge').text(response.count);
                $('#notification-badge').toggle(response.count > 0);

                var notificationList = $('#notification-list');
                notificationList.empty();
                $.each(response.notifications, function(index, notification) {
                    var item = $('<div>')
                        .addClass('notification-item ' + (notification.status === '0' ? 'unread' : 'read'))
                        .text(notification.message);
                    notificationList.append(item);
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX request failed:', textStatus, errorThrown);
        }
    });
}





