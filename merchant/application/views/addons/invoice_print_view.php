
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (empty($addon_purchase)) {
    show_error('Invoice not found or payment is not completed.', 404);
    return;
}

// ==========================================
// SAFE OUTPUT
// ==========================================

$e = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

// ==========================================
// COMPANY DETAILS - MYP ONLINE MARKETING
// ==========================================

$company_name = 'MYP Online Marketing Ltd';
$company_service = 'Yellow Markets - A MYP Network Service';

$company_address = '14 Avenue Sodnac, Quatre Bornes, Mauritius | Postal code: 72256';
$company_email = 'acct@themyp.com';
$company_phone = '+230 5252 5772';
$company_brn = 'C14124113';

// ==========================================
// MERCHANT DETAILS
// ==========================================

$merchant_name = $addon_purchase->merchant_legal_name
    ?? $addon_purchase->merchant_name
    ?? 'N/A';

$merchant_address = $addon_purchase->billing_address
    ?? $addon_purchase->merchant_address
    ?? '';

$merchant_brn = $addon_purchase->billing_brn
    ?? $addon_purchase->merchant_brn
    ?? '';

$merchant_email = $addon_purchase->billing_email
    ?? $addon_purchase->merchant_email
    ?? '';

$merchant_reference = $addon_purchase->merchant_reference
    ?? 'N/A';

// ==========================================
// INVOICE DETAILS
// ==========================================

$purchase_id = $addon_purchase->purchase_id ?? '';

$service_name = $addon_purchase->addon_service_name ?? '';
$addon_name = $addon_purchase->addon_name ?? '';
$description = $addon_purchase->addon_description ?? '';

$order_number = $addon_purchase->order_number ?? '';
$ticket_number = $addon_purchase->ticket_number ?? '';
$transaction_ref = $addon_purchase->transaction_ref ?? '';

$invoice_date = $addon_purchase->date ?? '';

$currency = 'MUR';

// ==========================================
// PAYMENT STATUS
// ==========================================

$payment_status = strtolower(
    trim((string)($addon_purchase->payment_status ?? ''))
);

$is_paid = in_array(
    $payment_status,
    ['paid', 'success', 'completed'],
    TRUE
);

// ==========================================
// INVOICE CALCULATION
// ==========================================

$price = (float)($addon_purchase->price ?? 0);
$vat_percent = (float)($addon_purchase->vat_percent ?? 0);
$final_price = (float)($addon_purchase->final_price ?? 0);

$subtotal = $price;

$vat_amount = ($subtotal * $vat_percent) / 100;

$invoice_total = $subtotal + $vat_amount;

if ($final_price > 0 && abs($final_price - $invoice_total) < 0.01) {
    $invoice_total = $final_price;
}

$paid_amount = $is_paid ? $invoice_total : 0;

$amount_due = max(0, $invoice_total - $paid_amount);

// ==========================================
// LOGO
// ==========================================

$logo_image_src = '';

$candidate_logos = [
    rtrim(FCPATH, '/\\') . '/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/../merchant/public/images/yellow-markets-logo-yellow-white.png',
    'C:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png'
];

foreach ($candidate_logos as $logo) {

    if (file_exists($logo) && filesize($logo) > 0) {

        $extension = strtolower(pathinfo($logo, PATHINFO_EXTENSION));

        $mime = in_array($extension, ['jpg', 'jpeg']) ? 'jpeg' : 'png';

        $logo_image_src = 'data:image/' . $mime . ';base64,' .
            base64_encode(file_get_contents($logo));

        break;
    }
}

if (empty($logo_image_src)) {
    $logo_image_src = base_url(
        'public/images/yellow-markets-logo-yellow-white.png'
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add-on Invoice #<?php echo $e($purchase_id); ?></title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 15px;
    background: #eef1f5;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #172b4d;
}

.invoice-box {
    width: 100%;
    max-width: 800px;
    margin: auto;
    padding: 20px;
    background: #fff;
    border: 1px solid #cbd3df;
}

/* =================================
   HEADER
================================= */

.header {
    padding-bottom: 12px;
    margin-bottom: 15px;
    border-bottom: 2px solid #d6a900;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: middle;
}

.logo-container {
    display: inline-block;
    padding: 8px 12px;
    background: #23282d;
    border-radius: 4px;
}

.logo-container img {
    display: block;
    width: 120px;
    height: auto;
}

.brand-subtitle {
    margin-top: 5px;
    font-size: 10px;
    color: #68778b;
}

.invoice-heading {
    text-align: right;
    font-size: 19px;
    font-weight: bold;
    color: #172b4d;
}

.invoice-number {
    margin-top: 5px;
    text-align: right;
    font-size: 10px;
    color: #52647b;
}

/* =================================
   COMPANY DETAILS
================================= */

.details-title {
    margin-bottom: 18px;
    font-size: 21px;
    font-weight: bold;
    color: #172b4d;
}

.company-details {
    font-size: 11px;
    line-height: 1.65;
    color: #172b4d;
    padding-bottom: 12px;
    border-bottom: 1px solid #cbd3df;
}

.company-name {
    font-size: 12px;
    font-weight: bold;
    color: #172b4d;
}

.company-details strong {
    color: #172b4d;
}

/* =================================
   BILL TO MERCHANT
================================= */

.billing-section {
    padding-top: 15px;
    margin-bottom: 15px;
}

.billing-heading {
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: bold;
    color: #172b4d;
}

.billing-details {
    font-size: 11px;
    line-height: 1.8;
    color: #172b4d;
}

.billing-details strong {
    color: #172b4d;
}

/* =================================
   INVOICE DETAILS
================================= */

.details-wrapper {
    width: 100%;
    padding: 12px 0;
    margin-bottom: 10px;
    border-top: 1px solid #cbd3df;
    border-bottom: 1px solid #cbd3df;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    padding: 5px;
    border: none;
    vertical-align: top;
    font-size: 10px;
}

.section-heading {
    font-size: 13px !important;
    font-weight: bold;
    color: #172b4d;
    padding-bottom: 8px !important;
}

.detail-label {
    display: inline-block;
    width: 105px;
    color: #52647b;
}

/* =================================
   ITEMS TABLE
================================= */

.items-table {
    width: 100%;
    margin-top: 15px;
    margin-bottom: 15px;
    border-collapse: collapse;
}

.items-table th {
    padding: 8px 6px;
    background: #edf0f4;
    color: #172b4d;
    border-bottom: 1px solid #cbd3df;
    font-size: 10px;
    text-align: left;
}

.items-table td {
    padding: 8px 6px;
    border-bottom: 1px solid #cbd3df;
    font-size: 10px;
    color: #33445e;
    vertical-align: top;
}

.text-center {
    text-align: center !important;
}

.text-right {
    text-align: right !important;
}

/* =================================
   PAYMENT AND TOTAL
================================= */

.bottom-table {
    width: 100%;
    border-collapse: collapse;
}

.bottom-table > tbody > tr > td {
    padding: 3px;
    border: none;
    vertical-align: top;
}

.payment-heading,
.total-heading {
    padding-bottom: 8px;
    font-size: 12px;
    font-weight: bold;
    color: #172b4d;
}

.payment-info {
    font-size: 10px;
    line-height: 1.7;
    color: #33445e;
}

.status-paid {
    color: #198754;
    font-weight: bold;
}

.status-unpaid {
    color: #dc3545;
    font-weight: bold;
}

.total-table {
    width: 100%;
    border-collapse: collapse;
}

.total-table td {
    padding: 6px 3px;
    border-bottom: 1px solid #dce1e8;
    font-size: 10px;
    color: #33445e;
}

.total-table .grand-total td {
    padding: 8px 3px;
    font-size: 13px;
    font-weight: bold;
    color: #172b4d;
    border-top: 2px solid #172b4d;
    border-bottom: 2px solid #172b4d;
}

/* =================================
   FOOTER
================================= */

.invoice-footer {
    margin-top: 18px;
    padding-top: 10px;
    border-top: 1px solid #cbd3df;
    font-size: 9px;
    line-height: 1.6;
    color: #68778b;
}

.invoice-footer strong {
    color: #172b4d;
}

/* =================================
   PRINT BUTTON
================================= */

.print-section {
    margin-top: 20px;
    text-align: center;
}

.btn-print {
    padding: 10px 22px;
    border: none;
    border-radius: 4px;
    background: #ffd200;
    color: #000;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
}

/* =================================
   PRINT
================================= */

@page {
    size: A4;
    margin: 5mm;
}

@media print {

    body {
        padding: 0;
        background: #fff;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .invoice-box {
        width: 100%;
        max-width: none;
        padding: 8px;
        border: none;
    }

    .print-section {
        display: none !important;
    }

    .items-table th {
        background: #edf0f4 !important;
    }

    .logo-container {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

</style>

</head>

<body>

<div class="invoice-box">

    <!-- =================================
         HEADER
    ================================== -->

    <div class="header">

        <table class="header-table">

            <tr>

                <td width="60%">

                    <div class="logo-container">

                        <img
                            src="<?php echo $e($logo_image_src); ?>"
                            alt="Yellow Markets Logo"
                        >

                    </div>

                    <div class="brand-subtitle">
                        Merchant services
                    </div>

                </td>

                <td width="40%">

                    <div class="invoice-heading">
                        INVOICE
                    </div>

                    <div class="invoice-number">
                        Invoice #<?php echo $e($purchase_id); ?>
                    </div>

                </td>

            </tr>

        </table>

    </div>


    <!-- =================================
         COMPANY DETAILS
    ================================== -->

    <div class="details-title">
        Invoice Details
    </div>

    <div class="company-details">

        <div class="company-name">
            <?php echo $e($company_name); ?>
        </div>

        <?php echo $e($company_service); ?>

        <br>

        <strong>Address:</strong>
        <?php echo $e($company_address); ?>

        <br>

        <strong>Email:</strong>
        <?php echo $e($company_email); ?>

        | <strong>Phone:</strong>
        <?php echo $e($company_phone); ?>

        <br>

        <strong>BRN:</strong>
        <?php echo $e($company_brn); ?>

    </div>


    <!-- =================================
         BILL TO MERCHANT
    ================================== -->

    <div class="billing-section">

        <div class="billing-heading">
            Bill to Merchant
        </div>

        <div class="billing-details">

            <strong>Merchant legal name:</strong>
            <?php echo $e($merchant_name); ?>

            <br>

            <strong>Billing address:</strong>
            <?php if (!empty($merchant_address)): ?>
                <?php echo nl2br($e($merchant_address)); ?>
            <?php else: ?>
                N/A
            <?php endif; ?>

            <br>

            <strong>BRN:</strong>
            <?php echo !empty($merchant_brn) ? $e($merchant_brn) : 'N/A'; ?>

            <br>

            <strong>Billing email:</strong>
            <?php echo !empty($merchant_email) ? $e($merchant_email) : 'N/A'; ?>

            <br>

            <strong>Merchant reference:</strong>
            <?php echo $e($merchant_reference); ?>

        </div>

    </div>


    <!-- =================================
         INVOICE INFORMATION
    ================================== -->

    <div class="details-wrapper">

        <table class="details-table">

            <tr>

                <td colspan="2" class="section-heading">
                    Invoice Information
                </td>

            </tr>

            <tr>

                <td width="50%">
                    <span class="detail-label">Invoice number:</span>
                    <?php echo $e($purchase_id); ?>
                </td>

                <td width="50%">
                    <span class="detail-label">Invoice date:</span>
                    <?php echo $e($invoice_date); ?>
                </td>

            </tr>

            <tr>

                <td>
                    <span class="detail-label">Currency:</span>
                    <?php echo $e($currency); ?>
                </td>

                <td>
                    <span class="detail-label">Add-on:</span>
                    <?php echo $e($addon_name); ?>
                </td>

            </tr>

            <?php if (!empty($order_number)): ?>

            <tr>
                <td>
                    <span class="detail-label">Order reference:</span>
                    <?php echo $e($order_number); ?>
                </td>
                <td>
                    <?php if (!empty($ticket_number)): ?>
                        <span class="detail-label">Ticket:</span>
                        <?php echo $e($ticket_number); ?>
                    <?php endif; ?>
                </td>
            </tr>

            <?php endif; ?>

        </table>

    </div>


    <!-- =================================
         ADD-ON SERVICE TABLE
    ================================== -->

    <table class="items-table">

        <thead>

            <tr>

                <th width="23%">Service Name</th>

                <th width="42%">Description</th>

                <th width="8%" class="text-center">Qty</th>

                <th width="13%" class="text-right">
                    Unit Price (Excl. VAT)
                </th>

                <th width="14%" class="text-right">
                    Amount (Excl. VAT)
                </th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>
                    <strong><?php echo $e($service_name); ?></strong>
                </td>

                <td>

                    <strong><?php echo $e($addon_name); ?></strong>

                    <?php if (!empty($description)): ?>

                        <br>

                        <span style="color:#52647b;">

                            <?php
                            echo nl2br(
                                $e(
                                    html_entity_decode(
                                        strip_tags($description),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                )
                            );
                            ?>

                        </span>

                    <?php endif; ?>

                    <?php if (!empty($order_number)): ?>

                        <br>

                        <span style="font-size:9px;color:#68778b;">

                            Replacement Delivery for Order #
                            <?php echo $e($order_number); ?>

                            <?php if (!empty($ticket_number)): ?>
                                (Ticket <?php echo $e($ticket_number); ?>)
                            <?php endif; ?>

                        </span>

                    <?php endif; ?>

                </td>

                <td class="text-center">1</td>

                <td class="text-right">
                    <?php echo number_format($price, 2); ?>
                </td>

                <td class="text-right">
                    <?php echo number_format($price, 2); ?>
                </td>

            </tr>

        </tbody>

    </table>


    <!-- =================================
         PAYMENT INFORMATION AND TOTALS
    ================================== -->

    <table class="bottom-table">

        <tr>

            <td width="55%">

                <div class="payment-heading">
                    Payment Information
                </div>

                <div class="payment-info">

                    <strong>Accepted method:</strong>
                    My.T Money (mobile payment)

                    <br>

                    <strong>Status at issue:</strong>

                    <?php if ($is_paid): ?>

                        <span class="status-paid">Paid</span>

                    <?php else: ?>

                        <span class="status-unpaid">Unpaid</span>

                    <?php endif; ?>

                    <br>

                    <?php if (!empty($transaction_ref)): ?>

                        <strong>Transaction reference:</strong>
                        <?php echo $e($transaction_ref); ?>

                        <br>

                    <?php endif; ?>

                    An invoice does not independently confirm
                    receipt of payment. Payment confirmation
                    should be retained separately.

                    <br><br>

                    <strong>Service notes</strong>

                    <br>

                    One purchased add-on service.

                    <?php if (!empty($order_number)): ?>

                        <br>

                        Related order:
                        <?php echo $e($order_number); ?>

                    <?php endif; ?>

                </div>

            </td>

            <td width="45%">

                <div class="total-heading">
                    Invoice Totals (MUR)
                </div>

                <table class="total-table">

                    <tr>
                        <td>Subtotal</td>
                        <td class="text-right">
                            <?php echo number_format($subtotal, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            VAT (<?php echo number_format($vat_percent, 2); ?>%)
                        </td>
                        <td class="text-right">
                            <?php echo number_format($vat_amount, 2); ?>
                        </td>
                    </tr>

                    <tr class="grand-total">
                        <td>Invoice Total</td>
                        <td class="text-right">
                            <?php echo number_format($invoice_total, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>Verified payment allocated at issue</td>
                        <td class="text-right">
                            <?php echo number_format($paid_amount, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong>Amount Due at Issue</strong>
                        </td>
                        <td class="text-right">
                            <strong>
                                <?php echo number_format($amount_due, 2); ?>
                            </strong>
                        </td>
                    </tr>

                </table>

            </td>

        </tr>

    </table>


    <!-- =================================
         FOOTER
    ================================== -->

    <div class="invoice-footer">

        <strong><?php echo $e($company_name); ?></strong>

        <br>

        <?php echo $e($company_service); ?>

        <br>

        Approved billing terms / verified support contact

        <br>

        Thank you for using Yellow Markets.

    </div>


    <!-- =================================
         PRINT BUTTON
    ================================== -->

    <?php if (empty($is_pdf)): ?>

        <div class="print-section">

            <button
                type="button"
                onclick="window.print();"
                class="btn-print"
            >
                Print / Download Invoice
            </button>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
