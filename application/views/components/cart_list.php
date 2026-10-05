<?php
// echo "<pre>";
// print_r($CartData);
// echo "</pre>";

/*
|--------------------------------------------------------------------------
| CART VALIDATION
|--------------------------------------------------------------------------
| YM Delivery Crates
|
| Small  = 20 KG = 20,000 grams = 300 MUR
| Medium = 40 KG = 40,000 grams = 600 MUR
| Large  = 60 KG = 60,000 grams = 900 MUR
|
| More than 60 KG = BLOCK CHECKOUT
|--------------------------------------------------------------------------
*/

$has_out_of_stock = false;
$total_cart_weight = 0;

/*
|--------------------------------------------------------------------------
| Maximum cart weight
|--------------------------------------------------------------------------
*/
$max_cart_weight = 60000;

/*
|--------------------------------------------------------------------------
| Calculate total cart weight
|--------------------------------------------------------------------------
*/
if (isset($CartData->cartItems) && !empty($CartData->cartItems)) {

    foreach ($CartData->cartItems as $chk) {

        /*
        |--------------------------------------------------------------------------
        | Out of stock
        |--------------------------------------------------------------------------
        */
        if (
            isset($chk->available_qty) &&
            (int)$chk->available_qty <= 0
        ) {
            $has_out_of_stock = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Product weight
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | $chk->weight must be stored in GRAMS.
        |
        */
        $product_weight = isset($chk->weight)
            ? (float)$chk->weight
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Product quantity
        |--------------------------------------------------------------------------
        */
        $product_qty = isset($chk->qty_ordered)
            ? (int)$chk->qty_ordered
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Total weight for this product
        |--------------------------------------------------------------------------
        */
        $total_cart_weight += ($product_weight * $product_qty);
    }
}

/*
|--------------------------------------------------------------------------
| Weight exceeded
|--------------------------------------------------------------------------
*/
$weight_exceeded = ($total_cart_weight > $max_cart_weight);

/*
|--------------------------------------------------------------------------
| Convert grams to KG
|--------------------------------------------------------------------------
*/
$total_cart_weight_kg = $total_cart_weight / 1000;


/*
|--------------------------------------------------------------------------
| Calculate YM crate shipping
|--------------------------------------------------------------------------
|
| 0 - 20 KG     = 300 MUR
| >20 - 40 KG   = 600 MUR
| >40 - 60 KG   = 900 MUR
| >60 KG        = BLOCK
|
|--------------------------------------------------------------------------
*/

$ym_delivery_charge = 0;

if ($total_cart_weight > 0 && $total_cart_weight <= 20000) {

    $ym_delivery_charge = 300;

} elseif ($total_cart_weight > 20000 && $total_cart_weight <= 40000) {

    $ym_delivery_charge = 600;

} elseif ($total_cart_weight > 40000 && $total_cart_weight <= 60000) {

    $ym_delivery_charge = 900;

} elseif ($total_cart_weight > 60000) {

    $ym_delivery_charge = 0;
}
?>

<div class="col-md-12 col-sm-12 <?php
    echo (
        isset($CartData->cartItems) &&
        count($CartData->cartItems) > 0
    ) ? '' : 'text-center';
?>">

    <h1>
        <?php echo $this->lang->line('shopping_cart'); ?>
    </h1>


    <!-- ==============================================================
         FLASH MESSAGE
    ============================================================== -->

    <?php if ($this->session->flashdata('error_message')): ?>

        <div
            class="alert alert-danger"
            style="margin-top:15px;"
        >
            <i class="fa fa-exclamation-circle"></i>

            <?php
            echo $this->session->flashdata('error_message');
            ?>
        </div>

    <?php endif; ?>


    <!-- ==============================================================
         OUT OF STOCK MESSAGE
    ============================================================== -->

    <?php if ($has_out_of_stock): ?>

        <div
            class="alert alert-danger"
            style="margin-top:15px;"
        >

            <i class="fa fa-exclamation-triangle"></i>

            <strong>Notice:</strong>

            One or more items in your cart are currently
            <strong>Out of Stock</strong>.

            Please remove them before proceeding to checkout.

        </div>

    <?php endif; ?>


    <!-- ==============================================================
         WEIGHT LIMIT MESSAGE
    ============================================================== -->

    <?php if ($weight_exceeded): ?>

        <div
            class="alert alert-danger"
            style="margin-top:15px;"
        >

            <i class="fa fa-exclamation-triangle"></i>

            <strong>Delivery weight limit exceeded.</strong>

            Your cart weighs

            <strong>
                <?php echo number_format($total_cart_weight_kg, 2); ?> KG
            </strong>.

            The maximum allowed weight per order is
            <strong>60 KG</strong>.

            Please remove some items before proceeding to checkout.

        </div>

    <?php endif; ?>


    <!-- ==============================================================
         CART
    ============================================================== -->

    <?php if (
        isset($CartData->cartItems) &&
        count($CartData->cartItems) > 0
    ) { ?>


        <div class="goods-page">

            <div class="row">


                <!-- ======================================================
                     CART ITEMS
                ======================================================= -->

                <div class="col-md-9 col-sm-8">

                    <div class="goods-data">

                        <div class="table-wrapper-responsive">

                            <table
                                summary="<?php
                                    echo $this->lang->line('shopping_cart');
                                ?>"
                            >

                                <tr>

                                    <th class="goods-page-image">
                                        <?php
                                        echo $this->lang->line('image');
                                        ?>
                                    </th>

                                    <th class="goods-page-description">
                                        <?php
                                        echo $this->lang->line('description');
                                        ?>
                                    </th>

                                    <th class="goods-page-quantity">
                                        <?php
                                        echo $this->lang->line('quantity');
                                        ?>
                                    </th>

                                    <th class="goods-page-price">
                                        <?php
                                        echo $this->lang->line('unit_price');
                                        ?>
                                    </th>

                                    <th
                                        class="goods-page-total"
                                        colspan="2"
                                    >
                                        <?php
                                        echo $this->lang->line('total');
                                        ?>
                                    </th>

                                </tr>


                                <?php foreach (
                                    $CartData->cartItems as $value
                                ) {


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Product image
                                    |--------------------------------------------------------------------------
                                    */

                                    $base_image = (
                                        isset($value->base_image) &&
                                        $value->base_image != ''
                                    )
                                    ? PRODUCT_THUMB_IMG . $value->base_image
                                    : PRODUCT_DEFAULT_IMG;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Product variants
                                    |--------------------------------------------------------------------------
                                    */

                                    $product_variants = (
                                        isset($value->product_variants) &&
                                        $value->product_variants != ''
                                    )
                                    ? json_decode($value->product_variants)
                                    : '';


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Bundle child IDs
                                    |--------------------------------------------------------------------------
                                    */

                                    $bundle_child_ids = '';

                                    if (
                                        isset($value->bundle_child_details) &&
                                        $value->bundle_child_details != ''
                                    ) {

                                        $bundle_child_details =
                                            json_decode(
                                                $value->bundle_child_details
                                            );

                                        if (
                                            is_array($bundle_child_details) ||
                                            is_object($bundle_child_details)
                                        ) {

                                            $ids = array_column(
                                                (array)$bundle_child_details,
                                                'bundle_child_product_id'
                                            );

                                            $bundle_child_ids =
                                                implode(',', $ids);
                                        }
                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Variant HTML
                                    |--------------------------------------------------------------------------
                                    */

                                    $variants = '';

                                    if (
                                        isset($product_variants) &&
                                        $product_variants != ''
                                    ) {

                                        foreach (
                                            $product_variants
                                            as $single_variant
                                        ) {

                                            foreach (
                                                $single_variant
                                                as $key => $val
                                            ) {

                                                $variants .=
                                                    '<p>' .
                                                    $key .
                                                    ' : ' .
                                                    $val .
                                                    '</p>';
                                            }
                                        }
                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Product weight
                                    |--------------------------------------------------------------------------
                                    */

                                    $item_weight = isset($value->weight)
                                        ? (float)$value->weight
                                        : 0;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Quantity
                                    |--------------------------------------------------------------------------
                                    */

                                    $item_qty = isset($value->qty_ordered)
                                        ? (int)$value->qty_ordered
                                        : 0;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Product total weight
                                    |--------------------------------------------------------------------------
                                    */

                                    $item_total_weight =
                                        $item_weight * $item_qty;

                                ?>

                                    <tr>

                                        <!-- ITEM ID -->

                                        <input
                                            type="hidden"
                                            name="item_id[]"
                                            value="<?php
                                                echo $value->item_id;
                                            ?>"
                                        >


                                        <!-- BUNDLE CHILD IDS -->

                                        <input
                                            type="hidden"
                                            name="bundle_child_ids[]"
                                            id="bundle_child_ids_<?php
                                                echo $value->item_id;
                                            ?>"
                                            value="<?php
                                                echo $bundle_child_ids;
                                            ?>"
                                        >


                                        <!-- ==================================================
                                             IMAGE
                                        =================================================== -->

                                        <td class="goods-page-image">

                                            <a
                                                href="<?php
                                                    echo base_url();
                                                ?>product-detail/<?php
                                                    echo $value->url_key;
                                                ?>"
                                                target="_blank"
                                            >

                                                <img
                                                    src="<?php
                                                        echo $base_image;
                                                    ?>"
                                                    alt="<?php
                                                        echo get_display_product_name(
                                                            $value
                                                        );
                                                    ?>"
                                                >

                                            </a>

                                        </td>


                                        <!-- ==================================================
                                             DESCRIPTION
                                        =================================================== -->

                                        <td class="goods-page-description">

                                            <h3>

                                                <a
                                                    href="<?php
                                                        echo base_url();
                                                    ?>product-detail/<?php
                                                        echo $value->url_key;
                                                    ?>"
                                                    target="_blank"
                                                >

                                                    <?php
                                                    echo get_display_product_name(
                                                        $value
                                                    );
                                                    ?>

                                                </a>

                                            </h3>


                                            <?php echo $variants; ?>


                                            <!-- BUNDLE -->

                                            <?php if (
                                                isset($value->product_type) &&
                                                $value->product_type == 'bundle'
                                            ) { ?>

                                                <em>
                                                    <?php
                                                    echo !empty(
                                                        $value->bundleData
                                                    )
                                                    ? $value->bundleData
                                                    : '';
                                                    ?>
                                                </em>

                                            <?php } ?>


                                            <!-- OUT OF STOCK -->

                                            <?php if (
                                                isset($value->available_qty) &&
                                                (int)$value->available_qty <= 0
                                            ): ?>

                                                <p>

                                                    <span
                                                        class="label label-danger"
                                                        style="
                                                            font-size:12px;
                                                            padding:4px 8px;
                                                        "
                                                    >

                                                        <?php

                                                        echo $this->lang->line(
                                                            'out_of_stock_label'
                                                        )
                                                        ? $this->lang->line(
                                                            'out_of_stock_label'
                                                        )
                                                        : 'Out of Stock';

                                                        ?>

                                                    </span>

                                                </p>

                                            <?php endif; ?>


                                            <!-- ==================================================
                                                 PRODUCT WEIGHT
                                            =================================================== -->

                                            <?php if ($item_weight > 0): ?>

                                                <p
                                                    style="
                                                        font-size:13px;
                                                        color:#777;
                                                    "
                                                >

                                                    Weight:

                                                    <strong>
                                                        <?php
                                                        echo number_format(
                                                            $item_weight / 1000,
                                                            2
                                                        );
                                                        ?>
                                                        KG
                                                    </strong>

                                                    ×

                                                    <strong>
                                                        <?php
                                                        echo $item_qty;
                                                        ?>
                                                    </strong>

                                                    =

                                                    <strong>
                                                        <?php
                                                        echo number_format(
                                                            $item_total_weight / 1000,
                                                            2
                                                        );
                                                        ?>
                                                        KG
                                                    </strong>

                                                </p>

                                            <?php endif; ?>


                                            <p
                                                id="qtyError_<?php
                                                    echo $value->item_id;
                                                ?>"
                                                class="qty-error"
                                            ></p>


                                            <p class="delivery-time">

                                                <?php

                                                echo (
                                                    isset(
                                                        $value->estimate_delivery_time
                                                    ) &&
                                                    $value->estimate_delivery_time != ''
                                                )

                                                ?

                                                $this->lang->line(
                                                    'delivery_in_days'
                                                ) .
                                                ' ' .
                                                $value->estimate_delivery_time .
                                                ' ' .
                                                $this->lang->line('days')

                                                : '';

                                                ?>

                                            </p>

                                        </td>


                                        <!-- ==================================================
                                             QUANTITY
                                        =================================================== -->

                                        <td class="goods-page-quantity">

                                            <div class="product-quantity">

                                                <?php

                                                $available_qty =
                                                    $value->available_qty;

                                                if (
                                                    $available_qty > $qty_limit ||
                                                    $value->prelaunch == 1 ||
                                                    $value->product_type == 'bundle'
                                                ) {

                                                    $available_qty =
                                                        $qty_limit;
                                                }

                                                ?>


                                                <div
                                                    class="
                                                        input-group
                                                        bootstrap-touchspin
                                                        input-group-sm
                                                    "
                                                >


                                                    <!-- DECREASE -->

                                                    <span class="input-group-btn">

                                                        <button
                                                            class="
                                                                btn
                                                                quantity-down
                                                                bootstrap-touchspin-down
                                                            "

                                                            onclick="decreaseQtyValue(
                                                                <?php
                                                                echo $value->item_id;
                                                                ?>,
                                                                '<?php
                                                                echo $value->product_type;
                                                                ?>',
                                                                <?php
                                                                echo $value->product_id;
                                                                ?>,
                                                                <?php
                                                                echo $value->parent_product_id;
                                                                ?>
                                                            )"

                                                            type="button"
                                                        >

                                                            <i class="fa fa-angle-down"></i>

                                                        </button>

                                                    </span>


                                                    <!-- QUANTITY -->

                                                    <input
                                                        id="quantity_<?php
                                                            echo $value->item_id;
                                                        ?>"

                                                        data-item-id="<?php
                                                            echo $value->item_id;
                                                        ?>"

                                                        data-price="<?php
                                                            echo number_format(
                                                                $value->price,
                                                                2
                                                            );
                                                        ?>"

                                                        type="text"

                                                        min="1"

                                                        max="<?php
                                                            echo $available_qty;
                                                        ?>"

                                                        value="<?php
                                                            echo $value->qty_ordered;
                                                        ?>"

                                                        readonly

                                                        class="
                                                            form-control
                                                            input-sm
                                                        "

                                                        style="display:block;"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        value="<?php
                                                            echo $value->qty_ordered;
                                                        ?>"
                                                        name="previous_qty[]"
                                                        id="previous_qty_<?php
                                                            echo $value->item_id;
                                                        ?>"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        value="<?php
                                                            echo $available_qty;
                                                        ?>"
                                                        name="max_qty[]"
                                                        id="max_qty_<?php
                                                            echo $value->item_id;
                                                        ?>"
                                                    >


                                                    <!-- INCREASE -->

                                                    <span class="input-group-btn">

                                                        <button
                                                            class="
                                                                btn
                                                                quantity-up
                                                                bootstrap-touchspin-up
                                                            "

                                                            onclick="increaseQtyValue(
                                                                <?php
                                                                echo $value->item_id;
                                                                ?>,
                                                                '<?php
                                                                echo $value->product_type;
                                                                ?>',
                                                                <?php
                                                                echo $value->product_id;
                                                                ?>,
                                                                <?php
                                                                echo $value->parent_product_id;
                                                                ?>
                                                            )"

                                                            type="button"
                                                        >

                                                            <i class="fa fa-angle-up"></i>

                                                        </button>

                                                    </span>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- ==================================================
                                             UNIT PRICE
                                        =================================================== -->

                                        <td class="goods-page-total">

                                            <strong>

                                                <?php

                                                echo (
                                                    (
                                                        $this->session->userdata(
                                                            'currency_code_session'
                                                        ) &&
                                                        $default_currency_flag != 1
                                                    )

                                                    ?

                                                    convert_currency_website(
                                                        $value->price,
                                                        $currency_conversion_rate,
                                                        $currency_symbol
                                                    )

                                                    :

                                                    CURRENCY_TYPE .
                                                    ' ' .
                                                    number_format(
                                                        $value->price,
                                                        2
                                                    )
                                                );

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- ==================================================
                                             TOTAL
                                        =================================================== -->

                                        <td class="goods-page-total">

                                            <strong
                                                id="item_total_price_<?php
                                                    echo $value->item_id;
                                                ?>"
                                            >

                                                <?php

                                                echo (
                                                    (
                                                        $this->session->userdata(
                                                            'currency_code_session'
                                                        ) &&
                                                        $default_currency_flag != 1
                                                    )

                                                    ?

                                                    convert_currency_website(
                                                        $value->total_price,
                                                        $currency_conversion_rate,
                                                        $currency_symbol
                                                    )

                                                    :

                                                    CURRENCY_TYPE .
                                                    ' ' .
                                                    number_format(
                                                        $value->total_price,
                                                        2
                                                    )
                                                );

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- ==================================================
                                             REMOVE
                                        =================================================== -->

                                        <td
                                            class="
                                                del-goods-col
                                                text-center
                                            "
                                        >

                                            <a
                                                href="javascript:;"
                                                onclick="RemoveCartItem(
                                                    '<?php
                                                    echo $value->item_id;
                                                    ?>'
                                                )"
                                            >

                                                <i
                                                    class="fa fa-trash"
                                                    aria-hidden="true"
                                                    style="font-size:20px;"
                                                ></i>

                                                <?php
                                                echo $this->lang->line(
                                                    'remove_label'
                                                );
                                                ?>

                                            </a>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- ======================================================
                     CART SUMMARY
                ======================================================= -->

                <div class="col-md-3 col-sm-4">

                    <div class="clearfix">

                        <div class="shopping-total">

                            <div id="cart-page-sidebar">

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Existing Cart Price Details
                                |--------------------------------------------------------------------------
                                */

                                (new CartList())->cartPriceDetails(
                                    $CartData,
                                    'cartPage'
                                );

                                ?>

                            </div>


                            <!-- ==================================================
                                 CHECKOUT
                            =================================================== -->

                            <div class="divcent text-center">


                                <?php if (
                                    $has_out_of_stock ||
                                    $weight_exceeded
                                ): ?>

                                    <button
                                        type="button"
                                        class="
                                            btn
                                            btn-primary
                                            chkout
                                        "

                                        disabled

                                        style="
                                            opacity:0.6;
                                            cursor:not-allowed;
                                        "

                                        title="<?php

                                        if ($weight_exceeded) {

                                            echo 'Maximum order weight is 60 KG';

                                        } else {

                                            echo 'Please remove out-of-stock items to proceed';

                                        }

                                        ?>"
                                    >

                                        <?php
                                        echo $this->lang->line(
                                            'checkout_label'
                                        );
                                        ?>

                                        <i class="fa fa-ban"></i>

                                    </button>


                                <?php } else: ?>


                                    <a
                                        href="<?php
                                            echo base_url();
                                        ?>checkout"
                                        class="
                                            btn
                                            btn-primary
                                            chkout
                                        "
                                    >

                                        <?php
                                        echo $this->lang->line(
                                            'checkout_label'
                                        );
                                        ?>

                                        <i class="fa fa-check"></i>

                                    </a>


                                <?php endif; ?>


                                <!-- CONTINUE SHOPPING -->

                                <a
                                    href="<?php echo base_url(); ?>"
                                    class="btn btn-default"
                                >

                                    <?php
                                    echo $this->lang->line(
                                        'continue_shopping'
                                    );
                                    ?>

                                    <i class="fa fa-shopping-cart"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    <?php } else { ?>


        <!-- ==============================================================
             EMPTY CART
        ============================================================== -->

        <div class="shopping-cart-page">

            <div class="shopping-cart-data clearfix">

                <p>
                    <?php
                    echo $this->lang->line('cart_empty');
                    ?>
                </p>

            </div>

        </div>


    <?php } ?>

</div>


<?php
/*
|--------------------------------------------------------------------------
| CURRENCY
|--------------------------------------------------------------------------
*/

$currency_conversion_rate =
    $this->session->userdata('currency_conversion_rate');

$currency_symbol =
    $this->session->userdata('currency_symbol');

$default_currency_flag =
    $this->session->userdata('default_currency_flag');


if (
    isset($CartData) &&
    isset($CartData->cartItems) &&
    count($CartData->cartItems) > 0
) {

    $cartItems =
        $CartData->cartItems;

    $cartDetails =
        $CartData->cartDetails;

?>


<!-- ==============================================================
     CART SUMMARY
=============================================================== -->

<ul>


    <!-- SUB TOTAL -->

    <li>

        <em>
            <?php
            echo $this->lang->line('sub_total');
            ?>
        </em>

        <strong class="price">

            <?php

            echo (
                (
                    $this->session->userdata(
                        'currency_code_session'
                    ) &&
                    $default_currency_flag != 1
                )

                ?

                convert_currency_website(
                    $cartDetails->base_subtotal,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                :

                CURRENCY_TYPE .
                ' ' .
                number_format(
                    $cartDetails->base_subtotal,
                    2
                )
            );

            ?>

        </strong>

    </li>


    <!-- VAT -->

    <li>

        <em>
            <?php
            echo $this->lang->line('taxes');
            ?>
        </em>

        <strong class="price">

            <?php

            echo (
                (
                    $this->session->userdata(
                        'currency_code_session'
                    ) &&
                    $default_currency_flag != 1
                )

                ?

                convert_currency_website(
                    $cartDetails->tax_amount,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                :

                CURRENCY_TYPE .
                ' ' .
                number_format(
                    $cartDetails->tax_amount,
                    2
                )
            );

            ?>

        </strong>

    </li>


    <!-- DISCOUNT -->

    <?php if (!empty($cartDetails->coupon_code)) { ?>

        <li>

            <em>
                <?php
                echo $this->lang->line('discount_label');
                ?>
            </em>

            <strong class="price">

                <?php

                echo (
                    (
                        $this->session->userdata(
                            'currency_code_session'
                        ) &&
                        $default_currency_flag != 1
                    )

                    ?

                    convert_currency_website(
                        $cartDetails->base_discount_amount,
                        $currency_conversion_rate,
                        $currency_symbol
                    )

                    :

                    CURRENCY_TYPE .
                    ' ' .
                    number_format(
                        $cartDetails->base_discount_amount,
                        2
                    )
                );

                ?>

            </strong>

        </li>

    <?php } ?>


    <!-- GIFT CARD -->

    <?php if (!empty($cartDetails->voucher_code)) { ?>

        <li>

            <em>
                <?php
                echo $this->lang->line('gift_card_amount');
                ?>
            </em>

            <strong class="price">

                <?php

                echo (
                    (
                        $this->session->userdata(
                            'currency_code_session'
                        ) &&
                        $default_currency_flag != 1
                    )

                    ?

                    convert_currency_website(
                        $cartDetails->voucher_amount,
                        $currency_conversion_rate,
                        $currency_symbol
                    )

                    :

                    CURRENCY_TYPE .
                    ' ' .
                    number_format(
                        $cartDetails->voucher_amount,
                        2
                    )
                );

                ?>

            </strong>

        </li>

    <?php } ?>


    <!-- ==============================================================
         SHIPPING
    ============================================================== -->

    <li>

        <em>
            <?php
            echo $this->lang->line('shipping_cost');
            ?>
        </em>

        <strong class="price">

            <?php

            /*
            |--------------------------------------------------------------------------
            | Calculate YM Shipping
            |--------------------------------------------------------------------------
            */

            $other_shipping =
                isset($cartDetails->shipping_amount)
                ? (float)$cartDetails->shipping_amount
                : 0;

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Use calculated crate charge for <= 60 KG.
            | Do NOT allow 120 KG to become 900 MUR.
            |
            */

            if ($weight_exceeded) {

                $effective_shipping = 0;

            } else {

                $effective_shipping =
                    $ym_delivery_charge +
                    $other_shipping;
            }


            echo (

                (
                    $this->session->userdata(
                        'currency_code_session'
                    ) &&
                    $default_currency_flag != 1
                )

                ?

                convert_currency_website(
                    $effective_shipping,
                    $currency_conversion_rate,
                    $currency_symbol
                )

                :

                CURRENCY_TYPE .
                ' ' .
                number_format(
                    $effective_shipping,
                    2
                )
            );

            ?>

        </strong>

    </li>


    <!-- ==============================================================
         WEIGHT INFORMATION
    ============================================================== -->

    <li>

        <em>
            Delivery Weight
        </em>

        <strong class="price">

            <?php
            echo number_format(
                $total_cart_weight_kg,
                2
            );
            ?>
            KG

        </strong>

    </li>


    <!-- ==============================================================
         WEIGHT LIMIT
    ============================================================== -->

    <li>

        <em>
            Delivery Crate
        </em>

        <strong class="price">

            <?php

            if ($weight_exceeded) {

                echo '<span style="color:#d9534f;">Not Available</span>';

            } elseif ($total_cart_weight <= 20000) {

                echo 'Small - 300 MUR';

            } elseif ($total_cart_weight <= 40000) {

                echo 'Medium - 600 MUR';

            } elseif ($total_cart_weight <= 60000) {

                echo 'Large - 900 MUR';

            } else {

                echo '<span style="color:#d9534f;">Not Available</span>';
            }

            ?>

        </strong>

    </li>


    <!-- ==============================================================
         GRAND TOTAL
    ============================================================== -->

    <li class="shopping-total-price">

        <em>
            <?php
            echo $this->lang->line('total_label');
            ?>
        </em>

        <strong class="price">

            <?php

            /*
            |--------------------------------------------------------------------------
            | If >60 KG
            |--------------------------------------------------------------------------
            |
            | Do not display a valid shipping/order total.
            |
            */

            if ($weight_exceeded) {

                echo '<span style="color:#d9534f;">Order not available</span>';

            } else {

                echo (

                    (
                        $this->session->userdata(
                            'currency_code_session'
                        ) &&
                        $default_currency_flag != 1
                    )

                    ?

                    convert_currency_website(
                        $cartDetails->grand_total,
                        $currency_conversion_rate,
                        $currency_symbol
                    )

                    :

                    CURRENCY_TYPE .
                    ' ' .
                    number_format(
                        $cartDetails->grand_total,
                        2
                    )
                );
            }

            ?>

        </strong>

    </li>


    <!-- ==============================================================
         DISCOUNT CODE
    ============================================================== -->

    <?php if (empty($cartDetails->voucher_code)) { ?>

        <li
            class="dvcode"
            id="li-discount-code"
            style="background-color:#ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php
                echo $this->lang->line(
                    'discount_code_label'
                );
                ?>

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
                            echo !empty(
                                $cartDetails->coupon_code
                            )
                            ? $cartDetails->coupon_code
                            : '';
                        ?>"

                        <?php

                        echo !empty(
                            $cartDetails->coupon_code
                        )
                        ? 'readonly'
                        : '';

                        ?>

                        placeholder="<?php
                            echo lang(
                                'enter_discount_code'
                            );
                        ?>"
                    >


                    <?php if (
                        !empty($cartDetails->coupon_code)
                    ) { ?>

                        <input
                            type="button"
                            name="apply_coupon"

                            onclick="removeDiscount(
                                '<?php
                                echo $cartDetails->coupon_code;
                                ?>',
                                0
                            );"

                            value="<?php
                                echo $this->lang->line(
                                    'remove_label'
                                );
                            ?>"

                            class="
                                btn
                                btn-primary
                                btn-sm
                            "
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_coupon"

                            value="<?php
                                echo $this->lang->line(
                                    'apply_label'
                                );
                            ?>"

                            class="
                                btn
                                btn-primary
                                btn-sm
                            "
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


    <!-- ==============================================================
         GIFT CARD
    ============================================================== -->

    <li
        class="dvcode"
        id="li-giftcard-code"
        style="background-color:#ECEBEB;"
    >

        <em style="margin-bottom:5px;">

            <?php
            echo $this->lang->line(
                'gift_card_label'
            );
            ?>

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

                    value="<?php
                        echo $this->session->userdata(
                            'sis_session_id'
                        );
                    ?>"
                >


                <div class="d-flex">

                    <input
                        id="giftcard_code"
                        class="form-control me-2"
                        name="giftcard_code"
                        type="text"

                        placeholder="<?php
                            echo lang(
                                'enter_gift_card_code'
                            );
                        ?>"
                    >


                    <button
                        type="button"
                        id="applyGiftCardBtn"
                        class="
                            btn
                            btn-primary
                            btn-sm
                        "
                    >
                        Apply
                    </button>

                </div>


                <div
                    id="applied-giftcards"
                    class="mt-2"
                >

                    <?php

                    if (!empty(
                        $cartDetails->voucher_code
                    )) {

                        $codes =
                            explode(
                                ',',
                                $cartDetails->voucher_code
                            );

                        foreach ($codes as $code) {

                            $code = trim($code);

                            echo '

                            <div
                                class="
                                    applied-giftcard
                                    badge
                                    bg-light
                                    text-dark
                                    p-2
                                    me-1
                                    mb-1
                                "
                                data-code="' .
                                htmlspecialchars(
                                    $code,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) .
                                '"
                            >

                                ' .
                                htmlspecialchars(
                                    $code,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) .
                                '

                                <span
                                    class="
                                        remove-giftcard
                                        text-danger
                                        ms-1
                                    "
                                    style="cursor:pointer;"
                                >
                                    &times;
                                </span>

                            </div>

                            ';
                        }
                    }

                    ?>

                </div>


                <div
                    id="giftcard-message"
                    class="
                        giftcard_code_message
                        mt-2
                    "
                ></div>

            </form>

        </div>

    </li>


    <!-- ==============================================================
         VOUCHER
    ============================================================== -->

    <?php if (empty($cartDetails->coupon_code)) { ?>

        <li
            class="dvcode"
            id="li-voucher-code"
            style="background-color:#ECEBEB;"
        >

            <em style="margin-bottom:5px;">

                <?php
                echo $this->lang->line(
                    'voucher_code_label'
                );
                ?>

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


                    <?php if (
                        !empty($cartDetails->voucher_code)
                    ) { ?>

                        <input
                            type="button"
                            name="apply_voucher"

                            onclick="removeDiscount(
                                '<?php
                                echo $cartDetails->voucher_code;
                                ?>',
                                1
                            );"

                            value="<?php
                                echo $this->lang->line(
                                    'remove_label'
                                );
                            ?>"

                            class="
                                btn
                                btn-primary
                            "
                        >

                    <?php } else { ?>

                        <input
                            type="submit"
                            name="apply_voucher"

                            value="<?php
                                echo $this->lang->line(
                                    'apply_label'
                                );
                            ?>"

                            class="
                                btn
                                btn-primary
                            "
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

<?php } ?>


<!-- ==============================================================
     GIFT CARD REMOVE MODAL
=============================================================== -->

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

                    <?php
                    echo $this->lang->line(
                        'remove_gift_card_modal_title'
                    );
                    ?>

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"

                    aria-label="<?php
                        echo $this->lang->line(
                            'close_label'
                        );
                    ?>"
                ></button>

            </div>


            <div class="modal-body">

                <?php
                echo $this->lang->line(
                    'remove_gift_card_modal_body'
                );
                ?>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >

                    <?php
                    echo $this->lang->line(
                        'cancel_label'
                    );
                    ?>

                </button>


                <button
                    type="button"
                    class="btn btn-danger"
                    id="confirmRemove"
                >

                    <?php
                    echo $this->lang->line(
                        'remove_label'
                    );
                    ?>

                </button>

            </div>

        </div>

    </div>

</div>


<!-- ==============================================================
     JAVASCRIPT
=============================================================== -->

<script>

$(document).ready(function () {


    /*
    |--------------------------------------------------------------------------
    | APPLY GIFT CARD
    |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | CHECK TOTAL
        |--------------------------------------------------------------------------
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


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url:
                BASE_URL +
                "cart/applyGiftCard",

            type: "POST",

            dataType: "json",

            data: {

                gift_code:
                    gift_code,

                session_id:
                    session_id

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


                    $('#giftcard_code')
                        .val('');


                    $('#applied-giftcards')
                        .empty();


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
                                        class="
                                            applied-giftcard
                                            badge
                                            bg-light
                                            text-dark
                                            p-2
                                            me-1
                                            mb-1
                                        "
                                        data-code="${code}"
                                    >

                                        ${code}

                                        <span
                                            class="
                                                remove-giftcard
                                                text-danger
                                                ms-1
                                            "
                                            style="cursor:pointer;"
                                        >
                                            &times;
                                        </span>

                                    </div>

                                `);

                            }
                        );

                    }


                    setTimeout(
                        function () {

                            location.reload();

                        },
                        1000
                    );


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
    |--------------------------------------------------------------------------
    | REMOVE GIFT CARD
    |--------------------------------------------------------------------------
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

                url:
                    BASE_URL +
                    "cart/removeGiftCard",

                type:
                    "POST",

                dataType:
                    "json",

                data: {

                    gift_code:
                        gift_code,

                    session_id:
                        session_id

                },


                success: function (res) {

                    if (
                        res.status === 'success'
                    ) {

                        showGiftcardAlert(
                            res.message,
                            'success'
                        );


                        $(
                            `.applied-giftcard[data-code="${gift_code}"]`
                        ).remove();


                        setTimeout(
                            function () {

                                location.reload();

                            },
                            800
                        );


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
|--------------------------------------------------------------------------
| GIFT CARD ALERT
|--------------------------------------------------------------------------
*/

function showGiftcardAlert(
    message,
    type = 'success'
) {

    $('#giftcard-message').html(`

        <div
            class="
                alert
                alert-${type}
                alert-dismissible
                fade
                show
            "
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

</script>