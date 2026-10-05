<!-- Order Resolution Ticket Creation Modal -->
<div class="modal fade" id="orderResolutionModal" tabindex="-1" role="dialog" aria-labelledby="orderResolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="modal-title font-weight-bold" id="orderResolutionModalLabel">
                    <i class="fa fa-life-ring mr-2"></i> <?= $this->lang->line('create_ticket') ?: 'Create Order Resolution Ticket'; ?>
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="orderResolutionForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div id="resModalAlert" class="alert d-none"></div>

                    <input type="hidden" name="order_id" id="res_order_id" value="">
                    <input type="hidden" name="order_item_id" id="res_order_item_id" value="">
                    <input type="hidden" name="product_id" id="res_product_id" value="">
                    <input type="hidden" name="merchant_id" id="res_merchant_id" value="">

                    <!-- Order and Product Summary -->
                    <div class="card mb-3 bg-light border-0">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-sm-6">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('order_number') ?: 'Order Number'; ?>:</small>
                                    <div class="font-weight-bold text-primary" id="res_order_number_display">-</div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted text-uppercase"><?= $this->lang->line('product') ?: 'Product'; ?>:</small>
                                    <div class="font-weight-bold text-dark" id="res_product_name_display">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Category (Mandatory) -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">
                                <?= $this->lang->line('category') ?: 'Category'; ?> <span class="text-danger">*</span>
                            </label>
                            <select name="category" id="res_category" class="form-control" required>
                                <option value=""><?= $this->lang->line('select_category') ?: '-- Select Category --'; ?></option>
                                <option value="Delivery"><?= $this->lang->line('category_delivery') ?: 'Delivery Issue'; ?></option>
                                <option value="Refund"><?= $this->lang->line('category_refund') ?: 'Refund Request'; ?></option>
                                <option value="Replacement"><?= $this->lang->line('category_replacement') ?: 'Replacement Request'; ?></option>
                                <option value="Return"><?= $this->lang->line('category_return') ?: 'Return Request'; ?></option>
                                <option value="Others"><?= $this->lang->line('category_others') ?: 'Others'; ?></option>
                            </select>
                        </div>

                        <!-- Priority (Mandatory) -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">
                                <?= $this->lang->line('priority') ?: 'Priority'; ?> <span class="text-danger">*</span>
                            </label>
                            <select name="priority" id="res_priority" class="form-control" required>
                                <option value="Low"><?= $this->lang->line('priority_low') ?: 'Low'; ?></option>
                                <option value="Medium" selected><?= $this->lang->line('priority_medium') ?: 'Medium'; ?></option>
                                <option value="High"><?= $this->lang->line('priority_high') ?: 'High'; ?></option>
                                <option value="Urgent"><?= $this->lang->line('priority_urgent') ?: 'Urgent'; ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Message (Mandatory) -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <?= $this->lang->line('message') ?: 'Message'; ?> <span class="text-danger">*</span>
                        </label>
                        <textarea name="message" id="res_message" rows="4" class="form-control" 
                                  placeholder="<?= $this->lang->line('describe_issue') ?: 'Describe the issue or reason in detail...'; ?>" 
                                  required minlength="10"></textarea>
                    </div>

                    <!-- Image Attachment (Optional) -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <?= $this->lang->line('attachment_optional') ?: 'Attachment / Photo (Optional)'; ?>
                        </label>
                        <input type="file" name="attachment" id="res_attachment" class="form-control-file" accept="image/*,.pdf">
                        <small class="text-muted"><?= $this->lang->line('allowed_files') ?: 'Supported: JPG, PNG, WEBP, PDF (Max 5MB)'; ?></small>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?= $this->lang->line('cancel') ?: 'Cancel'; ?>
                    </button>
                    <button type="submit" class="btn btn-warning font-weight-bold" id="resSubmitBtn">
                        <i class="fa fa-paper-plane mr-1"></i> <?= $this->lang->line('submit_ticket') ?: 'Submit Ticket'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Open modal handler
    $(document).on('click', '.raise-ticket-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        $('#res_order_id').val($btn.data('order-id'));
        $('#res_order_item_id').val($btn.data('item-id'));
        $('#res_product_id').val($btn.data('product-id'));
        $('#res_merchant_id').val($btn.data('merchant-id'));
        $('#res_order_number_display').text($btn.data('increment-id') || $btn.data('order-id'));
        $('#res_product_name_display').text($btn.data('product-name'));
        $('#resModalAlert').addClass('d-none').text('');
        $('#orderResolutionForm')[0].reset();
        $('#orderResolutionModal').modal('show');
    });

    // Submit handler
    $('#orderResolutionForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#resSubmitBtn');
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Submitting...');

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('order-resolution/create'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $('#resModalAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                    setTimeout(function() {
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 1200);
                } else {
                    $('#resModalAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                $('#resModalAlert').removeClass('d-none alert-success').addClass('alert-danger').text('An unexpected error occurred. Please try again.');
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
