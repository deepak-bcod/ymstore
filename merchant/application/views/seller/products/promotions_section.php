<?php
/**
 * Special Pricing Section for Edit Product (Merchant)
 * Matches YellowMarkets Special Pricing specification
 */

$product_name_val = '';
$eshop_price_val = 0.00;
$special_price_val = '';
$from_date_val = '';
$to_date_val = '';
$display_original_val = 1;
$promo_type_val = 'daily_deal';

if (isset($ProductData) && is_object($ProductData)) {
    $product_name_val = $ProductData->name ?? '';
    $eshop_price_val = (float)($ProductData->webshop_price ?: ($ProductData->price ?: 0));
    if (!empty($ProductData->special_price) && (float)$ProductData->special_price > 0) {
        $special_price_val = number_format((float)$ProductData->special_price, 2, '.', '');
    }

    $starts_ts = !empty($ProductData->special_price_from) ? $ProductData->special_price_from : null;
    $ends_ts = !empty($ProductData->special_price_to) ? $ProductData->special_price_to : null;

    if (!empty($starts_ts)) {
        $from_date_val = date('Y-m-d', is_numeric($starts_ts) ? (int)$starts_ts : strtotime($starts_ts));
    }
    if (!empty($ends_ts)) {
        $to_date_val = date('Y-m-d', is_numeric($ends_ts) ? (int)$ends_ts : strtotime($ends_ts));
    }

    if (!empty($ProductData->flash_sale)) {
        $promo_type_val = 'flash_sale';
    } else {
        $promo_type_val = 'daily_deal';
    }
}

// Enrich from products_special_prices if available
if (isset($SpecialPriceData) && is_object($SpecialPriceData)) {
    if (!empty($SpecialPriceData->special_price) && (float)$SpecialPriceData->special_price > 0) {
        $special_price_val = number_format((float)$SpecialPriceData->special_price, 2, '.', '');
    }
    if (!empty($SpecialPriceData->special_price_from)) {
        $from_date_val = date('Y-m-d', is_numeric($SpecialPriceData->special_price_from) ? (int)$SpecialPriceData->special_price_from : strtotime($SpecialPriceData->special_price_from));
    }
    if (!empty($SpecialPriceData->special_price_to)) {
        $to_date_val = date('Y-m-d', is_numeric($SpecialPriceData->special_price_to) ? (int)$SpecialPriceData->special_price_to : strtotime($SpecialPriceData->special_price_to));
    }
    if (isset($SpecialPriceData->display_original)) {
        $display_original_val = (int)$SpecialPriceData->display_original;
    }
    if (!empty($SpecialPriceData->offer_type)) {
        $promo_type_val = $SpecialPriceData->offer_type;
    }
}
?>

<div class="col-md-12 special-pricing-container" style="margin-top: 30px; margin-bottom: 30px;">
    <!-- Section Title matching screenshot -->
    <div style="border-bottom: 2px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 25px;">
        <span style="color: #ff8a00; border-bottom: 3px solid #ff8a00; padding-bottom: 11px; font-weight: 700; font-size: 13.5px; letter-spacing: 0.8px; text-transform: uppercase; display: inline-block;">
            SPECIAL PRICING
        </span>
    </div>

    <!-- Hidden compatibility flag -->
    <input type="hidden" name="enable_promotional_offer" id="enable_promotional_offer" value="1">

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6 col-sm-6">
            <!-- Product Name -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">Product Name</label>
                <input type="text" class="form-control" name="sp_product_name_display" id="sp_product_name_display" readonly value="<?php echo htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Product Name" style="background: #ffffff; border: 1px solid #ced4da; border-radius: 4px; height: 40px; box-shadow: none; font-size: 13.5px;">
            </div>

            <!-- Eshop Price -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">Eshop Price :</label>
                <input type="text" class="form-control" readonly id="sp_eshop_price_display" value="<?php echo ($eshop_price_val > 0) ? number_format($eshop_price_val, 2, '.', '') : ''; ?>" placeholder="" style="background: #e9ecef; border: 1px solid #ced4da; border-radius: 4px; height: 40px; color: #495057; font-weight: 600; box-shadow: none; font-size: 13.5px;">
            </div>

            <!-- From Date -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">From</label>
                <input type="date" class="form-control" name="from_date" id="sp_from_date" value="<?php echo $from_date_val; ?>" min="<?php echo date('Y-m-d'); ?>" placeholder="mm/dd/yyyy" style="background: #ffffff; border: 1px solid #ced4da; border-radius: 4px; height: 40px; box-shadow: none; font-size: 13.5px;">
            </div>

            <!-- Display Original Toggle -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; align-items: center; gap: 16px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 0;">Display Original</label>
                <label class="sp-switch-toggle" style="position: relative; display: inline-block; width: 44px; height: 22px; margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="display_original" id="sp_display_original" value="1" <?php echo ($display_original_val ? 'checked' : ''); ?> style="opacity: 0; width: 0; height: 0;">
                    <span class="sp-slider-round"></span>
                </label>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6 col-sm-6">
            <!-- Alignment spacer to match Left Column layout -->
            <div class="form-group" style="margin-bottom: 24px; visibility: hidden; height: 40px; display: none;">
                <label>&nbsp;</label>
            </div>

            <!-- Special Price -->
            <div class="form-group" style="margin-bottom: 24px; margin-top: 66px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">Special Price :</label>
                <input type="number" step="0.01" min="0" class="form-control" name="special_price" id="sp_special_price" value="<?php echo $special_price_val; ?>" placeholder="Special Price" style="background: #ffffff; border: 1px solid #ced4da; border-radius: 4px; height: 40px; box-shadow: none; font-size: 13.5px;">
                <input type="hidden" name="promo_special_price" id="promo_special_price" value="<?php echo $special_price_val; ?>">
            </div>

            <!-- To Date -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">To</label>
                <input type="date" class="form-control" name="to_date" id="sp_to_date" value="<?php echo $to_date_val; ?>" min="<?php echo date('Y-m-d'); ?>" placeholder="mm/dd/yyyy" style="background: #ffffff; border: 1px solid #ced4da; border-radius: 4px; height: 40px; box-shadow: none; font-size: 13.5px;">
            </div>

            <!-- Promotion Type Selection (Daily Deal vs Flash Sale integration) -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-size: 13px; font-weight: 600; color: #495057; margin-bottom: 6px; display: block;">Promotion Type :</label>
                <div style="display: flex; gap: 24px; align-items: center; margin-top: 5px;">
                    <label style="font-size: 13px; cursor: pointer; color: #333; margin-right: 15px; font-weight: normal; margin-bottom: 0;">
                        <input type="radio" name="promo_type" id="sp_promo_type_daily" value="daily_deal" <?php echo ($promo_type_val !== 'flash_sale' ? 'checked' : ''); ?>>
                        &nbsp;<strong style="color: #d48806;">★ Daily Deal</strong>
                    </label>
                    <label style="font-size: 13px; cursor: pointer; color: #333; font-weight: normal; margin-bottom: 0;">
                        <input type="radio" name="promo_type" id="sp_promo_type_flash" value="flash_sale" <?php echo ($promo_type_val === 'flash_sale' ? 'checked' : ''); ?>>
                        &nbsp;<strong style="color: #cf1322;">⚡ Flash Sale</strong>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview & Notice Bar -->
    <div id="sp_preview_row" style="margin-top: 15px; margin-bottom: 10px; display: none;">
        <div style="padding: 10px 16px; background: #fffcf0; border: 1px solid #ffd591; border-radius: 4px; font-size: 12.5px; color: #874d00; display: flex; align-items: center; justify-content: space-between;">
            <span id="sp_preview_text"></span>
            <span id="sp_discount_badge" style="background: #e52525; color: #fff; font-weight: 700; border-radius: 12px; padding: 2px 8px; font-size: 11px;"></span>
        </div>
    </div>

    <!-- Discard & Save Action Buttons matching screenshot -->
    <div class="row" style="margin-top: 30px; margin-bottom: 10px;">
        <div class="col-xs-12" style="display: flex; justify-content: flex-end; align-items: center; gap: 14px;">
            <button type="button" id="sp_discard_btn" style="background: #ffffff; color: #222222; border: 1.5px solid #222222; padding: 7px 26px; font-size: 13.5px; font-weight: 600; border-radius: 4px; cursor: pointer; transition: all 0.2s; outline: none;">
                Discard
            </button>
            <button type="submit" id="sp_save_btn" name="save_product" value="Save" style="background: #2b2b2b; color: #ffffff; border: 1.5px solid #2b2b2b; padding: 7px 32px; font-size: 13.5px; font-weight: 600; border-radius: 4px; cursor: pointer; transition: all 0.2s; outline: none;">
                Save
            </button>
        </div>
    </div>
</div>

<style>
/* Clean Toggle Switch styling for Special Pricing */
.sp-switch-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 22px;
}
.sp-switch-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}
.sp-slider-round {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ced4da;
    transition: .3s;
    border-radius: 22px;
}
.sp-slider-round:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: #ffffff;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.sp-switch-toggle input:checked + .sp-slider-round {
    background-color: #2b2b2b;
}
.sp-switch-toggle input:checked + .sp-slider-round:before {
    transform: translateX(22px);
}
#sp_discard_btn:hover {
    background-color: #f8f9fa;
    border-color: #000;
}
#sp_save_btn:hover {
    background-color: #000000;
    border-color: #000000;
}
</style>

<script>
(function() {
    // Initial loaded state snapshot for Discard action
    var initialData = {
        special_price: <?php echo json_encode($special_price_val); ?>,
        from_date: <?php echo json_encode($from_date_val); ?>,
        to_date: <?php echo json_encode($to_date_val); ?>,
        display_original: <?php echo json_encode((bool)$display_original_val); ?>,
        promo_type: <?php echo json_encode($promo_type_val); ?>
    };

    var specialPriceInput = document.getElementById('sp_special_price');
    var promoSpecialPriceHidden = document.getElementById('promo_special_price');
    var fromDateInput = document.getElementById('sp_from_date');
    var toDateInput = document.getElementById('sp_to_date');
    var displayOriginalInput = document.getElementById('sp_display_original');
    var eshopPriceInput = document.getElementById('sp_eshop_price_display');
    var discardBtn = document.getElementById('sp_discard_btn');
    var previewRow = document.getElementById('sp_preview_row');
    var previewText = document.getElementById('sp_preview_text');
    var discountBadge = document.getElementById('sp_discount_badge');

    function updatePreview() {
        var eshop = parseFloat(eshopPriceInput ? eshopPriceInput.value : 0) || 0;
        var special = parseFloat(specialPriceInput ? specialPriceInput.value : 0) || 0;
        if (promoSpecialPriceHidden) {
            promoSpecialPriceHidden.value = special > 0 ? special.toFixed(2) : '';
        }

        if (special > 0 && eshop > 0 && special < eshop) {
            var discountPct = Math.round(((eshop - special) / eshop) * 100);
            if (previewText) {
                previewText.textContent = 'Promotional Price: MUR ' + special.toFixed(2) + ' (Original: MUR ' + eshop.toFixed(2) + ')';
            }
            if (discountBadge) {
                discountBadge.textContent = '-' + discountPct + '% OFF';
            }
            if (previewRow) {
                previewRow.style.display = 'block';
            }
        } else {
            if (previewRow) {
                previewRow.style.display = 'none';
            }
        }
    }

    if (specialPriceInput) {
        specialPriceInput.addEventListener('input', updatePreview);
    }

    // Discard button resets inputs to initial loaded state without modifying saved pricing
    if (discardBtn) {
        discardBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (specialPriceInput) specialPriceInput.value = initialData.special_price;
            if (promoSpecialPriceHidden) promoSpecialPriceHidden.value = initialData.special_price;
            if (fromDateInput) fromDateInput.value = initialData.from_date;
            if (toDateInput) toDateInput.value = initialData.to_date;
            if (displayOriginalInput) displayOriginalInput.checked = initialData.display_original;

            var dailyRadio = document.getElementById('sp_promo_type_daily');
            var flashRadio = document.getElementById('sp_promo_type_flash');
            if (initialData.promo_type === 'flash_sale') {
                if (flashRadio) flashRadio.checked = true;
            } else {
                if (dailyRadio) dailyRadio.checked = true;
            }
            updatePreview();
        });
    }

    updatePreview();
})();
</script>
