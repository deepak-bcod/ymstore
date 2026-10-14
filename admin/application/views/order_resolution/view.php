<?php 
$currency = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
$this->load->view('common/fbc-user/header'); 
?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 13px; }

.chat-box { max-height: 520px; overflow-y: auto; padding: 15px; background: #fafafa; border: 1px solid #e9ecef; border-radius: 6px; margin-bottom: 25px; }
.chat-msg { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; max-width: 85%; }
.chat-msg-shopper { background-color: #e3f2fd; border: 1px solid #bbdefb; margin-right: auto; }
.chat-msg-merchant { background-color: #f1f8e9; border: 1px solid #dcedc8; margin-right: auto; }
.chat-msg-help { background-color: #fff3e0; border: 1px solid #ffe0b2; margin-left: auto; text-align: left; }
.chat-msg-acct { background-color: #f3e5f5; border: 1px solid #e1bee7; margin-left: auto; text-align: left; }
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
                        <div class="col-md-3">
                            <strong>Merchant Action:</strong> 
                            <span class="text-primary font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Delivery Option:</strong> 
                            <span class="text-success font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Refund Amount:</strong> 
                            <span class="font-weight-bold <?= ($resolution->refund_amount > 0) ? 'text-success' : 'text-muted'; ?>">
                                <?php 
                                if ($resolution->merchant_action === 'refund_approved' || $resolution->resolution_status === 'resolution_approved') {
                                    echo $currency . ' ' . number_format($resolution->refund_amount, 2);
                                } elseif ($resolution->merchant_action === 'refund_denied' || $resolution->resolution_status === 'resolution_denied') {
                                    echo 'Denied';
                                } elseif ($resolution->category === 'Refund' && $resolution->refund_amount > 0) {
                                    echo $currency . ' ' . number_format($resolution->refund_amount, 2) . ' (Pending)';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Resolution Status:</strong> 
                            <span class="badge badge-<?= ($resolution->resolution_status === 'resolution_approved') ? 'success' : (($resolution->resolution_status === 'resolution_denied') ? 'danger' : 'warning'); ?>">
                                <?= ucwords(str_replace('_', ' ', $resolution->resolution_status)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php 
                $is_replacement_completed = ($resolution->merchant_action === 'replacement_completed');
                $is_direct_merchant_replacement = ($resolution->merchant_action === 'replacement_completed' && in_array($resolution->delivery_option, ['own_delivery', 'self_pickup'], true));
                $is_replacement_denied = ($resolution->merchant_action === 'replacement_denied');
                $hide_acct = ($is_replacement_completed || $is_replacement_denied);
                if ($hide_acct && $current_role === 'acct') {
                    $current_role = 'help';
                }
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
                            <?php if (!empty($resolution->addon_purchase_id)): ?>
                                <div class="alert alert-success py-2 mb-3">
                                    <i class="fa fa-check-circle"></i> <strong>AddOn Service Purchased:</strong> The merchant has completed payment for the replacement delivery AddOn Service (Purchase ID: #<?= (int)$resolution->addon_purchase_id; ?>). YellowMarkets delivery logistics can be scheduled.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>YM Delivery Replacement (AddOn Service Required):</strong> The merchant selected Yellow Markets delivery for this replacement. Accounting (@Acct) must request the merchant to purchase the replacement delivery service as an <strong>AddOn Service</strong>.
                                </div>
                                <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#requestAddonModal">
                                        <i class="fa fa-paper-plane"></i> Request AddOn Service Purchase
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($resolution->status === 'Processing'): ?>
                            <?php if ($resolution->delivery_option === 'ym_delivery'): ?>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fa fa-truck"></i> <strong>Assigned to Accounting (@Acct):</strong> Coordinate with the merchant to purchase the replacement delivery AddOn Service. Once delivery is completed by the merchant, the ticket will be handed back to @Help for closure.
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
                        <?php if ($resolution->status === 'ReOpen' || $resolution->resolution_status === 'resolution_requested'): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fa fa-gavel"></i> <strong>Shopper Dispute Active:</strong> The shopper has requested Yellow Markets assistance. Please investigate via the ticket conversation and choose <strong>Resolution Approved</strong> (in favour of Shopper) or <strong>Resolution Denied</strong> (in favour of Merchant).
                            </div>
                        <?php elseif ($resolution->status === 'Processing'): ?>
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fa fa-hourglass-half"></i> <strong>Assigned to Accounting (@Acct):</strong> Awaiting Accounting to process refund from merchant's 15-day hold-back sales.
                            </div>
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
                                <i class="fa fa-hourglass-start"></i> <strong>Awaiting Merchant Action:</strong> The merchant has not taken any action on this resolution request yet. Management controls will be activated once the merchant takes an action.
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
                                <?php if (!$has_merchant_action): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;" title="Cannot close ticket: Merchant has not taken any action yet.">
                                        <i class="fa fa-lock"></i> Close Ticket
                                    </button>
                                    <?php if (!$hide_acct): ?>
                                        <button type="button" class="btn btn-warning btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;" title="Cannot assign to @acct: Merchant has not taken any action yet.">
                                            <i class="fa fa-user-plus"></i> Assign to @acct (Processing)
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#closeTicketModal">
                                        <i class="fa fa-lock"></i> Close Ticket <?= ($resolution->merchant_action === 'refund_denied') ? '(Refund Denied by Merchant)' : (($resolution->merchant_action === 'replacement_denied') ? '(Replacement Denied by Merchant)' : ''); ?>
                                    </button>
                                    <?php if ($resolution->merchant_action !== 'refund_denied' && !$hide_acct): ?>
                                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#assignAcctModal">
                                            <i class="fa fa-user-plus"></i> Assign to @acct (Processing)
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Dispute Decisions (@Help Only when ticket is ReOpened / Disputed) -->
                            <?php if ($resolution->status === 'ReOpen' || $resolution->resolution_status === 'resolution_requested'): ?>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#resApproveModal">
                                    <i class="fa fa-thumbs-up"></i> Resolution Approved (In Favour of Shopper)
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#resDeniedModal">
                                    <i class="fa fa-thumbs-down"></i> Resolution Denied (In Favour of Merchant)
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
                                            <?php if (!empty($msg->attachment)): ?>
                                                <div class="mt-2">
                                                    <a href="<?= base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fa fa-paperclip"></i> Attachment (<?= htmlspecialchars($msg->attachment); ?>)
                                                    </a>
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
                                <div class="card border">
                                    <div class="card-header bg-light py-2">
                                        <strong style="font-size: 13px;"><i class="fa fa-paper-plane text-primary"></i> Post Reply to Shopper</strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                            <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                            <input type="hidden" name="conversion_type" value="shopper_admin">
                                            <input type="hidden" name="role" value="help">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold" style="font-size: 12px;">Posting As:</label>
                                                <span class="badge badge-primary ml-1 py-1 px-2 font-weight-bold">Support Team (@Help)</span>
                                            </div>
                                            <div class="form-group mb-2">
                                                <textarea name="message" rows="3" class="form-control" placeholder="Enter message to shopper..." required></textarea>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
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
                                            <?php if (!empty($msg->attachment)): ?>
                                                <div class="mt-2">
                                                    <a href="<?= base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fa fa-paperclip"></i> Attachment (<?= htmlspecialchars($msg->attachment); ?>)
                                                    </a>
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
                                <div class="card border">
                                    <div class="card-header bg-light py-2">
                                        <strong style="font-size: 13px;"><i class="fa fa-envelope text-warning"></i> Send Message to Merchant</strong>
                                    </div>
                                    <div class="card-body p-3">
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
                                                <textarea name="message" rows="3" class="form-control" placeholder="Enter message / coordination note for merchant..." required></textarea>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
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

<!-- Modal: Resolution Approved (@Help) -->
<div class="modal fade" id="resApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="decision" value="approved">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-thumbs-up"></i> Resolution Approved (@Help)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Approve the shopper's Resolution Request in their favor.</p>
                    <div class="form-group">
                        <label>Decision Notes:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes explaining approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirm Resolution Approved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Resolution Denied (@Help) -->
<div class="modal fade" id="resDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="decision" value="denied">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fa fa-thumbs-down"></i> Resolution Denied (@Help)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Deny the shopper's Resolution Request.</p>
                    <div class="form-group">
                        <label>Decision Notes:</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes explaining denial..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Resolution Denied</button>
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

    // Refresh current page only after an operation in a modal has successfully completed and modal closed
    $('.modal').on('hidden.bs.modal', function() {
        if (operationCompleted) {
            operationCompleted = false;
            window.location.href = window.location.href;
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
                    $btn.html('<i class="fa fa-check"></i> Done');
                    showNotification('success', res.message || 'Action processed successfully.');

                    var $modal = $form.closest('.modal');
                    if ($modal.length) {
                        operationCompleted = true;
                        $modal.modal('hide');
                    } else {
                        setTimeout(function() {
                            if (res.redirect) {
                                window.location.href = res.redirect;
                            } else {
                                window.location.href = window.location.href;
                            }
                        }, 800);
                    }
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    var msg = (res && res.message) ? res.message : 'An error occurred while processing your request.';
                    showNotification('error', msg);

                    if (res && res.requires_reload) {
                        setTimeout(function() {
                            window.location.href = window.location.href;
                        }, 1500);
                    }
                }
            },
            error: function(xhr, status, err) {
                $btn.prop('disabled', false).html(origHtml);
                var errMsg = 'Unable to complete action. Please check your connection and try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                showNotification('error', errMsg);
            }
        });
    });
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
