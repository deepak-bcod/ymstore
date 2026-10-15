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

// Promotional countdown ends_at calculation
$ends_at = null;
if ($promo && !empty($promo->ends_at) && $promo->ends_at > time()) {
    $ends_at = (int)$promo->ends_at;
} elseif ($type === 'DailyDealsListing' && !empty($prod->daily_deal_ends_at)) {
    $parsed_ends = is_numeric($prod->daily_deal_ends_at) ? (int)$prod->daily_deal_ends_at : strtotime($prod->daily_deal_ends_at);
    if ($parsed_ends > time()) {
        $ends_at = $parsed_ends;
    }
} elseif ($type === 'FlashSaleListing' && !empty($prod->flash_sale_ends_at)) {
    $parsed_ends = is_numeric($prod->flash_sale_ends_at) ? (int)$prod->flash_sale_ends_at : strtotime($prod->flash_sale_ends_at);
    if ($parsed_ends > time()) {
        $ends_at = $parsed_ends;
    }
}

$is_normal_layout = (isset($viewmode) && $viewmode === 'list') || (isset($check) && $check === 'normalView');
$card_class = $is_normal_layout ? 'product-item product-item-normal' : 'product-item product-item-grid';
?>

<?php if (isset($check) && $check == 'owlView') { echo "<div>"; } ?>

<div class="product-item uniform-product-card <?php echo $card_class; ?>" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- LEFT COLUMN: Product Image -->
    <div class="pi-img-wrapper">
        <a href="<?= $product_url ?>" class="pi-img-link">
            <img src="<?php echo $prod_image; ?>" class="img-responsive pi-thumb-img" title="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name, ENT_QUOTES, 'UTF-8'); ?>">
        </a>
    </div>

    <!-- RIGHT COLUMN: Details -->
    <div class="pi-content-wrapper">
        <!-- Badges Row: Promotion badge & discount percentage or stock -->
        <div class="pi-badges-row">
            <?php if ($promo): ?>
                <?php if ($promo->type === 'flash_sale'): ?>
                    <span class="promo-badge promo-badge-flash-sale">⚡ FLASH SALE</span>
                <?php else: ?>
                    <span class="promo-badge promo-badge-daily-deal">★ DAILY DEAL</span>
                <?php endif; ?>
                <?php if (!empty($promo->discount_percent) && $promo->discount_percent > 0): ?>
                    <span class="promo-discount-badge">-<?php echo $promo->discount_percent; ?>%</span>
                <?php endif; ?>
            <?php elseif ($stock): ?>
                <span class="promo-badge promo-badge-limited-stock">📦 LIMITED STOCK</span>
                <?php if (!empty($stock->qty)): ?>
                    <span class="promo-stock-qty-text">Only <?php echo $stock->qty; ?> left</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Product Name -->
        <h3 class="pi-title" id="product-name-<?php echo $prod->id; ?>" title="<?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?= $product_url ?>">
                <?php echo (strlen($display_name) > 38) ? substr($display_name, 0, 35) . '...' : $display_name; ?>
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

        <!-- Stock Information (if no promo active but stock warning present) -->
        <?php if (!$promo && $stock && !empty($stock->qty)): ?>
            <div class="pi-stock-status">
                <span class="promo-stock-warning">⚠️ Only <?php echo $stock->qty; ?> left in stock</span>
            </div>
        <?php endif; ?>

        <!-- Active Countdown Timer -->
        <?php if (!empty($ends_at) && $ends_at > time()): ?>
            <div class="pi-countdown-timer" data-promo-ends-at="<?php echo $ends_at; ?>" data-countdown-prefix="Ends in ">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span class="countdown-time">Ends in ...</span>
            </div>
        <?php endif; ?>

        <!-- Action / View Details Button -->
        <div class="pi-actions">
            <a href="<?= $product_url ?>" class="btn pi-btn-details"><?= lang('view_details') ?: 'View Details' ?></a>
        </div>

        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>
    </div>

</div>

<?php if (isset($check) && $check == 'owlView') { echo "</div>"; } ?>
