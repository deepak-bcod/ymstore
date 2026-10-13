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
                            <small class="text-muted"><?php echo $this->lang->line('created_on') ?: 'Created on'; ?> <?php echo date('d M Y, h:i A', $resolution->created_at); ?></small>
                        </div>
                        <div class="col-sm-4 col-md-4 text-right">
                            <?php 
                            $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $resolution->status));
                            $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $resolution->status));
                            ?>
                            <span class="badge-status-<?php echo $status_slug; ?>">
                                <?php echo htmlspecialchars($this->lang->line($status_key) ?: $resolution->status); ?>
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

                            <?php if ($resolution->merchant_action !== 'none' || $resolution->delivery_option !== 'none' || $resolution->refund_amount > 0): ?>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <?php if ($resolution->merchant_action !== 'none'): ?>
                                        <div class="col-md-4">
                                            <p><b><?php echo $this->lang->line('merchant_decision') ?: 'Merchant Decision'; ?>:</b><br>
                                                <span class="text-primary font-weight-bold"><?php echo htmlspecialchars($this->lang->line($resolution->merchant_action) ?: ucwords(str_replace('_', ' ', $resolution->merchant_action))); ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resolution->delivery_option !== 'none'): ?>
                                        <div class="col-md-4">
                                            <p><b><?php echo $this->lang->line('delivery_option') ?: 'Delivery Option'; ?>:</b><br>
                                                <span class="text-success font-weight-bold"><?php echo htmlspecialchars($this->lang->line($resolution->delivery_option) ?: ucwords(str_replace('_', ' ', $resolution->delivery_option))); ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resolution->refund_amount > 0): ?>
                                        <div class="col-md-4">
                                            <p><b><?php echo $this->lang->line('approved_refund_amount') ?: 'Approved Refund Amount'; ?>:</b><br>
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
                                        <i class="fa fa-flag"></i> <?php echo $this->lang->line('resolution_request') ?: 'Resolution Request'; ?>
                                    </button>
                                    <small class="text-muted" style="margin-left: 10px;"><?php echo $this->lang->line('resolution_escalate_prompt') ?: 'Disagree with the merchant\'s decision? Request resolution assistance from Yellow Markets (@Help).'; ?></small>
                                </div>
                            <?php elseif ($resolution->resolution_status === 'resolution_requested'): ?>
                                <div style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 10px;">
                                    <span class="label label-warning" style="font-size: 13px; padding: 6px 12px;">
                                        <i class="fa fa-hourglass-half"></i> <?php echo $this->lang->line('dispute_under_review_badge') ?: 'Resolution Request Pending Review with Yellow Markets (@Help)'; ?>
                                    </span>
                                </div>
                            <?php elseif ($resolution->status === 'Close (Final)'): ?>
                                <div style="margin-top: 15px; border-top: 1px dashed #ddd; padding-top: 10px;">
                                    <span class="label label-default" style="font-size: 13px; padding: 6px 12px;">
                                        <i class="fa fa-lock"></i> <?php echo $this->lang->line('status_close_final') ?: 'Resolution Closed (Final)'; ?>
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
                                $sender_display = ($msg->sender_role === 'shopper') ? ($this->lang->line('you') ?: 'You') : (($msg->sender_role === 'merchant') ? ($merchant ? $merchant->publication_name : ($this->lang->line('merchant') ?: 'Merchant')) : ($this->lang->line('support_team') ?: 'Yellow Markets Support'));
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
                                                <i class="fa fa-paperclip"></i> <?php echo $this->lang->line('view_attachment') ?: 'View Attachment'; ?> (<?php echo htmlspecialchars($msg->attachment); ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center" style="padding: 20px;"><?php echo $this->lang->line('no_conversation_messages_yet') ?: 'No conversation messages yet.'; ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <?php if ($resolution->status !== 'Close (Final)'): ?>
                        <div class="panel panel-default" style="border-radius: 6px;">
                            <div class="panel-heading">
                                <h4 class="panel-title"><?php echo $this->lang->line('post_reply') ?: 'Post a Reply'; ?></h4>
                            </div>
                            <div class="panel-body">
                                <form action="<?php echo base_url('order_resolution/reply'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                                    <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                                    <div class="form-group">
                                        <textarea name="message" rows="4" class="form-control" placeholder="<?php echo $this->lang->line('enter_response_to_merchant') ?: 'Write your response to the merchant...'; ?>" required></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8 col-md-8">
                                            <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                                            <small class="text-muted"><?php echo $this->lang->line('optional_attachment') ?: 'Optional attachment (Max: 5MB)'; ?></small>
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
                            <?php echo $this->lang->line('closed_final_desc') ?: 'This Order Resolution ticket has been closed with no further actions.'; ?>
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
            <form action="<?php echo base_url('order_resolution/request_resolution'); ?>" method="POST" class="ajax-resolution-form">
                <input type="hidden" name="ticket_number" value="<?php echo htmlspecialchars($resolution->ticket_number); ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('request_resolution_escalation') ?: 'Request Resolution Escalation (@Help)'; ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo $this->lang->line('escalation_modal_desc') ?: 'If you disagree with the merchant\'s decision or the issue has not been satisfactorily resolved, submit this request to escalate the ticket directly to Yellow Markets Support (@Help).'; ?></p>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('message_description') ?: 'Reason / Additional Details:'; ?></label>
                        <textarea name="message" class="form-control" rows="4" placeholder="<?php echo $this->lang->line('explain_mediation_reason') ?: 'Explain why you are requesting mediation...'; ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel') ?: 'Cancel'; ?></button>
                    <button type="submit" class="btn btn-warning"><?php echo $this->lang->line('confirm_resolution_request') ?: 'Confirm Resolution Request'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
                        $modal.modal('hide');
                    }

                    setTimeout(function() {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    var msg = (res && res.message) ? res.message : (<?php echo json_encode($this->lang->line('ajax_error') ?: 'An error occurred while processing your request.'); ?>);
                    showNotification('error', msg);

                    if (res && res.requires_reload) {
                        setTimeout(function() {
                            window.location.reload();
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
