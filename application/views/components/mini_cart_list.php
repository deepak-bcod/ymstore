    <?php
$CartData = '';
if (!empty($cart_response) && isset($cart_response) && $cart_response->is_success == 'true') {
    $CartData = $cart_response->cartData;
}

?>
<style>
    .slimScrollDiv {
        height: auto !important;
    }
    #mini-cart-main-container {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
    }
    .right-middle-header {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 15px;
        flex-wrap: nowrap;
    }
    .right-middle-header .customer-action {
        display: flex;
        align-items: center;
        flex-shrink: 1;
    }
    .right-middle-header .customer-action .link-customer-action {
        display: flex;
        flex-direction: column;
    }
    .right-middle-header .customer-action .link-customer-action a {
        white-space: nowrap;
        font-size: 12px;
        line-height: 1.3;
    }
    .top-cart-block {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        white-space: nowrap !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .top-cart-block .top-cart-info {
        display: inline-flex !important;
        align-items: center !important;
        background: #ffffff !important;
        border-radius: 20px !important;
        padding: 6px 44px 6px 14px !important;
        height: 38px !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        position: relative !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        margin: 0 !important;
    }
    .top-cart-block .top-cart-info-count {
        display: inline-flex !important;
        align-items: center !important;
        color: #333333 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        padding-right: 8px !important;
        margin-right: 8px !important;
        border-right: 1px solid #dcdcdc !important;
        white-space: nowrap !important;
    }
    .top-cart-block .top-cart-info-count span {
        margin-left: 4px;
    }
    .top-cart-block .top-cart-info-value {
        color: #333333 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        white-space: nowrap !important;
    }
    .top-cart-block > i.fa-shopping-cart,
    .top-cart-block > i {
        position: absolute !important;
        right: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 38px !important;
        height: 38px !important;
        background: #f39c12 !important;
        color: #ffffff !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 15px !important;
        line-height: 38px !important;
        text-align: center !important;
        cursor: pointer;
        z-index: 2;
        margin: 0 !important;
    }
</style>
<div class="top-cart-block">


    <div class="top-cart-info">
        <a href="javascript:void(0);" class="top-cart-info-count"><?php echo ($cart_count > 0) ? $cart_count : 0 ?>
         <span><?= $this->lang->line('items'); ?></span></a>


        <a href="javascript:void(0);" class="top-cart-info-value">
            <?php 
if (is_object($CartData) && isset($CartData->cartDetails->base_grand_total)) {
    echo ($CartData->cartDetails->base_grand_total > 0) ? 'MUR ' . $CartData->cartDetails->base_grand_total : 'MUR 0';
} else {
    echo 'MUR 0';
}
?></a>
    </div>
    <i class="fa fa-shopping-cart"></i>

    <div class="top-cart-content-wrapper">
        <?php
        if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) { ?>
            <?php $cartItems = $CartData->cartItems; ?>
            <?php
            $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
            $currency_symbol = $this->session->userdata('currency_symbol');
            $default_currency_flag = $this->session->userdata('default_currency_flag');
            ?>
            <div class="top-cart-content">
                <ul class="scroller" style="height: 250px;">
                    <?php foreach ($cartItems as $value) {
                        $base_image = ((isset($value->base_image) && $value->base_image != '') ? PRODUCT_THUMB_IMG . $value->base_image : PRODUCT_DEFAULT_IMG);
                        $product_variants = (($value->product_variants != '') ? json_decode($value->product_variants) : '');
                        $qty_ordered = $value->qty_ordered;

                        $variants = '';
                        if (isset($product_variants) && $product_variants != '') {
                            foreach ($product_variants as $pk => $single_variant) {
                                foreach ($single_variant as $key => $val) {
                                    $variants .= $key . ' - ' . $val;
                                }
                            }
                        } ?>

                        <li>
                            <a href="<?= linkUrl('product-detail/' . $value->url_key . '?type=prelaunch') ?>">
                                <img src="<?php echo $base_image; ?>" alt="<?php echo get_display_product_name($value); ?>" width="37" height="34">
                            </a>
                            <span class="cart-content-count">x <?php echo $qty_ordered; ?></span>
                            <strong>
                                <?php echo get_display_product_name($value); ?>.</br>
                                <?php if (isset($product_variants) && $product_variants != '') { ?>
                                    [<?= rtrim($variants, ", "); ?>]
                                <?php } ?>
                            </strong>

                            <?php
                            $qty = (float) $value->qty_ordered;
                            $unit_price = (float) $value->price;
                            $subtotal = $qty * $unit_price;
                            ?>

                            <em>
                                <?php
                                echo (
                                    $this->session->userdata('currency_code_session') && $default_currency_flag != 1
                                )
                                    ? convert_currency_website(
                                        $subtotal,
                                        $currency_conversion_rate,
                                        $currency_symbol
                                    )
                                    : CURRENCY_TYPE . ' ' . number_format($subtotal, 2);
                                ?>
                            </em>
                            <a href="javascript:void(0);" class="del-goods" onclick="RemoveCartItem('<?php echo $value->item_id; ?>')">&nbsp;</a>

                        </li>
                    <?php } ?>



                </ul>
                <div class="text-right">
                    <a href="<?= base_url(); ?>cart" class="btn btn-default">
    <?= $this->lang->line('view_cart'); ?>
</a>

                    <!-- <a href="<?php echo base_url(); ?>checkout" class="btn btn-primary">Checkout</a> -->
                </div>
            </div>
        <?php } else { ?>
            <div class="top-cart-content">
<p style="padding:8px 0;text-align:center;">
  <?= $this->lang->line('cart_empty'); ?>
</p>

<!-- <p style="padding:8px 0;text-align:center;">
  <a href="<?= base_url(); ?>" class="btn btn-blue" data-abc="true">
    <?= $this->lang->line('continue_shopping'); ?>
  </a>
</p> -->

            </div>
        <?php } ?>
    </div>
</div>