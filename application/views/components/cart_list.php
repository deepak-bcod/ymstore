<?php // echo "<pre>"; print_r($CartData); echo "</pre>"; ?>

<div class="col-md-12 col-sm-12 <?php echo (isset($CartData->cartItems) && count($CartData->cartItems) > 0) ? '' : 'text-center'; ?>">

    <h1><?php echo $this->lang->line('shopping_cart'); ?></h1>

    <?php if ($this->session->flashdata('error_message')): ?>
        <div class="alert alert-danger" style="margin-top: 15px;">
            <i class="fa fa-exclamation-circle"></i>
            <?php echo $this->session->flashdata('error_message'); ?>
        </div>
    <?php endif; ?>


    <?php
    /*
     * ============================================================
     * CART VALIDATION
     * ============================================================
     */

    $has_out_of_stock = false;
    $has_weight_error = false;

    /*
     * YM maximum delivery weight
     */
    $max_ym_weight = 60;

    /*
     * Calculate total cart weight
     */
    $total_cart_weight = 0;

    if (isset($CartData->cartItems) && count($CartData->cartItems) > 0) {

        foreach ($CartData->cartItems as $chk) {

            /*
             * Out of stock check
             */
            if (isset($chk->available_qty) && (int)$chk->available_qty <= 0) {
                $has_out_of_stock = true;
            }


            /*
             * Product weight
             *
             * IMPORTANT:
             * Make sure your CartData contains the product weight
             * in $chk->weight.
             */
            $product_weight = isset($chk->weight) ? (float)$chk->weight : 0;

            /*
             * Product quantity
             */
            $product_qty = isset($chk->qty_ordered)
                ? (int)$chk->qty_ordered
                : 0;

            /*
             * Total weight for this cart item
             */
            $total_cart_weight += ($product_weight * $product_qty);
        }
    }


    /*
     * ============================================================
     * 60 KG HARD LIMIT
     * ============================================================
     *
     * 0 - 20 KG   = 300 MUR
     * >20 - 40 KG = 600 MUR
     * >40 - 60 KG = 900 MUR
     * >60 KG     = BLOCK ORDER
     */
    if ($total_cart_weight > $max_ym_weight) {
        $has_weight_error = true;
    }
    ?>


    <?php if ($has_out_of_stock): ?>

        <div class="alert alert-danger" style="margin-top: 15px;">
            <i class="fa fa-exclamation-triangle"></i>

            <strong>Notice:</strong>
            One or more items in your cart are currently
            <strong>Out of Stock</strong>.
            Please remove them before proceeding to checkout.
        </div>

    <?php endif; ?>


    <?php
    /*
     * ============================================================
     * WEIGHT LIMIT ERROR MESSAGE
     * ============================================================
     */
    ?>

    <?php if ($has_weight_error): ?>

        <div class="alert alert-danger" style="margin-top: 15px;">
            <i class="fa fa-exclamation-triangle"></i>

            <strong>Delivery weight limit exceeded.</strong>

            Your cart weighs
            <strong><?php echo number_format($total_cart_weight, 2); ?> Kg</strong>.

            The maximum allowed weight for YM delivery is
            <strong>60 Kg</strong>.

            Please reduce the quantity or remove some products before
            proceeding to checkout.
        </div>

    <?php endif; ?>


    <?php if (isset($CartData->cartItems) && count($CartData->cartItems) > 0): ?>

        <div class="goods-page">

            <div class="row">

                <div class="col-md-9 col-sm-8">

                    <div class="goods-data">

                        <div class="table-wrapper-responsive">

                            <table summary="<?php echo $this->lang->line('shopping_cart'); ?>">

                                <tr>

                                    <th class="goods-page-image">
                                        <?php echo $this->lang->line('image'); ?>
                                    </th>

                                    <th class="goods-page-description">
                                        <?php echo $this->lang->line('description'); ?>
                                    </th>

                                    <th class="goods-page-quantity">
                                        <?php echo $this->lang->line('quantity'); ?>
                                    </th>

                                    <th class="goods-page-price">
                                        <?php echo $this->lang->line('unit_price'); ?>
                                    </th>

                                    <th class="goods-page-total" colspan="2">
                                        <?php echo $this->lang->line('total'); ?>
                                    </th>

                                </tr>


                                <?php foreach ($CartData->cartItems as $value): ?>

                                    <?php

                                    /*
                                     * Product image
                                     */
                                    $base_image = (
                                        isset($value->base_image) &&
                                        $value->base_image != ''
                                    )
                                        ? PRODUCT_THUMB_IMG . $value->base_image
                                        : PRODUCT_DEFAULT_IMG;


                                    /*
                                     * Product variants
                                     */
                                    $product_variants = (
                                        $value->product_variants != ''
                                    )
                                        ? json_decode($value->product_variants)
                                        : '';


                                    /*
                                     * Bundle child IDs
                                     */
                                    $bundle_child_ids = '';

                                    if (
                                        isset($value->bundle_child_details) &&
                                        $value->bundle_child_details != ''
                                    ) {

                                        $bundle_child_details =
                                            json_decode($value->bundle_child_details);

                                        if (isset($bundle_child_details)) {

                                            $ids = array_column(
                                                $bundle_child_details,
                                                'bundle_child_product_id'
                                            );

                                            $bundle_child_ids .= implode(',', $ids);
                                        }
                                    }


                                    /*
                                     * Variants HTML
                                     */
                                    $variants = '';

                                    if (
                                        isset($product_variants) &&
                                        $product_variants != ''
                                    ) {

                                        foreach (
                                            $product_variants
                                            as $pk => $single_variant
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
                                     * Product weight
                                     */
                                    $item_weight = isset($value->weight)
                                        ? (float)$value->weight
                                        : 0;

                                    /*
                                     * Item total weight
                                     */
                                    $item_total_weight =
                                        $item_weight *
                                        (int)$value->qty_ordered;

                                    ?>

                                    <tr>

                                        <input
                                            type="hidden"
                                            name="item_id[]"
                                            value="<?= $value->item_id ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="bundle_child_ids[]"
                                            id="bundle_child_ids_<?= $value->item_id ?>"
                                            value="<?php echo $bundle_child_ids; ?>"
                                        >


                                        <!-- PRODUCT IMAGE -->
                                        <td class="goods-page-image">

                                            <a
                                                href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>"
                                                target="_blank"
                                            >

                                                <img
                                                    src="<?php echo $base_image; ?>"
                                                    alt="<?php echo get_display_product_name($value); ?>"
                                                >

                                            </a>

                                        </td>


                                        <!-- PRODUCT DESCRIPTION -->
                                        <td class="goods-page-description">

                                            <h3>

                                                <a
                                                    href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>"
                                                    target="_blank"
                                                >
                                                    <?php echo get_display_product_name($value); ?>
                                                </a>

                                            </h3>


                                            <?php echo $variants; ?>


                                            <?php if (
                                                isset($value->product_type) &&
                                                $value->product_type == 'bundle'
                                            ): ?>

                                                <em>
                                                    <?php
                                                    echo !empty($value->bundleData)
                                                        ? $value->bundleData
                                                        : '';
                                                    ?>
                                                </em>

                                            <?php endif; ?>


                                            <?php if (
                                                isset($value->available_qty) &&
                                                (int)$value->available_qty <= 0
                                            ): ?>

                                                <p>

                                                    <span
                                                        class="label label-danger"
                                                        style="font-size:12px;padding:4px 8px;"
                                                    >

                                                        <?=
                                                        $this->lang->line('out_of_stock_label')
                                                            ? $this->lang->line('out_of_stock_label')
                                                            : 'Out of Stock';
                                                        ?>

                                                    </span>

                                                </p>

                                            <?php endif; ?>


                                            <p
                                                id="qtyError_<?php echo $value->item_id; ?>"
                                                class="qty-error"
                                            ></p>


                                            <p class="delivery-time">

                                                <?=
                                                ($value->estimate_delivery_time != '')
                                                    ? $this->lang->line('delivery_in_days') .
                                                      ' ' .
                                                      $value->estimate_delivery_time .
                                                      ' ' .
                                                      $this->lang->line('days')
                                                    : '';
                                                ?>

                                            </p>

                                        </td>


                                        <!-- QUANTITY -->
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

                                                <div class="input-group bootstrap-touchspin input-group-sm">


                                                    <span class="input-group-btn">

                                                        <button
                                                            class="btn quantity-down bootstrap-touchspin-down"

                                                            onclick="decreaseQtyValue(
                                                                <?php echo $value->item_id; ?>,
                                                                '<?php echo $value->product_type; ?>',
                                                                <?php echo $value->product_id; ?>,
                                                                <?php echo $value->parent_product_id; ?>
                                                            )"

                                                            type="button"
                                                        >

                                                            <i class="fa fa-angle-down"></i>

                                                        </button>

                                                    </span>


                                                    <input
                                                        id="quantity_<?php echo $value->item_id; ?>"

                                                        data-item-id="<?php echo $value->item_id; ?>"

                                                        data-price="<?php echo number_format($value->price, 2); ?>"

                                                        type="text"

                                                        min="1"

                                                        max="<?php echo $available_qty; ?>"

                                                        value="<?php echo $value->qty_ordered; ?>"

                                                        readonly

                                                        class="form-control input-sm"

                                                        style="display:block;"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        value="<?php echo $value->qty_ordered; ?>"
                                                        name="previous_qty[]"
                                                        id="previous_qty_<?php echo $value->item_id; ?>"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        value="<?php echo $available_qty; ?>"
                                                        name="max_qty[]"
                                                        id="max_qty_<?php echo $value->item_id; ?>"
                                                    >


                                                    <span class="input-group-btn">

                                                        <button
                                                            class="btn quantity-up bootstrap-touchspin-up"

                                                            onclick="increaseQtyValue(
                                                                <?php echo $value->item_id; ?>,
                                                                '<?php echo $value->product_type; ?>',
                                                                <?php echo $value->product_id; ?>,
                                                                <?php echo $value->parent_product_id; ?>
                                                            )"

                                                            type="button"
                                                        >

                                                            <i class="fa fa-angle-up"></i>

                                                        </button>

                                                    </span>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- UNIT PRICE -->
                                        <td class="goods-page-total">

                                            <strong>

                                                <?php

                                                echo (
                                                    $this->session->userdata('currency_code_session') &&
                                                    $default_currency_flag != 1
                                                )

                                                    ? convert_currency_website(
                                                        $value->price,
                                                        $currency_conversion_rate,
                                                        $currency_symbol
                                                    )

                                                    : CURRENCY_TYPE .
                                                      ' ' .
                                                      number_format(
                                                          $value->price,
                                                          2
                                                      );

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- TOTAL PRICE -->
                                        <td class="goods-page-total">

                                            <strong
                                                id="item_total_price_<?php echo $value->item_id; ?>"
                                            >

                                                <?php

                                                echo (
                                                    $this->session->userdata('currency_code_session') &&
                                                    $default_currency_flag != 1
                                                )

                                                    ? convert_currency_website(
                                                        $value->total_price,
                                                        $currency_conversion_rate,
                                                        $currency_symbol
                                                    )

                                                    : CURRENCY_TYPE .
                                                      ' ' .
                                                      number_format(
                                                          $value->total_price,
                                                          2
                                                      );

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- REMOVE -->
                                        <td class="del-goods-col text-center">

                                            <a
                                                href="javascript:;"
                                                onclick="RemoveCartItem('<?php echo $value->item_id; ?>')"
                                            >

                                                <i
                                                    class="fa fa-trash"
                                                    aria-hidden="true"
                                                    style="font-size:20px;"
                                                ></i>

                                                <?php
                                                echo $this->lang->line('remove_label');
                                                ?>

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- RIGHT SIDE -->
                <div class="col-md-3 col-sm-4">

                    <div class="clearfix">

                        <div class="shopping-total">


                            <!-- CART PRICE DETAILS -->
                            <div id="cart-page-sidebar">

                                <?php
                                (new CartList())->cartPriceDetails(
                                    $CartData,
                                    'cartPage'
                                );
                                ?>

                            </div>


                            <!-- CHECKOUT -->
                            <div class="divcent text-center">


                                <?php if ($has_out_of_stock || $has_weight_error): ?>


                                    <button
                                        type="button"
                                        class="btn btn-primary chkout"
                                        disabled
                                        style="opacity:0.6;cursor:not-allowed;"

                                        title="<?php

                                        if ($has_weight_error) {

                                            echo 'Cart weight exceeds the maximum allowed YM delivery weight of 60 Kg';

                                        } else {

                                            echo 'Please remove out-of-stock items to proceed';

                                        }

                                        ?>"
                                    >

                                        <?php
                                        echo $this->lang->line('checkout_label');
                                        ?>

                                        <i class="fa fa-ban"></i>

                                    </button>


                                <?php else: ?>


                                    <a
                                        href="<?php echo base_url(); ?>checkout"
                                        class="btn btn-primary chkout"
                                    >

                                        <?php
                                        echo $this->lang->line('checkout_label');
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
                                    echo $this->lang->line('continue_shopping');
                                    ?>

                                    <i class="fa fa-shopping-cart"></i>

                                </a>


                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    <?php else: ?>


        <div class="shopping-cart-page">

            <div class="shopping-cart-data clearfix">

                <p>
                    <?php
                    echo $this->lang->line('cart_empty');
                    ?>
                </p>

            </div>

        </div>


    <?php endif; ?>

</div>