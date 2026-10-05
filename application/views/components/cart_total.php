<?php
$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');

if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) {
    $cartItems = $CartData->cartItems;
    $cartDetails = $CartData->cartDetails;
?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <ul>
        <li>
            <em><?php echo $this->lang->line('sub_total'); ?></em>
            <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . ' '. number_format($cartDetails->base_subtotal, 2)); ?></strong>
        </li>

        <li>
            <em><?php echo $this->lang->line('taxes'); ?></em>
            <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->tax_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . ' '. number_format($cartDetails->tax_amount, 2)); ?></strong>
        </li>

        <?php if (!empty($cartDetails->coupon_code)) { ?>
            <li>
                <em><?php echo $this->lang->line('discount_label'); ?></em>
                <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_discount_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . ' '. number_format($cartDetails->base_discount_amount, 2)); ?></strong>
            </li>
        <?php } ?>

        <?php if (!empty($cartDetails->voucher_code)) { ?>
            <li>
                <em><?php echo $this->lang->line('gift_card_amount'); ?></em>
                <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->voucher_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . ' '. number_format($cartDetails->voucher_amount, 2)); ?></strong>
            </li>
        <?php } ?>

       <?php
$effective_shipping = (float)($cartDetails->ym_charge ?? 0) + (float)($cartDetails->shipping_amount ?? 0);
?>

<?php if ($effective_shipping > 0): ?>
    <li>
        <em><?php echo $this->lang->line('shipping_cost'); ?></em>
        <strong class="price">
            <?php
            echo (
                $this->session->userdata('currency_code_session') && $default_currency_flag != 1
                ? convert_currency_website($effective_shipping, $currency_conversion_rate, $currency_symbol)
                : CURRENCY_TYPE . ' ' . number_format($effective_shipping, 2)
            );
            ?>
        </strong>
    </li>
<?php endif; ?>

        <li class="shopping-total-price">
            <em><?php echo $this->lang->line('total_label'); ?></em>
            <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->grand_total, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . ' '. number_format($cartDetails->grand_total, 2)); ?></strong>
        </li>

        <?php if (empty($cartDetails->voucher_code)) { ?>
            <li class="dvcode" id="li-discount-code" style="background-color: #ECEBEB;">
                <em style="margin-bottom:5px;"><?php echo $this->lang->line('discount_code_label'); ?></em>
                <div class="form-group">
                    <form id="form-coupon" class="checkout_coupon" method="POST">
                        <input type="hidden" name="coupon_type" value="0">
                        <input id="coupon_code" class="form-control" name="coupon_code" type="text"
                            value="<?php echo !empty($cartDetails->coupon_code) ? $cartDetails->coupon_code : ''; ?>"
                            <?php echo !empty($cartDetails->coupon_code) ? 'readonly' : ''; ?>  placeholder="<?= lang('enter_discount_code') ?>">
                        <?php if (!empty($cartDetails->coupon_code)) { ?>
                            <input type="button" name="apply_coupon" onclick="removeDiscount('<?php echo $cartDetails->coupon_code ?>',0);" value="<?php echo $this->lang->line('remove_label'); ?>" class="btn btn-primary btn-sm">
                        <?php } else { ?>
                            <input type="submit" name="apply_coupon" value="<?php echo $this->lang->line('apply_label'); ?>" class="btn btn-primary btn-sm">
                        <?php } ?>
                        <div id="coupon-message" class="coupon_code_message"></div>
                    </form>
                </div>
            </li>
        <?php } ?>

        <li class="dvcode" id="li-giftcard-code" style="background-color: #ECEBEB;">
            <em style="margin-bottom:5px;"><?php echo $this->lang->line('gift_card_label'); ?></em>
            <div class="form-group">
                <form id="form-giftcard" class="checkout_giftcard" method="POST" onsubmit="return false;">
                    <input type="hidden" name="session_id" value="<?php echo $this->session->userdata('sis_session_id'); ?>">
                    <div class="d-flex">
                        <input id="giftcard_code" class="form-control me-2" name="giftcard_code" type="text"  placeholder="<?= lang('enter_gift_card_code') ?>">
                        <button type="button" id="applyGiftCardBtn" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                    <div id="applied-giftcards" class="mt-2">
                        <?php
                        if (!empty($cartDetails->voucher_code)) {
                            $codes = explode(',', $cartDetails->voucher_code);
                            foreach ($codes as $code) {
                                echo "<div class='applied-giftcard badge bg-light text-dark p-2 me-1 mb-1' data-code='{$code}'>
                                {$code} <span class='remove-giftcard text-danger ms-1' style='cursor:pointer;'>&times;</span>
                              </div>";
                            }
                        }
                        ?>
                    </div>
                    <div id="giftcard-message" class="giftcard_code_message mt-2"></div>
                </form>
            </div>
        </li>

        <?php if (empty($cartDetails->coupon_code)) { ?>
            <li class="dvcode" id="li-voucher-code" style="background-color: #ECEBEB;">
                <em style="margin-bottom:5px;"><?php echo $this->lang->line('voucher_code_label'); ?></em>
                <strong class="price">
                    <form id="form-voucher" class="checkout_voucher" method="POST">
                        <input type="hidden" name="coupon_type" value="1">
                        <input id="voucher_code" class="form-control" name="coupon_code" type="text"
                            value="<?php echo !empty($cartDetails->voucher_code) ? $cartDetails->voucher_code : ''; ?>"
                            <?php echo !empty($cartDetails->voucher_code) ? 'readonly' : ''; ?>>
                        <?php if (!empty($cartDetails->voucher_code)) { ?>
                            <input type="button" name="apply_voucher" onclick="removeDiscount('<?php echo $cartDetails->voucher_code ?>',1);" value="<?php echo $this->lang->line('remove_label'); ?>" class="btn btn-primary">
                        <?php } else { ?>
                            <input type="submit" name="apply_voucher" value="<?php echo $this->lang->line('apply_label'); ?>" class="btn btn-primary">
                        <?php } ?>
                        <div id="voucher-message" class="voucher_code_message"></div>
                    </form>
                </strong>
            </li>
        <?php } ?>
    </ul>
<?php } ?>
<div class="modal fade" id="giftcardRemoveModal" tabindex="-1" aria-labelledby="removeGiftcardLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeGiftcardLabel"><?php echo $this->lang->line('remove_gift_card_modal_title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo $this->lang->line('close_label'); ?>"></button>
            </div>
            <div class="modal-body">
                <?php echo $this->lang->line('remove_gift_card_modal_body'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $this->lang->line('cancel_label'); ?></button>
                <button type="button" class="btn btn-danger" id="confirmRemove"><?php echo $this->lang->line('remove_label'); ?></button>
            </div>
        </div>
    </div>
</div>




<script>
    $(document).ready(function() {

        // === Apply Gift Card ===
        $('#applyGiftCardBtn').on('click', function(e) {
            e.preventDefault();
            const gift_code = $('#giftcard_code').val().trim();
            const session_id = $('input[name="session_id"]').val();

            if (gift_code === '') {
                showGiftcardAlert('Please enter a gift card code', 'danger');
                return;
            }

            // ✅ Check if total amount is already 0
            let totalText = $('.shopping-total-price strong.price').text().replace(/[^\d.-]/g, '');
            let totalAmount = parseFloat(totalText);

            if (!isNaN(totalAmount) && totalAmount <= 0) {
                showGiftcardAlert('You have reached total amount 0. You cannot apply more gift cards for this order.', 'warning');
                return;
            }

            $.ajax({
                url: BASE_URL + "cart/applyGiftCard",
                type: "POST",
                dataType: "json",
                data: {
                    gift_code,
                    session_id
                },
                beforeSend: function() {
                    $('#applyGiftCardBtn').prop('disabled', true).text('Applying...');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        showGiftcardAlert(res.message, 'success');
                        $('#giftcard_code').val('');

                        // Update UI - show applied giftcards
                        $('#applied-giftcards').empty();
                        res.applied_codes.forEach(code => {
                            $('#applied-giftcards').append(`
                        <div class="applied-giftcard badge bg-light text-dark p-2 me-1 mb-1" data-code="${code}">
                            ${code} <span class="remove-giftcard text-danger ms-1" style="cursor:pointer;">&times;</span>
                        </div>
                    `);
                        });

                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showGiftcardAlert(res.message, 'danger');
                    }
                },
                complete: function() {
                    $('#applyGiftCardBtn').prop('disabled', false).text('Apply');
                },
                error: function() {
                    showGiftcardAlert('Error applying gift card', 'danger');
                }
            });
        });


        // === Remove Gift Card ===
        $(document).on('click', '.remove-giftcard', function() {
            const gift_code = $(this).closest('.applied-giftcard').data('code');
            const session_id = $('input[name="session_id"]').val();

            $.ajax({
                url: BASE_URL + "cart/removeGiftCard",
                type: "POST",
                dataType: "json",
                data: {
                    gift_code,
                    session_id
                },
                success: function(res) {
                    if (res.status === 'success') {
                        showGiftcardAlert(res.message, 'success');
                        $(`.applied-giftcard[data-code="${gift_code}"]`).remove();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showGiftcardAlert(res.message, 'danger');
                    }
                },
                error: function() {
                    showGiftcardAlert('Error removing gift card', 'danger');
                }
            });
        });

    });

    // Alert helper
    function showGiftcardAlert(message, type = 'success') {
        $('#giftcard-message').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
    }
   const MAX_CART_WEIGHT = 60; // Maximum 60 KG

function getCartTotalWeight() {
    let totalWeight = 0;

    $('input[id^="quantity_"]').each(function () {
        const qty = parseFloat($(this).val()) || 0;
        const weight = parseFloat($(this).attr('data-weight')) || 0;

        totalWeight += qty * weight;
    });

    return totalWeight;
}

function validateCartWeightBeforeIncrease(itemId) {

    const qtyInput = $('#quantity_' + itemId);

    if (!qtyInput.length) {
        return true;
    }

    const itemWeight = parseFloat(qtyInput.attr('data-weight')) || 0;
    const currentTotalWeight = getCartTotalWeight();
    const newTotalWeight = currentTotalWeight + itemWeight;

    console.log('Current cart weight:', currentTotalWeight);
    console.log('Item weight:', itemWeight);
    console.log('New cart weight:', newTotalWeight);

    if (newTotalWeight > MAX_CART_WEIGHT) {

        $('#qtyError_' + itemId).html(
            '<span style="color:#d9534f;">' +
            'Maximum cart weight is 60 KG. You cannot add more.' +
            '</span>'
        );

        return false;
    }

    $('#qtyError_' + itemId).html('');

    return true;
}
</script>