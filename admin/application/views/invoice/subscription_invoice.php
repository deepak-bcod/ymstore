
<?php
// ==========================================
// INVOICE DATA
// ==========================================

$currency = !empty($order['currency']) ? $order['currency'] : 'MUR';

$amount = isset($order['amount']) ? (float)$order['amount'] : 0;

$created_date = !empty($order['created_at'])
    ? date('d F Y', strtotime($order['created_at']))
    : date('d F Y');

$status = !empty($order['status']) ? strtolower($order['status']) : 'unpaid';

$plan_name = !empty($order['plan']['name'])
    ? $order['plan']['name']
    : 'Subscription Plan';

$merchant_name = !empty($order['publisher']['publication_name'])
    ? $order['publisher']['publication_name']
    : (!empty($order['publisher']['name'])
        ? $order['publisher']['name']
        : 'N/A');

$invoice_number = !empty($order['invoice_number'])
    ? $order['invoice_number']
    : 'YM-INV-' . date('Y') . '-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);

$expiry_date = !empty($order['expiry_date'])
    ? date('d F Y', strtotime($order['expiry_date']))
    : date('d F Y', strtotime($order['created_at'] . ' +1 year'));

// VAT is only calculated when a VAT rate is supplied.
$vat_rate = isset($order['vat_rate']) ? (float)$order['vat_rate'] : 0;

$vat_amount = isset($order['vat_amount'])
    ? (float)$order['vat_amount']
    : ($amount * $vat_rate / 100);

$subtotal = isset($order['subtotal'])
    ? (float)$order['subtotal']
    : $amount;

$invoice_total = isset($order['total_amount'])
    ? (float)$order['total_amount']
    : ($subtotal + $vat_amount);

$paid_amount = isset($order['paid_amount'])
    ? (float)$order['paid_amount']
    : ($status == 'paid' ? $invoice_total : 0);

$amount_due = max(0, $invoice_total - $paid_amount);

$payment_method = !empty($order['payment_method'])
    ? $order['payment_method']
    : 'My.T Money (mobile payment)';

$term = !empty($order['term'])
    ? $order['term']
    : 'Yearly';

$service_start = $created_date;
$service_end = $expiry_date;

// ==========================================
// LOGO
// ==========================================

$logo_image_src = '';

$candidate_logos = [
    rtrim(FCPATH, '/\\') . '/../merchant/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/../uploads/yellow-markets-logo.png',
    rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png',
    dirname(FCPATH) . '/uploads/yellow-markets-logo.png',
    'C:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',
    'C:/xampp/htdocs/ymstore/uploads/yellow-markets-logo.png'
];

foreach ($candidate_logos as $logo_path) {
    if (file_exists($logo_path) && filesize($logo_path) > 0) {
        $ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));

        $mime = ($ext == 'jpg' || $ext == 'jpeg')
            ? 'image/jpeg'
            : (($ext == 'gif') ? 'image/gif' : 'image/png');

        $logo_image_src = 'data:' . $mime . ';base64,' .
            base64_encode(file_get_contents($logo_path));

        break;
    }
}

if (empty($logo_image_src) && !empty($logo_src)) {
    $logo_image_src = $logo_src;
}

// ==========================================
// ESCAPE HELPER
// ==========================================

$invoice_escape = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$format_money = function ($value) use ($currency) {
    return number_format((float)$value, 2) . ' ' . $currency;
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Subscription Invoice - <?php echo $invoice_escape($order['id']); ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #252b35;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .invoice-wrapper {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
            padding: 25px 30px;
            background: #ffffff;
        }

        .invoice-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .logo-box {
            display: inline-block;
            background: #23282d;
            border-radius: 5px;
            padding: 12px 18px;
            min-width: 240px;
            text-align: center;
        }

        .logo-box img {
            display: block;
            width: 200px;
            max-width: 100%;
            height: auto;
            border: 0;
            margin: 0 auto;
        }

        .invoice-heading {
            text-align: right;
            vertical-align: middle;
        }

        .invoice-heading h1 {
            font-size: 24px;
            font-weight: 700;
            color: #222831;
            margin: 0;
            letter-spacing: 1px;
        }

        .invoice-heading span {
            font-size: 11px;
            font-weight: 600;
            color: #777777;
        }

        .yellow-line {
            height: 4px;
            background: #eeb600;
            margin: 12px 0 20px;
            width: 100%;
        }

        .company-details {
            font-size: 11px;
            line-height: 1.7;
            color: #343a40;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .company-name {
            font-weight: 700;
            font-size: 13px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 12px 0 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #263342;
            margin: 0 0 10px;
        }

        .detail-content {
            font-size: 11px;
            line-height: 1.8;
            color: #343a40;
        }

        .detail-content strong {
            font-weight: 600;
        }

        .info-label {
            display: inline-block;
            width: 115px;
            font-weight: 600;
        }

        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 22px;
            table-layout: fixed;
        }

        .invoice-items th {
            background: #edf1f4;
            color: #273444;
            font-size: 10px;
            font-weight: 700;
            text-align: left;
            padding: 10px 7px;
            border-bottom: 1px solid #d9dee3;
        }

        .invoice-items td {
            padding: 12px 7px;
            font-size: 10.5px;
            vertical-align: top;
            color: #333333;
            border-bottom: 1px solid #dee2e6;
            word-wrap: break-word;
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
            margin-top: 12px;
        }

        .bottom-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 12px 0 0;
        }

        .payment-details {
            font-size: 11px;
            line-height: 1.8;
            color: #343a40;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 0;
            font-size: 11px;
            border-bottom: 1px solid #e5e8eb;
        }

        .totals-table .total-row td {
            font-size: 15px;
            font-weight: 700;
            border-top: 1px solid #aeb7c0;
            border-bottom: 2px solid #aeb7c0;
            padding: 10px 0;
        }

        .totals-table .amount {
            text-align: right;
            font-weight: 600;
        }

        .service-notes {
            font-size: 10px;
            line-height: 1.7;
            color: #555555;
            margin-top: 12px;
        }

        .invoice-footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            line-height: 1.7;
            color: #555555;
        }

        .invoice-footer strong {
            color: #252b35;
        }

        .no-print {
            margin: 15px auto;
            max-width: 850px;
            text-align: center;
        }

        .print-button {
            background: #23282d;
            color: #ffffff;
            border: 0;
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .print-button:hover {
            background: #eeb600;
            color: #222222;
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            .no-print {
                display: none !important;
            }

            .invoice-wrapper {
                max-width: 100%;
                padding: 15px 20px;
                margin: 0;
            }

            .logo-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .yellow-line,
            .invoice-items th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .invoice-items,
            .bottom-table,
            .details-table {
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }

        @media screen and (max-width: 600px) {
            .invoice-wrapper {
                padding: 15px;
            }

            .logo-box {
                min-width: 0;
                padding: 10px;
            }

            .logo-box img {
                width: 140px;
            }

            .invoice-heading h1 {
                font-size: 18px;
            }

            .info-label {
                width: 95px;
            }

            .invoice-items th,
            .invoice-items td {
                padding: 6px 4px;
                font-size: 9px;
            }
        }
    </style>
</head>

<body>

<?php if (!empty($is_html_fallback)): ?>
    <div class="no-print">
        <button class="print-button" onclick="window.print();">
            Print / Save as PDF
        </button>
    </div>
<?php endif; ?>

<div class="invoice-wrapper">

    <!-- ==================================
         HEADER / LOGO
    =================================== -->

    <table class="invoice-header">
        <tr>
            <td width="65%" valign="middle">
                <div class="logo-box">
                    <?php if (!empty($logo_image_src)): ?>
                        <img src="<?php echo $logo_image_src; ?>"
                             alt="Yellow Markets Logo">
                    <?php else: ?>
                        <span style="font-size:22px;color:#ffffff;">
                            <span style="color:#ffd200;">Yellow</span> Markets
                        </span>
                    <?php endif; ?>
                </div>
            </td>

            <td width="35%" class="invoice-heading">
                <h1>INVOICE</h1>
                <span>SUBSCRIPTION INVOICE</span>
            </td>
        </tr>
    </table>

    <div class="yellow-line"></div>

    <!-- ==================================
         COMPANY INFORMATION
    =================================== -->

    <div class="company-details">
        <div class="company-name">MYP Online Marketing Ltd</div>
        Yellow Markets - A MYP Network Service<br>

        <?php if (!empty($order['company_address'])): ?>
            <?php echo nl2br($invoice_escape($order['company_address'])); ?><br>
        <?php endif; ?>

        <?php if (!empty($order['company_brn'])): ?>
            BRN: <?php echo $invoice_escape($order['company_brn']); ?><br>
        <?php endif; ?>

        <?php if (!empty($order['company_email'])): ?>
            Email: <?php echo $invoice_escape($order['company_email']); ?><br>
        <?php endif; ?>

        <?php if (!empty($order['company_phone'])): ?>
            Telephone: <?php echo $invoice_escape($order['company_phone']); ?><br>
        <?php endif; ?>

        <?php if (!empty($order['company_vat_number'])): ?>
            VAT number: <?php echo $invoice_escape($order['company_vat_number']); ?>
        <?php endif; ?>
    </div>

    <!-- ==================================
         MERCHANT / INVOICE DETAILS
    =================================== -->

    <table class="details-table">
        <tr>
            <td>
                <h3 class="section-title">Bill to Merchant</h3>

                <div class="detail-content">
                    <strong><?php echo $invoice_escape($merchant_name); ?></strong><br>

                    <?php if (!empty($order['publisher']['legal_name'])): ?>
                        <?php echo $invoice_escape($order['publisher']['legal_name']); ?><br>
                    <?php endif; ?>

                    <?php if (!empty($order['publisher']['address'])): ?>
                        <?php echo nl2br($invoice_escape($order['publisher']['address'])); ?><br>
                    <?php endif; ?>

                    <?php if (!empty($order['publisher']['brn'])): ?>
                        BRN: <?php echo $invoice_escape($order['publisher']['brn']); ?><br>
                    <?php endif; ?>

                    <?php if (!empty($order['publisher']['email'])): ?>
                        Email: <?php echo $invoice_escape($order['publisher']['email']); ?><br>
                    <?php endif; ?>

                    <?php if (!empty($order['publisher']['vat_number'])): ?>
                        VAT number: <?php echo $invoice_escape($order['publisher']['vat_number']); ?><br>
                    <?php endif; ?>

                    Merchant reference:
                    <?php echo !empty($order['merchant_reference'])
                        ? $invoice_escape($order['merchant_reference'])
                        : 'N/A'; ?>
                </div>
            </td>

            <td>
                <h3 class="section-title">Invoice Details</h3>

                <div class="detail-content">

                    <div>
                        <span class="info-label">Invoice number</span>:
                        <?php echo $invoice_escape($invoice_number); ?>
                    </div>

                    <div>
                        <span class="info-label">Issue date</span>:
                        <?php echo $created_date; ?>
                    </div>

                    <div>
                        <span class="info-label">Due date</span>:
                        <?php echo !empty($order['due_date'])
                            ? date('d F Y', strtotime($order['due_date']))
                            : $created_date; ?>
                    </div>

                    <div>
                        <span class="info-label">Currency</span>:
                        <?php echo $invoice_escape($currency); ?>
                    </div>

                    <div>
                        <span class="info-label">Order reference</span>:
                        <?php echo !empty($order['order_reference'])
                            ? $invoice_escape($order['order_reference'])
                            : 'DEMO-ORDER-' . $invoice_escape($order['id']); ?>
                    </div>

                    <div>
                        <span class="info-label">Subscription</span>:
                        <?php echo !empty($order['subscription_code'])
                            ? $invoice_escape($order['subscription_code'])
                            : 'DEMO-SUB-' . $invoice_escape($order['id']); ?>
                    </div>

                    <div>
                        <span class="info-label">Term</span>:
                        <?php echo $invoice_escape($term); ?>
                    </div>

                </div>
            </td>
        </tr>
    </table>

    <!-- ==================================
         SUBSCRIPTION ITEMS
    =================================== -->

    <table class="invoice-items">
        <thead>
            <tr>
                <th width="23%">Service Name</th>
                <th width="27%">Description</th>
                <th width="20%">Service Dates</th>
                <th width="7%" class="text-center">Qty</th>
                <th width="12%" class="text-right">Unit Price</th>
                <th width="11%" class="text-right">Amount</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <strong><?php echo $invoice_escape($plan_name); ?></strong>
                </td>

                <td>
                    Initial paid subscription<br>
                    Billing period: <?php echo $invoice_escape($term); ?>
                </td>

                <td>
                    Start: <?php echo $service_start; ?><br>
                    Expiry: <?php echo $service_end; ?>
                </td>

                <td class="text-center">1</td>

                <td class="text-right">
                    <?php echo $format_money($subtotal); ?>
                </td>

                <td class="text-right">
                    <?php echo $format_money($subtotal); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ==================================
         PAYMENT / TOTALS
    =================================== -->

    <table class="bottom-table">
        <tr>
            <td>
                <h3 class="section-title">Payment Information</h3>

                <div class="payment-details">
                    <strong>Accepted method:</strong>
                    <?php echo $invoice_escape($payment_method); ?><br>

                    <strong>Status at issue:</strong>
                    <?php echo ucfirst($invoice_escape($status)); ?><br>

                    <?php if ($status != 'paid'): ?>
                        An invoice does not confirm receipt of payment.
                        A separate payment confirmation or receipt follows
                        verified payment.
                    <?php else: ?>
                        Payment has been received successfully.
                    <?php endif; ?>
                </div>

                <div class="service-notes">
                    <strong style="font-size:12px;color:#263342;">
                        Service Notes
                    </strong><br>

                    Yearly subscription term as shown in Service Dates.
                </div>
            </td>

            <td>
                <h3 class="section-title">Invoice Totals (<?php echo $invoice_escape($currency); ?>)</h3>

                <table class="totals-table">
                    <tr>
                        <td>Subtotal (Excl. VAT)</td>
                        <td class="amount">
                            <?php echo $format_money($subtotal); ?>
                        </td>
                    </tr>

                    <?php if ($vat_rate > 0 || $vat_amount > 0): ?>
                        <tr>
                            <td>
                                VAT at <?php echo number_format($vat_rate, 2); ?>%
                            </td>
                            <td class="amount">
                                <?php echo $format_money($vat_amount); ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr class="total-row">
                        <td>Invoice Total</td>
                        <td class="amount">
                            <?php echo $format_money($invoice_total); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>Verified payment allocated at issue</td>
                        <td class="amount">
                            <?php echo $format_money($paid_amount); ?>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>Amount due at issue</strong></td>
                        <td class="amount">
                            <strong><?php echo $format_money($amount_due); ?></strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ==================================
         FOOTER
    =================================== -->

    <div class="invoice-footer">
        <strong>MYP Online Marketing Ltd</strong><br>
        Yellow Markets - A MYP Network Service<br>

        <?php if (!empty($order['company_brn'])): ?>
            BRN: <?php echo $invoice_escape($order['company_brn']); ?><br>
        <?php endif; ?>

        <?php if (!empty($order['company_email'])): ?>
            <?php echo $invoice_escape($order['company_email']); ?><br>
        <?php endif; ?>

        Thank you for using Yellow Markets.
    </div>

</div>

</body>
</html>
