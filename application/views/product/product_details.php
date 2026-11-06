<?php $this->load->view('common/header'); ?>

<style>
    .badge-section {
        display: flex;
        flex-wrap: nowrap;
        /* ❌ prevent wrapping */
        gap: 10px;
        margin-top: 10px;
        align-items: center;
        /* vertical align */
    }

    .product-badge {
        width: 80px;
        /* adjust as needed */
        height: auto;
    }
</style>

<?php
//echo "<pre>";
$lang = $this->session->userdata('site_lang');  
// print_r($ProductData); 
// echo "<pre>"; print_r($ProductData);die;
?>
<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><?php echo $this->lang->line('home'); ?></a></li>

            <?php
            (new ProductDetails())->breadcrum($ProductData->id);
            ?>
            <li class="active">
                <?php echo ($lang == 'french' && !empty($ProductData->lang_title)) 
                ? $ProductData->lang_title 
                : $ProductData->name; ?>
            </li>
        </ul>

        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="product-page pad20">
                    <div class="row">

                        <?php if ($this->session->userdata('addtocart_error')) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $this->session->userdata('addtocart_error'); ?>
                            </div>
                        <?php $this->session->unset_userdata('addtocart_error');
                        } ?>

                        <div class="col-md-12">
                            <h1 class="product-name">
                                <?php
                                if ($lang == 'french' && !empty($ProductData->lang_title)) {
                                    echo $ProductData->lang_title;
                                } else {
                                    echo $ProductData->name;
                                }
                                ?>
                            </h1>
                            <?php 
                                $top_p_avg = !empty($product_rating->avg_rating) ? (float)$product_rating->avg_rating : 0;
                                $top_p_total = !empty($product_rating->total_reviews) ? (int)$product_rating->total_reviews : 0;
                                $top_p_half = round($top_p_avg * 2) / 2;
                            ?>
                            <div class="product-top-rating" style="margin: 6px 0 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="color: #ffbf00; font-size: 16px; letter-spacing: 2px;" title="Rating: <?= $top_p_avg ?> / 5 (<?= $top_p_total ?> reviews)">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($top_p_half >= $i): ?>
                                            <i class="fa fa-star"></i>
                                        <?php elseif ($top_p_half == $i - 0.5): ?>
                                            <i class="fa fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-star" style="color: #ddd;"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <a href="#reviews" class="jump-to-reviews-btn" style="font-size: 13px; color: #087bf3; text-decoration: none; font-weight: 500;">
                                    (<?= $top_p_total ?> <?= $top_p_total == 1 ? 'review' : 'reviews' ?>)
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4">
                            <div class="product-image-section" id="product-image-section">
                                <?php $this->load->view('product/media_gallery') ?>
                            </div>
                        </div>

                        <div class="col-md-8 col-sm-8">
                            <ul class="list-group">
                                <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name) && $ProductData->publication_name !== 'Anu Test Developer Account') { ?>
                                    <li class="list-group-item"><b><?php echo $this->lang->line('merchant'); ?>:</b>
                                        <a href="https://mu.yellowmarkets.com/shops/shop_details/<?=$ProductData->publisher_id?>" title="Go to Shop"><?php echo $ProductData->publication_name; ?></a></li>
                                <?php } ?>
                                
                                <?php if ($lang == 'french') { ?>

                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) { ?>
                                        <?php if ($attr->attr_value_lang) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name_lang ?>:</b>
                                                <?php echo $attr->attr_value_lang ?></li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>

                                <?php } else { ?>

                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) { ?>
                                        <?php if ($attr->attr_value) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name ?>:</b>
                                                <?php echo $attr->attr_value ?></li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>

                                <?php } ?>

                                <?php
                                $categoryIds = $CatApiResponse->CategoryIds->cat_ids;
                                $categoryArray = explode(',', $categoryIds);

                                if (in_array('8', $categoryArray) || (isset($ProductData) && ($ProductData->id == 3914 || $ProductData->id == 3388))) {
                                ?>
                                    <li class="list-group-item" style="position: relative;">
                                        <span style="position: absolute;top: 4px; left: -5px;color: #ef0d08;">* </span>
                                        <b><?php echo $this->lang->line('shipping_charges'); ?>:</b>
                                        <span><?php echo $this->lang->line('shipping_info'); ?></span>
                                    </li>
                                <?php } ?>
                            </ul>

                            <?php if (
                                $ProductData->made_in_maurituus_approval_status == "approve" ||
                                $ProductData->social_empowerment_approval_status == "approve" ||
                                $ProductData->environment_friendly_approval_status == "approve" ||
                                $ProductData->health_friendly_approval_status == "approve"
                            ) { ?>
                                <div class="badge-section pull-right">
                                    <?php if ($ProductData->made_in_maurituus_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_made_in_mauritius.png'); ?>"
                                            alt="Made in Mauritius" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->social_empowerment_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_social_empowerment.png'); ?>"
                                            alt="Social Empowerment" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->environment_friendly_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_environment_friendly.png'); ?>"
                                            alt="Environment Friendly" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->health_friendly_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_health_friendly.png'); ?>"
                                            alt="Health Friendly" class="product-badge" />
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <form name="product-frm" id="product-frm" method="POST"
                                action="<?php echo base_url(); ?>CartController/addtocart">
                                <input type="hidden" name="product_type" id="product_type"
                                    value="<?php echo $ProductData->product_type; ?>">
                                <input type="hidden" name="media_variant_id" id="media_variant_id"
                                    value="<?php echo $ProductData->media_variant_id; ?>">
                                <?php
                                // Promotion countdown and stock indicator calculation for Flash Sale & Daily Deals
                                $now = time();
                                $is_flash_sale_detail = false;
                                $is_daily_deal_detail = false;
                                $fs_ends_at_detail = 0;
                                $dd_ends_at_detail = 0;

                                if (!empty($ProductData->id)) {
                                    $ci =& get_instance();
                                    if (isset($ci->db)) {
                                        $p_promo_data = $ci->db->select('daily_deals, daily_deal_starts_at, daily_deal_ends_at, flash_sale, flash_sale_starts_at, flash_sale_ends_at')
                                            ->where('id', $ProductData->id)
                                            ->get('products')
                                            ->row();
                                        if ($p_promo_data) {
                                            if (!empty($p_promo_data->flash_sale) && $p_promo_data->flash_sale == 1) {
                                                $fs_s = !empty($p_promo_data->flash_sale_starts_at) ? (is_numeric($p_promo_data->flash_sale_starts_at) ? (int)$p_promo_data->flash_sale_starts_at : strtotime($p_promo_data->flash_sale_starts_at)) : 0;
                                                $fs_e = !empty($p_promo_data->flash_sale_ends_at) ? (is_numeric($p_promo_data->flash_sale_ends_at) ? (int)$p_promo_data->flash_sale_ends_at : strtotime($p_promo_data->flash_sale_ends_at)) : 0;
                                                if (($fs_s == 0 || $fs_s <= $now) && ($fs_e == 0 || $fs_e > $now)) {
                                                    $is_flash_sale_detail = true;
                                                    if ($fs_e > $now) $fs_ends_at_detail = $fs_e;
                                                }
                                            }
                                            if (!empty($p_promo_data->daily_deals) && $p_promo_data->daily_deals == 1) {
                                                $dd_s = !empty($p_promo_data->daily_deal_starts_at) ? (is_numeric($p_promo_data->daily_deal_starts_at) ? (int)$p_promo_data->daily_deal_starts_at : strtotime($p_promo_data->daily_deal_starts_at)) : 0;
                                                $dd_e = !empty($p_promo_data->daily_deal_ends_at) ? (is_numeric($p_promo_data->daily_deal_ends_at) ? (int)$p_promo_data->daily_deal_ends_at : strtotime($p_promo_data->daily_deal_ends_at)) : 0;
                                                if (($dd_s == 0 || $dd_s <= $now) && ($dd_e == 0 || $dd_e > $now)) {
                                                    $is_daily_deal_detail = true;
                                                    $dd_ends_at_detail = ($dd_e > $now) ? $dd_e : ($now + 86400);
                                                }
                                            }
                                        }
                                    }
                                }

                                if (!$is_flash_sale_detail) {
                                    if (!empty($ProductData->flash_sale) && $ProductData->flash_sale == 1) {
                                        $fs_s = !empty($ProductData->flash_sale_starts_at) ? (is_numeric($ProductData->flash_sale_starts_at) ? (int)$ProductData->flash_sale_starts_at : strtotime($ProductData->flash_sale_starts_at)) : 0;
                                        $fs_e = !empty($ProductData->flash_sale_ends_at) ? (is_numeric($ProductData->flash_sale_ends_at) ? (int)$ProductData->flash_sale_ends_at : strtotime($ProductData->flash_sale_ends_at)) : 0;
                                        if (($fs_s == 0 || $fs_s <= $now) && ($fs_e == 0 || $fs_e > $now)) {
                                            $is_flash_sale_detail = true;
                                            if ($fs_e > $now) $fs_ends_at_detail = $fs_e;
                                        }
                                    } elseif (!empty($ProductData->offer_type) && $ProductData->offer_type === 'flash_sale') {
                                        $is_flash_sale_detail = true;
                                    }
                                }

                                if (!$is_daily_deal_detail) {
                                    if (!empty($ProductData->daily_deals) && $ProductData->daily_deals == 1) {
                                        $dd_s = !empty($ProductData->daily_deal_starts_at) ? (is_numeric($ProductData->daily_deal_starts_at) ? (int)$ProductData->daily_deal_starts_at : strtotime($ProductData->daily_deal_starts_at)) : 0;
                                        $dd_e = !empty($ProductData->daily_deal_ends_at) ? (is_numeric($ProductData->daily_deal_ends_at) ? (int)$ProductData->daily_deal_ends_at : strtotime($ProductData->daily_deal_ends_at)) : 0;
                                        if (($dd_s == 0 || $dd_s <= $now) && ($dd_e == 0 || $dd_e > $now)) {
                                            $is_daily_deal_detail = true;
                                            $dd_ends_at_detail = ($dd_e > $now) ? $dd_e : ($now + 86400);
                                        }
                                    } elseif (!empty($ProductData->daily_deal) && $ProductData->daily_deal == 1) {
                                        $is_daily_deal_detail = true;
                                        $dd_ends_at_detail = $now + 86400;
                                    } elseif (!empty($ProductData->offer_type) && $ProductData->offer_type === 'daily_deal') {
                                        $is_daily_deal_detail = true;
                                        $dd_ends_at_detail = $now + 86400;
                                    } elseif (!empty($ProductData->is_daily_deal)) {
                                        $is_daily_deal_detail = true;
                                        $dd_ends_at_detail = $now + 86400;
                                    }
                                }

                                if ($is_daily_deal_detail && $dd_ends_at_detail <= $now) {
                                    $dd_ends_at_detail = $now + 86400;
                                }

                                $product_stock_detail = null;
                                if (isset($ProductData->calculated_stock) && is_numeric($ProductData->calculated_stock)) {
                                    $product_stock_detail = (float)$ProductData->calculated_stock;
                                } elseif (isset($ProductData->available_qty) && is_numeric($ProductData->available_qty)) {
                                    $product_stock_detail = (float)$ProductData->available_qty;
                                } elseif (isset($ProductData->qty) && is_numeric($ProductData->qty) && (float)$ProductData->qty > 0) {
                                    $product_stock_detail = (float)$ProductData->qty;
                                } elseif (isset($ProductData->stock_qty) && is_numeric($ProductData->stock_qty) && (float)$ProductData->stock_qty > 0) {
                                    $product_stock_detail = (float)$ProductData->stock_qty;
                                } elseif (isset($ProductData->total_qty) && is_numeric($ProductData->total_qty) && (float)$ProductData->total_qty > 0) {
                                    $product_stock_detail = (float)$ProductData->total_qty;
                                } elseif (isset($ProductData->quantity) && is_numeric($ProductData->quantity) && (float)$ProductData->quantity > 0) {
                                    $product_stock_detail = (float)$ProductData->quantity;
                                }

                                if ($product_stock_detail === null && !empty($ProductData->id)) {
                                    $ci =& get_instance();
                                    if (isset($ci->db)) {
                                        $child_inv = $ci->db->query("SELECT SUM(COALESCE(vi.available_qty, vi.qty, 0)) as total_stock FROM products v JOIN products_inventory vi ON vi.product_id = v.id WHERE v.parent_id = ? AND v.status = 1 AND v.remove_flag = 0", [$ProductData->id])->row();
                                        if ($child_inv && $child_inv->total_stock !== null && (float)$child_inv->total_stock > 0) {
                                            $product_stock_detail = (float)$child_inv->total_stock;
                                        } else {
                                            $p_inv_row = $ci->db->select('available_qty, qty')->where('product_id', $ProductData->id)->get('products_inventory')->row();
                                            if ($p_inv_row) {
                                                if ($p_inv_row->available_qty !== null && is_numeric($p_inv_row->available_qty)) {
                                                    $product_stock_detail = (float)$p_inv_row->available_qty;
                                                } elseif ($p_inv_row->qty !== null && is_numeric($p_inv_row->qty)) {
                                                    $product_stock_detail = (float)$p_inv_row->qty;
                                                }
                                            }
                                        }
                                    }
                                }

                                $is_out_of_stock_detail = false;
                                if ($product_stock_detail !== null && $product_stock_detail <= 0) {
                                    $is_out_of_stock_detail = true;
                                } elseif (isset($ProductData->stock_status) && (strtolower(trim($ProductData->stock_status)) == 'out of stock' || strtolower(trim($ProductData->stock_status)) == 'outofstock' || strtolower(trim($ProductData->stock_status)) == 'notavailable')) {
                                    $is_out_of_stock_detail = true;
                                }

                                $remaining_stock = ($product_stock_detail !== null && $product_stock_detail > 0) ? (int)$product_stock_detail : null;

                                if ($is_out_of_stock_detail) {
                                    $is_flash_sale_detail = false;
                                    $is_daily_deal_detail = false;
                                }
                                ?>

                                <?php if ($ProductData->product_type == 'simple') { ?>
                                    <div class="table-responsive product-price">
                                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                                            <thead>
                                                <tr class="active">
                                                    <th><?php echo $this->lang->line('eshop_price'); ?></th>
                                                    <?php if (!empty($ProductData->special_price)) { ?>
                                                        <th><?php echo $this->lang->line('special_price'); ?></th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr>
                                                    <?php if (!empty($ProductData->special_price)) { ?>
                                                        <!-- Eshop price with strikethrough -->
                                                        <td class="cover-price" style="text-decoration: line-through;">
                                                            <?php echo number_format($ProductData->webshop_price, 2); ?>
                                                        </td>

                                                        <!-- Special price with badge on right -->
                                                        <td class="offer-price">
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                                <!-- Left: price + discount -->
                                                                <span>
                                                                    <?php
                                                                    $offPercentPrice = '';
                                                                    $excludedIds = [];
                                                                    if (!in_array($ProductData->id, $excludedIds)) {
                                                                        $cal1 = ($ProductData->webshop_price - $ProductData->special_price) / $ProductData->webshop_price;
                                                                        $off_percent_price = round($cal1 * 100);
                                                                        if ($off_percent_price > 0) {
                                                                            $offPercentPrice = ' <span>(' . $off_percent_price . '% ' . $this->lang->line('off') . ')</span>';
                                                                        }
                                                                    }
                                                                    echo number_format($ProductData->special_price, 2) . $offPercentPrice;
                                                                    ?>
                                                                </span>

                                                                <!-- Right: badge -->
                                                                <div class="right-badge">
                                                                    <?php
                                                                    $avg_badge_val = !empty($rating->avg_rating) ? (float)$rating->avg_rating : (!empty($merchant_rating->avg_rating) ? (float)$merchant_rating->avg_rating : 0);
                                                                    if ($avg_badge_val >= 4.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                                    } elseif ($avg_badge_val >= 3.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_4_stars.png');
                                                                    } elseif ($avg_badge_val >= 2.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_3_stars.svg');
                                                                    } else {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                                    }
                                                                    ?>
                                                                    <img src="<?= $badge; ?>"
                                                                        alt="<?php echo $this->lang->line('trust_badge'); ?>"
                                                                        width="100" class="badge-product-details">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    <?php } else { ?>
                                                        <!-- Only normal price -->
                                                        <td class="cover-price">
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                                <?php echo number_format($ProductData->webshop_price, 2); ?>
                                                                <!-- Right: badge -->
                                                                <div class="right-badge">
                                                                    <?php
                                                                    $avg_badge_val = !empty($rating->avg_rating) ? (float)$rating->avg_rating : (!empty($merchant_rating->avg_rating) ? (float)$merchant_rating->avg_rating : 0);
                                                                    if ($avg_badge_val >= 4.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                                    } elseif ($avg_badge_val >= 3.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_4_stars.png');
                                                                    } elseif ($avg_badge_val >= 2.5) {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_3_stars.svg');
                                                                    } else {
                                                                        $badge = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                                    }
                                                                    ?>
                                                                    <img src="<?= $badge; ?>"
                                                                        alt="<?php echo $this->lang->line('trust_badge'); ?>"
                                                                        width="100" class="badge-product-details">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                            </tbody>
                                    </div>
                                <?php } ?>

                                <?php if ($ProductData->product_type == 'configurable') { ?>
                                    <div class="table-responsive product-price">
                                        <div class="product-price-detail"></div>
                                    </div>
                                <?php } ?>


                                <?php (new ProductDetails())->productStockVariant($ProductData, $CategoryIds); ?>



                                <?php if (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == 1) { ?>

                                    <div class="notifywrap">
                                        <div class="notifyhd">
                                            <i class="fa fa-exclamation-circle fa-2x"></i>
                                            <span><?= $this->lang->line('out_of_stock_label'); ?></span>
                                        </div>
                                        <div class="notifyfrm">
                                            <p>
                                                <?= $this->lang->line('out_of_stock_message'); ?>
                                            </p>
                                            <div class="form-inline" id="keepnotified">
                                                <div class="form-group">
                                                    <label class="sr-only"
                                                        for="notified-email"><?= $this->lang->line('email_address_label'); ?></label>
                                                    <input type="email" class="form-control" id="notified-email"
                                                        placeholder="<?= $this->lang->line('email_placeholder'); ?>"
                                                        value="<?php echo $_SESSION["EmailID"] ?? ""; ?>"
                                                        onkeyup="ValidateEmail();">
                                                </div>
                                                <button type="button" id="notified-keep-inform" class="btn btn-primary"
                                                    onclick="openNotifiedPopup(<?php echo $ProductData->id; ?>)">
                                                    <?= $this->lang->line('keep_me_informed_btn'); ?>
                                                </button>
                                            </div>
                                            <span id="lblError" class="notified-error"></span>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="subscribe-deli">
                                    <?php if (!$is_out_of_stock_detail && isset($ProductData->stock_status) && ($ProductData->stock_status == 'Instock') && (!isset($ProductData->total_qty) || (float)$ProductData->total_qty > 0) && (!isset($ProductData->qty) || (float)$ProductData->qty > 0) && (!isset($ProductData->stock_qty) || (float)$ProductData->stock_qty > 0)) { ?>
                                        <?php if ($restricted_access == "yes" && $customer_id == 0) { ?>
                                            <button type="button" class="btn btn-primary subscr-now pull-left" <?php echo isset($ProductData->stock_status) &&
                                                                                                                    ($ProductData->stock_status == "Instock" && $ProductData->product_type == "simple")
                                                                                                                    ? ""
                                                                                                                    : "disabled"; ?>
                                                onclick="openRestrictedAccessPopup()">
                                                <?= $this->lang->line('add_to_cart_btn'); ?>
                                            </button>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p class="pull-right"><b>
                                                        <?= $this->lang->line('delivery_duration_label'); ?>
                                                        <?php echo $ProductData->estimate_delivery_time; ?>
                                                        <?= $this->lang->line('days_label'); ?>
                                                    </b></p>
                                                <div class="clearfix"></div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="submit" id="add_to_cart" class="btn btn-primary subscr-now pull-left">
                                                <?= $this->lang->line('add_to_cart_btn'); ?>
                                            </button>
                                            <?php if (!empty($customer_id) && $customer_id != 0): ?>
                                                <button type="button" id="askQuestionBtn" class="btn btn-outline-primary">
                                                    <?= $this->lang->line('ask_a_question'); ?>
                                                </button>
                                            <?php endif; ?>


                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p class="pull-right"><b>
                                                        <?= $this->lang->line('expected_delivery_label'); ?>
                                                        <?php echo $ProductData->estimate_delivery_time; ?>
                                                        <?= $this->lang->line('days_label'); ?>
                                                    </b></p>
                                                <div class="clearfix"></div>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-primary subscr-now pull-left" id="add_to_cart" type="submit"
                                            disabled>
                                            <?= $this->lang->line('out_of_stock_label'); ?>
                                        </button>
                                    <?php } ?>
                                    <div id="addtocart-message" class="addtocart-message"></div>
                                    <?php
                                    $has_active_daily_deal = ($is_daily_deal_detail && $dd_ends_at_detail > $now && !$is_out_of_stock_detail);
                                    if ($has_active_daily_deal) {
                                        $diff_d = $dd_ends_at_detail - $now;
                                        if ($diff_d >= 86400) {
                                            $days_d = floor($diff_d / 86400);
                                            $countdown_str = $days_d . ($days_d == 1 ? ' day' : ' days');
                                        } else {
                                            $countdown_str = sprintf('%02d:%02d:%02d', floor($diff_d / 3600), floor(($diff_d % 3600) / 60), $diff_d % 60);
                                        }
                                    }
                                    $show_stock_initially = ($remaining_stock !== null && $remaining_stock > 0 && ($is_flash_sale_detail || ($remaining_stock <= 10 && !$is_daily_deal_detail)) && !$is_out_of_stock_detail);
                                    $stock_pad_top = $has_active_daily_deal ? '4px' : '10px';
                                    ?>
                                    <!-- Countdown Timer: Only show if Daily Deal is active (or both Daily Deal & Flash Sale) -->
                                    <?php if ($has_active_daily_deal): ?>
                                        <div class="promo-countdown-wrapper" style="clear: both; padding-top: 10px !important; color: #e52525 !important; font-size: 13.5px !important; font-weight: normal !important; display: flex !important; align-items: center !important; gap: 5px !important;">
                                            <i class="fa fa-clock-o" style="color: #e52525 !important; font-size: 14px !important;"></i>
                                            <span>Ends in <span class="live-countdown" data-ends-at="<?= $dd_ends_at_detail ?>"><?= $countdown_str ?></span></span>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Product Count Left: Show if Flash Sale is active (or both Daily Deal & Flash Sale, or limited stock <= 10) -->
                                    <div id="details-stock-left" class="promo-stock-wrapper" data-is-flash-sale="<?= $is_flash_sale_detail ? '1' : '0' ?>" data-is-daily-deal="<?= $is_daily_deal_detail ? '1' : '0' ?>" style="clear: both; padding-top: <?= $stock_pad_top ?> !important; color: #e52525 !important; font-size: 13px !important; font-weight: normal !important; display: <?= $show_stock_initially ? 'flex' : 'none' ?> !important; align-items: center !important; gap: 5px !important;">
                                        <i class="fa fa-fire" style="color: #e52525 !important; font-size: 13px !important;"></i>
                                        <span>Only <strong id="details-stock-qty" style="font-weight: 600;"><?= ($remaining_stock !== null && $remaining_stock > 0) ? $remaining_stock : '' ?></strong> left</span>
                                    </div>
                                </div>

                                <ul id="myTab" class="nav nav-tabs">
                                    <?php if (!empty($ProductData->description)) { ?>
                                        <li class="active"><a href="#Description"
                                                data-toggle="tab"><?= $this->lang->line('description_tab'); ?></a></li>
                                    <?php } ?>
                                    <?php if ((is_array($ProductData->specification) && count($ProductData->specification) > 0) || (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0)) { ?>
                                        <li><a href="#Specifications"
                                                data-toggle="tab"><?= $this->lang->line('specifications_tab'); ?></a></li>
                                    <?php } ?>
                                    <?php if ((is_array($ProductData->highlights) && count($ProductData->highlights) > 0) || (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0)) { ?>
                                        <li><a href="#technical_specification"
                                                data-toggle="tab"><?= $this->lang->line('technical_specification_tab'); ?></a></li>
                                    <?php } ?>
                                    <li id="tab-reviews-link">
                                        <a href="#reviews" data-toggle="tab">
                                            <?= $this->lang->line('reviews_tab') ?? 'Reviews'; ?> 
                                            (<?= !empty($product_rating->total_reviews) ? (int)$product_rating->total_reviews : 0; ?>)
                                        </a>
                                    </li>
                                </ul>

                                <div id="myTabContent" class="tab-content">
                                    <div class="tab-pane fade in active" id="Description">
                                        <?php
                                        if ($lang == 'french' && !empty($ProductData->lang_description)) {
                                            echo $ProductData->lang_description;
                                        } else {
                                            echo $ProductData->description;
                                        }
                                        ?>
                                    </div>
                                    <div class="tab-pane fade" id="Specifications">
                                        <table class="datasheet">
                                            <tr>
                                                <th colspan="2"><?= $this->lang->line('additional_features_label'); ?>
                                                </th>
                                            </tr>
                                            <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name)) { ?>
                                                <tr>
                                                    <td class="datasheet-features-type">
                                                        <?= $this->lang->line('merchant_label'); ?></td>
                                                    <td><?php echo $ProductData->publication_name; ?></td>
                                                </tr>
                                            <?php } ?>
                                            
                                            <?php if($lang == "french") { ?>
                                            <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                                foreach ($ProductData->AttributesWithOptions as $attr) {
                                                    if (isset($attr->attr_value) && !empty($attr->attr_value)) { ?>
                                                        <tr>
                                                            <td class="datasheet-features-type"><?php echo $attr->attr_name_lang; ?></td>
                                                            <td><?php echo $attr->attr_value_lang; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php } else { ?>
                                            <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                                foreach ($ProductData->AttributesWithOptions as $attr) {
                                                    if (isset($attr->attr_value) && !empty($attr->attr_value)) { ?>
                                                        <tr>
                                                            <td class="datasheet-features-type"><?php echo $attr->attr_name; ?></td>
                                                            <td><?php echo $attr->attr_value; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php  } ?>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="technical_specification">
                                        <?php
                                        if ($lang == 'french' && !empty($ProductData->lang_highlights)) {
                                            echo $ProductData->lang_highlights;
                                        } else {
                                            echo $ProductData->highlights;
                                        }
                                        ?>
                                    </div>

                                    <!-- Reviews Tab Pane (YM Review Figma UI) -->
                                    <div class="tab-pane fade" id="reviews">
                                        <?php
                                            $prod_avg = !empty($product_rating->avg_rating) ? (float)$product_rating->avg_rating : 0;
                                            $prod_total = !empty($product_rating->total_reviews) ? (int)$product_rating->total_reviews : 0;
                                            $prod_half = round($prod_avg * 2) / 2;

                                            $merch_avg = !empty($merchant_rating->avg_rating) ? (float)$merchant_rating->avg_rating : (!empty($rating->avg_rating) ? (float)$rating->avg_rating : 0);
                                            $merch_total = !empty($merchant_rating->total_reviews) ? (int)$merchant_rating->total_reviews : (!empty($rating->total_reviews) ? (int)$rating->total_reviews : 0);

                                            // Determine Merchant Badge from uploads/trust_badges
                                            if ($merch_avg >= 4.5) {
                                                $m_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                $m_badge_alt = 'Merchant 5 Stars Trusted Badge';
                                            } elseif ($merch_avg >= 3.5) {
                                                $m_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_4_stars.png');
                                                $m_badge_alt = 'Merchant 4 Stars Trusted Badge';
                                            } elseif ($merch_avg >= 2.5) {
                                                $m_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_3_stars.svg');
                                                $m_badge_alt = 'Merchant 3 Stars Trusted Badge';
                                            } else {
                                                $m_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                $m_badge_alt = 'Merchant Trusted Badge';
                                            }

                                            // Determine Product Badge from uploads/trust_badges
                                            if ($prod_avg >= 4.5) {
                                                $p_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_5_stars.png');
                                                $p_badge_alt = 'Product 5 Stars Trusted Badge';
                                            } elseif ($prod_avg >= 3.5) {
                                                $p_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_4_stars.png');
                                                $p_badge_alt = 'Product 4 Stars Trusted Badge';
                                            } elseif ($prod_avg >= 2.5) {
                                                $p_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_3_stars.svg');
                                                $p_badge_alt = 'Product 3 Stars Trusted Badge';
                                            } else {
                                                $p_badge_img = base_url('uploads/trust_badges/ym_trusted_badges_4_stars.png');
                                                $p_badge_alt = 'Product Trusted Badge';
                                            }

                                            // Featured testimony
                                            $feat = !empty($product_rating->featured_review) ? $product_rating->featured_review : null;
                                            $feat_name = !empty($feat->customer_name) ? $feat->customer_name : ($prod_total > 0 ? 'Verified Shopper' : 'Shopper Feedback');
                                            $feat_rating = !empty($feat->rating) ? (int)$feat->rating : ($prod_total > 0 ? 5 : 5);
                                            $feat_text = !empty($feat->comments) ? $feat->comments : ($prod_total > 0 ? 'Great product and excellent customer service on Yellow Markets.' : 'Be the first to review this product after purchase to help our community of shoppers!');
                                            $feat_initials = '';
                                            $words = explode(' ', trim($feat_name));
                                            if (count($words) >= 2) {
                                                $feat_initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1));
                                            } else {
                                                $feat_initials = strtoupper(substr($feat_name, 0, 2));
                                            }

                                            // Header company + category label
                                            $company_name = !empty($ProductData->publication_name) ? $ProductData->publication_name : 'MERCHANT';
                                            $cat_label = 'PRODUCT';
                                            if (!empty($categoryListSub->CategoryDetails->cat_name)) {
                                                $cat_label = $categoryListSub->CategoryDetails->cat_name;
                                            } elseif (!empty($categoryList->CategoryDetails->cat_name)) {
                                                $cat_label = $categoryList->CategoryDetails->cat_name;
                                            }
                                            $header_breadcrumb = strtoupper($company_name . ' | ' . $cat_label . ' REVIEW');
                                        ?>
                                        <div class="review-summary">
                                            <div>
                                                <small><?= htmlspecialchars($header_breadcrumb) ?></small>
                                                <h1><?= htmlspecialchars($ProductData->name) ?></h1>
                                                <div class="stars" aria-label="<?= $prod_avg ?> out of 5 stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?= ($prod_half >= $i) ? '★' : (($prod_half == $i - 0.5) ? '★' : '☆') ?>
                                                    <?php endfor; ?>
                                                    <span>(<?= $prod_total ?>)</span>
                                                </div>
                                            </div>

                                            <div class="circle-pair" aria-label="Illustrative Merchant and Product badges">
                                                <img src="<?= $m_badge_img ?>" width="120" height="120" alt="<?= $m_badge_alt ?>" title="Merchant Service Rating: <?= $merch_avg ?> / 5" style="width: 120px; height: 120px; object-fit: contain;">
                                                <img src="<?= $p_badge_img ?>" width="120" height="120" alt="<?= $p_badge_alt ?>" title="Product Rating: <?= $prod_avg ?> / 5" style="width: 120px; height: 120px; object-fit: contain;">
                                            </div>

                                            <div class="testimony">
                                                <span class="shopper-avatar"><?= !empty($feat_initials) ? $feat_initials : '' ?></span>
                                                <div>
                                                    <div style="font-weight: bold; font-size: 15px; color: #111;"><?= htmlspecialchars($feat_name) ?></div>
                                                    <div class="mini-stars">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?= ($feat_rating >= $i) ? '★' : '☆' ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <small><?= htmlspecialchars($feat_text) ?></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="review-list">
                                            <div class="list-heading">
                                                <span><strong id="reviews-count-display"><?= $prod_total ?></strong> <?= $prod_total == 1 ? 'review' : 'reviews' ?></span>
                                                <div class="review-sort-wrap" style="position: relative;">
                                                    <button type="button" class="sort-btn" id="review-sort-btn">
                                                        <span id="sort-current-text">Sort</span> <i class="fa fa-angle-down" style="margin-left: 4px;"></i>
                                                    </button>
                                                    <ul id="review-sort-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); list-style: none; padding: 6px 0; margin: 4px 0 0; z-index: 100; min-width: 140px; font-size: 13px;">
                                                        <li><a href="javascript:void(0)" class="sort-option active" data-sort="newest" style="display: block; padding: 6px 14px; color: #333; text-decoration: none;">Newest</a></li>
                                                        <li><a href="javascript:void(0)" class="sort-option" data-sort="highest" style="display: block; padding: 6px 14px; color: #333; text-decoration: none;">Highest Rating</a></li>
                                                        <li><a href="javascript:void(0)" class="sort-option" data-sort="lowest" style="display: block; padding: 6px 14px; color: #333; text-decoration: none;">Lowest Rating</a></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div id="reviews-container">
                                                <?php if (!empty($product_reviews)): ?>
                                                    <?php foreach ($product_reviews as $idx => $rev): ?>
                                                        <?php 
                                                            $shopper_name = !empty($rev->customer_name) ? $rev->customer_name : 'Shopper';
                                                            $words = explode(' ', trim($shopper_name));
                                                            $initials = '';
                                                            if (count($words) >= 2) {
                                                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1));
                                                            } else {
                                                                $initials = strtoupper(substr($shopper_name, 0, 2));
                                                            }
                                                            $item_r = (int)$rev->item_rating;
                                                            $service_r = (int)$rev->service_rating;
                                                            $rev_time = strtotime($rev->created_at);
                                                            $time_ago = date('d M Y', $rev_time);
                                                            if (time() - $rev_time < 31536000) {
                                                                $diff_d = round((time() - $rev_time) / 86400);
                                                                if ($diff_d == 0) $time_ago = 'Today';
                                                                elseif ($diff_d == 1) $time_ago = 'Yesterday';
                                                                elseif ($diff_d < 30) $time_ago = $diff_d . ' days ago';
                                                                elseif ($diff_d < 365) $time_ago = round($diff_d / 30) . ' months ago';
                                                            } else {
                                                                $time_ago = round((time() - $rev_time) / 31536000) . ' year ago';
                                                            }

                                                            // Generate a review headline / title
                                                            $review_title = 'Great Product';
                                                            if ($item_r == 5) {
                                                                $review_title = ($idx % 2 == 1) ? 'They Are Just The Best' : 'Great Product';
                                                            } elseif ($item_r == 4) {
                                                                $review_title = 'Good Quality and Fit';
                                                            } elseif ($item_r == 3) {
                                                                $review_title = 'Satisfactory Experience';
                                                            } else {
                                                                $review_title = 'Product Review';
                                                            }
                                                        ?>
                                                        <article class="review" data-rating="<?= $item_r ?>" data-time="<?= $rev_time ?>">
                                                            <div class="review-head">
                                                                <span class="avatar"><?= $initials ?></span>
                                                                <div>
                                                                    <b><?= htmlspecialchars($shopper_name) ?></b>
                                                                    <span class="verified">Verified Shopper</span>
                                                                    <?php if (!empty($ProductData->AttributesWithOptions)): ?>
                                                                        <p><?= htmlspecialchars($ProductData->name) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <time><?= $time_ago ?></time>
                                                            </div>
                                                            <p class="item-score">
                                                                ITEM: &nbsp;<?= $item_r ?> <span>★</span> &nbsp;|&nbsp; SERVICE: <?= $service_r ?> <span>★</span>
                                                            </p>
                                                            <h3><?= htmlspecialchars($review_title) ?></h3>
                                                            <p><?= nl2br(htmlspecialchars($rev->comments)) ?></p>
                                                            <div class="helpful">
                                                                Was this helpful? 
                                                                <button type="button" class="btn-helpful" data-helpful aria-label="Helpful review" aria-pressed="false">↑</button>
                                                                <button type="button" class="btn-helpful" data-helpful aria-label="Not helpful review" aria-pressed="false">↓</button>
                                                            </div>
                                                        </article>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div style="padding: 40px 15px; text-align: center; color: #777;">
                                                        <i class="fa fa-comment-dots" style="font-size: 38px; color: #ffce00; margin-bottom: 12px; display: block;"></i>
                                                        <h4 style="color: #333; margin-bottom: 6px; font-weight: bold;">No published reviews yet</h4>
                                                        <p style="font-size: 14px; color: #666;">Be the first to review this product after purchase!</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($product_reviews) && count($product_reviews) > 2): ?>
                                                <button type="button" class="show-more-btn" id="reviews-show-more">Show More</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </form>
                            <!-- Ask a Question Modal -->
                            <div class="modal fade" id="askQuestionModal" tabindex="-1"
                                aria-labelledby="askQuestionLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title mb-0" id="askQuestionLabel"><?= $this->lang->line('ask_a_question'); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <form id="askQuestionForm">
                                                <input type="hidden" name="product_id" value="<?= $ProductData->id ?>">
                                                <input type="hidden" name="merchant_id"
                                                    value="<?= $ProductData->publisher_id ?>">
                                                <input type="hidden" name="customer_id" value="<?= $customer_id ?>">

                                                <div class="mb-3">
                                                    <label for="questionName" class="form-label"><?= $this->lang->line('name'); ?></label>
                                                    <?php if (!empty($customer_id)): ?>
                                                        <input type="text" class="form-control" id="questionName"
                                                            name="name"
                                                            value="<?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                            readonly>
                                                    <?php else: ?>
                                                        <input type="text" class="form-control" id="questionName"
                                                            name="name" placeholder="Enter your name" required>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="questionCategory" class="form-label"><?= $this->lang->line('category'); ?></label>
                                                    <select class="form-select" id="questionCategory" name="category"
                                                        >
                                                        <option value=""><?= $this->lang->line('select_category'); ?></option>
                                                        <option value="Merchant"><?= $this->lang->line('merchant_delivery'); ?></option>
                                                        <option value="General Enquiry"><?= $this->lang->line('general_enquiry_product'); ?></option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="questionMessage" class="form-label"><?= $this->lang->line('message'); ?></label>
                                                    <textarea class="form-control" id="questionMessage" name="message"
                                                        rows="4" ></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100"><?= $this->lang->line('save_button'); ?></button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<?php $this->load->view('common/footer'); ?>



<script>
    $(document).ready(function($) {
        $('#askQuestionBtn').on('click', function() {
            $('#askQuestionModal').modal('show');
        });

        // Remove error when user types/selects
        $('#questionName, #questionCategory, #questionMessage').on('keyup change', function () {
            $(this).removeClass('is-invalid');
            $(this).next('.validation-error').remove();
        });

        $('#askQuestionForm').on('submit', function(e) {
            e.preventDefault();

            let isValid = true;

            // Remove old errors
            $('.validation-error').remove();
            $('.form-control, .form-select').removeClass('is-invalid');

            // Get values
            let name = $('#questionName').val().trim();
            let category = $('#questionCategory').val();
            let message = $('#questionMessage').val().trim();

            // ==========================
            // ✅ VALIDATION RULES
            // ==========================

            // Name validation (only if not readonly)
            if (!$('#questionName').prop('readonly')) {

                if (name === '') {
                    showError('#questionName', 'Name is required.');
                    isValid = false;
                } else if (name.length < 3) {
                    showError('#questionName', 'Name must be at least 3 characters.');
                    isValid = false;
                }
            }

            // Category validation
            if (category === '') {
                showError('#questionCategory', 'Please select category.');
                isValid = false;
            }

            // Message validation
            if (message === '') {
                showError('#questionMessage', 'Message is required.');
                isValid = false;
            } 

            // Stop if invalid
            if (!isValid) {
                return;
            }

            // ==========================
            // ✅ YOUR ORIGINAL AJAX
            // ==========================

            $.ajax({
                url: BASE_URL + 'ProductsController/saveQuestion',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function () {
                    $('#askQuestionForm button[type="submit"]')
                        .prop('disabled', true)
                        .text('Sending...');
                },
                success: function(response) {
                    if (response.flag == 1) {

                        swal({
                            title: "",
                            icon: "success",
                            text: response.msg,
                            buttons: false,
                            timer: 1000
                        }).then(() => {
                            $('#askQuestionForm')[0].reset();
                            location.reload();
                        });

                    } else {
                        swal({
                            title: "Error",
                            icon: "error",
                            text: response.msg
                        });
                    }
                },
                error: function() {
                    swal({
                        title: "Error",
                        icon: "error",
                        text: "Something went wrong!"
                    });
                },
                complete: function () {
                    $('#askQuestionForm button[type="submit"]')
                        .prop('disabled', false)
                        .text('Send Message');
                }
            });

        });

        // ==========================
        // ✅ ERROR FUNCTION
        // ==========================

        function showError(field, message) {
            $(field).addClass('is-invalid');
            $(field).after('<small class="text-danger validation-error">' + message + '</small>');
        }


        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalEl = btn.closest('.modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            });
        });



        var product_type = $("#product_type").val();
        if (!product_type) {
            product_type = "<?= $ProductData->product_type ?? '' ?>";
        }

        if (product_type == "bundle") {
            $(".bundle_child_id").each(function(i) {
                var bundle_child_id = $(this).val();
                GetVariantProductForBundle(bundle_child_id);
            });
        } else if (product_type == "configurable") {
            GetVariantProduct();
        }

    });
</script>



<script type="text/javascript">
    function ValidateEmail() {



        var email = document.getElementById("notified-email").value;



        var lblError = document.getElementById("lblError");



        lblError.innerHTML = "";



        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;



        if (!expr.test(email)) {

            lblError.innerHTML = "Invalid email address.";

        }



    }







    function openNotifiedPopup(product_id) {



        var email_notified = $("#notified-email").val();



        $('#lblError').html('');



        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;







        if (email_notified != '' && expr.test(email_notified)) {



            if (product_id != '' && email_notified != '') {



                $.ajax({



                    type: "POST",



                    dataType: "html",



                    url: BASE_URL + "ProductsController/productNotified",



                    data: {

                        email: email_notified,

                        product_id: product_id

                    },



                    complete: function() {



                    },



                    beforeSend: function() {

                        $('#notified-keep-inform').prop("disabled", true);

                        // $('#ajax-spinner').show();



                    },



                    success: function(response) {

                        Swal.fire({

                            title: 'KEEP ME NOTIFIED',

                            html: '<label>You have been successfully subscribed to updates of this product.</label>',

                            icon: 'success'

                        });

                        $('#notified-keep-inform').prop("disabled", false);

                    }



                });



            } else {



                return false;



            }



        } else {



            $('#lblError').html('Please enter valid email.');



            return false;



        }



    }



    function GetVariantProduct() {

        $("#addtocart_error").html("");

        $("#add_to_cart").prop("disabled", true);

        var selected_variant = [];

        var variant_count = [];

        var product_id = $("#product_id").val();



        var media_variant_id = $("#media_variant_id").val();



        // var value_variants = $(".single_variant:checked").val();

        var value_variants = $('input[name="variant_options_data"]:checked').val();

        var conf_simple_id = value_variants;





        // console.log("VariantsOptionData: ",$('input[name="variant_options_data"]:checked').val());

        // return;



        var array_attr = value_variants.split(",");

        var conf_simple_id = array_attr[0];

        var product_id = array_attr[1];





        if (array_attr.length > 0) {

            var attr_value = array_attr[0];

            var attr_id = array_attr[1];

            selected_variant.push({

                attr_id,

                attr_value,

            });

            variant_count.push({

                attr_id,

                attr_value,

            });



            var total_selected_variant = variant_count.length;



            if (conf_simple_id != "") {

                $.ajax({

                    url: BASE_URL + "ProductsController/getVariantProductNew",

                    type: "POST",

                    dataType: "json",

                    cache: false,

                    headers: {

                        "X-Requested-With": "XMLHttpRequest",

                    },

                    data: {

                        parent_id: product_id,

                        product_id: conf_simple_id,

                        total_variant: total_selected_variant,

                        selected_variant: selected_variant,

                        media_variant_id: media_variant_id,

                        //   isPrelaunch:isPrelaunch

                    },



                    success: function(response) {



                        if (response.status == 200) {

                            // alert(response.qty_limit);

                            const simple_product_id =

                                response.ConfigSimpleDetails.conf_simple_pro_id;



                            const simple_price = parseFloat(

                                response.ConfigSimpleDetails.conf_simple_pro_pice

                            ).toFixed(2);



                            const simple_qty =

                                response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;



                            const simple_qty_status =

                                response.ConfigSimpleDetails.conf_simple_pro_inventory.status;



                            const special_price = response.ConfigSimpleDetails.special_price;



                            const display_original =

                                response.ConfigSimpleDetails.display_original;





                            const qty_limit = response.qty_limit;







                            if (response.mediaGallery != "") {

                                $("#product-image-section").html(response.mediaGallery);

                            }



                            if (simple_qty_status === "instock") {

                                $("#add_to_cart").prop("disabled", false);



                                $("#conf_simple_pid").val(simple_product_id);



                                $("#conf_simple_price").val(simple_price);



                                $("#conf_simple_qty").val(simple_qty);
                                var isFs = $("#details-stock-left").data("is-flash-sale") == 1;
                                var isDd = $("#details-stock-left").data("is-daily-deal") == 1;
                                var qty = parseInt(simple_qty);
                                if (qty > 0 && (isFs || (qty <= 10 && !isDd))) {
                                    $("#details-stock-qty").text(simple_qty);
                                    $("#details-stock-left").show().css("display", "flex");
                                } else {
                                    $("#details-stock-left").hide();
                                }



                                if (special_price > 0) {

                                    if (display_original > 0 && display_original == 1) {

                                        if (parseInt(special_price) < parseInt(simple_price)) {

                                            var percent = 0;
                                            if (simple_price > special_price) {
                                                percent =
                                                    ((simple_price - special_price) / simple_price) * 100;
                                            }

                                            var percent_ceil = Math.round(percent);



                                            var final_percentage = percent_ceil.toFixed(0);



                                            var addition_percent_html = "";



                                            if (final_percentage != 0.0) {

                                                var addition_percent_html =

                                                    '<span class="price save-discount">(' +

                                                    final_percentage +

                                                    "%)</span>";

                                            }



                                            // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                            // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);



                                            // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                            // 	$('.product-price-detail').html('<span class="discounted-price special-price" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span> <span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>'+addition_percent_html+'');



                                            // }else{



                                            $(".product-price-detail").html(

                                                '<span class="discounted-price special-price" id="product_price">' +

                                                CURRENCY_TYPE +

                                                "&nbsp;" +

                                                numberWithCommas(simple_price) +

                                                '</span> <span class="special-price" id="discounted_price">' +

                                                CURRENCY_TYPE +

                                                "&nbsp;" +

                                                numberWithCommas(special_price) +

                                                "</span>" +

                                                addition_percent_html +

                                                ""

                                            );



                                            // }

                                        } else {

                                            // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                            // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                            // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');



                                            // }else{



                                            $(".product-price-detail").html(

                                                '<span class="special-price" id="discounted_price">' +

                                                CURRENCY_TYPE +

                                                "&nbsp;" +

                                                numberWithCommas(special_price) +

                                                "</span>"

                                            );



                                            // }

                                        }

                                    } else {

                                        // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                        // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                        // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');



                                        // }else{



                                        $(".product-price-detail").html(

                                            '<span class="special-price" id="discounted_price">' +

                                            CURRENCY_TYPE +

                                            "&nbsp;" +

                                            numberWithCommas(special_price) +

                                            "</span>"

                                        );



                                        // }

                                    }

                                } else {

                                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                    // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);



                                    // 	$('.product-price-detail').html('<span class="special-price here" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span>');



                                    // }else{



                                    $(".product-price-detail").html(

                                        '<span class="special-price" id="product_price">' +

                                        CURRENCY_TYPE +

                                        "&nbsp;" +

                                        simple_price +

                                        "</span>"

                                    );



                                    // }

                                }



                                var j = 0;



                                var qty_html_refresh = "";



                                if (simple_qty > 0) {

                                    if (simple_qty > qty_limit) {

                                        simple_qty = qty_limit;

                                    }



                                    for (j = 1; j <= simple_qty; j++) {

                                        qty_html_refresh +=

                                            '<option value="' + j + '">' + j + "</option>";

                                    }



                                    $("#quantity").attr("disabled", false);

                                }



                                $("#quantity").html(qty_html_refresh);

                            } else {

                                $("#quantity").html("");



                                $("#quantity").attr("disabled", true);



                                $("#add_to_cart").prop("disabled", true);
                                $("#details-stock-left").hide();



                                $("#addtocart_error").html("Product is out of stock.");



                                //swal('Error','Product is out of stock.','error');



                                return false;

                            }

                        } else {

                            $("#add_to_cart").prop("disabled", true);



                            $("#addtocart_error").html(response.message);



                            //swal('Error',response.message,'error');



                            return false;

                        }

                    },

                });

            } else {

                return false;

            }

        } else {

            return false;

        }

    }



    function GetVariantProductForBundle(bundle_child_id) {



        $("#addtocart_error").html("");



        $("#add_to_cart").attr("disabled", true);



        var selected_variant = [];



        var count_variant = $(".single_variant_" + bundle_child_id).length;



        var variant_count = [];



        var product_id = $("#product_id_child_main_" + bundle_child_id).val();



        var className = ".single_variant_" + bundle_child_id;



        var value_variants = $(

            ".single_variant_" + bundle_child_id + ":checked"

        ).val();



        var array_attr = value_variants.split(",");





        if (array_attr.length > 0) {

            var attr_value = array_attr[0];



            var attr_id = array_attr[1];



            var default_qty = array_attr[2];



            selected_variant.push({

                attr_id,

                attr_value,

            });



            variant_count.push({

                attr_id,

                attr_value,

            });



            var total_selected_variant = variant_count.length;



            if (total_selected_variant != "") {

                $.ajax({

                    url: BASE_URL + "ProductsController/getVariantProduct",



                    type: "POST",



                    dataType: "json",



                    cache: false,



                    headers: {

                        "X-Requested-With": "XMLHttpRequest",

                    },



                    data: {

                        product_id: product_id,



                        total_variant: total_selected_variant,



                        selected_variant: selected_variant,



                        media_variant_id: "",

                    },



                    success: function(response) {



                        if (response.status == 200) {

                            const simple_product_id =

                                response.ConfigSimpleDetails.conf_simple_pro_id;



                            const simple_price = parseFloat(

                                response.ConfigSimpleDetails.conf_simple_pro_pice

                            ).toFixed(2);



                            var simple_qty =

                                response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;



                            const simple_qty_status =

                                response.ConfigSimpleDetails.conf_simple_pro_inventory.status;



                            const qty_limit = response.qty_limit;



                            if (response.mediaGallery != "") {

                                $("#product-image-section").html(response.mediaGallery);

                            }



                            if (simple_qty_status === "instock" && simple_qty >= default_qty) {

                                $("#conf_simple_pid_" + bundle_child_id).val(simple_product_id);



                                $("#conf_simple_price_" + bundle_child_id).val(simple_price);



                                $("#conf_simple_qty_" + bundle_child_id).val(simple_qty);



                                var string = $("#bundle-child-ids-merge").val();



                                var bundle_child_ids = string.split(",");





                                var flag = 0;



                                $.each(bundle_child_ids, function(key, value) {

                                    var id = $("#conf_simple_pid_" + value).val();



                                    if ($("#conf_simple_pid_" + value).val() == "") {

                                        flag = 1;

                                    }

                                });



                                if (flag == 0) {

                                    $("#add_to_cart").attr("disabled", false);



                                    $("#addtocart_error").html("");

                                } else {

                                    $("#add_to_cart").attr("disabled", true);



                                    $("#addtocart_error").html("Not all values selected");

                                }

                            } else {

                                $("#quantity").val(1);



                                $("#quantity").attr("disabled", true);



                                $("#add_to_cart").attr("disabled", true);



                                $("#addtocart_error").html("Product is out of stock.");



                                return false;

                            }

                        } else {

                            $("#conf_simple_pid_" + bundle_child_id).val("");



                            $("#conf_simple_price_" + bundle_child_id).val("");



                            $("#conf_simple_qty_" + bundle_child_id).val("");



                            $("#add_to_cart").attr("disabled", true);



                            $("#addtocart_error").html(response.message);



                            return false;

                        }

                    },

                });

            } else {

                return false;

            }

        }

    }





    function converted_price(price, currency_conversion_rate) {

        let new_price = price * currency_conversion_rate;



        new_price = new_price.toFixed(2);



        new_price = numberWithCommas(new_price);



        return new_price;

    }



    function numberWithCommas(number) {

        var parts = number.toString().split(".");



        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");



        return parts.join(".");

    }



    function validateBundleQty(value) {

        var bundle_chid = $("#bundle-child-ids").val();



        var product_id = $("#product_id").val();



        var conf_simple_array = [];



        var string = $("#bundle-child-ids-merge").val();



        if (string != "") {

            var bundle_child_ids = string.split(",");



            $.each(bundle_child_ids, function(key, value) {

                var product_id_child_main = $("#product_id_child_main_" + value).val();



                var conf_simple_pid = $("#conf_simple_pid_" + value).val();



                conf_simple_array.push({

                    bundle_child_id: value,

                    product_id_child_main: product_id_child_main,

                    conf_simple_pid: conf_simple_pid,

                });

            });

        }



        $.ajax({

            url: BASE_URL + "ProductsController/getBundleChildValidateQty",



            type: "POST",



            dataType: "json",



            data: {

                product_id: product_id,



                bundle_chid_ids: bundle_chid,



                qty: value,



                conf_simple_array: conf_simple_array,

            },



            success: function(response) {

                if (response.status == 200) {

                    $("#add_to_cart").attr("disabled", false);



                    $("#addtocart_error").html("");

                } else {

                    $("#add_to_cart").attr("disabled", true);



                    $("#addtocart_error").html(

                        "This Product qty " + value + " is not available."

                    );



                    return false;

                }

            },

        });

    }







    $(document).ready(function() {





        $("#product-frm").validate({



            ignore: [],



            debug: false,



            rules: {

                product_name: {

                    required: true,

                },

            },



            errorPlacement: function(error, element) {

                error.insertAfter(element);

            },



            messages: {

                quantity: {

                    required: "Product quantity is required.",

                },

            },



            submitHandler: function(form) {



                $("#addtocart_error").html("");

                $("#add_to_cart").attr("disabled", true);

                if ($("#product_type").val() === "configurable" && (!$("#conf_simple_pid").val() || $("#conf_simple_pid").val() === "")) {
                    var checkedVal = $('input[name="variant_options_data"]:checked').val();
                    if (checkedVal) {
                        var parts = checkedVal.split(",");
                        if (parts[0]) {
                            $("#conf_simple_pid").val(parts[0]);
                        }
                    }
                }

                var form_obj = $("#product-frm")[0];



                var formData = new FormData(form_obj);



                $.ajax({

                    url: form.action,



                    type: "ajax",



                    method: form.method,



                    dataType: "json",



                    //enctype: 'multipart/form-data',



                    processData: false, // Important!



                    contentType: false,



                    cache: false,



                    data: formData,



                    beforeSend: function() {



                        $("#ajax-spinner").show();

                    },



                    success: function(response) {



                        $("#add_to_cart").attr("disabled", false);



                        $("#ajax-spinner").hide();



                        if (response.status == 200) {

                            $("#addtocart-message")

                                .html('<span class="success-msg">' + response.message + "</span>")



                                .show();



                            setTimeout(function() {

                                $("#addtocart-message").hide();

                            }, 3000);



                            $.ajax({

                                type: "POST",



                                url: BASE_URL + "CartController/updateminicart",



                                dataType: "html",



                                data: {},



                                success: function(response) {

                                    $("#mini-cart-main-container").html(response);

                                    $(".top-cart-content").css('display', 'block');



                                },

                            });



                            return false;

                        } else {

                            $("#addtocart-message")

                                .html('<span class="error-msg">' + response.message + "</span>")



                                .show();



                            setTimeout(function() {

                                $("#addtocart-message").hide();

                            }, 3000);



                            return false;

                        }

                    },

                });

            },

        });



        $(".required-field").each(function() {

            $(this).rules(

                "add",



                {

                    required: true,



                    messages: {

                        required: "Field is required",

                    },

                }

            );

        });



    });
</script>



<script>
    $(document).ready(function() {
        var $container = $('.badge-section');
        var imageCount = $container.find('img').length;

        if (imageCount > 3) {
            $container.removeClass('three-per-row').addClass('two-rows');
        } else {
            $container.removeClass('two-rows').addClass('three-per-row');
        }
    });

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

<!-- Floating Reviews Ribbon -->
<a class="reviews-ribbon" id="open-reviews-ribbon" href="#reviews" title="View Customer Reviews">REVIEWS</a>

<style>
/* Reviews Tab Figma Wireframe Styles */
#reviews {
    background: #ffffff;
    padding: 10px 0;
}
.review-summary {
    margin: 35px 0 0;
    border-top: 1px solid #ddd;
    padding: 40px 0 35px;
    display: grid;
    grid-template-columns: 351px 257px 1fr;
    gap: 28px;
    align-items: center;
}
.review-summary small {
    font-size: 12px;
    color: #444;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: block;
}
.review-summary h1 {
    font-size: 34px;
    font-weight: 500;
    margin: 18px 0 24px;
    line-height: 1.2;
    color: #111;
}
.stars {
    font-size: 26px;
    white-space: nowrap;
    letter-spacing: 3px;
    color: #111;
    background: #ffce00;
    padding: 3px 10px;
    display: inline-block;
    line-height: 1.2;
    border-radius: 2px;
}
.stars span {
    font-size: 14px;
    letter-spacing: 0;
    color: #111;
    margin-left: 8px;
    font-weight: 500;
}
.circle-pair {
    display: flex !important;
    gap: 17px;
    width: 257px;
    align-items: flex-start;
}
.circle-pair img {
    flex: 0 0 120px;
    width: 120px;
    height: 120px;
    object-fit: contain;
}
.circle-pair .badge-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 2px dashed #ccc;
    background: #fbfbfb;
    display: flex;
    align-items: center;
    justify-content: center;
}
.testimony {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #ffce00;
    border-radius: 55px;
    padding: 16px 24px;
    font-size: 15px;
}
.shopper-avatar {
    height: 68px;
    width: 68px;
    border-radius: 50%;
    background: #d0d0d0;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
    color: #555;
}
.mini-stars {
    font-size: 13px;
    margin: 4px 0;
    color: #111;
    letter-spacing: 1px;
}
.testimony small {
    font-size: 11px;
    color: #222;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.review-list {
    margin: 0 0 40px;
}
.list-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #aaa;
    padding: 16px 0;
    font-size: 14px;
    color: #333;
}
.list-heading .sort-btn {
    background: none;
    border: none;
    font-size: 14px;
    cursor: pointer;
    color: #333;
    font-weight: 500;
    padding: 4px 8px;
}
.list-heading .sort-btn:focus {
    outline: none;
}
.review {
    padding: 25px 0 20px;
    border-bottom: 1px solid #bbb;
}
.review-head {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
}
.avatar {
    background: #8d36ef;
    color: white;
    padding: 10px 12px;
    font-size: 15px;
    font-weight: bold;
    border-radius: 3px;
    display: inline-block;
    line-height: 1;
}
.verified {
    color: #2683b7;
    border-left: 1px solid #777;
    margin-left: 10px;
    padding-left: 12px;
    font-size: 13px;
    font-weight: normal;
}
.review-head p {
    font-size: 12px;
    color: #777;
    margin: 6px 0 0;
}
.review-head time {
    margin-left: auto;
    font-size: 13px;
    color: #777;
}
.item-score {
    font-size: 12px;
    margin: 18px 0 10px;
    color: #333;
    font-weight: 500;
}
.item-score span {
    color: #ffbf00;
    font-size: 16px;
}
.review h3 {
    font-size: 18px;
    margin: 10px 0 8px;
    font-weight: bold;
    color: #111;
}
.review p:not(.item-score) {
    font-size: 14px;
    line-height: 1.6;
    color: #444;
}
.helpful {
    font-size: 12px;
    color: #777;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
}
.helpful button {
    background: #eee;
    border: 0;
    color: #666;
    width: 26px;
    height: 24px;
    border-radius: 2px;
    cursor: pointer;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.helpful button[aria-pressed=true] {
    background: #ffce00;
    color: #111;
}
.show-more-btn {
    font-size: 14px;
    padding: 10px 28px;
    margin: 32px auto 10px;
    display: block;
    background: #087bf3;
    color: white;
    border: 0;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}
.show-more-btn:hover {
    background: #006ce0;
}
.reviews-ribbon {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    writing-mode: vertical-rl;
    text-orientation: upright;
    background: #ffce00;
    padding: 16px 8px;
    border-radius: 8px 0 0 8px;
    text-decoration: none !important;
    font-size: 12px;
    font-weight: bold;
    color: #111 !important;
    letter-spacing: 2px;
    box-shadow: -2px 3px 10px rgba(0,0,0,0.18);
    z-index: 999;
    cursor: pointer;
}
.reviews-ribbon:hover {
    background: #e6b800;
}
@media (max-width: 1000px) {
    .review-summary {
        grid-template-columns: 1fr 257px;
    }
    .testimony {
        grid-column: 1 / -1;
    }
}
@media (max-width: 700px) {
    .review-summary {
        display: block;
    }
    .circle-pair {
        margin: 20px 0;
    }
    .testimony {
        margin-top: 20px;
    }
    .reviews-ribbon {
        display: none;
    }
}
</style>

<script>
$(document).ready(function() {
    function activateReviewsTab() {
        $('a[href="#reviews"]').tab('show');
        if ($('#reviews').length) {
            $('html, body').animate({
                scrollTop: $('#reviews').offset().top - 90
            }, 500);
        }
    }

    $('#open-reviews-ribbon, .jump-to-reviews-btn').on('click', function(e) {
        e.preventDefault();
        activateReviewsTab();
    });

    if (window.location.hash === '#reviews') {
        activateReviewsTab();
    }

    // Toggle sort dropdown
    $('#review-sort-btn').on('click', function(e) {
        e.stopPropagation();
        $('#review-sort-dropdown').toggle();
    });

    $(document).on('click', function() {
        $('#review-sort-dropdown').hide();
    });

    // Handle sort options
    $('.sort-option').on('click', function(e) {
        e.preventDefault();
        var sortType = $(this).data('sort');
        var sortText = $(this).text();
        $('#sort-current-text').text('Sort: ' + sortText);
        $('.sort-option').removeClass('active');
        $(this).addClass('active');
        $('#review-sort-dropdown').hide();

        var $container = $('#reviews-container');
        var $items = $container.children('article.review').get();

        $items.sort(function(a, b) {
            var ratingA = parseInt($(a).data('rating')) || 0;
            var ratingB = parseInt($(b).data('rating')) || 0;
            var timeA = parseInt($(a).data('time')) || 0;
            var timeB = parseInt($(b).data('time')) || 0;

            if (sortType === 'highest') {
                return (ratingB - ratingA) || (timeB - timeA);
            } else if (sortType === 'lowest') {
                return (ratingA - ratingB) || (timeB - timeA);
            } else {
                return timeB - timeA;
            }
        });

        $.each($items, function(idx, item) {
            $container.append(item);
        });
    });

    // Helpful button feedback
    $(document).on('click', '[data-helpful]', function() {
        var $btn = $(this);
        var isPressed = $btn.attr('aria-pressed') === 'true';
        $btn.closest('.helpful').find('button').attr('aria-pressed', 'false');
        $btn.attr('aria-pressed', (!isPressed).toString());
    });

    // Show More pagination for reviews list
    var initialVisible = 2;
    var $allReviews = $('#reviews-container article.review');
    if ($allReviews.length > initialVisible) {
        $allReviews.slice(initialVisible).hide();
        $('#reviews-show-more').show();
    } else {
        $('#reviews-show-more').hide();
    }

    $('#reviews-show-more').on('click', function() {
        var $hidden = $('#reviews-container article.review:hidden');
        $hidden.slice(0, 5).fadeIn();
        if ($('#reviews-container article.review:hidden').length === 0) {
            $(this).hide();
        }
    });
});
</script>