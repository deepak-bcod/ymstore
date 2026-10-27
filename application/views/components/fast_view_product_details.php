<?php $lang = $this->session->userdata('site_lang'); ?>
    <div class="container-fluid">
        <div class="row">
            <!-- BEGIN CONTENT -->
            <div class="col-md-12">
                <div class="product-page">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="product-name">
                                <?php echo (($lang == "french" && isset($ProductData->lang_title) && $ProductData->lang_title != '') ? $ProductData->lang_title : $ProductData->name); ?>
                            </h1>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="product-image-section" id="product-image-section">
                                <?php $this->load->view('product/media_gallery') ?>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-8">
                            <ul class="list-group">
                                <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name) && $ProductData->publication_name !== 'Anu Test Developer Account') { ?>
                                    <li class="list-group-item"><b><?= lang('merchant') ?>:
                                        </b><?php echo $ProductData->publication_name; ?>
                                    </li>
                                <?php } ?>
                                <?php if($lang == "french") { ?>
                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) {
                                        if (isset($attr->attr_value_lang) && !empty($attr->attr_value_lang)) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name_lang ?>:
                                                </b><?php echo $attr->attr_value_lang ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php } else { ?>
                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) {
                                        if (isset($attr->attr_value) && !empty($attr->attr_value)) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name ?>:
                                                </b><?php echo $attr->attr_value ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php  } ?>
                                            
                                
                            </ul>
                            <form name="product-frm" id="quick-product-frm">
                                <input type="hidden" name="media_variant_id" id="media_variant_id" value="<?php echo  $ProductData->media_variant_id; ?>">

                                <input type="hidden" name="product_type" id="product_type" value="<?php echo  $ProductData->product_type; ?>">

                                <?php if ($ProductData->product_type == 'simple') { ?>
                                    <div class="table-responsive product-price">
                                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                                            <thead>
                                                <tr class="active">
                                                    <th><?= lang('price') ?> <?= lang('mur') ?></th>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <th><?= lang('offer_price') ?> <?= lang('mur') ?></th>
                                                    <?php } ?>
                                                    <!-- <th>Issues</th>
                                                    <th>Gift</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo number_format((float)$ProductData->webshop_price, 2); ?></td>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <td>
    <?php echo number_format((float)$ProductData->special_price, 2); ?>
    <?php echo $ProductData->off_percent_price; ?><?= lang('percent_off') ?>
</td>
                                                    <?php } ?>
                                                    <!-- <td><?php //echo $ProductData->sub_issues; ?> issues</td>
                                                    <td><?php //echo $ProductData->gift_master_name; ?> </td> -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                                <?php (new ProductDetails())->productStockVariant($ProductData, $CategoryIds); ?>

                                <?php
                                if (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == 1) { ?>
                                    <!-- To use when items OUT of stock (no variants - STARTS) -->
                                    <div class="notifywrap">
                                        <div class="notifyhd">
                                            <i class="fa fa-exclamation-circle fa-2x"></i> <span><?= lang('out_of_stock_label') ?></span>
                                        </div>
                                        <div class="notifyfrm">
                                            <p><?= lang('out_of_stock_message') ?></p>
                                            <div class="form-inline" id="keepnotified">
                                                <div class="form-group">
                                                    <label class="sr-only" for="notified-email"><?= lang('email_address_label') ?></label>
                                                    <input type="email" class="form-control" id="notified-email" placeholder="<?= lang('email_placeholder') ?>" value="<?php echo $_SESSION["EmailID"] ?? ""; ?>" onkeyup="ValidateEmail();">

                                                </div>

                                                <button type="button" id="notified-keep-inform" class="btn btn-primary" onclick=openNotifiedPopup(<?php echo $ProductData->id; ?>)><?= lang('keep_me_informed_btn') ?></button>
                                            </div>
                                            <span id="lblError" class="notified-error"></span>
                                        </div>
                                    </div>
                                    <!-- To use when items OUT of stock (no variants - ENDS) -->

                                <?php } ?>

                                <div class="subscribe-deli">
                                    <?php if (isset($ProductData->stock_status) && ($ProductData->stock_status == 'Instock') && (!isset($ProductData->total_qty) || (float)$ProductData->total_qty > 0) && (!isset($ProductData->qty) || (float)$ProductData->qty > 0) && (!isset($ProductData->stock_qty) || (float)$ProductData->stock_qty > 0)) { ?>
                                        <?php if ($restricted_access == "yes" && $customer_id == 0) { ?>
                                            <button type="button" class="btn btn-primary subscr-now pull-left as" <?php echo isset($ProductData->stock_status) &&
                                                                                                                        ($ProductData->stock_status == "Instock" &&
                                                                                                                            $ProductData->product_type == "simple")
                                                                                                                        ? ""
                                                                                                                        : "disabled"; ?> onclick="openRestrictedAccessPopup()" class="add-to-cart-btn">
                                                <?= lang('add_to_cart_btn') ?>
                                            </button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent"> <?= lang('more_details') ?></a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b><?= lang('expected_delivery') ?>: <?php echo $ProductData->estimate_delivery_time; ?> <?= lang('days') ?>.</b></p>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="button" id="add_to_cart" onclick="Quickaddtocart()" class="btn btn-primary subscr-now pull-left as"><?= lang('add_to_cart_btn') ?></button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent"><?= lang('more_details') ?></a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b><?= lang('expected_delivery') ?>: <?php echo $ProductData->estimate_delivery_time; ?> <?= lang('days') ?>.</b></p>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-primary subscr-now pull-left" id="add_to_cart" type="submit" disabled><?= lang('out_of_stock') ?></button>
                                    <?php } ?>
                                    <div id="addtocart-message" class="addtocart-message"></div>

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

                                    $has_active_daily_deal = ($is_daily_deal_detail && $dd_ends_at_detail > $now && !$is_out_of_stock_detail);
                                    $countdown_str = '';
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
                                    $stock_pad_top = $has_active_daily_deal ? '4px' : '0px';
                                    ?>

                                    <div class="promo-deal-info-container" style="clear: both; padding-top: 10px !important;">
                                        <!-- Countdown Timer: Only show if Daily Deal is active (or both Daily Deal & Flash Sale) -->
                                        <?php if ($has_active_daily_deal): ?>
                                            <div class="promo-countdown-wrapper" style="clear: both; color: #e52525 !important; font-size: 13.5px !important; font-weight: normal !important; display: flex !important; align-items: center !important; gap: 5px !important; margin-bottom: 4px !important;">
                                                <i class="fa fa-clock-o" style="color: #e52525 !important; font-size: 14px !important;"></i>
                                                <span>Ends in <span class="live-countdown" data-ends-at="<?= $dd_ends_at_detail ?>"><?= $countdown_str ?></span></span>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Product Count Left: Show if Flash Sale is active (or both Daily Deal & Flash Sale, or limited stock <= 10) -->
                                        <div id="quickview-stock-left" class="promo-stock-wrapper" data-is-flash-sale="<?= $is_flash_sale_detail ? '1' : '0' ?>" data-is-daily-deal="<?= $is_daily_deal_detail ? '1' : '0' ?>" style="clear: both; padding-top: <?= $stock_pad_top ?> !important; color: #e52525 !important; font-size: 13px !important; font-weight: normal !important; display: <?= $show_stock_initially ? 'flex' : 'none' ?> !important; align-items: center !important; gap: 5px !important;">
                                            <i class="fa fa-fire" style="color: #e52525 !important; font-size: 13px !important;"></i>
                                            <span>Only <strong id="quickview-stock-qty" style="font-weight: 600;"><?= ($remaining_stock !== null && $remaining_stock > 0) ? $remaining_stock : '' ?></strong> left</span>
                                        </div>
                                    </div>

                                    <script>
                                    if (typeof tickModalCountdowns === 'function') {
                                        tickModalCountdowns();
                                    }
                                    </script>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
    </div>