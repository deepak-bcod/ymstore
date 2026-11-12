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
                            <div class="col-md-4 col-sm-6">
                                <strong><?= $this->lang->line('merchant_action') ?: 'Merchant Action'; ?>:</strong> 
                                <?php if (!empty($resolution->merchant_action) && !in_array(strtolower($resolution->merchant_action), ['none', 'n/a'], true)): ?>
                                    <span class="text-primary font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="background-color: #ffc107; color: #212529; font-weight: 600; padding: 4px 8px; border-radius: 4px;"><?= htmlspecialchars($this->lang->line('pending') ?: 'Pending'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <strong><?= $this->lang->line('delivery_option') ?: 'Delivery Option'; ?>:</strong> 
                                <span class="text-success font-weight-bold"><?= ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?></span>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <strong><?= $this->lang->line('refund_amount') ?: 'Refund Amount'; ?>:</strong> 
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
                                                        <a href="<?= base_url('addons/download_invoice/' . $resolution->addon_purchase_id); ?>" class="btn btn-sm btn-outline-primary ml-1" target="_blank" title="View / Download Invoice">
                                                            <i class="fa fa-file-text-o"></i> <?= $this->lang->line('invoice') ?: 'Invoice'; ?>
                                                        </a>
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

                    <!-- Warning Callout Matching MYP Wireframe -->
                    <div class="alert alert-warning py-3 mb-3" style="border-left: 4px solid #d4ac00; background: #fff8d8; color: #22272c; font-size: 14px;">
                        <i class="fa fa-info-circle text-warning mr-1"></i> <strong>The original Merchant decision has already been made. This Resolution Request is under YM Admin review. Merchant cannot issue a second verdict.</strong>
                    </div>

                    <?php if ($resolution->status === 'Close (Final)'): ?>
                        <div class="alert alert-secondary py-2 mb-3">
                            <i class="fa fa-lock"></i> <strong><?= $this->lang->line('closed_final_title') ?: 'Closed (Final):'; ?></strong> <?= $this->lang->line('closed_final_desc') ?: 'This resolution ticket has been closed (Final) with no further options.'; ?>
                        </div>
                    <?php endif; ?>

                    <!-- YM Admin Decision and Merchant Actions Section -->
                    <div class="p-3 bg-light border rounded mb-4">
                        <h6 class="font-weight-bold mb-2"><i class="fa fa-gavel"></i> YM Admin decision and Merchant actions</h6>

                        <?php 
                        $admin_dec = $resolution->admin_decision ?? '';
                        $res_stat  = $resolution->resolution_status ?? '';
                        $admin_rem = $resolution->admin_remedy ?? '';
                        $category_clean = strtolower(trim($resolution->category ?? ''));
                        $is_refund = in_array($category_clean, ['refund', 'return'], true) || ($admin_rem === 'return_refund');
                        $is_replacement = ($category_clean === 'replacement') || ($admin_rem === 'replacement');
                        $is_refund_ticket = $is_refund;
                        $is_replacement_ticket = $is_replacement;

                        $is_approved_shopper = ($admin_dec === 'in_favour_shopper' || $res_stat === 'resolution_approved' || $resolution->status === 'Processing');
                        $is_approved_merchant = ($admin_dec === 'in_favour_merchant' || $res_stat === 'resolution_denied' || ($resolution->status === 'Close (Final)' && !$is_approved_shopper));
                        ?>

                        <?php if ($is_approved_shopper): ?>
                            <?php if ($is_replacement): ?>
                                <div class="alert alert-success py-2 mb-3">
                                    <strong><i class="fa fa-check-circle"></i> YM Admin Decision: In Favour of Shopper – Replacement</strong>
                                    <p class="mb-0 small">Both parties have received official decision notification. Disputed funds remain held pending fulfilment and Shopper receipt confirmation.</p>
                                </div>
                                <h6 class="font-weight-bold text-dark mt-2 mb-1">Required Merchant fulfilment</h6>
                                <p class="text-muted small mb-3">Merchant arranges replacement. Select existing delivery method. Shopper confirmation (or documented Admin exception) is required before disputed payout eligibility.</p>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#replacementApproveModal" data-bs-toggle="modal" data-bs-target="#replacementApproveModal">
                                        <i class="fa fa-truck"></i> Arrange replacement
                                    </button>
                                    <?php if ($resolution->merchant_action !== 'replacement_completed'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replacementCompletedModal" data-bs-toggle="modal" data-bs-target="#replacementCompletedModal">
                                            <i class="fa fa-check-square-o"></i> Mark dispatched / delivered
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-success p-2">
                                            <i class="fa fa-check-circle"></i> Replacement Marked Dispatched / Delivered
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success py-2 mb-3">
                                    <strong><i class="fa fa-check-circle"></i> YM Admin Decision: In Favour of Shopper – Return and Refund</strong>
                                    <p class="mb-0 small">Both parties have received official decision notification.</p>
                                </div>
                                <h6 class="font-weight-bold text-dark mt-2 mb-1">Required Merchant fulfilment</h6>
                                <p class="text-muted small mb-3">Shopper return must be confirmed before Accounting processes the refund. YM Admin can document a justified return-confirmation exception.</p>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                                    <?php if (empty($resolution->merchant_return_confirmed)): ?>
                                        <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#confirmReturnModal" data-bs-toggle="modal" data-bs-target="#confirmReturnModal">
                                            <i class="fa fa-undo"></i> Confirm item returned
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-success p-2">
                                            <i class="fa fa-check-circle"></i> Returned Item Confirmed by Merchant
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($is_approved_merchant): ?>
                            <div class="alert alert-secondary py-2 mb-0">
                                <strong><i class="fa fa-lock"></i> YM Admin Decision: In Favour of Merchant</strong>
                                <p class="mb-0 small">Original rejection upheld. Dispute hold becomes eligible for release under the 7-day after Completed payout rule.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 mb-2" style="background: #fff8d8; border-left: 3px solid #d4ac00;">
                                <i class="fa fa-clock-o"></i> <strong>Admin decision pending.</strong> The Merchant may respond privately but cannot decide the Resolution outcome.
                            </div>
                            <?php if ($is_replacement && $resolution->status !== 'Close (Final)'): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <h6 class="font-weight-bold text-dark mb-1">Required Merchant fulfilment</h6>
                                    <p class="text-muted small mb-2">Merchant arranges replacement. Select existing delivery method. Shopper confirmation (or documented Admin exception) releases hold subject to payout eligibility.</p>
                                    <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
                                        <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#replacementApproveModal" data-bs-toggle="modal" data-bs-target="#replacementApproveModal">
                                            <i class="fa fa-truck"></i> Arrange replacement
                                        </button>
                                        <?php if (!empty($resolution->delivery_option) && !in_array($resolution->delivery_option, ['none', 'n/a'], true) && $resolution->merchant_action !== 'replacement_completed'): ?>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replacementCompletedModal" data-bs-toggle="modal" data-bs-target="#replacementCompletedModal">
                                                <i class="fa fa-check-square-o"></i> Mark dispatched / delivered
                                            </button>
                                        <?php elseif ($resolution->merchant_action === 'replacement_completed'): ?>
                                            <span class="badge badge-success p-2">
                                                <i class="fa fa-check-circle"></i> Replacement Marked Dispatched / Delivered
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

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
                            <span class="badge mr-1" style="background-color: #6f42c1; color: #fff;"><i class="fa fa-history"></i> Original Request Context</span>
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
                                
                                <div class="mt-2 p-2 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
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
                                    <div class="p-1 px-2 mt-1 rounded bg-light border text-danger" style="font-size: 12px; line-height: 1.4; background-color: #fff9f9 !important;">
                                        <i class="fa fa-commenting-o mr-1"></i> <?= htmlspecialchars($item_context->rejection_reason); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- ================= ORIGINAL DISPUTE CLAIM & SUBMITTED EVIDENCE ================= -->
                    <div class="card mb-4 shadow-sm" style="border-left: 4px solid #17a2b8;">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="font-weight-bold text-dark" style="font-size: 14px;">
                                <i class="fa fa-file-text-o text-info mr-1"></i> <?= $this->lang->line('original_dispute_claim_heading') ?: 'Original Dispute Claim & Submitted Evidence'; ?>
                            </span>
                            <div>
                                <span class="badge badge-info mr-1"><i class="fa fa-gavel"></i> <?= $this->lang->line('dispute_origin') ?: 'Dispute Origin'; ?></span>
                                <span class="text-muted small"><i class="fa fa-clock-o"></i> <?= $this->lang->line('submitted') ?: 'Submitted'; ?>: <?= date('d M Y, h:i A', $resolution->created_at); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-2">
                                        <strong class="text-muted small text-uppercase"><?= $this->lang->line('shopper_claim_reason') ?: "Shopper's Original Claim / Reason:"; ?></strong>
                                        <div class="p-3 mt-1 rounded bg-white border" style="font-size: 14px; line-height: 1.6; min-height: 50px;">
                                            <?= !empty($resolution->message) ? nl2br(htmlspecialchars($resolution->message, ENT_QUOTES, 'UTF-8')) : '<em class="text-muted">No text statement provided.</em>'; ?>
                                        </div>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fa fa-info-circle text-primary"></i> <strong><?= $this->lang->line('shared_dispute_context') ?: 'Shared Dispute Context:'; ?></strong> <?= $this->lang->line('shared_dispute_context_desc') ?: 'This is the initial claim statement submitted by the customer upon dispute creation. Shared across Shopper, Merchant, and Support.'; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-muted small text-uppercase"><?= $this->lang->line('initial_evidence') ?: 'Initial Evidence:'; ?></strong>
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
                                                    <i class="fa <?= $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip text-secondary'); ?>"></i>
                                                    <?= htmlspecialchars($resolution->attachment); ?>
                                                </span>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= $att_preview_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= $this->lang->line('preview') ?: 'Preview Evidence'; ?>">
                                                        <i class="fa fa-eye"></i> <?= $this->lang->line('preview') ?: 'Preview'; ?>
                                                    </a>
                                                    <a href="<?= $att_download_url; ?>" class="btn btn-sm btn-outline-secondary" title="<?= $this->lang->line('download') ?: 'Download'; ?>">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php if ($is_img): ?>
                                                <div style="max-height: 140px; overflow: hidden; border-radius: 4px; border: 1px solid #dee2e6; text-align: center; background: #f8f9fa;">
                                                    <a href="<?= $att_preview_url; ?>" target="_blank" title="<?= $this->lang->line('click_to_view_full') ?: 'Click to view full size'; ?>">
                                                        <img src="<?= $att_preview_url; ?>" alt="Evidence" style="max-height: 140px; width: auto; max-width: 100%; object-fit: contain;">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 mt-1 rounded bg-white border text-muted small">
                                            <i class="fa fa-ban"></i> <?= $this->lang->line('no_initial_evidence') ?: 'No file attachment uploaded with original claim.'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation with Support & Accounting (Shopper Chat Removed from Merchant Panel) -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark" style="font-size: 15px;">
                                <i class="fa fa-shield text-info mr-1"></i> <?= $this->lang->line('conversion_merchant_admin_tab') ?: 'Support & Accounting (Merchant ↔ Support & Accounting)'; ?>
                            </span>
                            <span class="badge badge-pill badge-info px-2 py-1"><?= count($merchant_admin_msgs); ?> <?= count($merchant_admin_msgs) === 1 ? 'Message' : 'Messages'; ?></span>
                        </div>

                        <div class="card-body p-3">
                            <div class="alert alert-warning py-2 px-3 mb-3 d-flex justify-content-between align-items-center flex-wrap" style="font-size: 13px; color: #856404; background-color: #fff3cd; border-color: #ffeeba;">
                                <div><i class="fa fa-lock text-warning mr-1"></i> <strong>Private Support &amp; Accounting Thread:</strong> Confidential coordination with YellowMarkets Support (@Help) and Accounting (@Acct). Both authorized teams have access to this thread.</div>
                                <span class="badge badge-warning"><i class="fa fa-eye-slash"></i> Hidden from Shopper</span>
                            </div>
                            <div class="chat-container mb-3" style="max-height: 480px; overflow-y: auto; padding: 12px; background: #fafbfc; border: 1px solid #e9ecef; border-radius: 6px;">
                                <?php if (!empty($merchant_admin_msgs)): ?>
                                    <?php foreach ($merchant_admin_msgs as $msg): 
                                        $is_merchant = ($msg->sender_role === 'merchant');
                                        $role_class  = 'chat-msg-' . $msg->sender_role;
                                        $sender_display = $is_merchant ? ($this->lang->line('you_merchant') ?: 'You (Merchant)') : (($msg->sender_role === 'acct') ? ($this->lang->line('accounting_team') ?: 'Accounting Team (@Acct)') : ($this->lang->line('support_team') ?: 'Support Team (@Help)'));
                                    ?>
                                        <div class="chat-msg <?= $role_class; ?> mb-3 p-3 rounded" style="background: <?= $is_merchant ? '#eef5ff' : '#fef8ea'; ?>; border: 1px solid <?= $is_merchant ? '#cce2ff' : '#faebcc'; ?>;">
                                            <div class="chat-msg-meta mb-1 text-muted" style="font-size: 12px;">
                                                <strong class="<?= $is_merchant ? 'text-primary' : 'text-warning'; ?>"><?= htmlspecialchars($sender_display); ?></strong> &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
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
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= $this->lang->line('preview') ?: 'Preview Evidence'; ?>">
                                                                <i class="fa fa-eye"></i> <?= $this->lang->line('preview') ?: 'Preview'; ?>
                                                            </a>
                                                            <a href="<?= $att_download_url; ?>" class="btn btn-sm btn-outline-secondary" title="<?= $this->lang->line('download') ?: 'Download'; ?>">
                                                                <i class="fa fa-download"></i> <?= $this->lang->line('download') ?: 'Download'; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php if ($is_img): ?>
                                                        <div class="attachment-preview mt-2" style="max-width: 320px; border-radius: 4px; overflow: hidden; border: 1px solid #ced4da; background: #fff;">
                                                            <a href="<?= $att_preview_url; ?>" target="_blank" title="<?= $this->lang->line('click_to_view_full') ?: 'Click to view full size'; ?>">
                                                                <img src="<?= $att_preview_url; ?>" alt="<?= htmlspecialchars($msg->attachment); ?>" style="width: 100%; max-height: 200px; object-fit: contain; display: block; background: #f8f9fa;" onerror="this.parentElement.parentElement.style.display='none';">
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4"><?= $this->lang->line('no_messages_admin_channel') ?: 'No conversation messages with YellowMarkets Support & Accounting yet.'; ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Message to Admin Form -->
                            <?php if ($resolution->status !== 'Close (Final)'): ?>
                                <div class="card border">
                                    <div class="card-header bg-light py-2">
                                        <strong style="font-size: 13px;"><i class="fa fa-envelope text-info"></i> <?= $this->lang->line('message_to_admin') ?: 'Message to YellowMarkets Support & Accounting'; ?></strong>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="alert alert-warning py-1 px-3 mb-3" style="font-size: 12px; color: #856404; background-color: #fff3cd; border-color: #ffeeba;">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <div><i class="fa fa-shield mr-1"></i> <strong>Recipient:</strong> YellowMarkets Support &amp; Accounting (@Help / @Acct)</div>
                                                <div><i class="fa fa-eye-slash mr-1"></i> <strong>Visibility:</strong> Confidential coordination between Merchant, Support, and Accounting. Both Support and Accounting can access this thread. <em>Shopper cannot view this thread.</em></div>
                                            </div>
                                        </div>
                                        <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                            <input type="hidden" name="conversion_type" value="merchant_admin">
                                            <div class="form-group mb-2">
                                                <textarea name="message" rows="3" class="form-control" placeholder="<?= $this->lang->line('enter_response_to_admin') ?: 'Enter your message or inquiry for YellowMarkets Support & Accounting...'; ?>" required></textarea>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                                    <small class="text-muted"><?= $this->lang->line('optional_attachment') ?: 'Optional attachment: JPG, PNG, WEBP, PDF (Max: 1 MB)'; ?></small>
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
</main>

<!-- Modal: Confirm Item Returned -->
<div class="modal fade" id="confirmReturnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('order_resolution/confirm_item_returned'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fa fa-undo"></i> Confirm Returned Item</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">Record evidence that the disputed item was returned by the shopper. Refund processing remains with Accounting.</p>
                    <div class="form-group">
                        <label class="font-weight-bold">Evidence / Reference (required):</label>
                        <textarea name="evidence" class="form-control" rows="3" placeholder="Return tracking number, collection receipt or inspection notes" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-dark btn-sm">Confirm Item Returned</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
<!-- Modal: Replacement Delivery Method (Wireframe Aligned) -->
<div class="modal fade" id="replacementApproveModal" tabindex="-1" role="dialog" aria-labelledby="replacementApproveModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 8px;">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_approved">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="replacementApproveModalLabel" style="font-size: 18px; color: #1e293b;">
                        <?= $this->lang->line('replacement_delivery_method') ?: 'Replacement delivery method'; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-secondary mb-1" style="font-size: 13.5px;"><?= $this->lang->line('method') ?: 'Method'; ?></label>
                        <select name="delivery_option" class="form-control" style="border-radius: 6px; height: 38px; font-size: 14px;" required>
                            <option value="own_delivery" <?= ($resolution->delivery_option === 'own_delivery') ? 'selected' : ''; ?>><?= $this->lang->line('own_delivery') ?: 'Own Delivery'; ?></option>
                            <option value="ym_delivery" <?= ($resolution->delivery_option === 'ym_delivery') ? 'selected' : ''; ?>><?= $this->lang->line('ym_delivery') ?: 'YM Delivery'; ?></option>
                            <option value="self_pickup" <?= ($resolution->delivery_option === 'self_pickup') ? 'selected' : ''; ?>><?= $this->lang->line('self_pickup') ?: 'Self Pickup'; ?></option>
                        </select>
                        <p class="text-muted small mt-2 mb-0" style="font-size: 13px; line-height: 1.4;">
                            <?= $this->lang->line('ym_delivery_note_wireframe') ?: 'YM Delivery retains the existing paid Add-on process; no purchase is made in this demo.'; ?>
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3" style="justify-content: flex-start; gap: 8px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-dismiss="modal" data-bs-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-dark btn-sm px-3 font-weight-bold"><?= $this->lang->line('confirm_action') ?: 'Confirm action'; ?></button>
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

    // Client-side file attachment validation (1 MB limit)
    $(document).on('change', 'input[type="file"][name="attachment"]', function() {
        var file = this.files && this.files[0];
        if (!file) return;

        var maxSize = 1024 * 1024; // 1 MB
        var allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        var fileName = file.name;
        var ext = fileName.split('.').pop().toLowerCase();

        if ($.inArray(ext, allowedExtensions) === -1) {
            alert('Invalid file format: ' + ext.toUpperCase() + '. Allowed formats: JPG, PNG, WEBP, PDF.');
            $(this).val('');
            return;
        }

        if (file.size > maxSize) {
            var sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            alert('File size exceeds 1 MB limit (' + sizeMB + ' MB selected). Please choose an image or PDF under 1 MB.');
            $(this).val('');
            return;
        }
    });

</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
