<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1 font-weight-bold">
                    <i class="fa fa-ticket text-warning mr-2"></i> Order Resolution Ticket #<?= htmlspecialchars($ticket->ticket_id); ?>
                </h3>
                <?php
                $badge_map = [
                    'Open'          => 'badge-primary',
                    'Processing'    => 'badge-warning text-dark',
                    'Done'          => 'badge-info',
                    'Close'         => 'badge-success',
                    'ReOpen'        => 'badge-danger',
                    'Close (Final)' => 'badge-dark'
                ];
                $badge_class = $badge_map[$ticket->status_code] ?? 'badge-secondary';
                ?>
                <span class="badge p-2 <?= $badge_class; ?>" style="font-size: 13px;">
                    Status: <?= htmlspecialchars($ticket->status_code); ?>
                </span>
                <?php if ($ticket->merchant_action !== 'none'): ?>
                    <span class="badge badge-light border p-2 ml-2">
                        Action: <?= ucwords(str_replace('_', ' ', $ticket->merchant_action)); ?>
                        <?= ($ticket->delivery_option !== 'none') ? ' (' . ucwords(str_replace('_', ' ', $ticket->delivery_option)) . ')' : ''; ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('help_desk/shopper'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Back to Tickets
            </a>
        </div>

        <!-- Ticket & Order Meta Card -->
        <div class="card mb-4 shadow-sm border-0 bg-light">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase">Order Number:</small>
                        <div class="font-weight-bold text-primary">#<?= htmlspecialchars($ticket->order_increment_id ?: $ticket->order_id); ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase">Product:</small>
                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->product_name ?: ('Product #' . $ticket->products)); ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase">Shopper:</small>
                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->customer_first_name . ' ' . $ticket->customer_last_name); ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted text-uppercase">Category & Priority:</small>
                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->category); ?> (<?= htmlspecialchars($ticket->priority); ?>)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Merchant Decision Action Controls (Only available in Open / ReOpen) -->
        <?php if (in_array($ticket->status_code, ['Open', 'ReOpen'])): ?>
            <div class="card mb-4 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-gavel mr-1"></i> Merchant Resolution Actions</span>
                    <small>Review shopper dispute and select an action</small>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        
                        <!-- REFUND ACTIONS -->
                        <?php if ($ticket->merchant_action === 'none'): ?>
                            <button type="button" class="btn btn-success btn-sm font-weight-bold" onclick="openActionModal('refund_approved')">
                                <i class="fa fa-check-circle mr-1"></i> Refund Approved
                            </button>
                            <button type="button" class="btn btn-danger btn-sm font-weight-bold" onclick="openActionModal('refund_denied')">
                                <i class="fa fa-times-circle mr-1"></i> Refund Denied
                            </button>
                        <?php endif; ?>

                        <!-- REPLACEMENT ACTIONS -->
                        <?php if ($ticket->merchant_action === 'none'): ?>
                            <button type="button" class="btn btn-info btn-sm font-weight-bold" onclick="openActionModal('replacement_approved')">
                                <i class="fa fa-refresh mr-1"></i> Replacement Approved
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold" onclick="openActionModal('replacement_denied')">
                                <i class="fa fa-ban mr-1"></i> Replacement Denied
                            </button>
                        <?php endif; ?>

                        <!-- REPLACEMENT COMPLETED: ONLY appears after Replacement Approved + Delivery option selected -->
                        <?php if ($ticket->merchant_action === 'replacement_approved' && !empty($ticket->delivery_option) && $ticket->delivery_option !== 'none'): ?>
                            <button type="button" class="btn btn-primary btn-sm font-weight-bold" onclick="openActionModal('replacement_completed')">
                                <i class="fa fa-truck mr-1"></i> Replacement Completed
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Conversation Stream -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold border-bottom">
                <i class="fa fa-comments mr-2 text-primary"></i> Conversation History
            </div>
            <div class="card-body p-4" style="max-height: 500px; overflow-y: auto;">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php 
                            $is_merchant = ($msg->sender_role === 'merchant');
                            $bubble_class = $is_merchant ? 'ml-auto bg-light border-success' : 'mr-auto bg-white border';
                            $role_badge = [
                                'shopper'   => 'badge-primary',
                                'merchant'  => 'badge-success',
                                'admin'     => 'badge-danger',
                                'account'   => 'badge-info'
                            ][$msg->sender_role] ?? 'badge-secondary';
                        ?>
                        <div class="d-flex mb-3 <?= $is_merchant ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <div class="card <?= $bubble_class; ?> shadow-sm" style="max-width: 80%; border-radius: 12px;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                        <span class="badge <?= $role_badge; ?> mr-2"><?= ucfirst($msg->sender_role); ?> (<?= htmlspecialchars($msg->sender_name); ?>)</span>
                                        <small class="text-muted"><?= date('d M Y, H:i', $msg->created_at); ?></small>
                                    </div>
                                    <p class="card-text mb-2 text-dark" style="white-space: pre-wrap; font-size: 14px;"><?= htmlspecialchars($msg->message); ?></p>
                                    <?php if (!empty($msg->attachment)): ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <a href="<?= base_url('uploads/help_desk_attachment/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fa fa-paperclip mr-1"></i> View Attachment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No conversation messages yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Merchant Reply Form -->
        <?php if (in_array($ticket->status_code, ['Open', 'Processing', 'ReOpen'])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fa fa-reply mr-1"></i> Reply to Shopper & Admin
                </div>
                <div class="card-body p-3">
                    <form id="merchantReplyForm" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                        <div id="replyAlert" class="alert d-none"></div>

                        <div class="form-group mb-2">
                            <textarea name="message" id="merchantReplyMessage" rows="3" class="form-control" placeholder="Type your response here..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="btn btn-outline-secondary btn-sm mb-0 cursor-pointer">
                                    <i class="fa fa-camera mr-1"></i> Upload Image
                                    <input type="file" name="attachment" id="merchantReplyAttachment" accept="image/*,.pdf" style="display: none;">
                                </label>
                                <span id="merchantFileName" class="small text-muted ml-2"></span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" id="btnMerchantReply">
                                <i class="fa fa-paper-plane mr-1"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Action Decision Modal -->
<div class="modal fade" id="merchantActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="actionModalTitle">Action</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="merchantActionForm">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                <input type="hidden" name="action" id="modal_action_input" value="">

                <div class="modal-body p-4">
                    <div id="actionAlert" class="alert d-none"></div>

                    <!-- REFUND APPROVED CONFIRMATION -->
                    <div id="field_refund_approved" class="action-field-group d-none">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle mr-1"></i> Approving this refund will notify Shopper and Yellow Markets Admin. Admin will route this to @Account to deduct the refund from your 15-day hold-back sales balance.
                        </div>
                        <p class="font-weight-bold">Confirm refund approval for this order item?</p>
                    </div>

                    <!-- REFUND / REPLACEMENT DENIAL REASON -->
                    <div id="field_denial_reason" class="action-field-group d-none">
                        <div class="form-group">
                            <label class="font-weight-bold">Reason for Denial <span class="text-danger">*</span></label>
                            <textarea name="reason" id="modal_reason" rows="3" class="form-control" placeholder="State reasons clearly..."></textarea>
                        </div>
                    </div>

                    <!-- REPLACEMENT DELIVERY OPTIONS -->
                    <div id="field_replacement_approved" class="action-field-group d-none">
                        <label class="font-weight-bold">Select Delivery Option <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_option" id="del_own" value="own_delivery" checked>
                            <label class="form-check-label font-weight-bold" for="del_own">
                                Own Delivery (Merchant delivers directly)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_option" id="del_self" value="self_pickup">
                            <label class="form-check-label font-weight-bold" for="del_self">
                                Self Pickup (Customer collects from merchant shop)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_option" id="del_ym" value="ym_delivery">
                            <label class="form-check-label font-weight-bold text-warning" for="del_ym">
                                YM Delivery (Yellow Markets Fleet - Requires Add-on purchase)
                            </label>
                            <small class="form-text text-muted">Selecting YM Delivery notifies @Admin and @Account to request you to purchase a replacement delivery Add-on.</small>
                        </div>
                    </div>

                    <!-- REPLACEMENT COMPLETED DISPATCH NOTES -->
                    <div id="field_replacement_completed" class="action-field-group d-none">
                        <div class="form-group">
                            <label class="font-weight-bold">Dispatch / Fulfillment Details</label>
                            <textarea name="dispatch_notes" id="modal_dispatch_notes" rows="3" class="form-control" placeholder="Tracking reference, delivery date, or pickup verification notes..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold" id="btnSubmitAction">Confirm Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openActionModal(action) {
    $('#modal_action_input').val(action);
    $('.action-field-group').addClass('d-none');
    $('#actionAlert').addClass('d-none').text('');

    if (action === 'refund_approved') {
        $('#actionModalTitle').text('Approve Refund');
        $('#field_refund_approved').removeClass('d-none');
    } else if (action === 'refund_denied') {
        $('#actionModalTitle').text('Deny Refund Request');
        $('#field_denial_reason').removeClass('d-none');
    } else if (action === 'replacement_approved') {
        $('#actionModalTitle').text('Approve Replacement');
        $('#field_replacement_approved').removeClass('d-none');
    } else if (action === 'replacement_denied') {
        $('#actionModalTitle').text('Deny Replacement Request');
        $('#field_denial_reason').removeClass('d-none');
    } else if (action === 'replacement_completed') {
        $('#actionModalTitle').text('Confirm Replacement Completed');
        $('#field_replacement_completed').removeClass('d-none');
    }

    $('#merchantActionModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    $('#merchantReplyAttachment').on('change', function() {
        var fn = $(this).val().split('\\').pop();
        $('#merchantFileName').text(fn || '');
    });

    $('#merchantReplyForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnMerchantReply');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        var formData = new FormData(this);
        $.ajax({
            url: '<?= base_url('merchant/order-resolution/reply'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    window.location.reload();
                } else {
                    $('#replyAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send Reply');
                }
            },
            error: function() {
                $('#replyAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send Reply');
            }
        });
    });

    $('#merchantActionForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitAction');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

        $.ajax({
            url: '<?= base_url('merchant/order-resolution/action'); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#actionAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    $('#actionAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                    $btn.prop('disabled', false).html('Confirm Action');
                }
            },
            error: function() {
                $('#actionAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).html('Confirm Action');
            }
        });
    });
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
