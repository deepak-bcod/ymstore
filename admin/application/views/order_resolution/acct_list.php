<?php 
$currency = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
$this->load->view('common/fbc-user/header'); 
?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

/* Responsive table & overview optimization */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 25px;
    position: relative;
}
.table-acct-resolution-list {
    width: 100% !important;
    min-width: 860px;
    margin-bottom: 0 !important;
}
.table-acct-resolution-list th,
.table-acct-resolution-list td {
    vertical-align: middle !important;
    padding: 10px 8px !important;
}
.table-acct-resolution-list thead th {
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
    border-left: 4px solid #ffc107;
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
            <h1 class="h2"><i class="fa fa-calculator text-warning"></i> Assigned Order Resolution (@Acct)</h1>
            <div>
                <a href="<?= base_url('order_resolution'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-list"></i> View All Tickets (@Help)
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

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center table-acct-resolution-list">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 110px;">Ticket #</th>
                        <th style="min-width: 180px;">Order / Item</th>
                        <th style="min-width: 120px;">Shopper</th>
                        <th style="min-width: 130px;">Merchant</th>
                        <th style="width: 80px;">Priority</th>
                        <th style="min-width: 130px;">Action Required</th>
                        <th style="width: 110px;">Refund / Cost</th>
                        <th style="width: 100px;">Status</th>
                        <th class="sticky-action-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resolutions)): ?>
                        <?php foreach ($resolutions as $res): 
                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                            $shopper_name = trim(($res->first_name ?? '') . ' ' . ($res->last_name ?? '')) ?: ($res->customer_email ?: 'Shopper');
                            $view_url = base_url('order_resolution/view/' . $res->ticket_number . '?as=acct');
                            $guidance = $this->OrderResolutionModel->get_next_action_guidance($res);
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
                                    <a href="<?= $view_url; ?>" class="font-weight-bold text-primary text-nowrap" title="Process ticket">
                                        <?= htmlspecialchars($res->ticket_number); ?>
                                    </a>
                                    <div class="small text-muted text-nowrap"><?= htmlspecialchars($guidance['last_activity']); ?></div>
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
                                    <span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($res->priority ?: 'Medium'); ?></span>
                                </td>
                                <td>
                                    <?php if ($res->delivery_option === 'ym_delivery'): ?>
                                        <span class="badge badge-info p-1">YM Delivery Service</span>
                                    <?php else: ?>
                                        <span class="badge badge-success p-1">Refund Processing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= ($res->refund_amount > 0) ? $currency . ' ' . number_format($res->refund_amount, 2) : 'N/A'; ?></strong>
                                </td>
                                <td>
                                    <span class="badge-status-<?= $status_slug; ?>">
                                        <?= htmlspecialchars($res->status); ?>
                                    </span>
                                    <div class="mt-1">
                                        <span class="badge <?= $guidance['badge_class']; ?> text-nowrap" style="font-size: 11px;">
                                            <i class="fa fa-user-circle"></i> Next: <?= htmlspecialchars($guidance['actor']); ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($guidance['action']); ?>">
                                        <i class="fa fa-info-circle text-secondary"></i> <?= htmlspecialchars($guidance['short_action']); ?>
                                    </div>
                                </td>
                                <td class="sticky-action-col">
                                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                        <a href="<?= $view_url; ?>" class="btn btn-sm btn-warning text-nowrap" title="Process ticket">
                                            <i class="fa fa-pencil"></i> Process
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-toggle" data-toggle="collapse" data-target="#acct-detail-<?= $res->id; ?>" aria-expanded="false" title="Toggle full details">
                                            <i class="fa fa-chevron-down toggle-icon"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Secondary Detail Disclosure Row -->
                            <tr id="acct-detail-<?= $res->id; ?>" class="collapse detail-row">
                                <td colspan="9" class="p-0 border-top-0">
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
                                                <div class="small text-muted font-weight-bold">Merchant Details:</div>
                                                <div><?= htmlspecialchars($res->merchant_name ?: 'N/A'); ?></div>
                                                <div class="small text-muted">Merchant Status: <?= ucwords(str_replace('_', ' ', $res->merchant_action ?? 'Pending')); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted font-weight-bold">Order & Financials:</div>
                                                <div><strong>Item:</strong> <?= htmlspecialchars($res->product_name ?: 'All Items'); ?></div>
                                                <div><strong>Refund:</strong> <?= ($res->refund_amount > 0) ? $currency . ' ' . number_format($res->refund_amount, 2) : 'N/A'; ?></div>
                                                <div class="small text-muted">Delivery Option: <?= htmlspecialchars($res->delivery_option ?: 'none'); ?></div>
                                            </div>
                                            <div class="col-md-3 text-right">
                                                <div class="small text-muted">Created: <?= date('d M Y, h:i A', $res->created_at); ?></div>
                                                <div class="mt-2">
                                                    <a href="<?= $view_url; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fa fa-pencil"></i> Process Ticket #<?= htmlspecialchars($res->ticket_number); ?>
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
                            <td colspan="9" class="text-center py-4 text-muted">
                                No tickets currently assigned to Accounting (@Acct).
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
