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
        <div class="row">
            <?php 
            $data['side_tab'] = 'order_resolution';
            $this->load->view('common/profile_sidebar', $data); 
            ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">

                    <!-- Header & Status Pill -->
                    <div class="row" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                        <div class="col-sm-8 col-md-8">
                            <h2 style="margin: 0;">
                                <?php echo $this->lang->line('ticket') ?: 'Ticket'; ?> #<?php echo htmlspecialchars($resolution->ticket_number); ?>
                            </h2>
                            <small class="text-muted">Created on <?php echo date('d M Y, h:i A', $resolution->created_at); ?></small>
                        </div>
                        <div class="col-sm-4 col-md-4 text-right">
                            <?php 
                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $resolution->status));
                            ?>
                            <span class="badge-status-<?php echo $status_slug; ?>">
                                <?php echo htmlspecialchars($resolution->status); ?>
                            </span>
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
                                    <p><b>Order Number:</b><br>
                                        <a href="<?php echo base_url('customer/my-orders'); ?>" target="_blank">
                                            #<?php echo htmlspecialchars($resolution->order_number); ?>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <p><b>Product:</b><br>
                                        <?php echo htmlspecialchars($product ? $product->name : 'All Items'); ?>
                                    </p>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <p><b>Merchant:</b><br>
                                        <?php echo htmlspecialchars($merchant ? $merchant->publication_name : 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <p><b>Category / Priority:</b><br>
                                        <span class="label label-info"><?php echo htmlspecialchars($resolution->category); ?></span>
                                        <span class="label label-default"><?php echo htmlspecialchars($resolution->priority); ?></span>
                                    </p>
                                </div>
                            </div>

                            <?php if ($resolution->merchant_action !== 'none' || $resolution->delivery_option !== 'none' || $resolution->refund_amount > 0): ?>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <?php if ($resolution->merchant_action !== 'none'): ?>
                                        <div class="col-md-4">
                                            <p><b>Merchant Decision:</b><br>
                                                <span class="text-primary font-weight-bold"><?php echo ucwords(str_replace('_', ' ', $resolution->merchant_action)); ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resolution->delivery_option !== 'none'): ?>
                                        <div class="col-md-4">
                                            <p><b>Delivery Option:</b><br>
                                                <span class="text-success font-weight-bold"><?php echo ucwords(str_replace('_', ' ', $resolution->delivery_option)); ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resolution->refund_amount > 0): ?>
                                        <div class="col-md-4">
                                            <p><b>Approved Refund Amount:</b><br>
                                                <span class="text-success font-weight-bold"><?php echo CURRENCY_TYPE . ' ' . number_format($resolution->refund_amount, 2); ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Shopper Resolution Escalation Button -->
                            <?php if (in_array($resolution->status, ['Close', 'Done', 'Processing']) && $resolution->resolution_status !== 'resolution_requested'): ?>
                                <div style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 10px;">
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#resolutionRequestModal">
                                        <i class="fa fa-flag"></i> <?php echo $this->lang->line('resolution_request') ?: 'Resolution Request (Escalate to Support)'; ?>
                                    </button>
                                    <small class="text-muted" style="margin-left: 10px;">Not satisfied? Click to escalate this request directly to Yellow Markets Support (@Help).</small>
                                </div>
                            <?php elseif ($resolution->resolution_status === 'resolution_requested'): ?>
                                <div style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 10px;">
                                    <span class="label label-warning" style="font-size: 13px; padding: 6px 12px;">
                                        <i class="fa fa-hourglass-half"></i> Resolution Request Pending Review with Yellow Markets (@Help)
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Conversation History Stream -->
                    <h3 style="margin-top: 25px; margin-bottom: 12px;"><?php echo $this->lang->line('conversation') ?: 'Resolution Conversation'; ?></h3>
                    <div class="chat-box">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $msg): 
                                $role_class = 'chat-' . $msg->sender_role;
                                $sender_display = ($msg->sender_role === 'shopper') ? 'You' : (($msg->sender_role === 'merchant') ? ($merchant ? $merchant->publication_name : 'Merchant') : 'Yellow Markets Support');
                            ?>
                                <div class="chat-message <?php echo $role_class; ?>">
                                    <div class="chat-meta">
                                        <b><?php echo htmlspecialchars($sender_display); ?></b> (<?php echo ucfirst($msg->sender_role); ?>) &bull; <?php echo date('d M Y, h:i A', $msg->created_at); ?>
                                    </div>
                                    <div class="chat-body">
                                        <?php echo nl2br(htmlspecialchars($msg->message, ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                    <?php if (!empty($msg->attachment)): ?>
                                        <div class="chat-attachment">
                                            <a href="<?php echo base_url('uploads/order_resolution/' . $msg->attachment); ?>" target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-paperclip"></i> View Attachment (<?php echo htmlspecialchars($msg->attachment); ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center" style="padding: 20px;">No messages yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <?php if ($resolution->status !== 'Close (Final)'): ?>
                        <div class="panel panel-default" style="border-radius: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title"><?php echo $this->lang->line('post_reply') ?: 'Post a Reply'; ?></h4>
                            </div>
                            <div class="panel-body">
                                <form action="<?php echo base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                                    <div class="form-group">
                                        <textarea name="message" rows="4" class="form-control" placeholder="Write your response to the merchant..." required></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 col-md-8">
                                            <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                                            <small class="text-muted">Optional attachment (Max: 5MB)</small>
                                        </div>
                                        <div class="col-sm-4 col-md-4 text-right">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-reply"></i> <?php echo $this->lang->line('send_reply') ?: 'Send Reply'; ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            This Order Resolution ticket has been closed with no further actions.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resolution Escalation Modal -->
<div class="modal fade" id="resolutionRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('order_resolution/request_resolution'); ?>" method="POST">
                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Request Resolution Escalation (@Help)</h4>
                </div>
                <div class="modal-body">
                    <p>If you disagree with the merchant's decision or the issue has not been satisfactorily resolved, submit this request to escalate the ticket directly to Yellow Markets Support (@Help).</p>
                    <div class="form-group">
                        <label>Reason / Additional Details:</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Explain why you are requesting mediation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Resolution Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
