<?php 
$currency = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
$this->load->view('common/fbc-user/header'); 
?>

<style>
/* Remove background color from all text elements (badges, labels, status pills, alerts, spans) except buttons */
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
}

.badge-status-open, .badge-primary { color: #007bff !important; }
.badge-status-processing, .badge-info { color: #17a2b8 !important; }
.badge-status-done, .badge-success { color: #28a745 !important; }
.badge-status-close, .badge-secondary { color: #6c757d !important; }
.badge-status-reopen { color: #fd7e14 !important; }
.badge-status-close-final, .badge-dark { color: #343a40 !important; }
.badge-danger { color: #dc3545 !important; }
.badge-warning { color: #b38600 !important; }
.badge-light { color: #495057 !important; }

/* Remove background color from alert boxes, chat messages and text containers */
.alert:not(.alert-dismissible),
.bg-light,
.card-header.bg-light,
.chat-box,
.chat-msg,
.chat-msg-shopper,
.chat-msg-merchant,
.chat-msg-help,
.chat-msg-acct,
.chat-attachment-box {
    background: transparent !important;
    background-color: transparent !important;
}

.chat-box { max-height: 520px; overflow-y: auto; padding: 15px; border: 1px solid #e9ecef; border-radius: 6px; margin-bottom: 25px; }
.chat-msg { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; max-width: 85%; }
.chat-msg-shopper { border: 1px solid #bbdefb; margin-right: auto; }
.chat-msg-merchant { border: 1px solid #dcedc8; margin-right: auto; }
.chat-msg-help { border: 1px solid #ffe0b2; margin-left: auto; text-align: left; }
.chat-msg-acct { border: 1px solid #e1bee7; margin-left: auto; text-align: left; }
.chat-msg-meta { font-size: 11px; color: #666; margin-bottom: 5px; }
.chat-msg-body { font-size: 14px; line-height: 1.5; color: #222; }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="content-main form-dashboard">

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

        <!-- Ticket Card Header -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <div>
                    <h4 class="mb-0 d-inline">Ticket #<?= htmlspecialchars($resolution->ticket_number); ?></h4>
                    <span class="text-muted ml-2">(Order #<?= htmlspecialchars($resolution->order_number); ?>)</span>
                </div>
                <div>
                    <?php 
                    $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $resolution->status));
                    ?>
                    <span class="badge-status-<?= $status_slug; ?>">
                        Status: <?= htmlspecialchars($resolution->status); ?>
                    </span>
                    <span class="badge badge-<?= ($resolution->assigned_role === 'Acct') ? 'warning' : 'primary'; ?> ml-2">
                        Assigned: @<?= htmlspecialchars($resolution->assigned_role); ?>
                    </span>
                    <a href="<?= base_url('order_resolution'); ?>" class="btn btn-outline-secondary btn-sm ml-3">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Metadata Grid -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Shopper:</strong></p>
                        <p><?= htmlspecialchars($customer ? trim($customer->first_name . ' ' . $customer->last_name) : 'Shopper'); ?><br>
                           <small class="text-muted"><?= htmlspecialchars($customer->email_id ?? ''); ?></small></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Merchant:</strong></p>
                        <p><?= htmlspecialchars($merchant ? $merchant->publication_name : 'N/A'); ?><br>
                           <small class="text-muted"><?= htmlspecialchars($merchant->email ?? ''); ?></small></p>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-1"><strong>Product:</strong></p>
                        <p><?= htmlspecialchars($product ? $product->name : 'All Items / General'); ?></p>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-1"><strong>Subject Type:</strong></p>
                        <p>
                            <span class="badge badge-info"><?= htmlspecialchars($resolution->category); ?></span>
                        </p>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-1"><strong>Priority:</strong></p>
                        <p>
                            <?php
                            $p_badge = 'badge-secondary';
                            if ($resolution->priority === 'High') {
                                $p_badge = 'badge-danger';
                            } elseif ($resolution->priority === 'Medium') {
                                $p_badge = 'badge-warning';
                            } elseif ($resolution->priority === 'Low') {
                                $p_badge = 'badge-info';
                            }
                            ?>
                            <span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($resolution->priority ?: 'Medium'); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Status & Decision Details Banner -->
                <div class="alert alert-secondary py-2 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <strong>Merchant Action:</strong> 
                            <?php if (!empty($resolution->merchant_action) && !in_array(strtolower($resolution->merchant_action), ['none', 'n/a'], true)): ?>
                                <span class="text-primary font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning font-weight-bold" style="color: #b38600;">Pending</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <strong>Delivery Option:</strong> 
                            <span class="text-success font-weight-bold"><?php echo $delivery_option = ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?></span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <strong>Refund Amount:</strong> 
                            <?php 
                            $disp_refund = (float)($resolution->refund_amount ?? 0);
                            if ($disp_refund <= 0) {
                                if (!empty($product) && !empty($product->price)) {
                                    $disp_refund = (float)(($product->special_price > 0) ? $product->special_price : $product->price);
                                } elseif (!empty($order)) {
                                    $disp_refund = (float)($order->grand_total ?: $order->subtotal);
                                }
                            }
                            ?>
                            <span class="font-weight-bold <?= ($disp_refund > 0) ? 'text-success' : 'text-muted'; ?>">
                                <?= 'MUR ' . number_format($disp_refund, 2); ?>
                            </span>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <strong>Admin Decision:</strong>
                            <?php if ($resolution->admin_decision === 'in_favour_shopper' || $resolution->resolution_status === 'resolution_approved'): ?>
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-gavel"></i> In Favour of Shopper</span>
                            <?php elseif ($resolution->admin_decision === 'in_favour_merchant' || $resolution->resolution_status === 'resolution_denied'): ?>
                                <span class="badge badge-danger px-2 py-1"><i class="fa fa-gavel"></i> In Favour of Merchant</span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-2 py-1"><i class="fa fa-clock-o"></i> Under Review</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                    $is_refund_remedy = ($resolution->category === 'Refund' || ($resolution->admin_remedy ?? '') === 'return_refund');
                    $is_rep_remedy    = ($resolution->category === 'Replacement' || ($resolution->admin_remedy ?? '') === 'replacement');
                    ?>
                    <?php if ($is_refund_remedy || $is_rep_remedy): ?>
                        <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.1);">
                        <div class="row align-items-center small">
                            <?php if ($is_refund_remedy): ?>
                                <div class="col-md-6">
                                    <strong>Workflow 5A (Return Confirmation):</strong>
                                    <?php if (!empty($resolution->merchant_return_confirmed)): ?>
                                        <span class="badge badge-success ml-1"><i class="fa fa-check"></i> Confirmed by Merchant</span>
                                        <?php if (!empty($resolution->merchant_return_notes)): ?>
                                            <span class="text-muted ml-1">(<?= htmlspecialchars($resolution->merchant_return_notes); ?>)</span>
                                        <?php endif; ?>
                                    <?php elseif (!empty($resolution->admin_return_exception)): ?>
                                        <span class="badge badge-info ml-1"><i class="fa fa-file-text-o"></i> Documented by Admin Exception</span>
                                        <?php if (!empty($resolution->admin_return_exception_notes)): ?>
                                            <span class="text-muted ml-1">(<?= htmlspecialchars($resolution->admin_return_exception_notes); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning ml-1 font-weight-bold" style="color: #b38600;"><i class="fa fa-clock-o"></i> Pending Merchant Confirmation or Admin Exception</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_rep_remedy): ?>
                                <div class="col-md-6">
                                    <strong>Workflow 5B (Replacement Receipt):</strong>
                                    <?php if (!empty($resolution->shopper_receipt_confirmed)): ?>
                                        <span class="badge badge-success ml-1"><i class="fa fa-check"></i> Confirmed by Shopper</span>
                                    <?php elseif (!empty($resolution->admin_replacement_exception)): ?>
                                        <span class="badge badge-info ml-1"><i class="fa fa-truck"></i> Documented by Admin Exception (Carrier Proof)</span>
                                        <?php if (!empty($resolution->admin_replacement_exception_notes)): ?>
                                            <span class="text-muted ml-1">(<?= htmlspecialchars($resolution->admin_replacement_exception_notes); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning ml-1 font-weight-bold" style="color: #b38600;"><i class="fa fa-clock-o"></i> Awaiting Shopper Confirmation or Admin Exception</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php 
                $is_replacement_completed = ($resolution->merchant_action === 'replacement_completed');
                $is_direct_merchant_replacement = ($resolution->merchant_action === 'replacement_completed' && in_array($resolution->delivery_option, ['own_delivery', 'self_pickup'], true));
                $is_replacement_denied = ($resolution->merchant_action === 'replacement_denied');
                $hide_acct = ($is_replacement_completed || $is_replacement_denied);
                if ($hide_acct && $current_role === 'acct') {
                    $current_role = 'help';
                }
                $is_addon_paid = !empty($is_addon_paid) || (!empty($addon_purchase) && in_array(strtolower($addon_purchase->status ?? ''), ['paid', 'success', 'completed'], true));
                ?>

                <!-- Admin Action Management Controls -->
                <div class="p-3 bg-light border rounded mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0">
                            <i class="fa fa-cogs"></i> Resolution Management Controls
                            <span class="badge badge-<?= ($current_role === 'acct') ? 'warning' : 'primary'; ?> ml-2">
                                <?= ($current_role === 'acct') ? '@Acct (Accounting Only)' : '@Help (Support Only)'; ?>
                            </span>
                        </h6>
                    </div>

                    <?php if ($current_role === 'acct' && !$hide_acct): ?>
                        <!-- ================= @Acct (ACCOUNTING ONLY) CONTROLS ================= -->
                        <?php if ($resolution->delivery_option === 'ym_delivery' && $resolution->merchant_action === 'replacement_approved'): ?>
                            <?php if ($is_addon_paid): ?>
                                <div class="alert alert-success py-2 mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-check-circle"></i> <strong>AddOn Service Purchased:</strong> The merchant has completed payment for the replacement delivery AddOn Service (Purchase ID: #<?= (int)$resolution->addon_purchase_id; ?>). YellowMarkets delivery logistics can be scheduled.
                                    </div>
                                    <div>
                                        <a href="<?= base_url('addons/download_invoice/' . $resolution->addon_purchase_id); ?>" class="btn btn-sm btn-outline-success font-weight-bold" target="_blank">
                                            <i class="fa fa-file-pdf-o"></i> Download Invoice
                                        </a>
                                    </div>
                                </div>
                            <?php elseif (!empty($resolution->addon_purchase_id)): ?>
                                <div class="alert alert-warning py-2 mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-clock-o"></i> <strong>AddOn Service Requested (Pending Payment):</strong> AddOn Service purchase (Purchase ID: #<?= (int)$resolution->addon_purchase_id; ?>) has been requested and is awaiting merchant payment. YellowMarkets delivery logistics can be scheduled once payment is confirmed.
                                    </div>
                                    <div>
                                        <span class="badge badge-warning p-2"><i class="fa fa-clock-o"></i> Payment Pending</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>YM Delivery Replacement (AddOn Service Required):</strong> The merchant selected Yellow Markets delivery for this replacement. Accounting (@Acct) must request the merchant to purchase the replacement delivery service as an <strong>AddOn Service</strong>.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($resolution->status === 'Processing'): ?>
                            <?php if ($resolution->delivery_option === 'ym_delivery'): ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>Assigned to Accounting (@Acct):</strong> Coordinate with the merchant to purchase the replacement delivery AddOn Service. Yellow Markets logistics team will collect and deliver the replacement product to the shopper, then @Help will complete and close the ticket.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-money"></i> <strong>Assigned to Accounting (@Acct):</strong> Refund the shopper from the merchant's 15-day hold-back sales, then click <strong>Mark Done</strong>.
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#markDoneModal">
                                        <i class="fa fa-check-circle"></i> Mark Done (Refund Processed from Hold-back)
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($resolution->status === 'Done'): ?>
                            <div class="alert alert-success py-2 mb-0">
                                <i class="fa fa-check-circle"></i> <strong>Refund Processed:</strong> Accounting has completed the refund from merchant 15-day hold-back sales. Ticket handed back to @Help for final closure.
                            </div>
                        <?php elseif ($resolution->status === 'Close (Final)'): ?>
                            <div class="alert alert-secondary py-2 mb-0">
                                <i class="fa fa-lock"></i> <strong>Ticket Closed (Final):</strong> Dispute has been resolved and this resolution is finalized.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border py-2 mb-0 text-muted">
                                <i class="fa fa-info-circle"></i> Ticket is currently handled in the Support (@Help) queue. No financial action required from Accounting at this stage.
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- ================= @Help (SUPPORT ONLY) CONTROLS ================= -->
                        <?php if ($resolution->delivery_option === 'ym_delivery' && $resolution->merchant_action === 'replacement_approved'): ?>
                            <?php if ($is_addon_paid): ?>
                                <div class="alert alert-success py-2 mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-check-circle"></i> <strong>AddOn Service Purchased:</strong> The merchant has completed payment for the replacement delivery AddOn Service (Purchase ID: #<?= (int)$resolution->addon_purchase_id; ?>). YellowMarkets delivery logistics can be scheduled.
                                    </div>
                                    <div>
                                        <a href="<?= base_url('addons/download_invoice/' . $resolution->addon_purchase_id); ?>" class="btn btn-sm btn-outline-success font-weight-bold" target="_blank">
                                            <i class="fa fa-file-pdf-o"></i> Download Invoice
                                        </a>
                                    </div>
                                </div>
                            <?php elseif (!empty($resolution->addon_purchase_id)): ?>
                                <div class="alert alert-warning py-2 mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-clock-o"></i> <strong>AddOn Service Requested (Pending Payment):</strong> AddOn Service purchase (Purchase ID: #<?= (int)$resolution->addon_purchase_id; ?>) has been requested and is awaiting merchant payment. YellowMarkets delivery logistics can be scheduled once payment is confirmed.
                                    </div>
                                    <div>
                                        <span class="badge badge-warning p-2"><i class="fa fa-clock-o"></i> Payment Pending</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>YM Delivery Replacement (AddOn Service Required):</strong> The merchant selected Yellow Markets delivery for this replacement. Accounting (@Acct) / Support must request the merchant to purchase the replacement delivery service as an <strong>AddOn Service</strong>.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (in_array($resolution->status, ['Open', 'ReOpen'], true) && !in_array($resolution->resolution_status, ['resolution_approved', 'resolution_denied'], true)): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fa fa-gavel"></i> <strong>Shopper Dispute Active:</strong> The shopper has requested Yellow Markets dispute assistance following the merchant's rejection. Please investigate via the ticket conversation and choose <strong>Resolution Approved</strong> (in favour of Shopper), <strong>Resolution Denied</strong> (in favour of Merchant), or <strong>Close Ticket</strong>.
                            </div>
                        <?php elseif ($resolution->status === 'Processing'): ?>
                            <?php if ($resolution->delivery_option === 'ym_delivery'): ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>Assigned to Accounting (@Acct):</strong> Coordinate with the merchant to purchase the replacement delivery AddOn Service. Yellow Markets logistics team will collect and deliver the replacement product to the shopper, then @Help will complete and close the ticket.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-2">
                                    <i class="fa fa-hourglass-half"></i> <strong>Assigned to Accounting (@Acct):</strong> Awaiting Accounting to process refund from merchant's 15-day hold-back sales.
                                </div>
                                <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                    <?php if (!empty($resolution->merchant_return_confirmed) || !empty($resolution->admin_return_exception)): ?>
                                        <button type="button" class="btn btn-success btn-sm font-weight-bold" data-toggle="modal" data-target="#markDoneModal">
                                            <i class="fa fa-check-circle"></i> Mark Done (Refund Processed from Hold-back)
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= base_url('order_resolution/view/' . $resolution->ticket_number . '?as=acct'); ?>" class="btn btn-outline-warning btn-sm">
                                        <i class="fa fa-user-circle"></i> Open Accounting (@Acct) View
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($resolution->status === 'Done'): ?>
                            <div class="alert alert-success py-2 mb-3">
                                <i class="fa fa-check"></i> <strong>Refund Completed:</strong> Accounting has completed the refund from hold-back sales. @Help can now close the ticket as <strong>Close (Final)</strong>.
                            </div>
                        <?php elseif ($resolution->status === 'Close (Final)'): ?>
                            <div class="alert alert-secondary py-2 mb-0">
                                <i class="fa fa-lock"></i> <strong>Ticket Closed (Final):</strong> Dispute has been resolved and this resolution is finalized. No further actions available.
                            </div>
                        <?php endif; ?>

                        <?php 
                        $has_merchant_action = (!empty($resolution->merchant_action) && $resolution->merchant_action !== 'none');
                        ?>

                        <?php if (!$has_merchant_action && $resolution->status === 'Open'): ?>
                            <div class="alert alert-light border py-2 mb-3 text-muted">
                                <i class="fa fa-hourglass-start"></i> <strong>Merchant Coordination:</strong> The merchant has not taken any action on this resolution request yet. Support (@Help) can adjudicate directly or wait for merchant coordination/fulfilment.
                            </div>
                        <?php endif; ?>

                        <?php if ($resolution->merchant_action === 'refund_denied' && $resolution->status === 'Open'): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fa fa-info-circle"></i> <strong>Merchant Denied Refund:</strong> The merchant has rejected the refund request. @Help can review and click <strong>Close Ticket</strong>.
                            </div>
                        <?php elseif ($resolution->merchant_action === 'replacement_denied' && $resolution->status === 'Open'): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fa fa-info-circle"></i> <strong>Merchant Denied Replacement:</strong> The merchant has rejected the replacement request. @Help can review and click <strong>Close Ticket</strong>.
                            </div>
                        <?php elseif ($is_replacement_completed && in_array($resolution->status, ['Open', 'Processing', 'Done'])): ?>
                            <div class="alert alert-success py-2 mb-3">
                                <i class="fa fa-check-circle"></i> <strong>Replacement Completed:</strong> The merchant has completed the product replacement via <?= htmlspecialchars(ucwords(str_replace('_', ' ', $resolution->delivery_option))); ?>. @Help can now close the ticket.
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                            <!-- Step 3: @Help Marks Ticket Close (When Merchant Denied Refund/Replacement or General Initial Close) -->
                            <?php if (in_array($resolution->status, ['Open', 'Processing', 'Done']) && $resolution->status !== 'Close' && $resolution->status !== 'Close (Final)'): ?>
                                <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#closeTicketModal">
                                    <i class="fa fa-lock"></i> Close Ticket <?= ($resolution->merchant_action === 'refund_denied') ? '(Refund Denied by Merchant)' : (($resolution->merchant_action === 'replacement_denied') ? '(Replacement Denied by Merchant)' : ''); ?>
                                </button>

                                <?php if ($resolution->status === 'Open' && $has_merchant_action && $resolution->merchant_action !== 'refund_denied' && !$hide_acct && $resolution->delivery_option === 'ym_delivery'): ?>
                                    <!--button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#assignAcctModal">
                                        <i class="fa fa-user-plus"></i> Assign to @acct (Processing)
                                    </button-->
                                <?php endif; ?>

                                <?php if ($resolution->delivery_option === 'ym_delivery' && $resolution->merchant_action === 'replacement_approved' && $is_addon_paid): ?>
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#completeYmDeliveryModal">
                                        <i class="fa fa-truck"></i> Mark YM Delivery Completed
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Dispute Decisions (@Help Direct Adjudication for Active Dispute) -->
                            <?php if (in_array($resolution->status, ['Open', 'ReOpen'], true) && !in_array($resolution->resolution_status, ['resolution_approved', 'resolution_denied'], true)): ?>
                                <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#resApproveModal">
                                    <i class="fa fa-gavel"></i> Decision: In Favour of Shopper
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#resDeniedModal">
                                    <i class="fa fa-gavel"></i> Decision: In Favour of Merchant
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#fulfilmentCheckpointsModal">
                                    <i class="fa fa-tasks"></i> View fulfilment checkpoints
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#fulfilmentCheckpointsModal">
                                    <i class="fa fa-tasks"></i> View fulfilment checkpoints
                                </button>
                            <?php endif; ?>

                            <!-- Workflow 5A Exception Action: Return Confirmation Exception -->
                            <?php 
                            $can_doc_return_exc = ($resolution->admin_decision === 'in_favour_shopper' || $resolution->resolution_status === 'resolution_approved')
                                && ($resolution->category === 'Refund' || ($resolution->admin_remedy ?? '') === 'return_refund')
                                && empty($resolution->merchant_return_confirmed)
                                && empty($resolution->admin_return_exception)
                                && $resolution->status !== 'Close (Final)';
                            ?>
                            <?php if ($can_doc_return_exc): ?>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#docReturnExceptionModal">
                                    <i class="fa fa-exclamation-triangle"></i> Document Return Exception (Workflow 5A)
                                </button>
                            <?php endif; ?>

                            <!-- Workflow 5B Exception Action: Replacement Receipt Exception -->
                            <?php 
                            $can_doc_rep_exc = ($resolution->admin_decision === 'in_favour_shopper' || $resolution->resolution_status === 'resolution_approved')
                                && ($resolution->category === 'Replacement' || ($resolution->admin_remedy ?? '') === 'replacement')
                                && empty($resolution->shopper_receipt_confirmed)
                                && empty($resolution->admin_replacement_exception)
                                && $resolution->status !== 'Close (Final)';
                            ?>
                            <?php if ($can_doc_rep_exc): ?>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#docReplacementExceptionModal">
                                    <i class="fa fa-truck"></i> Document Receipt Exception (Workflow 5B)
                                </button>
                            <?php endif; ?>

                            <!-- Scenario 1 - Step 7: @Help Changes Resolution Status to Close (Final) -->
                            <?php if ($resolution->status === 'Done'): ?>
                                <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#closeFinalModal">
                                    <i class="fa fa-archive"></i> Close (Final)
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                // Separate conversion workflows
                $shopper_admin_msgs  = [];
                $merchant_admin_msgs = [];
                if (!empty($messages)) {
                    foreach ($messages as $msg) {
                        $c_type = !empty($msg->conversion_type) ? $msg->conversion_type : '';
                        if ($c_type === 'merchant_admin') {
                            $merchant_admin_msgs[] = $msg;
                        } elseif ($c_type === 'shopper_admin') {
                            $shopper_admin_msgs[] = $msg;
                        } else {
                            if ($msg->sender_role === 'merchant') {
                                $merchant_admin_msgs[] = $msg;
                            } else {
                                $shopper_admin_msgs[] = $msg;
                            }
                        }
                    }
                }
                $is_acct_active = ($current_role === 'acct');
                ?>

                                <?php
                $item_context = $item_context ?? ($resolution->item_context ?? $this->OrderResolutionModel->get_original_item_context($resolution));
                $currency     = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
                ?>
                <!-- ================= LINKED ORIGINAL REQUEST & AFFECTED ITEM CONTEXT (YM-11) ================= -->
                <div class="card mb-4 shadow-sm border-0" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="font-weight-bold text-dark" style="font-size: 14px;">
                            <i class="fa fa-link mr-1" style="color: #6f42c1;"></i> Linked Original Request & Affected Item Context
                        </span>
                        <div>
                            <span class="badge mr-1 font-weight-bold" style="color: #6f42c1;"><i class="fa fa-history"></i> Original Request Context</span>
                            <span class="badge badge-danger"><i class="fa fa-times-circle"></i> Merchant Rejected</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <!-- Col 1: Affected Item & Variant -->
                            <div class="col-md-4 mb-3 mb-md-0 border-right">
                                <div class="text-muted small text-uppercase font-weight-bold mb-1">Affected Order Item</div>
                                <div class="font-weight-bold text-dark" style="font-size: 15px;">
                                    <?= htmlspecialchars($item_context->product_name ?: ($product ? $product->name : 'Item Not Found')); ?>
                                </div>
                                
                                <div class="mt-2 p-2 rounded" style="border: 1px solid #e9ecef;">
                                    <div class="small mb-1">
                                        <strong class="text-secondary"><i class="fa fa-tags"></i> Variant:</strong>
                                        <?php if ($item_context->has_variant): ?>
                                            <span class="badge badge-light border text-dark font-weight-normal ml-1" style="font-size: 12px;">
                                                <?= htmlspecialchars($item_context->variant_display); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted font-italic ml-1"><?= htmlspecialchars($item_context->variant_status_note); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small mb-1">
                                        <strong class="text-secondary"><i class="fa fa-cubes"></i> Disputed Quantity:</strong>
                                        <span class="badge badge-info ml-1 font-weight-bold" style="font-size: 12px;"><?= (int)$item_context->disputed_qty; ?> unit(s)</span>
                                    </div>
                                    <div class="small">
                                        <strong class="text-secondary"><i class="fa fa-money"></i> Item Financial Basis:</strong>
                                        <span class="font-weight-bold text-dark ml-1"><?= $currency; ?> <?= number_format($item_context->total_price, 2); ?></span>
                                        <small class="text-muted">(<?= $currency; ?> <?= number_format($item_context->unit_price, 2); ?> / unit)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Col 2: Order & Reference Mapping -->
                            <div class="col-md-4 mb-3 mb-md-0 border-right">
                                <div class="text-muted small text-uppercase font-weight-bold mb-1">Order & Reference Mapping</div>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                    <tr>
                                        <td class="text-muted py-1" style="width: 48%;"><i class="fa fa-shopping-cart text-primary"></i> Shopper Order:</td>
                                        <td class="font-weight-bold py-1">#<?= htmlspecialchars($item_context->order_number); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1"><i class="fa fa-building text-info"></i> Merchant Order ID:</td>
                                        <td class="font-weight-bold py-1"><?= htmlspecialchars($item_context->merchant_order_number); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-1"><i class="fa fa-exchange" style="color: #6f42c1;"></i> Original Request:</td>
                                        <td class="font-weight-bold py-1">
                                            <span class="badge badge-secondary"><?= htmlspecialchars($item_context->request_reference); ?></span>
                                            <small class="text-muted">(<?= htmlspecialchars($item_context->request_type_label); ?>)</small>
                                        </td>
                                    </tr>
                                    <?php if (!empty($item_context->request_date)): ?>
                                    <tr>
                                        <td class="text-muted py-1"><i class="fa fa-calendar"></i> Request Date:</td>
                                        <td class="py-1"><?= date('d M Y, h:i A', $item_context->request_date); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>

                            <!-- Col 3: Original Rejection & History -->
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase font-weight-bold mb-1">Original Rejection Context</div>
                                <div class="mb-2">
                                    <span class="badge badge-danger p-1 px-2" style="font-size: 12px;">
                                        <i class="fa fa-ban mr-1"></i> <?= htmlspecialchars($item_context->rejection_status); ?>
                                    </span>
                                    <?php if (!empty($item_context->rejection_date)): ?>
                                        <small class="text-muted ml-1"><?= date('d M Y', $item_context->rejection_date); ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="small mb-2">
                                    <strong class="text-secondary">Original Customer Reason:</strong>
                                    <div class="p-1 px-2 mt-1 rounded bg-light border text-dark" style="font-size: 12px; line-height: 1.4;">
                                        <?= htmlspecialchars($item_context->customer_reason); ?>
                                    </div>
                                </div>

                                <div class="small">
                                    <strong class="text-secondary">Merchant Rejection Reason / Note:</strong>
                                    <div class="p-1 px-2 mt-1 rounded border text-danger" style="font-size: 12px; line-height: 1.4;">
                                        <i class="fa fa-commenting-o mr-1"></i> <?= htmlspecialchars($item_context->rejection_reason); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= ORIGINAL DISPUTE CLAIM & SUBMITTED EVIDENCE ================= -->
                <div class="card mb-4 shadow-sm border-info" style="border-left: 4px solid #17a2b8;">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="font-weight-bold text-dark" style="font-size: 14px;">
                            <i class="fa fa-file-text-o text-info mr-1"></i> Original Dispute Claim & Submitted Evidence
                        </span>
                        <div>
                            <span class="badge badge-info mr-1"><i class="fa fa-gavel"></i> Dispute Origin</span>
                            <span class="text-muted small"><i class="fa fa-clock-o"></i> Submitted: <?= date('d M Y, h:i A', $resolution->created_at); ?></span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <strong class="text-muted small text-uppercase">Shopper's Original Claim / Reason:</strong>
                                    <div class="p-3 mt-1 rounded bg-white border" style="font-size: 14px; line-height: 1.6; min-height: 50px;">
                                        <?= !empty($resolution->message) ? nl2br(htmlspecialchars($resolution->message, ENT_QUOTES, 'UTF-8')) : '<em class="text-muted">No text statement provided.</em>'; ?>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <i class="fa fa-info-circle text-primary"></i> <strong>Shared Context:</strong> This is the initial dispute statement submitted by the shopper following merchant rejection of the return/replacement request. Visible to all parties (Shopper, Merchant, Support) for dispute context.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <strong class="text-muted small text-uppercase">Initial Evidence:</strong>
                                <?php if (!empty($resolution->attachment)): 
                                    $att_ext = strtolower(pathinfo($resolution->attachment, PATHINFO_EXTENSION));
                                    $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                    $is_pdf  = ($att_ext === 'pdf');
                                    $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($resolution->attachment));
                                    $att_download_url = $att_preview_url . '?download=1';
                                ?>
                                    <div class="p-2 mt-1 rounded bg-white border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small font-weight-bold text-truncate mr-2" style="max-width: 160px;" title="<?= htmlspecialchars($resolution->attachment); ?>">
                                                <i class="fa <?= $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip'); ?>"></i>
                                                <?= htmlspecialchars($resolution->attachment); ?>
                                            </span>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= $att_preview_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Preview Evidence">
                                                    <i class="fa fa-eye"></i> Preview
                                                </a>
                                                <a href="<?= $att_download_url; ?>" class="btn btn-sm btn-outline-secondary" title="Download Evidence">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php if ($is_img): ?>
                                            <div style="max-height: 140px; overflow: hidden; border-radius: 4px; border: 1px solid #dee2e6; text-align: center; background: #f8f9fa;">
                                                <a href="<?= $att_preview_url; ?>" target="_blank">
                                                    <img src="<?= $att_preview_url; ?>" alt="Evidence" style="max-height: 140px; width: auto; max-width: 100%; object-fit: contain;">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 mt-1 rounded bg-white border text-muted small">
                                        <i class="fa fa-ban"></i> No file attachment uploaded with original claim.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversation Workflows Navigation Tabs -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white p-0 border-bottom">
                        <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="adminConvTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link <?= !$is_acct_active ? 'active' : ''; ?> font-weight-bold py-3 px-4" id="tab-admin-shopper" data-toggle="tab" href="#pane-admin-shopper" role="tab" aria-controls="pane-admin-shopper" aria-selected="<?= !$is_acct_active ? 'true' : 'false'; ?>">
                                    <i class="fa fa-user text-primary mr-1"></i> Shopper Dispute (Admin ↔ Shopper)
                                    <span class="badge badge-pill badge-primary ml-1"><?= count($shopper_admin_msgs); ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $is_acct_active ? 'active' : ''; ?> font-weight-bold py-3 px-4" id="tab-admin-merchant" data-toggle="tab" href="#pane-admin-merchant" role="tab" aria-controls="pane-admin-merchant" aria-selected="<?= $is_acct_active ? 'true' : 'false'; ?>">
                                    <i class="fa fa-shopping-bag text-warning mr-1"></i> Merchant Coordination (Admin ↔ Merchant)
                                    <span class="badge badge-pill badge-warning ml-1"><?= count($merchant_admin_msgs); ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content card-body p-3" id="adminConvContent">
                        <!-- Pane 1: Admin <-> Shopper -->
                        <div class="tab-pane fade <?= !$is_acct_active ? 'show active' : ''; ?>" id="pane-admin-shopper" role="tabpanel" aria-labelledby="tab-admin-shopper">
                            <div class="alert alert-primary py-2 px-3 mb-3 d-flex justify-content-between align-items-center flex-wrap" style="border: 1px solid #cce2ff; font-size: 13px;">
                                <div>
                                    <i class="fa fa-lock text-primary"></i> <strong>Private Dispute Thread (Admin ↔ Shopper):</strong> Confidential investigation channel with the Shopper.
                                </div>
                                <span class="badge badge-primary py-1 px-2 font-weight-bold"><i class="fa fa-eye-slash"></i> Hidden from Merchant</span>
                            </div>
                            <div class="chat-box mb-3" style="max-height: 480px; overflow-y: auto; padding: 12px; background: #fafbfc; border: 1px solid #e9ecef; border-radius: 6px;">
                                <?php if (!empty($shopper_admin_msgs)): ?>
                                    <?php foreach ($shopper_admin_msgs as $msg): 
                                        $role_class = 'chat-msg-' . $msg->sender_role;
                                        $is_admin = in_array($msg->sender_role, ['help', 'acct', 'admin'], true);
                                    ?>
                                        <div class="chat-msg <?= $role_class; ?> mb-3 p-3 rounded" style="background: <?= $is_admin ? '#eef5ff' : '#ffffff'; ?>; border: 1px solid <?= $is_admin ? '#cce2ff' : '#e5e9f0'; ?>;">
                                            <div class="chat-msg-meta mb-1 text-muted" style="font-size: 12px;">
                                                <strong class="<?= $is_admin ? 'text-primary' : 'text-dark'; ?>"><?= htmlspecialchars($msg->sender_name ?: ucfirst($msg->sender_role)); ?></strong> 
                                                (<?= ucfirst($msg->sender_role); ?>) &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                            </div>
                                            <div class="chat-msg-body" style="font-size: 14px; line-height: 1.5;">
                                                <?= nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                            <?php if (!empty($msg->attachment)): 
                                                $att_ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                                                $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                                $is_pdf  = ($att_ext === 'pdf');
                                                $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($msg->attachment));
                                                $att_download_url = $att_preview_url . '?download=1';
                                            ?>
                                                <div class="chat-attachment-box mt-2 p-2 rounded" style="background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); display: inline-block; max-width: 100%;">
                                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                        <span class="text-muted" style="font-size: 13px;">
                                                            <i class="fa <?= $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip text-secondary'); ?>"></i>
                                                            <strong><?= htmlspecialchars($msg->attachment); ?></strong>
                                                        </span>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Preview Evidence">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </a>
                                                            <a href="<?= $att_download_url; ?>" class="btn btn-sm btn-outline-secondary" title="Download">
                                                                <i class="fa fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php if ($is_img): ?>
                                                        <div class="attachment-preview mt-2" style="max-width: 320px; border-radius: 4px; overflow: hidden; border: 1px solid #ced4da; background: #fff;">
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" title="Click to view full size">
                                                                <img src="<?= $att_preview_url; ?>" alt="<?= htmlspecialchars($msg->attachment); ?>" style="width: 100%; max-height: 200px; object-fit: contain; display: block; background: #f8f9fa;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='block';}">
                                                                <div class="text-muted small p-2" style="display: none; background: #f8f9fa;"><i class="fa fa-info-circle text-info"></i> Thumbnail preview unavailable. Click Preview above to inspect.</div>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4">No dispute messages between Admin and Shopper yet.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Reply to Shopper Form -->
                            <?php if ($resolution->status !== 'Close (Final)'): ?>
                                <div class="card border border-primary mt-3">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <strong style="font-size: 13px;"><i class="fa fa-paper-plane text-primary"></i> Post Reply to Shopper</strong>
                                        <span class="badge badge-primary py-1 px-2 font-weight-bold"><i class="fa fa-lock"></i> Private: Admin ↔ Shopper</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="alert alert-primary py-2 px-3 mb-3" style="font-size: 12px; border: 1px solid #b8daff; color: #004085;">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div>
                                                    <i class="fa fa-user"></i> <strong>Recipient:</strong> <?= htmlspecialchars($customer ? trim($customer->first_name . ' ' . $customer->last_name) : 'Shopper'); ?> (Shopper)
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye-slash"></i> <strong>Visibility:</strong> Confidential between Support & Shopper only. <em>The Merchant cannot see this message.</em>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                            <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                            <input type="hidden" name="conversion_type" value="shopper_admin">
                                            <input type="hidden" name="role" value="help">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold" style="font-size: 12px;">Posting As:</label>
                                                <span class="badge badge-primary ml-1 py-1 px-2 font-weight-bold">Support Team (@Help)</span>
                                            </div>
                                            <div class="form-group mb-2">
                                                <textarea name="message" rows="3" class="form-control" placeholder="Write message to shopper..." required></textarea>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                                    <small class="text-muted">Optional attachment: JPG, PNG, WEBP, PDF (Max: 1 MB)</small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <button type="submit" class="btn btn-primary btn-sm px-3">
                                                        <i class="fa fa-paper-plane"></i> Send to Shopper
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pane 2: Admin <-> Merchant -->
                        <div class="tab-pane fade <?= $is_acct_active ? 'show active' : ''; ?>" id="pane-admin-merchant" role="tabpanel" aria-labelledby="tab-admin-merchant">
                            <div class="alert alert-warning py-2 px-3 mb-3 d-flex justify-content-between align-items-center flex-wrap text-dark" style="border: 1px solid #ffe699; font-size: 13px;">
                                <div>
                                    <i class="fa fa-lock text-warning"></i> <strong>Private Coordination Thread (Admin ↔ Merchant):</strong> Confidential coordination between YellowMarkets Support and the Merchant.
                                </div>
                                <span class="badge badge-warning text-dark py-1 px-2 font-weight-bold"><i class="fa fa-eye-slash"></i> Hidden from Shopper</span>
                            </div>
                            <div class="chat-box mb-3" style="max-height: 480px; overflow-y: auto; padding: 12px; background: #fafbfc; border: 1px solid #e9ecef; border-radius: 6px;">
                                <?php if (!empty($merchant_admin_msgs)): ?>
                                    <?php foreach ($merchant_admin_msgs as $msg): 
                                        $role_class = 'chat-msg-' . $msg->sender_role;
                                        $is_admin = in_array($msg->sender_role, ['help', 'acct', 'admin'], true);
                                    ?>
                                        <div class="chat-msg <?= $role_class; ?> mb-3 p-3 rounded" style="background: <?= $is_admin ? '#fef8ea' : '#ffffff'; ?>; border: 1px solid <?= $is_admin ? '#faebcc' : '#e5e9f0'; ?>;">
                                            <div class="chat-msg-meta mb-1 text-muted" style="font-size: 12px;">
                                                <strong class="<?= $is_admin ? 'text-warning' : 'text-dark'; ?>"><?= htmlspecialchars($msg->sender_name ?: ucfirst($msg->sender_role)); ?></strong> 
                                                (<?= ucfirst($msg->sender_role); ?>) &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                            </div>
                                            <div class="chat-msg-body" style="font-size: 14px; line-height: 1.5;">
                                                <?= nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                            <?php if (!empty($msg->attachment)): 
                                                $att_ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                                                $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                                $is_pdf  = ($att_ext === 'pdf');
                                                $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($msg->attachment));
                                                $att_download_url = $att_preview_url . '?download=1';
                                            ?>
                                                <div class="chat-attachment-box mt-2 p-2 rounded" style="background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); display: inline-block; max-width: 100%;">
                                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                        <span class="text-muted" style="font-size: 13px;">
                                                            <i class="fa <?= $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip text-secondary'); ?>"></i>
                                                            <strong><?= htmlspecialchars($msg->attachment); ?></strong>
                                                        </span>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Preview Evidence">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </a>
                                                            <a href="<?= $att_download_url; ?>" class="btn btn-sm btn-outline-secondary" title="Download">
                                                                <i class="fa fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php if ($is_img): ?>
                                                        <div class="attachment-preview mt-2" style="max-width: 320px; border-radius: 4px; overflow: hidden; border: 1px solid #ced4da; background: #fff;">
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" title="Click to view full size">
                                                                <img src="<?= $att_preview_url; ?>" alt="<?= htmlspecialchars($msg->attachment); ?>" style="width: 100%; max-height: 200px; object-fit: contain; display: block; background: #f8f9fa;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='block';}">
                                                                <div class="text-muted small p-2" style="display: none; background: #f8f9fa;"><i class="fa fa-info-circle text-info"></i> Thumbnail preview unavailable. Click Preview above to inspect.</div>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4">No coordination messages between Admin and Merchant yet.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Message to Merchant Form -->
                            <?php if ($resolution->status !== 'Close (Final)'): ?>
                                <div class="card border border-warning mt-3">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <strong style="font-size: 13px;"><i class="fa fa-envelope text-warning"></i> Send Message to Merchant</strong>
                                        <span class="badge badge-warning text-dark py-1 px-2 font-weight-bold"><i class="fa fa-lock"></i> Private: Admin ↔ Merchant</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="alert alert-warning py-2 px-3 mb-3 text-dark" style="font-size: 12px; border: 1px solid #ffe699;">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <div>
                                                    <i class="fa fa-building"></i> <strong>Recipient:</strong> <?= htmlspecialchars($merchant ? $merchant->publication_name : 'Merchant'); ?> (Merchant)
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye-slash"></i> <strong>Visibility:</strong> Confidential between Support & Merchant only. <em>The Shopper cannot see this message.</em>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                            <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                            <input type="hidden" name="conversion_type" value="merchant_admin">
                                            <input type="hidden" name="role" value="<?= htmlspecialchars($current_role); ?>">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold" style="font-size: 12px;">Posting As:</label>
                                                <span class="badge badge-<?= ($current_role === 'acct') ? 'warning' : 'info'; ?> ml-1 py-1 px-2 font-weight-bold">
                                                    <?= ($current_role === 'acct') ? 'Accounting Team (@Acct)' : 'Support Team (@Help)'; ?>
                                                </span>
                                            </div>
                                            <div class="form-group mb-2">
                                                <textarea name="message" rows="3" class="form-control" placeholder="Write coordination note to merchant..." required></textarea>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                                    <small class="text-muted">Optional attachment: JPG, PNG, WEBP, PDF (Max: 1 MB)</small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <button type="submit" class="btn btn-warning btn-sm px-3 font-weight-bold">
                                                        <i class="fa fa-paper-plane"></i> Send to Merchant
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Audit Log Table -->
                <h5 class="font-weight-bold mb-3"><i class="fa fa-history"></i> Audit Trail & State Transitions</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Action</th>
                                <th>From Status</th>
                                <th>To Status</th>
                                <th>Actor</th>
                                <th>Notes</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($audit_logs)): ?>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($log->action); ?></strong></td>
                                        <td><?= htmlspecialchars($log->from_status ?: 'None'); ?></td>
                                        <td><?= htmlspecialchars($log->to_status ?: 'None'); ?></td>
                                        <td><?= ucfirst($log->actor_role); ?> (ID: <?= $log->actor_id; ?>)</td>
                                        <td><?= htmlspecialchars($log->notes ?: '-'); ?></td>
                                        <td><?= date('d M Y, h:i A', $log->created_at); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted">No audit logs recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</main>

<!-- Modal: Assign to @Acct -->
<div class="modal fade" id="assignAcctModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/assign_to_acct'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="fa fa-user-plus"></i> Assign to Accounting (@Acct)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Assign this ticket to Accounting (@Acct) for refund or YM delivery processing. The ticket status will update to <strong>Processing</strong>, and notification emails will be sent to Merchant and @Acct.</p>
                    <div class="form-group">
                        <label>Notes for Accounting:</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter instructions or notes for accounting team..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Confirm Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Mark Done (@Acct) -->
<div class="modal fade" id="markDoneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/mark_done'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-check-circle"></i> Mark Done (Refund Processed from Hold-back)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the refund has been processed to the shopper from the merchant's 15-day hold-back sales balance. The Resolution status will update to <strong>Done</strong>, and @Help will be notified to proceed with final ticket closure.</p>
                    <div class="form-group">
                        <label>Accounting Completion Notes / Transaction ID:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Transaction Ref / Bank Details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirm Done</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Close Ticket (@Help) -->
<div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/close_ticket'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-secondary"><i class="fa fa-lock"></i> Close Ticket (@Help)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Close this ticket where the shopper still has a Resolution Option (e.g. they can click Resolution Request if dissatisfied). The status will update to <strong>Close</strong> and an email will be sent to the merchant.</p>
                    <div class="form-group">
                        <label>Closure Notes:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for closing..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary btn-sm">Confirm Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Close (Final) (@Help) -->
<div class="modal fade" id="closeFinalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/close_final'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="fa fa-archive"></i> Close (Final - No Further Options)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-danger font-weight-bold">Warning: This will permanently finalize this Resolution Request. No further actions or escalation options will be available.</p>
                    <div class="form-group">
                        <label>Final Closure Notes:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Final closure summary..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Permanently Finalize & Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Decision in favour of Shopper (@Help) -->
<div class="modal fade" id="resApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="decision" value="approved">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title text-white"><i class="fa fa-gavel"></i> Decision in favour of Shopper</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Decision reason (required):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Record reason and evidence" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Remedy:</label>
                        <select name="remedy" class="form-control">
                            <option value="return_refund" <?= ($resolution->category === 'Refund') ? 'selected' : ''; ?>>Return and Refund</option>
                            <option value="replacement" <?= ($resolution->category === 'Replacement') ? 'selected' : ''; ?>>Replacement</option>
                        </select>
                    </div>
                    <small class="text-muted">Both parties will receive official decision notification. Required Merchant fulfilment or Accounting refund verification will be initiated.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Confirm Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Decision in favour of Merchant (@Help) -->
<div class="modal fade" id="resDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="decision" value="denied">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark"><i class="fa fa-gavel"></i> Decision in favour of Merchant</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Decision reason (required):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Record reason and evidence" required></textarea>
                    </div>
                    <p class="text-muted small mb-0">Original rejection will be upheld. Ticket will be closed (Final) and dispute hold becomes eligible for release under the standard 7-day after Completed payout rule.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Confirm Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Fulfilment Checkpoints (@Help) -->
<div class="modal fade" id="fulfilmentCheckpointsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-tasks"></i> Fulfilment Checkpoints</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" style="line-height: 1.6; font-size: 14px;">
                <p><strong>Return and Refund:</strong> Returned item confirmed by Merchant or documented Admin exception &rarr; Accounting refund verification &rarr; close.</p>
                <p><strong>Replacement:</strong> Merchant fulfilment &rarr; Shopper receipt confirmation or documented Admin exception &rarr; disputed payout eligibility.</p>
                <p class="mb-0"><strong>In Favour of Merchant:</strong> Release dispute hold, subject to the 7-day after Completed standard payout rule.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Document Return Confirmation Exception (Workflow 5A) -->
<div class="modal fade" id="docReturnExceptionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('order_resolution/document_return_exception'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle"></i> Document Return Confirmation Exception</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        <strong>Workflow 5A Requirement:</strong> Return confirmation by the Merchant or a documented Admin exception is mandatory before Accounting can verify and process the refund.
                    </p>
                    <div class="form-group">
                        <label class="font-weight-bold">Justified Exception Reason (required):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Provide auditable justification (e.g. Return tracking shows delivered to merchant warehouse; merchant uncontactable; or return waived due to damaged/hazardous item)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold">Document Exception & Enable Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Document Replacement Receipt Exception (Workflow 5B) -->
<div class="modal fade" id="docReplacementExceptionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('order_resolution/document_replacement_exception'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-truck"></i> Document Replacement Receipt Exception</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        <strong>Workflow 5B Requirement:</strong> Shopper receipt confirmation or a documented Admin exception based on carrier delivery proof is required to close the replacement dispute and release the merchant payout hold.
                    </p>
                    <div class="form-group">
                        <label class="font-weight-bold">Justified Exception Reason / Carrier Proof (required):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Provide carrier name, tracking number, and delivery confirmation proof showing receipt by shopper" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold">Document Receipt Exception</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal: Complete YM Delivery (@Help) -->
<div class="modal fade" id="completeYmDeliveryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/complete_ym_delivery'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-truck"></i> Complete YM Delivery</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Confirm that Yellow Markets logistics has collected the replacement product from the merchant and successfully delivered it to the shopper.</p>
                    <div class="form-group mb-0">
                        <label>Delivery / Tracking Notes (optional):</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Delivered by YM courier partner on <?= date('d M Y'); ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirm Delivery Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Request AddOn Service Purchase (@Acct) -->
<div class="modal fade" id="requestAddonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/request_addon_delivery'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="fa fa-paper-plane"></i> Request AddOn Delivery Purchase</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Request the merchant to purchase the replacement delivery service as an <strong>AddOn Service</strong>. An official notification with service specifications and payment instructions will be sent to the merchant.</p>
                    <table class="table table-sm table-bordered bg-light mb-3">
                        <tr>
                            <th style="width: 40%;">Delivery Category:</th>
                            <td><?= htmlspecialchars($delivery_addon_service->category_name ?? 'YM Delivery Service'); ?></td>
                        </tr>
                        <tr>
                            <th>AddOn Service:</th>
                            <td><?= htmlspecialchars($delivery_addon_service->title ?? 'YM Delivery'); ?> (ID: <?= (int)($delivery_addon_service->id ?? 61); ?>)</td>
                        </tr>
                        <tr>
                            <th>Service Final Price:</th>
                            <td class="font-weight-bold text-success">MUR <?= number_format($delivery_addon_service->final_price ?? 1.00, 2); ?></td>
                        </tr>
                    </table>
                    <div class="form-group mb-0">
                        <label>Additional Notes / Instructions for Merchant (Optional):</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Please proceed with payment so our logistics partner can collect the item..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Send Purchase Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var operationCompleted = false;
    var pageReloading = false;

    function reloadPage(targetUrl) {
        if (pageReloading) return;
        pageReloading = true;
        if (targetUrl) {
            window.location.href = targetUrl;
        } else {
            window.location.reload();
        }
    }

    // Refresh current page only after an operation in a modal has successfully completed and modal closed
    $('.modal').on('hidden.bs.modal', function() {
        if (operationCompleted) {
            reloadPage();
        }
    });

    function showNotification(type, message) {
        var alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        var icon = (type === 'success') ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-exclamation-circle"></i> ';
        var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show resolution-ajax-alert" role="alert" style="position:fixed; top:20px; right:20px; z-index:99999; min-width:320px; max-width:480px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">' +
                   icon + message +
                   '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                   '</div>';
        $('.resolution-ajax-alert').remove();
        $('body').append(html);
        setTimeout(function() {
            $('.resolution-ajax-alert').fadeOut(function() { $(this).remove(); });
        }, 4000);
    }

    
    // Client-side file attachment validation (1 MB limit)
    $(document).on('change', 'input[type="file"][name="attachment"]', function() {
        var file = this.files && this.files[0];
        if (!file) return;

        var maxSize = 1024 * 1024; // 1 MB
        var allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        var fileName = file.name;
        var ext = fileName.split('.').pop().toLowerCase();

        if ($.inArray(ext, allowedExtensions) === -1) {
            showNotification('error', 'Invalid file format: ' + ext.toUpperCase() + '. Allowed formats: JPG, PNG, WEBP, PDF.');
            $(this).val('');
            return;
        }

        if (file.size > maxSize) {
            var sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            showNotification('error', 'File size exceeds 1 MB limit (' + sizeMB + ' MB selected). Please choose an image or PDF under 1 MB.');
            $(this).val('');
            return;
        }
    });

    $(document).on('submit', 'form.ajax-resolution-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        if ($btn.length === 0) {
            $btn = $form.find(':submit');
        }

        // Double-click guard
        if ($btn.prop('disabled')) {
            return false;
        }

        var origHtml = $btn.html();
        $btn.data('original-html', origHtml);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        var formData = new FormData(this);
        formData.append('is_ajax', '1');

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(res) {
                if (res && res.status) {
                    operationCompleted = true;
                    $btn.html('<i class="fa fa-check"></i> Done');
                    showNotification('success', res.message || 'Action processed successfully.');

                    var $modal = $form.closest('.modal');
                    if ($modal.length) {
                        try {
                            $modal.modal('hide');
                        } catch(e) {}
                    }

                    setTimeout(function() {
                        reloadPage(res.redirect || null);
                    }, 500);
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    var msg = (res && res.message) ? res.message : 'An error occurred while processing your request.';
                    showNotification('error', msg);

                    if (res && res.requires_reload) {
                        setTimeout(function() {
                            reloadPage();
                        }, 1200);
                    }
                }
            },
            error: function(xhr, status, err) {
                $btn.prop('disabled', false).html(origHtml);
                var errMsg = 'Unable to complete action. Please check your connection and try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed && parsed.message) {
                            errMsg = parsed.message;
                        }
                    } catch(e) {}
                }
                showNotification('error', errMsg);
            }
        });
    });
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
