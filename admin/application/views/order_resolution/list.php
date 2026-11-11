<?php $this->load->view('common/fbc-user/header'); ?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }

.badge-action { font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; display: inline-block; vertical-align: middle; margin: 0; }
.badge-action-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-action-denied { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.badge-action-completed { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
.badge-action-pending { background-color: #ffc107 !important; color: #212529 !important; border: 1px solid #e0a800 !important; }

/* Responsive table & overview optimization */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 25px;
    position: relative;
}
.table-resolution-list {
    width: 100% !important;
    min-width: 860px;
    margin-bottom: 0 !important;
}
.table-resolution-list th,
.table-resolution-list td {
    vertical-align: middle !important;
    padding: 10px 8px !important;
}
.table-resolution-list thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap !important;
    background-color: #f8f9fa;
    color: #495057;
}

/* Sticky Action Column (Persistently Visible) */
.sticky-action-col {
    position: sticky !important;
    right: 0 !important;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0, 0, 0, 0.12) !important;
    z-index: 2 !important;
    white-space: nowrap !important;
    width: 120px;
}
thead th.sticky-action-col {
    background-color: #f1f3f5 !important;
    z-index: 3 !important;
}
tbody tr:hover td.sticky-action-col {
    background-color: #f8f9fa !important;
}

/* Detail disclosure row styling */
.detail-row td {
    background-color: #fbfcfd !important;
}
.detail-disclosure-card {
    margin: 6px 12px 14px 12px;
    padding: 14px 18px;
    background: #ffffff;
    border-left: 4px solid #17a2b8;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    text-align: left;
}
.btn-detail-toggle[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}
.toggle-icon {
    transition: transform 0.2s ease-in-out;
}

/* Custom horizontal scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="content-main form-dashboard">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Order Resolution Tickets (@Help)</h1>
            <div>
                <a href="<?= base_url('order_resolution/acct'); ?>" class="btn btn-outline-info btn-sm">
                    <i class="fa fa-calculator"></i> View Accounting Assigned (@Acct)
                </a>
            </div>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Status Filter Nav Pills -->
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link <?= empty($status_filter) ? 'active' : ''; ?>" href="<?= base_url('order_resolution'); ?>">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'Open') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=Open'); ?>">Open</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'Processing') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=Processing'); ?>">Processing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'Done') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=Done'); ?>">Done</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'Close') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=Close'); ?>">Close</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'ReOpen') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=ReOpen'); ?>">ReOpen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($status_filter === 'Close (Final)') ? 'active' : ''; ?>" href="<?= base_url('order_resolution?status=Close+(Final)'); ?>">Close (Final)</a>
            </li>
        </ul>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center table-resolution-list">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 110px;">Ticket #</th>
                        <th style="min-width: 180px;">Order / Item</th>
                        <th style="min-width: 120px;">Shopper</th>
                        <th style="min-width: 130px;">Merchant</th>
                        <th style="width: 115px;">Type & Priority</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 75px;">Assigned</th>
                        <th class="sticky-action-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resolutions)): ?>
                        <?php foreach ($resolutions as $res): 
                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                            $shopper_name = trim(($res->first_name ?? '') . ' ' . ($res->last_name ?? '')) ?: ($res->customer_email ?: 'Shopper');
                            $view_url = base_url('order_resolution/view/' . $res->ticket_number . '?as=help');
                            $mAction = trim($res->merchant_action ?? '');
                            $has_mAction = (!empty($mAction) && !in_array(strtolower($mAction), ['none', 'n/a'], true));
                            $bClass = 'badge-action-completed';
                            if (strpos($mAction, 'approved') !== false) {
                                $bClass = 'badge-action-approved';
                            } elseif (strpos($mAction, 'denied') !== false) {
                                $bClass = 'badge-action-denied';
                            }
                            $p_badge = 'badge-secondary';
                            if ($res->priority === 'High') {
                                $p_badge = 'badge-danger';
                            } elseif ($res->priority === 'Medium') {
                                $p_badge = 'badge-warning';
                            } elseif ($res->priority === 'Low') {
                                $p_badge = 'badge-info';
                            }
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= $view_url; ?>" class="font-weight-bold text-primary text-nowrap" title="Open ticket">
                                        <?= htmlspecialchars($res->ticket_number); ?>
                                    </a>
                                    <div class="small text-muted text-nowrap"><?= date('d M Y', $res->created_at); ?></div>
                                </td>
                                <td class="text-left">
                                    <div class="font-weight-bold text-dark">#<?= htmlspecialchars($res->order_number); ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($res->product_name ?: 'All Items'); ?>">
                                        <i class="fa fa-cube text-secondary"></i> <?= htmlspecialchars($res->product_name ?: 'All Items'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span title="<?= htmlspecialchars($res->customer_email ?: ''); ?>"><?= htmlspecialchars($shopper_name); ?></span>
                                </td>
                                <td><?= htmlspecialchars($res->merchant_name ?: 'N/A'); ?></td>
                                <td>
                                    <div><span class="badge badge-secondary"><?= htmlspecialchars($res->category); ?></span></div>
                                    <div class="mt-1"><span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($res->priority ?: 'Medium'); ?></span></div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge-status-<?= $status_slug; ?>">
                                            <?= htmlspecialchars($res->status); ?>
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <?php if ($has_mAction): ?>
                                            <span class="badge-action <?= $bClass; ?>">
                                                <?= ucwords(str_replace('_', ' ', $mAction)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-action badge-action-pending badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($res->assigned_role === 'Acct') ? 'warning' : 'primary'; ?>">
                                        @<?= htmlspecialchars($res->assigned_role); ?>
                                    </span>
                                </td>
                                <td class="sticky-action-col">
                                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                        <a href="<?= $view_url; ?>" class="btn btn-sm btn-info text-nowrap" title="Open and manage ticket">
                                            <i class="fa fa-eye"></i> Manage
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-toggle" data-toggle="collapse" data-target="#detail-<?= $res->id; ?>" aria-expanded="false" title="Toggle full details">
                                            <i class="fa fa-chevron-down toggle-icon"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Secondary Detail Disclosure Row -->
                            <tr id="detail-<?= $res->id; ?>" class="collapse detail-row">
                                <td colspan="8" class="p-0 border-top-0">
                                    <div class="detail-disclosure-card">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="small text-muted font-weight-bold">Shopper Details:</div>
                                                <div><?= htmlspecialchars($shopper_name); ?></div>
                                                <?php if (!empty($res->customer_email)): ?>
                                                    <div class="small text-muted"><i class="fa fa-envelope-o"></i> <?= htmlspecialchars($res->customer_email); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted font-weight-bold">Merchant Information:</div>
                                                <div><?= htmlspecialchars($res->merchant_name ?: 'N/A'); ?></div>
                                                <div class="small text-muted">Merchant Status: <?= $has_mAction ? ucwords(str_replace('_', ' ', $mAction)) : 'Pending'; ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted font-weight-bold">Item & Resolution Details:</div>
                                                <div><strong>Product:</strong> <?= htmlspecialchars($res->product_name ?: 'All Items'); ?></div>
                                                <div class="small text-muted">Category: <?= htmlspecialchars($res->category); ?> | Priority: <?= htmlspecialchars($res->priority ?: 'Medium'); ?></div>
                                                <?php if ($res->refund_amount > 0): ?>
                                                    <div class="small text-success font-weight-bold">Refund: MUR <?= number_format($res->refund_amount, 2); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-3 text-right">
                                                <div class="small text-muted">Created: <?= date('d M Y, h:i A', $res->created_at); ?></div>
                                                <div class="mt-2">
                                                    <a href="<?= $view_url; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-external-link"></i> Manage Ticket #<?= htmlspecialchars($res->ticket_number); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No Order Resolution tickets found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
