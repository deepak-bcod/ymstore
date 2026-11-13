
<?php
// =====================================================
// YELLOW MARKETS - SUBSCRIPTION INVOICE
// =====================================================

$e = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$publisher = isset($order['publisher']) && is_array($order['publisher'])
    ? $order['publisher'] : [];

$plan = isset($order['plan']) && is_array($order['plan'])
    ? $order['plan'] : [];

// Basic invoice information
$invoice_id = $order['id'] ?? '';
$merchant_name = $publisher['publication_name']
    ?? $publisher['name'] ?? 'N/A';

$plan_name = $plan['name'] ?? 'Subscription Plan';

// Get actual subscription price from plan
$billing_period = $order['billing_period'] ?? 'Yearly';

if (strtolower($billing_period) === 'yearly') {
    $amount = (float)(
        $plan['yearly_price']
        ?? $plan['yearly_amount']
        ?? $plan['price']
        ?? $order['amount']
        ?? 0
    );
} else {
    $amount = (float)(
        $plan['price']
        ?? $order['amount']
        ?? 0
    );
}

$status = strtolower($order['status'] ?? 'unpaid');

$currency = $order['currency'] ?? 'MUR';

// Dates
$created_timestamp = !empty($order['created_at'])
    ? strtotime($order['created_at']) : time();

$created_date = date('d F Y', $created_timestamp);

$expiry_date = !empty($order['expiry_date'])
    ? date('d F Y', strtotime($order['expiry_date']))
    : date('d F Y', strtotime('+1 year', $created_timestamp));

// Invoice references
$invoice_number = $order['invoice_number']
    ?? 'SUB-INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);

$order_reference = $order['order_reference']
    ?? 'SUB-ORDER-' . $invoice_id;

// =====================================================
// SUBSCRIPTION NAME / REFERENCE
// =====================================================

$subscription_reference = '';

if (!empty($order['subscription_reference'])) {
    $subscription_reference = $order['subscription_reference'];

} elseif (!empty($order['subscription_name'])) {
    $subscription_reference = $order['subscription_name'];

} elseif (!empty($plan['name'])) {
    $subscription_reference = $plan['name'];

} elseif (!empty($plan_name)) {
    $subscription_reference = $plan_name;

} else {
    $subscription_reference = 'Subscription Plan';
}

// =====================================================
// MERCHANT DETAILS
// =====================================================
// Read the first available, non-empty value from the publisher data.
// The exact database column names must be included in the controller's
// $order['publisher'] result.
$getPublisherValue = function (array $source, array $keys, $default = 'N/A') {
    foreach ($keys as $key) {
        if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
            return $source[$key];
        }
    }
    return $default;
};

$merchant_name = $getPublisherValue($publisher, [
    'publication_name', 'legal_name', 'merchant_legal_name', 'business_name', 'name'
], 'N/A');

$merchant_address = $getPublisherValue($publisher, [
    'billing_address', 'business_address', 'company_address',
    'registered_address', 'merchant_address', 'address',
    'address_line_1', 'address1', 'street_address'
], 'N/A');

$merchant_brn = $getPublisherValue($publisher, [
    'brn_number', 'brn_no', 'business_registration_number',
    'business_reg_no', 'company_brn', 'merchant_brn', 'brn', 'BRN'
], 'N/A');

$merchant_email = $getPublisherValue($publisher, [
    'billing_email', 'business_email', 'merchant_email', 'email'
], 'N/A');

$merchant_phone = $getPublisherValue($publisher, [
    'billing_phone', 'phone_no', 'mobile_no', 'contact_number', 'phone'
], 'N/A');

$merchant_reference = $getPublisherValue($publisher, [
    'merchant_reference', 'publisher_reference', 'reference_code'
], 'N/A');

// VAT
// Supply vat_rate from your actual invoice data.
// Default is 0% when no VAT rate is supplied.
$vat_rate = isset($order['vat_rate'])
    ? (float)$order['vat_rate'] : 0;

$subtotal = $amount;
$vat_amount = ($subtotal * $vat_rate) / 100;
$invoice_total = $subtotal + $vat_amount;

// Payment information
$payment_allocated = ($status === 'paid')
    ? $invoice_total : 0;

$amount_due = max(0, $invoice_total - $payment_allocated);

$payment_method = $order['payment_method']
    ?? 'My.T Money (mobile payment)';

$transaction_reference = $order['transaction_reference']
    ?? 'N/A';

// Subscription term
$term = $order['term'] ?? 'Yearly';

$billing_period = $order['billing_period'] ?? 'Yearly';

// =====================================================
// LOGO
// =====================================================

// Prefer a transparent logo file.
// Place yellow-markets-logo-transparent.png in one
// of the locations below.

$logo_image_src = '';

$candidate_logos = [
    FCPATH . 'uploads/yellow-markets-logo-transparent.png',
    FCPATH . 'uploads/yellow-markets-logo.png',
    FCPATH . 'public/images/yellow-markets-logo-transparent.png',
    dirname(FCPATH) . '/merchant/public/images/yellow-markets-logo-transparent.png',

    // Existing logo fallback.
    // This may have a black background baked into the PNG.
    dirname(FCPATH) . '/merchant/public/images/yellow-markets-logo-yellow-white.png',
];

foreach ($candidate_logos as $logo_path) {

    if (file_exists($logo_path) && filesize($logo_path) > 0) {

        $ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));

        $mime = ($ext === 'jpg' || $ext === 'jpeg')
            ? 'image/jpeg'
            : 'image/png';

        $logo_image_src = 'data:' . $mime . ';base64,' .
            base64_encode(file_get_contents($logo_path));

        break;
    }
}

if (
    empty($logo_image_src) &&
    !empty($logo_src) &&
    strpos($logo_src, 'data:image') === 0
) {
    $logo_image_src = $logo_src;
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
    box-sizing: border-box;
}

/* PRINT BUTTON */

.print-button {
    text-align: right;
    margin-bottom: 12px;
}

.print-button button {
    padding: 8px 18px;
    background: #e3ad13;
    color: #ffffff;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

/* HEADER */

.header-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.header-table td {
    vertical-align: middle;
    padding: 0;
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
    background: transparent;
    border: 0;
}

.invoice-heading {
    display: block;
    width: 100%;
    text-align: right;
    font-size: 21px;
    font-weight: bold;
    color: #202938;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
}

.invoice-number-small {
    font-size: 10px;
    color: #666666;
    text-align: right;
}

/* YELLOW LINE */

.yellow-line {
    width: 100%;
    height: 3px;
    background: #e5ad18;
    margin: 12px 0;
}

/* SUBSCRIPTION DETAILS TITLE */

.subscription-title {
    font-size: 19px;
    font-weight: bold;
    letter-spacing: 0.3px;
    color: #202938;
    margin: 18px 0 12px;
}

/* COMPANY INFORMATION */

.company-details {
    font-size: 10px;
    line-height: 1.5;
    color: #333333;
}

.company-name {
    font-size: 11px;
    font-weight: bold;
}

.divider {
    border-top: 1px solid #d9dfe5;
    margin: 12px 0;
}

/* MERCHANT AND INVOICE DETAILS */

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    width: 50%;
    vertical-align: top;
    padding: 5px 12px 8px 0;
}

.section-heading {
    font-size: 12px;
    font-weight: bold;
    color: #202938;
    margin-bottom: 6px;
}

.detail-line {
    font-size: 10px;
    line-height: 1.5;
    margin-bottom: 2px;
    overflow-wrap: anywhere;
}

.detail-label {
    font-weight: bold;
}

/* SERVICE TABLE */

.service-heading {
    font-size: 12px;
    font-weight: bold;
    margin: 12px 0 7px;
    color: #202938;
}

.service-table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    table-layout: fixed;
}

.service-table th {
    background: #eeeeef;
    color: #303030;
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

/* PAYMENT AND TOTALS */

.bottom-table {
    width: 100%;
    border-collapse: collapse;
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

/* TOTALS */

.totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.totals-table td {
    padding: 4px 0;
    vertical-align: middle;
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

/* SERVICE NOTES */

.service-notes {
    margin-top: 12px;
    font-size: 10px;
    line-height: 1.5;
}

/* FOOTER */

.invoice-footer {
    border-top: 1px solid #d9dfe5;
    margin-top: 15px;
    padding-top: 10px;
    font-size: 9px;
    line-height: 1.5;
    color: #555555;
}

/* MOBILE */

@media screen and (max-width: 650px) {

    .invoice-wrapper {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 15px;
    }

    .details-table td {
        padding-right: 5px;
    }

    .bottom-table > tbody > tr > td {
        padding-right: 5px;
    }

    .service-table {
        font-size: 9px;
    }

    .service-table th,
    .service-table td {
        padding: 5px 3px;
    }

}

/* A4 PRINT */

@page {
    size: A4 portrait;
    margin: 8mm;
}

@media print {

    html,
    body {
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #303030;
        font-size: 9px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .invoice-wrapper {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 5mm !important;
        box-sizing: border-box !important;
        overflow: visible !important;
        box-shadow: none !important;
    }

    .header-table,
    .details-table,
    .service-table,
    .bottom-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
    }

    .header-table td:first-child {
        width: 55% !important;
    }

    .header-table td:last-child {
        width: 45% !important;
        padding-left: 8px !important;
        padding-right: 2px !important;
    }

    .invoice-heading,
    .invoice-number-small {
        width: 100% !important;
        text-align: right !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
    }

    .service-table th,
    .service-table td {
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    .print-button {
        display: none !important;
    }

    .logo-box {
        background: #23282d !important;
        padding: 9px 18px !important;
        border-radius: 4px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .logo {
        max-width: 145px !important;
        max-height: 38px !important;
        background: transparent !important;
    }

    .invoice-heading {
        font-size: 19px !important;
    }

    .subscription-title {
        font-size: 17px !important;
        margin: 12px 0 8px !important;
    }

    .company-details {
        font-size: 9px !important;
        line-height: 1.35 !important;
    }

    .yellow-line {
        margin: 8px 0 !important;
        height: 3px !important;
    }

    .divider {
        margin: 7px 0 !important;
    }

    .section-heading,
    .service-heading {
        font-size: 10px !important;
        margin-bottom: 4px !important;
    }

    .details-table td {
        padding: 3px 8px 5px 0 !important;
    }

    .detail-line {
        font-size: 9px !important;
        line-height: 1.35 !important;
        margin-bottom: 1px !important;
    }

    .service-heading {
        margin-top: 8px !important;
    }

    .service-table {
        font-size: 9px !important;
    }

    .service-table th {
        padding: 6px 5px !important;
    }

    .service-table td {
        padding: 7px 5px !important;
    }

    .bottom-table {
        margin-top: 9px !important;
    }

    .bottom-table > tbody > tr > td {
        padding-right: 8px !important;
    }

    .payment-details {
        font-size: 9px !important;
        line-height: 1.4 !important;
    }

    .totals-table {
        font-size: 9px !important;
    }

    .totals-table td {
        padding: 3px 0 !important;
    }

    .totals-table .invoice-total td {
        font-size: 11px !important;
        padding: 5px 0 !important;
    }

    .service-notes {
        margin-top: 6px !important;
        font-size: 9px !important;
    }

    .invoice-footer {
        margin-top: 9px !important;
        padding-top: 6px !important;
        font-size: 8px !important;
    }

    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid !important;
    }

}

</style>

</head>

<body>

<div class="invoice-wrapper">

    <!-- PRINT BUTTON -->

    <!-- <div class="print-button">
        <button type="button" onclick="window.print();">
            Print / Save as PDF
        </button>
    </div> -->

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

                        <strong style="font-size:18px;color:#ffffff;">
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

        Address:  14 Avenue Sodnac, Quatre Bornes, Mauritius | Postal code: 72256<br>

        Email : acct@themyp.com | Phone : +230 5252 5772<br>

        BRN: C14124113<br>

        

    </div>

    <div class="divider"></div>

    <!-- BILL TO MERCHANT / INVOICE DETAILS -->

    <table class="details-table">

        <tbody>

            <tr>

                <td>

                    <div class="section-heading">
                        Bill to Merchant
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Merchant legal name:
                        </span>
                        <?php echo $e($merchant_name); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Billing address:
                        </span>
                        <?php echo $e($merchant_address); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            BRN:
                        </span>
                        <?php echo $e($merchant_brn); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Billing email:
                        </span>
                        <?php echo $e($merchant_email); ?>
                    </div>

                    <!-- <div class="detail-line">
                        <span class="detail-label">
                            Merchant reference:
                        </span>
                        <?php echo $e($merchant_reference); ?>
                    </div> -->

                </td>

                <td>

                    <div class="section-heading">
                        Invoice details
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Invoice number:
                        </span>
                        <?php echo $e($invoice_number); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Invoice date:
                        </span>
                        <?php echo $e($created_date); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Currency:
                        </span>
                        <?php echo $e($currency); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Order reference:
                        </span>
                        <?php echo $e($order_reference); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Subscription:
                        </span>
                        <?php echo $e($subscription_reference); ?>
                    </div>

                    <div class="detail-line">
                        <span class="detail-label">
                            Term:
                        </span>
                        <?php echo $e($term); ?>
                    </div>

                </td>

            </tr>

        </tbody>

    </table>

    <div class="divider"></div>

    <!-- SERVICE INFORMATION -->

    <div class="service-heading">
        Subscription Information
    </div>

    <table class="service-table">

        <thead>

            <tr>

                <th width="25%">
                    Service Name
                </th>

                <th width="32%">
                    Description
                </th>

                <th width="22%">
                    Service Dates
                </th>

                <th width="5%" class="text-center">
                    Qty
                </th>

                <th width="8%" class="text-right">
                    Unit Price
                </th>

                <th width="8%" class="text-right">
                    Amount
                </th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>
                    <?php echo $e($plan_name); ?>
                </td>

                <td>
                    Initial paid subscription plan<br>
                    Billing period: <?php echo $e($billing_period); ?>
                </td>

                <td>
                    Start: <?php echo $e($created_date); ?><br>
                    Expiry: <?php echo $e($expiry_date); ?>
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

                        <span class="<?php echo ($status === 'paid') ? 'status-paid' : 'status-unpaid'; ?>">
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

                        Yearly subscription term as shown in Service Dates.

                    </div>

                </td>

                <td>

                    <div class="section-heading">
                        Invoice totals (<?php echo $e($currency); ?>)
                    </div>

                    <table class="totals-table">

                        <tr>

                            <td>
                                Subtotal
                            </td>

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

                            <td>
                                Invoice total
                            </td>

                            <td class="text-right">
                                <?php echo number_format($invoice_total, 2); ?>
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Verified payment allocated
                            </td>

                            <td class="text-right">
                                <?php echo number_format($payment_allocated, 2); ?>
                            </td>

                        </tr>

                        <tr class="due-total">

                            <td>
                                Amount due at issue
                            </td>

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

        [Approved billing terms / verified support contact]<br>

        Thank you for using Yellow Markets.

    </div>

</div>

</body>

</html>
