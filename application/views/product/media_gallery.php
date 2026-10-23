<div class="product-main-image" style="position: relative;">
    <?php $default_product_image='';
		$default_product_image = ((isset($ProductData->base_image) && $ProductData->base_image!='') ? PRODUCT_LARGE_IMG.$ProductData->base_image : PRODUCT_DEFAULT_IMG);
   
        if (empty($refresh_flag)) { ?>
        <img src="<?php echo $default_product_image; ?>" 
            alt="<?php echo $ProductData->name; ?>"
            data-BigImgsrc="<?php echo $default_product_image; ?>"
            class="img-responsive">
    <?php } ?>

    <?php
    // Determine promotion and badge info for Product Details
    $is_out_of_stock = false;
    $product_stock = null;
    if (isset($ProductData->qty) && is_numeric($ProductData->qty)) {
        $product_stock = (float)$ProductData->qty;
    } elseif (isset($ProductData->stock_qty) && is_numeric($ProductData->stock_qty)) {
        $product_stock = (float)$ProductData->stock_qty;
    } elseif (isset($ProductData->total_qty) && is_numeric($ProductData->total_qty)) {
        $product_stock = (float)$ProductData->total_qty;
    } elseif (isset($ProductData->quantity) && is_numeric($ProductData->quantity)) {
        $product_stock = (float)$ProductData->quantity;
    }

    if ($product_stock !== null && $product_stock <= 0) {
        $is_out_of_stock = true;
    } elseif (isset($ProductData->stock_status) && (strtolower(trim($ProductData->stock_status)) == 'out of stock' || strtolower(trim($ProductData->stock_status)) == 'outofstock')) {
        $is_out_of_stock = true;
    }

    $now = time();
    $is_daily_deal = false;
    $is_flash_sale = false;

    // Check directly in database for current promo status and end times
    if (!empty($ProductData->id)) {
        $ci =& get_instance();
        if (isset($ci->db)) {
            $p_promo = $ci->db->select('daily_deals, daily_deal_ends_at, flash_sale, flash_sale_ends_at')
                ->where('id', $ProductData->id)
                ->get('products')
                ->row();
            if ($p_promo) {
                if (!empty($p_promo->flash_sale) && $p_promo->flash_sale == 1 && (empty($p_promo->flash_sale_ends_at) || (int)$p_promo->flash_sale_ends_at > $now)) {
                    $is_flash_sale = true;
                }
                if (!$is_flash_sale && !empty($p_promo->daily_deals) && $p_promo->daily_deals == 1 && (empty($p_promo->daily_deal_ends_at) || (int)$p_promo->daily_deal_ends_at > $now)) {
                    $is_daily_deal = true;
                }
            }
        }
    }

    if (!$is_flash_sale) {
        if (!empty($ProductData->flash_sale) && $ProductData->flash_sale == 1 && (empty($ProductData->flash_sale_ends_at) || (int)$ProductData->flash_sale_ends_at > $now)) {
            $is_flash_sale = true;
        } elseif (!empty($ProductData->offer_type) && $ProductData->offer_type === 'flash_sale' && (empty($ProductData->flash_sale_ends_at) || (int)$ProductData->flash_sale_ends_at > $now)) {
            $is_flash_sale = true;
        }
    }

    if (!$is_flash_sale && !$is_daily_deal) {
        if (!empty($ProductData->daily_deals) && $ProductData->daily_deals == 1 && (empty($ProductData->daily_deal_ends_at) || (int)$ProductData->daily_deal_ends_at > $now)) {
            $is_daily_deal = true;
        } elseif (!empty($ProductData->daily_deal) && $ProductData->daily_deal == 1 && (empty($ProductData->daily_deal_ends_at) || (int)$ProductData->daily_deal_ends_at > $now)) {
            $is_daily_deal = true;
        } elseif (!empty($ProductData->offer_type) && $ProductData->offer_type === 'daily_deal' && (empty($ProductData->daily_deal_ends_at) || (int)$ProductData->daily_deal_ends_at > $now)) {
            $is_daily_deal = true;
        }
    }

    $regular_price = !empty($ProductData->webshop_price) ? (float)$ProductData->webshop_price : 0;
    $special_price = !empty($ProductData->special_price) ? (float)$ProductData->special_price : 0;
    $discount_percent = 0;
    if ($special_price > 0 && $regular_price > $special_price) {
        $discount_percent = (int)round((($regular_price - $special_price) / $regular_price) * 100);
    }
    ?>

    <!-- Badges inside product image at TOP (Daily Deals, Flash Sales, Discount Pill, Out of Stock) -->
    <?php if ($is_out_of_stock || $is_daily_deal || $is_flash_sale || $discount_percent > 0): ?>
        <span class="pi-img-top-badges" style="position: absolute !important; top: 12px !important; left: 12px !important; right: 12px !important; display: flex !important; align-items: flex-start !important; justify-content: space-between !important; gap: 6px !important; z-index: 8 !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; width: calc(100% - 24px) !important; background: transparent !important;">
            <span style="display: flex !important; align-items: center !important; gap: 6px !important;">
                <?php if ($is_out_of_stock): ?>
                    <span class="badge-out-of-stock" style="background-color: #dc3545 !important; color: #ffffff !important; font-size: 11px !important; font-weight: 800 !important; padding: 3px 8px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">OUT OF STOCK</span>
                <?php elseif ($is_flash_sale): ?>
                    <span class="badge-deal-flash" style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 11px !important; font-weight: 800 !important; padding: 3px 8px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 3px !important;">⚡ FLASH SALE</span>
                <?php elseif ($is_daily_deal): ?>
                    <span class="badge-deal-daily" style="background-color: #ffd200 !important; color: #000000 !important; font-size: 11px !important; font-weight: 800 !important; padding: 3px 8px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 3px !important;">★ DAILY DEAL</span>
                <?php endif; ?>
            </span>

            <?php if ($discount_percent > 0 && !$is_out_of_stock): ?>
                <span style="margin-left: auto !important; display: inline-block !important;">
                    <span class="badge-discount-pill" style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 11px !important; font-weight: 800 !important; padding: 3px 8px !important; border-radius: 12px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">-<?= $discount_percent ?>%</span>
                </span>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</div>

<div class="product-other-images">
    <?php if(isset($ProductData->mediaGallery) && !empty($ProductData->mediaGallery)){
        foreach($ProductData->mediaGallery as $media){
            if($media->is_base_image == 1 && !empty($refresh_flag)){ 
                continue;
            }
            $product_image='';
            $product_image = ((isset($media->image) && $media->image!='') ? PRODUCT_LARGE_IMG.$media->image : PRODUCT_DEFAULT_IMG);	
            ?>
            <a href="<?php echo $product_image; ?>" class="fancybox-button" rel="photos-lib">
                <img alt="<?php echo $ProductData->name; ?>" src="<?php echo $product_image; ?>">
            </a>

        <?php } ?>
    <?php }else{ ?>
        <a href="<?php echo PRODUCT_DEFAULT_IMG; ?>" class="fancybox-button" rel="photos-lib">
            <img alt="<?php echo $ProductData->name; ?>" src="<?php echo PRODUCT_DEFAULT_IMG; ?>">
        </a>
    <?php } ?>
</div>