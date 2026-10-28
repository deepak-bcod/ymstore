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

// Stock check: if stock is less than 0 or out of stock
$is_out_of_stock = false;
$product_stock = null;
if (isset($prod->calculated_stock) && is_numeric($prod->calculated_stock)) {
    $product_stock = (float)$prod->calculated_stock;
} elseif (isset($prod->available_qty) && is_numeric($prod->available_qty)) {
    $product_stock = (float)$prod->available_qty;
} elseif (isset($prod->qty) && is_numeric($prod->qty) && (float)$prod->qty > 0) {
    $product_stock = (float)$prod->qty;
} elseif (isset($prod->stock_qty) && is_numeric($prod->stock_qty) && (float)$prod->stock_qty > 0) {
    $product_stock = (float)$prod->stock_qty;
} elseif (isset($prod->total_qty) && is_numeric($prod->total_qty) && (float)$prod->total_qty > 0) {
    $product_stock = (float)$prod->total_qty;
} elseif (isset($prod->quantity) && is_numeric($prod->quantity) && (float)$prod->quantity > 0) {
    $product_stock = (float)$prod->quantity;
}

if ($product_stock === null) {
    $prod_id_check = isset($prod->id) ? (int)$prod->id : (isset($prod->product_id) ? (int)$prod->product_id : 0);
    if ($prod_id_check > 0) {
        $ci =& get_instance();
        if (isset($ci->db)) {
            $child_inv = $ci->db->query("SELECT SUM(COALESCE(vi.available_qty, vi.qty, 0)) as total_stock FROM products v JOIN products_inventory vi ON vi.product_id = v.id WHERE v.parent_id = ? AND v.status = 1 AND v.remove_flag = 0", [$prod_id_check])->row();
            if ($child_inv && $child_inv->total_stock !== null && (float)$child_inv->total_stock > 0) {
                $product_stock = (float)$child_inv->total_stock;
            } else {
                $s_inv = $ci->db->select('available_qty, qty')->where('product_id', $prod_id_check)->get('products_inventory')->row();
                if ($s_inv) {
                    if ($s_inv->available_qty !== null && is_numeric($s_inv->available_qty)) {
                        $product_stock = (float)$s_inv->available_qty;
                    } elseif ($s_inv->qty !== null && is_numeric($s_inv->qty)) {
                        $product_stock = (float)$s_inv->qty;
                    }
                }
            }
        }
    }
}

if ($product_stock !== null && $product_stock <= 0) {
    $is_out_of_stock = true;
} elseif (isset($prod->stock_status) && (strtolower(trim($prod->stock_status)) == 'out of stock' || strtolower(trim($prod->stock_status)) == 'outofstock' || strtolower(trim($prod->stock_status)) == 'notavailable')) {
    $is_out_of_stock = true;
}

// Request-level cache for active Daily Deals and Flash Sales across the site
static $active_promos_cache = null;
$now = time();

if ($active_promos_cache === null) {
    $active_promos_cache = [];
    $ci =& get_instance();
    if (isset($ci->db)) {
        // Auto-expire deals in DB so expired deals are cleaned up and default special prices restored
        if (!isset($ci->Product_model)) {
            $ci->load->model('Product_model');
        }
        $ci->Product_model->auto_expire_deals($now);

        $query = $ci->db->select('p.id, p.daily_deals, p.daily_deal_starts_at, p.daily_deal_ends_at, p.flash_sale, p.flash_sale_starts_at, p.flash_sale_ends_at, p.stock_qty, p.qty, p.webshop_price, p.special_price, inv.available_qty as inv_available_qty, inv.qty as inv_qty')
            ->from('products p')
            ->join('products_inventory inv', 'inv.product_id = p.id', 'left')
            ->group_start()
                ->group_start()
                    ->where('p.daily_deals', 1)
                    ->group_start()
                        ->where('p.daily_deal_starts_at IS NULL', null, false)
                        ->or_where('p.daily_deal_starts_at <=', $now)
                    ->group_end()
                    ->where('p.daily_deal_ends_at >', $now)
                ->group_end()
                ->or_group_start()
                    ->where('p.flash_sale', 1)
                    ->group_start()
                        ->where('p.flash_sale_starts_at IS NULL', null, false)
                        ->or_where('p.flash_sale_starts_at <=', $now)
                    ->group_end()
                    ->where('p.flash_sale_ends_at >', $now)
                ->group_end()
            ->group_end()
            ->get();

        if ($query) {
            foreach ($query->result() as $row) {
                $active_promos_cache[$row->id] = $row;
            }
        }
    }
}

$prod_id = isset($prod->id) ? (int)$prod->id : (isset($prod->product_id) ? (int)$prod->product_id : 0);
$promo_db = ($prod_id > 0 && isset($active_promos_cache[$prod_id])) ? $active_promos_cache[$prod_id] : null;

// Determine if Flash Sale is active (strictly checking start and expiration)
$is_flash_sale = false;
$fs_ends_at = 0;
if ($promo_db && !empty($promo_db->flash_sale)) {
    if ((empty($promo_db->flash_sale_starts_at) || (int)$promo_db->flash_sale_starts_at <= $now) && (empty($promo_db->flash_sale_ends_at) || (int)$promo_db->flash_sale_ends_at > $now)) {
        $is_flash_sale = true;
        if (!empty($promo_db->flash_sale_ends_at)) $fs_ends_at = (int)$promo_db->flash_sale_ends_at;
    }
} elseif (!empty($prod->flash_sale) && $prod->flash_sale == 1) {
    if ((empty($prod->flash_sale_starts_at) || (int)$prod->flash_sale_starts_at <= $now) && (empty($prod->flash_sale_ends_at) || (int)$prod->flash_sale_ends_at > $now)) {
        $is_flash_sale = true;
        if (!empty($prod->flash_sale_ends_at)) $fs_ends_at = (int)$prod->flash_sale_ends_at;
    }
} elseif ($type === 'FlashSaleListing') {
    if ((empty($prod->flash_sale_starts_at) || (int)$prod->flash_sale_starts_at <= $now) && (empty($prod->flash_sale_ends_at) || (int)$prod->flash_sale_ends_at > $now)) {
        $is_flash_sale = true;
        if (!empty($prod->flash_sale_ends_at)) $fs_ends_at = (int)$prod->flash_sale_ends_at;
    }
} elseif ($promo && $promo->type === 'flash_sale') {
    if ((empty($promo->starts_at) || (int)$promo->starts_at <= $now) && (empty($promo->ends_at) || (int)$promo->ends_at > $now)) {
        $is_flash_sale = true;
        if (!empty($promo->ends_at)) $fs_ends_at = (int)$promo->ends_at;
    }
} elseif (!empty($prod->offer_type) && $prod->offer_type === 'flash_sale') {
    if ((empty($prod->flash_sale_starts_at) || (int)$prod->flash_sale_starts_at <= $now) && (empty($prod->flash_sale_ends_at) || (int)$prod->flash_sale_ends_at > $now)) {
        $is_flash_sale = true;
        if (!empty($prod->flash_sale_ends_at)) $fs_ends_at = (int)$prod->flash_sale_ends_at;
    }
}

// Determine if Daily Deal is active (strictly checking start and expiration)
$is_daily_deal = false;
$dd_ends_at = 0;
if ($promo_db && !empty($promo_db->daily_deals)) {
    if ((empty($promo_db->daily_deal_starts_at) || (int)$promo_db->daily_deal_starts_at <= $now) && (empty($promo_db->daily_deal_ends_at) || (int)$promo_db->daily_deal_ends_at > $now)) {
        $is_daily_deal = true;
        if (!empty($promo_db->daily_deal_ends_at)) $dd_ends_at = (int)$promo_db->daily_deal_ends_at;
    }
} elseif (!empty($prod->daily_deals) && $prod->daily_deals == 1) {
    if ((empty($prod->daily_deal_starts_at) || (int)$prod->daily_deal_starts_at <= $now) && (empty($prod->daily_deal_ends_at) || (int)$prod->daily_deal_ends_at > $now)) {
        $is_daily_deal = true;
        if (!empty($prod->daily_deal_ends_at)) $dd_ends_at = (int)$prod->daily_deal_ends_at;
    }
} elseif ($type === 'DailyDealsListing') {
    if ((empty($prod->daily_deal_starts_at) || (int)$prod->daily_deal_starts_at <= $now) && (empty($prod->daily_deal_ends_at) || (int)$prod->daily_deal_ends_at > $now)) {
        $is_daily_deal = true;
        if (!empty($prod->daily_deal_ends_at)) $dd_ends_at = (int)$prod->daily_deal_ends_at;
    }
} elseif ($promo && $promo->type === 'daily_deal') {
    if ((empty($promo->starts_at) || (int)$promo->starts_at <= $now) && (empty($promo->ends_at) || (int)$promo->ends_at > $now)) {
        $is_daily_deal = true;
        if (!empty($promo->ends_at)) $dd_ends_at = (int)$promo->ends_at;
    }
} elseif (!empty($prod->offer_type) && $prod->offer_type === 'daily_deal') {
    if ((empty($prod->daily_deal_starts_at) || (int)$prod->daily_deal_starts_at <= $now) && (empty($prod->daily_deal_ends_at) || (int)$prod->daily_deal_ends_at > $now)) {
        $is_daily_deal = true;
        if (!empty($prod->daily_deal_ends_at)) $dd_ends_at = (int)$prod->daily_deal_ends_at;
    }
}

// If product is out of stock, it cannot have active daily deal or flash sale badges/timers
if ($is_out_of_stock) {
    $is_flash_sale = false;
    $is_daily_deal = false;
}

// Stock extraction for Limited Stock and Flash Sale stock indicator
$remaining_stock = null;
if (isset($prod->calculated_stock) && is_numeric($prod->calculated_stock) && (float)$prod->calculated_stock > 0) {
    $remaining_stock = (int)$prod->calculated_stock;
} elseif (isset($prod->available_qty) && is_numeric($prod->available_qty) && (float)$prod->available_qty > 0) {
    $remaining_stock = (int)$prod->available_qty;
} elseif ($product_stock !== null && $product_stock > 0) {
    $remaining_stock = (int)$product_stock;
} elseif ($promo_db && isset($promo_db->inv_available_qty) && is_numeric($promo_db->inv_available_qty) && $promo_db->inv_available_qty > 0) {
    $remaining_stock = (int)$promo_db->inv_available_qty;
} elseif ($promo_db && isset($promo_db->inv_qty) && is_numeric($promo_db->inv_qty) && $promo_db->inv_qty > 0) {
    $remaining_stock = (int)$promo_db->inv_qty;
} elseif ($stock && !empty($stock->qty) && is_numeric($stock->qty) && (int)$stock->qty > 0) {
    $remaining_stock = (int)$stock->qty;
} elseif (isset($prod->stock_qty) && is_numeric($prod->stock_qty) && (float)$prod->stock_qty > 0) {
    $remaining_stock = (int)$prod->stock_qty;
} elseif (isset($prod->qty) && is_numeric($prod->qty) && (float)$prod->qty > 0) {
    $remaining_stock = (int)$prod->qty;
} elseif (isset($prod->quantity) && is_numeric($prod->quantity) && (float)$prod->quantity > 0) {
    $remaining_stock = (int)$prod->quantity;
} elseif (isset($prod->total_qty) && is_numeric($prod->total_qty) && (float)$prod->total_qty > 0) {
    $remaining_stock = (int)$prod->total_qty;
} elseif ($promo_db && isset($promo_db->stock_qty) && is_numeric($promo_db->stock_qty) && $promo_db->stock_qty > 0) {
    $remaining_stock = (int)$promo_db->stock_qty;
} elseif ($promo_db && isset($promo_db->qty) && is_numeric($promo_db->qty) && $promo_db->qty > 0) {
    $remaining_stock = (int)$promo_db->qty;
}

if ($remaining_stock === null && $prod_id > 0 && isset($ci->db)) {
    $child_inv = $ci->db->query("SELECT SUM(COALESCE(vi.available_qty, vi.qty, 0)) as total_stock FROM products v JOIN products_inventory vi ON vi.product_id = v.id WHERE v.parent_id = ? AND v.status = 1 AND v.remove_flag = 0", [$prod_id])->row();
    if ($child_inv && $child_inv->total_stock !== null && (float)$child_inv->total_stock > 0) {
        $remaining_stock = (int)$child_inv->total_stock;
    } else {
        $single_inv = $ci->db->select('available_qty, qty')->where('product_id', $prod_id)->get('products_inventory')->row();
        if ($single_inv) {
            if (isset($single_inv->available_qty) && is_numeric($single_inv->available_qty) && $single_inv->available_qty > 0) {
                $remaining_stock = (int)$single_inv->available_qty;
            } elseif (isset($single_inv->qty) && is_numeric($single_inv->qty) && $single_inv->qty > 0) {
                $remaining_stock = (int)$single_inv->qty;
            }
        }
    }
}

// Limited stock condition (stock <= 10 when not in flash sale or daily deal)
$is_limited_stock = (!$is_flash_sale && !$is_daily_deal && !$is_out_of_stock && $remaining_stock !== null && $remaining_stock > 0 && $remaining_stock <= 10);

// End timestamp for countdown timer (only for daily deals per rule: daily deals show timer, flash sales show count left only)
$timer_ends_at = 0;
if ($is_daily_deal) {
    if ($dd_ends_at > $now) {
        $timer_ends_at = $dd_ends_at;
    } elseif ($promo && !empty($promo->ends_at) && (int)$promo->ends_at > $now) {
        $timer_ends_at = (int)$promo->ends_at;
    }
}

$initial_countdown_str = '';
if ($timer_ends_at > $now) {
    $diff = $timer_ends_at - $now;
    if ($diff >= 86400) {
        $days = floor($diff / 86400);
        $initial_countdown_str = $days . ($days == 1 ? ' day' : ' days');
    } else {
        $hours = floor($diff / 3600);
        $mins = floor(($diff % 3600) / 60);
        $secs = $diff % 60;
        $initial_countdown_str = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }
}

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
} elseif ($promo_db && !empty($promo_db->special_price) && (float)$promo_db->special_price > 0 && $price_to_show > (float)$promo_db->special_price) {
    $special_price_to_show = (float)$promo_db->special_price;
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

        <!-- Badges inside product image at TOP (Daily Deals, Flash Sales, Limited Stock, Out of Stock, Discount Pill) -->
        <?php if ($is_out_of_stock || $is_daily_deal || $is_flash_sale || $is_limited_stock || ($discount_percent > 0 && !$is_out_of_stock)): ?>
            <span class="pi-img-top-badges" style="position: absolute !important; top: 6px !important; left: 6px !important; right: 6px !important; display: flex !important; align-items: flex-start !important; justify-content: space-between !important; gap: 4px !important; z-index: 8 !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; width: calc(100% - 12px) !important; background: transparent !important;">
                <span style="display: flex !important; align-items: center !important; gap: 4px !important; flex-wrap: wrap !important;">
                    <?php if ($is_out_of_stock): ?>
                        <span class="badge-out-of-stock" style="background-color: #dc3545 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">OUT OF STOCK</span>
                    <?php else: ?>
                        <?php if ($is_flash_sale): ?>
                            <span class="badge-deal-flash" <?php if ($fs_ends_at > 0): ?>data-ends-at="<?= $fs_ends_at ?>"<?php endif; ?> style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;"><i class="fa fa-bolt" style="color: #ffd200 !important; font-size: 11px !important;"></i> FLASH SALE</span>
                        <?php endif; ?>
                        <?php if ($is_daily_deal): ?>
                            <span class="badge-deal-daily" <?php if ($dd_ends_at > 0): ?>data-ends-at="<?= $dd_ends_at ?>"<?php endif; ?> style="background-color: #ffd200 !important; color: #000000 !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;"><i class="fa fa-star" style="color: #000000 !important; font-size: 10px !important;"></i> DAILY DEAL</span>
                        <?php endif; ?>
                        <?php if ($is_limited_stock): ?>
                            <span class="badge-limited-stock" style="background-color: #28a745 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;"><i class="fa fa-archive" style="color: #ffffff !important; font-size: 10px !important;"></i> LIMITED STOCK</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>

                <?php if ($discount_percent > 0 && !$is_out_of_stock): ?>
                    <span style="margin-left: auto !important; display: inline-block !important;">
                        <span class="badge-discount-pill" style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 11px !important; font-weight: 800 !important; padding: 2px 7px !important; border-radius: 12px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">-<?= $discount_percent ?>%</span>
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
        <div class="pi-price" style="margin: 2px 0 4px 0; line-height: 1.25;">
            <?php 
            if ($special_price_to_show > 0 && $price_to_show > 0) {
                // Show Regular price with strikethrough in grey, Special price in bold orange
                echo '<div style="color: #8c9196; font-size: 12px; margin-bottom: 2px;"><del>MUR ' . number_format($price_to_show, 2) . '</del></div>'; 
                echo '<div style="color: #ff9e0c; font-size: 15px; font-weight: 700;">MUR ' . number_format($special_price_to_show, 2) . '</div>';
            } else {
                echo '<div style="color: #ff9e0c; font-size: 15px; font-weight: 700;">' . (($price_to_show > 0) ? 'MUR ' . number_format($price_to_show, 2) : 'Price on request') . '</div>';
            }
            ?>
        </div>

        <?php 
        $ci =& get_instance();
        $is_home_page = (
            (isset($ci->router) && $ci->router->fetch_class() === 'HomeController' && $ci->router->fetch_method() === 'index')
            || uri_string() === ''
            || uri_string() === '/'
            || (isset($_SERVER['REQUEST_URI']) && (trim($_SERVER['REQUEST_URI'], '/') === '' || parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/'))
        );
        $is_search = ($type === 'SearchListing' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'searchresult') !== false));
        $is_centered_listing = (!$is_home_page && ($type === 'TrendingListing' || $type === 'NewArrivalListing' || $is_search));
        $item_align_justify = $is_centered_listing ? 'center' : 'flex-start';
        $item_align_text = $is_centered_listing ? 'center' : 'left';
        ?>

        <!-- Countdown Timer & Stock Indicator: Set in one line if both are active -->
        <?php 
        $has_deal_timer = ($is_daily_deal && $timer_ends_at > $now && !$is_out_of_stock);
        $has_deal_stock = ($remaining_stock !== null && $remaining_stock > 0 && ($is_flash_sale || ($is_limited_stock && !$is_daily_deal)) && !$is_out_of_stock);
        ?>
        <?php if ($has_deal_timer || $has_deal_stock): ?>
            <div class="promo-deal-info-container" style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: <?= $item_align_justify ?> !important; text-align: <?= $item_align_text ?> !important; gap: 8px !important; flex-wrap: nowrap !important; width: 100% !important; margin: 3px 0 5px 0 !important;">
                <?php if ($has_deal_timer): ?>
                    <div class="promo-countdown-wrapper" style="color: #e52525 !important; font-size: 11.5px !important; font-weight: normal !important; margin: 0 !important; display: inline-flex !important; align-items: center !important; gap: 4px !important; width: auto !important; white-space: nowrap !important;">
                        <i class="fa fa-clock-o" style="color: #e52525 !important; font-size: 12px !important;"></i>
                        <span>Ends in <span class="live-countdown" data-ends-at="<?= $timer_ends_at ?>"><?= $initial_countdown_str ?></span></span>
                    </div>
                <?php endif; ?>

                <?php if ($has_deal_stock): ?>
                    <div class="promo-stock-wrapper" style="color: #e52525 !important; font-size: 11.5px !important; font-weight: normal !important; margin: 0 !important; display: inline-flex !important; align-items: center !important; gap: 4px !important; width: auto !important; white-space: nowrap !important;">
                        <i class="fa fa-fire" style="color: #e52525 !important; font-size: 11px !important;"></i>
                        <span>Only <?= $remaining_stock ?> left</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Action Button -->
        <div class="promo-btn-container" style="margin-top: 6px !important; text-align: <?= $item_align_text ?> !important; width: 100% !important;">
            <?php if ($is_out_of_stock) : ?>
                <a href="<?= $product_url ?>" class="btn btn-default add2cart" style="background-color: #6c757d !important; border-color: #6c757d !important; color: #ffffff !important; cursor: pointer; display: block !important; width: 100% !important; text-align: center !important;"><?= $this->lang->line('out_of_stock_btn') ? $this->lang->line('out_of_stock_btn') : ($this->lang->line('out_of_stock_label') ? $this->lang->line('out_of_stock_label') : 'Out of Stock'); ?></a>
            <?php elseif ($type === 'PrelaunchListing') : ?>
                <?php if ($prod->product_type === 'simple') { ?>
                    <a href="<?= $product_url ?>" data-prelaunch="yes" <?php echo $prod->product_type; ?> data-product-id="<?php echo $prod->id; ?>" data-qty="1" class="btn btn-default add2cart"><?= lang('view_details') ?></a>
                <?php } else { ?>
                    <a href="javascript:;" onclick="gotoLocation('<?php echo BASE_URL . 'product-detail/' . $prod->url_key . '?type=prelaunch'; ?>');" class="btn btn-default add2cart"><?= lang('view_details') ?></a>
                <?php } ?>
            <?php else : ?>
                <?php if ($is_centered_listing) : ?>
                    <a href="<?= $product_url ?>" class="btn view-product-btn ym-view-details-btn" style="display: block !important; width: 100% !important; min-height: 40px !important; padding: 10px 15px !important; margin: 0 auto !important; border-radius: 4px !important; font-size: 15px !important; font-weight: 500 !important; line-height: 20px !important; text-align: center !important; text-decoration: none !important; box-sizing: border-box !important; cursor: pointer !important; transition: all 0.2s ease-in-out !important;"><?= lang('view_details') ?></a>
                <?php else : ?>
                    <a href="<?= $product_url ?>" class="btn btn-primary add2cart" style="background-color: #0d6efd !important; border-color: #0d6efd !important; color: #ffffff !important; font-size: 12px !important; font-weight: 600 !important; padding: 6px 14px !important; border-radius: 4px !important; text-transform: capitalize !important; line-height: 1.35 !important; display: inline-block !important; width: fit-content !important; text-align: center !important; margin: 0 !important;"><?= lang('view_details') ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <style>
        .view-product-btn.ym-view-details-btn {
            background: #ffffff !important;
            border: 1px solid #777777 !important;
            color: #555555 !important;
            transition: all 0.2s ease-in-out !important;
        }
        .view-product-btn.ym-view-details-btn:hover,
        .view-product-btn.ym-view-details-btn:focus,
        .view-product-btn.ym-view-details-btn:active {
            background: #0f5cd0 !important;
            border-color: #0f5cd0 !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }
        </style>

        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>

    </div>

</div>

<?php if (isset($check) && $check == 'owlView') {
    echo "</div>";
} ?>

<script>
if (typeof window.hasLiveCountdownInit === 'undefined') {
    window.hasLiveCountdownInit = true;
    (function() {
        function tickCountdowns() {
            var now = Math.floor(Date.now() / 1000);
            // Check individual promo badges with data-ends-at
            var promoBadges = document.querySelectorAll('.badge-deal-flash[data-ends-at], .badge-deal-daily[data-ends-at]');
            for (var j = 0; j < promoBadges.length; j++) {
                var pb = promoBadges[j];
                var bEnds = parseInt(pb.getAttribute('data-ends-at'), 10);
                if (bEnds && bEnds <= now) {
                    var pItem = pb.closest('.product-item');
                    var isFlash = pb.classList.contains('badge-deal-flash');
                    var isDaily = pb.classList.contains('badge-deal-daily');
                    pb.remove();
                    if (pItem) {
                        var ddSec = pItem.closest('#sm_filterproducts_daily_deals');
                        var fsSec = pItem.closest('#sm_filterproducts_flash_sales');
                        if ((ddSec && isDaily) || (fsSec && isFlash)) {
                            var owlItm = pItem.closest('.owl-item') || pItem;
                            owlItm.style.display = 'none';
                        }
                        if (!pItem.querySelector('.badge-deal-flash') && !pItem.querySelector('.badge-deal-daily')) {
                            var dPill = pItem.querySelector('.badge-discount-pill');
                            if (dPill) dPill.remove();
                        }
                    }
                }
            }

            var els = document.querySelectorAll('.live-countdown[data-ends-at]');
            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                var endsAt = parseInt(el.getAttribute('data-ends-at'), 10);
                if (!endsAt || isNaN(endsAt)) continue;
                var diff = endsAt - now;
                if (diff <= 0) {
                    var wrap = el.closest('.promo-countdown-wrapper');
                    if (wrap) wrap.style.display = 'none';
                    continue;
                }
                if (diff >= 86400) {
                    var days = Math.floor(diff / 86400);
                    el.textContent = days + (days === 1 ? ' day' : ' days');
                } else {
                    var h = Math.floor(diff / 3600);
                    var m = Math.floor((diff % 3600) / 60);
                    var s = diff % 60;
                    var pad = function(n) { return (n < 10 ? '0' : '') + n; };
                    el.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
                }
            }
        }
        setInterval(tickCountdowns, 1000);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tickCountdowns);
        } else {
            tickCountdowns();
        }
    })();
}
</script>
