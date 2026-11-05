<?php $this->load->view('common/fbc-user/header'); ?>

<style>
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
        min-width: 1450px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #DataTables_Table_Project_Review_list th,
    #DataTables_Table_Project_Review_list td {
        vertical-align: middle !important;
        white-space: nowrap !important;
    }
    #DataTables_Table_Project_Review_list th:nth-child(8),
    #DataTables_Table_Project_Review_list td:nth-child(8) {
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

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="head-name" style="margin-bottom: 5px;">Review Management</h1>
            <p class="text-muted" style="margin-bottom: 0;">Moderate merchant customer service ratings and individual product reviews independently.</p>
        </div>
    </div>

    <!-- Status Filter Tabs & Type Selector -->
    <div class="row mb-3 align-items-center" style="margin-bottom: 20px;">
        <div class="col-md-8">
            <ul class="nav nav-pills" id="reviewStatusTabs" style="gap: 8px;">
                <li class="nav-item">
                    <a class="nav-link active" href="javascript:void(0)" onclick="filterByStatus('all', this)" style="border-radius: 20px; font-weight: 600; padding: 8px 18px;">
                        All <span class="badge badge-secondary ml-1"><?= $status_counts['all'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="javascript:void(0)" onclick="filterByStatus('pending', this)" style="border-radius: 20px; font-weight: 600; padding: 8px 18px;">
                        <i class="fa fa-clock"></i> Pending <span class="badge badge-warning ml-1" style="background:#ffc107;color:#212529;"><?= $status_counts['pending'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-success" href="javascript:void(0)" onclick="filterByStatus('published', this)" style="border-radius: 20px; font-weight: 600; padding: 8px 18px;">
                        <i class="fa fa-check-circle"></i> Published <span class="badge badge-success ml-1" style="background:#28a745;"><?= $status_counts['published'] ?? 0 ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="javascript:void(0)" onclick="filterByStatus('rejected', this)" style="border-radius: 20px; font-weight: 600; padding: 8px 18px;">
                        <i class="fa fa-times-circle"></i> Rejected <span class="badge badge-danger ml-1" style="background:#dc3545;"><?= $status_counts['rejected'] ?? 0 ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-4 text-right">
            <div class="d-inline-flex align-items-center" style="gap: 10px;">
                <label for="reviewTypeFilter" class="mb-0 text-muted" style="font-weight: 600; white-space: nowrap;">Type:</label>
                <select id="reviewTypeFilter" class="form-control form-control-sm" onchange="filterByType(this.value)" style="width: auto; min-width: 170px; display: inline-block;">
                    <option value="all">All Review Types</option>
                    <option value="merchant">Merchant Service Only</option>
                    <option value="product">Product Reviews Only</option>
                </select>
            </div>
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
