<?php $this->load->view('common/header'); ?>

<?php
$order_id = isset($order_id) ? $order_id : $this->input->get('order_id');
$merchant_id = isset($merchant_id) ? $merchant_id : 0;
$shop_url = !empty($merchant_id) ? base_url('shops/shop_details/' . $merchant_id) : base_url('shops');
$view_review_url = !empty($order_id) ? base_url('review/form/' . $order_id . '?mode=view') : base_url();
$edit_review_url = !empty($order_id) ? base_url('review/form/' . $order_id . '?mode=edit') : base_url();
?>

<div class="ym-thankyou-page-container">
    <div class="ym-thankyou-card">
        <!-- LEFT COLUMN: Dark Hero Banner with Laptop background -->
        <div class="ym-thankyou-left-col">
            <div class="ym-thankyou-left-content">
                <h1 class="ym-thankyou-brand-title">Yellow Markets Reviews</h1>
                <h2 class="ym-thankyou-brand-subtitle">Your Opinion Counts</h2>

                <div class="ym-thankyou-brand-copy">
                    <p>Thank you for helping other shoppers make a decision. Your Review also help merchant and it gives YellowMarkets.com an online voice for merchant and us.</p>
                    <p>Your review will be anonymous to the Public.</p>
                </div>

                <div class="ym-thankyou-eshop-btn-wrap">
                    <a href="<?= $shop_url ?>" class="ym-btn-visit-eshop">Visit eShop</a>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Thank You & Published confirmation -->
        <div class="ym-thankyou-right-col">
            <div class="ym-thankyou-right-content">
                <div class="ym-thankyou-avatar-wrap">
                    <?php if (!empty($merchant->shop_image) && file_exists(FCPATH . 'merchant/public/images/shop_images/' . $merchant->shop_image)): ?>
                        <img src="<?= base_url('merchant/public/images/shop_images/' . $merchant->shop_image) ?>" alt="Merchant Logo" class="ym-thankyou-avatar-img">
                    <?php else: ?>
                        <div class="ym-thankyou-avatar-circle"></div>
                    <?php endif; ?>
                </div>

                <h1 class="ym-thankyou-heading">Thank You For The Review</h1>

                <div class="ym-thankyou-published-text">
                    Great Your Review Was Published
                </div>

                <div class="ym-thankyou-buttons-group">
                    <a href="<?= $view_review_url ?>" class="ym-btn-view-review">View Review</a>
                    <a href="<?= $edit_review_url ?>" class="ym-btn-edit-review">Edit Review</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>

<style>
/* Page Container */
.ym-thankyou-page-container {
    background: #ffffff;
    min-height: 80vh;
    padding: 30px 15px 60px 15px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #212529;
}

.ym-thankyou-card {
    max-width: 1040px;
    margin: 0 auto;
    background: #ffffff;
    display: flex;
    flex-direction: row;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    min-height: 520px;
}

/* Left Column */
.ym-thankyou-left-col {
    width: 50%;
    background: linear-gradient(rgba(17, 24, 39, 0.88), rgba(17, 24, 39, 0.94)),
                url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=900&q=80') center/cover no-repeat #111827;
    color: #ffffff;
    padding: 50px 40px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.ym-thankyou-left-content {
    max-width: 440px;
    width: 100%;
}

.ym-thankyou-brand-title {
    font-size: 26px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 12px 0;
    line-height: 1.25;
}

.ym-thankyou-brand-subtitle {
    font-size: 19px;
    font-weight: 600;
    color: #ffffff;
    margin: 0 0 24px 0;
}

.ym-thankyou-brand-copy {
    color: #e5e7eb;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 30px;
}

.ym-thankyou-brand-copy p {
    margin-bottom: 16px;
}

.ym-btn-visit-eshop {
    display: inline-block;
    background-color: #007bff;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    padding: 11px 32px;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

.ym-btn-visit-eshop:hover {
    background-color: #0069d9;
    color: #ffffff;
}

/* Right Column */
.ym-thankyou-right-col {
    width: 50%;
    background: #ffffff;
    padding: 50px 40px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.ym-thankyou-right-content {
    max-width: 380px;
    width: 100%;
}

.ym-thankyou-avatar-wrap {
    margin-bottom: 25px;
}

.ym-thankyou-avatar-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: #d1d5db;
    margin: 0 auto;
}

.ym-thankyou-avatar-img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: contain;
    border: 1px solid #e5e7eb;
}

.ym-thankyou-heading {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 24px 0;
    line-height: 1.3;
}

.ym-thankyou-published-text {
    font-size: 19px;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 36px;
}

.ym-thankyou-buttons-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ym-btn-view-review {
    display: block;
    width: 100%;
    padding: 13px 20px;
    background-color: #007bff;
    color: #ffffff;
    font-size: 15px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    box-sizing: border-box;
    transition: all 0.2s ease-in-out;
}

.ym-btn-view-review:hover {
    background-color: #0069d9;
    color: #ffffff;
}

.ym-btn-edit-review {
    display: block;
    width: 100%;
    padding: 13px 20px;
    background-color: #f3f4f6;
    color: #1f2937;
    font-size: 15px;
    font-weight: 600;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    box-sizing: border-box;
    transition: all 0.2s ease-in-out;
}

.ym-btn-edit-review:hover {
    background-color: #e5e7eb;
    color: #111827;
}

/* Responsive */
@media (max-width: 820px) {
    .ym-thankyou-card {
        flex-direction: column;
    }
    .ym-thankyou-left-col,
    .ym-thankyou-right-col {
        width: 100%;
        padding: 40px 20px;
    }
}
</style>
