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
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
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
            padding: 10px 20px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 0.5px;
        }
        .invoice-heading {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #222;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 8px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 14px;
        }
        .details-table td.col-label {
            color: #555;
            width: 120px;
        }
        .details-table td.col-val {
            font-weight: bold;
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
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #dee2e6;
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
            .invoice-box {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <!-- Top Header Banner -->
    <div class="header-section">
        <div class="brand-logo">🛒 Yellow Markets</div>
        <div class="invoice-heading">Add-on Invoice</div>
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
            <td class="col-val">: <span style="text-transform: capitalize; color: #28a745;"><?php echo $addon_purchase->status; ?></span></td>
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