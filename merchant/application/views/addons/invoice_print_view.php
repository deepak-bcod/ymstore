<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (empty($addon_purchase)) {
    show_error('Invoice not found or payment is not completed.', 404);
    return;
}

$purchase_id = $addon_purchase->purchase_id ?? '';

$merchant_name = $addon_purchase->merchant_name ?? 'N/A';
$merchant_email = $addon_purchase->merchant_email ?? '';

$merchant_address = $addon_purchase->merchant_address ?? '';
$merchant_brn = $addon_purchase->merchant_brn ?? '';

$service_name = $addon_purchase->addon_service_name ?? '';
$addon_name = $addon_purchase->addon_name ?? '';
$description = $addon_purchase->addon_description ?? '';

$order_number = $addon_purchase->order_number ?? '';
$ticket_number = $addon_purchase->ticket_number ?? '';
$transaction_ref = $addon_purchase->transaction_ref ?? '';

$price = (float)($addon_purchase->price ?? 0);
$vat_percent = (float)($addon_purchase->vat_percent ?? 0);
$final_price = (float)($addon_purchase->final_price ?? 0);
$amount = (float)($addon_purchase->amount ?? 0);

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
// INVOICE CALCULATIONS
// ==========================================

$invoice_total = $amount > 0 ? $amount : $final_price;

$vat_amount = max(0, $final_price - $price);

if ($final_price <= 0) {
    $vat_amount = $price * $vat_percent / 100;
}

$vat_amount = min($vat_amount, $invoice_total);

$subtotal = max(0, $invoice_total - $vat_amount);

$paid_amount = $is_paid ? $invoice_total : 0;

$amount_due = max(0, $invoice_total - $paid_amount);

// ==========================================
// SAFE OUTPUT
// ==========================================

$e = function($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
};


$logo_image_src = '';

$candidate_logos = [
    rtrim(FCPATH, '/\\') .
        '/public/images/yellow-markets-logo-yellow-white.png',

    rtrim(FCPATH, '/\\') .
        '/../merchant/public/images/yellow-markets-logo-yellow-white.png',

    'C:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',

    rtrim(FCPATH, '/\\') .
        '/uploads/yellow-markets-logo.png'
];

foreach ($candidate_logos as $logo) {

    if (file_exists($logo) && filesize($logo) > 0) {

        $extension = strtolower(
            pathinfo($logo, PATHINFO_EXTENSION)
        );

        $mime = in_array($extension, ['jpg', 'jpeg'])
            ? 'jpeg'
            : 'png';

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
    padding: 10px;
    background: #eef1f5;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #172b4d;
}

.invoice-box {
    width: 100%;
    max-width: 800px;
    min-height: auto;
    margin: auto;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #cbd3df;
}

/* HEADER */

.header {
    width: 100%;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 2px solid #d6a900;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table td {
    padding: 0;
    border: none;
    vertical-align: top;
}

.logo-container {
    background-color: #23282d;
    padding: 8px 12px;
    border-radius: 4px;
    display: inline-block;
    vertical-align: middle;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.logo-container img {
    display: block;
    width: 120px;
    max-width: 100%;
    height: auto;
    border: 0;
}

.brand-subtitle {
    margin-top: 4px;
    font-size: 9px;
    color: #68778b;
}

.invoice-heading {
    text-align: right;
    font-size: 18px;
    font-weight: bold;
    letter-spacing: 1px;
    color: #172b4d;
}

.invoice-number {
    text-align: right;
    margin-top: 4px;
    font-size: 9px;
    color: #52647b;
}


.company-details {
    line-height: 1.4;
    margin-bottom: 10px;
    font-size: 10px;
    color: #33445e;
}

.company-name {
    font-weight: bold;
    color: #172b4d;
}

.company-details .billing-info {
    margin-top: 2px;
}

.company-details strong {
    color: #172b4d;
}


.details-wrapper {
    width: 100%;
    padding: 10px 0;
    margin-bottom: 10px;
    border-top: 1px solid #cbd3df;
    border-bottom: 1px solid #cbd3df;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    padding: 3px 4px;
    border: none;
    vertical-align: top;
    font-size: 10px;
}

.details-table .section-heading {
    font-size: 13px;
    font-weight: bold;
    color: #172b4d;
    padding-bottom: 6px;
}

.details-table .detail-label {
    width: 110px;
    color: #52647b;
}

.details-table .detail-value {
    color: #172b4d;
}


.items-table {
    width: 100%;
    margin-top: 10px;
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


.bottom-table {
    width: 100%;
    border-collapse: collapse;
}

.bottom-table > tbody > tr > td {
    padding: 2px;
    border: none;
    vertical-align: top;
}

.payment-heading,
.total-heading {
    padding-bottom: 6px;
    font-size: 12px;
    font-weight: bold;
    color: #172b4d;
}

.payment-info {
    font-size: 10px;
    line-height: 1.5;
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
    padding: 5px 3px;
    border-bottom: 1px solid #dce1e8;
    font-size: 10px;
    color: #33445e;
}

.total-table .grand-total td {
    padding-top: 6px;
    padding-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
    color: #172b4d;
    border-top: 2px solid #172b4d;
    border-bottom: 2px solid #172b4d;
}


.invoice-footer {
    margin-top: 15px;
    padding-top: 8px;
    border-top: 1px solid #cbd3df;
    font-size: 9px;
    line-height: 1.4;
    color: #68778b;
}

.invoice-footer strong {
    color: #172b4d;
}


.print-section {
    margin-top: 15px;
    text-align: center;
}

.btn-print {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    background: #ffd200;
    color: #000;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
}

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
        min-height: auto;
        padding: 5px;
        border: none;
    }

    .print-section {
        display: none !important;
    }

    .items-table th {
        background: #edf0f4 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .logo-container {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .header {
        border-bottom: 2px solid #d6a900;
    }
}

</style>

</head>

<body>

<div class="invoice-box">

    <!-- HEADER -->

    <div class="header">

        <table class="header-table">

            <tr>

                <td width="60%">

                    <div class="logo-container">

                        <?php if (!empty($logo_image_src)): ?>

                            <img
                                src="<?php echo $e($logo_image_src); ?>"
                                alt="Yellow Markets Logo"
                            >

                        <?php else: ?>

                            <span>Yellow Markets Logo</span>

                        <?php endif; ?>

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


    <!-- ======================================
         COMPANY INFORMATION
         MERCHANT ADDRESS AND BRN
    ======================================= -->

    
<!-- COMPANY INFORMATION -->

<div class="company-details">

    <div class="company-name">
        MYP Online Marketing Ltd
    </div>

    Yellow Markets - A MYP Network Service<br>

    <?php if (!empty($merchant_address)): ?>
        <strong>Merchant Address:</strong>
        <?php echo nl2br($e($merchant_address)); ?>
        <br>
    <?php endif; ?>

    <?php if (!empty($merchant_brn)): ?>
        <strong>BRN:</strong>
        <?php echo $e($merchant_brn); ?>
        <br>
    <?php endif; ?>

    <?php if (!empty($merchant_email)): ?>
        <?php echo $e($merchant_email); ?>
    <?php endif; ?>

    <?php if (!empty($merchant_phone)): ?>
        · <?php echo $e($merchant_phone); ?>
    <?php endif; ?>

    <?php if (!empty($merchant_landline)): ?>
        · <?php echo $e($merchant_landline); ?>
    <?php endif; ?>

</div>



    <!-- MERCHANT AND INVOICE DETAILS -->

    <div class="details-wrapper">

        <table class="details-table">

            <tr>

                <td width="50%" class="section-heading">
                    Bill to Merchant
                </td>

                <td width="50%" class="section-heading">
                    Invoice details
                </td>

            </tr>

            <tr>

                <td>
                    <strong><?php echo $e($merchant_name); ?></strong>
                </td>

                <td>
                    <span class="detail-label">Invoice number</span>
                    <span class="detail-value"><?php echo $e($purchase_id); ?></span>
                </td>

            </tr>

            <tr>

                <td>
                    <?php echo $e($merchant_email); ?>
                </td>

                <td>
                    <span class="detail-label">Invoice date</span>
                    <span class="detail-value"><?php echo $e($invoice_date); ?></span>
                </td>

            </tr>

            <tr>

                <td>
                    Merchant reference: <?php echo $e($merchant_name); ?>
                </td>

                <td>
                    <span class="detail-label">Currency</span>
                    <span class="detail-value"><?php echo $e($currency); ?></span>
                </td>

            </tr>

            <?php if (!empty($order_number)): ?>

                <tr>
                    <td></td>
                    <td>
                        <span class="detail-label">Order reference</span>
                        <span class="detail-value"><?php echo $e($order_number); ?></span>
                    </td>
                </tr>

            <?php endif; ?>

            <?php if (!empty($ticket_number)): ?>

                <tr>
                    <td></td>
                    <td>
                        <span class="detail-label">Resolution ticket</span>
                        <span class="detail-value"><?php echo $e($ticket_number); ?></span>
                    </td>
                </tr>

            <?php endif; ?>

            <tr>
                <td></td>
                <td>
                    <span class="detail-label">Add-on</span>
                    <span class="detail-value"><?php echo $e($addon_name); ?></span>
                </td>
            </tr>

        </table>

    </div>


    <!-- ADD-ON SERVICE TABLE -->

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
                    <?php echo number_format($subtotal, 2); ?>
                </td>

                <td class="text-right">
                    <?php echo number_format($subtotal, 2); ?>
                </td>

            </tr>

        </tbody>

    </table>


    <!-- PAYMENT INFORMATION AND TOTALS -->

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
                    Invoice totals (MUR)
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
                        <td>Invoice total</td>
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
                            <strong>Amount due at issue</strong>
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


    <!-- FOOTER -->

    <div class="invoice-footer">

        <strong>MYP Online Marketing Ltd</strong>

        <br>

        Yellow Markets - A MYP Network Service

        <br>

        Approved billing terms / verified support contact

        <br>

        Thank you for using Yellow Markets.

    </div>


    <!-- PRINT BUTTON -->

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