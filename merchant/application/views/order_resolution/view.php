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
                        <a href="<?= base_url('order_resolution'); ?>" class="btn btn-outline-secondary btn-sm ml-2">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Ticket Details Row -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Shopper:</strong></p>
                            <p><?= htmlspecialchars($customer ? trim($customer->first_name . ' ' . $customer->last_name) : 'Shopper'); ?> 
                               <?= !empty($customer->email_id) ? '(' . htmlspecialchars($customer->email_id) . ')' : ''; ?></p>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-2">
                            <p class="mb-1"><strong>Date Created:</strong></p>
                            <p><?= date('d M Y, h:i A', $resolution->created_at); ?></p>
                        </div>
                    </div>

                    <!-- Current Resolution Decision Info Banner -->
                    <?php if ($resolution->merchant_action !== 'none' || $resolution->delivery_option !== 'none' || $resolution->refund_amount > 0): ?>
                        <div class="alert alert-info py-2 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <strong>Merchant Action:</strong> <?= ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?>
                                </div>
                                <?php if ($resolution->delivery_option !== 'none'): ?>
                                    <div class="col-md-4">
                                        <strong>Delivery Option:</strong> <?= ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($resolution->refund_amount > 0): ?>
                                    <div class="col-md-4">
                                        <strong>Approved Refund:</strong> <?= $currency . ' ' . number_format($resolution->refund_amount, 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons Section -->
                    <div class="p-3 bg-light border rounded mb-4">
                        <h6 class="font-weight-bold mb-3"><i class="fa fa-gavel"></i> Order Resolution Decision Actions</h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">

                            <!-- REFUND DECISION BUTTONS (Merchant Only) -->
                            <?php if ($resolution->merchant_action === 'none' || $resolution->merchant_action === 'refund_denied'): ?>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#refundApproveModal" data-bs-toggle="modal" data-bs-target="#refundApproveModal">
                                    <i class="fa fa-check"></i> Refund Approved
                                </button>
                                <?php if ($resolution->merchant_action !== 'refund_denied'): ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#refundDeniedModal" data-bs-toggle="modal" data-bs-target="#refundDeniedModal">
                                        <i class="fa fa-times"></i> Refund Denied
                                    </button>
                                <?php endif; ?>
                            <?php elseif ($resolution->merchant_action === 'refund_approved'): ?>
                                <span class="badge badge-success p-2">
                                    <i class="fa fa-check-circle"></i> Refund Approved (<?= $currency . ' ' . number_format($resolution->refund_amount, 2); ?>)
                                </span>
                            <?php endif; ?>

                            <!-- REPLACEMENT DECISION BUTTONS (Merchant Only) -->
                            <?php if ($resolution->merchant_action === 'none' || $resolution->merchant_action === 'replacement_denied'): ?>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#replacementApproveModal" data-bs-toggle="modal" data-bs-target="#replacementApproveModal">
                                    <i class="fa fa-refresh"></i> Replacement Approved
                                </button>
                                <?php if ($resolution->merchant_action !== 'replacement_denied'): ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#replacementDeniedModal" data-bs-toggle="modal" data-bs-target="#replacementDeniedModal">
                                        <i class="fa fa-ban"></i> Replacement Denied
                                    </button>
                                <?php endif; ?>
                            <?php elseif ($resolution->merchant_action === 'replacement_approved'): ?>
                                <span class="badge badge-info p-2">
                                    <i class="fa fa-check-circle"></i> Replacement Approved (<?= ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?>)
                                </span>
                                <!-- Replacement Completed: displayed ONLY after Replacement Approved AND delivery option selected -->
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replacementCompletedModal" data-bs-toggle="modal" data-bs-target="#replacementCompletedModal">
                                    <i class="fa fa-check-square-o"></i> Replacement Completed
                                </button>
                            <?php elseif ($resolution->merchant_action === 'replacement_completed'): ?>
                                <span class="badge badge-primary p-2">
                                    <i class="fa fa-check-circle"></i> Replacement Completed
                                </span>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- Conversation History Stream -->
                    <h5 class="font-weight-bold mb-3"><i class="fa fa-comments"></i> Conversation</h5>
                    <div class="chat-container">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $msg): 
                                $is_merchant = ($msg->sender_role === 'merchant');
                                $role_class  = 'chat-msg-' . $msg->sender_role;
                                $sender_display = $is_merchant ? 'You (Merchant)' : (($msg->sender_role === 'shopper') ? 'Shopper' : 'Yellow Markets Support');
                            ?>
                                <div class="chat-msg <?= $role_class; ?>">
                                    <div class="chat-msg-meta">
                                        <strong><?= htmlspecialchars($sender_display); ?></strong> &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                    </div>
                                    <div class="chat-msg-body">
                                        <?= nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                    <?php if (!empty($msg->attachment)): ?>
                                        <div class="mt-2">
                                            <a href="<?= base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-paperclip"></i> View Attachment (<?= htmlspecialchars($msg->attachment); ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No conversation messages yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 font-weight-bold">Reply to Shopper</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                <div class="form-group">
                                    <textarea name="message" rows="4" class="form-control" placeholder="Enter your response to the shopper..." required></textarea>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                        <small class="text-muted">Optional attachment (Max: 5MB)</small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-paper-plane"></i> Send Reply
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</main>

<!-- Modal: Refund Approved -->
<div class="modal fade" id="refundApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="refund_approved">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-check-circle"></i> Approve Refund</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Confirm the approved refund amount for Order #<?= htmlspecialchars($resolution->order_number); ?>.</p>
                    <div class="form-group">
                        <label>Refund Amount (<?= $currency ?>):</label>
                        <input type="number" step="0.01" min="0.01" name="refund_amount" class="form-control" 
                               value="<?= !empty($order->grand_total) ? number_format((float)$order->grand_total, 2, '.', '') : '0.00'; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirm Refund Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Refund Denied -->
<div class="modal fade" id="refundDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="refund_denied">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fa fa-times-circle"></i> Deny Refund</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deny this refund request? Support (@Help) will be notified.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Refund Denied</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replacement Approved with Delivery Options -->
<div class="modal fade" id="replacementApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_approved">
                <div class="modal-header">
                    <h5 class="modal-title text-info"><i class="fa fa-refresh"></i> Approve Replacement</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Select the replacement delivery method:</p>
                    <div class="form-group">
                        <label><strong>Delivery Option:</strong> <span class="text-danger">*</span></label>
                        <select name="delivery_option" class="form-control" required>
                            <option value="">-- Select Delivery Option --</option>
                            <option value="own_delivery">Own Delivery</option>
                            <option value="self_pickup">Self Pickup</option>
                            <option value="ym_delivery">YM Delivery Service</option>
                        </select>
                        <small class="form-text text-muted">
                            Selecting <strong>YM Delivery Service</strong> notifies Yellow Markets (@Help) to organize fulfillment.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm">Confirm Replacement Approved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replacement Denied -->
<div class="modal fade" id="replacementDeniedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_denied">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fa fa-ban"></i> Deny Replacement</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deny this replacement request? The product status will be set to Replacement Rejected.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Replacement Denied</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replacement Completed -->
<div class="modal fade" id="replacementCompletedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('order_resolution/action'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <input type="hidden" name="action" value="replacement_completed">
                <div class="modal-header">
                    <h5 class="modal-title text-primary"><i class="fa fa-check-square-o"></i> Mark Replacement Completed</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the replacement product has been successfully delivered / collected. Support (@Help) will be notified.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Mark Replacement Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
