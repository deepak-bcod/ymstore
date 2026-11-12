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

.table-resolution-list > tbody > tr > td { vertical-align: middle !important; }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <div class="content-main form-dashboard helf-section">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-2 border-bottom">
                <div>
                    <h1 class="h2 mb-1"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></h1>
                    <div class="text-muted small">Admin escalations and fulfilment</div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#rulesModal" data-bs-toggle="modal" data-bs-target="#rulesModal">
                        <i class="fa fa-book"></i> Review approved workflow
                    </button>
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
                <div class="col-md-4 col-sm-4 col-12 mb-2">
                    <div class="card p-3 shadow-sm border" style="border-radius: 9px; background: #fff;">
                        <span class="text-muted small text-uppercase font-weight-bold">Open</span>
                        <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['open'] ?? 0; ?></h2>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-12 mb-2">
                    <div class="card p-3 shadow-sm border" style="border-radius: 9px; background: #fff;">
                        <span class="text-muted small text-uppercase font-weight-bold">Awaiting fulfilment</span>
                        <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['awaiting_fulfilment'] ?? 0; ?></h2>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-12 mb-2">
                    <div class="card p-3 shadow-sm border" style="border-radius: 9px; background: #fff;">
                        <span class="text-muted small text-uppercase font-weight-bold">Closed</span>
                        <h2 class="font-weight-bold text-dark mb-0 mt-1"><?= $stats['closed'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="card mb-3 shadow-sm border-0 bg-light p-3">
                <div class="row align-items-center">
                    <div class="col-md-8 col-sm-12 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="merchantSearchInput" class="form-control border-left-0" placeholder="Search ticket, order, product or shopper..." oninput="filterMerchantTicketRows()">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <select id="merchantWorkflowFilter" class="form-control bg-white" onchange="filterMerchantTicketRows()">
                            <option value="">All statuses</option>
                            <option value="admin_review">Admin Review</option>
                            <option value="merchant_action">Merchant Action</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center table-resolution-list" id="merchantTicketsTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 15%;">Ticket ID</th>
                            <th style="width: 25%;">Order / Item</th>
                            <th style="width: 18%;">Shopper</th>
                            <th style="width: 14%;">Request</th>
                            <th style="width: 16%;">Status / Next</th>
                            <th style="width: 12%;">Updated</th>
                            <th style="width: 10%;"><?= $this->lang->line('action') ?: 'Action'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($resolutions)): ?>
                            <?php foreach ($resolutions as $res): 
                                $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $res->status));
                                $cat_key     = 'category_' . strtolower(trim($res->category ?? ''));
                                $shopper_name = trim(($res->first_name ?? '') . ' ' . ($res->last_name ?? '')) ?: ($res->customer_email ?: ($this->lang->line('shopper') ?: 'Shopper'));
                                $view_url    = base_url('order_resolution/view/' . $res->ticket_number);

                                $is_closed = in_array($res->status, ['Close', 'Close (Final)', 'Done'], true);
                                if ($is_closed) {
                                    $wf_status = 'Closed';
                                    $wf_key = 'closed';
                                    $wf_next = 'None';
                                } elseif ($res->status === 'Processing' || (!empty($res->merchant_action) && !in_array($res->merchant_action, ['none', 'n/a', 'replacement_completed'], true))) {
                                    $wf_status = 'Merchant Action';
                                    $wf_key = 'merchant_action';
                                    $wf_next = 'Merchant';
                                } else {
                                    $wf_status = 'Admin Review';
                                    $wf_key = 'admin_review';
                                    $wf_next = 'YM Admin';
                                }

                                $search_blob = strtolower($res->ticket_number . ' ' . $res->order_number . ' ' . ($res->product_name ?? '') . ' ' . $shopper_name . ' ' . $res->category . ' ' . $res->status . ' ' . $wf_status . ' ' . $wf_next);
                                $updated_text = (date('Y-m-d', $res->created_at) === date('Y-m-d')) ? 'Today' : ((date('Y-m-d', $res->created_at) === date('Y-m-d', strtotime('-1 day'))) ? 'Yesterday' : date('d M Y', $res->created_at));
                            ?>
                                <tr data-search="<?= htmlspecialchars($search_blob); ?>" data-workflow="<?= $wf_key; ?>">
                                    <td>
                                        <a href="<?= $view_url; ?>" class="font-weight-bold text-primary text-nowrap" style="text-decoration: underline; text-underline-offset: 3px;">
                                            <?= htmlspecialchars($res->ticket_number); ?>
                                        </a>
                                    </td>
                                    <td class="text-left">
                                        <div class="font-weight-bold text-dark">#<?= htmlspecialchars($res->order_number); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 230px;" title="<?= htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?>">
                                            <i class="fa fa-cube text-secondary"></i> <?= htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($shopper_name); ?></div>
                                        <?php if (!empty($res->customer_email)): ?>
                                            <div class="small text-muted text-truncate" style="max-width: 150px;"><?= htmlspecialchars($res->customer_email); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info px-2 py-1"><?= htmlspecialchars($this->lang->line($cat_key) ?: $res->category); ?></span>
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
                                    <td>
                                        <a href="<?= $view_url; ?>" class="btn btn-sm btn-info text-nowrap">
                                            <i class="fa fa-eye"></i> <?= $this->lang->line('view') ?: 'View'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <?= $this->lang->line('no_records_found') ?: 'No Order Resolution tickets found.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

<!-- Approved Workflow Modal Matching MYP Wireframe -->
<div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="rulesModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-warning">
                <h5 class="modal-title font-weight-bold text-dark" id="rulesModalTitle"><i class="fa fa-shield"></i> Approved Merchant-facing rules</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="line-height: 1.6; font-size: 14px;">
                <p><strong>Approved Order Resolution Workflow:</strong></p>
                <p>Original Merchant rejection &rarr; direct Shopper escalation to YM Admin. Merchant communicates privately with Admin; cannot see Admin–Shopper messages. Decisions use “In Favour of Shopper/Merchant”. Replacement requires Shopper receipt confirmation. Return must be confirmed before refund, with documented Admin exceptions. Disputed item-level amounts are held; standard Merchant payout timing is 7 days after Completed.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterMerchantTicketRows() {
    var q = (document.getElementById('merchantSearchInput').value || '').toLowerCase().trim();
    var filter = document.getElementById('merchantWorkflowFilter').value;
    var rows = document.querySelectorAll('#merchantTicketsTable tbody tr[data-search]');
    
    rows.forEach(function(row) {
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
}
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>