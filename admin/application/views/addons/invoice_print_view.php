<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add-on Invoice #<?php echo $addon_purchase->purchase_id; ?></title>
<?php
$logo_image_src = '';
$candidate_logos = [
    rtrim(FCPATH, '/\\') . '/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/../merchant/public/images/yellow-markets-logo-yellow-white.png',
    'c:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',
    rtrim(FCPATH, '/\\') . '/../uploads/yellow-markets-logo.png',
    rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png',
    dirname(FCPATH) . '/uploads/yellow-markets-logo.png',
    'c:/xampp/htdocs/ymstore/uploads/yellow-markets-logo.png'
];
foreach ($candidate_logos as $c_logo) {
    if (file_exists($c_logo) && filesize($c_logo) > 0) {
        $ext = pathinfo($c_logo, PATHINFO_EXTENSION);
        $logo_image_src = 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode(file_get_contents($c_logo));
        break;
    }
}
if (empty($logo_image_src)) {
    $logo_image_src = base_url('public/images/yellow-markets-logo-yellow-white.png');
}
?>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 40px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .brand-logo-container {
            background-color: #23282d;
            padding: 10px 16px;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .brand-logo-container img {
            display: block;
            width: 135px;
            max-width: 100%;
            height: auto;
            border: 0;
        }
        .invoice-heading {
            font-size: 16px;
            font-weight: 700;
            color: #212529;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 14px;
        }
        .details-table td.col-label {
            color: #333;
            width: 110px;
        }
        .details-table td.col-val {
            font-weight: normal;
            color: #111;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        .table th {
            background-color: #ffd200 !important;
            color: #000000 !important;
            text-align: center;
            padding: 12px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #dee2e6;
            letter-spacing: 0.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 14px;
            color: #333;
        }
        .print-section {
            text-align: center;
            margin-top: 30px;
        }
        .btn-print {
            background-color: #ffd200;
            color: #000;
            border: none;
            padding: 10px 25px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-print:hover {
            background-color: #e0a800;
        }
        @media print {
            .print-section {
                display: none;
            }
            body {
                padding: 0;
            }
            .brand-logo-container {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <!-- Top Header Banner matching reference design -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 30px; border-collapse: collapse; border: none;">
        <tr>
            <td style="vertical-align: middle; text-align: left; border: none; padding: 0;">
                <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0; background-color: #23282d; border-radius: 4px;">
                    <tr>
                        <td style="background-color: #23282d; padding: 10px 16px; border-radius: 4px; border: none; text-align: center; vertical-align: middle;">
                            <img src="<?php echo $logo_image_src; ?>" width="135" style="display: block; max-width: 100%; height: auto; border: 0;" alt="Yellow Markets">
                        </td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: middle; text-align: right; border: none; padding: 0;">
                <div class="invoice-heading">INVOICE</div>
            </td>
        </tr>
    </table>

    <!-- Details Section Header -->
    <div class="section-title">Add-on Details</div>

    <!-- Meta Details Grid -->
    <table class="details-table">
        <tr>
            <td class="col-label">Merchant</td>
            <td class="col-val">: <?php echo isset($addon_purchase->merchant_name) ? htmlspecialchars($addon_purchase->merchant_name) : 'N/A'; ?></td>
            <td class="col-label">Invoice ID</td>
            <td class="col-val">: <?php echo $addon_purchase->purchase_id; ?></td>
        </tr>
        <tr>
            <td class="col-label">Email</td>
            <td class="col-val">: <?php echo isset($addon_purchase->merchant_email) ? htmlspecialchars($addon_purchase->merchant_email) : 'N/A'; ?></td>
            <td class="col-label">Date</td>
            <td class="col-val">: <?php echo $addon_purchase->date; ?></td>
        </tr>
        <tr>
            <td class="col-label"></td>
            <td class="col-val"></td>
            <!-- <td class="col-label">Status</td>
            <td class="col-val">: <span style="text-transform: capitalize; color: #28a745; font-weight: bold;"><?php echo $addon_purchase->status; ?></span></td> -->
        </tr>
    </table>

    <!-- Table Section Header -->
    <div class="section-title">Add-on Information</div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr style="background-color: #ffd200;">
                <th style="background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 12px; font-weight: bold; font-size: 12px; text-align: center;">ADD-ON SERVICE</th>
                <th style="background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 12px; font-weight: bold; font-size: 12px; text-align: center;">ADD-ON NAME</th>
                <th style="background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 12px; font-weight: bold; font-size: 12px; text-align: center;">AMOUNT</th>
                <th style="background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 12px; font-weight: bold; font-size: 12px; text-align: center;">PAYMENT METHOD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo isset($addon_purchase->addon_service_name) ? htmlspecialchars($addon_purchase->addon_service_name) : ''; ?></td>
                <td><?php echo isset($addon_purchase->addon_name) ? htmlspecialchars($addon_purchase->addon_name) : ''; ?></td>
                <td>MUR <?php echo number_format($addon_purchase->amount, 2); ?></td>
                <td>My.T Money</td>
            </tr>
        </tbody>
    </table>

    <!-- Print Button -->
<?php if (empty($is_pdf)): ?>
    <div class="print-section">
        <button onclick="window.print();" class="btn-print">🖨 Print / Download Invoice</button>
    </div>
<?php endif; ?>
</div>

</body>
</html>
