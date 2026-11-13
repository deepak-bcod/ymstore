<?php $this->load->view('common/fbc-user/header'); ?>

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

.badge-primary { color: #007bff !important; }
.badge-secondary { color: #6c757d !important; }
.badge-success { color: #28a745 !important; }
.badge-danger { color: #dc3545 !important; }
.badge-warning { color: #b38600 !important; }
.badge-info { color: #17a2b8 !important; }

.badge-action { font-size: 12px; font-weight: 700; margin: 0; }
.badge-action-approved { color: #28a745 !important; }
.badge-action-denied { color: #dc3545 !important; }
.badge-action-completed { color: #007bff !important; }
.badge-action-pending { color: #b38600 !important; }

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

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-2 border-bottom">
            <div>
                <h1 class="h2 mb-1">Order Resolution Tickets (@Help)</h1>
                <div class="text-muted small">Select a Ticket ID to open its Resolution interface</div>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#rulesModal" data-bs-toggle="modal" data-bs-target="#rulesModal">
                    <i class="fa fa-book"></i> Review rules
                </button>
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

        <!-- Stat Cards Matching MYP Wireframe -->
        <div class="row mb-3 mt-3">
            <div class="col-md-3 col-6 mb-2">
                <div class="card p-3 shadow-sm border stat-filter-card" data-filter="open" style="border-radius: 9px; background: #fff; cursor: pointer; transition: all 0.2s ease;" title="Click to filter Open tickets">
                    <span class="text-muted small text-uppercase font-weight-bold">Open</span>
                    <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['open'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card p-3 shadow-sm border stat-filter-card" data-filter="admin_review" style="border-radius: 9px; background: #fff; cursor: pointer; transition: all 0.2s ease;" title="Click to filter Awaiting Admin tickets">
                    <span class="text-muted small text-uppercase font-weight-bold">Awaiting Admin</span>
                    <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['awaiting_admin'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card p-3 shadow-sm border stat-filter-card" data-filter="awaiting_merchant" style="border-radius: 9px; background: #fff; cursor: pointer; transition: all 0.2s ease;" title="Click to filter Awaiting Fulfilment tickets">
                    <span class="text-muted small text-uppercase font-weight-bold">Awaiting fulfilment</span>
                    <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['awaiting_fulfilment'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card p-3 shadow-sm border stat-filter-card" data-filter="closed" style="border-radius: 9px; background: #fff; cursor: pointer; transition: all 0.2s ease;" title="Click to filter Closed tickets">
                    <span class="text-muted small text-uppercase font-weight-bold">Closed</span>
                    <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['closed'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="card mb-3 shadow-sm border-0 bg-light p-3">
            <div class="row align-items-center">
                <div class="col-md-7 col-sm-12 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="ticketSearchInput" class="form-control border-left-0" placeholder="Search ticket, order, product, shopper or merchant..." oninput="filterTicketRows()">
                    </div>
                </div>
                <div class="col-md-5 col-sm-12">
                    <select id="ticketWorkflowFilter" class="form-control bg-white" onchange="filterTicketRows()">
                        <option value="">All statuses</option>
                        <option value="open">Open (Active Disputes)</option>
                        <option value="admin_review">Awaiting Admin</option>
                        <option value="awaiting_merchant">Awaiting Fulfilment</option>
                        <option value="awaiting_shopper">Awaiting Shopper</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center table-resolution-list" id="ticketListTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 120px;">Ticket ID</th>
                        <th style="min-width: 190px;">Order / Item</th>
                        <th style="min-width: 130px;">Shopper</th>
                        <th style="min-width: 130px;">Merchant</th>
                        <th style="width: 120px;">Request</th>
                        <th style="width: 160px;">Status / Next</th>
                        <th style="width: 100px;">Updated</th>
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
                            
                            // Determine wireframe workflow stage & next actor
                            $is_closed = in_array($res->status, ['Close', 'Close (Final)', 'Done'], true);
                            if ($is_closed) {
                                $wf_status = 'Closed';
                                $wf_key = 'closed';
                                $wf_next = 'None';
                            } elseif ($res->status === 'Processing') {
                                if ($res->category === 'Replacement' && $mAction !== 'replacement_completed') {
                                    $wf_status = 'Awaiting Fulfilment';
                                    $wf_key = 'awaiting_merchant';
                                    $wf_next = htmlspecialchars($res->merchant_name ?: 'Merchant');
                                } else {
                                    $wf_status = 'Awaiting Shopper';
                                    $wf_key = 'awaiting_shopper';
                                    $wf_next = htmlspecialchars($shopper_name);
                                }
                            } else {
                                $wf_status = 'Admin Review';
                                $wf_key = 'admin_review';
                                $wf_next = 'YM Admin';
                            }

                            $search_blob = strtolower($res->ticket_number . ' ' . $res->order_number . ' ' . ($res->product_name ?? '') . ' ' . $shopper_name . ' ' . ($res->merchant_name ?? '') . ' ' . $res->category . ' ' . $res->status . ' ' . $wf_status . ' ' . $wf_next);
                            $updated_text = (date('Y-m-d', $res->created_at) === date('Y-m-d')) ? 'Today' : ((date('Y-m-d', $res->created_at) === date('Y-m-d', strtotime('-1 day'))) ? 'Yesterday' : date('d M Y', $res->created_at));
                        ?>
                            <tr data-search="<?= htmlspecialchars($search_blob); ?>" data-workflow="<?= $wf_key; ?>">
                                <td>
                                    <a href="<?= $view_url; ?>" class="font-weight-bold text-primary text-nowrap" style="text-decoration: underline; text-underline-offset: 3px;" title="Open ticket">
                                        <?= htmlspecialchars($res->ticket_number); ?>
                                    </a>
                                </td>
                                <td class="text-left">
                                    <div class="font-weight-bold text-dark">#<?= htmlspecialchars($res->order_number); ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 190px;" title="<?= htmlspecialchars($res->product_name ?: 'All Items'); ?>">
                                        <i class="fa fa-cube text-secondary"></i> <?= htmlspecialchars($res->product_name ?: 'All Items'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?= htmlspecialchars($shopper_name); ?></div>
                                    <?php if (!empty($res->customer_email)): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 130px;"><?= htmlspecialchars($res->customer_email); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?= htmlspecialchars($res->merchant_name ?: 'N/A'); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-info px-2 py-1"><?= htmlspecialchars($res->category); ?></span>
                                    <div class="small text-muted mt-1">Priority: <?= htmlspecialchars($res->priority ?: 'Medium'); ?></div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge-status-<?= $status_slug; ?>">
                                            <?= htmlspecialchars($wf_status); ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="fa fa-user-circle-o"></i> <?= $wf_next; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small font-weight-bold"><?= $updated_text; ?></div>
                                    <div class="small text-muted"><?= date('h:i A', $res->created_at); ?> MUT</div>
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
                                                    <?php if ($res->category === 'Replacement'): ?>
                                                        <div class="small text-muted font-weight-bold">Item Value: MUR <?= number_format($res->refund_amount, 2); ?></div>
                                                    <?php else: ?>
                                                        <div class="small text-success font-weight-bold">Refund: MUR <?= number_format($res->refund_amount, 2); ?></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-3 text-right">
                                                <div class="small text-muted">Created: <?= date('d M Y, h:i A', $res->created_at); ?> MUT</div>
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

<!-- Approved Rules Modal Matching MYP Wireframe -->
<div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="rulesModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-warning">
                <h5 class="modal-title font-weight-bold text-dark" id="rulesModalTitle"><i class="fa fa-shield"></i> Approved rules</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="line-height: 1.6; font-size: 14px;">
                <p><strong>Approved Order Resolution Rules:</strong></p>
                <p>Direct-to-Admin escalation; item-level holds; 7-day payout rule for all Merchant Orders; return confirmed before refund with Admin exceptions; Shopper replacement receipt confirmation; separate private Admin threads; 1 MB image maximum.</p>
                <p class="mb-0 text-muted"><em>V2 remedy switching and administration fees are excluded.</em></p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterTicketRows() {
    var q = (document.getElementById('ticketSearchInput').value || '').toLowerCase().trim();
    var filter = document.getElementById('ticketWorkflowFilter').value;
    var rows = document.querySelectorAll('#ticketListTable tbody tr[data-search]');
    
    rows.forEach(function(row) {
        var text = row.getAttribute('data-search') || '';
        var wf = row.getAttribute('data-workflow') || '';
        var matchText = (!q || text.indexOf(q) !== -1);
        var matchWf = true;
        if (filter === 'open') {
            matchWf = (wf !== 'closed');
        } else if (filter) {
            matchWf = (wf === filter);
        }

        if (matchText && matchWf) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Sync card active states
    document.querySelectorAll('.stat-filter-card').forEach(function(card) {
        var cardFilter = card.getAttribute('data-filter');
        if (filter && cardFilter === filter) {
            card.style.borderColor = '#007bff';
            card.style.backgroundColor = '#f0f7ff';
        } else {
            card.style.borderColor = '#dee2e6';
            card.style.backgroundColor = '#ffffff';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-filter-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var targetFilter = this.getAttribute('data-filter');
            var select = document.getElementById('ticketWorkflowFilter');
            if (select.value === targetFilter) {
                select.value = ''; // Toggle off
            } else {
                select.value = targetFilter;
            }
            filterTicketRows();
        });
    });
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
