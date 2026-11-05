<?php $this->load->view('common/fbc-user/header'); ?>

<style>
    /* Tab Navigation for Review Categories */
    #reviewMainCategoryTabs .nav-link {
        color: #4b5563;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-bottom: none;
        margin-right: 6px;
        transition: all 0.2s ease-in-out;
    }
    #reviewMainCategoryTabs .nav-link:hover {
        background: #ffffff;
        color: #007bff;
    }
    #reviewMainCategoryTabs .nav-link.active {
        color: #1e293b !important;
        background: #ffffff !important;
        border-color: #cbd5e1 #cbd5e1 #ffffff !important;
        border-top: 3px solid #ff9e0c !important;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.03);
    }

    /* Horizontal scroller styling for Review Management */
    .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .dataTables_wrapper .dataTables_scroll {
        width: 100%;
    }
    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
        width: 100% !important;
        border-bottom: 1px solid #dee2e6;
    }
    #DataTables_Table_Project_Review_list {
        min-width: 1350px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #DataTables_Table_Project_Review_list th,
    #DataTables_Table_Project_Review_list td {
        vertical-align: middle !important;
        white-space: nowrap !important;
    }
    #DataTables_Table_Project_Review_list th:nth-child(7),
    #DataTables_Table_Project_Review_list td:nth-child(7) {
        white-space: normal !important;
        min-width: 220px !important;
        max-width: 320px !important;
    }

    /* Custom visible horizontal scrollbar */
    .dataTables_scrollBody::-webkit-scrollbar,
    .table-responsive::-webkit-scrollbar {
        height: 10px;
    }
    .dataTables_scrollBody::-webkit-scrollbar-track,
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 6px;
    }
    .dataTables_scrollBody::-webkit-scrollbar-thumb,
    .table-responsive::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 6px;
    }
    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover,
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #6c757d;
    }
</style>

<script>
    window.reviewStatusCounts = <?= json_encode($status_counts); ?>;
</script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="head-name" style="margin-bottom: 5px;">Review Management</h1>
            <p class="text-muted" style="margin-bottom: 0;">Moderate merchant customer service ratings and individual product reviews independently.</p>
        </div>
    </div>

    <!-- 2 Top-Level Tabs: 1. Merchant Service Reviews, 2. Product Reviews -->
    <ul class="nav nav-tabs mb-4" id="reviewMainCategoryTabs" style="border-bottom: 2px solid #e2e8f0; font-size: 15px;">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="tab-merchant-reviews" href="javascript:void(0)" onclick="switchReviewCategory('merchant', this)" style="padding: 12px 26px; border-radius: 8px 8px 0 0; cursor: pointer;">
                <i class="fa fa-store text-info mr-2"></i> 1. Merchant Service Reviews
                <span class="badge badge-info ml-2" id="badge-tab-merchant"><?= $status_counts['merchant_all'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold text-muted" id="tab-product-reviews" href="javascript:void(0)" onclick="switchReviewCategory('product', this)" style="padding: 12px 26px; border-radius: 8px 8px 0 0; cursor: pointer;">
                <i class="fa fa-box mr-2" style="color: #6f42c1;"></i> 2. Product Reviews
                <span class="badge ml-2" id="badge-tab-product" style="background:#6f42c1; color:#fff;"><?= $status_counts['product_all'] ?? 0 ?></span>
            </a>
        </li>
    </ul>

    <!-- Status Filter Pills for Active Tab -->
    <div class="row mb-3 align-items-center" style="margin-bottom: 20px;">
        <div class="col-md-8">
            <ul class="nav nav-pills" id="reviewStatusTabs" style="gap: 8px;">
                <li class="nav-item">
                    <a class="nav-link active" href="javascript:void(0)" onclick="filterByStatus('all', this)" style="border-radius: 20px; font-weight: 600; padding: 7px 18px;">
                        All <span class="badge badge-secondary ml-1" id="badge-status-all"><?= $status_counts['merchant_all'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="javascript:void(0)" onclick="filterByStatus('pending', this)" style="border-radius: 20px; font-weight: 600; padding: 7px 18px;">
                        <i class="fa fa-clock"></i> Pending <span class="badge badge-warning ml-1" id="badge-status-pending" style="background:#ffc107;color:#212529;"><?= $status_counts['merchant_pending'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-success" href="javascript:void(0)" onclick="filterByStatus('published', this)" style="border-radius: 20px; font-weight: 600; padding: 7px 18px;">
                        <i class="fa fa-check-circle"></i> Published <span class="badge badge-success ml-1" id="badge-status-published" style="background:#28a745;"><?= $status_counts['merchant_published'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="javascript:void(0)" onclick="filterByStatus('rejected', this)" style="border-radius: 20px; font-weight: 600; padding: 7px 18px;">
                        <i class="fa fa-times-circle"></i> Rejected <span class="badge badge-danger ml-1" id="badge-status-rejected" style="background:#dc3545;"><?= $status_counts['merchant_rejected'] ?? 0 ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-4 text-right">
            <span class="badge badge-light border py-2 px-3 text-secondary" style="font-size: 13px; font-weight: 600;">
                Viewing: <span id="indicator-text" class="text-dark font-weight-bold">Merchant Service Reviews</span>
            </span>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="card shadow-sm" style="border: 1px solid #e5e7eb; border-radius: 8px;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e5e7eb;">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="bulkAction('published')" style="font-weight: 600;">
                    <i class="fa fa-check"></i> Bulk Publish
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkAction('rejected')" style="font-weight: 600;">
                    <i class="fa fa-times"></i> Bulk Reject
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="bulkAction('pending')" style="font-weight: 600;">
                    <i class="fa fa-clock"></i> Bulk Set Pending
                </button>
            </div>
            <div class="text-muted small">
                * Pending and Rejected reviews are strictly hidden from public store and product pages.
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="padding: 15px;">
                <?php include('product_review_list.php'); ?>
            </div>
        </div>
    </div>
</main>

<!-- MODAL: Order Review Full Detail Popup -->
<div class="modal fade" id="review_details" tabindex="-1" role="dialog" aria-labelledby="reviewDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 850px;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title font-weight-bold" id="reviewDetailsLabel">
                    <i class="fa fa-star text-warning"></i> Order Reviews & Moderation
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="pro-review-popup">
                <!-- Dynamically loaded via AJAX -->
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var reviewTable = null;
window.currentStatusFilter = 'all';
window.currentTypeFilter = 'merchant';
window.activeParentReviewId = null;

$(document).ready(function() {
    initReviewDataTable();

    // Select all checkbox
    $('#ckbCheckAllReviews').on('click', function() {
        $('input[name="chk_review[]"]').prop('checked', $(this).prop('checked'));
    });

    $(window).on('resize', function() {
        if (reviewTable) {
            reviewTable.columns.adjust();
        }
    });

    if (window.reviewStatusCounts) {
        updateStatusCountBadges('merchant');
    }
});

function initReviewDataTable() {
    if ($.fn.dataTable.isDataTable('#DataTables_Table_Project_Review_list')) {
        $('#DataTables_Table_Project_Review_list').DataTable().destroy();
    }

    reviewTable = $('#DataTables_Table_Project_Review_list').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'scrollX': true,
        'scrollCollapse': true,
        'autoWidth': false,
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
                d.type_filter = window.currentTypeFilter || 'merchant';
            },
            'error': function(xhr, error, thrown) {
                console.error("Review DataTable error: ", xhr.responseText);
            }
        },
        'columns': [
            { data: 'id' },
            { data: 'order_id' },
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
        'order': [[7, 'desc']],
        'columnDefs': [
            { 'targets': [0, 9, 10, 11], 'orderable': false },
            { 'className': 'text-center', 'targets': [0, 1, 5, 8, 9, 10, 11] }
        ]
    });
}

function switchReviewCategory(category, elem) {
    window.currentTypeFilter = category;

    // Update main tab active classes
    $('#reviewMainCategoryTabs .nav-link').removeClass('active').addClass('text-muted');
    $('#tab-' + category + '-reviews').addClass('active').removeClass('text-muted');

    // Update column headers & indicator across all header elements (including DataTables scroll clone)
    if (category === 'merchant') {
        $('[id="col-target-header"]').text('Merchant Name');
        $('[id="col-rating-header"]').text('Service Rating');
        $('#indicator-text').text('Merchant Service Reviews');
    } else {
        $('[id="col-target-header"]').text('Product Name');
        $('[id="col-rating-header"]').text('Product Rating');
        $('#indicator-text').text('Product Reviews');
    }

    // Update status badge counts
    updateStatusCountBadges(category);

    // Reset status filter to 'all'
    window.currentStatusFilter = 'all';
    $('#reviewStatusTabs .nav-link').removeClass('active');
    $('#reviewStatusTabs .nav-link').first().addClass('active');

    // Uncheck check-all
    $('#ckbCheckAllReviews').prop('checked', false);

    if (reviewTable) {
        reviewTable.ajax.reload();
    }
}

function updateStatusCountBadges(category) {
    if (!window.reviewStatusCounts) return;
    var counts = window.reviewStatusCounts;
    var prefix = (category === 'product') ? 'product_' : 'merchant_';

    $('#badge-status-all').text(counts[prefix + 'all'] !== undefined ? counts[prefix + 'all'] : 0);
    $('#badge-status-pending').text(counts[prefix + 'pending'] !== undefined ? counts[prefix + 'pending'] : 0);
    $('#badge-status-published').text(counts[prefix + 'published'] !== undefined ? counts[prefix + 'published'] : 0);
    $('#badge-status-rejected').text(counts[prefix + 'rejected'] !== undefined ? counts[prefix + 'rejected'] : 0);

    if (counts.merchant_all !== undefined) $('#badge-tab-merchant').text(counts.merchant_all);
    if (counts.product_all !== undefined) $('#badge-tab-product').text(counts.product_all);
}

function filterByStatus(status, elem) {
    window.currentStatusFilter = status;
    $('#reviewStatusTabs .nav-link').removeClass('active');
    $(elem).addClass('active');

    $('#ckbCheckAllReviews').prop('checked', false);

    if (reviewTable) {
        reviewTable.ajax.reload();
    }
}

function changeReviewStatus(type, id, status, fromModal) {
    $.ajax({
        type: 'POST',
        url: BASE_URL + 'ProductReviewController/change_reviews_status',
        dataType: 'json',
        data: {
            type: type,
            id: id,
            status: status
        },
        success: function(response) {
            if (response.flag == 1) {
                if (response.counts) {
                    window.reviewStatusCounts = response.counts;
                    updateStatusCountBadges(window.currentTypeFilter);
                }
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

                if (fromModal && window.activeParentReviewId) {
                    viewOrderReviews(window.activeParentReviewId);
                }

                if (reviewTable) {
                    reviewTable.ajax.reload(null, false);
                }
            } else {
                alert(response.msg || "Failed to update review status.");
            }
        },
        error: function() {
            alert("Error communicating with server.");
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
                    if (response.counts) {
                        window.reviewStatusCounts = response.counts;
                        updateStatusCountBadges(window.currentTypeFilter);
                    }
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
            type: $(this).data('type') || window.currentTypeFilter
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
                    if (response.counts) {
                        window.reviewStatusCounts = response.counts;
                        updateStatusCountBadges(window.currentTypeFilter);
                    }
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
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
