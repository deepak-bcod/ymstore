
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// =====================================================
// YELLOW MARKETS - SUBSCRIPTION / RENEWAL INVOICE
// =====================================================

$e = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$publisher = (isset($order['publisher']) && is_array($order['publisher']))
    ? $order['publisher'] : [];

$plan = (isset($order['plan']) && is_array($order['plan']))
    ? $order['plan'] : [];

$getValue = function ($source, $keys, $default = '') {
    if (!is_array($source)) {
        return $default;
    }

    foreach ($keys as $key) {
        if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
            return $source[$key];
        }
    }

    return $default;
};

$formatDate = function ($date, $fallback = 'N/A') {
    if (empty($date)) {
        return $fallback;
    }

    $timestamp = strtotime($date);

    return ($timestamp !== false)
        ? date('d F Y', $timestamp)
        : $fallback;
};

// =====================================================
// BASIC INVOICE INFORMATION
// =====================================================

$invoice_id = $order['id'] ?? '';

$invoice_number = $order['invoice_number']
    ?? 'SUB-INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);

$order_reference = $order['order_reference']
    ?? 'SUB-ORDER-' . $invoice_id;

$currency = $order['currency'] ?? 'MUR';

$status = strtolower($order['status'] ?? 'unpaid');

$created_timestamp = !empty($order['created_at'])
    ? strtotime($order['created_at'])
    : time();

$created_date = date('d F Y', $created_timestamp);

$expiry_date = $formatDate(
    $order['expiry_date'] ?? '',
    date('d F Y', strtotime('+1 year', $created_timestamp))
);

// =====================================================
// SUBSCRIPTION ID
// IMPORTANT:
// subscription_id / new_subscription_id must contain
// the newly generated subscription record ID.
// =====================================================

$new_subscription_id = $getValue($order, [
    'new_subscription_id',
    'subscription_id',
    'subscription_record_id'
], $invoice_id);

$old_subscription_id = $getValue($order, [
    'renewal_of',
    'previous_subscription_id',
    'old_subscription_id',
    'renewed_from_subscription_id'
], '');

// =====================================================
// RENEWAL DETECTION
// =====================================================

$is_renewal = (
    !empty($order['is_renewal']) &&
    (string)$order['is_renewal'] === '1'
);

if ($old_subscription_id !== '') {
    $is_renewal = true;
}

if (
    !empty($order['purchase_type']) &&
    strtolower($order['purchase_type']) === 'renewal'
) {
    $is_renewal = true;
}

// =====================================================
// SUBSCRIPTION NAME
// =====================================================

$plan_name = $plan['name'] ?? 'Subscription Plan';

$subscription_reference = $getValue($order, [
    'subscription_reference',
    'subscription_name'
], $plan_name);

// =====================================================
// SUBSCRIPTION TERMS
// =====================================================

$term = $order['term'] ?? 'Yearly';

$billing_period = $order['billing_period'] ?? $term;

// Previous term
$previous_term_reference = $getValue($order, [
    'previous_term_reference',
    'old_term_reference',
    'previous_term_id'
], 'N/A');

$previous_term_start = $getValue($order, [
    'previous_term_start',
    'old_term_start',
    'previous_start_date'
]);

$previous_term_expiry = $getValue($order, [
    'previous_term_expiry',
    'old_term_expiry',
    'previous_expiry_date'
]);

// New term
$new_term_reference = $getValue($order, [
    'new_term_reference',
    'current_term_reference',
    'term_reference',
    'term_id'
], 'N/A');

$new_term_start = $getValue($order, [
    'new_term_start',
    'current_term_start',
    'term_start_date'
]);

$new_term_expiry = $getValue($order, [
    'new_term_expiry',
    'current_term_expiry',
    'term_expiry_date'
]);

// Service dates
$service_start_date = $formatDate(
    $new_term_start,
    $created_date
);

$service_expiry_date = $formatDate(
    $new_term_expiry,
    $expiry_date
);

// =====================================================
// MERCHANT DETAILS
// =====================================================

$merchant_name = $getValue($publisher, [
    'publication_name',
    'legal_name',
    'merchant_legal_name',
    'business_name',
    'name'
], 'N/A');

$merchant_address = $getValue($publisher, [
    'billing_address',
    'business_address',
    'company_address',
    'registered_address',
    'merchant_address',
    'address',
    'address_line_1',
    'address1',
    'street_address'
], 'N/A');

$merchant_brn = $getValue($publisher, [
    'brn_number',
    'brn_no',
    'business_registration_number',
    'business_reg_no',
    'company_brn',
    'merchant_brn',
    'brn',
    'BRN'
], 'N/A');

$merchant_email = $getValue($publisher, [
    'billing_email',
    'business_email',
    'merchant_email',
    'email'
], 'N/A');

$merchant_phone = $getValue($publisher, [
    'billing_phone',
    'phone_no',
    'mobile_no',
    'contact_number',
    'phone'
], 'N/A');

// =====================================================
// PRICE AND VAT
// =====================================================

$amount = isset($order['amount'])
    ? (float)$order['amount']
    : 0;

$yearly_price = (float)(
    $order['invoice_amount']
    ?? $plan['yearly_price']
    ?? $plan['yearlyPrice']
    ?? $plan['price']
    ?? $amount
);

$vat_rate = (float)(
    $order['vat_rate']
    ?? $plan['vat_rate']
    ?? $plan['vat_percentage']
    ?? $plan['ym_vat']
    ?? 0
);

$subtotal = $yearly_price;

$vat_amount = ($subtotal * $vat_rate) / 100;

$invoice_total = isset($order['final_price'])
    ? (float)$order['final_price']
    : (isset($plan['final_price'])
        ? (float)$plan['final_price']
        : $subtotal + $vat_amount);

// =====================================================
// PAYMENT INFORMATION
// =====================================================

$payment_allocated = ($status === 'paid')
    ? $invoice_total
    : 0;

$amount_due = max(0, $invoice_total - $payment_allocated);

$payment_method = $order['payment_method']
    ?? 'My.T Money (mobile payment)';

$transaction_reference = $order['transaction_reference']
    ?? 'N/A';

// =====================================================
// LOGO
// =====================================================

$logo_image_src = '';

$candidate_logos = [
    FCPATH . 'uploads/yellow-markets-logo-transparent.png',
    FCPATH . 'uploads/yellow-markets-logo.png',
    FCPATH . 'public/images/yellow-markets-logo-transparent.png',
    dirname(FCPATH) . '/merchant/public/images/yellow-markets-logo-transparent.png',
    dirname(FCPATH) . '/merchant/public/images/yellow-markets-logo-yellow-white.png'
];

foreach ($candidate_logos as $logo_path) {

    if (file_exists($logo_path) && filesize($logo_path) > 0) {

        $ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));

        $mime = in_array($ext, ['jpg', 'jpeg'])
            ? 'image/jpeg'
            : 'image/png';

        $logo_image_src = 'data:' . $mime . ';base64,' .
            base64_encode(file_get_contents($logo_path));

        break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Invoice - <?php echo $e($invoice_number); ?></title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: #edf0f2;
    color: #303030;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.45;
}

.invoice-wrapper {
    width: 100%;
    max-width: 900px;
    margin: 20px auto;
    padding: 25px 30px;
    background: #ffffff;
    box-shadow: 0 0 5px rgba(0,0,0,0.12);
}

.header-table,
.details-table,
.service-table,
.bottom-table,
.totals-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table {
    table-layout: fixed;
}

.header-table td {
    vertical-align: middle;
}

.logo-box {
    display: inline-block;
    background: #23282d;
    padding: 11px 22px;
    border-radius: 4px;
    line-height: 0;
}

.logo {
    display: block;
    max-width: 165px;
    max-height: 42px;
    width: auto;
    height: auto;
    object-fit: contain;
}

.invoice-heading {
    text-align: right;
    font-size: 21px;
    font-weight: bold;
    color: #202938;
    overflow-wrap: anywhere;
}

.invoice-number-small {
    font-size: 10px;
    color: #666;
    text-align: right;
}

.yellow-line {
    width: 100%;
    height: 3px;
    background: #e5ad18;
    margin: 12px 0;
}

.subscription-title {
    font-size: 19px;
    font-weight: bold;
    color: #202938;
    margin: 18px 0 12px;
}

.company-details {
    font-size: 10px;
    line-height: 1.5;
}

.company-name {
    font-size: 11px;
    font-weight: bold;
}

.divider {
    border-top: 1px solid #d9dfe5;
    margin: 12px 0;
}

.details-table td {
    width: 50%;
    vertical-align: top;
    padding: 5px 12px 8px 0;
}

.section-heading,
.service-heading {
    font-size: 12px;
    font-weight: bold;
    color: #202938;
    margin-bottom: 6px;
}

.detail-line {
    font-size: 10px;
    line-height: 1.5;
    margin-bottom: 3px;
    overflow-wrap: anywhere;
}

.detail-label {
    font-weight: bold;
}

.service-heading {
    margin: 12px 0 7px;
}

.service-table {
    table-layout: fixed;
    font-size: 10px;
}

.service-table th {
    background: #eeeeef;
    font-weight: bold;
    text-align: left;
    padding: 9px 7px;
    border-bottom: 1px solid #dce0e4;
}

.service-table td {
    padding: 10px 7px;
    border-bottom: 1px solid #dce0e4;
    vertical-align: top;
    overflow-wrap: anywhere;
}

.text-center {
    text-align: center !important;
}

.text-right {
    text-align: right !important;
}

.bottom-table {
    margin-top: 15px;
}

.bottom-table > tbody > tr > td {
    width: 50%;
    vertical-align: top;
    padding: 0 12px 5px 0;
}

.payment-details {
    font-size: 10px;
    line-height: 1.6;
}

.status-paid {
    color: #218838;
    font-weight: bold;
}

.status-unpaid {
    color: #c0392b;
    font-weight: bold;
}

.totals-table {
    font-size: 10px;
}

.totals-table td {
    padding: 4px 0;
}

.totals-table .invoice-total td {
    border-top: 2px solid #202938;
    border-bottom: 1px solid #d9dfe5;
    padding: 8px 0;
    font-weight: bold;
    font-size: 12px;
}

.totals-table .due-total td {
    font-weight: bold;
    padding-top: 7px;
}

.service-notes {
    margin-top: 12px;
    font-size: 10px;
    line-height: 1.5;
}

.invoice-footer {
    border-top: 1px solid #d9dfe5;
    margin-top: 15px;
    padding-top: 10px;
    font-size: 9px;
    color: #555;
    line-height: 1.5;
}

@media screen and (max-width: 650px) {

    .invoice-wrapper {
        width: 100%;
        margin: 0;
        padding: 15px;
    }

    .service-table {
        font-size: 9px;
    }

    .service-table th,
    .service-table td {
        padding: 5px 3px;
    }
}

@page {
    size: A4 portrait;
    margin: 8mm;
}

@media print {

    html, body {
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        font-size: 9px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .invoice-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 5mm !important;
        box-shadow: none !important;
    }

    .header-table,
    .details-table,
    .service-table,
    .bottom-table {
        width: 100% !important;
        table-layout: fixed !important;
    }

    .logo-box {
        background: #23282d !important;
        padding: 9px 18px !important;
        print-color-adjust: exact !important;
    }

    .logo {
        max-width: 145px !important;
        max-height: 38px !important;
    }

    .invoice-heading {
        font-size: 19px !important;
    }

    .subscription-title {
        font-size: 17px !important;
        margin: 12px 0 8px !important;
    }

    .company-details,
    .detail-line,
    .payment-details {
        font-size: 9px !important;
    }

    .yellow-line {
        margin: 8px 0 !important;
    }

    .divider {
        margin: 7px 0 !important;
    }

    .details-table td {
        padding: 3px 8px 5px 0 !important;
    }

    .service-table {
        font-size: 9px !important;
    }

    .service-table th,
    .service-table td {
        padding: 6px 5px !important;
        overflow-wrap: anywhere !important;
    }

    .bottom-table {
        margin-top: 9px !important;
    }

    .totals-table {
        font-size: 9px !important;
    }

    .totals-table .invoice-total td {
        font-size: 11px !important;
        padding: 5px 0 !important;
    }

    .invoice-footer {
        margin-top: 9px !important;
        padding-top: 6px !important;
        font-size: 8px !important;
    }

    tr {
        page-break-inside: avoid !important;
    }
}

</style>

</head>

<body>

<div class="invoice-wrapper">

    <!-- HEADER -->

    <table class="header-table">

        <tr>

            <td width="55%">

                <div class="logo-box">

                    <?php if (!empty($logo_image_src)) { ?>

                        <img
                            src="<?php echo $logo_image_src; ?>"
                            class="logo"
                            alt="Yellow Markets Logo"
                        >

                    <?php } else { ?>

                        <strong style="font-size:18px;color:#fff;">
                            Yellow Markets
                        </strong>

                    <?php } ?>

                </div>

            </td>

            <td width="45%" style="padding-left:8px;">

                <div class="invoice-heading">
                    Subscription Invoice
                </div>

                <div class="invoice-number-small">
                    Invoice #<?php echo $e($invoice_number); ?>
                </div>

            </td>

        </tr>

    </table>

    <div class="yellow-line"></div>

    <div class="subscription-title">
        Subscription Details
    </div>

    <!-- COMPANY DETAILS -->

    <div class="company-details">

        <div class="company-name">
            MYP Online Marketing Ltd
        </div>

        Yellow Markets - A MYP Network Service<br>

        Address: 14 Avenue Sodnac, Quatre Bornes, Mauritius |
        Postal code: 72256<br>

        Email: acct@themyp.com |
        Phone: +230 5252 5772<br>

        BRN: C14124113

    </div>

    <div class="divider"></div>

    <!-- MERCHANT AND INVOICE DETAILS -->

    <table class="details-table">

        <tbody>
        <tr>

            <td>

                <div class="section-heading">
                    Bill to Merchant
                </div>

                <div class="detail-line">
                    <strong>Merchant legal name:</strong>
                    <?php echo $e($merchant_name); ?>
                </div>

                <div class="detail-line">
                    <strong>Billing address:</strong>
                    <?php echo $e($merchant_address); ?>
                </div>

                <div class="detail-line">
                    <strong>BRN:</strong>
                    <?php echo $e($merchant_brn); ?>
                </div>

                <div class="detail-line">
                    <strong>Billing email:</strong>
                    <?php echo $e($merchant_email); ?>
                </div>

            </td>

            <td>

                <div class="section-heading">
                    Invoice details
                </div>

                <div class="detail-line">
                    <strong>Invoice number:</strong>
                    <?php echo $e($invoice_number); ?>
                </div>

                <div class="detail-line">
                    <strong>Invoice date:</strong>
                    <?php echo $e($created_date); ?>
                </div>

                <div class="detail-line">
                    <strong>Currency:</strong>
                    <?php echo $e($currency); ?>
                </div>

                <div class="detail-line">
                    <strong>Order reference:</strong>
                    <?php echo $e($order_reference); ?>
                </div>

                <?php if ($is_renewal) { ?>

                    <div class="detail-line">
                        <strong>Renewal of:</strong>
                        <?php echo $e($old_subscription_id); ?>
                    </div>

                    <div class="detail-line">
                        <strong>New Subscription ID:</strong>
                        <?php echo $e($new_subscription_id); ?>
                    </div>

                    <div class="detail-line">
                        <strong>Previous term:</strong>
                        <?php echo $e($previous_term_reference); ?>

                        <?php if ($previous_term_start && $previous_term_expiry) { ?>
                            <br>
                            <?php echo $e($formatDate($previous_term_start)); ?>
                            -
                            <?php echo $e($formatDate($previous_term_expiry)); ?>
                        <?php } ?>
                    </div>

                    <div class="detail-line">
                        <strong>New term:</strong>
                        <?php echo $e($new_term_reference); ?>
                        - <?php echo $e($term); ?>
                    </div>

                <?php } else { ?>

                    <div class="detail-line">
                        <strong>Subscription:</strong>
                        <?php echo $e($subscription_reference); ?>
                    </div>

                    <div class="detail-line">
                        <strong>Subscription ID:</strong>
                        <?php echo $e($new_subscription_id); ?>
                    </div>

                    <div class="detail-line">
                        <strong>Term:</strong>
                        <?php echo $e($term); ?>
                    </div>

                <?php } ?>

            </td>

        </tr>
        </tbody>

    </table>

    <div class="divider"></div>

    <!-- SERVICE INFORMATION -->

    <div class="service-heading">

        <?php echo $is_renewal
            ? 'Renewal Information'
            : 'Subscription Information'; ?>

    </div>

    <table class="service-table">

        <thead>
        <tr>

            <th width="25%">Service Name</th>

            <th width="32%">Description</th>

            <th width="22%">Service Dates</th>

            <th width="5%" class="text-center">Qty</th>

            <th width="8%" class="text-right">Unit Price</th>

            <th width="8%" class="text-right">Amount</th>

        </tr>
        </thead>

        <tbody>
        <tr>

            <td>
                <?php echo $e($plan_name); ?>
            </td>

            <td>

                <?php if ($is_renewal) { ?>

                    Yearly renewal of
                    <?php echo $e($old_subscription_id); ?><br>

                    New Subscription ID:
                    <?php echo $e($new_subscription_id); ?><br>

                    Previous term:
                    <?php echo $e($previous_term_reference); ?>

                <?php } else { ?>

                    Initial paid subscription plan<br>

                    Billing period:
                    <?php echo $e($billing_period); ?>

                <?php } ?>

            </td>

            <td>
                Start: <?php echo $e($service_start_date); ?><br>
                Expiry: <?php echo $e($service_expiry_date); ?>
            </td>

            <td class="text-center">
                1
            </td>

            <td class="text-right">
                <?php echo number_format($subtotal, 2); ?>
            </td>

            <td class="text-right">
                <?php echo number_format($subtotal, 2); ?>
            </td>

        </tr>
        </tbody>

    </table>

    <!-- PAYMENT AND TOTALS -->

    <table class="bottom-table">

        <tbody>
        <tr>

            <td>

                <div class="section-heading">
                    Payment Information
                </div>

                <div class="payment-details">

                    <strong>Accepted method:</strong>
                    <?php echo $e($payment_method); ?><br>

                    <strong>Status at issue:</strong>

                    <span class="<?php echo ($status === 'paid')
                        ? 'status-paid'
                        : 'status-unpaid'; ?>">

                        <?php echo $e(ucfirst($status)); ?>

                    </span>

                    <br>

                    <?php if ($transaction_reference !== 'N/A') { ?>

                        <strong>Transaction reference:</strong>
                        <?php echo $e($transaction_reference); ?><br>

                    <?php } ?>

                    An invoice does not confirm receipt of payment.
                    A separate payment confirmation or receipt follows
                    verified payment.

                </div>

                <div class="service-notes">

                    <strong>Service notes</strong><br>

                    <?php if ($is_renewal) { ?>

                        Subscription renewal for the new term shown
                        in the invoice details.

                    <?php } else { ?>

                        Initial subscription term as shown in
                        Service Dates.

                    <?php } ?>

                </div>

            </td>

            <td>

                <div class="section-heading">
                    Invoice totals (<?php echo $e($currency); ?>)
                </div>

                <table class="totals-table">

                    <tr>
                        <td>Subtotal</td>
                        <td class="text-right">
                            <?php echo number_format($subtotal, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            VAT (<?php echo number_format($vat_rate, 2); ?>%)
                        </td>
                        <td class="text-right">
                            <?php echo number_format($vat_amount, 2); ?>
                        </td>
                    </tr>

                    <tr class="invoice-total">
                        <td>Invoice total</td>
                        <td class="text-right">
                            <?php echo number_format($invoice_total, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>Verified payment allocated</td>
                        <td class="text-right">
                            <?php echo number_format($payment_allocated, 2); ?>
                        </td>
                    </tr>

                    <tr class="due-total">
                        <td>Amount due at issue</td>
                        <td class="text-right">
                            <?php echo number_format($amount_due, 2); ?>
                        </td>
                    </tr>

                </table>

            </td>

        </tr>
        </tbody>

    </table>

    <!-- FOOTER -->

    <div class="invoice-footer">

        <strong>MYP Online Marketing Ltd</strong><br>

        Yellow Markets - A MYP Network Service<br>

        Thank you for using Yellow Markets.

    </div>

</div>

</body>
</html>
