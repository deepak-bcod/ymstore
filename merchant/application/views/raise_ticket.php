<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <div class="content-main form-dashboard helf-section">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><?= !empty($page_title) ? $page_title : lang('raise_ticket'); ?></h2>

    <a href="javascript:void(0);" onclick="window.history.back();" 
       style="text-decoration: none; color: #000000; font-weight: bold; border-bottom: 2px solid #555; padding-bottom: 2px;">
        <?= lang('back_to_list'); ?>
    </a>
</div>
            

            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
            <?php endif; ?>
            <form action="<?=site_url('help_desk/submit_ticket');?>" method="POST" name="raise_form" id="raise_form" lang="<?=$this->session->userdata('lcode');?>" enctype="multipart/form-data">

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold"><?= lang('ticket_subject'); ?></label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="<?= lang('ticket_subject_placeholder'); ?>">
                        <div class="alert alert-danger" id="subject_error" style="display:none"><?= lang('subject_required'); ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold"><?= lang('ticket_subject_type'); ?></label>
                        <select name="priority" id="priority" class="form-control">
                            <option value=""><?= lang('ticket_select'); ?></option>
                            <option value="3"><?= lang('ticket_general'); ?></option>
                            <option value="1"><?= lang('ticket_accounting'); ?></option>
                            <option value="2"><?= lang('ticket_tech'); ?></option>
                        </select>
                        <div class="alert alert-danger" id="priority_error" style="display:none"><?= lang('subject_type_required'); ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold"><?= lang('ticket_message'); ?></label>
                    <textarea name="message" id="message" class="form-control" rows="5" placeholder="<?= lang('ticket_msg_placeholder'); ?>"></textarea>
                    <div class="alert alert-danger" id="message_error" style="display:none"><?= lang('message_required'); ?></div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">
                        <?= lang('ticket_attachment'); ?> 
                        <small class="text-muted"><?= lang('ticket_attach_hint'); ?></small>
                    </label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="attachment" name="attachment">
                        <label class="custom-file-label" for="attachment">
                            <?= $this->lang->line('choose_file'); ?>
                        </label>
                    </div>
                </div>

                 <div class="row">
    
    <div class="col-md-6">
                                            <div class="form-group">
                                                <label><?php echo lang('order'); ?></label>
                                                <select id="order_id" name="order_id" class="form-control select2 required-entry" onchange="valueChangeHandler()">
                                                    <option value=""><?php echo lang('select_order'); ?></option>
                                                    <?php foreach ($orders as $order): ?>
                                                        <option value="<?= $order->order_id; ?>">
                                                            #<?= $order->increment_id; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

    <div class="col-md-6">
                                            <div class="form-group">
                                                <label style="display: block;"><?php echo lang('products'); ?></label>
                                                <select id="product_id" name="products" class="form-control select2 required-entry">
                                                    <option value=""><?php echo lang('select_product'); ?></option>
                                                </select>
                                            </div>
                                        </div>

</div> <div class="text-center mt-3">
    <button type="button" class="btn btn-warning font-weight-bold px-5 py-2 text-dark" style="background-color: #ffcc00; border: none; border-radius: 4px;" onclick="raise_ticket()">
        <?= lang('ticket_submit_btn'); ?>
    </button>
</div>

            </form>
        </div>
    </div>
</main>
<script>
    $('#attachment').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

	var LANG = {
		error        : "<?= lang('error'); ?>",
		subject_required       : "<?= lang('subject_required'); ?>",
		subject_type_required       : "<?= lang('subject_type_required'); ?>",
		message_required       : "<?= lang('message_required'); ?>"
	};
</script>
   

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

function raise_ticket(){
    $('#subject_error').hide();
    $('#priority_error').hide();
    $('#message_error').hide();

    if($('#subject').val() == ""){
       $('#subject_error').show();
    }else if($('#priority').val() == ""){
       $('#priority_error').show();
    }else if($('#message').val() == ""){
       $('#message_error').show();
    }else{
       $('#subject_error').hide();
       $('#priority_error').hide();
       $('#message_error').hide();
       $('#raise_form').submit();
    }
}

$(document).ready(function() {
    // 1. Define translated strings from PHP for use in JS
    var labels = {
        select_product: "<?= lang('select_product'); ?>"
    };

    $('.select2').select2();
    $('#order_id').change(valueChangeHandler);

    function valueChangeHandler() {
        var orderId = $('#order_id').val();
        var $productSelect = $('#product_id');

        if(orderId) {
            $.ajax({
                url: '<?= base_url("UserController/get_order_products"); ?>',
                type: 'POST',
                data: { order_id: orderId }, 
                dataType: 'json',
                success: function(response) {   
                    // 2. Use the 'labels' variable instead of hardcoded text
                    $productSelect.empty().append('<option value="">' + labels.select_product + '</option>');

                    if (response && response.length > 0) {
                        $.each(response, function(index, product) {
                            $productSelect.append('<option value="' + product.product_id + '">' + product.name + ' (Qty: ' + product.qty + ')</option>');
                        });
                    }
                    $productSelect.trigger('change.select2');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        } else {
            // 3. Use the 'labels' variable here as well
            $productSelect.empty().append('<option value="">' + labels.select_product + '</option>');
            $productSelect.trigger('change.select2');
        }
    }
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>