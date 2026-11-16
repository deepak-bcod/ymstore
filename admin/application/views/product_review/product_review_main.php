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
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #DataTables_Table_Project_Review_list th,
    #DataTables_Table_Project_Review_list td {
        vertical-align: middle !important;
        white-space: nowrap !important;
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

    /* Switch Toggle Styling for Merchant Badge */
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switch-toggle .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #dc3545;
        transition: .3s;
        border-radius: 26px;
    }
    .switch-toggle .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    .switch-toggle input:checked + .slider {
        background-color: #28a745;
    }
    .switch-toggle input:checked + .slider:before {
        transform: translateX(22px);
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

    <!-- 3 Top-Level Tabs: 1. Merchant Service Reviews, 2. Product Reviews, 3. Merchant Badge Controls -->
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
        <li class="nav-item">
            <a class="nav-link font-weight-bold text-muted" id="tab-merchant-badges" href="javascript:void(0)" onclick="switchReviewCategory('badge', this)" style="padding: 12px 26px; border-radius: 8px 8px 0 0; cursor: pointer;">
                <i class="fa fa-award mr-2" style="color: #f59e0b;"></i> 3. Merchant Badge Controls
                <span class="badge ml-2" id="badge-tab-merchant-badges" style="background:#f59e0b; color:#fff;"><?= $merchant_count ?? 0 ?></span>
            </a>
        </li>
    </ul>

    <!-- Reviews Section (Tabs 1 & 2) -->
    <div id="card-reviews-section">
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
                    <span class="badge badge-light border py-2 px-3 text-secondary mr-2" style="font-size: 13px; font-weight: 600;">
                        Viewing: <span id="indicator-text" class="text-dark font-weight-bold">Merchant Service Reviews</span>
                    </span>
                    * Pending and Rejected reviews are strictly hidden from public store and product pages.
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="padding: 15px;">
                    <?php include('product_review_list.php'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant Badge Controls Section (Tab 3) -->
    <div id="card-merchant-badges-section" style="display: none;">
        <div class="card shadow-sm" style="border: 1px solid #e5e7eb; border-radius: 8px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e5e7eb;">
                <div>
                    <h5 class="mb-0 font-weight-bold" style="font-size: 16px;"><i class="fa fa-award text-warning mr-2"></i> Merchant Trust Badge Front-End Controls</h5>
                    <small class="text-muted">Enable or disable trust badge display on the front-end (Merchant eShop & Product pages) per merchant.</small>
                </div>
                <div class="text-right">
                    <span class="badge badge-light border py-2 px-3 text-secondary" style="font-size: 13px; font-weight: 600;">
                        Total Merchants: <span class="text-dark font-weight-bold"><?= $merchant_count ?? 0 ?></span>
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-hover text-center" id="merchantBadgeTable" style="width: 100%;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th>Trade / Shop Name</th>
                                <th>Merchant Name</th>
                                <th style="width: 160px;">Published Reviews</th>
                                <th style="width: 160px;">Average Rating</th>
                                <th style="width: 160px;">Front-End Badge</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
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
            { data: 'rating' },
            { data: 'status' },
            { data: 'action' },
            { data: 'delete' }
        ],
        'order': [[1, 'desc']],
        'columnDefs': [
            { 'targets': [0, 4, 5], 'orderable': false },
            { 'className': 'text-center', 'targets': [0, 1, 2, 3, 4, 5] }
        ]
    });
}

function switchReviewCategory(category, elem) {
    if (category === 'badge') {
        window.currentTypeFilter = 'badge';
        $('#reviewMainCategoryTabs .nav-link').removeClass('active').addClass('text-muted');
        $('#tab-merchant-badges').addClass('active').removeClass('text-muted');
        $('#card-reviews-section').hide();
        $('#card-merchant-badges-section').show();
        initMerchantBadgeTable();
        return;
    }

    window.currentTypeFilter = category;

    // Show reviews section and hide badge section
    $('#card-merchant-badges-section').hide();
    $('#card-reviews-section').show();

    // Update main tab active classes
    $('#reviewMainCategoryTabs .nav-link').removeClass('active').addClass('text-muted');
    $('#tab-' + category + '-reviews').addClass('active').removeClass('text-muted');

    // Update column headers & indicator across all header elements (including DataTables scroll clone)
    if (category === 'merchant') {
        $('[id="col-rating-header"]').text('Service Rating');
        $('#indicator-text').text('Merchant Service Reviews');
    } else {
        $('[id="col-rating-header"]').text('Product Rating');
        $('#indicator-text').text('Product Reviews');
    }

    // Reset status filter to 'all'
    window.currentStatusFilter = 'all';

    // Uncheck check-all
    $('#ckbCheckAllReviews').prop('checked', false);

    if (reviewTable) {
        reviewTable.ajax.reload();
    }
}

var merchantBadgeTable = null;
function initMerchantBadgeTable() {
    if (merchantBadgeTable) {
        merchantBadgeTable.ajax.reload();
        return;
    }

    merchantBadgeTable = $('#merchantBadgeTable').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'responsive': true,
        'destroy': true,
        'language': {
            searchPlaceholder: "Search by Merchant Name, Trade Name, Email, ID...",
            search: "_INPUT_",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        },
        'iDisplayLength': 50,
        'pageLength': 50,
        'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
        'ajax': {
            'url': BASE_URL + 'ProductReviewController/ajaxmerchantbadgelist',
            'type': 'POST',
            'error': function(xhr, error, thrown) {
                console.error("Merchant Badge DataTable error: ", xhr.responseText);
            }
        },
        'columns': [
            { data: 'trade_name' },
            { data: 'vendor_name' },
            { data: 'published_reviews' },
            { data: 'avg_rating' },
            { data: 'toggle_badge' }
        ],
        'order': [[0, 'asc']],
        'columnDefs': [
            { 'targets': [4], 'orderable': false },
            { 'className': 'text-center', 'targets': [2, 3, 4] },
            { 'className': 'text-left', 'targets': [0, 1] }
        ]
    });
}

function toggleMerchantBadge(merchantId, isChecked) {
    var newStatus = isChecked ? 1 : 0;
    var labelElem = $('#label_merchant_' + merchantId);

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'ProductReviewController/toggle_merchant_badge',
        dataType: 'json',
        data: {
            merchant_id: merchantId,
            is_badge_enabled: newStatus
        },
        success: function(response) {
            if (response.flag == 1) {
                if (newStatus === 1) {
                    labelElem.text('Enabled').css('color', '#28a745');
                } else {
                    labelElem.text('Disabled').css('color', '#dc3545');
                }
                if (typeof swal !== 'undefined') {
                    swal({
                        title: newStatus ? "Badge Enabled!" : "Badge Disabled!",
                        text: response.msg,
                        icon: "success",
                        timer: 1500,
                        buttons: false
                    });
                }
            } else {
                $('#switch_merchant_' + merchantId).prop('checked', !isChecked);
                if (typeof swal !== 'undefined') {
                    swal("Error", response.msg || "Failed to update badge status.", "error");
                } else {
                    alert(response.msg || "Failed to update badge status.");
                }
            }
        },
        error: function(xhr) {
            $('#switch_merchant_' + merchantId).prop('checked', !isChecked);
            alert("An error occurred while updating merchant badge status.");
        }
    });
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

                if (fromModal && window.activeReviewType && window.activeReviewId) {
                    viewReviewDetails(window.activeReviewType, window.activeReviewId);
                } else if (fromModal && window.activeParentReviewId) {
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

function viewReviewDetails(type, reviewId) {
    window.activeReviewType = type;
    window.activeReviewId = reviewId;
    window.activeParentReviewId = reviewId;

    var defaultTitle = (type === 'merchant')
        ? '<i class="fa fa-store text-info mr-2"></i> Merchant Service Review'
        : '<i class="fa fa-box mr-2" style="color: #6f42c1;"></i> Product Review';

    $('#reviewDetailsLabel').html(defaultTitle);

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'ProductReviewController/viewOrderReviews',
        dataType: 'json',
        data: { 
            type: type,
            review_id: reviewId 
        },
        beforeSend: function() {
            $('#pro-review-popup').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Loading review details...</p></div>');
            $('#review_details').modal('show');
        },
        success: function(response) {
            if (response.flag == 1) {
                if (response.modal_title) {
                    $('#reviewDetailsLabel').html(response.modal_title);
                }
                $('#pro-review-popup').html(response.data);
            } else {
                $('#pro-review-popup').html('<div class="alert alert-danger">' + (response.msg || 'Failed to load details') + '</div>');
            }
        },
        error: function(xhr) {
            var err = 'Error fetching review details.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.msg) {
                err = xhr.responseJSON.msg;
            } else if (xhr && xhr.responseText && xhr.responseText.length < 500) {
                err = xhr.responseText;
            }
            $('#pro-review-popup').html('<div class="alert alert-danger">' + err + '</div>');
        }
    });
}

function viewOrderReviews(parentReviewId) {
    viewReviewDetails('merchant', parentReviewId);
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
