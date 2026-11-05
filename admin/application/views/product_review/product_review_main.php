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

<script type="text/javascript" src="<?php echo SKIN_JS; ?>project_review.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
