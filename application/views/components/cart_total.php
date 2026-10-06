<?php

$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol          = $this->session->userdata('currency_symbol');
$default_currency_flag   = $this->session->userdata('default_currency_flag');

if (
    isset($CartData) &&
    isset($CartData->cartItems) &&
    count($CartData->cartItems) > 0
) {

    $cartItems   = $CartData->cartItems;
    $cartDetails = $CartData->cartDetails;

    $MAX_CART_WEIGHT = 60000;

    $totalCartWeight = 0;

    foreach ($cartItems as $item) {

        $itemWeight = (float)($item->weight ?? 0);
        $itemQty    = (int)($item->qty_ordered ?? 0);

        $totalCartWeight += ($itemWeight * $itemQty);
    }

    $totalCartWeightKg = $totalCartWeight / 1000;

    if ($totalCartWeight > 40000) {

        $crateShipping = 900;

    } elseif ($totalCartWeight > 20000) {

        $crateShipping = 600;

    } elseif ($totalCartWeight > 0) {

        $crateShipping = 300;

    } else {

        $crateShipping = 0;
    }

    $additionalShipping =
        (float)($cartDetails->shipping_amount ?? 0);

    $effective_shipping =
        $crateShipping + $additionalShipping;


    $cartOverWeight =
        ($totalCartWeight > $MAX_CART_WEIGHT);

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<ul>

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


    <?php if ($cartOverWeight) { ?>

        <!-- <li class="cart-weight-warning-row">

            <p class="cart-weight-error">

                <?php

                echo $this->lang->line(
                    'maximum_cart_weight_message'
                );

                ?>

            </p>

        </li> -->

    <?php } ?>


    <!-- ============================================================
         SHIPPING / CRATE COST
         
         IMPORTANT:
         Shipping is shown even when cart is >60 KG.
         ============================================================ -->

    <?php if ($effective_shipping > 0) { ?>

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
            style="background-color:#ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php echo $this->lang->line(
                    'discount_code_label'
                ); ?>

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
                        value="<?php
                        echo !empty($cartDetails->coupon_code)
                            ? $cartDetails->coupon_code
                            : '';
                        ?>"
                        <?php
                        echo !empty($cartDetails->coupon_code)
                            ? 'readonly'
                            : '';
                        ?>
                        placeholder="<?= lang(
                            'enter_discount_code'
                        ) ?>"
                    >


                    <?php if (!empty($cartDetails->coupon_code)) { ?>

                        <input
                            type="button"
                            name="apply_coupon"
                            onclick="removeDiscount(
                                '<?php echo $cartDetails->coupon_code ?>',
                                0
                            );"
                            value="<?php echo $this->lang->line(
                                'remove_label'
                            ); ?>"
                            class="btn btn-primary btn-sm"
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_coupon"
                            value="<?php echo $this->lang->line(
                                'apply_label'
                            ); ?>"
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
        style="background-color:#ECEBEB;"
    >

        <em style="margin-bottom:5px;">

            <?php echo $this->lang->line(
                'gift_card_label'
            ); ?>

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
                    value="<?php echo $this->session->userdata(
                        'sis_session_id'
                    ); ?>"
                >


                <div class="d-flex">

                    <input
                        id="giftcard_code"
                        class="form-control me-2"
                        name="giftcard_code"
                        type="text"
                        placeholder="<?= lang(
                            'enter_gift_card_code'
                        ) ?>"
                    >


                    <button
                        type="button"
                        id="applyGiftCardBtn"
                        class="btn btn-primary btn-sm"
                    >
                        Apply
                    </button>

                </div>


                <!-- ====================================================
                     APPLIED GIFT CARDS
                     ==================================================== -->

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

                            if ($code == '') {
                                continue;
                            }

                    ?>

                        <div
                            class="applied-giftcard badge bg-light text-dark p-2 me-1 mb-1"
                            data-code="<?php echo htmlspecialchars(
                                $code,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >

                            <?php echo htmlspecialchars(
                                $code,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>


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
            style="background-color:#ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php echo $this->lang->line(
                    'voucher_code_label'
                ); ?>

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
                        value="<?php

                        echo !empty(
                            $cartDetails->voucher_code
                        )

                        ? $cartDetails->voucher_code
                        : '';

                        ?>"
                        <?php

                        echo !empty(
                            $cartDetails->voucher_code
                        )

                        ? 'readonly'
                        : '';

                        ?>
                    >


                    <?php if (!empty($cartDetails->voucher_code)) { ?>

                        <input
                            type="button"
                            name="apply_voucher"
                            onclick="removeDiscount(
                                '<?php echo $cartDetails->voucher_code ?>',
                                1
                            );"
                            value="<?php echo $this->lang->line(
                                'remove_label'
                            ); ?>"
                            class="btn btn-primary"
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_voucher"
                            value="<?php echo $this->lang->line(
                                'apply_label'
                            ); ?>"
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

                    <?php echo $this->lang->line(
                        'remove_gift_card_modal_title'
                    ); ?>

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="<?php echo $this->lang->line(
                        'close_label'
                    ); ?>"
                ></button>

            </div>


            <div class="modal-body">

                <?php echo $this->lang->line(
                    'remove_gift_card_modal_body'
                ); ?>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >

                    <?php echo $this->lang->line(
                        'cancel_label'
                    ); ?>

                </button>


                <button
                    type="button"
                    class="btn btn-danger"
                    id="confirmRemove"
                >

                    <?php echo $this->lang->line(
                        'remove_label'
                    ); ?>

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
    margin: 5px 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: #d9534f !important;
    font-size: 13px;
    line-height: 18px;
    font-weight: 600;
}

</style>



<!-- ================================================================
     JAVASCRIPT
     ================================================================ -->

<script>

$(document).ready(function () {


    /*
     * ============================================================
     * APPLY GIFT CARD
     * ============================================================
     */

    $('#applyGiftCardBtn').on('click', function (e) {

        e.preventDefault();


        const gift_code =
            $('#giftcard_code').val().trim();


        const session_id =
            $('input[name="session_id"]').val();


        if (gift_code === '') {

            showGiftcardAlert(
                'Please enter a gift card code',
                'danger'
            );

            return;
        }


        /*
         * Get current total.
         */

        let totalText =
            $('.shopping-total-price strong.price')
                .text()
                .replace(/[^\d.-]/g, '');


        let totalAmount =
            parseFloat(totalText);


        if (
            !isNaN(totalAmount) &&
            totalAmount <= 0
        ) {

            showGiftcardAlert(
                'You have reached total amount 0. You cannot apply more gift cards for this order.',
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


            beforeSend: function () {

                $('#applyGiftCardBtn')
                    .prop('disabled', true)
                    .text('Applying...');

            },


            success: function (res) {

                if (res.status === 'success') {

                    showGiftcardAlert(
                        res.message,
                        'success'
                    );


                    $('#giftcard_code').val('');


                    $('#applied-giftcards').empty();


                    if (
                        Array.isArray(
                            res.applied_codes
                        )
                    ) {

                        res.applied_codes.forEach(
                            function (code) {

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


                    setTimeout(function () {

                        location.reload();

                    }, 1000);


                } else {

                    showGiftcardAlert(
                        res.message,
                        'danger'
                    );

                }

            },


            complete: function () {

                $('#applyGiftCardBtn')
                    .prop('disabled', false)
                    .text('Apply');

            },


            error: function () {

                showGiftcardAlert(
                    'Error applying gift card',
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
        function () {

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


                success: function (res) {

                    if (res.status === 'success') {

                        showGiftcardAlert(
                            res.message,
                            'success'
                        );


                        $(
                            '.applied-giftcard[data-code="' +
                            gift_code +
                            '"]'
                        ).remove();


                        setTimeout(function () {

                            location.reload();

                        }, 800);


                    } else {

                        showGiftcardAlert(
                            res.message,
                            'danger'
                        );

                    }

                },


                error: function () {

                    showGiftcardAlert(
                        'Error removing gift card',
                        'danger'
                    );

                }

            });

        }
    );

});



/*
 * ============================================================
 * GIFT CARD ALERT
 * ============================================================
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
                aria-label="Close"
            ></button>

        </div>

    `);

}



/*
 * ============================================================
 * CART WEIGHT VALIDATION
 *
 * Product weight = GRAMS
 *
 * Maximum = 60 KG
 * 60 KG = 60,000 GRAMS
 * ============================================================
 */

const MAX_CART_WEIGHT = 60000;



/*
 * ============================================================
 * GET CURRENT CART WEIGHT
 * ============================================================
 */

function getCartTotalWeight() {

    let totalWeight = 0;


    $('input[id^="quantity_"]').each(
        function () {

            const qty =
                parseInt(
                    $(this).val(),
                    10
                ) || 0;


            const weight =
                parseFloat(
                    $(this).attr('data-weight')
                ) || 0;


            const itemTotalWeight =
                qty * weight;


            console.log(
                'Item:',
                $(this).attr('id'),
                'Qty:',
                qty,
                'Weight:',
                weight,
                'Total:',
                itemTotalWeight
            );


            totalWeight +=
                itemTotalWeight;

        }
    );


    console.log(
        'TOTAL WEIGHT:',
        totalWeight,
        'grams'
    );


    console.log(
        'TOTAL WEIGHT:',
        totalWeight / 1000,
        'KG'
    );


    return totalWeight;
}



/*
 * ============================================================
 * VALIDATE BEFORE QUANTITY INCREASE
 * ============================================================
 */

function validateCartWeightBeforeIncrease(
    itemId
) {

    const qtyInput =
        $('#quantity_' + itemId);


    /*
     * If quantity input does not exist,
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
     * New weight after adding 1 quantity.
     */

    const newTotalWeight =
        currentTotalWeight + itemWeight;


    console.log(
        'Current:',
        currentTotalWeight / 1000,
        'KG'
    );


    console.log(
        'Adding:',
        itemWeight / 1000,
        'KG'
    );


    console.log(
        'New:',
        newTotalWeight / 1000,
        'KG'
    );


    /*
     * ========================================================
     * MAXIMUM 60 KG
     * ========================================================
     */

    if (
        newTotalWeight >
        MAX_CART_WEIGHT
    ) {

        swal({

            title:
                "<?php echo $this->lang->line(
                    'maximum_cart_weight'
                ); ?>",

            text:
                "<?php echo $this->lang->line(
                    'maximum_cart_weight_message'
                ); ?>",

            type:
                "warning",

            confirmButtonText:
                "<?php echo $this->lang->line(
                    'ok'
                ); ?>"

        });


        /*
         * Do NOT increase quantity.
         */

        return false;
    }


    /*
     * ========================================================
     * VALID
     * ========================================================
     */

    return true;
}



/*
 * ============================================================
 * CHECK EXISTING CART ON PAGE LOAD
 * ============================================================
 */

$(document).ready(function () {

    const totalCartWeight =
        getCartTotalWeight();


    console.log(
        'Cart weight:',
        totalCartWeight / 1000,
        'KG'
    );


    /*
     * Show warning if existing cart
     * is already above 60 KG.
     */

    if (
        totalCartWeight >
        MAX_CART_WEIGHT
    ) {

        swal({

            title:
                "<?php echo $this->lang->line(
                    'maximum_cart_weight'
                ); ?>",

            text:
                "<?php echo $this->lang->line(
                    'maximum_cart_weight_message'
                ); ?>",

            type:
                "warning",

            confirmButtonText:
                "<?php echo $this->lang->line(
                    'ok'
                ); ?>"

        });

    }

});

</script>

<?php
}
?>
