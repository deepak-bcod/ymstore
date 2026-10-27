<?php 
// echo "<pre>";
// print_r($prod); 
// die;
$lang = $this->session->userdata('site_lang');
if ($lang == 'french' && !empty($prod->lang_title)) {
    $display_name = $prod->lang_title;
} else {
    $display_name = $prod->name;
}
 ?>

<?php if ($check == 'owlView') {

    echo "<div>";

} ?>

<div class="product-item" title="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name); ?>">

    <div class="pi-img-wrapper">

        <a href="<?= $product_url ?>">

            <img src="<?php echo $prod_image; ?>" class="img-responsive" title="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name); ?>" alt="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name); ?>">

        </a>

        <div>

            <a href="javascript:QuickViewProdDetails('<?php echo $prod->url_key; ?>','<?= $product_url ?>')" class="btn btn-default"><?= lang('view_label') ?></a>

        </div>

    </div>

    <div class="pi-content-wrapper">

        <?php

        $productname = (isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name;

        ?>

        <h3 id="product-name-<?php echo $prod->id; ?>" title="<?php echo $display_name; ?>">
            <a href="<?= $product_url ?>">
                <?php echo (strlen($display_name) > 30) ? substr($display_name, 0, 28) . '...' : $display_name; ?>
            </a>
        </h3>



    <div class="pi-price">
    <?php 
    // (Keep your existing logic for finding $price_to_show)
    $price_options = [
        $prod->product_type == 'configurable' ? $prod->price_sorting_configurable : null,
        $prod->eu_webshop_price,
        $prod->min_price,
        $prod->price 
    ];

    $price_to_show = 0;
    foreach ($price_options as $option) {
        if (!empty($option) && $option > 0) {
            $price_to_show = $option;
            break; 
        }
    }

    // New Layout Logic
    if (!empty($prod->special_price) && $prod->special_price > 0 && $prod->special_price < $price_to_show) {
        // Strikethrough price on top
        echo '<div style="color: #999; font-size: 0.85em;"><del>MUR ' . number_format($price_to_show, 2) . '</del></div>'; 
        // Special price below
        echo '<div style="color: #ff9e0c; font-size: 1.1em;">MUR ' . number_format($prod->special_price, 2) . '</div>';
    } else {
        // Standard price
        echo '<div>' . (($price_to_show > 0) ? '<span>MUR ' . number_format($price_to_show, 2) . '</span>' : '<span>Price on request</span>') . '</div>';
    }
    ?>
</div>

        <?php 
        $legacy_now = time();
        $is_legacy_dd = (!empty($prod->daily_deals) && $prod->daily_deals == 1) || ($type === 'DailyDealsListing' && !empty($prod->daily_deal_ends_at));
        $is_legacy_fs = (!empty($prod->flash_sale) && $prod->flash_sale == 1) || ($type === 'FlashSaleListing' && !empty($prod->flash_sale_ends_at));
        $dd_ts = !empty($prod->daily_deal_ends_at) ? (is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at)) : 0;
        $legacy_stock = null;
        if (isset($prod->calculated_stock) && is_numeric($prod->calculated_stock) && (float)$prod->calculated_stock > 0) {
            $legacy_stock = (int)$prod->calculated_stock;
        } elseif (isset($prod->available_qty) && is_numeric($prod->available_qty) && (float)$prod->available_qty > 0) {
            $legacy_stock = (int)$prod->available_qty;
        } elseif (isset($prod->qty) && is_numeric($prod->qty) && (float)$prod->qty > 0) {
            $legacy_stock = (int)$prod->qty;
        } elseif (isset($prod->stock_qty) && is_numeric($prod->stock_qty) && (float)$prod->stock_qty > 0) {
            $legacy_stock = (int)$prod->stock_qty;
        }
        ?>
        <?php 
        $is_centered_legacy = ($type === 'TrendingListing' || $type === 'NewArrivalListing');
        $legacy_align_justify = $is_centered_legacy ? 'center' : 'flex-start';
        $legacy_align_text = $is_centered_legacy ? 'center' : 'left';
        ?>
        <?php if ($is_legacy_dd && $dd_ts > $legacy_now) : 
            $diff = $dd_ts - $legacy_now;
            $cd_str = ($diff >= 86400) ? (floor($diff / 86400) . ' days') : sprintf('%02d:%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60), $diff % 60);
        ?>
            <div class="promo-countdown-wrapper" style="color: #e52525 !important; font-size: 12px !important; font-weight: normal !important; margin: 3px 0 4px 0 !important; display: flex !important; align-items: center !important; justify-content: <?= $legacy_align_justify ?> !important; text-align: <?= $legacy_align_text ?> !important; gap: 4px !important; width: 100% !important;">
                <i class="fa fa-clock-o" style="color: #e52525 !important; font-size: 13px !important;"></i>
                <span>Ends in <span class="live-countdown" data-ends-at="<?= $dd_ts ?>"><?= $cd_str ?></span></span>
            </div>
        <?php endif; ?>
        <?php if ($is_legacy_fs && $legacy_stock !== null && $legacy_stock > 0) : ?>
            <div class="promo-stock-wrapper" style="color: #e52525 !important; font-size: 11.5px !important; font-weight: normal !important; margin: 2px 0 5px 0 !important; display: flex !important; align-items: center !important; justify-content: <?= $legacy_align_justify ?> !important; text-align: <?= $legacy_align_text ?> !important; gap: 4px !important; width: 100% !important;">
                <i class="fa fa-fire" style="color: #e52525 !important; font-size: 11px !important;"></i>
                <span>Only <?= $legacy_stock ?> left</span>
            </div>
        <?php endif; ?>

        <?php if ($type === 'PrelaunchListing') : ?>

            <?php if ($prod->product_type === 'simple') { ?>

                <a href="<?= $product_url ?>" data-prelaunch="yes" <?php echo $prod->product_type; ?> data-product-id="<?php echo $prod->id; ?>" data-qty="1" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } else { ?>

                <a href="javascript:;" onclick="gotoLocation('<?php echo BASE_URL . 'product-detail/' . $prod->url_key . '?type=prelaunch'; ?>');" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } ?>

        <?php else : ?>

            <?php if (isset($prod->stock_status)) { ?>

                <?php if ($prod->stock_status == 'Instock' && ($prod->product_type == 'simple' || $prod->product_type == 'conf-simple')) { ?>

                    <?php if (isset($restricted_access) && $restricted_access == 'yes' && $customer_id == 0) { ?>

                        <a href="javascript:;" id="add_to_cart" onclick="openRestrictedAccessPopup()" class="btn btn-default add2cart"><?= lang('add_to_cart') ?></a>

                    <?php } else { ?>

                        <a href="<?= $product_url ?>" <?php echo $prod->product_type; ?> data-product-id="<?php echo $prod->id; ?>" data-qty="1" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

                    <?php } ?>

                <?php } elseif ($prod->stock_status == 'Instock' && ($prod->product_type == 'configurable' || $prod->product_type == 'bundle')) { ?>

                    <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

                <?php } ?>

            <?php } else { ?>

                <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } ?>

        <?php endif; // prelaunchlisting ?>



        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>

    </div>

</div>

<?php if ($check == 'owlView') {

    echo "</div>";

} ?>
