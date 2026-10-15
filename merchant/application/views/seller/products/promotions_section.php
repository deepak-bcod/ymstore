<?php
$is_promo_enabled = false;
$promo_type = 'daily_deal';
$promo_starts_at = null;
$promo_ends_at = null;
$promo_special_val = '';
$regular_price_val = 0.00;

if (isset($ProductData) && is_object($ProductData)) {
    $is_promo_enabled = (!empty($ProductData->daily_deals) || !empty($ProductData->flash_sale) || (!empty($ProductData->special_price) && $ProductData->special_price > 0));
    $promo_type = (!empty($ProductData->flash_sale)) ? 'flash_sale' : 'daily_deal';

    if (!empty($ProductData->daily_deals) && !empty($ProductData->daily_deal_starts_at)) {
        $promo_starts_at = $ProductData->daily_deal_starts_at;
    } elseif (!empty($ProductData->flash_sale) && !empty($ProductData->flash_sale_starts_at)) {
        $promo_starts_at = $ProductData->flash_sale_starts_at;
    } elseif (!empty($ProductData->special_price_from)) {
        $promo_starts_at = $ProductData->special_price_from;
    }

    if (!empty($ProductData->daily_deals) && !empty($ProductData->daily_deal_ends_at)) {
        $promo_ends_at = $ProductData->daily_deal_ends_at;
    } elseif (!empty($ProductData->flash_sale) && !empty($ProductData->flash_sale_ends_at)) {
        $promo_ends_at = $ProductData->flash_sale_ends_at;
    } elseif (!empty($ProductData->special_price_to)) {
        $promo_ends_at = $ProductData->special_price_to;
    }

    if (!empty($ProductData->special_price) && $ProductData->special_price > 0) {
        $promo_special_val = number_format((float)$ProductData->special_price, 2, '.', '');
    }

    if (!empty($ProductData->webshop_price) && $ProductData->webshop_price > 0) {
        $regular_price_val = (float)$ProductData->webshop_price;
    } elseif (!empty($ProductData->price) && $ProductData->price > 0) {
        $regular_price_val = (float)$ProductData->price;
    }
}

$promo_start_val = $promo_starts_at ? date('Y-m-d\TH:i', $promo_starts_at) : '';
$promo_end_val = $promo_ends_at ? date('Y-m-d\TH:i', $promo_ends_at) : '';
?>

<div class="col-md-12 promo-pricing-section" style="margin-top: 25px; margin-bottom: 25px;">
    <div style="background: #ffffff; border: 1px solid #dce4ec; border-radius: 8px; padding: 22px 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <h2 style="font-size: 1.25em; font-weight: 700; color: #2c3e50; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid #edf2f7; display: flex; align-items: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            Promotions &amp; Special Pricing
        </h2>

        <div class="row">
            <div class="col-sm-12" style="margin-bottom: 15px;">
                <label class="checkbox font-weight-bold" style="font-size: 15px; cursor: pointer; user-select: none; display: inline-flex; align-items: center;">
                    <input type="checkbox" name="enable_promotional_offer" id="enable_promotional_offer" value="1" <?php echo ($is_promo_enabled ? 'checked' : ''); ?>>
                    &nbsp;<span style="font-weight: 700; color: #333;">Enable promotional offer</span>
                    <span class="checkmark"></span>
                </label>
                <p style="font-size: 0.88em; color: #7f8c8d; margin-top: 4px; margin-left: 24px;">Activate calendar-based Daily Deals or Flash Sales with special discounted pricing.</p>
            </div>
        </div>

        <div id="promotional_offer_fields" style="<?php echo ($is_promo_enabled ? 'display: block;' : 'display: none;'); ?>">
            <div class="row" style="margin-bottom: 18px;">
                <div class="col-sm-12">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block; color: #34495e;">Promotion type</label>
                    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                        <label class="radio-inline" style="font-size: 14px; cursor: pointer; margin-right: 25px;">
                            <input type="radio" name="promo_type" id="promo_type_daily_deal" value="daily_deal" <?php echo ($promo_type !== 'flash_sale' ? 'checked' : ''); ?>>
                            &nbsp;<strong style="color: #d48806;">★ Daily Deal</strong> <span style="color: #666; font-size: 0.9em;">— scheduled promotional offer</span>
                        </label>
                        <label class="radio-inline" style="font-size: 14px; cursor: pointer;">
                            <input type="radio" name="promo_type" id="promo_type_flash_sale" value="flash_sale" <?php echo ($promo_type === 'flash_sale' ? 'checked' : ''); ?>>
                            &nbsp;<strong style="color: #cf1322;">⚡ Flash Sale</strong> <span style="color: #666; font-size: 0.9em;">— time-sensitive promotion</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                    <label style="font-weight: 600; color: #34495e;">Regular price (MUR)</label>
                    <input type="text" class="form-control" id="promo_regular_price_display" readonly value="<?php echo number_format($regular_price_val, 2); ?>" style="background-color: #f8fafc; font-weight: 600; color: #475569; border-color: #cbd5e1;">
                    <small style="color: #94a3b8; font-size: 11px;">Derived from EShop price</small>
                </div>
                <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                    <label style="font-weight: 600; color: #34495e;">Special price (MUR) <span style="color: red;">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" name="promo_special_price" id="promo_special_price" value="<?php echo $promo_special_val; ?>" placeholder="e.g. 990.00" style="border-color: #cbd5e1;">
                </div>
                <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                    <label style="font-weight: 600; color: #34495e;">Start date &amp; time <span style="color: red;">*</span></label>
                    <input type="datetime-local" class="form-control" name="promo_start_date" id="promo_start_date" value="<?php echo $promo_start_val; ?>" style="border-color: #cbd5e1;">
                </div>
                <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                    <label style="font-weight: 600; color: #34495e;">End date &amp; time <span style="color: red;">*</span></label>
                    <input type="datetime-local" class="form-control" name="promo_end_date" id="promo_end_date" value="<?php echo $promo_end_val; ?>" style="border-color: #cbd5e1;">
                </div>
            </div>

            <!-- Discount Preview Box -->
            <div id="promo_discount_preview_box" style="margin-top: 15px; padding: 16px 20px; background: #fffcf0; border: 1px solid #ffd591; border-radius: 6px;">
                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #d46b08;">Discount preview</span>
                <div style="display: flex; align-items: baseline; gap: 18px; margin-top: 6px; flex-wrap: wrap;">
                    <h3 id="promo_preview_discount_text" style="margin: 0; font-size: 22px; font-weight: 800; color: #d4380d;"># 0% off</h3>
                    <div id="promo_preview_price_transition" style="font-size: 15px; font-weight: 600; color: #434343;">MUR 0.00 → MUR 0.00</div>
                </div>
                <div id="promo_validation_warning" style="display: none; color: #cf1322; font-size: 13px; margin-top: 8px; font-weight: 600;"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initPromoScript() {
        var enableCheckbox = document.getElementById('enable_promotional_offer');
        var promoFields = document.getElementById('promotional_offer_fields');
        var regularDisplay = document.getElementById('promo_regular_price_display');
        var specialInput = document.getElementById('promo_special_price');
        var startDateInput = document.getElementById('promo_start_date');
        var endDateInput = document.getElementById('promo_end_date');
        var discountText = document.getElementById('promo_preview_discount_text');
        var transitionText = document.getElementById('promo_preview_price_transition');
        var warningBox = document.getElementById('promo_validation_warning');

        if (!enableCheckbox || !promoFields) return;

        function getActiveRegularPrice() {
            var mainWebshopInput = document.getElementById('webshop_price');
            if (mainWebshopInput && parseFloat(mainWebshopInput.value) > 0) {
                return parseFloat(mainWebshopInput.value);
            }
            var bundleWebshopInput = document.getElementById('bundle_webshop_price');
            if (bundleWebshopInput && parseFloat(bundleWebshopInput.value) > 0) {
                return parseFloat(bundleWebshopInput.value);
            }
            if (regularDisplay) {
                var cleaned = regularDisplay.value.replace(/[^0-9.]/g, '');
                var val = parseFloat(cleaned);
                if (!isNaN(val) && val > 0) return val;
            }
            return 0;
        }

        function updatePreview() {
            var regular = getActiveRegularPrice();
            if (regularDisplay) {
                regularDisplay.value = regular.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            var special = parseFloat(specialInput ? specialInput.value : 0);
            var hasWarning = false;
            var warningMsg = '';

            if (special > 0 && regular > 0) {
                if (special >= regular) {
                    hasWarning = true;
                    warningMsg = 'Special price (MUR ' + special.toFixed(2) + ') must be less than regular price (MUR ' + regular.toFixed(2) + ').';
                    discountText.innerText = '# 0% off';
                    transitionText.innerText = 'MUR ' + regular.toFixed(2) + ' → MUR ' + special.toFixed(2);
                } else {
                    var discountPct = ((regular - special) / regular) * 100;
                    discountText.innerText = '# ' + discountPct.toFixed(2) + '% off';
                    transitionText.innerText = 'MUR ' + regular.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' → MUR ' + special.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            } else {
                discountText.innerText = '# 0% off';
                transitionText.innerText = 'MUR ' + regular.toFixed(2) + ' → MUR ' + (special > 0 ? special.toFixed(2) : '0.00');
            }

            if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
                var startVal = new Date(startDateInput.value);
                var endVal = new Date(endDateInput.value);
                if (endVal <= startVal) {
                    hasWarning = true;
                    warningMsg = (warningMsg ? warningMsg + ' ' : '') + 'End date & time must be later than start date & time.';
                }
            }

            if (warningBox) {
                if (hasWarning) {
                    warningBox.innerText = warningMsg;
                    warningBox.style.display = 'block';
                } else {
                    warningBox.style.display = 'none';
                    warningBox.innerText = '';
                }
            }
        }

        enableCheckbox.addEventListener('change', function() {
            if (this.checked) {
                promoFields.style.display = 'block';
                updatePreview();
            } else {
                promoFields.style.display = 'none';
            }
        });

        if (specialInput) {
            specialInput.addEventListener('input', updatePreview);
            specialInput.addEventListener('change', updatePreview);
        }
        if (startDateInput) {
            startDateInput.addEventListener('change', updatePreview);
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', updatePreview);
        }

        var mainWebshop = document.getElementById('webshop_price');
        if (mainWebshop) {
            mainWebshop.addEventListener('input', updatePreview);
            mainWebshop.addEventListener('change', updatePreview);
            mainWebshop.addEventListener('blur', updatePreview);
        }

        if (enableCheckbox.checked) {
            updatePreview();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPromoScript);
    } else {
        initPromoScript();
    }
})();
</script>
