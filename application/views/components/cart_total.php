<?php

$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');

if (
    isset($CartData) &&
    isset($CartData->cartItems) &&
    count($CartData->cartItems) > 0
) {

    $cartItems   = $CartData->cartItems;
    $cartDetails = $CartData->cartDetails;


    /*
     * ============================================================
     * CART WEIGHT CALCULATION
     *
     * Product weight is stored in GRAMS.
     * Maximum allowed cart weight = 60 KG = 60,000 grams.
     * ============================================================
     */

    $MAX_CART_WEIGHT = 60000;

    $totalCartWeight = 0;

    foreach ($cartItems as $item) {

        $itemWeight = (float)($item->weight ?? 0);
        $itemQty    = (int)($item->qty_ordered ?? 0);

        $totalCartWeight += ($itemWeight * $itemQty);
    }

    $totalCartWeightKg = $totalCartWeight / 1000;


    /*
     * ============================================================
     * CART OVERWEIGHT
     * ============================================================
     */

    $cartOverWeight = ($totalCartWeight > $MAX_CART_WEIGHT);


    /*
     * ============================================================
     * SHIPPING / CRATE CHARGE
     *
     * IMPORTANT:
     *
     * Do NOT calculate 300 / 600 / 900 here.
     *
     * Backend should calculate:
     *
     * 0 - 20 KG       = 300
     * >20 - 40 KG     = 600
     * >40 - 60 KG     = 900
     * >60 KG         = 0 / invalid
     *
     * This view only displays backend values.
     * ============================================================
     */

    $effective_shipping =
        (float)($cartDetails->ym_charge ?? 0) +
        (float)($cartDetails->shipping_amount ?? 0);


    /*
     * ============================================================
     * NEVER SHOW SHIPPING ABOVE 60 KG
     * ============================================================
     */

    if ($cartOverWeight) {

        $effective_shipping = 0;

    }

?>

<!-- ================================================================
     JQUERY
     ================================================================ -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- ================================================================
     CART SUMMARY
     ================================================================ -->

<ul>


    <!-- ============================================================
         SUB TOTAL
         ============================================================ -->

    <li>

        <em>
            <?php echo $this->lang->line('sub_total'); ?>
        </em>

        <strong class="price">

            <?php

            echo (
                $this->session->userdata('currency_code_session')
                && $default_currency_flag != 1
            )

            ? convert_currency_website(
                $cartDetails->base_subtotal,
                $currency_conversion_rate,
                $currency_symbol
            )

            : CURRENCY_TYPE . ' ' . number_format(
                $cartDetails->base_subtotal,
                2
            );

            ?>

        </strong>

    </li>


    <!-- ============================================================
         TAX
         ============================================================ -->

    <li>

        <em>
            <?php echo $this->lang->line('taxes'); ?>
        </em>

        <strong class="price">

            <?php

            echo (
                $this->session->userdata('currency_code_session')
                && $default_currency_flag != 1
            )

            ? convert_currency_website(
                $cartDetails->tax_amount,
                $currency_conversion_rate,
                $currency_symbol
            )

            : CURRENCY_TYPE . ' ' . number_format(
                $cartDetails->tax_amount,
                2
            );

            ?>

        </strong>

    </li>


    <!-- ============================================================
         COUPON DISCOUNT
         ============================================================ -->

    <?php if (!empty($cartDetails->coupon_code)) { ?>

        <li>

            <em>
                <?php echo $this->lang->line('discount_label'); ?>
            </em>

            <strong class="price">

                <?php

                echo (
                    $this->session->userdata('currency_code_session')
                    && $default_currency_flag != 1
                )

                ? convert_currency_website(
                    $cartDetails->base_discount_amount,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                : CURRENCY_TYPE . ' ' . number_format(
                    $cartDetails->base_discount_amount,
                    2
                );

                ?>

            </strong>

        </li>

    <?php } ?>


    <!-- ============================================================
         GIFT CARD
         ============================================================ -->

    <?php if (!empty($cartDetails->voucher_code)) { ?>

        <li>

            <em>
                <?php echo $this->lang->line('gift_card_amount'); ?>
            </em>

            <strong class="price">

                <?php

                echo (
                    $this->session->userdata('currency_code_session')
                    && $default_currency_flag != 1
                )

                ? convert_currency_website(
                    $cartDetails->voucher_amount,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                : CURRENCY_TYPE . ' ' . number_format(
                    $cartDetails->voucher_amount,
                    2
                );

                ?>

            </strong>

        </li>

    <?php } ?>


    <!-- ============================================================
         SHIPPING / CRATE COST
         
         IMPORTANT:
         
         - <= 60 KG and shipping > 0 => show
         - > 60 KG => completely hide
         - shipping = 0 => completely hide
         ============================================================ -->

    <?php if (!$cartOverWeight && $effective_shipping > 0) { ?>

        <li>

            <em>
                <?php echo $this->lang->line('shipping_cost'); ?>
            </em>

            <strong class="price">

                <?php

                echo (
                    $this->session->userdata('currency_code_session')
                    && $default_currency_flag != 1
                )

                ? convert_currency_website(
                    $effective_shipping,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                : CURRENCY_TYPE . ' ' . number_format(
                    $effective_shipping,
                    2
                );

                ?>

            </strong>

        </li>

    <?php } ?>


    <!-- ============================================================
         TOTAL
         ============================================================ -->

    <li class="shopping-total-price">

        <em>
            <?php echo $this->lang->line('total_label'); ?>
        </em>

        <strong class="price">

            <?php

            echo (
                $this->session->userdata('currency_code_session')
                && $default_currency_flag != 1
            )

            ? convert_currency_website(
                $cartDetails->grand_total,
                $currency_conversion_rate,
                $currency_symbol
            )

            : CURRENCY_TYPE . ' ' . number_format(
                $cartDetails->grand_total,
                2
            );

            ?>

        </strong>

    </li>


    <!-- ============================================================
         DISCOUNT CODE
         ============================================================ -->

    <?php if (empty($cartDetails->voucher_code)) { ?>

        <li
            class="dvcode"
            id="li-discount-code"
            style="background-color: #ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php echo $this->lang->line('discount_code_label'); ?>

            </em>

            <div class="form-group">

                <form
                    id="form-coupon"
                    class="checkout_coupon"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="coupon_type"
                        value="0"
                    >

                    <input
                        id="coupon_code"
                        class="form-control"
                        name="coupon_code"
                        type="text"
                        value="<?php echo !empty($cartDetails->coupon_code) ? $cartDetails->coupon_code : ''; ?>"
                        <?php echo !empty($cartDetails->coupon_code) ? 'readonly' : ''; ?>
                        placeholder="<?php echo $this->lang->line('enter_discount_code'); ?>"
                    >

                    <?php if (!empty($cartDetails->coupon_code)) { ?>

                        <input
                            type="button"
                            name="apply_coupon"
                            onclick="removeDiscount('<?php echo $cartDetails->coupon_code; ?>',0);"
                            value="<?php echo $this->lang->line('remove_label'); ?>"
                            class="btn btn-primary btn-sm"
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_coupon"
                            value="<?php echo $this->lang->line('apply_label'); ?>"
                            class="btn btn-primary btn-sm"
                        >

                    <?php } ?>

                    <div
                        id="coupon-message"
                        class="coupon_code_message"
                    ></div>

                </form>

            </div>

        </li>

    <?php } ?>


    <!-- ============================================================
         GIFT CARD
         ============================================================ -->

    <li
        class="dvcode"
        id="li-giftcard-code"
        style="background-color: #ECEBEB;"
    >

        <em style="margin-bottom:5px;">

            <?php echo $this->lang->line('gift_card_label'); ?>

        </em>

        <div class="form-group">

            <form
                id="form-giftcard"
                class="checkout_giftcard"
                method="POST"
                onsubmit="return false;"
            >

                <input
                    type="hidden"
                    name="session_id"
                    value="<?php echo $this->session->userdata('sis_session_id'); ?>"
                >

                <div class="d-flex">

                    <input
                        id="giftcard_code"
                        class="form-control me-2"
                        name="giftcard_code"
                        type="text"
                        placeholder="<?php echo $this->lang->line('enter_gift_card_code'); ?>"
                    >

                    <button
                        type="button"
                        id="applyGiftCardBtn"
                        class="btn btn-primary btn-sm"
                    >
                        <?php echo $this->lang->line('apply_label'); ?>
                    </button>

                </div>


                <!-- ==================================================
                     APPLIED GIFTCARDS
                     ================================================== -->

                <div
                    id="applied-giftcards"
                    class="mt-2"
                >

                    <?php

                    if (!empty($cartDetails->voucher_code)) {

                        $codes = explode(
                            ',',
                            $cartDetails->voucher_code
                        );

                        foreach ($codes as $code) {

                            $code = trim($code);

                    ?>

                        <div
                            class="applied-giftcard badge bg-light text-dark p-2 me-1 mb-1"
                            data-code="<?php echo htmlspecialchars($code); ?>"
                        >

                            <?php echo htmlspecialchars($code); ?>

                            <span
                                class="remove-giftcard text-danger ms-1"
                                style="cursor:pointer;"
                            >
                                &times;
                            </span>

                        </div>

                    <?php

                        }
                    }

                    ?>

                </div>


                <!-- ==================================================
                     GIFT CARD MESSAGE
                     ================================================== -->

                <div
                    id="giftcard-message"
                    class="giftcard_code_message mt-2"
                ></div>

            </form>

        </div>

    </li>


    <!-- ============================================================
         VOUCHER
         ============================================================ -->

    <?php if (empty($cartDetails->coupon_code)) { ?>

        <li
            class="dvcode"
            id="li-voucher-code"
            style="background-color: #ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php echo $this->lang->line('voucher_code_label'); ?>

            </em>

            <strong class="price">

                <form
                    id="form-voucher"
                    class="checkout_voucher"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="coupon_type"
                        value="1"
                    >

                    <input
                        id="voucher_code"
                        class="form-control"
                        name="coupon_code"
                        type="text"
                        value="<?php echo !empty($cartDetails->voucher_code) ? $cartDetails->voucher_code : ''; ?>"
                        <?php echo !empty($cartDetails->voucher_code) ? 'readonly' : ''; ?>
                    >

                    <?php if (!empty($cartDetails->voucher_code)) { ?>

                        <input
                            type="button"
                            name="apply_voucher"
                            onclick="removeDiscount('<?php echo $cartDetails->voucher_code; ?>',1);"
                            value="<?php echo $this->lang->line('remove_label'); ?>"
                            class="btn btn-primary"
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_voucher"
                            value="<?php echo $this->lang->line('apply_label'); ?>"
                            class="btn btn-primary"
                        >

                    <?php } ?>

                    <div
                        id="voucher-message"
                        class="voucher_code_message"
                    ></div>

                </form>

            </strong>

        </li>

    <?php } ?>

</ul>


<!-- ================================================================
     GIFT CARD REMOVE MODAL
     ================================================================ -->

<div
    class="modal fade"
    id="giftcardRemoveModal"
    tabindex="-1"
    aria-labelledby="removeGiftcardLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="removeGiftcardLabel"
                >

                    <?php echo $this->lang->line('remove_gift_card_modal_title'); ?>

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="<?php echo $this->lang->line('close_label'); ?>"
                ></button>

            </div>

            <div class="modal-body">

                <?php echo $this->lang->line('remove_gift_card_modal_body'); ?>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >

                    <?php echo $this->lang->line('cancel_label'); ?>

                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="confirmRemove"
                >

                    <?php echo $this->lang->line('remove_label'); ?>

                </button>

            </div>

        </div>

    </div>

</div>


<!-- ================================================================
     CSS
     ================================================================ -->

<style>

.cart-weight-warning-row {
    background: transparent !important;
}

.cart-weight-error {
    display: block !important;

    margin: 5px 0 0 !important;
    padding: 0 !important;

    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;

    color: #d9534f !important;

    font-size: 13px;
    line-height: 18px;
    font-weight: 600;

    width: 100%;
}

</style>


<!-- ================================================================
     JAVASCRIPT
     ================================================================ -->

<script>


/*
 * ================================================================
 * GIFT CARD
 * ================================================================
 */

$(document).ready(function() {


    /*
     * ============================================================
     * APPLY GIFT CARD
     * ============================================================
     */

    $('#applyGiftCardBtn').on('click', function(e) {

        e.preventDefault();


        const gift_code =
            $('#giftcard_code').val().trim();


        const session_id =
            $('input[name="session_id"]').val();


        if (gift_code === '') {

            showGiftcardAlert(
                "<?php echo $this->lang->line('enter_gift_card_code'); ?>",
                'danger'
            );

            return;
        }


        let totalText =
            $('.shopping-total-price strong.price')
                .text()
                .replace(/[^\d.-]/g, '');


        let totalAmount =
            parseFloat(totalText);


        if (!isNaN(totalAmount) && totalAmount <= 0) {

            showGiftcardAlert(
                "<?php echo $this->lang->line('gift_card_total_zero_message'); ?>",
                'warning'
            );

            return;
        }


        $.ajax({

            url: BASE_URL + "cart/applyGiftCard",

            type: "POST",

            dataType: "json",

            data: {
                gift_code: gift_code,
                session_id: session_id
            },


            beforeSend: function() {

                $('#applyGiftCardBtn')
                    .prop('disabled', true)
                    .text(
                        "<?php echo $this->lang->line('applying_label'); ?>"
                    );

            },


            success: function(res) {


                if (res.status === 'success') {


                    showGiftcardAlert(
                        res.message,
                        'success'
                    );


                    $('#giftcard_code').val('');


                    $('#applied-giftcards').empty();


                    if (
                        Array.isArray(res.applied_codes)
                    ) {

                        res.applied_codes.forEach(
                            function(code) {

                                $('#applied-giftcards')
                                    .append(`

                                        <div
                                            class="applied-giftcard badge bg-light text-dark p-2 me-1 mb-1"
                                            data-code="${code}"
                                        >

                                            ${code}

                                            <span
                                                class="remove-giftcard text-danger ms-1"
                                                style="cursor:pointer;"
                                            >
                                                &times;
                                            </span>

                                        </div>

                                    `);

                            }
                        );

                    }


                    setTimeout(function() {

                        location.reload();

                    }, 1000);


                } else {


                    showGiftcardAlert(
                        res.message,
                        'danger'
                    );

                }

            },


            complete: function() {

                $('#applyGiftCardBtn')
                    .prop('disabled', false)
                    .text(
                        "<?php echo $this->lang->line('apply_label'); ?>"
                    );

            },


            error: function() {

                showGiftcardAlert(
                    "<?php echo $this->lang->line('gift_card_apply_error'); ?>",
                    'danger'
                );

            }

        });

    });


    /*
     * ============================================================
     * REMOVE GIFT CARD
     * ============================================================
     */

    $(document).on(
        'click',
        '.remove-giftcard',
        function()
    {


        const gift_code =
            $(this)
                .closest('.applied-giftcard')
                .data('code');


        const session_id =
            $('input[name="session_id"]').val();


        $.ajax({

            url: BASE_URL + "cart/removeGiftCard",

            type: "POST",

            dataType: "json",

            data: {
                gift_code: gift_code,
                session_id: session_id
            },


            success: function(res) {


                if (res.status === 'success') {


                    showGiftcardAlert(
                        res.message,
                        'success'
                    );


                    $(
                        `.applied-giftcard[data-code="${gift_code}"]`
                    ).remove();


                    setTimeout(function() {

                        location.reload();

                    }, 800);


                } else {


                    showGiftcardAlert(
                        res.message,
                        'danger'
                    );

                }

            },


            error: function() {

                showGiftcardAlert(
                    "<?php echo $this->lang->line('gift_card_remove_error'); ?>",
                    'danger'
                );

            }

        });

    });

});


/*
 * ================================================================
 * GIFT CARD ALERT
 * ================================================================
 */

function showGiftcardAlert(
    message,
    type = 'success'
) {


    $('#giftcard-message').html(`

        <div
            class="alert alert-${type} alert-dismissible fade show"
            role="alert"
        >

            ${message}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="<?php echo $this->lang->line('close_label'); ?>"
            ></button>

        </div>

    `);

}


/*
 * ================================================================
 * CART WEIGHT VALIDATION
 *
 * Product weight = GRAMS
 * Maximum cart weight = 60 KG
 * 60 KG = 60,000 GRAMS
 * ================================================================
 */

const MAX_CART_WEIGHT = 60000;


/*
 * ================================================================
 * GET CURRENT CART WEIGHT
 * ================================================================
 */

function getCartTotalWeight() {


    let totalWeight = 0;


    $('input[id^="quantity_"]').each(function() {


        const qty =
            parseInt($(this).val(), 10) || 0;


        const weight =
            parseFloat(
                $(this).attr('data-weight')
            ) || 0;


        totalWeight +=
            qty * weight;

    });


    return totalWeight;

}


/*
 * ================================================================
 * VALIDATE BEFORE + QUANTITY
 *
 * IMPORTANT:
 *
 * This checks the weight BEFORE calling
 * increaseQtyValue().
 *
 * If new weight > 60 KG:
 *
 * - quantity is NOT increased
 * - warning is shown under that product
 * - no SweetAlert popup
 * ================================================================
 */

function validateCartWeightBeforeIncrease(itemId) {


    const qtyInput =
        $('#quantity_' + itemId);


    /*
     * If quantity input doesn't exist,
     * allow normal processing.
     */

    if (!qtyInput.length) {

        return true;

    }


    /*
     * Product weight in grams.
     */

    const itemWeight =
        parseFloat(
            qtyInput.attr('data-weight')
        ) || 0;


    /*
     * Current cart weight.
     */

    const currentTotalWeight =
        getCartTotalWeight();


    /*
     * Weight after adding one quantity.

     */

    const newTotalWeight =
        currentTotalWeight + itemWeight;


    /*
     * Clear previous messages.
     */

    $('.qty-error').text('');


    /*
     * ============================================================
     * OVER 60 KG
     * ============================================================
     */

    if (newTotalWeight > MAX_CART_WEIGHT) {


        $('#qtyError_' + itemId).text(

            "<?php echo $this->lang->line('maximum_cart_weight_message'); ?>"

        );


        return false;

    }


    /*
     * ============================================================
     * 60 KG OR BELOW
     * ============================================================
     */

    return true;

}

</script>


<?php
}
?>