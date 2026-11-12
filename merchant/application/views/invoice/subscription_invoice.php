
<?php
// ============================================
// SUBSCRIPTION INVOICE
// ============================================

$e = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$publisher = isset($order['publisher']) && is_array($order['publisher'])
    ? $order['publisher'] : [];

$plan = isset($order['plan']) && is_array($order['plan'])
    ? $order['plan'] : [];

$invoice_id = isset($order['id']) ? $order['id'] : '';

$merchant_name = !empty($publisher['publication_name'])
    ? $publisher['publication_name']
    : (!empty($publisher['name']) ? $publisher['name'] : 'N/A');

$plan_name = !empty($plan['name']) ? $plan['name'] : 'Subscription Plan';

$amount = isset($order['amount']) ? (float)$order['amount'] : 0;

// VAT calculation: assumes amount is EXCLUSIVE of VAT.
$vat_rate = isset($order['vat_rate']) ? (float)$order['vat_rate'] : 15;

$subtotal = $amount;
$vat_amount = ($subtotal * $vat_rate) / 100;
$invoice_total = $subtotal + $vat_amount;

// Payment status
$status = strtolower(isset($order['status']) ? $order['status'] : 'unpaid');

$payment_allocated = ($status === 'paid') ? $invoice_total : 0;
$amount_due = max(0, $invoice_total - $payment_allocated);

// Currency
$currency = !empty($order['currency']) ? $order['currency'] : 'MUR';

// Dates
$created_date = !empty($order['created_at'])
    ? date('d F Y', strtotime($order['created_at']))
    : date('d F Y');

$expiry_date = !empty($order['expiry_date'])
    ? date('d F Y', strtotime($order['expiry_date']))
    : date('d F Y', strtotime($created_date . ' +1 year'));

$invoice_number = !empty($order['invoice_number'])
    ? $order['invoice_number']
    : 'SUB-INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);

$order_reference = !empty($order['order_reference'])
    ? $order['order_reference']
    : 'SUB-ORDER-' . $invoice_id;

$subscription_reference = !empty($order['subscription_reference'])
    ? $order['subscription_reference']
    : 'N/A';

// Merchant information (uses available publisher fields)
$merchant_address = !empty($publisher['billing_address'])
    ? $publisher['billing_address']
    : (!empty($publisher['address']) ? $publisher['address'] : 'N/A');

$merchant_email = !empty($publisher['billing_email'])
    ? $publisher['billing_email']
    : (!empty($publisher['email']) ? $publisher['email'] : 'N/A');

$merchant_phone = !empty($publisher['billing_phone'])
    ? $publisher['billing_phone']
    : (!empty($publisher['phone']) ? $publisher['phone'] : 'N/A');

$merchant_brn = !empty($publisher['brn']) ? $publisher['brn'] : 'N/A';

$merchant_reference = !empty($publisher['merchant_reference'])
    ? $publisher['merchant_reference']
    : 'N/A';

// Logo
$logo_image_src = '';

$candidate_logos = [
    rtrim(FCPATH, '/\\') . '/../merchant/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/../uploads/yellow-markets-logo.png',
    rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png',
    dirname(FCPATH) . '/uploads/yellow-markets-logo.png',
    'c:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',
    'c:/xampp/htdocs/ymstore/uploads/yellow-markets-logo.png'
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

if (empty($logo_image_src) &&
    !empty($logo_src) &&
    strpos($logo_src, 'data:image') === 0) {
    $logo_image_src = $logo_src;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Subscription Invoice - <?php echo $e($invoice_number); ?></title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
            font-size: 12px;
            line-height: 1.5;
        }

        .invoice-wrapper {
            max-width: 900px;
            margin: 25px auto;
            background: #ffffff;
            padding: 30px 35px;
        }

        .print-button {
            text-align: right;
            margin-bottom: 15px;
        }

        .print-button button {
            background: #d4a900;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 13px;
        }

        .invoice-header {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-header td {
            vertical-align: middle;
            padding: 5px 0;
        }

        .logo {
            max-width: 175px;
            max-height: 65px;
            object-fit: contain;
        }

        .invoice-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #202938;
        }

        .invoice-subtitle {
            font-size: 10px;
            color: #777777;
            text-align: right;
        }

        .yellow-line {
            height: 3px;
            background: #e5ad18;
            margin: 15px 0;
        }

        .company-details {
            font-size: 11px;
            line-height: 1.7;
            color: #444444;
            padding-bottom: 12px;
        }

        .company-name {
            font-weight: bold;
            font-size: 12px;
        }

        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .section-table td {
            width: 50%;
            vertical-align: top;
            padding: 8px 12px 15px 0;
        }

        .section-heading {
            font-size: 13px;
            font-weight: bold;
            color: #202938;
            margin-bottom: 7px;
        }

        .detail-line {
            margin-bottom: 4px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .detail-label {
            display: inline-block;
            font-weight: bold;
            min-width: 115px;
            color: #444444;
        }

        .divider {
            border-top: 1px solid #dee2e6;
            margin: 10px 0 15px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 20px;
            font-size: 11px;
        }

        .items-table th {
            background: #f1f2f3;
            color: #333333;
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
            font-weight: bold;
        }

        .items-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #dee2e6;
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
            margin-top: 15px;
        }

        .bottom-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 15px 10px 0;
        }

        .payment-details {
            line-height: 1.8;
            font-size: 11px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .totals-table td {
            padding: 5px 0;
        }

        .totals-table .total-row td {
            border-top: 2px solid #202938;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
            font-weight: bold;
            padding: 10px 0;
        }

        .totals-table .due-row td {
            font-weight: bold;
            padding-top: 9px;
        }

        .notes {
            margin-top: 12px;
            font-size: 11px;
            line-height: 1.7;
        }

        .invoice-footer {
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
            padding-top: 12px;
            font-size: 10px;
            line-height: 1.7;
            color: #555555;
        }

        .status-paid {
            color: #228b45;
            font-weight: bold;
        }

        .status-unpaid {
            color: #c0392b;
            font-weight: bold;
        }

        @media screen and (max-width: 600px) {

            .invoice-wrapper {
                margin: 0;
                padding: 18px;
            }

            .section-table td {
                display: block;
                width: 100%;
            }

            .bottom-table > tbody > tr > td {
                display: block;
                width: 100%;
                padding-right: 0;
                padding-bottom: 20px;
            }

            .items-table {
                font-size: 10px;
            }

            .items-table th,
            .items-table td {
                padding: 7px 4px;
            }

        }

        @media print {

            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .invoice-wrapper {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .print-button {
                display: none !important;
            }

            .yellow-line,
            .items-table th {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
            }

        }

    </style>

</head>

<body>

<div class="invoice-wrapper">

    <div class="print-button">
        <button onclick="window.print();">
            Print / Save as PDF
        </button>
    </div>

    <!-- HEADER -->

    <table class="invoice-header">

        <tr>

            <td width="50%">

                <?php if (!empty($logo_image_src)) { ?>

                    <img
                        src="<?php echo $logo_image_src; ?>"
                        class="logo"
                        alt="Yellow Markets Logo"
                    >

                <?php } else { ?>

                    <strong style="font-size:20px;color:#222;">
                        YELLOW MARKETS
                    </strong>

                <?php } ?>

            </td>

            <td width="50%">

                <div class="invoice-title">
                    INVOICE
                </div>

                <div class="invoice-subtitle">
                    SUBSCRIPTION INVOICE
                </div>

            </td>

        </tr>

    </table>

    <div class="yellow-line"></div>

    <!-- COMPANY INFORMATION -->

    <div class="company-details">

        <div class="company-name">
            MYP Online Marketing Ltd
        </div>

        Yellow Markets - A MYP Network Service<br>

        [Verified supplier address] - BRN: [Verified BRN]<br>

        [Billing email] | [Telephone]<br>

        VAT number: [Verified supplier VAT number]

    </div>

    <div class="divider"></div>

    <!-- MERCHANT + INVOICE DETAILS -->

    <table class="section-table">

        <tr>

            <td>

                <div class="section-heading">
                    Bill to Merchant
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Merchant legal name
                    </span>
                    <?php echo $e($merchant_name); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Merchant address
                    </span>
                    <?php echo $e($merchant_address); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        BRN
                    </span>
                    <?php echo $e($merchant_brn); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Billing email
                    </span>
                    <?php echo $e($merchant_email); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Billing phone
                    </span>
                    <?php echo $e($merchant_phone); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Merchant reference
                    </span>
                    <?php echo $e($merchant_reference); ?>
                </div>

            </td>

            <td>

                <div class="section-heading">
                    Invoice details
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Invoice number
                    </span>
                    <?php echo $e($invoice_number); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Issue date
                    </span>
                    <?php echo $e($created_date); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Due date
                    </span>
                    <?php echo $e($created_date); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Currency
                    </span>
                    <?php echo $e($currency); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Order reference
                    </span>
                    <?php echo $e($order_reference); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Subscription
                    </span>
                    <?php echo $e($subscription_reference); ?>
                </div>

                <div class="detail-line">
                    <span class="detail-label">
                        Term
                    </span>
                    Yearly
                </div>

            </td>

        </tr>

    </table>

    <div class="divider"></div>

    <!-- SUBSCRIPTION INFORMATION -->

    <div class="section-heading">
        Subscription Information
    </div>

    <table class="items-table">

        <thead>

            <tr>

                <th width="25%">
                    Service Name
                </th>

                <th width="35%">
                    Description
                </th>

                <th width="17%">
                    Service Dates
                </th>

                <th width="5%" class="text-center">
                    Qty
                </th>

                <th width="18%" class="text-right">
                    Amount (Excl. VAT)
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
                    Billing period: Yearly
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

            </tr>

        </tbody>

    </table>

    <!-- PAYMENT INFORMATION + TOTALS -->

    <table class="bottom-table">

        <tbody>

            <tr>

                <!-- PAYMENT INFORMATION -->

                <td>

                    <div class="section-heading">
                        Payment information
                    </div>

                    <div class="payment-details">

                        <strong>Accepted method:</strong>
                        My.T Money (mobile payment)<br>

                        <strong>Status at issue:</strong>

                        <span class="<?php echo ($status === 'paid') ? 'status-paid' : 'status-unpaid'; ?>">

                            <?php echo $e(ucfirst($status)); ?>

                        </span>

                        <br>

                        An invoice does not confirm receipt of payment.
                        A separate payment confirmation or receipt follows
                        verified payment.

                    </div>

                    <div class="notes">

                        <strong>Service notes</strong><br>

                        Yearly subscription term as shown in Service Dates.

                    </div>

                </td>

                <!-- INVOICE TOTALS -->

                <td>

                    <div class="section-heading">
                        Invoice totals (<?php echo $e($currency); ?>)
                    </div>

                    <table class="totals-table">

                        <tr>

                            <td>
                                Subtotal (Excl. VAT)
                            </td>

                            <td class="text-right">
                                <?php echo number_format($subtotal, 2); ?>
                            </td>

                        </tr>

                        <tr>

                            <td>
                                Taxable amount
                            </td>

                            <td class="text-right">
                                <?php echo number_format($subtotal, 2); ?>
                            </td>

                        </tr>

                        <tr>

                            <td>
                                VAT at <?php echo number_format($vat_rate, 2); ?>%
                            </td>

                            <td class="text-right">
                                <?php echo number_format($vat_amount, 2); ?>
                            </td>

                        </tr>

                        <tr class="total-row">

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

                        <tr class="due-row">

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
