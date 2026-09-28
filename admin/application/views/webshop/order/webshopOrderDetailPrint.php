<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Safety fallbacks for constants & functions
if (!defined('CURRENCY_TYPE')) {
	define('CURRENCY_TYPE', (!empty($currency_code) ? $currency_code : 'MUR') . ' ');
}
$curr = !empty($currency_code) ? $currency_code : 'MUR';

if (!function_exists('lang')) {
	function lang($line, $for = '', $attributes = array())
	{
		$CI = &get_instance();
		if (isset($CI->lang) && method_exists($CI->lang, 'line')) {
			$trans = $CI->lang->line($line, FALSE);
			if (!empty($trans)) {
				return $trans;
			}
		}
		$labels = [
			'receipt' => 'RECEIPT',
			'order_details' => 'Order Details',
			'bill_to' => 'Bill To',
			'ship_to' => 'Ship To',
			'order_id' => 'Order ID',
			'ordered_on' => 'Ordered On',
			'payment_mode' => 'Payment Mode',
			'products' => 'Products',
			'description' => 'DESCRIPTION',
			'qty' => 'QTY',
			'unit_price' => 'UNIT PRICE',
			'amount' => 'AMOUNT',
			'price' => 'Price',
			'items' => 'items',
			'inclusive_of_taxes' => 'Inclusive of taxes',
			'taxes' => 'VAT',
			'discount_amount' => 'Discount Amount',
			'shipping_charges' => 'Shipping Charges',
			'sub_total' => 'Sub Total',
			'order_total' => 'ORDER TOTAL',
			'print' => 'PRINT'
		];
		if (isset($labels[$line])) {
			return $labels[$line];
		}
		return ucwords(str_replace('_', ' ', $line));
	}
}

if (!function_exists('get_display_product_name')) {
	function get_display_product_name($item)
	{
		if (is_object($item)) {
			return $item->product_name ?? $item->name ?? $item->title ?? '-';
		} elseif (is_array($item)) {
			return $item['product_name'] ?? $item['name'] ?? $item['title'] ?? '-';
		}
		return '-';
	}
}

// Ensure items array
$items_list = [];
if (!empty($OrderItems)) {
	$items_list = $OrderItems;
} elseif (!empty($OrderData->order_items)) {
	$items_list = $OrderData->order_items;
} elseif (!empty($order_items)) {
	$items_list = $order_items;
}

// Logo URL
$logo_url = 'https://mu.yellowmarkets.com/uploads/yellow-markets-logo.png';
if (defined('SITE_LOGO') && !empty(SITE_LOGO)) {
	$logo_url = SITE_LOGO;
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Order Details Receipt">
	<title>Yellow Market Receipt - <?php echo htmlspecialchars($OrderData->increment_id ?? ''); ?></title>

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

	<style>
		body {
			margin: 0;
			padding: 0;
			font-family: 'Montserrat', sans-serif;
			font-size: 14px;
			background: #f5f5f5;
			color: #333333;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}

		.receipt-wrapper {
			max-width: 900px;
			margin: 30px auto;
			background: #ffffff;
			border-radius: 4px;
			box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
			padding: 40px 45px;
		}

		.top-toolbar {
			max-width: 900px;
			margin: 20px auto 0;
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 0 5px;
		}

		.btn-top-print {
			background: #f1da0dfb;
			color: #000000;
			font-weight: 700;
			border: none;
			padding: 10px 22px;
			border-radius: 4px;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			cursor: pointer;
			font-size: 14px;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
			transition: all 0.2s ease;
		}

		.btn-top-print:hover {
			background: #d97706;
			color: #ffffff;
		}

		.btn-top-close {
			background: #64748b;
			color: #ffffff;
			font-weight: 600;
			border: none;
			padding: 10px 18px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.btn-top-close:hover {
			background: #475569;
			color: #ffffff;
			text-decoration: none;
		}

		.receipt-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 35px;
		}

		.logo-print {
			background-color: #333333;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 10px 14px;
		}

		.logo-print img {
			width: 164px;
			max-width: 164px;
			height: auto;
			display: block;
		}

		.receipt-title-badge {
			font-size: 18px;
			font-weight: 700;
			color: #333333;
			letter-spacing: 0.05em;
			text-transform: uppercase;
		}

		.section-heading {
			font-weight: 700;
			font-size: 18px;
			line-height: 22px;
			letter-spacing: 0.04em;
			text-transform: capitalize;
			color: #333333;
			margin-bottom: 20px;
			margin-top: 10px;
		}

		.details-grid {
			display: flex;
			justify-content: space-between;
			gap: 25px;
			margin-bottom: 40px;
		}

		.details-col {
			flex: 1;
			font-size: 14px;
			line-height: 1.6;
			color: #333333;
		}

		.details-col-title {
			font-weight: 700;
			font-size: 14px;
			margin-bottom: 8px;
			color: #212529;
		}

		.details-col p {
			margin-bottom: 6px;
			line-height: 1.5;
		}

		.meta-field {
			display: flex;
			margin-bottom: 6px;
			line-height: 1.5;
		}

		.meta-field-label {
			width: 130px;
			font-weight: 600;
			flex-shrink: 0;
			color: #333333;
		}

		.meta-field-val {
			font-weight: 500;
			color: #212529;
			word-break: break-word;
		}

		.merchant-highlight {
			color: #d97706;
			font-weight: 700;
		}

		table.table-style {
			font-family: 'Montserrat', sans-serif;
			font-size: 14px;
			margin: 10px auto 30px;
			text-align: left;
			padding: 0;
			border: 1px solid #dee2e6;
			border-collapse: collapse;
			width: 100%;
		}

		table.table-style th {
			border: 1px solid #dee2e6;
			padding: 12px 14px;
			vertical-align: middle;
			font-weight: 700;
			text-transform: uppercase;
			font-size: 14px;
			color: #000000;
			background-color: #f1d50dfb;
			letter-spacing: 0.03em;
		}

		table.table-style td {
			border: 1px solid #dee2e6;
			padding: 12px 14px;
			vertical-align: middle;
			font-weight: 500;
			color: #212529;
			font-size: 14px;
		}

		.item-merchant-tag {
			display: inline-block;
			margin-top: 4px;
			color: #4f46e5;
			font-size: 12px;
			font-weight: 600;
		}

		.summary-row td {
			padding: 9px 14px;
			font-size: 14px;
		}

		.summary-label {
			text-align: right;
			font-weight: 500;
			color: #212529;
		}

		.summary-val {
			text-align: center;
			font-weight: 500;
			color: #212529;
			white-space: nowrap;
		}

		.grand-total-row td {
			font-weight: 700 !important;
			color: #000000 !important;
			font-size: 15px !important;
			background-color: #fafafa;
		}

		.btn-bottom-print {
			background-color: #212529;
			border-color: #212529;
			color: #ffffff;
			font-weight: 700;
			padding: 10px 30px;
			font-size: 14px;
			border-radius: 4px;
			cursor: pointer;
			transition: all 0.2s ease;
		}

		.btn-bottom-print:hover {
			background-color: #000000;
			color: #ffffff;
		}

		@media print {
			#noprint, .no-print {
				display: none !important;
			}
			body {
				background: #ffffff !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			.receipt-wrapper {
				max-width: 100% !important;
				margin: 0 !important;
				padding: 15px 10px !important;
				box-shadow: none !important;
				border-radius: 0 !important;
			}
			table.table-style th {
				background-color: #f1da0dfb !important;
				color: #000000 !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			table {
				page-break-inside: auto;
			}
			tr {
				page-break-inside: avoid;
				page-break-after: auto;
			}
		}
	</style>
</head>

<body>

	<!-- Top Action Bar (hidden on print) -->
	<div class="top-toolbar" id="noprint">
		<div>
			<a href="javascript:window.close();" class="btn-top-close">
				<i class="fas fa-arrow-left"></i> Close
			</a>
		</div>
		<div>
			<button type="button" class="btn-top-print" onclick="window.print();">
				<i class="fas fa-print"></i> Print / Save as PDF
			</button>
		</div>
	</div>

	<!-- Receipt Card Container -->
	<div class="receipt-wrapper">

		<!-- Header -->
		<div class="receipt-header">
			<div class="logo-print">
				<img src="<?php echo htmlspecialchars($logo_url); ?>" 
					alt="Yellow Markets"
					onerror="this.src='https://mu.yellowmarkets.com/uploads/yellow-markets-logo.png';">
			</div>
			<div class="receipt-title-badge">
				<strong><?= lang('receipt') ?></strong>
			</div>
		</div>

		<!-- Order Details Title -->
		<div class="section-heading">
			<?= lang('order_details') ?>
		</div>

		<!-- Three-Column Order Information -->
		<div class="details-grid">
			<!-- Bill To -->
			<div class="details-col">
				<div class="details-col-title"><?= lang('bill_to') ?></div>
				<div>
					<?php echo !empty($billing_address) ? $billing_address : '<strong>' . htmlspecialchars(trim(($OrderData->customer_firstname ?? '') . ' ' . ($OrderData->customer_lastname ?? ''))) . '</strong>'; ?>
				</div>
			</div>

			<!-- Ship To -->
			<div class="details-col">
				<div class="details-col-title"><?= lang('ship_to') ?></div>
				<div>
					<?php echo !empty($shipping_address) ? $shipping_address : (!empty($billing_address) ? $billing_address : '<strong>' . htmlspecialchars(trim(($OrderData->customer_firstname ?? '') . ' ' . ($OrderData->customer_lastname ?? ''))) . '</strong>'); ?>
				</div>
			</div>

			<!-- Order Meta -->
			<div class="details-col">
				<div class="meta-field">
					<span class="meta-field-label"><?= lang('order_id') ?></span>
					<span class="meta-field-val">: <?php echo htmlspecialchars($OrderData->increment_id ?? ''); ?></span>
				</div>
				<div class="meta-field">
					<span class="meta-field-label"><?= lang('ordered_on') ?></span>
					<span class="meta-field-val">: <?php echo !empty($OrderData->created_at) ? date('d-M-Y', $OrderData->created_at) : date('d-M-Y'); ?></span>
				</div>
				<div class="meta-field">
					<span class="meta-field-label"><?= lang('payment_mode') ?></span>
					<span class="meta-field-val">: <?php echo htmlspecialchars($actual_payment_method ?? ($payment_method ?? 'Online')); ?></span>
				</div>
				<div class="meta-field">
					<span class="meta-field-label">Shipment Type</span>
					<span class="meta-field-val">: <?php echo htmlspecialchars($shipment_display ?? 'Own Delivery'); ?></span>
				</div>
				<div class="meta-field">
					<span class="meta-field-label">Merchant Name</span>
					<span class="meta-field-val">: <strong class="merchant-highlight"><?php echo htmlspecialchars($merchant_names ?? ($merchant_name ?? 'Yellow Markets')); ?></strong></span>
				</div>
			</div>
		</div>

		<!-- Products Title -->
		<div class="section-heading">
			<?= lang('products') ?>
		</div>

		<!-- Products Table -->
		<table class="table-style">
			<thead>
				<tr>
					<th style="text-align: left;"><?= lang('description') ?></th>
					<th style="text-align: center; width: 70px;"><?= lang('qty') ?></th>
					<th style="text-align: right; width: 140px;"><?= lang('unit_price') ?></th>
					<th style="text-align: right; width: 140px;"><?= lang('amount') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$CI = &get_instance();
				if (!empty($items_list) && count($items_list) > 0) {
					foreach ($items_list as $value) {
						$prod_name = get_display_product_name($value);
						$qty_val = (float)(is_object($value) ? ($value->qty_ordered ?? $value->qty ?? 1) : ($value['qty_ordered'] ?? $value['qty'] ?? 1));
						$price_val = (float)(is_object($value) ? ($value->price ?? 0) : ($value['price'] ?? 0));
						$amount_val = (float)(is_object($value) ? ($value->total_price ?? ($qty_val * $price_val)) : ($value['total_price'] ?? ($qty_val * $price_val)));

						// Parse variants
						$variant_raw = is_object($value) ? ($value->product_variants ?? '') : ($value['product_variants'] ?? '');
						$variants_arr = [];
						if (!empty($variant_raw)) {
							$decoded = json_decode($variant_raw, true);
							if (is_array($decoded)) {
								foreach ($decoded as $single_variant) {
									if (is_array($single_variant)) {
										foreach ($single_variant as $key => $val) {
											$variants_arr[] = htmlspecialchars($key) . ' : ' . htmlspecialchars($val);
										}
									}
								}
							}
						}

						// Item Merchant Name
						$item_merchant = '';
						$pub_id = is_object($value) ? ($value->publisher_id ?? 0) : ($value['publisher_id'] ?? 0);
						$prod_id = is_object($value) ? ($value->product_id ?? 0) : ($value['product_id'] ?? 0);
						$parent_prod_id = is_object($value) ? ($value->parent_product_id ?? 0) : ($value['parent_product_id'] ?? 0);

						if (!$pub_id && !empty($prod_id) && isset($CI->ShopProductModel)) {
							$pRow = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $prod_id), 'publisher_id');
							if (!empty($pRow->publisher_id)) {
								$pub_id = $pRow->publisher_id;
							}
						}
						if (!$pub_id && !empty($parent_prod_id) && isset($CI->ShopProductModel)) {
							$pRow = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $parent_prod_id), 'publisher_id');
							if (!empty($pRow->publisher_id)) {
								$pub_id = $pRow->publisher_id;
							}
						}
						if ($pub_id > 0 && isset($CI->CommonModel)) {
							$item_merchant = $CI->CommonModel->getWebShopNameByShopId($pub_id);
						}
						if (empty($item_merchant) && !empty($merchant_names)) {
							$item_merchant = $merchant_names;
						}
				?>
						<tr>
							<td style="text-align: left;">
								<strong><?php echo htmlspecialchars($prod_name); ?></strong>
								<?php if (!empty($variants_arr)) { ?>
									<br><span style="color: #4b5563; font-size: 13px;"><?php echo implode(' , ', $variants_arr); ?></span>
								<?php } ?>
								<?php if (!empty($item_merchant)) { ?>
									<br><span class="item-merchant-tag"><i class="fas fa-store"></i> Merchant: <?php echo htmlspecialchars($item_merchant); ?></span>
								<?php } ?>
							</td>
							<td style="text-align: center; font-weight: 600;"><?php echo $qty_val; ?></td>
							<td style="text-align: right;"><?php echo $curr . ' ' . number_format($price_val, 2); ?></td>
							<td style="text-align: right; font-weight: 600;"><?php echo $curr . ' ' . number_format($amount_val, 2); ?></td>
						</tr>
					<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">No items found in this order</td>
					</tr>
				<?php } ?>

				<!-- Subtotal / Base Price -->
				<?php
				$base_sub_show = isset($base_subtotal) ? (float)$base_subtotal : (isset($OrderData->base_subtotal) ? (float)$OrderData->base_subtotal : (float)$subtotal);
				$total_qty_show = isset($total_qty_ordered) ? $total_qty_ordered : (isset($OrderData->total_qty_ordered) ? $OrderData->total_qty_ordered : (count($items_list) ?: 1));
				?>
				<tr class="summary-row">
					<td colspan="3" class="summary-label">
						<?= lang('price') ?> (<?php echo $total_qty_show; ?> <?= lang('items') ?>) (<?= lang('inclusive_of_taxes') ?>)
					</td>
					<td class="summary-val">
						<?php echo $curr . ' ' . number_format($base_sub_show, 2); ?>
					</td>
				</tr>

				<!-- Taxes / VAT -->
				<?php $tax_val = isset($vat_amount) ? (float)$vat_amount : (float)($OrderData->tax_amount ?? 0); ?>
				<tr class="summary-row">
					<td colspan="3" class="summary-label">
						<?= lang('taxes') ?>
					</td>
					<td class="summary-val">
						<?php echo $curr . ' ' . number_format($tax_val, 2); ?>
					</td>
				</tr>

				<!-- Discount -->
				<?php
				$disc_val = isset($discount_amount) ? (float)$discount_amount : (float)($OrderData->discount_amount ?? 0);
				$disc_pct = isset($discount_percent) ? (float)$discount_percent : (float)($OrderData->discount_percent ?? 0);
				if ($disc_val > 0) {
				?>
					<tr class="summary-row">
						<td colspan="3" class="summary-label">
							<?= lang('discount_amount') ?> (<?php echo $disc_pct; ?>%) -
						</td>
						<td class="summary-val" style="color: #dc2626;">
							- <?php echo $curr . ' ' . number_format($disc_val, 2); ?>
						</td>
					</tr>
				<?php } ?>

				<!-- Shipping Charges -->
				<?php
				$ship_val = isset($shipping_amount) ? (float)$shipping_amount : (float)($OrderData->shipping_amount ?? ($OrderData->ym_charge ?? 0));
				?>
				<tr class="summary-row">
					<td colspan="3" class="summary-label">
						<?= lang('shipping_charges') ?> +
					</td>
					<td class="summary-val">
						<?php echo $curr . ' ' . number_format($ship_val, 2); ?>
					</td>
				</tr>

				<!-- Payment Gateway Charges -->
				<?php
				$gateway_val = isset($payment_gateway_charges) ? (float)$payment_gateway_charges : (float)($OrderData->payment_gateway_charges ?? 0);
				if ($gateway_val > 0) {
				?>
					<tr class="summary-row">
						<td colspan="3" class="summary-label">
							Payment Gateway Charges +
						</td>
						<td class="summary-val">
							<?php echo $curr . ' ' . number_format($gateway_val, 2); ?>
						</td>
					</tr>
				<?php } ?>

				<!-- Sub Total -->
				<tr class="summary-row">
					<td colspan="3" class="summary-label">
						<?= lang('sub_total') ?>
					</td>
					<td class="summary-val">
						<?php echo $curr . ' ' . number_format($subtotal, 2); ?>
					</td>
				</tr>

				<!-- Processing Fees / Commission (if applicable) -->
				<?php if (isset($publisher_commision_per) && $publisher_commision_per > 0) { ?>
					<tr class="summary-row">
						<td colspan="3" class="summary-label">
							YM Processing Fees (<?php echo number_format($publisher_commision_per, 2); ?>%)
						</td>
						<td class="summary-val">
							<?php echo $curr . ' ' . number_format($processing_amount, 2); ?>
						</td>
					</tr>
				<?php } ?>

				<!-- ORDER TOTAL -->
				<?php
				$grand_val = isset($grand_total) ? (float)$grand_total : (float)($OrderData->grand_total ?? ($subtotal + $ship_val - $disc_val));
				?>
				<tr class="grand-total-row">
					<td colspan="3" class="summary-label">
						<strong><?= lang('order_total') ?></strong>
					</td>
					<td class="summary-val">
						<strong><?php echo $curr . ' ' . number_format($grand_val, 2); ?></strong>
					</td>
				</tr>

				<!-- Net Payable Amount (if applicable) -->
				<?php if (isset($payable_amount) && $payable_amount != $grand_val) { ?>
					<!--tr class="summary-row" style="background-color: #f0fdf4;">
						<td colspan="3" class="summary-label" style="font-weight: 700; color: #166534;">
							YM Net Payable Amount
						</td>
						<td class="summary-val" style="font-weight: 700; color: #166534;">
							<?php echo $curr . ' ' . number_format($payable_amount, 2); ?>
						</td>
					</tr-->
				<?php } ?>
			</tbody>
		</table>

		<!-- Bottom Print Button (Matching user screenshot) -->
		<!--div id="noprint" class="text-right" style="margin-top: 25px;">
			<button class="btn btn-bottom-print" onclick="window.print();">
				<strong><?= lang('print') ?></strong>
			</button>
		</div-->
	</div>

	<!-- Auto-trigger print dialog on page load -->
	<script type="text/javascript">
		window.addEventListener('load', function() {
			setTimeout(function() {
				window.print();
			}, 500);
		});
	</script>
</body>

</html>