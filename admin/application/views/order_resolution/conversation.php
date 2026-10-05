<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner py-4">

        <!-- Top Header & Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1 font-weight-bold">
                    <i class="fa fa-ticket text-primary mr-2"></i> Order Resolution Ticket #<?= htmlspecialchars($ticket->ticket_id); ?>
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
                <span class="badge badge-light border p-2 ml-2">
                    Department: @<?= htmlspecialchars($ticket->assigned_role ?: 'Admin'); ?>
                </span>
                <?php if ($ticket->merchant_action !== 'none'): ?>
                    <span class="badge badge-light border p-2 ml-2">
                        Merchant: <?= ucwords(str_replace('_', ' ', $ticket->merchant_action)); ?>
                        <?= ($ticket->delivery_option !== 'none') ? ' (' . ucwords(str_replace('_', ' ', $ticket->delivery_option)) . ')' : ''; ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('admin/order-resolution'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Back to All Tickets
            </a>
        </div>

        <!-- Ticket & Stakeholder Details Card -->
        <div class="card mb-4 shadow-sm border-0 bg-light">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <small class="text-muted text-uppercase">Order Number:</small>
                        <div class="font-weight-bold text-primary">#<?= htmlspecialchars($ticket->order_increment_id ?: $ticket->order_id); ?></div>
                        <small class="text-muted">Item: <?= htmlspecialchars($ticket->product_name ?: ('Product #' . $ticket->products)); ?></small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted text-uppercase">Shopper:</small>
                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->customer_first_name . ' ' . $ticket->customer_last_name); ?></div>
                        <small class="text-muted"><?= htmlspecialchars($ticket->customer_email); ?></small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted text-uppercase">Merchant:</small>
                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($ticket->merchant_name ?: 'Yellow Merchant'); ?></div>
                        <small class="text-info font-weight-bold">15-Day Holdback: MUR <?= number_format($merchant_holdback_balance, 2); ?></small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted text-uppercase">Assignment & Status:</small>
                        <div class="font-weight-bold text-dark">
                            Assigned To: <span class="text-primary"><?= htmlspecialchars($ticket->assigned_user_name ?: ($ticket->assigned_to ? 'Account User #' . $ticket->assigned_to : 'Unassigned')); ?></span>
                        </div>
                        <div>
                            Status: <span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($ticket->status_code); ?></span>
                        </div>
                        <?php if ((int)$ticket->refund_deducted_from_holdback === 1): ?>
                            <div class="mt-1 small text-success font-weight-bold">
                                <i class="fa fa-check-circle mr-1"></i> Refund: MUR <?= number_format($ticket->refund_amount, 2); ?> (Ref: <?= htmlspecialchars($ticket->refund_reference ?: '-'); ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Action Controls -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fa fa-cogs mr-2 text-warning"></i> Role Actions & Governance</span>
                <span class="badge badge-light text-dark">Current Role: <?= htmlspecialchars($user_role); ?></span>
            </div>
            <div class="card-body p-3 bg-white">
                <div class="d-flex flex-wrap" style="gap: 10px;">

                    <!-- 1. ASSIGN @ACCOUNT (Admin only, when in Open / Processing / ReOpen) -->
                    <?php if (in_array($ticket->status_code, ['Open', 'Processing', 'ReOpen'])): ?>
                        <button type="button" class="btn btn-warning btn-sm font-weight-bold text-dark" onclick="openAdminModal('assign_account')">
                            <i class="fa fa-user-plus mr-1"></i> Assign to @Account
                        </button>
                    <?php endif; ?>

                    <!-- 2. @ACCOUNT DONE ACTION (When Processing) -->
                    <?php if ($ticket->status_code === 'Processing'): ?>
                        <button type="button" class="btn btn-info btn-sm font-weight-bold text-white" onclick="openAdminModal('account_done')">
                            <i class="fa fa-money mr-1"></i> Deduct from Hold-back & Mark Done (@Account)
                        </button>
                    <?php endif; ?>

                    <!-- 3. NORMAL CLOSE (Admin only, when Open or Done) -->
                    <?php if (in_array($ticket->status_code, ['Open', 'Done']) && $ticket->resolution_status !== 'resolution_requested'): ?>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" onclick="openAdminModal('admin_close')">
                            <i class="fa fa-check mr-1"></i> Close Ticket (@Admin)
                        </button>
                    <?php endif; ?>

                    <!-- 4. RESOLUTION DISPUTE ACTIONS (Admin only, when ReOpen) -->
                    <?php if ($ticket->status_code === 'ReOpen'): ?>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" onclick="openAdminModal('resolution_approved')">
                            <i class="fa fa-thumbs-up mr-1"></i> Resolution Approved (Overrule & Refund)
                        </button>
                        <button type="button" class="btn btn-danger btn-sm font-weight-bold" onclick="openAdminModal('resolution_denied')">
                            <i class="fa fa-thumbs-down mr-1"></i> Resolution Denied (Uphold Merchant Denial)
                        </button>
                    <?php endif; ?>

                    <!-- 5. FINAL CLOSE (Admin only, when Done post-resolution) -->
                    <?php if ($ticket->status_code === 'Done' && $ticket->resolution_status === 'resolution_approved'): ?>
                        <button type="button" class="btn btn-dark btn-sm font-weight-bold" onclick="openAdminModal('admin_close_final')">
                            <i class="fa fa-lock mr-1"></i> Close (Final) (@Admin)
                        </button>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Conversation Stream -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold border-bottom">
                <i class="fa fa-comments mr-2 text-primary"></i> Complete Ticket History & Discussion
            </div>
            <div class="card-body p-4" style="max-height: 500px; overflow-y: auto;">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php 
                            $is_admin_or_account = in_array($msg->sender_role, ['admin', 'account']);
                            $bubble_class = $is_admin_or_account ? 'ml-auto bg-light border-primary' : 'mr-auto bg-white border';
                            $role_badge = [
                                'shopper'   => 'badge-primary',
                                'merchant'  => 'badge-success',
                                'admin'     => 'badge-danger',
                                'account'   => 'badge-info'
                            ][$msg->sender_role] ?? 'badge-secondary';
                        ?>
                        <div class="d-flex mb-3 <?= $is_admin_or_account ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <div class="card <?= $bubble_class; ?> shadow-sm" style="max-width: 80%; border-radius: 12px;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                        <span class="badge <?= $role_badge; ?> mr-2">
                                            <?= ucfirst($msg->sender_role); ?>: <?= htmlspecialchars($msg->sender_name); ?>
                                        </span>
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

        <!-- Audit Trail -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold border-bottom">
                <i class="fa fa-history mr-2 text-secondary"></i> State Machine Audit Log
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor Role</th>
                                <th>From State</th>
                                <th>To State</th>
                                <th>Action Taken</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($audit_logs)): ?>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><?= date('d M Y, H:i:s', $log->created_at); ?></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($log->actor_role); ?></span></td>
                                        <td><code><?= htmlspecialchars($log->from_status); ?></code></td>
                                        <td><code><?= htmlspecialchars($log->to_status); ?></code></td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($log->action); ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($log->notes ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-2">No audit log entries recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Admin / Account Reply Form -->
        <?php if (!in_array($ticket->status_code, ['Close (Final)'])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fa fa-reply mr-1"></i> Add Official Reply / Note to Thread
                </div>
                <div class="card-body p-3">
                    <form id="adminReplyForm" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                        <div id="replyAlert" class="alert d-none"></div>

                        <div class="form-group mb-2">
                            <textarea name="message" id="adminReplyMessage" rows="3" class="form-control" placeholder="Type response visible to Shopper, Merchant & Staff..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="btn btn-outline-secondary btn-sm mb-0 cursor-pointer">
                                    <i class="fa fa-camera mr-1"></i> Upload Image / Doc
                                    <input type="file" name="attachment" id="adminReplyAttachment" accept="image/*,.pdf" style="display: none;">
                                </label>
                                <span id="adminFileName" class="small text-muted ml-2"></span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" id="btnAdminReply">
                                <i class="fa fa-paper-plane mr-1"></i> Post Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Admin / Account Action Modal -->
<div class="modal fade" id="adminActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="adminActionModalTitle">Perform Action</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="adminActionForm">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket->ticket_id); ?>">
                <input type="hidden" name="action_type" id="admin_action_type" value="">
                <input type="hidden" name="decision" id="admin_decision" value="">

                <div class="modal-body p-4">
                    <div id="adminActionAlert" class="alert d-none"></div>

                    <!-- ASSIGN @ACCOUNT -->
                    <div id="group_assign_account" class="action-group d-none">
                        <p class="text-muted">Route this ticket to the Finance / Accounts department for refund processing or YM delivery Add-on invoice request.</p>
                        <div class="form-group">
                            <label class="font-weight-bold">Select Account Staff User (Optional)</label>
                            <select name="account_user_id" class="form-control">
                                <option value="">-- General Accounts Queue --</option>
                                <?php if (!empty($account_users)): ?>
                                    <?php foreach ($account_users as $u): ?>
                                        <option value="<?= $u->id; ?>"><?= htmlspecialchars($u->name); ?> (<?= htmlspecialchars($u->email); ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- @ACCOUNT DONE REFUND CONFIRMATION -->
                    <div id="group_account_done" class="action-group d-none">
                        <div class="alert alert-info">
                            <strong>Hold-back Ledger Confirmation:</strong>
                            <p class="mb-0">Deduct refund from Merchant (<?= htmlspecialchars($ticket->merchant_name); ?>) 15-day sales hold-back balance (Available: MUR <?= number_format($merchant_holdback_balance, 2); ?>).</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Refund Amount (MUR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="refund_amount" class="form-control" value="<?= number_format((float)($ticket->refund_amount > 0 ? $ticket->refund_amount : ($ticket->item_price ?? 0)), 2, '.', ''); ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Refund Reference / Transaction ID</label>
                                <input type="text" name="refund_reference" class="form-control" value="REF-<?= date('Ymd'); ?>-<?= strtoupper(substr(md5(uniqid()), 0, 6)); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Financial Processing Notes / Details</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Deducted from 15-day sales reserve via Payment Gateway..."></textarea>
                        </div>
                    </div>

                    <!-- ADMIN CLOSE -->
                    <div id="group_admin_close" class="action-group d-none">
                        <p>Verify resolution and close this ticket normally. Payout eligibility will be restored according to standard schedule.</p>
                        <div class="form-group">
                            <label class="font-weight-bold">Closure Notes</label>
                            <textarea name="close_notes" class="form-control" rows="2" placeholder="Optional closure comments..."></textarea>
                        </div>
                    </div>

                    <!-- RESOLUTION APPROVED / DENIED -->
                    <div id="group_resolution_decision" class="action-group d-none">
                        <p id="resDecisionText" class="font-weight-bold"></p>
                        <div class="form-group">
                            <label class="font-weight-bold">Investigation Findings & Rationale <span class="text-danger">*</span></label>
                            <textarea name="decision_notes" id="resDecisionNotes" class="form-control" rows="3" placeholder="Provide full justification..." required></textarea>
                        </div>
                    </div>

                    <!-- CLOSE FINAL -->
                    <div id="group_close_final" class="action-group d-none">
                        <div class="alert alert-danger">
                            <i class="fa fa-lock mr-1"></i> <strong>Close (Final) Warning:</strong> This terminates the dispute permanently. Neither party will be able to reopen or escalate this ticket further.
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Final Closure Notes</label>
                            <textarea name="final_notes" class="form-control" rows="2" placeholder="Final comments..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold" id="btnAdminSubmitAction">Execute Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdminModal(action) {
    $('#admin_action_type').val(action);
    $('.action-group').addClass('d-none');
    $('#adminActionAlert').addClass('d-none').text('');

    if (action === 'assign_account') {
        $('#adminActionModalTitle').text('Assign Ticket to @Account');
        $('#group_assign_account').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-danger btn-success btn-dark').addClass('btn-warning text-dark').text('Assign to @Account');
    } else if (action === 'account_done') {
        $('#adminActionModalTitle').text('Confirm Refund Deduction & Mark Done');
        $('#group_account_done').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-warning btn-danger btn-dark').addClass('btn-info').text('Confirm & Mark Done');
    } else if (action === 'admin_close') {
        $('#adminActionModalTitle').text('Close Order Resolution Ticket');
        $('#group_admin_close').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-warning btn-danger btn-info btn-dark').addClass('btn-success').text('Confirm Normal Closure');
    } else if (action === 'resolution_approved') {
        $('#adminActionModalTitle').text('Approve Dispute Resolution');
        $('#admin_decision').val('approved');
        $('#resDecisionText').text('You are approving the Shopper dispute. This will overrule the merchant decision and route the ticket to @Account for a customer refund.');
        $('#group_resolution_decision').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-warning btn-danger btn-info btn-dark').addClass('btn-success').text('Approve Resolution');
    } else if (action === 'resolution_denied') {
        $('#adminActionModalTitle').text('Deny Dispute Resolution');
        $('#admin_decision').val('denied');
        $('#resDecisionText').text('You are upholding the merchant decision and denying the Shopper dispute. The ticket will be permanently closed as Close (Final).');
        $('#group_resolution_decision').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-warning btn-success btn-info btn-dark').addClass('btn-danger').text('Deny Resolution & Close (Final)');
    } else if (action === 'admin_close_final') {
        $('#adminActionModalTitle').text('Permanent Final Closure');
        $('#group_close_final').removeClass('d-none');
        $('#btnAdminSubmitAction').removeClass('btn-warning btn-success btn-info btn-danger').addClass('btn-dark').text('Confirm Close (Final)');
    }

    $('#adminActionModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    $('#adminReplyAttachment').on('change', function() {
        var fn = $(this).val().split('\\').pop();
        $('#adminFileName').text(fn || '');
    });

    $('#adminReplyForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnAdminReply');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Posting...');

        var formData = new FormData(this);
        $.ajax({
            url: '<?= base_url('admin/order-resolution/reply'); ?>',
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
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Post Reply');
                }
            },
            error: function() {
                $('#replyAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Post Reply');
            }
        });
    });

    $('#adminActionForm').on('submit', function(e) {
        e.preventDefault();
        var action = $('#admin_action_type').val();
        var targetUrl = '';

        if (action === 'assign_account') {
            targetUrl = '<?= base_url('admin/order-resolution/assign-account'); ?>';
        } else if (action === 'account_done') {
            targetUrl = '<?= base_url('admin/order-resolution/account-done'); ?>';
        } else if (action === 'admin_close') {
            targetUrl = '<?= base_url('admin/order-resolution/admin-close'); ?>';
        } else if (action === 'resolution_approved' || action === 'resolution_denied') {
            targetUrl = '<?= base_url('admin/order-resolution/admin-resolve'); ?>';
        } else if (action === 'admin_close_final') {
            targetUrl = '<?= base_url('admin/order-resolution/admin-close-final'); ?>';
        }

        var $btn = $('#btnAdminSubmitAction');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#adminActionAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    $('#adminActionAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                    $btn.prop('disabled', false).text('Execute Action');
                }
            },
            error: function() {
                $('#adminActionAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Network error.');
                $btn.prop('disabled', false).text('Execute Action');
            }
        });
    });
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
