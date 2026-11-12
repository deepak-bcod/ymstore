<?php $this->load->view('common/header'); ?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold; }

.chat-box { max-height: 550px; overflow-y: auto; padding: 15px; background: #fdfdfd; border: 1px solid #e9ecef; border-radius: 6px; margin-bottom: 25px; }
.chat-message { margin-bottom: 18px; padding: 12px 16px; border-radius: 8px; position: relative; max-width: 85%; }
.chat-shopper { background-color: #e3f2fd; border: 1px solid #bbdefb; margin-left: auto; text-align: left; }
.chat-merchant { background-color: #f1f8e9; border: 1px solid #dcedc8; margin-right: auto; }
.chat-help { background-color: #fff3e0; border: 1px solid #ffe0b2; margin-right: auto; }
.chat-acct { background-color: #f3e5f5; border: 1px solid #e1bee7; margin-right: auto; }
.chat-meta { font-size: 11px; color: #777; margin-bottom: 6px; }
.chat-body { font-size: 14px; line-height: 1.5; color: #333; }
.chat-attachment { margin-top: 8px; font-size: 12px; }

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
}
</style>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li><a href="<?php echo base_url('order_resolution'); ?>"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></a></li>
                <li class="active"><?php echo htmlspecialchars($resolution->ticket_number); ?></li>
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

                    <!-- Header & Status Pill -->
                    <!-- Header & Actions -->
                    <div class="row" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; align-items: center;">
                        <div class="col-sm-8 col-md-8">
                            <h2 style="margin: 0;">
                                <?php echo $this->lang->line('ticket') ?: 'Ticket'; ?> #<?php echo htmlspecialchars($resolution->ticket_number); ?>
                            </h2>
                            <small class="text-muted"><?php echo $this->lang->line('created_on') ?: 'Created on'; ?> <?php echo date('d M Y, h:i A', $resolution->created_at); ?></small>
                        </div>
                        <div class="col-sm-4 col-md-4 text-right">
                            <a href="<?php echo base_url('order_resolution'); ?>" class="btn btn-sm btn-info">
                                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back_to_list') ?: 'Back to List'; ?>
                            </a>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <!-- Ticket Metadata Grid -->
                    <div class="panel panel-default" style="border-radius: 6px;">
                        <div class="panel-body" style="background: #fafafa;">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <p><b><?php echo $this->lang->line('order_number') ?: 'Order Number'; ?>:</b><br>
                                        <a href="<?php echo base_url('customer/my-orders'); ?>" target="_blank">
                                            #<?php echo htmlspecialchars($resolution->order_number); ?>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <p><b><?php echo $this->lang->line('product') ?: 'Product'; ?>:</b><br>
                                        <?php echo htmlspecialchars($product ? $product->name : ($this->lang->line('all_items') ?: 'All Items')); ?>
                                    </p>
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <p><b><?php echo $this->lang->line('merchant') ?: 'Merchant'; ?>:</b><br>
                                        <?php echo htmlspecialchars($merchant ? $merchant->publication_name : 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <p><b><?php echo $this->lang->line('subject_type') ?: 'Subject Type'; ?>:</b><br>
                                        <?php
                                        $cat_key = 'category_' . strtolower(trim($resolution->category ?? ''));
                                        ?>
                                        <span class="label label-info"><?php echo htmlspecialchars($this->lang->line($cat_key) ?: $resolution->category); ?></span>
                                    </p>
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    <p><b><?php echo $this->lang->line('priority') ?: 'Priority'; ?>:</b><br>
                                        <?php
                                        $p_class = 'label-info';
                                        if ($resolution->priority === 'High') {
                                            $p_class = 'label-danger';
                                        } elseif ($resolution->priority === 'Medium') {
                                            $p_class = 'label-warning';
                                        }
                                        $pri_key = 'priority_' . strtolower(trim($resolution->priority ?? ''));
                                        ?>
                                        <span class="label <?php echo $p_class; ?>"><?php echo htmlspecialchars($this->lang->line($pri_key) ?: ($resolution->priority ?: 'Medium')); ?></span>
                                    </p>
                                </div>
                            </div>

                            <?php 
                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $resolution->status));
                            $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $resolution->status));
                            ?>
                            <hr style="margin: 10px 0;">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <p><b><?php echo $this->lang->line('merchant_decision') ?: 'Merchant Decision'; ?>:</b><br>
                                        <?php if (!empty($resolution->merchant_action) && !in_array(strtolower($resolution->merchant_action), ['none', 'n/a'], true)): ?>
                                            <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($this->lang->line($resolution->merchant_action) ?: ucwords(str_replace('_', ' ', $resolution->merchant_action))); ?></span>
                                        <?php else: ?>
                                            <span class="label label-warning" style="background-color: #ffc107; color: #212529; font-weight: 600; padding: 3px 8px; border-radius: 4px; display: inline-block; font-size: 11px;"><?php echo htmlspecialchars($this->lang->line('pending') ?: ($this->lang->line('status_pending') ?: 'Pending')); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <p><b><?php echo $this->lang->line('escalation_status') ?: 'Escalation Status'; ?>:</b><br>
                                        <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($this->lang->line($status_key) ?: $resolution->status); ?></span>
                                    </p>
                                </div>

                                <?php if ($resolution->delivery_option !== 'none'): ?>
                                    <div class="col-md-3 col-sm-6">
                                        <p><b><?php echo $this->lang->line('delivery_option') ?: 'Delivery Option'; ?>:</b><br>
                                            <span class="text-success font-weight-bold"><?php echo htmlspecialchars($this->lang->line($resolution->delivery_option) ?: ucwords(str_replace('_', ' ', $resolution->delivery_option))); ?></span>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="col-md-3 col-sm-6">
                                    <p><b><?php echo $this->lang->line('refund_amount') ?: 'Refund Amount'; ?>:</b><br>
                                        <span class="font-weight-bold <?php echo ($resolution->refund_amount > 0) ? 'text-success' : 'text-muted'; ?>">
                                            <?php 
                                            $disp_refund = (float)($resolution->refund_amount ?? 0);
                                            if ($disp_refund <= 0) {
                                                if (!empty($product) && !empty($product->price)) {
                                                    $disp_refund = (float)(($product->special_price > 0) ? $product->special_price : $product->price);
                                                } elseif (!empty($order)) {
                                                    $disp_refund = (float)($order->grand_total ?: $order->subtotal);
                                                }
                                            }
                                            if ($resolution->merchant_action === 'refund_approved' || $resolution->resolution_status === 'resolution_approved') {
                                                echo CURRENCY_TYPE . ' ' . number_format($disp_refund, 2);
                                            } elseif ($resolution->merchant_action === 'refund_denied' && $resolution->resolution_status !== 'resolution_approved') {
                                                echo 'Denied';
                                            } elseif ($resolution->category === 'Refund' && $disp_refund > 0) {
                                                echo CURRENCY_TYPE . ' ' . number_format($disp_refund, 2) . ' (Pending)';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>

                            
                        </div>
                    </div>

                    <?php
                    $is_shopper_won = ($resolution->admin_decision === 'in_favour_shopper' || $resolution->resolution_status === 'resolution_approved');
                    $is_merchant_won = ($resolution->admin_decision === 'in_favour_merchant' || $resolution->resolution_status === 'resolution_denied');
                    $is_replacement_ticket = ($resolution->category === 'Replacement' || ($resolution->admin_remedy ?? '') === 'replacement');
                    $is_refund_ticket = ($resolution->category === 'Refund' || ($resolution->admin_remedy ?? '') === 'return_refund');
                    ?>

                    <!-- Official Admin Decision Banner -->
                    <?php if ($is_shopper_won): ?>
                        <div class="alert alert-success" style="border-left: 4px solid #28a745; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                <div>
                                    <h4 style="margin: 0 0 5px 0; color: #155724; font-weight: bold;">
                                        <i class="fa fa-gavel"></i> <?php echo $this->lang->line('decision_favour_shopper') ?: 'Official Resolution Decision: In Favour of Shopper'; ?>
                                    </h4>
                                    <p style="margin: 0; font-size: 13px;">
                                        <?php if ($is_replacement_ticket): ?>
                                            Yellow Markets Support has approved a <strong>Product Replacement</strong>. The Merchant is required to arrange and deliver a replacement item.
                                        <?php else: ?>
                                            Yellow Markets Support has approved a <strong>Return and Refund</strong>. Once item return is confirmed, your refund will be processed.
                                        <?php endif; ?>
                                        <?php if (!empty($resolution->admin_decision_reason)): ?>
                                            <br><em>Decision Reason: <?php echo htmlspecialchars($resolution->admin_decision_reason); ?></em>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="label label-success" style="font-size: 12px; padding: 5px 10px; margin-top: 5px;">Resolution Approved</span>
                            </div>
                        </div>
                    <?php elseif ($is_merchant_won): ?>
                        <div class="alert alert-danger" style="border-left: 4px solid #dc3545; margin-bottom: 20px;">
                            <h4 style="margin: 0 0 5px 0; color: #721c24; font-weight: bold;">
                                <i class="fa fa-gavel"></i> <?php echo $this->lang->line('decision_favour_merchant') ?: 'Official Resolution Decision: In Favour of Merchant'; ?>
                            </h4>
                            <p style="margin: 0; font-size: 13px;">
                                Yellow Markets Support has reviewed the evidence and upheld the merchant's original decision. This dispute ticket is closed (Final).
                                <?php if (!empty($resolution->admin_decision_reason)): ?>
                                    <br><em>Decision Reason: <?php echo htmlspecialchars($resolution->admin_decision_reason); ?></em>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Workflow 5B: Replacement Fulfilment & Delivery Confirmation Card -->
                    <?php if ($is_replacement_ticket && ($is_shopper_won || $resolution->merchant_action === 'replacement_completed' || $resolution->status === 'Processing')): ?>
                        <div class="panel panel-default" style="border: 1px solid #bce8f1; border-left: 4px solid #337ab7; border-radius: 6px; margin-bottom: 20px; background-color: #f7fbfe;">
                            <div class="panel-heading" style="background-color: #e4f2fb; border-bottom: 1px solid #bce8f1; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                <span style="font-weight: 700; color: #245269; font-size: 14px;">
                                    <i class="fa fa-truck text-primary"></i> Replacement Fulfilment & Delivery Status
                                </span>
                                <?php if (!empty($resolution->shopper_receipt_confirmed)): ?>
                                    <span class="label label-success" style="font-size: 11px;"><i class="fa fa-check"></i> Receipt Confirmed</span>
                                <?php elseif (!empty($resolution->admin_replacement_exception)): ?>
                                    <span class="label label-info" style="font-size: 11px;"><i class="fa fa-truck"></i> Verified via Carrier Proof</span>
                                <?php else: ?>
                                    <span class="label label-warning" style="font-size: 11px; background-color: #f0ad4e;"><i class="fa fa-clock-o"></i> Confirmation Required</span>
                                <?php endif; ?>
                            </div>
                            <div class="panel-body" style="padding: 15px;">
                                <?php if (!empty($resolution->shopper_receipt_confirmed)): ?>
                                    <div class="alert alert-success" style="margin: 0;">
                                        <i class="fa fa-check-circle"></i> <strong>Replacement Received:</strong> You have confirmed physical receipt of the replacement item. The dispute resolution is successfully closed (Final).
                                    </div>
                                <?php elseif (!empty($resolution->admin_replacement_exception)): ?>
                                    <div class="alert alert-info" style="margin: 0;">
                                        <i class="fa fa-info-circle"></i> <strong>Delivery Verified:</strong> Delivery was confirmed via carrier delivery proof by Yellow Markets Support (<?= htmlspecialchars($resolution->admin_replacement_exception_notes); ?>).
                                    </div>
                                <?php else: ?>
                                    <p style="margin-bottom: 12px; font-size: 14px;">
                                        The Merchant has arranged replacement via <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $resolution->delivery_option))); ?></strong>.
                                        Please inspect the replacement item upon delivery and confirm receipt below:
                                    </p>
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmReceiptModal">
                                            <i class="fa fa-check"></i> I have received the replacement item
                                        </button>
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#reportNotReceivedModal">
                                            <i class="fa fa-times text-danger"></i> I have not received it
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Separate conversion workflows
                    $shopper_merchant_msgs = [];
                    $shopper_admin_msgs    = [];
                    if (!empty($messages)) {
                        foreach ($messages as $msg) {
                            $c_type = !empty($msg->conversion_type) ? $msg->conversion_type : '';
                            if ($c_type === 'shopper_admin' || in_array($msg->sender_role, ['acct', 'help'], true)) {
                                $shopper_admin_msgs[] = $msg;
                            } else {
                                $shopper_merchant_msgs[] = $msg;
                            }
                        }
                    }
                    $is_escalated = (!empty($shopper_admin_msgs) || $resolution->resolution_status === 'resolution_requested' || in_array($resolution->status, ['Open', 'ReOpen', 'Processing', 'Done'], true));
                    ?>

                                        <?php
                    $item_context = $item_context ?? ($resolution->item_context ?? $this->OrderResolutionModel->get_original_item_context($resolution));
                    $currency     = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
                    ?>
                    <!-- ================= LINKED ORIGINAL REQUEST & AFFECTED ITEM CONTEXT (YM-11) ================= -->
                    <div class="panel panel-default" style="border: 1px solid #dcd1ea; border-left: 4px solid #6f42c1; border-radius: 6px; margin-top: 25px; margin-bottom: 25px; background-color: #faf9fd;">
                        <div class="panel-heading" style="background-color: #f3eff9; border-bottom: 1px solid #dcd1ea; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                            <span style="font-weight: 700; color: #4a2d7a; font-size: 14px;">
                                <i class="fa fa-link" style="color: #6f42c1;"></i> Linked Original Request & Affected Item Context
                            </span>
                            <div>
                                <span class="label" style="background-color: #6f42c1; color: #fff; font-size: 11px; margin-right: 5px;"><i class="fa fa-history"></i> Original Request Context</span>
                                <span class="label label-danger" style="font-size: 11px;"><i class="fa fa-times-circle"></i> Merchant Rejected</span>
                            </div>
                        </div>
                        <div class="panel-body" style="padding: 15px;">
                            <div class="row">
                                <!-- Col 1: Affected Item & Variant -->
                                <div class="col-sm-4 col-md-4" style="border-right: 1px solid #eee; margin-bottom: 15px;">
                                    <div class="text-muted small text-uppercase" style="font-weight: bold; margin-bottom: 5px;">Affected Order Item</div>
                                    <div style="font-size: 15px; font-weight: bold; color: #333;">
                                        <?php echo htmlspecialchars($item_context->product_name ?: ($product ? $product->name : 'Item Not Found')); ?>
                                    </div>
                                    <div style="margin-top: 8px; padding: 8px 10px; border-radius: 4px; background: #fff; border: 1px solid #e5e5e5;">
                                        <div class="small" style="margin-bottom: 5px;">
                                            <strong class="text-secondary"><i class="fa fa-tags"></i> Variant:</strong>
                                            <?php if ($item_context->has_variant): ?>
                                                <span class="label label-default" style="font-size: 11px; font-weight: normal; margin-left: 3px;">
                                                    <?php echo htmlspecialchars($item_context->variant_display); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-style: italic; margin-left: 3px;"><?php echo htmlspecialchars($item_context->variant_status_note); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small" style="margin-bottom: 5px;">
                                            <strong class="text-secondary"><i class="fa fa-cubes"></i> Disputed Quantity:</strong>
                                            <span class="label label-info" style="font-size: 11px; margin-left: 3px;"><?php echo (int)$item_context->disputed_qty; ?> unit(s)</span>
                                        </div>
                                        <div class="small">
                                            <strong class="text-secondary"><i class="fa fa-money"></i> Financial Basis:</strong>
                                            <span style="font-weight: bold; color: #333; margin-left: 3px;"><?php echo $currency; ?> <?php echo number_format($item_context->total_price, 2); ?></span>
                                            <small class="text-muted">(<?php echo $currency; ?> <?php echo number_format($item_context->unit_price, 2); ?> / unit)</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col 2: Order & Reference Mapping -->
                                <div class="col-sm-4 col-md-4" style="border-right: 1px solid #eee; margin-bottom: 15px;">
                                    <div class="text-muted small text-uppercase" style="font-weight: bold; margin-bottom: 5px;">Order & Reference Mapping</div>
                                    <table class="table table-condensed table-borderless" style="font-size: 13px; margin-bottom: 0;">
                                        <tr>
                                            <td class="text-muted" style="width: 50%; padding: 4px 0;"><i class="fa fa-shopping-cart text-primary"></i> Order Number:</td>
                                            <td style="font-weight: bold; padding: 4px 0;">#<?php echo htmlspecialchars($item_context->order_number); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="padding: 4px 0;"><i class="fa fa-building text-info"></i> Merchant Order ID:</td>
                                            <td style="font-weight: bold; padding: 4px 0;"><?php echo htmlspecialchars($item_context->merchant_order_number); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="padding: 4px 0;"><i class="fa fa-exchange" style="color: #6f42c1;"></i> Original Request:</td>
                                            <td style="font-weight: bold; padding: 4px 0;">
                                                <span class="label label-default"><?php echo htmlspecialchars($item_context->request_reference); ?></span>
                                                <small class="text-muted">(<?php echo htmlspecialchars($item_context->request_type_label); ?>)</small>
                                            </td>
                                        </tr>
                                        <?php if (!empty($item_context->request_date)): ?>
                                        <tr>
                                            <td class="text-muted" style="padding: 4px 0;"><i class="fa fa-calendar"></i> Request Date:</td>
                                            <td style="padding: 4px 0;"><?php echo date('d M Y, h:i A', $item_context->request_date); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>

                                <!-- Col 3: Original Rejection Context -->
                                <div class="col-sm-4 col-md-4">
                                    <div class="text-muted small text-uppercase" style="font-weight: bold; margin-bottom: 5px;">Original Rejection Context</div>
                                    <div style="margin-bottom: 8px;">
                                        <span class="label label-danger" style="font-size: 11px; padding: 3px 6px;">
                                            <i class="fa fa-ban"></i> <?php echo htmlspecialchars($item_context->rejection_status); ?>
                                        </span>
                                        <?php if (!empty($item_context->rejection_date)): ?>
                                            <small class="text-muted" style="margin-left: 5px;"><?php echo date('d M Y', $item_context->rejection_date); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small" style="margin-bottom: 8px;">
                                        <strong class="text-secondary">Your Original Request Reason:</strong>
                                        <div style="margin-top: 3px; padding: 6px 10px; border-radius: 4px; background: #fff; border: 1px solid #e5e5e5; font-size: 12px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($item_context->customer_reason); ?>
                                        </div>
                                    </div>
                                    <div class="small">
                                        <strong class="text-secondary">Merchant Rejection Reason / Note:</strong>
                                        <div style="margin-top: 3px; padding: 6px 10px; border-radius: 4px; background: #fff9f9; border: 1px solid #f5c6cb; color: #a94442; font-size: 12px; line-height: 1.4;">
                                            <i class="fa fa-commenting-o"></i> <?php echo htmlspecialchars($item_context->rejection_reason); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= ORIGINAL DISPUTE CLAIM & SUBMITTED EVIDENCE ================= -->
                    <div class="panel panel-info" style="border: 1px solid #bce8f1; border-left: 4px solid #31708f; border-radius: 6px; margin-top: 25px; margin-bottom: 25px; background-color: #fbfcff;">
                        <div class="panel-heading" style="background-color: #ebf7fd; border-bottom: 1px solid #bce8f1; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                            <span style="font-weight: 700; color: #245269; font-size: 14px;">
                                <i class="fa fa-file-text-o text-info"></i> <?php echo $this->lang->line('original_dispute_claim_title') ?: 'Original Dispute Claim & Submitted Evidence'; ?>
                            </span>
                            <div>
                                <span class="label label-info" style="font-size: 11px; margin-right: 5px;"><i class="fa fa-gavel"></i> Dispute Origin</span>
                                <span class="text-muted small"><i class="fa fa-clock-o"></i> Submitted: <?php echo date('d M Y, h:i A', $resolution->created_at); ?></span>
                            </div>
                        </div>
                        <div class="panel-body" style="padding: 15px;">
                            <div class="row">
                                <div class="col-md-8">
                                    <div style="margin-bottom: 10px;">
                                        <strong class="text-muted" style="font-size: 12px; text-transform: uppercase;">Your Original Claim / Statement:</strong>
                                        <div style="padding: 12px; margin-top: 5px; border-radius: 4px; background: #fff; border: 1px solid #ddd; font-size: 14px; line-height: 1.6; min-height: 50px;">
                                            <?php echo !empty($resolution->message) ? nl2br(htmlspecialchars($resolution->message, ENT_QUOTES, 'UTF-8')) : '<em class="text-muted">No text description provided.</em>'; ?>
                                        </div>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fa fa-info-circle text-primary"></i> <strong>Shared Dispute Context:</strong> This is the initial resolution request you submitted following the merchant's rejection. Visible to you, the merchant, and YellowMarkets Support for dispute evaluation.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-muted" style="font-size: 12px; text-transform: uppercase;">Submitted Evidence:</strong>
                                    <?php if (!empty($resolution->attachment)): 
                                        $att_ext = strtolower(pathinfo($resolution->attachment, PATHINFO_EXTENSION));
                                        $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                        $is_pdf  = ($att_ext === 'pdf');
                                        $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($resolution->attachment));
                                        $att_download_url = $att_preview_url . '?download=1';
                                    ?>
                                        <div style="padding: 10px; margin-top: 5px; border-radius: 4px; background: #fff; border: 1px solid #ddd;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                                <span class="small font-weight-bold" style="max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($resolution->attachment); ?>">
                                                    <i class="fa <?php echo $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip'); ?>"></i>
                                                    <?php echo htmlspecialchars($resolution->attachment); ?>
                                                </span>
                                                <div class="btn-group btn-group-xs">
                                                    <a href="<?php echo $att_preview_url; ?>" target="_blank" class="btn btn-xs btn-primary">
                                                        <i class="fa fa-eye"></i> Preview
                                                    </a>
                                                    <a href="<?php echo $att_download_url; ?>" class="btn btn-xs btn-default">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php if ($is_img): ?>
                                                <div style="max-height: 140px; overflow: hidden; border-radius: 4px; border: 1px solid #eee; text-align: center; background: #fafafa;">
                                                    <a href="<?php echo $att_preview_url; ?>" target="_blank">
                                                        <img src="<?php echo $att_preview_url; ?>" alt="Evidence" style="max-height: 140px; width: auto; max-width: 100%; object-fit: contain;">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="padding: 12px; margin-top: 5px; border-radius: 4px; background: #fff; border: 1px solid #ddd;" class="text-muted small">
                                            <i class="fa fa-ban"></i> No file attachment uploaded with original request.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation Workflows Navigation Tabs -->
                    <div style="margin-top: 25px; margin-bottom: 25px;">
                        <ul class="nav nav-tabs" id="shopperConvTabs" role="tablist" style="border-bottom: 2px solid #ddd;">
                            <li role="presentation" class="<?php echo !$is_escalated ? 'active' : ''; ?>">
                                <a href="#shopper_merchant_pane" aria-controls="shopper_merchant_pane" role="tab" data-toggle="tab" style="font-weight: 600; font-size: 14px;">
                                    <i class="fa fa-shopping-bag" style="color: #31708f;"></i> <?php echo $this->lang->line('conversion_shopper_merchant_tab') ?: 'Merchant (Shopper ↔ Merchant)'; ?>
                                    <span class="badge" style="background-color: #31708f; margin-left: 5px;"><?php echo count($shopper_merchant_msgs); ?></span>
                                </a>
                            </li>
                            <li role="presentation" class="<?php echo $is_escalated ? 'active' : ''; ?>">
                                <a href="#shopper_admin_pane" aria-controls="shopper_admin_pane" role="tab" data-toggle="tab" style="font-weight: 600; font-size: 14px;">
                                    <i class="fa fa-shield" style="color: #f0ad4e;"></i> <?php echo $this->lang->line('conversion_shopper_admin_tab') ?: 'YellowMarkets Support (Shopper ↔ Support)'; ?>
                                    <span class="badge" style="background-color: #f0ad4e; margin-left: 5px;"><?php echo count($shopper_admin_msgs); ?></span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" style="padding-top: 20px;">
                            <!-- Pane 1: Shopper <-> Merchant -->
                            <div role="tabpanel" class="tab-pane <?php echo !$is_escalated ? 'active' : ''; ?>" id="shopper_merchant_pane">
                                <div class="alert alert-info py-2 px-3" style="font-size: 13px; margin-bottom: 15px; background-color: #f4fbfd; border-color: #bce8f1; color: #245269; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                    <div><i class="fa fa-shopping-bag text-info"></i> <strong>Merchant Coordination Thread:</strong> Direct communication with the seller. Support can review for adjudication.</div>
                                    <span class="label label-info"><i class="fa fa-eye"></i> Merchant + Support</span>
                                </div>
                                <div class="chat-box" style="margin-bottom: 20px;">
                                    <?php if (!empty($shopper_merchant_msgs)): ?>
                                        <?php foreach ($shopper_merchant_msgs as $msg): 
                                            $role_class = 'chat-' . $msg->sender_role;
                                            $sender_display = ($msg->sender_role === 'shopper') ? ($this->lang->line('you') ?: 'You') : ($merchant ? $merchant->publication_name : ($this->lang->line('merchant') ?: 'Merchant'));
                                        ?>
                                            <div class="chat-message <?php echo $role_class; ?>">
                                                <div class="chat-meta">
                                                    <b><?php echo htmlspecialchars($sender_display); ?></b> (<?php echo ucfirst($msg->sender_role); ?>) &bull; <?php echo date('d M Y, h:i A', $msg->created_at); ?>
                                                </div>
                                                <div class="chat-body">
                                                    <?php echo nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                                <?php if (!empty($msg->attachment)): 
                                                    $att_ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                                                    $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                                    $is_pdf  = ($att_ext === 'pdf');
                                                    $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($msg->attachment));
                                                    $att_download_url = $att_preview_url . '?download=1';
                                                ?>
                                                    <div class="chat-attachment-box" style="margin-top: 8px; padding: 6px 10px; background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); border-radius: 4px; display: inline-block; max-width: 100%;">
                                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                            <span style="font-size: 12px; color: #555;">
                                                                <i class="fa <?php echo $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip'); ?>"></i>
                                                                <strong><?php echo htmlspecialchars($msg->attachment); ?></strong>
                                                            </span>
                                                            <div class="btn-group btn-group-xs">
                                                                <a href="<?php echo $att_preview_url; ?>" target="_blank" class="btn btn-xs btn-primary">
                                                                    <i class="fa fa-eye"></i> <?php echo $this->lang->line('preview') ?: 'Preview'; ?>
                                                                </a>
                                                                <a href="<?php echo $att_download_url; ?>" class="btn btn-xs btn-default">
                                                                    <i class="fa fa-download"></i> <?php echo $this->lang->line('download') ?: 'Download'; ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <?php if ($is_img): ?>
                                                            <div style="margin-top: 6px; max-width: 280px; border-radius: 4px; overflow: hidden; border: 1px solid #ddd; background: #fff;">
                                                                <a href="<?php echo $att_preview_url; ?>" target="_blank">
                                                                    <img src="<?php echo $att_preview_url; ?>" alt="<?php echo htmlspecialchars($msg->attachment); ?>" style="width: 100%; max-height: 180px; object-fit: contain; display: block;" onerror="this.parentElement.parentElement.style.display='none';">
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center" style="padding: 20px;"><?php echo $this->lang->line('no_messages_shopper_merchant') ?: 'No messages with the merchant yet.'; ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Shopper Resolution Escalation Action -->
                                <?php if (in_array($resolution->status, ['Close', 'Done', 'Processing']) && $resolution->resolution_status !== 'resolution_requested' && $resolution->category == 'Refund'): ?>
                                    <div class="panel panel-default" style="border-radius: 6px; margin-bottom: 20px; border: 1px solid #bce8f1; background-color: #f4fbfd;">
                                        <div class="panel-body" style="padding: 15px; display: flex; align-items: center; justify-content: flex-start; flex-wrap: wrap;">
                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#resolutionRequestModal" style="margin-right: 15px;">
                                                <i class="fa fa-flag"></i> <?php echo $this->lang->line('escalate_to_yellow_market') ?: 'Escalate to Yellow Market'; ?>
                                            </button>
                                            <span style="color: #d9534f; font-weight: 500; font-size: 13px;">
                                                <?php echo $this->lang->line('resolution_escalate_prompt') ?: 'Merchant has not replied or resolved? Request Yellow Markets Support to intervene and assist.'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php elseif ($resolution->resolution_status === 'resolution_requested' || in_array($resolution->status, ['Open', 'ReOpen'], true)): ?>
                                    <div class="alert alert-info" style="margin-bottom: 20px; background-color: #e8f4fd; border-color: #b8daff; color: #004085;">
                                        <i class="fa fa-shield"></i>
                                        <strong><?php echo $this->lang->line('direct_investigation_badge') ?: 'Dispute Under Review'; ?>:</strong>
                                        <?php echo $this->lang->line('direct_investigation_info') ?: 'Your resolution request is under direct investigation by Yellow Markets Support following the merchant\'s rejection. Support will adjudicate the dispute.'; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Reply to Merchant Form -->
                                <?php if ($resolution->status !== 'Close (Final)'): ?>
                                    <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #bce8f1;">
                                        <div class="panel-heading" style="background-color: #f4fbfd; border-bottom: 1px solid #bce8f1;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <h4 class="panel-title" style="font-weight: 600; color: #245269;"><i class="fa fa-reply text-info"></i> <?php echo $this->lang->line('reply_to_merchant') ?: 'Reply to Merchant'; ?></h4>
                                                <span class="label label-info" style="font-size: 11px;"><i class="fa fa-shopping-bag"></i> Direct Merchant Channel</span>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 15px;">
                                            <div class="alert alert-info py-1 px-3" style="font-size: 12px; margin-bottom: 15px; background-color: #ebf7fd; border-color: #bce8f1; color: #245269;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                                    <div><i class="fa fa-building"></i> <strong>Recipient:</strong> <?php echo htmlspecialchars($merchant ? $merchant->publication_name : 'Merchant'); ?></div>
                                                    <div><i class="fa fa-eye"></i> <strong>Visibility:</strong> Direct communication with Merchant. Support can review for dispute adjudication.</div>
                                                </div>
                                            </div>
                                            <form action="<?php echo base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                                                <input type="hidden" name="conversion_type" value="shopper_merchant">
                                                <div class="form-group">
                                                    <textarea name="message" rows="3" class="form-control" placeholder="<?php echo $this->lang->line('enter_response_to_merchant') ?: 'Write your response to the merchant...'; ?>" required></textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-8 col-md-8">
                                                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                                                        <small class="text-muted"><?php echo $this->lang->line('optional_attachment') ?: 'Optional attachment: JPG, PNG, WEBP, PDF (Max: 1 MB)'; ?></small>
                                                    </div>
                                                    <div class="col-sm-4 col-md-4 text-right">
                                                        <button type="submit" class="btn btn-info btn-block">
                                                            <i class="fa fa-reply"></i> <?php echo $this->lang->line('send_reply') ?: 'Send Reply'; ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Pane 2: Shopper <-> YellowMarkets Support -->
                            <div role="tabpanel" class="tab-pane <?php echo $is_escalated ? 'active' : ''; ?>" id="shopper_admin_pane">
                                <div class="alert alert-warning py-2 px-3" style="font-size: 13px; margin-bottom: 15px; color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                    <div><i class="fa fa-lock text-warning"></i> <strong>Private Support Thread:</strong> Confidential dispute investigation with YellowMarkets Support.</div>
                                    <span class="label label-warning" style="background-color: #f0ad4e;"><i class="fa fa-eye-slash"></i> Hidden from Merchant</span>
                                </div>
                                <div class="chat-box" style="margin-bottom: 20px;">
                                    <?php if (!empty($shopper_admin_msgs)): ?>
                                        <?php foreach ($shopper_admin_msgs as $msg): 
                                            $role_class = 'chat-' . $msg->sender_role;
                                            $sender_display = ($msg->sender_role === 'shopper') ? ($this->lang->line('you') ?: 'You') : ($this->lang->line('support_team') ?: 'Yellow Markets Support');
                                        ?>
                                            <div class="chat-message <?php echo $role_class; ?>" style="<?php echo ($msg->sender_role !== 'shopper') ? 'background-color: #fcf8e3; border-color: #faebcc;' : ''; ?>">
                                                <div class="chat-meta">
                                                    <b style="<?php echo ($msg->sender_role !== 'shopper') ? 'color: #8a6d3b;' : ''; ?>"><?php echo htmlspecialchars($sender_display); ?></b> &bull; <?php echo date('d M Y, h:i A', $msg->created_at); ?>
                                                </div>
                                                <div class="chat-body">
                                                    <?php echo nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                                </div>
                                                <?php if (!empty($msg->attachment)): 
                                                    $att_ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                                                    $is_img  = in_array($att_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                                    $is_pdf  = ($att_ext === 'pdf');
                                                    $att_preview_url  = base_url('order_resolution/attachment/' . rawurlencode($resolution->ticket_number) . '/' . rawurlencode($msg->attachment));
                                                    $att_download_url = $att_preview_url . '?download=1';
                                                ?>
                                                    <div class="chat-attachment-box" style="margin-top: 8px; padding: 6px 10px; background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.08); border-radius: 4px; display: inline-block; max-width: 100%;">
                                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                            <span style="font-size: 12px; color: #555;">
                                                                <i class="fa <?php echo $is_img ? 'fa-file-image-o text-info' : ($is_pdf ? 'fa-file-pdf-o text-danger' : 'fa-paperclip'); ?>"></i>
                                                                <strong><?php echo htmlspecialchars($msg->attachment); ?></strong>
                                                            </span>
                                                            <div class="btn-group btn-group-xs">
                                                                <a href="<?php echo $att_preview_url; ?>" target="_blank" class="btn btn-xs btn-primary">
                                                                    <i class="fa fa-eye"></i> <?php echo $this->lang->line('preview') ?: 'Preview'; ?>
                                                                </a>
                                                                <a href="<?php echo $att_download_url; ?>" class="btn btn-xs btn-default">
                                                                    <i class="fa fa-download"></i> <?php echo $this->lang->line('download') ?: 'Download'; ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <?php if ($is_img): ?>
                                                            <div style="margin-top: 6px; max-width: 280px; border-radius: 4px; overflow: hidden; border: 1px solid #ddd; background: #fff;">
                                                                <a href="<?php echo $att_preview_url; ?>" target="_blank">
                                                                    <img src="<?php echo $att_preview_url; ?>" alt="<?php echo htmlspecialchars($msg->attachment); ?>" style="width: 100%; max-height: 180px; object-fit: contain; display: block;" onerror="this.parentElement.parentElement.style.display='none';">
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info text-center" style="margin: 20px 0;">
                                            <i class="fa fa-info-circle"></i> <?php echo $this->lang->line('support_channel_ready_info') ?: 'Your dispute is under direct Support investigation. You can post a message directly to Yellow Markets Support below.'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Reply to Support Form -->
                                <?php if ($resolution->status !== 'Close (Final)'): ?>
                                    <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #faebcc;">
                                        <div class="panel-heading" style="background-color: #fcf8e3; border-bottom: 1px solid #faebcc;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <h4 class="panel-title" style="font-weight: 600; color: #8a6d3b;"><i class="fa fa-shield"></i> <?php echo $this->lang->line('message_to_support') ?: 'Message to YellowMarkets Support'; ?></h4>
                                                <span class="label label-warning" style="background-color: #f0ad4e; font-size: 11px;"><i class="fa fa-lock"></i> Private Support Channel</span>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 15px;">
                                            <div class="alert alert-warning py-1 px-3" style="font-size: 12px; margin-bottom: 15px; color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                                    <div><i class="fa fa-shield"></i> <strong>Recipient:</strong> YellowMarkets Support Team (@Help / @Acct)</div>
                                                    <div><i class="fa fa-eye-slash"></i> <strong>Visibility:</strong> Confidential between You and Support. <em>Merchant cannot see this.</em></div>
                                                </div>
                                            </div>
                                            <form action="<?php echo base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                                                <input type="hidden" name="conversion_type" value="shopper_admin">
                                                <div class="form-group">
                                                    <textarea name="message" rows="3" class="form-control" placeholder="<?php echo $this->lang->line('enter_response_to_support') ?: 'Write your message to YellowMarkets Support team...'; ?>" required></textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-8 col-md-8">
                                                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                                                        <small class="text-muted"><?php echo $this->lang->line('optional_attachment') ?: 'Optional attachment: JPG, PNG, WEBP, PDF (Max: 1 MB)'; ?></small>
                                                    </div>
                                                    <div class="col-sm-4 col-md-4 text-right">
                                                        <button type="submit" class="btn btn-warning btn-block font-weight-bold" style="background-color: #f0ad4e; color: #fff;">
                                                            <i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('send_to_support') ?: 'Send to Support'; ?>
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
</div>

<?php if (in_array($resolution->status, ['Close', 'Done', 'Processing']) && $resolution->resolution_status !== 'resolution_requested'): ?>
<!-- Resolution Escalation Modal -->
<div class="modal fade" id="resolutionRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('order_resolution/request_resolution'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('escalate_modal_title') ?: 'Escalate to Yellow Market Support'; ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo $this->lang->line('escalate_modal_desc') ?: 'If you disagree with the merchant\'s decision or the issue has not been satisfactorily resolved, submit this request to escalate the ticket directly to Yellow Markets Support.'; ?></p>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('message_description') ?: 'Reason / Additional Details:'; ?></label>
                        <textarea name="message" class="form-control" rows="4" placeholder="<?php echo $this->lang->line('explain_mediation_reason') ?: 'Explain why you are requesting escalation to Yellow Markets Support...'; ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('submit_escalation') ?: 'Submit Escalation'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: Confirm Replacement Receipt (Workflow 5B) -->
<div class="modal fade" id="confirmReceiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('order_resolution/confirm_replacement_receipt'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="received" value="yes">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title font-weight-bold text-success"><i class="fa fa-check-circle"></i> Confirm Receipt of Replacement</h4>
                </div>
                <div class="modal-body">
                    <p>Please confirm that you have received the replacement product in satisfactory condition.</p>
                    <div class="form-group">
                        <label>Notes / Feedback (optional):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional comments regarding the received replacement..."></textarea>
                    </div>
                    <div class="alert alert-info small" style="margin-bottom: 0;">
                        <i class="fa fa-info-circle"></i> Once confirmed, this resolution dispute will be completed and permanently closed (Final).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold"><i class="fa fa-check"></i> Yes, I Received It</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Report Replacement Not Received (Workflow 5B) -->
<div class="modal fade" id="reportNotReceivedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('order_resolution/confirm_replacement_receipt'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="received" value="no">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title font-weight-bold text-danger"><i class="fa fa-exclamation-triangle"></i> Report Replacement Not Received</h4>
                </div>
                <div class="modal-body">
                    <p>If you have not received the replacement item or experienced delivery issues, report the details below. Yellow Markets Support will investigate with the merchant.</p>
                    <div class="form-group">
                        <label>Issue Details (required):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Explain the non-receipt or delivery issue in detail..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Report Non-Receipt</button>
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
        var html = '<div class="alert ' + alertClass + ' alert-dismissible resolution-ajax-alert" role="alert" style="position:fixed; top:20px; right:20px; z-index:99999; min-width:320px; max-width:480px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">' +
                   '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                   icon + message +
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
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + (<?php echo json_encode($this->lang->line('ajax_processing') ?: 'Processing...'); ?>));

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
                    $btn.html('<i class="fa fa-check"></i> ' + (<?php echo json_encode($this->lang->line('ajax_done') ?: 'Done'); ?>));
                    showNotification('success', res.message || (<?php echo json_encode($this->lang->line('action_processed_success') ?: 'Action processed successfully.'); ?>));

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
                    var msg = (res && res.message) ? res.message : (<?php echo json_encode($this->lang->line('ajax_error') ?: 'An error occurred while processing your request.'); ?>);
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
                var errMsg = <?php echo json_encode($this->lang->line('ajax_error') ?: 'Unable to complete action. Please check your connection and try again.'); ?>;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                showNotification('error', errMsg);
            }
        });
    });
});
</script>

<?php $this->load->view('common/footer'); ?>
