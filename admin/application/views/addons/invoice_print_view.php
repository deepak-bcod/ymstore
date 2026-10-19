<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - #<?php echo $addon_purchase->purchase_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        .invoice-title {
            font-size: 20px;
            color: #777;
            text-transform: uppercase;
        }
        .details-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8px;
            vertical-align: top;
        }
        .col-label {
            font-weight: bold;
            width: 150px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            background: #ffc107;
            color: #000;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .items-table th, .items-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f1f1f1;
        }
        .text-right {
            text-align: right;
        }
        .btn-print {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 10px;
            text-align: center;
            background: #ffc107;
            color: #000;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        @media print {
            .btn-print {
                display: none;
            }
            body {
                background-color: #fff;
                padding: 0;
            }
            .invoice-box {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <div class="logo-title">Yellow Markets</div>
        <div class="invoice-title">Invoice</div>
    </div>

    <table class="details-table">
        <tr>
            <td class="col-label">Merchant</td>
            <td>: <?php echo html_escape($addon_purchase->merchant_name ?? 'N/A'); ?></td>
            <td class="col-label">Invoice ID</td>
            <td>: #<?php echo html_escape($addon_purchase->purchase_id); ?></td>
        </tr>
        <tr>
            <td class="col-label">Email</td>
            <td>: <?php echo html_escape($addon_purchase->merchant_email ?? 'N/A'); ?></td>
            <td class="col-label">Date</td>
            <td>: <?php echo html_escape($addon_purchase->date); ?></td>
        </tr>
    </table>

    <div class="section-title">Add-on Information</div>

    <table class="items-table">
        <thead>
            <tr>
                <th>ADD-ON SERVICE</th>
                <th>ADD-ON NAME</th>
                <th>PRICE</th>
                <th>VAT (%)</th>
                <th class="text-right">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo html_escape($addon_purchase->addon_service_name); ?></td>
                <td><?php echo html_escape($addon_purchase->addon_name); ?></td>
                <td><?php echo number_format($addon_purchase->price ?? 0, 2); ?></td>
                <td><?php echo number_format($addon_purchase->vat_percent ?? 0, 2); ?>%</td>
                <td class="text-right">MUR <?php echo number_format($addon_purchase->amount ?? 0, 2); ?></td>
            </tr>
        </tbody>
    </table>

    <button onclick="window.print();" class="btn-print">Print / Download Invoice</button>
</div>

</body>
</html>