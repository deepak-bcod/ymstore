<?php $this->load->view('common/header'); ?>

<style>
    /* 0: Not Opened - Gray */
    .badge-not-opened {
        color: #dc3545;
    }

    /* 1: Open - Green */
    .badge-open {
       color: #3128a7;
    }

    /* 2: Closed - Red */
    .badge-closed {
       color: #28a745;
    }
    th.t, td.tictet-section {
    width: 60px !important;
    word-wrap: break-word !important;
    max-width: 60px !important;
    white-space: normal;
}
th.o, td.o {
    width: 90px !important;
    word-wrap: break-word !important;
    white-space: normal !important;
    max-width: 90px !important;
}
th.s, td.s {
    width: 160px !important;
    word-wrap: break-word !important;
    white-space: normal !important;
    max-width: 160px !important;
}
th.r, td.r {
    width: 110px !important;
    word-wrap: break-word !important;
    max-width: 110px !important;
    white-space: normal;
}
th.st, td.st {
    width: 150px !important;
    word-wrap: break-word !important;
    max-width: 150px !important;
    white-space: normal;
}
th.p, td.p {
    width: 90px !important;
    word-wrap: break-word !important;
    max-width: 90px !important;
    white-space: normal;
}
</style>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li class="active"><?php echo lang('help_desk'); ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">
                    <div class="row">
                        <div class="col-sm-4 col-md-6">
                            <h1><?php echo lang('create_new_ticket'); ?></h1>
                        </div>
                        <div class="row">
                            <div class="col-md-12 check-top">

                                <form class="default-form" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL; ?>MyProfileController/helpDeskPost" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo lang('subject'); ?></label>
                                                <input type="text" class="form-control" placeholder="" value="" id="subject" name="subject">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo lang('recipient_type'); ?></label>
                                                <select id="category_id" name="category_id" class="form-control select2 required-entry" style="width: 100%;">
                                                    <option value=""><?php echo lang('select_support'); ?></option>
                                                    <option value="1"><?php echo lang('merchant_'); ?></option>
                                                    <option value="2"><?php echo lang('yellow_market'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo lang('subject_type'); ?></label>
                                                <select id="priority_id" name="priority_id" class="form-control select2 required-entry" style="width: 100%;">
                                                    <option value=""><?php echo !empty(lang('select_subject_type')) ? lang('select_subject_type') : 'Select a Subject Type'; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo lang('priority'); ?></label>
                                                <select id="priority_level" name="priority_level" class="form-control select2" style="width: 100%;">
                                                    <option value="" selected><?php echo !empty(lang('select_priority')) ? lang('select_priority') : 'Select Priority'; ?></option>
                                                    <option value="Low"><?php echo !empty(lang('low')) ? lang('low') : 'Low'; ?></option>
                                                    <option value="Medium"><?php echo !empty(lang('medium')) ? lang('medium') : 'Medium'; ?></option>
                                                    <option value="High"><?php echo !empty(lang('high')) ? lang('high') : 'High'; ?></option>
                                                    <option value="Critical"><?php echo !empty(lang('critical')) ? lang('critical') : 'Critical'; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php echo lang('message'); ?></label>
                                                <textarea placeholder="" class="form-control" rows="5" name="message" id="message"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php echo lang('attachment'); ?></label>
                                                <input type="file" class="form-control" placeholder="" id="attachment" name="attachment">
                                               
                                            </div>
                                        </div>

                                        

                                        <div class="col-md-4" id="order_select_wrapper">
                                            <div class="form-group">
                                                <label><?php echo lang('order'); ?></label>
                                                <select id="order_id" name="order_id" class="form-control select2" style="width: 100%;">
                                                    <option value=""><?php echo lang('select_order'); ?></option>
                                                    <?php foreach ($orders as $order): ?>
                                                        <option value="<?= $order->order_id; ?>">
                                                            #<?= $order->increment_id; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="merchant_select_wrapper" style="display: none;">
                                            <div class="form-group">
                                                <label><?php echo lang('merchant_'); ?> <span class="text-danger">*</span></label>
                                                <select id="merchant_id" name="merchant_id" class="form-control select2" style="width: 100%;">
                                                    <option value=""><?php echo !empty(lang('select_merchant')) ? lang('select_merchant') : 'Select Merchant'; ?></option>
                                                    <?php if (!empty($merchants)): ?>
                                                        <?php foreach ($merchants as $m): ?>
                                                            <option value="<?= $m->id; ?>"><?= htmlspecialchars($m->publication_name); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4" id="product_select_wrapper">
                                            <div class="form-group">
                                                <label style="display: block;"><?php echo lang('products'); ?></label>
                                                <select id="product_id" name="product_id" class="form-control select2" style="width: 100%;">
                                                    <option value=""><?php echo lang('select_product'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-primary"><?php echo lang('submit_ticket'); ?></button>
                                        </div>
                                    </div>
                                </form>

                                <?php
                                $grouped_tickets = [];
                                foreach ($help_desk_data as $ticket) {
                                    $key = !empty($ticket->ticket_id) ? $ticket->ticket_id : ($ticket->order_id . '_' . $ticket->products . '_' . ($ticket->id ?? ''));
                                    if (!isset($grouped_tickets[$key])) {
                                        $grouped_tickets[$key] = [];
                                    }
                                    $grouped_tickets[$key][] = $ticket;
                                }
                                ?>
                                <div class="table-section">
                                    <table class="table table-bordered mt-3">
                                        <thead>
                                            <tr>
                                                <th class="t"><?php echo lang('ticket_id'); ?></th>
                                                <th class="o"><?php echo lang('order_no'); ?></th>
                                                <th class="s"><?php echo lang('subject'); ?></th>
                                                <th class="r"><?php echo lang('recipient_type'); ?></th>
                                                <th class="st"><?php echo !empty(lang('subject_type')) ? lang('subject_type') : lang('category'); ?></th>
                                                <th class="p"><?php echo lang('priority'); ?></th>
                                                <th><?php echo lang('last_activity'); ?></th>
                                                <th><?php echo lang('status'); ?></th>
                                                <th><?php echo lang('action'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grouped_tickets as $tickets) : ?>
                                                <?php
                                                $first_ticket = $tickets[0];
                                                $last_activity = max(array_column(array_map(fn($t) => (array)$t, $tickets), 'updated_at'));
                                                ?>
                                                <tr>
                                                    <td class="tictet-section">
                                                        <a href="<?= base_url("MyProfileController/viewTicket/{$first_ticket->order_id}/{$first_ticket->ticket_id}/" . ($first_ticket->products ?? '')) ?>" >
                                                            <?= $first_ticket->ticket_id; ?>
                                                        </a>
                                                    </td>
                                                    <td class="o">
                                                       <?= !empty($first_ticket->display_order_no) ? $first_ticket->display_order_no : 'N/A' ?>
                                                    </td>
                                                    <td class="s"><?= $first_ticket->subject; ?></td>
                                                    <td class="r"><?php if($first_ticket->category == 1 ) {echo lang('merchant_');} else{ echo lang('yellow_market'); }  ?></td>
                                                    <td class="st">
                                                        <?php
                                                     
                                                        $subject_types = [
                                                            1 => lang('order_issue'), 
                                                            2 => lang('refund_request'), 
                                                            3 => lang('replacement_request'), 
                                                            4 => lang('merchant_delivery'), 
                                                            5 => lang('ym_delivery'), 
                                                            6 => lang('resolution_request'), 
                                                            7 => lang('general_support'), 
                                                            8 => lang('technical_issue')
                                                        ];
                                                        
                                                        echo $subject_types[$first_ticket->priority] ?? lang('unknown'); 
                                                        ?>
                                                    </td>
                                                    <td class="p">
                                                        <?php
                                                        $p_val = !empty($first_ticket->priority_level) ? $first_ticket->priority_level : '';
                                                        $priority_lang_map = [
                                                            'Low' => lang('low') ?: 'Low',
                                                            'Medium' => lang('medium') ?: 'Medium',
                                                            'High' => lang('high') ?: 'High',
                                                            'Critical' => lang('critical') ?: 'Critical',
                                                            'low' => lang('low') ?: 'Low',
                                                            'medium' => lang('medium') ?: 'Medium',
                                                            'high' => lang('high') ?: 'High',
                                                            'critical' => lang('critical') ?: 'Critical',
                                                            '1' => lang('low') ?: 'Low',
                                                            '2' => lang('medium') ?: 'Medium',
                                                            '3' => lang('high') ?: 'High',
                                                            '4' => lang('critical') ?: 'Critical',
                                                        ];
                                                        if (!empty($p_val) && isset($priority_lang_map[$p_val])) {
                                                            echo $priority_lang_map[$p_val];
                                                        } elseif (!empty($p_val)) {
                                                            echo htmlspecialchars($p_val);
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= date('d/m/Y', $last_activity); ?></td>
                                                    <td>
                                                        <?php
                                                        $status = $first_ticket->status;
                                                        $statusLabels = [0 => lang('not_opened'), 1 => lang('open'), 2 => lang('closed')];
                                                        $statusClasses = [0 => 'badge-not-opened', 1 => 'badge-open', 2 => 'badge-closed'];

                                                        $label = $statusLabels[$status] ?? '-';
                                                        $class = $statusClasses[$status] ?? '';

                                                        echo "<span class='status-badge {$class}'>" . $label . "</span>";
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url("MyProfileController/viewTicket/{$first_ticket->order_id}/{$first_ticket->ticket_id}/" . ($first_ticket->products ?? '')) ?>"><?php echo lang('view'); ?></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>

<script>
$(document).ready(function() {
   
    const subjectTypesMap = {
        "1": [
            { value: "1", text: "<?= lang('order_issue'); ?>" },
            { value: "2", text: "<?= lang('refund_request'); ?>" },
            { value: "3", text: "<?= lang('replacement_request'); ?>" },
            { value: "4", text: "<?= lang('merchant_delivery'); ?>" }
        ],
        "2": [
            { value: "5", text: "<?= lang('ym_delivery'); ?>" },
            { value: "6", text: "<?= lang('resolution_request'); ?>" },
            { value: "7", text: "<?= lang('general_support'); ?>" },
            { value: "8", text: "<?= lang('technical_issue'); ?>" }
        ]
    };

    
    const initialMerchantsHtml = $('#merchant_id').html();
    const selectMerchantText = '<?= !empty(lang('select_merchant')) ? addslashes(lang('select_merchant')) : 'Select Merchant'; ?>';
    const selectProductText = '<?= !empty(lang('select_product')) ? addslashes(lang('select_product')) : 'Select Product'; ?>';
    let currentOrderAjax = null;
    let currentProductAjax = null;
    let isOrderChanging = false;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderMerchantOptions(merchants) {
        var $merchantSelect = $('#merchant_id');
        $merchantSelect.empty().append('<option value="">' + selectMerchantText + '</option>');

        var seenIds = {};
        if (merchants && merchants.length > 0) {
            $.each(merchants, function(index, merchant) {
                if (merchant && merchant.id && !seenIds[merchant.id]) {
                    seenIds[merchant.id] = true;
                    var mName = merchant.publication_name ? escapeHtml(merchant.publication_name) : '';
                    $merchantSelect.append('<option value="' + escapeHtml(merchant.id) + '">' + mName + '</option>');
                }
            });
        }
        // Ensure "Select Merchant" placeholder remains visible and selected
        $merchantSelect.val('').trigger('change.select2');
    }

    function renderProductOptions(products) {
        var $productSelect = $('#product_id');
        $productSelect.empty().append('<option value="">' + selectProductText + '</option>');
        if (products && products.length > 0) {
            $.each(products, function(index, product) {
                var qtyText = product.qty ? ' (Qty: ' + escapeHtml(product.qty) + ')' : '';
                var pName = product.name ? escapeHtml(product.name) : '';
                $productSelect.append('<option value="' + escapeHtml(product.product_id) + '">' + pName + qtyText + '</option>');
            });
        }
        $productSelect.val('').trigger('change.select2');
    }

    $('#category_id').on('change', function() {
        var categoryId = $(this).val();
        var $subjectTypeDropdown = $('#priority_id');

        $subjectTypeDropdown.empty().append('<option value=""><?= lang('select_subject_type'); ?></option>');
 
        if (categoryId !== '' && subjectTypesMap[categoryId]) {
            $.each(subjectTypesMap[categoryId], function(index, item) {
                $subjectTypeDropdown.append('<option value="' + item.value + '">' + item.text + '</option>');
            });
        }

        if (categoryId === '1') {
            $('#merchant_select_wrapper').show();
            $('#merchant_id').prop('required', true);
        } else {
            $('#merchant_select_wrapper').hide();
            $('#merchant_id').prop('required', false);
            $('#merchant_id').val('').trigger('change.select2');
        }

        if ($.fn.select2) {
            $subjectTypeDropdown.val('').trigger('change.select2');
        }
    });

    // Dependent Order Dropdown change handler
    $('#order_id').on('change', function() {
        var order_id = $(this).val();

        // Abort previous pending requests
        if (currentOrderAjax && currentOrderAjax.readyState !== 4) {
            currentOrderAjax.abort();
        }
        if (currentProductAjax && currentProductAjax.readyState !== 4) {
            currentProductAjax.abort();
        }

        isOrderChanging = true;

        // Reset the Merchant dropdown whenever the Order ID changes
        // Do not leave the Merchant field blank: Add "Select Merchant" as the default placeholder text
        var $merchantSelect = $('#merchant_id');
        $merchantSelect.empty().append('<option value="">' + selectMerchantText + '</option>').val('').trigger('change.select2');
        $('#product_id').empty().append('<option value="">' + selectProductText + '</option>').val('').trigger('change.select2');

        if (order_id !== '') {
            // Show merchant select wrapper if category is not Yellow Market (2)
            if ($('#category_id').val() !== '2') {
                $('#merchant_select_wrapper').show();
            }

            currentOrderAjax = $.ajax({
                url: '<?= base_url("MyProfileController/get_order_details") ?>',
                type: 'POST',
                data: { order_id: order_id },
                dataType: 'json',
                success: function(response) {
                    if (response && response.status == 1) {
                        renderMerchantOptions(response.merchants || []);
                        renderProductOptions(response.products || []);
                    } else {
                        renderMerchantOptions([]);
                        renderProductOptions([]);
                    }
                },
                error: function(xhr, status) {
                    if (status !== 'abort') {
                        renderMerchantOptions([]);
                        renderProductOptions([]);
                    }
                },
                complete: function() {
                    isOrderChanging = false;
                }
            });
        } else {
            // When Order is cleared, restore placeholder and default merchants
            $('#merchant_id').html(initialMerchantsHtml).val('').trigger('change.select2');
            $('#product_id').empty().append('<option value="">' + selectProductText + '</option>').val('').trigger('change.select2');
            if ($('#category_id').val() !== '1') {
                $('#merchant_select_wrapper').hide();
            }
            isOrderChanging = false;
        }
    });

    // Dependent Merchant Dropdown change handler
    $('#merchant_id').on('change', function() {
        if (isOrderChanging) {
            return;
        }

        var merchant_id = $(this).val();
        var order_id = $('#order_id').val();

        // Abort previous pending product requests
        if (currentProductAjax && currentProductAjax.readyState !== 4) {
            currentProductAjax.abort();
        }

        // Reset product selection immediately
        $('#product_id').empty().append('<option value="">Loading products...</option>').val('').trigger('change.select2');

        if (order_id !== '') {
            // Filter products by both selected Order and selected Merchant (or all order products if merchant deselected)
            currentProductAjax = $.ajax({
                url: '<?= base_url("MyProfileController/get_order_products") ?>',
                type: 'POST',
                data: { order_id: order_id, merchant_id: merchant_id },
                dataType: 'json',
                success: function(response) {
                    var products = (response && response.products) ? response.products : [];
                    renderProductOptions(products);
                },
                error: function(xhr, status) {
                    if (status !== 'abort') {
                        renderProductOptions([]);
                    }
                }
            });
        } else if (merchant_id !== '') {
            // When no order is selected, load products for the chosen merchant
            currentProductAjax = $.ajax({
                url: '<?= base_url("MyProfileController/get_merchant_products") ?>',
                type: 'POST',
                data: { merchant_id: merchant_id },
                dataType: 'json',
                success: function(response) {
                    var products = (response && response.products) ? response.products : [];
                    renderProductOptions(products);
                },
                error: function(xhr, status) {
                    if (status !== 'abort') {
                        renderProductOptions([]);
                    }
                }
            });
        } else {
            // When neither order nor merchant is selected, clear product options
            renderProductOptions([]);
        }
    });

    $('#subject, #category_id, #priority_id, #priority_level, #priority, #message, #merchant_id, #order_id, #product_id').on('keyup change', function () {
        $(this).removeClass('is-invalid');
        $(this).parent().find('.validation-error').remove();
    });

    // Handle AJAX Ticket Submission Form Process
    $('#customer-personal-info-form').on('submit', function (e) {
        e.preventDefault();
        let isValid = true;

        $('.validation-error').remove();
        $('.form-control').removeClass('is-invalid');

        let subject = $('#subject').val().trim();
        let category = $('#category_id').val();
        let priority = $('#priority_id').val();
        let message = $('#message').val().trim();
        let merchant = $('#merchant_id').val();
        let order = $('#order_id').val();
        let product = $('#product_id').val();

        if (subject === '') {
            showError('#subject', 'Subject is required.');
            isValid = false;
        } else if (subject.length < 3) {
            showError('#subject', 'Subject must be at least 3 characters.');
            isValid = false;
        }

        if (category === '') {
            showError('#category_id', 'Please select a recipient support type.');
            isValid = false;
        } else if (category === '1' && merchant === '') {
            showError('#merchant_id', 'Please select a merchant.');
            isValid = false;
        }

        // Validate dependent relationships when Order is selected
        if (order !== '') {
            if (merchant !== '' && $('#merchant_id option[value="' + merchant + '"]').length === 0) {
                showError('#merchant_id', 'The selected merchant does not belong to the selected order.');
                isValid = false;
            }

            if (product !== '' && $('#product_id option[value="' + product + '"]').length === 0) {
                showError('#product_id', 'The selected product does not belong to the selected order.');
                isValid = false;
            }
        }

        if (priority === '') {
            showError('#priority_id', 'Please select a subject type.');
            isValid = false;
        }

        if (message === '') {
            showError('#message', 'Message is required.');
            isValid = false;
        }

        if (!isValid) return;

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $('#customer-personal-info-form button[type="submit"]').prop('disabled', true).text('Submitting...');
            },
            success: function (response) {
                if (response.flag == 1) {
                    swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        timer: 1000
                    }).then(() => {
                        $('#customer-personal-info-form')[0].reset();
                        $('#merchant_id').html(initialMerchantsHtml).val('').trigger('change.select2');
                        $('#product_id').html('<option value="">' + selectProductText + '</option>').val('').trigger('change.select2');
                        if ($.fn.select2) { $('.select2').trigger('change.select2'); }
                        location.reload();
                    });
                } else {
                    swal({ title: "Error", icon: "error", text: response.msg });
                }
            },
            error: function () {
                swal({ title: "Error", icon: "error", text: "Something went wrong!" });
            },
            complete: function () {
                $('#customer-personal-info-form button[type="submit"]').prop('disabled', false).text('Submit Ticket');
            }
        });
    });

    function showError(field, message) {
        var $field = $(field);
        $field.addClass('is-invalid');
        var $target = $field;
        if ($field.next('.select2-container').length > 0) {
            $target = $field.next('.select2-container');
        }
        $target.after('<small class="text-danger validation-error" style="display: block; margin-top: 4px;">' + message + '</small>');
    }
});
</script>