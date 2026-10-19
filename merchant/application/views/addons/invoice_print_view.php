<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add-on Invoice #<?php echo $addon_purchase->purchase_id; ?></title>
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
        .brand-logo {
            background: #2b2b2b;
            color: #ffc107;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 15px;
            display: inline-block;
        }
        .invoice-heading {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            letter-spacing: 1px;
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
            background-color: #ffc107;
            color: #000;
            text-align: center;
            padding: 12px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #dee2e6;
            letter-spacing: 0.5px;
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
            background-color: #ffc107;
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
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <!-- Top Header Banner matching reference design -->
    <div class="header-section">
        <div class="brand-logo">🛒 Yellow Markets</div>
        <div class="invoice-heading">INVOICE</div>
    </div>

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
            <td class="col-label">Status</td>
            <td class="col-val">: <span style="text-transform: capitalize; color: #28a745; font-weight: bold;"><?php echo $addon_purchase->status; ?></span></td>
        </tr>
    </table>

    <!-- Table Section Header -->
    <div class="section-title">Add-on Information</div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th>ADD-ON SERVICE</th>
                <th>ADD-ON NAME</th>
                <th>AMOUNT</th>
                <th>PAYMENT METHOD</th>
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
    <div class="print-section">
        <button onclick="window.print();" class="btn-print">🖨 Print / Download Invoice</button>
    </div>
</div>

</body>
</html>