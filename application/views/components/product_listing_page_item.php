<?php 
$lang = $this->session->userdata('site_lang');
if ($lang == 'french' && !empty($prod->lang_title)) {
    $display_name = $prod->lang_title;
} else {
    $display_name = $prod->name;
}

if (!($prod instanceof ProductPresenter) && is_object($prod)) {
    $prod = ProductPresenter::from($prod);
}

$promo = isset($active_promotion) ? $active_promotion : (is_object($prod) && method_exists($prod, 'get_active_promotion') ? $prod->get_active_promotion() : null);
$stock = isset($stock_badge) ? $stock_badge : (is_object($prod) && method_exists($prod, 'get_stock_badge') ? $prod->get_stock_badge() : null);

// Stock check: if stock is 0 or less, or status is out of stock
$is_out_of_stock = is_product_out_of_stock($prod);
$product_stock = get_product_available_stock($prod);

// Promotion detection for Daily Deals, Flash Sales, New Arrivals, and Trending Products
$is_flash_sale = ($promo && $promo->type === 'flash_sale') 
    || ($type === 'FlashSaleListing') 
    || (!empty($prod->flash_sale) && $prod->flash_sale == 1) 
    || (!empty($prod->offer_type) && $prod->offer_type === 'flash_sale');

$is_daily_deal = ($promo && $promo->type === 'daily_deal') 
    || ($type === 'DailyDealsListing') 
    || (!empty($prod->daily_deals) && $prod->daily_deals == 1) 
    || (!empty($prod->offer_type) && $prod->offer_type === 'daily_deal');

// Price determination
$price_options = [
    $prod->product_type == 'configurable' ? $prod->price_sorting_configurable : null,
    $prod->webshop_price,
    $prod->min_price,
    $prod->price 
];

$price_to_show = 0;
foreach ($price_options as $option) {
    if (!empty($option) && $option > 0) {
        $price_to_show = (float)$option;
        break; 
    }
}

$special_price_to_show = 0;
if ($promo && !empty($promo->special_price)) {
    $special_price_to_show = (float)$promo->special_price;
    if (!empty($promo->regular_price)) {
        $price_to_show = (float)$promo->regular_price;
    }
} elseif (!empty($prod->special_price) && (float)$prod->special_price > 0 && $price_to_show > (float)$prod->special_price) {
    $special_price_to_show = (float)$prod->special_price;
}

// Discount percentage calculation
$discount_percent = 0;
if ($promo && !empty($promo->discount_percent) && $promo->discount_percent > 0) {
    $discount_percent = (int)$promo->discount_percent;
} elseif ($special_price_to_show > 0 && $price_to_show > $special_price_to_show) {
    $discount_percent = (int)round((($price_to_show - $special_price_to_show) / $price_to_show) * 100);
}
?>

<?php if (isset($check) && $check == 'owlView') {
    echo "<div>";
} ?>

<div class="product-item" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="pi-img-wrapper" style="position: relative;">

        <a href="<?= $product_url ?>">
            <img src="<?php echo $prod_image; ?>" class="img-responsive" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>">
        </a>

        <!-- Badges inside product image at bottom (Daily Deals, New Arrivals, Trending, Flash Sales, Out of Stock) -->
        <?php if ($is_out_of_stock || $is_daily_deal || $is_flash_sale || $discount_percent > 0): ?>
            <span class="pi-img-bottom-badges" style="position: absolute !important; bottom: 8px !important; left: 8px !important; right: 8px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 4px !important; z-index: 8 !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; width: calc(100% - 16px) !important; background: transparent !important;">
                <span style="display: flex !important; align-items: center !important; gap: 4px !important;">
                    <?php if ($is_out_of_stock): ?>
                        <span class="badge-out-of-stock" style="background-color: #dc3545 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 3px 9px !important; border-radius: 12px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; white-space: nowrap !important; display: inline-block !important;">OUT OF STOCK</span>
                    <?php elseif ($is_flash_sale): ?>
                        <span class="badge-deal-flash" style="background-color: #e52525 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 3px 9px !important; border-radius: 12px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; white-space: nowrap !important; display: inline-block !important;">⚡ FLASH SALE</span>
                    <?php elseif ($is_daily_deal): ?>
                        <span class="badge-deal-daily" style="background-color: #f7b928 !important; color: #1a1a1a !important; font-size: 10px !important; font-weight: 800 !important; padding: 3px 9px !important; border-radius: 12px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; white-space: nowrap !important; display: inline-block !important;">★ DAILY DEAL</span>
                    <?php endif; ?>
                </span>

                <?php if ($discount_percent > 0 && !$is_out_of_stock): ?>
                    <span style="margin-left: auto !important; display: inline-block !important;">
                        <span class="badge-discount-pill" style="background-color: #e52525 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 3px 8px !important; border-radius: 12px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; white-space: nowrap !important; display: inline-block !important;">-<?= $discount_percent ?>%</span>
                    </span>
                <?php endif; ?>
            </span>
        <?php endif; ?>

        <div>
            <a href="javascript:QuickViewProdDetails('<?php echo $prod->url_key; ?>','<?= $product_url ?>')" class="btn btn-default"><?= lang('view_label') ?></a>
        </div>

    </div>

    <div class="pi-content-wrapper">

        <?php
        $productname = (isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name;
        ?>

        <h3 id="product-name-<?php echo $prod->id; ?>" title="<?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?= $product_url ?>">
                <?php echo (strlen($display_name) > 30) ? substr($display_name, 0, 28) . '...' : $display_name; ?>
            </a>
        </h3>

        <!-- Pricing -->
        <div class="pi-price">
            <?php 
            if ($special_price_to_show > 0 && $price_to_show > 0) {
                // Show EShop price with strikethrough
                echo '<div style="color: #999; font-size: 0.85em;"><del>MUR ' . number_format($price_to_show, 2) . '</del></div>'; 
                
                // Show Special price below
                echo '<div style="color: #ff9e0c; font-size: 1.1em; font-weight: 600;">MUR ' . number_format($special_price_to_show, 2) . '</div>';
            } else {
                // Fallback: Show only EShop price if no special price exists
                echo '<div>' . (($price_to_show > 0) ? '<span>MUR ' . number_format($price_to_show, 2) . '</span>' : '<span>Price on request</span>') . '</div>';
            }
            ?>
        </div>

        <!-- LIMITED STOCK & remaining stock indicator for Flash Sale promotion only -->
        <?php if ($is_flash_sale && !$is_out_of_stock) : ?>
            <?php 
            $remaining_stock = null;
            if ($stock && !empty($stock->qty)) {
                $remaining_stock = $stock->qty;
            } elseif (isset($prod->stock_qty) && is_numeric($prod->stock_qty) && $prod->stock_qty > 0) {
                $remaining_stock = (int)$prod->stock_qty;
            } elseif (isset($prod->qty) && is_numeric($prod->qty) && $prod->qty > 0) {
                $remaining_stock = (int)$prod->qty;
            }
            ?>
            <div class="flash-sale-stock" style="margin: 4px 0 6px 0; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <span class="badge-limited-stock" style="display: inline-block; background-color: #1b873f; color: #ffffff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.3px;">📦 LIMITED STOCK</span>
                <?php if ($remaining_stock !== null) : ?>
                    <span class="stock-indicator-text" style="color: #cf1322; font-weight: 700; font-size: 11px;">Only <?= $remaining_stock ?> left</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Deal Ends / Countdown -->
        <?php if ($promo && !empty($promo->ends_at) && $promo->ends_at > time()): ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;">
                <span class="pi-countdown-timer" data-promo-ends-at="<?php echo $promo->ends_at; ?>" data-countdown-prefix="Ends in ">
                    <span class="countdown-time">Ends in ...</span>
                </span>
            </p>
        <?php elseif ($type === 'DailyDealsListing' && !empty($prod->daily_deal_ends_at)) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('deal_ends') ? $this->lang->line('deal_ends') : 'Deal ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at)); ?></p>
        <?php elseif ($type === 'FlashSaleListing' && !empty($prod->flash_sale_ends_at)) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('sale_ends') ? $this->lang->line('sale_ends') : 'Sale ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->flash_sale_ends_at) ? (int)$prod->flash_sale_ends_at : strtotime($prod->flash_sale_ends_at)); ?></p>
        <?php elseif (!empty($prod->daily_deal_ends_at) && $is_daily_deal) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('deal_ends') ? $this->lang->line('deal_ends') : 'Deal ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at)); ?></p>
        <?php elseif (!empty($prod->flash_sale_ends_at) && $is_flash_sale) : ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('sale_ends') ? $this->lang->line('sale_ends') : 'Sale ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->flash_sale_ends_at) ? (int)$prod->flash_sale_ends_at : strtotime($prod->flash_sale_ends_at)); ?></p>
        <?php endif; ?>

        <?php if ($is_out_of_stock) : ?>
            <a href="<?= $product_url ?>" class="btn btn-default add2cart" style="background-color: #6c757d; border-color: #6c757d; color: #ffffff; cursor: pointer;"><?= $this->lang->line('out_of_stock_btn') ? $this->lang->line('out_of_stock_btn') : ($this->lang->line('out_of_stock_label') ? $this->lang->line('out_of_stock_label') : 'Out of Stock'); ?></a>
        <?php elseif ($type === 'PrelaunchListing') : ?>
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
                <?php } else { ?>
                    <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>
                <?php } ?>
            <?php } else { ?>
                <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>
            <?php } ?>
        <?php endif; ?>

        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>

    </div>

</div>

<?php if (isset($check) && $check == 'owlView') {
    echo "</div>";
} ?>
