<?php
$site_lang = $this->session->userdata('site_lang') ?: 'english';

$badge_map = [
    'Open'          => ['class' => 'badge-primary', 'label' => 'Open'],
    'Processing'    => ['class' => 'badge-warning text-dark', 'label' => 'Processing'],
    'Done'          => ['class' => 'badge-info', 'label' => 'Done'],
    'Close'         => ['class' => 'badge-success', 'label' => 'Closed'],
    'ReOpen'        => ['class' => 'badge-danger', 'label' => 'ReOpen (In Dispute)'],
    'Close (Final)' => ['class' => 'badge-dark', 'label' => 'Closed (Final)']
];
$status_info = $badge_map[$ticket->status_code] ?? ['class' => 'badge-secondary', 'label' => $ticket->status_code];
?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?= base_url(); ?>"><?= $this->lang->line('home') ?: 'Home'; ?></a></li>
                <li><a href="<?= base_url('my-orders'); ?>"><?= $this->lang->line('my_orders') ?: 'My Orders'; ?></a></li>
                <li class="active"><?= $this->lang->line('ticket') ?: 'Ticket'; ?> #<?= htmlspecialchars($ticket->ticket_id); ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full py-4">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-md-9 col-sm-9">
                <div class="content-page p-4 bg-white rounded shadow-sm">
                    
                    <!-- Ticket Header -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h2 class="mb-1 font-weight-bold text-dark">
                                <i class="fa fa-ticket text-warning mr-2"></i> <?= htmlspecialchars($ticket->ticket_id); ?>
                            </h2>
                            <span class="badge p-2 font-weight-bold <?= $status_info['class']; ?>">
                                <?= $status_info['label']; ?>
                            </span>
                            <span class="badge badge-light border ml-2">
                                <?= htmlspecialchars($ticket->category); ?> | <?= htmlspecialchars($ticket->priority); ?>
                            </span>
                        </div>
                        <a href="<?= base_url('my-orders'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left mr-1"></i> <?= $this->lang->line('back_to_orders') ?: 'Back to My Orders'; ?>
                        </a>
                    </div>

                    <!-- Order and Product Meta Details -->
                    <div class="card mb-4 bg-light border-0">
                        <div class="card-body p-3">
                            <div class="row text-center text-sm-left">
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('order_number') ?: 'Order Number'; ?>:</small>
                                    <div class="font-weight-bold text-primary">#<?= htmlspecialchars($ticket->order_increment_id ?: $ticket->order_id); ?></div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('product') ?: 'Product'; ?>:</small>
                                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->product_name ?: ('Product #' . $ticket->products)); ?></div>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('merchant') ?: 'Merchant'; ?>:</small>
                                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->merchant_name ?: 'Yellow Merchant'); ?></div>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('created_on') ?: 'Date'; ?>:</small>
                                    <div class="font-weight-bold text-muted"><?= date('d M Y', $ticket->created_at); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolution Request Action Banner if Closed and eligible -->
                    <?php if ($ticket->status_code === 'Close' && $ticket->resolution_status !== 'resolution_denied'): ?>
                        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4 p-3 rounded">
                            <div>
                                <i class="fa fa-exclamation-triangle mr-2"></i>
                                <strong><?= $this->lang->line('ticket_closed_notice') ?: 'This ticket has been marked Closed.'; ?></strong>
                                <span class="d-block small text-muted"><?= $this->lang->line('dispute_escalation_tip') ?: 'If you disagree with the merchant resolution or denial, you can escalate this dispute to Yellow Markets Admin.'; ?></span>
                            </div>
                            <button type="button" class="btn btn-danger font-weight-bold btn-sm text-nowrap ml-3" data-toggle="modal" data-target="#resolutionRequestModal">
                                <i class="fa fa-gavel mr-1"></i> <?= $this->lang->line('resolution_request') ?: 'Resolution Request'; ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($ticket->status_code === 'Close (Final)'): ?>
                        <div class="alert alert-secondary mb-4 p-3 rounded">
                            <i class="fa fa-lock mr-2"></i>
                            <strong><?= $this->lang->line('final_closure_notice') ?: 'Dispute Investigation Finalized.'; ?></strong>
                            <p class="small text-muted mb-0"><?= $this->lang->line('final_closure_desc') ?: 'This case has undergone final Yellow Markets Admin review and is permanently closed.'; ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Conversation Thread -->
                    <h5 class="font-weight-bold mb-3 border-bottom pb-2">
                        <i class="fa fa-comments mr-2 text-primary"></i> <?= $this->lang->line('conversation') ?: 'Conversation'; ?>
                    </h5>

                    <div class="conversation-container mb-4" style="max-height: 550px; overflow-y: auto; padding-right: 10px;">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php 
                                    $is_me = ($msg->sender_role === 'shopper');
                                    $bubble_class = $is_me ? 'ml-auto bg-light border-primary' : 'mr-auto bg-white border';
                                    $role_badge = [
                                        'shopper'   => 'badge-primary',
                                        'merchant'  => 'badge-success',
                                        'admin'     => 'badge-danger',
                                        'account'   => 'badge-info'
                                    ][$msg->sender_role] ?? 'badge-secondary';
                                ?>
                                <div class="d-flex mb-3 <?= $is_me ? 'justify-content-end' : 'justify-content-start'; ?>">
                                    <div class="card <?= $bubble_class; ?> shadow-sm" style="max-width: 80%; border-radius: 12px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                                <span class="badge <?= $role_badge; ?> mr-2">
                                                    <?= ucfirst($msg->sender_role); ?>
                                                </span>
                                                <small class="text-muted"><?= date('d M Y, H:i', $msg->created_at); ?></small>
                                            </div>
                                            <p class="card-text mb-2 text-dark" style="white-space: pre-wrap; font-size: 14px;"><?= htmlspecialchars($msg->message); ?></p>
                                            <?php if (!empty($msg->attachment)): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <a href="<?= base_url('uploads/help_desk_attachment/' . $msg->attachment); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-paperclip mr-1"></i> <?= $this->lang->line('view_attachment') ?: 'View Attachment'; ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4"><?= $this->lang->line('no_messages_yet') ?: 'No conversation messages yet.'; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Box (Only when active) -->
                    <?php if (in_array($ticket->status_code, ['Open', 'Processing', 'ReOpen'])): ?>
                        <div class="card border">
                            <div class="card-header bg-light font-weight-bold">
                                <i class="fa fa-reply mr-1"></i> <?= $this->lang->line('send_reply') ?: 'Send a Reply'; ?>
                            </div>
                            <div class="card-body p-3">
                                <form id="shopperReplyForm" enctype="multipart/form-data">
                                    <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                                    <div id="replyAlert" class="alert d-none"></div>

                                    <div class="form-group mb-2">
                                        <textarea name="message" id="replyMessage" rows="3" class="form-control" 
                                                  placeholder="<?= $this->lang->line('type_reply_placeholder') ?: 'Type your reply here...'; ?>" required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="btn btn-outline-secondary btn-sm mb-0 cursor-pointer">
                                                <i class="fa fa-camera mr-1"></i> <?= $this->lang->line('add_image') ?: 'Add Photo'; ?>
                                                <input type="file" name="attachment" id="replyAttachment" accept="image/*,.pdf" style="display: none;">
                                            </label>
                                            <span id="replyFileName" class="small text-muted ml-2"></span>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" id="replySubmitBtn">
                                            <i class="fa fa-paper-plane mr-1"></i> <?= $this->lang->line('send') ?: 'Send'; ?>
                                        </button>
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

<!-- Resolution Request Modal -->
<div class="modal fade" id="resolutionRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-gavel mr-1"></i> <?= $this->lang->line('shopper_dispute_title') ?: 'Submit Resolution Request (Escalate to Admin)'; ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="resolutionRequestForm">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                <div class="modal-body p-4">
                    <div id="resRequestAlert" class="alert d-none"></div>
                    <p class="text-muted small">
                        <?= $this->lang->line('dispute_expl') ?: 'If you are dissatisfied with the merchant decision, explain your grounds for dispute. A Yellow Markets Administrator will investigate and make a binding determination.'; ?>
                    </p>
                    <div class="form-group">
                        <label class="font-weight-bold"><?= $this->lang->line('dispute_reason') ?: 'Reason for Resolution Request'; ?> <span class="text-danger">*</span></label>
                        <textarea name="dispute_reason" rows="4" class="form-control" placeholder="Provide full details..." required minlength="15"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-danger font-weight-bold" id="btnSubmitResolutionRequest">
                        <i class="fa fa-paper-plane mr-1"></i> <?= $this->lang->line('submit_escalation') ?: 'Submit Resolution Request'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show selected attachment name
    $('#replyAttachment').on('change', function() {
        var fn = $(this).val().split('\\').pop();
        $('#replyFileName').text(fn || '');
    });

    // Reply form submit
    $('#shopperReplyForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#replySubmitBtn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('order-resolution/reply'); ?>',
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
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send');
                }
            },
            error: function() {
                $('#replyAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send');
            }
        });
    });

    // Resolution Request submit
    $('#resolutionRequestForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitResolutionRequest');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Submitting...');

        $.ajax({
            url: '<?= base_url('order-resolution/request-resolution'); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#resRequestAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    $('#resRequestAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                    $btn.prop('disabled', false).html('Submit Resolution Request');
                }
            },
            error: function() {
                $('#resRequestAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).html('Submit Resolution Request');
            }
        });
    });
});
</script>
