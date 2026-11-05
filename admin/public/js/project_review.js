var reviewTable = null;
window.currentStatusFilter = 'all';
window.currentTypeFilter = 'all';
window.activeParentReviewId = null;

$(document).ready(function() {
    initReviewDataTable();

    // Select all checkbox
    $('#ckbCheckAllReviews').on('click', function() {
        $('input[name="chk_review[]"]').prop('checked', $(this).prop('checked'));
    });
});

function initReviewDataTable() {
    if ($.fn.dataTable.isDataTable('#DataTables_Table_Project_Review_list')) {
        $('#DataTables_Table_Project_Review_list').DataTable().destroy();
    }

    reviewTable = $('#DataTables_Table_Project_Review_list').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'language': {
            search: "",
            searchPlaceholder: "Search reviews, orders, customers...",
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        },
        'iDisplayLength': 50,
        'pageLength': 50,
        'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        'ajax': {
            'url': BASE_URL + 'ProductReviewController/ajaxreviewlist',
            'type': 'POST',
            'data': function(d) {
                d.status_filter = window.currentStatusFilter || 'all';
                d.type_filter = window.currentTypeFilter || 'all';
            }
        },
        'columns': [
            { data: 'id' },
            { data: 'order_id' },
            { data: 'type' },
            { data: 'target_name' },
            { data: 'customer_name' },
            { data: 'email_id' },
            { data: 'rating' },
            { data: 'review' },
            { data: 'created_at' },
            { data: 'status' },
            { data: 'action' },
            { data: 'view_url' },
            { data: 'delete' }
        ],
        'order': [[8, 'desc']],
        'columnDefs': [
            { 'targets': [0, 10, 11, 12], 'orderable': false },
            { 'className': 'text-center', 'targets': [0, 1, 2, 6, 9, 10, 11, 12] }
        ]
    });
}

function filterByStatus(status, elem) {
    window.currentStatusFilter = status;
    $('#reviewStatusTabs .nav-link').removeClass('active');
    $(elem).addClass('active');

    if (reviewTable) {
        reviewTable.ajax.reload();
    }
}

function filterByType(type) {
    window.currentTypeFilter = type;
    if (reviewTable) {
        reviewTable.ajax.reload();
    }
}

function changeReviewStatus(type, id, status, fromModal) {
    var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'ProductReviewController/change_reviews_status',
        dataType: 'json',
        data: {
            type: type,
            id: id,
            status: status
        },
        beforeSend: function() {
            if (typeof $('#ajax-spinner') !== 'undefined') {
                $('#ajax-spinner').show();
            }
        },
        success: function(response) {
            if (typeof $('#ajax-spinner') !== 'undefined') {
                $('#ajax-spinner').hide();
            }

            if (response.flag == 1) {
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Updated!",
                        text: response.msg,
                        icon: "success",
                        timer: 1600,
                        buttons: false
                    });
                } else {
                    alert(response.msg);
                }

                if (reviewTable) {
                    reviewTable.ajax.reload(null, false);
                }

                // If updated from within the detail modal, reload modal data
                if (fromModal && window.activeParentReviewId) {
                    viewOrderReviews(window.activeParentReviewId);
                }
            } else {
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Error",
                        text: response.msg || "Could not update status.",
                        icon: "error"
                    });
                } else {
                    alert(response.msg || "Could not update status.");
                }
            }
        },
        error: function() {
            if (typeof $('#ajax-spinner') !== 'undefined') {
                $('#ajax-spinner').hide();
            }
            alert('An error occurred while communicating with the server.');
        }
    });
}

function viewOrderReviews(parentReviewId) {
    window.activeParentReviewId = parentReviewId;

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'ProductReviewController/viewOrderReviews',
        dataType: 'json',
        data: { review_id: parentReviewId },
        beforeSend: function() {
            $('#pro-review-popup').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Loading review details...</p></div>');
            $('#review_details').modal('show');
        },
        success: function(response) {
            if (response.flag == 1) {
                $('#pro-review-popup').html(response.data);
            } else {
                $('#pro-review-popup').html('<div class="alert alert-danger">' + (response.msg || 'Failed to load details') + '</div>');
            }
        },
        error: function() {
            $('#pro-review-popup').html('<div class="alert alert-danger">Error fetching review details.</div>');
        }
    });
}

function deleteReviewItem(type, id) {
    if (confirm("Are you sure you want to delete this " + type + " review?")) {
        $.ajax({
            type: 'POST',
            url: BASE_URL + 'ProductReviewController/delete_review',
            dataType: 'json',
            data: { type: type, id: id },
            success: function(response) {
                if (response.flag == 1) {
                    if (typeof swal !== 'undefined') {
                        swal({ title: "", text: response.msg, icon: "success", timer: 1500, buttons: false });
                    }
                    if (reviewTable) {
                        reviewTable.ajax.reload(null, false);
                    }
                } else {
                    alert(response.msg || "Failed to delete review.");
                }
            }
        });
    }
}

function bulkAction(status) {
    var checked = $('input[name="chk_review[]"]:checked');
    if (checked.length === 0) {
        if (typeof swal !== 'undefined') {
            swal({ title: "Selection Required", text: "Please select at least one review using the checkboxes.", icon: "warning" });
        } else {
            alert("Please select at least one review.");
        }
        return;
    }

    var reviews = [];
    checked.each(function() {
        reviews.push({
            id: $(this).val(),
            type: $(this).data('type')
        });
    });

    var statusTitle = status.charAt(0).toUpperCase() + status.slice(1);
    if (confirm("Are you sure you want to mark " + reviews.length + " selected review(s) as " + statusTitle + "?")) {
        $.ajax({
            type: 'POST',
            url: BASE_URL + 'ProductReviewController/bulk_update_status',
            dataType: 'json',
            data: {
                reviews: reviews,
                status: status
            },
            success: function(response) {
                if (response.flag == 1) {
                    if (typeof swal !== 'undefined') {
                        swal({ title: "Success", text: response.msg, icon: "success", timer: 1800, buttons: false });
                    } else {
                        alert(response.msg);
                    }
                    $('#ckbCheckAllReviews').prop('checked', false);
                    if (reviewTable) {
                        reviewTable.ajax.reload(null, false);
                    }
                } else {
                    alert(response.msg || "Bulk action failed.");
                }
            }
        });
    }
}
