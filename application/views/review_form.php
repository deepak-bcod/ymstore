<?php $this->load->view('common/header'); ?>

<?php
$is_view_mode = (isset($mode) && $mode === 'view');
$is_edit_mode = (isset($mode) && $mode === 'edit');
$merchant_trade_name = !empty($merchant->trade_name) ? $merchant->trade_name : (!empty($merchant->vendor_name) ? $merchant->vendor_name : (!empty($merchant->publication_name) ? $merchant->publication_name : 'Yellow Markets Merchant'));
$merchant_rating_val = isset($existing_merchant_review->rating) ? (int)$existing_merchant_review->rating : 0;
$merchant_comment_val = isset($existing_merchant_review->comments) ? $existing_merchant_review->comments : '';
?>

<div class="ym-review-page-container">
    <div class="ym-review-card">
        <!-- LEFT COLUMN: Brand info & copy -->
        <div class="ym-review-left-col">
            <div class="ym-review-brand-section">
                <div class="ym-review-avatar-placeholder">
                    <?php if (!empty($merchant->shop_image) && file_exists(FCPATH . 'merchant/public/images/shop_images/' . $merchant->shop_image)): ?>
                        <img src="<?= base_url('merchant/public/images/shop_images/' . $merchant->shop_image) ?>" alt="Merchant Logo" class="ym-merchant-avatar-img">
                    <?php else: ?>
                        <div class="ym-avatar-circle"></div>
                    <?php endif; ?>
                </div>

                <h1 class="ym-review-title">Yellow Markets Reviews</h1>
                <h2 class="ym-review-subtitle">Your Opinion Counts</h2>

                <div class="ym-review-copy">
                    <p>Thank you for contributing to our community by reviewing the vendor's service and products.</p>
                    <p>Your feedback aids shoppers and merchants on Yellow Markets.com. Rate your experience and products from 1 to 5, where 1 is poor and 5 is excellent.</p>
                    <p>Your review is anonymous and valuable in enhancing our platform.</p>
                </div>

                <div class="ym-review-action-area">
                    <?php if ($is_view_mode): ?>
                        <a href="<?= base_url('review/form/' . $order_id . '?mode=edit') ?>" class="ym-btn-action ym-btn-edit">Edit Review</a>
                        <a href="<?= base_url('shops/shop_details/' . $merchant_id) ?>" class="ym-btn-action ym-btn-secondary" style="margin-top: 10px;">Visit eShop</a>
                    <?php else: ?>
                        <button type="submit" form="ym-review-form" class="ym-btn-action ym-btn-submit">
                            <?= $is_edit_mode ? 'Update Review' : 'Submit' ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="ym-review-copyright">
                    &copy; YellowMarkets.com - All Rights Reserved.
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Rating form -->
        <div class="ym-review-right-col">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="ym-alert ym-alert-danger">
                    <?= $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="ym-alert ym-alert-success">
                    <?= $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <form id="ym-review-form" method="post" action="<?= base_url('review/save/' . $order_id . ($is_edit_mode ? '?mode=edit' : '')) ?>" onsubmit="return validateReviewForm();">
                <!-- MERCHANT CUSTOMER SERVICE SECTION -->
                <div class="ym-review-section ym-merchant-section">
                    <div class="ym-field-pill ym-trade-name-pill">
                        <?= htmlspecialchars($merchant_trade_name) ?>
                    </div>

                    <div class="ym-rating-row">
                        <div class="ym-field-pill ym-service-label-pill">
                            Customer Service
                        </div>

                        <div class="ym-rating-controls-wrapper">
                            <label class="ym-rating-prompt">How was the service provided?</label>
                            
                            <input type="hidden" name="merchant_rating" id="merchant_rating" value="<?= $merchant_rating_val ?>" required>
                            
                            <div class="ym-rating-btn-group">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" 
                                            class="ym-rating-num-btn <?= ($merchant_rating_val == $i) ? 'active' : '' ?>" 
                                            data-val="<?= $i ?>" 
                                            data-target="merchant_rating"
                                            <?= $is_view_mode ? 'disabled' : '' ?>>
                                        <?= $i ?>
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <div class="ym-scale-labels-row">
                                <span class="ym-scale-text scale-poor">Poor</span>
                                <span class="ym-scale-text scale-good">Good</span>
                                <span class="ym-scale-text scale-excellent">Excellent</span>
                            </div>
                        </div>
                    </div>

                    <div class="ym-comment-wrapper">
                        <label class="ym-comment-label">Do you want to add a comment?</label>
                        <textarea name="merchant_comments" 
                                  class="ym-comment-textarea" 
                                  placeholder="Write your comments about the customer service here..."
                                  <?= $is_view_mode ? 'readonly' : '' ?>><?= htmlspecialchars($merchant_comment_val) ?></textarea>
                    </div>
                </div>

                <!-- INDIVIDUAL PRODUCT SECTIONS -->
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $idx => $item): ?>
                        <?php
                        $p_id = $item->product_id;
                        $p_name = !empty($item->product_name) ? $item->product_name : (!empty($item->name) ? $item->name : 'Product #' . ($idx + 1));
                        $p_rating_val = isset($product_review_map[$p_id]->rating) ? (int)$product_review_map[$p_id]->rating : 0;
                        $p_comment_val = isset($product_review_map[$p_id]->comments) ? $product_review_map[$p_id]->comments : '';
                        ?>
                        <div class="ym-review-section ym-product-section">
                            <input type="hidden" name="product_names[<?= $p_id ?>]" value="<?= htmlspecialchars($p_name) ?>">

                            <div class="ym-field-pill ym-product-name-pill">
                                <?= htmlspecialchars($p_name) ?>
                            </div>

                            <div class="ym-rating-row">
                                <div class="ym-rating-controls-wrapper ym-product-rating-wrapper">
                                    <label class="ym-rating-prompt">How was the product purchased?</label>
                                    
                                    <input type="hidden" name="ratings[<?= $p_id ?>]" id="rating_product_<?= $p_id ?>" value="<?= $p_rating_val ?>" class="ym-required-product-rating" data-product="<?= htmlspecialchars($p_name) ?>" required>
                                    
                                    <div class="ym-rating-btn-group">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <button type="button" 
                                                    class="ym-rating-num-btn <?= ($p_rating_val == $i) ? 'active' : '' ?>" 
                                                    data-val="<?= $i ?>" 
                                                    data-target="rating_product_<?= $p_id ?>"
                                                    <?= $is_view_mode ? 'disabled' : '' ?>>
                                                <?= $i ?>
                                            </button>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="ym-scale-labels-row">
                                        <span class="ym-scale-text scale-poor">Poor</span>
                                        <span class="ym-scale-text scale-good">Good</span>
                                        <span class="ym-scale-text scale-excellent">Excellent</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ym-comment-wrapper">
                                <label class="ym-comment-label">Do you want to add a comment?</label>
                                <textarea name="comments[<?= $p_id ?>]" 
                                          class="ym-comment-textarea" 
                                          placeholder="Write your comments about this product here..."
                                          <?= $is_view_mode ? 'readonly' : '' ?>><?= htmlspecialchars($p_comment_val) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>

<style>
/* Page Layout */
.ym-review-page-container {
    background: #ffffff;
    min-height: 80vh;
    padding: 30px 15px 60px 15px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #212529;
}

.ym-review-card {
    max-width: 1040px;
    margin: 0 auto;
    background: #ffffff;
    display: flex;
    flex-direction: row;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

/* Left Column */
.ym-review-left-col {
    width: 38%;
    min-width: 320px;
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    padding: 40px 30px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

.ym-review-brand-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    height: 100%;
}

.ym-review-avatar-placeholder {
    margin-bottom: 20px;
}

.ym-avatar-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: #d1d5db;
    margin: 0 auto;
}

.ym-merchant-avatar-img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: contain;
    border: 1px solid #e5e7eb;
}

.ym-review-title {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 10px 0;
    line-height: 1.25;
}

.ym-review-subtitle {
    font-size: 17px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 24px 0;
}

.ym-review-copy {
    color: #374151;
    font-size: 13.5px;
    line-height: 1.55;
    margin-bottom: 28px;
}

.ym-review-copy p {
    margin-bottom: 16px;
}

.ym-review-action-area {
    width: 100%;
    margin-bottom: auto;
}

.ym-btn-action {
    display: block;
    width: 100%;
    max-width: 270px;
    margin: 0 auto;
    padding: 12px 20px;
    font-size: 15px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.ym-btn-submit {
    background-color: #007bff;
    color: #ffffff;
}

.ym-btn-submit:hover {
    background-color: #0069d9;
    color: #ffffff;
}

.ym-btn-edit {
    background-color: #007bff;
    color: #ffffff;
}

.ym-btn-edit:hover {
    background-color: #0069d9;
    color: #ffffff;
}

.ym-btn-secondary {
    background-color: #6c757d;
    color: #ffffff;
}

.ym-btn-secondary:hover {
    background-color: #5a6268;
    color: #ffffff;
}

.ym-review-copyright {
    margin-top: 35px;
    font-size: 11px;
    color: #9ca3af;
}

/* Right Column */
.ym-review-right-col {
    width: 62%;
    padding: 40px 35px;
    box-sizing: border-box;
    background: #ffffff;
}

.ym-alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}
.ym-alert-danger {
    background-color: #fde8e8;
    color: #9b1c1c;
    border: 1px solid #f8b4b4;
}
.ym-alert-success {
    background-color: #def7ec;
    color: #03543f;
    border: 1px solid #bcf0da;
}

.ym-review-section {
    padding-bottom: 28px;
    margin-bottom: 28px;
    border-bottom: 1px solid #e5e7eb;
}

.ym-review-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

/* Field Pills */
.ym-field-pill {
    display: inline-block;
    padding: 6px 14px;
    background-color: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13.5px;
    color: #1f2937;
    margin-bottom: 14px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
}

.ym-trade-name-pill {
    font-weight: 600;
    min-width: 170px;
}

.ym-service-label-pill {
    font-weight: 500;
    min-width: 170px;
    margin-bottom: 0;
    align-self: flex-start;
}

.ym-product-name-pill {
    font-weight: 600;
    min-width: 220px;
}

/* Rating Controls */
.ym-rating-row {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 18px;
}

.ym-rating-controls-wrapper {
    display: flex;
    flex-direction: column;
}

.ym-product-rating-wrapper {
    width: 100%;
}

.ym-rating-prompt {
    font-size: 13.5px;
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 8px;
}

.ym-rating-btn-group {
    display: flex;
    gap: 8px;
}

.ym-rating-num-btn {
    width: 34px;
    height: 34px;
    background-color: #ffc107;
    border: 1px solid #e0a800;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

.ym-rating-num-btn:hover:not(:disabled) {
    background-color: #e0a800;
    transform: translateY(-1px);
}

.ym-rating-num-btn.active {
    background-color: #d39e00;
    border: 2px solid #856404;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.4);
    color: #000;
}

.ym-rating-num-btn:disabled {
    cursor: default;
    opacity: 0.9;
}

.ym-rating-num-btn:disabled:not(.active) {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    color: #9ca3af;
}

.ym-scale-labels-row {
    display: flex;
    justify-content: space-between;
    width: 202px; /* spans 5 buttons (34*5 + 8*4) */
    margin-top: 4px;
}

.ym-scale-text {
    font-size: 11px;
    color: #6b7280;
}

/* Comment Area */
.ym-comment-wrapper {
    margin-top: 14px;
}

.ym-comment-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.ym-comment-textarea {
    width: 100%;
    height: 72px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13.5px;
    font-family: inherit;
    color: #1f2937;
    background-color: #ffffff;
    box-sizing: border-box;
    resize: vertical;
    outline: none;
    transition: border-color 0.2s;
}

.ym-comment-textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15);
}

.ym-comment-textarea[readonly] {
    background-color: #f9fafb;
    color: #4b5563;
    cursor: default;
}

/* Responsive adjustments */
@media (max-width: 820px) {
    .ym-review-card {
        flex-direction: column;
    }
    .ym-review-left-col {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 30px 20px;
    }
    .ym-review-right-col {
        width: 100%;
        padding: 30px 20px;
    }
    .ym-rating-row {
        flex-direction: column;
        gap: 12px;
    }
    .ym-scale-labels-row {
        width: 100%;
        max-width: 202px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Rating button clicks
    var ratingButtons = document.querySelectorAll('.ym-rating-num-btn');
    ratingButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.disabled) return;
            var targetInputId = this.getAttribute('data-target');
            var ratingVal = this.getAttribute('data-val');
            var targetInput = document.getElementById(targetInputId);
            if (targetInput) {
                targetInput.value = ratingVal;
            }

            // Update active state among siblings
            var parentGroup = this.closest('.ym-rating-btn-group');
            if (parentGroup) {
                parentGroup.querySelectorAll('.ym-rating-num-btn').forEach(function (sibling) {
                    sibling.classList.remove('active');
                });
            }
            this.classList.add('active');
        });
    });
});

function validateReviewForm() {
    var merchantRating = document.getElementById('merchant_rating');
    if (!merchantRating || !merchantRating.value || parseInt(merchantRating.value) < 1) {
        alert('Please provide a Customer Service rating for the merchant.');
        var merchantSec = document.querySelector('.ym-merchant-section');
        if (merchantSec) merchantSec.scrollIntoView({ behavior: 'smooth' });
        return false;
    }

    var productRatings = document.querySelectorAll('.ym-required-product-rating');
    for (var i = 0; i < productRatings.length; i++) {
        var pInput = productRatings[i];
        if (!pInput.value || parseInt(pInput.value) < 1) {
            var prodName = pInput.getAttribute('data-product') || 'product';
            alert('Please provide a rating for: ' + prodName);
            pInput.closest('.ym-product-section').scrollIntoView({ behavior: 'smooth' });
            return false;
        }
    }

    return true;
}
</script>
