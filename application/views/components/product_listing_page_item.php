<?php 
$lang = $this->session->userdata('site_lang');
if ($lang == 'french' && !empty($prod->lang_title)) {
    $display_name = $prod->lang_title;
} else {
    $display_name = $prod->name;
}

// Active promotion and stock status detection
if (!($prod instanceof ProductPresenter) && is_object($prod)) {
    $prod = ProductPresenter::from($prod);
}

$promo = isset($active_promotion) ? $active_promotion : (is_object($prod) && method_exists($prod, 'get_active_promotion') ? $prod->get_active_promotion() : null);
$stock = isset($stock_badge) ? $stock_badge : (is_object($prod) && method_exists($prod, 'get_stock_badge') ? $prod->get_stock_badge() : null);

// Price determination
$price_options = [
    $prod->product_type == 'configurable' ? $prod->price_sorting_configurable : null,
    $prod->webshop_price,
    $prod->min_price,
    $prod->price 
];

$regular_price = 0;
foreach ($price_options as $option) {
    if (!empty($option) && $option > 0) {
        $regular_price = (float)$option;
        break; 
    }
}

$special_price = 0;
if ($promo && !empty($promo->special_price)) {
    $special_price = (float)$promo->special_price;
    if (!empty($promo->regular_price)) {
        $regular_price = (float)$promo->regular_price;
    }
} elseif (!empty($prod->special_price) && (float)$prod->special_price > 0 && $regular_price > (float)$prod->special_price) {
    $special_price = (float)$prod->special_price;
}

$is_normal_layout = (isset($viewmode) && $viewmode === 'list') || (isset($check) && $check === 'normalView');
$card_class = $is_normal_layout ? 'product-item product-item-normal' : 'product-item product-item-grid';
?>

<?php if (isset($check) && $check == 'owlView') { echo "<div>"; } ?>

<div class="<?php echo $card_class; ?>" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="pi-img-wrapper">
        <!-- Top-Left Badge: Daily Deal / Flash Sale / Limited Stock -->
        <?php if (!$is_normal_layout): ?>
            <span class="promo-badge-overlay-left" style="position: absolute !important; top: 8px !important; left: 8px !important; right: auto !important; bottom: auto !important; width: auto !important; height: auto !important; z-index: 15 !important; display: flex !important; flex-direction: column !important; align-items: flex-start !important; text-align: left !important; pointer-events: none !important; background: transparent !important; margin: 0 !important; padding: 0 !important;">
                <?php if ($promo): ?>
                    <?php if ($promo->type === 'flash_sale'): ?>
                        <span class="promo-badge promo-badge-flash-sale">⚡ FLASH SALE</span>
                    <?php else: ?>
                        <span class="promo-badge promo-badge-daily-deal">★ DAILY DEAL</span>
                    <?php endif; ?>
                <?php elseif ($stock): ?>
                    <span class="promo-badge promo-badge-limited-stock">📦 LIMITED STOCK</span>
                <?php endif; ?>
            </span>

            <!-- Top-Right Badge: Discount Percentage (e.g. -22%) -->
            <?php if ($promo && !empty($promo->discount_percent) && $promo->discount_percent > 0): ?>
                <span class="promo-badge-overlay-right" style="position: absolute !important; top: 8px !important; right: 8px !important; left: auto !important; bottom: auto !important; width: auto !important; height: auto !important; z-index: 15 !important; display: block !important; text-align: right !important; pointer-events: none !important; background: transparent !important; margin: 0 !important; padding: 0 !important;">
                    <span class="promo-discount-badge">-<?php echo $promo->discount_percent; ?>%</span>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <a href="<?= $product_url ?>">
            <img src="<?php echo $prod_image; ?>" class="img-responsive" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>">
        </a>

        <?php if (!$is_normal_layout): ?>
            <div class="quick-view-wrapper d-none">
                <a href="javascript:QuickViewProdDetails('<?php echo $prod->url_key; ?>','<?= $product_url ?>')" class="btn btn-default"><?= lang('view_label') ?></a>
            </div>
        <?php endif; ?>
    </div>

    <div class="pi-content-wrapper">
        <h3 id="product-name-<?php echo $prod->id; ?>" title="<?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?= $product_url ?>">
                <?php echo (strlen($display_name) > 35) ? substr($display_name, 0, 32) . '...' : $display_name; ?>
            </a>
        </h3>

        <!-- Price Display -->
        <div class="pi-price-wrapper">
            <?php if ($special_price > 0 && $regular_price > 0): ?>
                <div class="pi-regular-price"><del>MUR <?php echo number_format($regular_price, 2); ?></del></div>
                <div class="pi-special-price">MUR <?php echo number_format($special_price, 2); ?></div>
            <?php else: ?>
                <div class="pi-normal-price"><?php echo ($regular_price > 0) ? 'MUR ' . number_format($regular_price, 2) : 'Price on request'; ?></div>
            <?php endif; ?>
        </div>

        <!-- Normal Layout Badges (Screenshot 1) -->
        <?php if ($is_normal_layout): ?>
            <?php if ($promo): ?>
                <?php if ($promo->type === 'flash_sale'): ?>
                    <span class="promo-pill-normal promo-pill-flash-sale">⚡ FLASH SALE</span>
                <?php else: ?>
                    <span class="promo-pill-normal promo-pill-daily-deal">★ DAILY DEAL</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($stock): ?>
                <div class="promo-stock-warning">⚠️ Only <?php echo $stock->qty; ?> left</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Countdown Timer (Active for scheduled/ends_at promotions) -->
        <?php if ($promo && !empty($promo->ends_at) && $promo->ends_at > time()): ?>
            <div class="pi-countdown-timer" data-promo-ends-at="<?php echo $promo->ends_at; ?>" data-countdown-prefix="Ends in ">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span class="countdown-time">Ends in ...</span>
            </div>
        <?php elseif ($type === 'DailyDealsListing' && !empty($prod->daily_deal_ends_at)): ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('deal_ends') ? $this->lang->line('deal_ends') : 'Deal ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at)); ?></p>
        <?php elseif ($type === 'FlashSaleListing' && !empty($prod->flash_sale_ends_at)): ?>
            <p class="deal-ends mb-2" style="font-size: 0.85em; color: #555; margin-bottom: 8px;"><?php echo $this->lang->line('sale_ends') ? $this->lang->line('sale_ends') : 'Sale ends:'; ?> <?php echo date("d M Y, H:i", is_numeric($prod->flash_sale_ends_at) ? (int)$prod->flash_sale_ends_at : strtotime($prod->flash_sale_ends_at)); ?></p>
        <?php endif; ?>

        <!-- View Details Button -->
        <div class="pi-actions" style="margin-top: 4px;">
            <a href="<?= $product_url ?>" class="btn pi-btn-details"><?= lang('view_details') ?: 'View Details' ?></a>
        </div>

        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>
    </div>

</div>

<?php if (isset($check) && $check == 'owlView') { echo "</div>"; } ?>
