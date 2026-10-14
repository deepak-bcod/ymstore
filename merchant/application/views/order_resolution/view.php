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

.chat-container { max-height: 520px; overflow-y: auto; padding: 15px; background: #fafafa; border: 1px solid #e9ecef; border-radius: 6px; margin-bottom: 25px; }
.chat-msg { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; max-width: 85%; }
.chat-msg-shopper { background-color: #e3f2fd; border: 1px solid #bbdefb; margin-right: auto; }
.chat-msg-merchant { background-color: #f1f8e9; border: 1px solid #dcedc8; margin-left: auto; text-align: left; }
.chat-msg-help { background-color: #fff3e0; border: 1px solid #ffe0b2; margin-right: auto; }
.chat-msg-acct { background-color: #f3e5f5; border: 1px solid #e1bee7; margin-right: auto; }
.chat-msg-meta { font-size: 11px; color: #666; margin-bottom: 5px; }
.chat-msg-body { font-size: 14px; line-height: 1.5; color: #222; }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <div class="content-main form-dashboard helf-section">

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
                        <h4 class="mb-0 d-inline"><?= $this->lang->line('ticket') ?: 'Ticket'; ?> #<?= htmlspecialchars($resolution->ticket_number); ?></h4>
                        <span class="text-muted ml-2">(<?= $this->lang->line('order_number') ?: 'Order #'; ?> <?= htmlspecialchars($resolution->order_number); ?>)</span>
                    </div>
                    <div>
                        <?php 
                        $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $resolution->status));
                        $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $resolution->status));
                        $cat_key     = 'category_' . strtolower(trim($resolution->category ?? ''));
                        $pri_key     = 'priority_' . strtolower(trim($resolution->priority ?? ''));
                        ?>
                        <span class="badge-status-<?= $status_slug; ?>">
                            <?= $this->lang->line('status') ?: 'Status'; ?>: <?= htmlspecialchars($this->lang->line($status_key) ?: $resolution->status); ?>
                        </span>
                        <a href="<?= base_url('order_resolution'); ?>" class="btn btn-outline-secondary btn-sm ml-2">
                            <i class="fa fa-arrow-left"></i> <?= $this->lang->line('back_to_list') ?: 'Back to List'; ?>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Ticket Details Row -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <p class="mb-1"><strong><?= $this->lang->line('shopper') ?: 'Shopper'; ?>:</strong></p>
                            <p><?= htmlspecialchars($customer ? trim($customer->first_name . ' ' . $customer->last_name) : ($this->lang->line('shopper') ?: 'Shopper')); ?> 
                               <?= !empty($customer->email_id) ? '(' . htmlspecialchars($customer->email_id) . ')' : ''; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong><?= $this->lang->line('product') ?: 'Product'; ?>:</strong></p>
                            <p><?= htmlspecialchars($product ? $product->name : ($this->lang->line('all_items_general') ?: 'All Items / General')); ?></p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1"><strong><?= $this->lang->line('subject_type') ?: 'Subject Type'; ?>:</strong></p>
                            <p>
                                <span class="badge badge-info"><?= htmlspecialchars($this->lang->line($cat_key) ?: $resolution->category); ?></span>
                            </p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1"><strong><?= $this->lang->line('priority') ?: 'Priority'; ?>:</strong></p>
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
                                <span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($this->lang->line($pri_key) ?: ($resolution->priority ?: 'Medium')); ?></span>
                            </p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1"><strong><?= $this->lang->line('date_created') ?: 'Date Created'; ?>:</strong></p>
                            <p><?= date('d M Y, h:i A', $resolution->created_at); ?></p>
                        </div>
                    </div>

                    <!-- Status & Decision Details Banner -->
                    <div class="alert alert-secondary py-2 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong><?= $this->lang->line('merchant_action') ?: 'Merchant Action'; ?>:</strong> 
                                <span class="text-primary font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong><?= $this->lang->line('delivery_option') ?: 'Delivery Option'; ?>:</strong> 
                                <span class="text-success font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong><?= $this->lang->line('refund_amount') ?: 'Refund Amount'; ?>:</strong> 
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
                                <strong><?= $this->lang->line('resolution_status') ?: 'Resolution Status'; ?>:</strong> 
                                <span class="badge badge-<?= ($resolution->resolution_status === 'resolution_approved') ? 'success' : (($resolution->resolution_status === 'resolution_denied') ? 'danger' : 'warning'); ?>">
                                    <?= ucwords(str_replace('_', ' ', $resolution->resolution_status)); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- YM Delivery Replacement: AddOn Service Card -->
                    <?php if ($resolution->delivery_option === 'ym_delivery' && $resolution->merchant_action === 'replacement_approved'): ?>
                        <?php if (!empty($is_addon_paid)): ?>
                            <div class="alert alert-success alert-dismissible fade show mb-4 border-success shadow-sm" role="alert" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 6px;">
                                <h5 class="alert-heading font-weight-bold mb-2">
                                    <i class="fa fa-check-circle text-success"></i> <?= $this->lang->line('addon_purchased_success_title') ?: 'AddOn Service Purchased Successfully'; ?>
                                </h5>
                                <p class="mb-2">
                                    <?= $this->lang->line('addon_purchased_success_desc') ?: 'Your YM Delivery AddOn Service payment has been confirmed. You can now proceed with completing the replacement.'; ?>
                                </p>
                                <hr style="border-top-color: #c3e6cb; margin: 10px 0;">
                                <div class="bg-white p-2 rounded border border-success d-flex flex-wrap align-items-center justify-content-between" style="font-size: 13px;">
                                    <span><strong><?= $this->lang->line('order_number') ?: 'Order Number'; ?>:</strong> #<?= htmlspecialchars($resolution->order_number); ?></span>
                                    <span><strong><?= $this->lang->line('resolution_ticket') ?: 'Resolution Ticket'; ?>:</strong> <?= htmlspecialchars($resolution->ticket_number); ?></span>
                                    <span><strong><?= $this->lang->line('service_name') ?: 'Service Name'; ?>:</strong> <?= htmlspecialchars($delivery_addon_service->title ?? 'YM Delivery'); ?></span>
                                    <span><strong><?= $this->lang->line('paid_amount') ?: 'Paid Amount'; ?>:</strong> <?= $currency . ' ' . number_format($delivery_addon_service->final_price ?? 1.00, 2); ?></span>
                                    <span><strong><?= $this->lang->line('payment_reference') ?: 'Payment Reference'; ?>:</strong> <?= htmlspecialchars($addon_transaction->transaction_ref ?? (!empty($addon_purchase) ? 'Purchase #' . $addon_purchase->id : 'N/A')); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fa fa-truck"></i> <?= $this->lang->line('ym_delivery_addon_required_title') ?: 'YM Delivery Replacement — AddOn Service Required'; ?>
                                </h6>
                                <?php if (!empty($is_addon_paid)): ?>
                                    <span class="badge badge-success p-2"><i class="fa fa-check"></i> <?= $this->lang->line('addon_status_paid') ?: 'AddOn Paid'; ?></span>
                                <?php elseif (!empty($is_addon_failed)): ?>
                                    <span class="badge badge-danger p-2"><i class="fa fa-times"></i> <?= $this->lang->line('payment_failed') ?: 'Payment Failed'; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning p-2"><i class="fa fa-clock-o"></i> <?= $this->lang->line('addon_status_pending') ?: 'Payment Required'; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">
                                    <?= $this->lang->line('ym_delivery_addon_desc') ?: 'You selected Yellow Markets delivery for this replacement. Accounting (@Acct) has requested you to purchase the replacement delivery service as an AddOn Service so logistics can collect and deliver the replacement product.'; ?>
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-3">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><?= $this->lang->line('order_number') ?: 'Order Number'; ?></th>
                                                <th><?= $this->lang->line('resolution_ticket') ?: 'Resolution Ticket'; ?></th>
                                                <th><?= $this->lang->line('product') ?: 'Product'; ?></th>
                                                <th><?= $this->lang->line('delivery_service') ?: 'Delivery Service'; ?></th>
                                                <th><?= $this->lang->line('addon_service') ?: 'AddOn Service'; ?></th>
                                                <th><?= $this->lang->line('amount') ?: 'Amount'; ?></th>
                                                <th><?= $this->lang->line('payment_status') ?: 'Payment Status'; ?></th>
                                                <th><?= $this->lang->line('confirmation') ?: 'Confirmation'; ?></th>
                                                <th><?= $this->lang->line('purchase_action') ?: 'Purchase Action'; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>#<?= htmlspecialchars($resolution->order_number); ?></strong></td>
                                                <td><span class="badge badge-secondary"><?= htmlspecialchars($resolution->ticket_number); ?></span></td>
                                                <td><?= htmlspecialchars($product ? $product->name : 'N/A'); ?></td>
                                                <td><span class="text-info font-weight-bold">YellowMarkets Delivery</span></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($delivery_addon_service->title ?? 'YM Delivery'); ?></strong>
                                                    <?php if (!empty($delivery_addon_service->category_name)): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($delivery_addon_service->category_name); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="font-weight-bold text-success"><?= $currency . ' ' . number_format($delivery_addon_service->final_price ?? 1.00, 2); ?></td>
                                                <td>
                                                    <?php if (!empty($is_addon_paid)): ?>
                                                        <span class="badge badge-success"><i class="fa fa-check"></i> <?= $this->lang->line('paid') ?: 'Paid'; ?></span>
                                                    <?php elseif (!empty($is_addon_failed)): ?>
                                                        <span class="badge badge-danger"><i class="fa fa-times"></i> <?= $this->lang->line('failed') ?: 'Failed'; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning"><i class="fa fa-clock-o"></i> <?= $this->lang->line('pending') ?: 'Pending'; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= !empty($addon_transaction->transaction_ref) ? htmlspecialchars($addon_transaction->transaction_ref) : (!empty($addon_purchase) ? 'Purchase #' . $addon_purchase->id : 'Awaiting Payment'); ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($is_addon_paid)): ?>
                                                        <button class="btn btn-sm btn-success disabled" disabled>
                                                            <i class="fa fa-check"></i> <?= $this->lang->line('addon_service_paid') ?: 'Purchased'; ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('addons/buy_resolution_delivery/' . $resolution->ticket_number); ?>" class="btn btn-sm btn-info font-weight-bold">
                                                            <i class="fa fa-shopping-cart"></i> <?= $this->lang->line('purchase_addon_service') ?: 'Purchase AddOn Service'; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-light border py-2 mb-0">
                                    <strong><i class="fa fa-info-circle"></i> <?= $this->lang->line('instructions') ?: 'Instructions'; ?>:</strong>
                                    <?= $this->lang->line('addon_merchant_instruction') ?: 'Click "Purchase AddOn Service" to complete the payment. Once confirmed, YellowMarkets delivery will be scheduled, and our logistics partner will collect and deliver the replacement product.'; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resolution->resolution_status === 'resolution_requested'): ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="fa fa-exclamation-triangle"></i> <strong><?= $this->lang->line('dispute_under_review') ?: 'Dispute Under Review:'; ?></strong> <?= $this->lang->line('dispute_under_review_desc') ?: 'The shopper has requested resolution assistance from Yellow Markets (@Help). Support is investigating and will communicate via the ticket conversation.'; ?>
                        </div>
                    <?php elseif ($resolution->status === 'Close (Final)'): ?>
                        <div class="alert alert-secondary py-2 mb-3">
                            <i class="fa fa-lock"></i> <strong><?= $this->lang->line('closed_final_title') ?: 'Closed (Final):'; ?></strong> <?= $this->lang->line('closed_final_desc') ?: 'This resolution ticket has been closed (Final) with no further options.'; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons Section -->
                    <div class="p-3 bg-light border rounded mb-4">
                        <h6 class="font-weight-bold mb-3"><i class="fa fa-gavel"></i> <?= $this->lang->line('resolution_decision_actions') ?: 'Order Resolution Decision Actions'; ?></h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">

                            <?php 
                            $category_clean = strtolower(trim($resolution->category ?? ''));
                            $is_refund_ticket = in_array($category_clean, ['refund', 'return'], true);
                            $is_replacement_ticket = ($category_clean === 'replacement');
                            ?>

                            <?php if ($is_refund_ticket): ?>
                                <!-- REFUND DECISION BUTTONS (Subject Type: Refund) -->
                                <?php if ($resolution->merchant_action === 'none'): ?>
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#refundApproveModal" data-bs-toggle="modal" data-bs-target="#refundApproveModal">
                                        <i class="fa fa-check"></i> <?= $this->lang->line('refund_approved') ?: 'Refund Approved'; ?>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#refundDeniedModal" data-bs-toggle="modal" data-bs-target="#refundDeniedModal">
                                        <i class="fa fa-times"></i> <?= $this->lang->line('refund_denied') ?: 'Refund Denied'; ?>
                                    </button>
                                <?php elseif ($resolution->merchant_action === 'refund_denied'): ?>
                                    <span class="badge badge-danger p-2">
                                        <i class="fa fa-times-circle"></i> <?= $this->lang->line('refund_denied') ?: 'Refund Denied'; ?>
                                    </span>
                                <?php elseif ($resolution->merchant_action === 'refund_approved'): ?>
                                    <span class="badge badge-success p-2">
                                        <i class="fa fa-check-circle"></i> <?= $this->lang->line('refund_approved') ?: 'Refund Approved'; ?> (<?= $currency . ' ' . number_format($resolution->refund_amount, 2); ?>)
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($is_replacement_ticket): ?>
                                <!-- REPLACEMENT DECISION BUTTONS (Subject Type: Replacement) -->
                                <?php if ($resolution->merchant_action === 'none'): ?>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#replacementApproveModal" data-bs-toggle="modal" data-bs-target="#replacementApproveModal">
                                        <i class="fa fa-refresh"></i> <?= $this->lang->line('replacement_approved') ?: 'Replacement Approved'; ?>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#replacementDeniedModal" data-bs-toggle="modal" data-bs-target="#replacementDeniedModal">
                                        <i class="fa fa-ban"></i> <?= $this->lang->line('replacement_denied') ?: 'Replacement Denied'; ?>
                                    </button>
                                <?php elseif ($resolution->merchant_action === 'replacement_denied'): ?>
                                    <span class="badge badge-danger p-2">
                                        <i class="fa fa-times-circle"></i> <?= $this->lang->line('replacement_denied') ?: 'Replacement Denied'; ?>
                                    </span>
                                <?php elseif ($resolution->merchant_action === 'replacement_approved'): ?>
                                    <span class="badge badge-info p-2">
                                        <i class="fa fa-check-circle"></i> <?= $this->lang->line('replacement_approved') ?: 'Replacement Approved'; ?> (<?= htmlspecialchars($this->lang->line($resolution->delivery_option) ?: ucwords(str_replace('_', ' ', $resolution->delivery_option))); ?>)
                                    </span>
                                    <!-- Replacement Completed: displayed ONLY after Replacement Approved AND delivery option selected -->
                                    <?php if ($resolution->delivery_option === 'ym_delivery' && empty($is_addon_paid)): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.65; cursor: not-allowed;" title="<?= htmlspecialchars($this->lang->line('addon_payment_required_tooltip') ?: 'Please purchase and complete payment for the YM Delivery AddOn service before marking replacement as completed.'); ?>">
                                            <i class="fa fa-lock"></i> <?= $this->lang->line('replacement_completed') ?: 'Replacement Completed'; ?>
                                        </button>
                                        <span class="badge badge-warning text-dark p-2 ml-1" style="font-size: 11px;">
                                            <i class="fa fa-exclamation-triangle"></i> <?= $this->lang->line('addon_service_payment_required') ?: 'YM Delivery AddOn Payment Required'; ?>
                                        </span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replacementCompletedModal" data-bs-toggle="modal" data-bs-target="#replacementCompletedModal">
                                            <i class="fa fa-check-square-o"></i> <?= $this->lang->line('replacement_completed') ?: 'Replacement Completed'; ?>
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($resolution->merchant_action === 'replacement_completed'): ?>
                                    <span class="badge badge-primary p-2">
                                        <i class="fa fa-check-circle"></i> <?= $this->lang->line('replacement_completed') ?: 'Replacement Completed'; ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php
                    // Separate conversion workflows
                    $shopper_merchant_msgs = [];
                    $merchant_admin_msgs  = [];
                    if (!empty($messages)) {
                        foreach ($messages as $msg) {
                            $c_type = !empty($msg->conversion_type) ? $msg->conversion_type : '';
                            if ($c_type === 'merchant_admin' || in_array($msg->sender_role, ['acct', 'help'], true)) {
                                $merchant_admin_msgs[] = $msg;
                            } else {
                                $shopper_merchant_msgs[] = $msg;
                            }
                        }
                    }
                    ?>

                    <!-- Conversation Workflows Navigation Tabs -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white p-0 border-bottom">
                            <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="merchantConvTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold py-3 px-4" id="tab-shopper-conv" data-toggle="tab" data-bs-toggle="tab" href="#pane-shopper-conv" role="tab" aria-controls="pane-shopper-conv" aria-selected="true">
                                        <i class="fa fa-user text-primary mr-1"></i> <?= $this->lang->line('conversion_shopper_merchant_tab') ?: 'Shopper (Merchant ↔ Shopper)'; ?>
                                        <span class="badge badge-pill badge-primary ml-1"><?= count($shopper_merchant_msgs); ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold py-3 px-4" id="tab-admin-conv" data-toggle="tab" data-bs-toggle="tab" href="#pane-admin-conv" role="tab" aria-controls="pane-admin-conv" aria-selected="false">
                                        <i class="fa fa-shield text-info mr-1"></i> <?= $this->lang->line('conversion_merchant_admin_tab') ?: 'YellowMarkets Admin (Merchant ↔ Admin)'; ?>
                                        <span class="badge badge-pill badge-info ml-1"><?= count($merchant_admin_msgs); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content card-body p-3" id="merchantConvContent">
                            <!-- Pane 1: Merchant <-> Shopper -->
                            <div class="tab-pane fade show active" id="pane-shopper-conv" role="tabpanel" aria-labelledby="tab-shopper-conv">
                                <div class="chat-container mb-3" style="max-height: 480px; overflow-y: auto; padding: 12px; background: #fafbfc; border: 1px solid #e9ecef; border-radius: 6px;">
                                    <?php if (!empty($shopper_merchant_msgs)): ?>
                                        <?php foreach ($shopper_merchant_msgs as $msg): 
                                            $is_merchant = ($msg->sender_role === 'merchant');
                                            $role_class  = 'chat-msg-' . $msg->sender_role;
                                            $sender_display = $is_merchant ? ($this->lang->line('you_merchant') ?: 'You (Merchant)') : ($this->lang->line('shopper') ?: 'Shopper');
                                        ?>
                                            <div class="chat-msg <?= $role_class; ?> mb-3 p-3 rounded" style="background: <?= $is_merchant ? '#eef5ff' : '#ffffff'; ?>; border: 1px solid <?= $is_merchant ? '#cce2ff' : '#e5e9f0'; ?>;">
                                                <div class="chat-msg-meta mb-1 text-muted" style="font-size: 12px;">
                                                    <strong class="<?= $is_merchant ? 'text-primary' : 'text-dark'; ?>"><?= htmlspecialchars($sender_display); ?></strong> &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                                </div>
                                                <div class="chat-msg-body" style="font-size: 14px; line-height: 1.5;">
                                                    <?= nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                                <?php if (!empty($msg->attachment)): ?>
                                                    <div class="mt-2">
                                                        <a href="<?= base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa fa-paperclip"></i> <?= $this->lang->line('view_attachment') ?: 'View Attachment'; ?> (<?= htmlspecialchars($msg->attachment); ?>)
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4"><?= $this->lang->line('no_messages_shopper_channel') ?: 'No conversation messages with shopper yet.'; ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Reply to Shopper Form -->
                                <?php if ($resolution->status !== 'Close (Final)'): ?>
                                    <div class="card border">
                                        <div class="card-header bg-light py-2">
                                            <strong style="font-size: 13px;"><i class="fa fa-paper-plane text-primary"></i> <?= $this->lang->line('reply_to_shopper') ?: 'Reply to Shopper'; ?></strong>
                                        </div>
                                        <div class="card-body p-3">
                                            <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                                <input type="hidden" name="conversion_type" value="shopper_merchant">
                                                <div class="form-group mb-2">
                                                    <textarea name="message" rows="3" class="form-control" placeholder="<?= $this->lang->line('enter_response_to_shopper') ?: 'Enter your response to the shopper...'; ?>" required></textarea>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                                        <small class="text-muted"><?= $this->lang->line('optional_attachment') ?: 'Optional attachment (Max: 5MB)'; ?></small>
                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <button type="submit" class="btn btn-primary btn-sm px-3">
                                                            <i class="fa fa-paper-plane"></i> <?= $this->lang->line('send_reply') ?: 'Send Reply'; ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Pane 2: Merchant <-> Admin -->
                            <div class="tab-pane fade" id="pane-admin-conv" role="tabpanel" aria-labelledby="tab-admin-conv">
                                <div class="chat-container mb-3" style="max-height: 480px; overflow-y: auto; padding: 12px; background: #fafbfc; border: 1px solid #e9ecef; border-radius: 6px;">
                                    <?php if (!empty($merchant_admin_msgs)): ?>
                                        <?php foreach ($merchant_admin_msgs as $msg): 
                                            $is_merchant = ($msg->sender_role === 'merchant');
                                            $role_class  = 'chat-msg-' . $msg->sender_role;
                                            $sender_display = $is_merchant ? ($this->lang->line('you_merchant') ?: 'You (Merchant)') : (($msg->sender_role === 'acct') ? ($this->lang->line('accounting_team') ?: 'Accounting Team (@Acct)') : ($this->lang->line('support_team') ?: 'Yellow Markets Support'));
                                        ?>
                                            <div class="chat-msg <?= $role_class; ?> mb-3 p-3 rounded" style="background: <?= $is_merchant ? '#eef5ff' : '#fef8ea'; ?>; border: 1px solid <?= $is_merchant ? '#cce2ff' : '#faebcc'; ?>;">
                                                <div class="chat-msg-meta mb-1 text-muted" style="font-size: 12px;">
                                                    <strong class="<?= $is_merchant ? 'text-primary' : 'text-warning'; ?>"><?= htmlspecialchars($sender_display); ?></strong> &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                                </div>
                                                <div class="chat-msg-body" style="font-size: 14px; line-height: 1.5;">
                                                    <?= nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                                <?php if (!empty($msg->attachment)): ?>
                                                    <div class="mt-2">
                                                        <a href="<?= base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa fa-paperclip"></i> <?= $this->lang->line('view_attachment') ?: 'View Attachment'; ?> (<?= htmlspecialchars($msg->attachment); ?>)
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4"><?= $this->lang->line('no_messages_admin_channel') ?: 'No conversation messages with YellowMarkets Admin / Accounting yet.'; ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Message to Admin Form -->
                                <?php if ($resolution->status !== 'Close (Final)'): ?>
                                    <div class="card border">
                                        <div class="card-header bg-light py-2">
                                            <strong style="font-size: 13px;"><i class="fa fa-envelope text-info"></i> <?= $this->lang->line('message_to_admin') ?: 'Message to YellowMarkets Support & Accounting'; ?></strong>
                                        </div>
                                        <div class="card-body p-3">
                                            <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                                <input type="hidden" name="conversion_type" value="merchant_admin">
                                                <div class="form-group mb-2">
                                                    <textarea name="message" rows="3" class="form-control" placeholder="<?= $this->lang->line('enter_response_to_admin') ?: 'Enter your message or inquiry for YellowMarkets Admin / Accounting team...'; ?>" required></textarea>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                                        <small class="text-muted"><?= $this->lang->line('optional_attachment') ?: 'Optional attachment (Max: 5MB)'; ?></small>
                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <button type="submit" class="btn btn-info btn-sm px-3 text-white">
                                                            <i class="fa fa-paper-plane"></i> <?= $this->lang->line('send_message') ?: 'Send Message'; ?>
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

                </div>
            </div>

        </div>
    </div>
</main>

<?php if ($is_refund_ticket): ?>
<!-- Modal: Refund Approved -->
<div class="modal fade" id="refundApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="refund_approved">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-check-circle"></i> <?= $this->lang->line('approve_refund') ?: 'Approve Refund'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?= sprintf($this->lang->line('confirm_approved_refund_amount') ?: 'Confirm the approved refund amount for Order #%s.', htmlspecialchars($resolution->order_number)); ?></p>
                    <div class="form-group">
                        <label><?= $this->lang->line('refund_amount') ?: 'Refund Amount'; ?> (<?= $currency ?>):</label>
                        <input type="number" step="0.01" min="0.01" name="refund_amount" class="form-control" 
                               value="<?= !empty($order->grand_total) ? number_format((float)$order->grand_total, 2, '.', '') : '0.00'; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-success btn-sm"><?= $this->lang->line('confirm_refund_approval') ?: 'Confirm Refund Approval'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Refund Denied -->
<div class="modal fade" id="refundDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="refund_denied">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fa fa-times-circle"></i> <?= $this->lang->line('deny_refund') ?: 'Deny Refund'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?= $this->lang->line('refund_deny_confirm_msg') ?: 'Are you sure you want to deny this refund request? Support (@Help) will be notified.'; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-danger btn-sm"><?= $this->lang->line('confirm_refund_denied') ?: 'Confirm Refund Denied'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($is_replacement_ticket): ?>
<!-- Modal: Replacement Approved with Delivery Options -->
<div class="modal fade" id="replacementApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_approved">
                <div class="modal-header">
                    <h5 class="modal-title text-info"><i class="fa fa-refresh"></i> <?= $this->lang->line('approve_replacement') ?: 'Approve Replacement'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?= $this->lang->line('select_replacement_delivery_method') ?: 'Select the replacement delivery method:'; ?></p>
                    <div class="form-group">
                        <label><strong><?= $this->lang->line('delivery_option') ?: 'Delivery Option'; ?>:</strong> <span class="text-danger">*</span></label>
                        <select name="delivery_option" class="form-control" required>
                            <option value=""><?= $this->lang->line('select_delivery_option') ?: '-- Select Delivery Option --'; ?></option>
                            <option value="own_delivery"><?= $this->lang->line('own_delivery') ?: 'Own Delivery'; ?></option>
                            <option value="self_pickup"><?= $this->lang->line('self_pickup') ?: 'Self Pickup'; ?></option>
                            <option value="ym_delivery"><?= $this->lang->line('ym_delivery') ?: 'YM Delivery Service'; ?></option>
                        </select>
                        <small class="form-text text-muted">
                            <?= $this->lang->line('ym_delivery_note') ?: 'Selecting YM Delivery Service notifies Yellow Markets (@Help) to organize fulfillment.'; ?>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-info btn-sm"><?= $this->lang->line('confirm_replacement_approved') ?: 'Confirm Replacement Approved'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replacement Denied -->
<div class="modal fade" id="replacementDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_denied">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fa fa-ban"></i> <?= $this->lang->line('deny_replacement') ?: 'Deny Replacement'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?= $this->lang->line('replacement_deny_confirm_msg') ?: 'Are you sure you want to deny this replacement request? The product status will be set to Replacement Rejected.'; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-danger btn-sm"><?= $this->lang->line('confirm_replacement_denied') ?: 'Confirm Replacement Denied'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replacement Completed -->
<div class="modal fade" id="replacementCompletedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_completed">
                <div class="modal-header">
                    <h5 class="modal-title text-primary"><i class="fa fa-check-square-o"></i> <?= $this->lang->line('mark_replacement_completed') ?: 'Mark Replacement Completed'; ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?= $this->lang->line('replacement_completed_confirm_msg') ?: 'Confirm that the replacement product has been successfully delivered / collected. Support (@Help) will be notified.'; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-primary btn-sm"><?= $this->lang->line('mark_replacement_completed') ?: 'Mark Replacement Completed'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
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

    // Refresh current page whenever an operation in a modal has completed and modal closes
    $('.modal').on('hidden.bs.modal', function() {
        if (operationCompleted) {
            reloadPage();
        }
    });

    // Explicit click handlers for decision action modal buttons
    $('[data-target="#refundApproveModal"], [data-bs-target="#refundApproveModal"]').on('click', function(e) {
        e.preventDefault();
        $('#refundApproveModal').modal('show');
    });
    $('[data-target="#refundDeniedModal"], [data-bs-target="#refundDeniedModal"]').on('click', function(e) {
        e.preventDefault();
        $('#refundDeniedModal').modal('show');
    });
    $('[data-target="#replacementApproveModal"], [data-bs-target="#replacementApproveModal"]').on('click', function(e) {
        e.preventDefault();
        $('#replacementApproveModal').modal('show');
    });
    $('[data-target="#replacementDeniedModal"], [data-bs-target="#replacementDeniedModal"]').on('click', function(e) {
        e.preventDefault();
        $('#replacementDeniedModal').modal('show');
    });
    $('[data-target="#replacementCompletedModal"], [data-bs-target="#replacementCompletedModal"]').on('click', function(e) {
        e.preventDefault();
        $('#replacementCompletedModal').modal('show');
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
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + (<?= json_encode($this->lang->line('ajax_processing') ?: 'Processing...'); ?>));

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
                    $btn.html('<i class="fa fa-check"></i> ' + (<?= json_encode($this->lang->line('ajax_done') ?: 'Done'); ?>));
                    showNotification('success', res.message || (<?= json_encode($this->lang->line('action_processed_success') ?: 'Action processed successfully.'); ?>));

                    var $modal = $form.closest('.modal');
                    if ($modal.length) {
                        try {
                            $modal.modal('hide');
                        } catch(e) {}
                    }

                    // Always refresh the page after popup or action completes
                    setTimeout(function() {
                        reloadPage(res.redirect || null);
                    }, 500);
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    var msg = (res && res.message) ? res.message : (<?= json_encode($this->lang->line('ajax_error') ?: 'An error occurred while processing your request.'); ?>);
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
                var errMsg = <?= json_encode($this->lang->line('ajax_error') ?: 'Unable to complete action. Please check your connection and try again.'); ?>;
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
