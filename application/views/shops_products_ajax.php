<?php if (!empty($shops)): ?>

<style>

.category-product .product-item {
    height: auto !important;
    min-height: 0 !important;
}

.category-product .product-item-info {
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
}

.category-product .item-inner {
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
}

.category-product .box-info {
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    padding-bottom: 15px !important;
}

.category-product .product-item-details {
    height: auto !important;
    overflow: visible !important;
}

.category-product .product-item-name,
.category-product .product-name {
    display: block !important;
    width: 100% !important;
    height: 22px !important;
    min-height: 22px !important;
    max-height: 22px !important;

    margin: 0 0 8px 0 !important;
    padding: 0 5px !important;

    overflow: hidden !important;
    text-align: center !important;
    line-height: 22px !important;

    box-sizing: border-box !important;
}

.category-product .product-item-link {
    display: block !important;

    width: 100% !important;
    height: 22px !important;
    line-height: 22px !important;

    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;

    text-align: center !important;

    font-weight: 500 !important;
    color: #333333 !important;
    text-decoration: none !important;

    box-sizing: border-box !important;
}

.category-product .box-image {
    position: relative !important;
    overflow: hidden !important;
    text-align: center !important;
    width: 100% !important;
    background: #fff !important;
}

.category-product .box-image .product-item-photo {
    display: block !important;
    position: relative !important;
    width: 100% !important;
}

.category-product .box-image .product-image-container {
    display: block !important;
    margin: 0 auto !important;
}


.category-product .box-image .product-image-photo {
    display: block !important;
    width: 100% !important;
    height: 250px !important;
    object-fit: contain !important;
    opacity: 1 !important;
    transform: none !important;
    transition: none !important;
   
 
}

.category-product .box-image::after {
    content: "";

    position: absolute !important;

    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;

    background: rgba(0, 0, 0, 0.35) !important;

    opacity: 0 !important;
    visibility: hidden !important;

    transition:
        opacity 0.2s ease,
        visibility 0.2s ease !important;

    z-index: 5 !important;

    pointer-events: none !important;
}


.category-product .box-image:hover::after {
    opacity: 1 !important;
    visibility: visible !important;
}

.category-product .product-item:has(.view-product-btn:hover) .box-image::after {
    opacity: 1 !important;
    visibility: visible !important;
}

.category-product .quick-view-btn {
    position: absolute !important;

    top: 50% !important;
    left: 50% !important;

    transform: translate(-50%, -50%) !important;

    background: rgba(0, 0, 0, 0.35) !important;
    color: #fff !important;

    padding: 9px 14px !important;

    border: 1px solid #fff !important;
    border-radius: 3px !important;

    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 20px !important;

    text-decoration: none !important;
    cursor: pointer !important;

    z-index: 20 !important;

    opacity: 0 !important;
    visibility: hidden !important;

    transition:
        opacity 0.2s ease,
        visibility 0.2s ease,
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease !important;
}

.category-product .box-image:hover .quick-view-btn {
    opacity: 1 !important;
    visibility: visible !important;
}

.category-product .product-item:has(.view-product-btn:hover) .box-image .quick-view-btn {
    opacity: 1 !important;
    visibility: visible !important;
}

.category-product .quick-view-btn:hover,
.category-product .quick-view-btn:focus {
    background: #ffd200 !important;
    color: #000 !important;
    border-color: #ffd200 !important;
    text-decoration: none !important;
    outline: none !important;
}

.category-product .view-product-wrapper {
    display: block !important;

    width: 100% !important;

    height: auto !important;
    min-height: 0 !important;

    margin-top: 15px !important;
    margin-bottom: 15px !important;

    padding: 0 !important;

    text-align: center !important;

    clear: both !important;

    overflow: visible !important;
}


.category-product .view-product-btn {

    display: block !important;

    visibility: visible !important;
    opacity: 1 !important;

    width: 100% !important;

    height: auto !important;
    min-height: 40px !important;

    padding: 10px 15px !important;

    margin: 0 !important;

    background: #ffffff !important;

    border: 1px solid #777777 !important;

    border-radius: 3px !important;

    color: #555555 !important;

    font-size: 16px !important;
    font-weight: 400 !important;
    line-height: 20px !important;

    text-align: center !important;

    text-decoration: none !important;

    box-sizing: border-box !important;

    cursor: pointer !important;

    transition:
        all 0.2s ease-in-out !important;
}

.category-product .view-product-btn:hover,
.category-product .view-product-btn:focus {

    background: #0f5cd0 !important;

    border-color: #0f5cd0 !important;

    color: #ffffff !important;

    text-decoration: none !important;

    outline: none !important;
}


.category-product .product-item,
.category-product .product-item-info,
.category-product .item-inner,
.category-product .box-info {

    overflow: visible !important;
}

</style>

<div class="category-product products wrapper grid products-grid">

    <ol class="products list items product-items row">


        <?php foreach ($shops as $product): ?>

            <?php
            $now = time();
            $is_out_of_stock = false;
            $product_stock = null;
            if (isset($product->qty) && is_numeric($product->qty)) {
                $product_stock = (float)$product->qty;
            } elseif (isset($product->stock_qty) && is_numeric($product->stock_qty)) {
                $product_stock = (float)$product->stock_qty;
            } elseif (isset($product->total_qty) && is_numeric($product->total_qty)) {
                $product_stock = (float)$product->total_qty;
            }

            if ($product_stock === null && !empty($product->id)) {
                $ci =& get_instance();
                if (isset($ci->db)) {
                    if (isset($product->product_type) && $product->product_type === 'configurable') {
                        $child_inv = $ci->db->query("SELECT SUM(COALESCE(vi.available_qty, vi.qty, 0)) as total_stock FROM products v JOIN products_inventory vi ON vi.product_id = v.id WHERE v.parent_id = ?", [$product->id])->row();
                        if ($child_inv && $child_inv->total_stock !== null) {
                            $product_stock = (float)$child_inv->total_stock;
                        }
                    } else {
                        $s_inv = $ci->db->select('available_qty, qty')->where('product_id', $product->id)->get('products_inventory')->row();
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

            if ($product_stock !== null && $product_stock <= 0) {
                $is_out_of_stock = true;
            } elseif (isset($product->stock_status) && in_array(strtolower(trim($product->stock_status)), ['out of stock', 'outofstock', 'notavailable'])) {
                $is_out_of_stock = true;
            }

            $is_daily_deal = (!$is_out_of_stock && !empty($product->daily_deals) && $product->daily_deals == 1 && (empty($product->daily_deal_starts_at) || (int)$product->daily_deal_starts_at <= $now) && (empty($product->daily_deal_ends_at) || (int)$product->daily_deal_ends_at > $now));
            $dd_ends_at = $is_daily_deal ? (int)$product->daily_deal_ends_at : 0;

            $is_flash_sale = (!$is_out_of_stock && !empty($product->flash_sale) && $product->flash_sale == 1 && (empty($product->flash_sale_starts_at) || (int)$product->flash_sale_starts_at <= $now) && (empty($product->flash_sale_ends_at) || (int)$product->flash_sale_ends_at > $now));
            $fs_ends_at = $is_flash_sale ? (int)$product->flash_sale_ends_at : 0;

            $remaining_stock = ($product_stock !== null && $product_stock > 0) ? (int)$product_stock : null;
            $is_limited_stock = (!$is_flash_sale && !$is_daily_deal && !$is_out_of_stock && $remaining_stock !== null && $remaining_stock <= 10);

            // Calculate final price & special price for discount percentage
            if ($product->product_type == 'configurable') {
                $finalPrice = !empty($product->price_sorting_configurable) ? $product->price_sorting_configurable : $product->webshop_price;
            } else {
                $finalPrice = !empty($product->price_sorting_simple) ? $product->price_sorting_simple : $product->webshop_price;
            }
            $specialPrice = !empty($product->special_price) ? (float)$product->special_price : 0;
            $discount_percent = 0;
            if ($specialPrice > 0 && (float)$finalPrice > $specialPrice) {
                $discount_percent = (int)round((((float)$finalPrice - $specialPrice) / (float)$finalPrice) * 100);
            } elseif ($specialPrice > 0 && !empty($product->webshop_price) && (float)$product->webshop_price > $specialPrice) {
                $discount_percent = (int)round((((float)$product->webshop_price - $specialPrice) / (float)$product->webshop_price) * 100);
            }

            // Initial countdown string for daily deal
            $initial_countdown_str = '';
            if ($dd_ends_at > $now) {
                $diff = $dd_ends_at - $now;
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
            ?>

            <li class="item product product-item col-md-3">


                <div
                    class="product-item-info"
                    data-container="product-grid"
                >


                    <div class="item-inner">


                        <div class="box-image" style="position: relative !important;">

                            <!-- TOP BADGES: Daily Deals, Flash Sales, Limited Stock, Out of Stock, Discount Pill -->
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


                            <a
                                href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                class="product photo product-item-photo"
                            >


                                <span
                                    class="product-image-container"
                                    style="width: 240px;"
                                >


                                    <span
                                        class="product-image-wrapper"
                                        style="padding-bottom: 100%;"
                                    >


                                        <img
                                            class="product-image-photo"
                                            src="<?php
                                            echo BASE_URL .
                                                'uploads/products/thumb/' .
                                                $product->base_image;
                                            ?>"
                                            alt="<?php
                                            echo htmlspecialchars(
                                                $product->name,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>"
                                        >


                                    </span>


                                </span>


                            </a>

                            <a
                                href="javascript:void(0);"
                                class="quick-view-btn"
                                onclick="QuickViewProdDetails(
                                    '<?php
                                    echo htmlspecialchars(
                                        $product->url_key,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>',
                                    '<?php
                                    echo BASE_URL .
                                        'product-detail/' .
                                        $product->url_key;
                                    ?>'
                                );"
                            >

                                <?php
                                echo $this->lang->line('view_label');
                                ?>

                            </a>


                        </div>

                        <div
                            class="product details product-item-details box-info"
                        >
                            <h2
                                class="product name product-item-name product-name"
                            >

                                <a
                                    class="product-item-link"
                                    href="<?php
                                    echo BASE_URL .
                                        'product-detail/' .
                                        $product->url_key;
                                    ?>"
                                >

                                    <?php

                                    $current_lang =
                                        $this->session->userdata(
                                            'site_lang'
                                        );


                                    if (
                                        $current_lang == 'french' &&
                                        !empty($product->lang_title)
                                    ) {

                                        echo htmlspecialchars(
                                            $product->lang_title,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    } else {

                                        echo htmlspecialchars(
                                            $product->name,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                    }

                                    ?>

                                </a>

                            </h2>

                            <div class="price-box price-final_price">


                                <?php

                                if (
                                    $product->product_type ==
                                    'configurable'
                                ) {

                                    $finalPrice =
                                        !empty(
                                            $product
                                                ->price_sorting_configurable
                                        )
                                            ? $product
                                                ->price_sorting_configurable
                                            : $product->webshop_price;

                                } else {

                                    $finalPrice =
                                        !empty(
                                            $product
                                                ->price_sorting_simple
                                        )
                                            ? $product
                                                ->price_sorting_simple
                                            : $product->webshop_price;

                                }


                                $specialPrice =
                                    !empty($product->special_price)
                                        ? $product->special_price
                                        : 0;


                                if ($specialPrice > 0):

                                ?>


                                    <!-- SPECIAL PRICE -->

                                    <span class="special-price">

                                        <span class="price">

                                            MUR

                                            <?php
                                            echo number_format(
                                                $specialPrice,
                                                2
                                            );
                                            ?>

                                        </span>

                                    </span>


                                    <!-- OLD PRICE -->

                                    <span class="old-price">

                                        <span
                                            class="price"
                                            style="text-decoration: line-through;"
                                        >

                                            MUR

                                            <?php
                                            echo number_format(
                                                $product->webshop_price,
                                                2
                                            );
                                            ?>

                                        </span>

                                    </span>


                                <?php else: ?>


                                    <!-- REGULAR PRICE -->

                                    <span class="regular-price">

                                        <span class="price">

                                            MUR

                                            <?php
                                            echo number_format(
                                                $finalPrice,
                                                2
                                            );
                                            ?>

                                        </span>

                                    </span>


                                <?php endif; ?>


                            </div>

                            <!-- COUNTDOWN TIMER: If Daily Deal is active (or both Daily Deal & Flash Sale) -->
                            <?php if ($is_daily_deal && $dd_ends_at > $now && !$is_out_of_stock): ?>
                                <div class="promo-countdown-wrapper" style="color: #e52525 !important; font-size: 12px !important; font-weight: normal !important; margin: 3px 0 4px 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; gap: 4px !important; width: 100% !important;">
                                    <i class="fa fa-clock-o" style="color: #e52525 !important; font-size: 13px !important;"></i>
                                    <span>Ends in <span class="live-countdown" data-ends-at="<?= $dd_ends_at ?>"><?= $initial_countdown_str ?></span></span>
                                </div>
                            <?php endif; ?>

                            <!-- PRODUCT COUNT LEFT: If Flash Sale is active (or both Daily Deal & Flash Sale) -->
                            <?php if ($remaining_stock !== null && $remaining_stock > 0 && ($is_flash_sale || ($is_limited_stock && !$is_daily_deal)) && !$is_out_of_stock): ?>
                                <div class="promo-stock-wrapper" style="color: #e52525 !important; font-size: 11.5px !important; font-weight: normal !important; margin: 2px 0 5px 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; gap: 4px !important; width: 100% !important;">
                                    <i class="fa fa-fire" style="color: #e52525 !important; font-size: 11px !important;"></i>
                                    <span>Only <?= $remaining_stock ?> left</span>
                                </div>
                            <?php endif; ?>

                            <div class="view-product-wrapper">

                                <?php if ($is_out_of_stock): ?>
                                    <a
                                        href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                        class="view-product-btn"
                                        style="background-color: #6c757d !important; border-color: #6c757d !important; color: #ffffff !important;"
                                    >
                                        <?= $this->lang->line('out_of_stock_btn') ? $this->lang->line('out_of_stock_btn') : ($this->lang->line('out_of_stock_label') ? $this->lang->line('out_of_stock_label') : 'Out of Stock'); ?>
                                    </a>
                                <?php else: ?>
                                    <a
                                        href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                        class="view-product-btn"
                                    >
                                        <?php
                                        echo $this->lang->line(
                                            'view_details'
                                        );
                                        ?>
                                    </a>
                                <?php endif; ?>

                            </div>


                        </div>


                        <!-- END PRODUCT DETAILS -->


                    </div>


                    <!-- END ITEM INNER -->


                </div>


                <!-- END PRODUCT ITEM INFO -->


            </li>


        <?php endforeach; ?>


    </ol>

</div>

<div class="pagination-wrap">

    <?php echo $pagination; ?>

</div>


<?php else: ?>


    <p>

        <?php
        echo $this->lang->line('no_products_shop');
        ?>

<?php endif; ?>

<script>
if (typeof window.hasLiveCountdownInit === 'undefined') {
    window.hasLiveCountdownInit = true;
    (function() {
        function tickCountdowns() {
            var now = Math.floor(Date.now() / 1000);
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