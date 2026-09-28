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
        $prod->webshop_price,
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
    if (!empty($prod->special_price) && $prod->special_price > 0 && $price_to_show > 0) {
        
        // Show EShop price with strikethrough
        echo '<div style="color: #999; font-size: 0.85em;"><del>MUR ' . number_format($price_to_show, 2) . '</del></div>'; 
        
        // Show Special price below
        echo '<div style="color: #ff9e0c; font-size: 1.1em;">MUR ' . number_format($prod->special_price, 2) . '</div>';
        
    } else {
        // Fallback: Show only EShop price if no special price exists
        echo '<div>' . (($price_to_show > 0) ? '<span>MUR ' . number_format($price_to_show, 2) . '</span>' : '<span>Price on request</span>') . '</div>';
    }
    ?>
</div>

        <?php if ($type === 'DailyDealsListing' && !empty($prod->daily_deal_ends_at)) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('deal_ends') ? $this->lang->line('deal_ends') : 'Deal ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at)); ?></p>
        <?php elseif ($type === 'FlashSaleListing' && !empty($prod->flash_sale_ends_at)) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('sale_ends') ? $this->lang->line('sale_ends') : 'Sale ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->flash_sale_ends_at) ? (int)$prod->flash_sale_ends_at : strtotime($prod->flash_sale_ends_at)); ?></p>
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
