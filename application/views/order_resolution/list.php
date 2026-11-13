<?php $this->load->view('common/header'); ?>

<style>
/* Status and action text badges: transparent background with distinct text colors */
span.badge,
span.label,
[class*="badge-status-"],
[class*="badge-action-"],
.badge-pill {
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
    font-weight: 700 !important;
    display: inline-block;
    vertical-align: middle;
}

.badge-status-open { color: #007bff !important; font-size: 13px; }
.badge-status-processing { color: #17a2b8 !important; font-size: 13px; }
.badge-status-done { color: #28a745 !important; font-size: 13px; }
.badge-status-close { color: #6c757d !important; font-size: 13px; }
.badge-status-reopen { color: #fd7e14 !important; font-size: 13px; }
.badge-status-close-final { color: #343a40 !important; font-size: 13px; }

.label-primary { color: #007bff !important; }
.label-default { color: #6c757d !important; }
.label-success { color: #28a745 !important; }
.label-danger { color: #dc3545 !important; }
.label-warning { color: #b38600 !important; }
.label-info { color: #17a2b8 !important; }

.badge-action { font-size: 12px; font-weight: 700; margin: 0; }
.badge-action-approved { color: #28a745 !important; }
.badge-action-denied { color: #dc3545 !important; }
.badge-action-completed { color: #007bff !important; }
.badge-action-pending { color: #b38600 !important; }

/* Mobile & Tablet layout: Prioritise task above lengthy account navigation (YM-02) */
@media (max-width: 991px) {
    .resolution-page-row {
        display: flex !important;
        flex-direction: column !important;
    }
    .resolution-page-row .resolution-main-content {
        order: 1 !important;
        width: 100% !important;
        float: none !important;
    }
    .resolution-page-row .sidebar {
        order: 2 !important;
        width: 100% !important;
        float: none !important;
        margin-top: 35px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    .resolution-page-row .sidebar::before {
        content: "Account Navigation";
        display: block;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    .resolution-header-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .resolution-btn-wrap {
        text-align: left !important;
    }
}

/* Toggle table vs card view */
.resolution-mobile-cards {
    display: none;
}
.resolution-table-view {
    display: block;
}

@media (max-width: 991px) {
    .resolution-table-view {
        display: none !important;
    }
    .resolution-mobile-cards {
        display: block !important;
    }
}

/* Responsive Table Styling (Desktop / Tablet >= 768px) */
.resolution-table-view .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 25px;
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
.table-resolution-list {
    width: 100% !important;
    min-width: 620px;
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
    border-bottom: 2px solid #dee2e6 !important;
}

/* Sticky Action Column (Persistently visible at all viewports) */
.sticky-action-col {
    position: sticky !important;
    right: 0 !important;
    background-color: #ffffff !important;
    box-shadow: -4px 0 8px -2px rgba(0, 0, 0, 0.12) !important;
    z-index: 2 !important;
    white-space: nowrap !important;
    width: 90px;
    text-align: center;
}
thead th.sticky-action-col {
    background-color: #f1f3f5 !important;
    z-index: 3 !important;
}
tbody tr:hover td.sticky-action-col {
    background-color: #f8f9fa !important;
}

/* Custom horizontal scrollbar */
.resolution-table-view .table-responsive::-webkit-scrollbar {
    height: 7px;
}
.resolution-table-view .table-responsive::-webkit-scrollbar-track {
    background: #f1f3f5;
}
.resolution-table-view .table-responsive::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 4px;
}
.resolution-table-view .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

/* Mobile Responsive Card Styling (< 768px) */
.res-mobile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.res-mobile-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.res-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    background-color: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}
.res-card-ticket {
    font-size: 15px;
    font-weight: 700;
    color: #0284c7;
    text-decoration: none !important;
}
.res-card-ticket:hover {
    color: #0369a1;
    text-decoration: underline !important;
}
.res-card-date {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}
.res-card-body {
    padding: 14px;
}
.res-card-order-row {
    margin-bottom: 8px;
}
.res-card-order-num {
    font-weight: 700;
    color: #1e293b;
    font-size: 13px;
}
.res-card-prod-name {
    font-size: 14px;
    color: #334155;
    font-weight: 500;
    margin-top: 3px;
    word-break: break-word;
}
.res-card-merchant-name {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
}
.res-card-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 10px 0 8px 0;
}
.res-card-merchant-action {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 10px;
    margin-top: 8px;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.res-card-footer {
    padding: 10px 14px 14px 14px;
    background-color: #ffffff;
    border-top: 1px solid #f1f5f9;
}
.res-card-footer .btn-view-card {
    display: block;
    width: 100%;
    font-weight: 600;
    padding: 9px 12px;
    font-size: 14px;
    border-radius: 6px;
    text-align: center;
}
</style>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li class="active"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row resolution-page-row">
            <?php 
            $data['side_tab'] = 'order_resolution';
            $this->load->view('common/profile_sidebar', $data); 
            ?>

            <div class="col-sm-9 col-md-9 resolution-main-content">
                <div class="content-page">
                    <div class="row resolution-header-row" style="margin-bottom: 20px;">
                        <div class="col-xs-12 col-sm-8 col-md-8">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #212529;"><?php echo $this->lang->line('order_resolution_requests') ?: 'Order Resolution Requests'; ?></h1>
                            <div class="text-muted" style="font-size: 13px; margin-top: 4px;">Review your cases and communicate privately with YM Admin</div>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-4 text-right resolution-btn-wrap">
                            <a href="<?php echo base_url('order_resolution/create'); ?>" class="btn btn-info">
                                <i class="fa fa-plus"></i> <?php echo $this->lang->line('raise_resolution_request') ?: 'New Resolution Request'; ?>
                            </a>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <!-- Stat Cards Matching MYP Wireframe -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <div style="background: #fff; border: 1px solid #dce1e5; border-radius: 9px; padding: 14px;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Under review</span>
                                <h3 style="font-size: 22px; font-weight: 700; color: #212529; margin: 6px 0 0 0;"><?= $stats['under_review'] ?? 0; ?></h3>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <div style="background: #fff; border: 1px solid #dce1e5; border-radius: 9px; padding: 14px;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Action required</span>
                                <h3 style="font-size: 22px; font-weight: 700; color: #212529; margin: 6px 0 0 0;"><?= $stats['action_required'] ?? 0; ?></h3>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <div style="background: #fff; border: 1px solid #dce1e5; border-radius: 9px; padding: 14px;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Closed</span>
                                <h3 style="font-size: 22px; font-weight: 700; color: #212529; margin: 6px 0 0 0;"><?= $stats['closed'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Filters & Search Toolbar -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 18px;">
                        <div class="row">
                            <div class="col-sm-8 col-xs-12" style="margin-bottom: 8px;">
                                <input type="text" id="shopperTicketSearch" class="form-control" placeholder="Search ticket, order, or product..." oninput="filterShopperRows()">
                            </div>
                            <div class="col-sm-4 col-xs-12">
                                <select id="shopperWorkflowFilter" class="form-control" onchange="filterShopperRows()">
                                    <option value="">All statuses</option>
                                    <option value="admin_review">Admin Review</option>
                                    <option value="awaiting_shopper">Awaiting Shopper</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop / Tablet Table View (>= 768px) with Persistent Sticky Action -->
                    <div class="resolution-table-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover text-center table-resolution-list">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 110px;"><?php echo $this->lang->line('ticket_no') ?: 'Ticket #'; ?></th>
                                        <th style="min-width: 170px;" class="text-left"><?php echo $this->lang->line('order_number') ?: 'Order / Product'; ?></th>
                                        <th style="width: 110px;"><?php echo $this->lang->line('category') ?: 'Category / Priority'; ?></th>
                                        <th style="width: 160px;"><?php echo $this->lang->line('escalation_status') ?: 'Status / Next'; ?></th>
                                        <th style="width: 110px;"><?php echo $this->lang->line('last_activity') ?: 'Last Activity'; ?></th>
                                        <!--th class="sticky-action-col"><?php echo $this->lang->line('action') ?: 'Action'; ?></th-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($resolutions)): ?>
                                        <?php foreach ($resolutions as $res): 
                                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                            $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $res->status));
                                            $badge_class = 'badge-status-' . $status_slug;
                                            $cat_key     = 'category_' . strtolower(trim($res->category ?? ''));
                                            $pri_key     = 'priority_' . strtolower(trim($res->priority ?? ''));
                                            $view_url    = base_url('order_resolution/view/' . $res->ticket_number);

                                            $mAction = trim($res->merchant_action ?? '');
                                            $has_mAction = (!empty($mAction) && !in_array(strtolower($mAction), ['none', 'n/a'], true));
                                            $bClass = 'badge-action-completed';
                                            if (strpos($mAction, 'approved') !== false) {
                                                $bClass = 'badge-action-approved';
                                            } elseif (strpos($mAction, 'denied') !== false) {
                                                $bClass = 'badge-action-denied';
                                            }

                                            $p_class = 'label-info';
                                            if ($res->priority === 'High') {
                                                $p_class = 'label-danger';
                                            } elseif ($res->priority === 'Medium') {
                                                $p_class = 'label-warning';
                                            }

                                            $guidance = $this->OrderResolutionModel->get_next_action_guidance($res);
                                            if ($guidance['actor_code'] === 'none') {
                                                $wf_key = 'closed';
                                            } elseif ($guidance['actor_code'] === 'shopper') {
                                                $wf_key = 'awaiting_shopper';
                                            } else {
                                                $wf_key = 'admin_review';
                                            }
                                            $search_blob = strtolower($res->ticket_number . ' ' . $res->order_number . ' ' . ($res->product_name ?? '') . ' ' . ($res->merchant_name ?? '') . ' ' . $res->category . ' ' . $res->status . ' ' . $guidance['actor'] . ' ' . $guidance['short_action'] . ' ' . $guidance['action']);
                                        ?>
                                            <tr data-search="<?= htmlspecialchars($search_blob); ?>" data-workflow="<?= $wf_key; ?>">
                                                <td>
                                                    <a href="<?php echo $view_url; ?>" class="font-weight-bold text-primary text-nowrap" title="<?php echo $this->lang->line('view') ?: 'View'; ?>">
                                                        <?php echo htmlspecialchars($res->ticket_number); ?>
                                                    </a>
                                                    <div class="small text-muted text-nowrap"><?php echo date('d M Y', $res->created_at); ?></div>
                                                </td>
                                                <td class="text-left">
                                                    <div class="font-weight-bold text-dark">#<?php echo htmlspecialchars($res->order_number); ?></div>
                                                    <div class="small text-muted text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?>">
                                                        <i class="fa fa-cube text-secondary"></i> <?php echo htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?>
                                                    </div>
                                                    <?php if (!empty($res->merchant_name)): ?>
                                                        <div class="small text-muted text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($res->merchant_name); ?>">
                                                            <i class="fa fa-shopping-bag"></i> <?php echo htmlspecialchars($res->merchant_name); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><span class="label label-default"><?php echo htmlspecialchars($this->lang->line($cat_key) ?: $res->category); ?></span></div>
                                                    <div class="mt-1"><span class="label <?php echo $p_class; ?>"><?php echo htmlspecialchars($this->lang->line($pri_key) ?: ($res->priority ?: 'Medium')); ?></span></div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="<?php echo $badge_class; ?>">
                                                            <?php echo htmlspecialchars($this->lang->line($status_key) ?: $res->status); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="label <?php echo ($guidance['actor_code'] === 'shopper') ? 'label-warning' : (($guidance['actor_code'] === 'merchant') ? 'label-primary' : (($guidance['actor_code'] === 'none') ? 'label-default' : 'label-info')); ?>" style="display: inline-block; font-size: 11px;">
                                                            <i class="fa fa-user-circle"></i> Next: <?php echo htmlspecialchars($guidance['actor']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 160px;" title="<?php echo htmlspecialchars($guidance['action']); ?>">
                                                        <i class="fa fa-info-circle text-secondary"></i> <?php echo htmlspecialchars($guidance['short_action']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small font-weight-bold"><?php echo htmlspecialchars($guidance['last_activity']); ?></div>
                                                </td>
                                                <!--td class="sticky-action-col">
                                                    <a href="<?php echo $view_url; ?>" class="btn btn-sm btn-info text-nowrap" title="<?php echo $this->lang->line('view') ?: 'View'; ?>">
                                                        <i class="fa fa-eye"></i> <?php echo $this->lang->line('view') ?: 'View'; ?>
                                                    </a>
                                                </td-->
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center" style="padding: 30px;">
                                                <p><?php echo $this->lang->line('no_order_resolution_tickets') ?: 'No Order Resolution requests found.'; ?></p>
                                                <a href="<?php echo base_url('customer/my-orders'); ?>" class="btn btn-default">
                                                    <?php echo $this->lang->line('view_my_orders') ?: 'Go to My Orders'; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Cards View (< 768px): Compact, readable, touch-friendly with 0 horizontal scroll -->
                    <div class="resolution-mobile-cards">
                        <?php if (!empty($resolutions)): ?>
                            <?php foreach ($resolutions as $res): 
                                $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $res->status));
                                $badge_class = 'badge-status-' . $status_slug;
                                $cat_key     = 'category_' . strtolower(trim($res->category ?? ''));
                                $pri_key     = 'priority_' . strtolower(trim($res->priority ?? ''));
                                $view_url    = base_url('order_resolution/view/' . $res->ticket_number);

                                $mAction = trim($res->merchant_action ?? '');
                                $has_mAction = (!empty($mAction) && !in_array(strtolower($mAction), ['none', 'n/a'], true));
                                $bClass = 'badge-action-completed';
                                if (strpos($mAction, 'approved') !== false) {
                                    $bClass = 'badge-action-approved';
                                } elseif (strpos($mAction, 'denied') !== false) {
                                    $bClass = 'badge-action-denied';
                                }

                                $p_class = 'label-info';
                                if ($res->priority === 'High') {
                                    $p_class = 'label-danger';
                                } elseif ($res->priority === 'Medium') {
                                    $p_class = 'label-warning';
                                }
                            ?>
                                <?php
                                    $guidance = $this->OrderResolutionModel->get_next_action_guidance($res);
                                    if ($guidance['actor_code'] === 'none') {
                                        $wf_key = 'closed';
                                    } elseif ($guidance['actor_code'] === 'shopper') {
                                        $wf_key = 'awaiting_shopper';
                                    } else {
                                        $wf_key = 'admin_review';
                                    }
                                    $search_blob = strtolower($res->ticket_number . ' ' . $res->order_number . ' ' . ($res->product_name ?? '') . ' ' . ($res->merchant_name ?? '') . ' ' . $res->category . ' ' . $res->status . ' ' . $guidance['actor'] . ' ' . $guidance['short_action'] . ' ' . $guidance['action']);
                                ?>
                                <div class="res-mobile-card" data-search="<?= htmlspecialchars($search_blob); ?>" data-workflow="<?= $wf_key; ?>">
                                    <div class="res-card-header">
                                        <div>
                                            <a href="<?php echo $view_url; ?>" class="res-card-ticket">
                                                <i class="fa fa-ticket"></i> <?php echo htmlspecialchars($res->ticket_number); ?>
                                            </a>
                                            <div class="res-card-date"><i class="fa fa-calendar-o"></i> <?php echo htmlspecialchars($guidance['last_activity']); ?></div>
                                        </div>
                                        <div>
                                            <span class="<?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($this->lang->line($status_key) ?: $res->status); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="res-card-body">
                                        <div class="res-card-order-row">
                                            <span class="res-card-order-num">#<?php echo htmlspecialchars($res->order_number); ?></span>
                                            <div class="res-card-prod-name">
                                                <i class="fa fa-cube text-secondary"></i> <?php echo htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?>
                                            </div>
                                            <?php if (!empty($res->merchant_name)): ?>
                                                <div class="res-card-merchant-name">
                                                    <i class="fa fa-shopping-bag"></i> <?php echo htmlspecialchars($res->merchant_name); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="res-card-tags">
                                            <span><strong><?php echo $this->lang->line('category') ?: 'Category'; ?>:</strong> <span class="label label-default"><?php echo htmlspecialchars($this->lang->line($cat_key) ?: $res->category); ?></span></span>
                                            <span><strong><?php echo $this->lang->line('priority') ?: 'Priority'; ?>:</strong> <span class="label <?php echo $p_class; ?>"><?php echo htmlspecialchars($this->lang->line($pri_key) ?: ($res->priority ?: 'Medium')); ?></span></span>
                                        </div>

                                        <div class="res-card-merchant-action">
                                            <span class="text-muted"><strong><?php echo $this->lang->line('merchant_status') ?: 'Merchant Status'; ?>:</strong></span>
                                            <?php if ($has_mAction): ?>
                                                <span class="badge-action <?php echo $bClass; ?>">
                                                    <?php echo htmlspecialchars($this->lang->line($mAction) ?: ucwords(str_replace('_', ' ', $mAction))); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-action badge-action-pending label label-warning"><?php echo htmlspecialchars($this->lang->line('pending') ?: ($this->lang->line('status_pending') ?: 'Pending')); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Next Responsible Actor & Action (YM-18) -->
                                        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; margin-top: 8px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                                <span class="label <?php echo ($guidance['actor_code'] === 'shopper') ? 'label-warning' : (($guidance['actor_code'] === 'merchant') ? 'label-primary' : (($guidance['actor_code'] === 'none') ? 'label-default' : 'label-info')); ?>" style="font-size: 11px;">
                                                    <i class="fa fa-user-circle"></i> Next: <?php echo htmlspecialchars($guidance['actor']); ?>
                                                </span>
                                                <span class="label label-default" style="font-size: 10px;"><?php echo htmlspecialchars($guidance['short_action']); ?></span>
                                            </div>
                                            <div style="font-size: 12px; color: #334155; margin-top: 3px;">
                                                <i class="fa fa-info-circle text-muted"></i> <?php echo htmlspecialchars($guidance['action']); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!--div class="res-card-footer">
                                        <a href="<?php echo $view_url; ?>" class="btn btn-info btn-view-card">
                                            <i class="fa fa-eye"></i> <?php echo $this->lang->line('view') ?: 'View Ticket Details'; ?>
                                        </a>
                                    </div-->
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 30px; background: #fff; border: 1px solid #dee2e6; border-radius: 6px;">
                                <p><?php echo $this->lang->line('no_order_resolution_tickets') ?: 'No Order Resolution requests found.'; ?></p>
                                <a href="<?php echo base_url('customer/my-orders'); ?>" class="btn btn-default">
                                    <?php echo $this->lang->line('view_my_orders') ?: 'Go to My Orders'; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterShopperRows() {
    var q = (document.getElementById('shopperTicketSearch').value || '').toLowerCase().trim();
    var filter = document.getElementById('shopperWorkflowFilter').value;
    
    // Filter desktop rows
    var tableRows = document.querySelectorAll('.resolution-table-view tbody tr[data-search]');
    tableRows.forEach(function(row) {
        var text = row.getAttribute('data-search') || '';
        var wf = row.getAttribute('data-workflow') || '';
        var matchText = (!q || text.indexOf(q) !== -1);
        var matchWf = (!filter || wf === filter);
        if (matchText && matchWf) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Filter mobile cards
    var cards = document.querySelectorAll('.resolution-mobile-cards .res-mobile-card[data-search]');
    cards.forEach(function(card) {
        var text = card.getAttribute('data-search') || '';
        var wf = card.getAttribute('data-workflow') || '';
        var matchText = (!q || text.indexOf(q) !== -1);
        var matchWf = (!filter || wf === filter);
        if (matchText && matchWf) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php $this->load->view('common/footer'); ?>
