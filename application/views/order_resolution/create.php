<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li><a href="<?php echo base_url('order_resolution'); ?>"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></a></li>
                <li class="active"><?php echo $this->lang->line('raise_resolution_request') ?: 'New Resolution Request'; ?></li>
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
                    <h1><?php echo $this->lang->line('submit_order_resolution_request') ?: 'Submit Order Resolution Request'; ?></h1>
                    <p class="text-muted" style="margin-bottom: 25px;">
                        <?php echo $this->lang->line('order_resolution_desc') ?: 'Order Resolution is available exclusively for items where your Return or Replacement request has been rejected by the Merchant.'; ?>
                    </p>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <?php if (empty($rejected_items)): ?>
                        <div class="alert alert-info" style="margin-bottom: 20px;">
                            <i class="fa fa-info-circle"></i>
                            <strong>No rejected Return or Replacement requests found.</strong>
                            Order Resolution is only available after a Merchant rejects a Return or Replacement request.
                            You can check your existing requests on your <a href="<?php echo base_url('customer/my-orders'); ?>" class="alert-link" style="text-decoration: underline; font-weight: bold;">My Orders</a> page.
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo base_url('order_resolution/store'); ?>" method="POST" enctype="multipart/form-data" class="ajax-resolution-form">
                        <div class="row">
                            <!-- Rejected Order Item Selection -->
                            <div class="col-md-12 form-group">
                                <label for="rejected_item_selection"><b><?php echo $this->lang->line('rejected_order_item') ?: 'Rejected Order Item'; ?> <span class="text-danger">*</span></b></label>
                                <select name="rejected_item_selection" id="rejected_item_selection" class="form-control" required onchange="handleItemSelect(this)" <?php echo empty($rejected_items) ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo $this->lang->line('select_rejected_item') ?: '-- Select Rejected Item --'; ?></option>
                                    <?php if (!empty($rejected_items)): ?>
                                        <?php foreach ($rejected_items as $item): 
                                            $isSelected = ($selected_order_id == $item->order_id && $selected_product_id == $item->product_id);
                                        ?>
                                            <option value="<?php echo $item->order_id . ':' . $item->product_id; ?>"
                                                    data-order-id="<?php echo $item->order_id; ?>"
                                                    data-product-id="<?php echo $item->product_id; ?>"
                                                    data-category="<?php echo $item->category; ?>"
                                                    data-type="<?php echo $item->request_type; ?>"
                                                    data-order-number="<?php echo htmlspecialchars($item->order_number); ?>"
                                                    data-product-name="<?php echo htmlspecialchars($item->product_name); ?>"
                                                    data-merchant-name="<?php echo htmlspecialchars($item->merchant_name); ?>"
                                                    <?php echo $isSelected ? 'selected' : ''; ?>>
                                                Order #<?php echo htmlspecialchars($item->order_number); ?> - <?php echo htmlspecialchars($item->product_name); ?> (<?php echo htmlspecialchars($item->rejected_status_text); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <input type="hidden" name="order_id" id="order_id" value="<?php echo $selected_order_id ?: ''; ?>">
                                <input type="hidden" name="product_id" id="product_id" value="<?php echo $selected_product_id ?: ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Subject Type -->
                            <div class="col-md-6 form-group">
                                <label for="category"><b><?php echo $this->lang->line('subject_type') ?: 'Subject Type'; ?> <span class="text-danger">*</span></b></label>
                                <select name="category" id="category" class="form-control" required <?php echo empty($rejected_items) ? 'disabled' : ''; ?>>
                                    <option value="Refund" <?php echo ($default_category === 'Refund') ? 'selected' : ''; ?>>Refund</option>
                                    <option value="Replacement" <?php echo ($default_category === 'Replacement') ? 'selected' : ''; ?>>Replacement</option>
                                    <option value="Delivery" <?php echo ($default_category === 'Delivery') ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Others" <?php echo ($default_category === 'Others') ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6 form-group">
                                <label for="priority"><b><?php echo $this->lang->line('priority') ?: 'Priority'; ?> <span class="text-danger">*</span></b></label>
                                <select name="priority" id="priority" class="form-control" required <?php echo empty($rejected_items) ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo $this->lang->line('select_priority') ?: 'Select Priority'; ?></option>
                                    <option value="High">High</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="form-group">
                            <label for="message"><b><?php echo $this->lang->line('message') ?: 'Message / Description'; ?> <span class="text-danger">*</span></b></label>
                            <textarea name="message" id="message" rows="5" class="form-control" placeholder="<?php echo $this->lang->line('enter_resolution_details') ?: 'Describe the issue or reason for this request in detail...'; ?>" required <?php echo empty($rejected_items) ? 'disabled' : ''; ?>></textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="form-group">
                            <label for="attachment"><b><?php echo $this->lang->line('upload_image_optional') ?: 'Upload Image / Document (Optional)'; ?></b></label>
                            <input type="file" name="attachment" id="attachment" class="form-control" accept="image/*,.pdf" <?php echo empty($rejected_items) ? 'disabled' : ''; ?>>
                            <small class="text-muted">Allowed formats: JPG, PNG, WEBP, PDF. Max size: 5MB.</small>
                        </div>

                        <div class="form-group text-right" style="margin-top: 30px;">
                            <a href="<?php echo base_url('order_resolution'); ?>" class="btn btn-default" style="margin-right: 10px;">
                                <?php echo $this->lang->line('cancel') ?: 'Cancel'; ?>
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" <?php echo empty($rejected_items) ? 'disabled' : ''; ?>>
                                <i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_resolution_request') ?: 'Submit Resolution Request'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleItemSelect(selectElem) {
    var selectedOpt = selectElem.options[selectElem.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) {
        document.getElementById('order_id').value = '';
        document.getElementById('product_id').value = '';
        return;
    }
    var orderId = selectedOpt.getAttribute('data-order-id');
    var productId = selectedOpt.getAttribute('data-product-id');
    var category = selectedOpt.getAttribute('data-category');

    document.getElementById('order_id').value = orderId;
    document.getElementById('product_id').value = productId;

    if (category) {
        var catSelect = document.getElementById('category');
        for (var i = 0; i < catSelect.options.length; i++) {
            if (catSelect.options[i].value.toLowerCase() === category.toLowerCase()) {
                catSelect.selectedIndex = i;
                break;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('rejected_item_selection');
    if (sel && sel.value) {
        handleItemSelect(sel);
    }
});

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
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

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
                    $btn.html('<i class="fa fa-check"></i> Submitted');
                    showNotification('success', res.message || 'Ticket submitted successfully.');

                    setTimeout(function() {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                } else {
                    $btn.prop('disabled', false).html(origHtml);
                    var msg = (res && res.message) ? res.message : 'An error occurred while submitting your ticket.';
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
                var errMsg = 'Unable to submit request. Please check your connection and try again.';
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