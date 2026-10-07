<?php $this->load->view('common/fbc-user/header'); ?>

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
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Product:</strong></p>
                        <p><?= htmlspecialchars($product ? $product->name : 'All Items / General'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Category:</strong></p>
                        <p>
                            <span class="badge badge-info"><?= htmlspecialchars($resolution->category); ?></span>
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
                            <span class="text-success font-weight-bold"><?= ($resolution->refund_amount > 0) ? CURRENCY_TYPE . ' ' . number_format($resolution->refund_amount, 2) : 'N/A'; ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Resolution Status:</strong> 
                            <span class="badge badge-<?= ($resolution->resolution_status === 'resolution_approved') ? 'success' : (($resolution->resolution_status === 'resolution_denied') ? 'danger' : 'warning'); ?>">
                                <?= ucwords(str_replace('_', ' ', $resolution->resolution_status)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Admin Action Management Controls -->
                <div class="p-3 bg-light border rounded mb-4">
                    <h6 class="font-weight-bold mb-3"><i class="fa fa-cogs"></i> Resolution Management Controls</h6>
                    <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">

                        <!-- @Help: Assign to @Acct (Status -> Processing) -->
                        <?php if ($resolution->assigned_role !== 'Acct' && in_array($resolution->status, ['Open', 'ReOpen'])): ?>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#assignAcctModal">
                                <i class="fa fa-user-plus"></i> Assign to @acct (Processing)
                            </button>
                        <?php endif; ?>

                        <!-- @Acct: Mark Done (Status -> Done) -->
                        <?php if ($resolution->status === 'Processing'): ?>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#markDoneModal">
                                <i class="fa fa-check-circle"></i> Mark Done (Payment/Refund Completed)
                            </button>
                        <?php endif; ?>

                        <!-- @Help: Close Ticket (Status -> Close) -->
                        <?php if ($resolution->status !== 'Close' && $resolution->status !== 'Close (Final)'): ?>
                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#closeTicketModal">
                                <i class="fa fa-lock"></i> Close Ticket (Shopper Has Resolution Option)
                            </button>
                        <?php endif; ?>

                        <!-- @Help: Close (Final) (Status -> Close (Final)) -->
                        <?php if ($resolution->status !== 'Close (Final)'): ?>
                            <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#closeFinalModal">
                                <i class="fa fa-archive"></i> Close (Final - No Further Options)
                            </button>
                        <?php endif; ?>

                        <!-- Resolution Decision (@Help Only) -->
                        <?php if ($resolution->resolution_status === 'resolution_requested'): ?>
                            <div class="ml-auto d-flex" style="gap: 8px;">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#resApproveModal">
                                    <i class="fa fa-thumbs-up"></i> Resolution Approved
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#resDeniedModal">
                                    <i class="fa fa-thumbs-down"></i> Resolution Denied
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Conversation Stream -->
                <h5 class="font-weight-bold mb-3"><i class="fa fa-comments"></i> Conversation</h5>
                <div class="chat-box">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): 
                            $role_class = 'chat-msg-' . $msg->sender_role;
                        ?>
                            <div class="chat-msg <?= $role_class; ?>">
                                <div class="chat-msg-meta">
                                    <strong><?= htmlspecialchars($msg->sender_name ?: ucfirst($msg->sender_role)); ?></strong> 
                                    (<?= ucfirst($msg->sender_role); ?>) &bull; <?= date('d M Y, h:i A', $msg->created_at); ?>
                                </div>
                                <div class="chat-msg-body">
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
                        <p class="text-muted text-center py-4">No conversation messages yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Reply Form -->
                <?php if ($resolution->status !== 'Close (Final)'): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 font-weight-bold">Post Admin Reply</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                                
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">Posting As:</label>
                                    <div class="form-check form-check-inline ml-2">
                                        <input class="form-check-input" type="radio" name="role" id="roleHelp" value="help" checked>
                                        <label class="form-check-label" for="roleHelp">Support Team (@Help)</label>
                                    </div>
                                    <div class="form-check form-check-inline ml-2">
                                        <input class="form-check-input" type="radio" name="role" id="roleAcct" value="acct">
                                        <label class="form-check-label" for="roleAcct">Accounting Team (@Acct)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <textarea name="message" rows="3" class="form-control" placeholder="Enter message..." required></textarea>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <input type="file" name="attachment" class="form-control-file" accept="image/*,.pdf">
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-paper-plane"></i> Post Reply
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

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
            <form action="<?= base_url('order_resolution/assign_to_acct'); ?>" method="POST">
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
            <form action="<?= base_url('order_resolution/mark_done'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fa fa-check-circle"></i> Mark Done (Payment/Refund Completed)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the refund or payment has been completed. The ticket status will update to <strong>Done</strong>, order status will update to Refund Paid (17) if applicable, and a confirmation email will be sent to the shopper.</p>
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
            <form action="<?= base_url('order_resolution/close_ticket'); ?>" method="POST">
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
            <form action="<?= base_url('order_resolution/close_final'); ?>" method="POST">
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
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST">
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
            <form action="<?= base_url('order_resolution/resolution_decision'); ?>" method="POST">
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

<?php $this->load->view('common/fbc-user/footer'); ?>
