<?php
// Get CI instance
$CI =& get_instance();

// Load models (only if not autoloaded already)
$CI->load->model('ShopProductModel');
$CI->load->model('CommonModel');
$CI->load->model('B2BOrdersModel');
$CI->load->model('WebshopOrdersModel');
// echo "<pre>";
// print_r($OrderData);die;

// Determine if this is an Eshop order
$is_eshop = ($this->uri->segment(2) == 'b2b' || (isset($current_tab) && ($current_tab == 'b2b-orders' || $current_tab == 'b2b-order')) || (isset($OrderData->webshop_order_id) && $OrderData->webshop_order_id > 0) || !empty($is_eshop_order));
$order_type_label = $is_eshop ? 'ES' : 'YM';

// Resolve sales order id
$effective_order_id = 0;
if (!empty($OrderData->order_id)) {
	$effective_order_id = $OrderData->order_id;
}
if (!empty($OrderData->webshop_order_id)) {
	$effective_order_id = $OrderData->webshop_order_id;
}

// Retrieve Shipping Address with robust fallbacks
if (empty($ShippingAddress) || empty($ShippingAddress->address_id)) {
	if ($effective_order_id > 0) {
		$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $effective_order_id, 'address_type' => 2), '');
		if (empty($ShippingAddress) || empty($ShippingAddress->address_id)) {
			$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $effective_order_id, 'address_type' => 1), '');
		}
	}
	if ((empty($ShippingAddress) || empty($ShippingAddress->address_id)) && !empty($OrderData->parent_id)) {
		$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->parent_id, 'address_type' => 2), '');
		if (empty($ShippingAddress) || empty($ShippingAddress->address_id)) {
			$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->parent_id, 'address_type' => 1), '');
		}
	}
	if ((empty($ShippingAddress) || empty($ShippingAddress->address_id)) && !empty($OrderData->main_parent_id)) {
		$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 2), '');
		if (empty($ShippingAddress) || empty($ShippingAddress->address_id)) {
			$ShippingAddress = $CI->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 1), '');
		}
	}
}
?>
<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<?php
		$sales_order = $CI->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $effective_order_id), '');
		if (empty($order_payment_method)) {
			if ($effective_order_id > 0) {
				$order_payment_method = $CI->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $effective_order_id), '');
			}
			if (empty($order_payment_method) && !empty($OrderData->main_parent_id)) {
				$order_payment_method = $CI->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->main_parent_id), '');
			}
			if (empty($order_payment_method) && !empty($OrderData->parent_id)) {
				$order_payment_method = $CI->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->parent_id), '');
			}
		}
		?>
		<p><span>Order Number :</span> <?php echo $OrderData->increment_id; ?></p>
		<?php
		if ($OrderData->main_parent_id > 0 || $OrderData->parent_id > 0) {
			$purchaseOn = $CI->CommonModel->getSingleShopDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id), 'created_at');
			if (isset($purchaseOn) && $purchaseOn->created_at) {
				$purchaseOnDate = date('d/m/Y', $purchaseOn->created_at) . ' | ' . date('h:i A', $purchaseOn->created_at);
			} else {
				$purchaseOnDate = date('d/m/Y', $OrderData->created_at) . ' | ' . date('h:i A', $OrderData->created_at);
			}
		} else {
			$purchaseOnDate = date('d/m/Y', $OrderData->created_at) . ' | ' . date('h:i A', $OrderData->created_at);
		}
		?>
		<p><span>Purchased on :</span> <?php echo $purchaseOnDate; ?></p>
		<p><span>Order Status :</span> 
		
			<?php echo $CI->CommonModel->getOrderStatusLabel($OrderData->status); ?>
				
		</p>
		
			<p><span>Shipping Address :</span> 	<span class="order-address-inner"><?php

		if (isset($ShippingAddress) && !empty($ShippingAddress->address_id)) {
			$shipName = trim(($ShippingAddress->first_name ?? '') . ' ' . ($ShippingAddress->last_name ?? ''));
			$shipMobile = $ShippingAddress->mobile_no ?? '';
			if ($shipName) {
				echo htmlspecialchars($shipName) . '<br/>';
			}
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
			if ($shipMobile) {
				echo '<br/>Mob: ' . htmlspecialchars($shipMobile);
			}
			if (!empty($ShippingAddress->company_name)) {
				echo '<br/>Comp Name: ' . htmlspecialchars($ShippingAddress->company_name);
			}
		} else {
			echo '-';
		}
		?></span></p>
		
	
	</div>
	<div class="col-sm-6 order-id">
		<p><span class="huge-name">Customer Name :</span> <?php echo $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname // $CI->B2BOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);
			?> </p>
		<?php
	
		$B2b_items = $CI->ShopProductModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
		$getCategory = $CI->B2BOrdersModel->getCategory($B2b_items->parent_product_id);
		$product_data = [];
		$new_product_data=[];

		if (isset($OrderItems) && count($OrderItems) > 0) {
			foreach ($OrderItems as $item) {
				$product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->parent_product_id), '');
				$new_product_details = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $item->product_id), '');
				$product_data[] = $product_details;
				$new_product_data[] = $new_product_details;

			}
		}
		// echo "<pre>";
		// print_r($OrderItems);die;

		foreach ($product_data as $keyprod => $valprod) {
			$product_name = $valprod->name ?? null;
		}
		$total_webshop_price = 0;

		foreach ($new_product_data as $keyprod => $valprod) {
			if (isset($valprod->webshop_price)) {
				$total_webshop_price += (float)$valprod->webshop_price;
			}
		}
		// print_r($webshop_price);die;
		
		$publisherdetails = $CI->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
		$publication_name = '';
		
		$publication_name = $PublisherDetails->publication_name ?? null;
		// if ($publication_name === 'Amar Chitra Katha (Books)') {
		// 	$order_total=$total_webshop_price;
		// } elseif($getCategory){
		// 	$order_total=$total_webshop_price;
		// }else{
		// 	$order_total=$OrderData->subtotal;
		// }
		// // print_r($order_total);die;
		// if($OrderData->order_id == '1895' ||  $OrderData->order_id == '1732' || $OrderData->order_id == '1739' || $OrderData->order_id == '1737' || $OrderData->order_id == '1735' || $OrderData->order_id == '1745' || $OrderData->order_id == '1682' || $OrderData->order_id == '1979' || $OrderData->order_id == '1999' || $OrderData->order_id == '2007' || $OrderData->order_id == '2008' || $OrderData->order_id == '1805' || $OrderData->order_id == '1777' || $OrderData->order_id == '1780' || $OrderData->order_id == '1794'){ 
		// 	$order_total=$OrderData->subtotal + ($OrderData->shipping_amount * $OrderData->total_qty_ordered);
		// }
		// if($OrderData->order_id == '3137'){
		// 	}
		$smallBoxChargeRow = $CI->ShopProductModel->getSingleDataByID(
			'custom_variables',
			array('identifier' => 'ym_small_box_charge'),
			''
		);

		$mediumBoxChargeRow = $CI->ShopProductModel->getSingleDataByID(
			'custom_variables',
			array('identifier' => 'ym_medium_box_charge'),
			''
		);

		$smallBoxCharge  = !empty($smallBoxChargeRow->value) ? (float)$smallBoxChargeRow->value : 0;
		$mediumBoxCharge = !empty($mediumBoxChargeRow->value) ? (float)$mediumBoxChargeRow->value : 0;

		$ymCharges = 0;

		if (!empty($OrderItems)) {

			foreach ($OrderItems as $item) {

				// ✅ IMPORTANT: handle configurable products
				if ($item->product_type == 'conf-simple' && !empty($item->parent_product_id)) {
					$productId = $item->parent_product_id;
				} else {
					$productId = $item->product_id;
				}

				$product = $CI->ShopProductModel->getSingleDataByID(
					'products',
					array('id' => $productId),
					''
				);

				if (!empty($product)) {

					$boxType = (int)($product->ym_shipping_charges_type ?? 0);

					if ($boxType === 1) {
						$ymCharges += $smallBoxCharge * $item->qty_ordered;
					} 
					elseif ($boxType === 2) {
						$ymCharges += $mediumBoxCharge * $item->qty_ordered;
					}
				}
			}
		}
		// echo "<pre>";
		// print_R($ymCharges);
		// die();
		$order_total = $OrderData->subtotal + $ymCharges;

		// if ($product_name === 'Business Manager Magazine' || $product_name === 'Business Manager Magazine Digital') {
		// 	// echo "hi2";
		// 	$order_total=$OrderData->subtotal - $OrderData->shipping_amount ;
		// 	// print_r($whuso_income);
		// }
		// print_r($ymCharges);
		// die;
		$order_merchants = [];
		if (!empty($OrderData->publisher_id)) {
			$pname = $CI->CommonModel->getWebShopNameByShopId($OrderData->publisher_id);
			if (!empty($pname)) {
				$order_merchants[$OrderData->publisher_id] = [
					'id' => $OrderData->publisher_id,
					'name' => $pname,
					'url' => base_url() . "PublisherController/editPublisher/" . $OrderData->publisher_id
				];
			}
		}
		if (!empty($OrderItems)) {
			foreach ($OrderItems as $it) {
				$pId = !empty($it->publisher_id) ? $it->publisher_id : 0;
				if (!$pId && !empty($it->product_id)) {
					$pRow = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $it->product_id), 'publisher_id');
					if (!empty($pRow->publisher_id)) {
						$pId = $pRow->publisher_id;
					} elseif (!empty($it->parent_product_id)) {
						$pRow = $CI->ShopProductModel->getSingleDataByID('products', array('id' => $it->parent_product_id), 'publisher_id');
						if (!empty($pRow->publisher_id)) {
							$pId = $pRow->publisher_id;
						}
					}
				}
				if ($pId && !isset($order_merchants[$pId])) {
					$pname = $CI->CommonModel->getWebShopNameByShopId($pId);
					if (!empty($pname)) {
						$order_merchants[$pId] = [
							'id' => $pId,
							'name' => $pname,
							'url' => base_url() . "PublisherController/editPublisher/" . $pId
						];
					}
				}
			}
		}
		$merchant_links = [];
		foreach ($order_merchants as $m) {
			$merchant_links[] = '<a href="' . $m['url'] . '" target="_blank">' . htmlspecialchars($m['name']) . '</a>';
		}
		$merchant_display_html = !empty($merchant_links) ? implode(', ', $merchant_links) : '-';

		$shipment_display = '';
		if (!empty($OrderData->ship_method_name)) {
			$shipment_display = $OrderData->ship_method_name;
		} elseif (!empty($OrderData->ship_method_id)) {
			$sm = $CI->WebshopOrdersModel->getSingleDataByID('shipping_methods', array('id' => $OrderData->ship_method_id), 'ship_method_name');
			if (!empty($sm->ship_method_name)) {
				$shipment_display = $sm->ship_method_name;
			}
		}
		if (empty($shipment_display) && isset($OrderData->shipment_type)) {
			$shipment_display = $CI->CommonModel->getOrderShipmentLabel($OrderData->shipment_type);
		}
		if (empty($shipment_display)) {
			$shipment_display = 'Own Delivery';
		}
		?>
		<p><span class="huge-name">Merchant Name :</span> <?= $merchant_display_html; ?> </p>
		<p><span class="huge-name">Shipment :</span> <?php echo htmlspecialchars($shipment_display); ?> </p>
	</div>

	<div class="col-sm-4 order-id order-b2b-info-span">
		<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
			<p><span><?php echo $order_type_label; ?> order total :</span> <?php echo ' ' . number_format($ParentOrder->subtotal, 2); ?> </p>
			<p><span> Discount (<?php echo $ParentOrder->discount_percent; ?>%) :</span> <?php echo ' ' . number_format($ParentOrder->discount_amount, 2); ?></p>
			<p><span>VAT :</span> <?php echo ' ' . number_format($ParentOrder->tax_amount, 2); ?></p>
			<p><span><?php echo $order_type_label; ?> Net Payable Amount :</span> <?php echo ' ' . number_format($ParentOrder->grand_total, 2); ?></p>
		<?php } else { ?>
			<?php
			$discount_amount = isset($OrderData->discount_amount) ? (float)$OrderData->discount_amount : 0;
			$vat_amount = isset($sales_order->tax_amount) ? (float)$sales_order->tax_amount : 0;
			$shipping_charge = isset($OrderData->shipping_amount) ? (float)$OrderData->shipping_amount : 0;
			$gateway_charges = isset($OrderData->payment_gateway_charges) ? (float)$OrderData->payment_gateway_charges : 0;

			$publisher_commision_per = 0;
			if (!empty($OrderData->publisher_id)) {
				$publisher_commision_per = (float)$CI->CommonModel->getWebShopCommisionByShopId($OrderData->publisher_id);
			} elseif (!empty($order_merchants)) {
				$first_m = reset($order_merchants);
				$publisher_commision_per = (float)$CI->CommonModel->getWebShopCommisionByShopId($first_m['id']);
			}
			if ($publisher_commision_per <= 0) {
				$publisher_commision_per = 4.0;
			}

			$processing_amount = round(($order_total * $publisher_commision_per) / 100, 2);
			$grand_total_calc = $order_total - $discount_amount + $vat_amount + $shipping_charge + $gateway_charges;
			$Payable_Amount = isset($sales_order->grand_total) ? (float)$sales_order->grand_total : 0;
			?>
			<p><span><?php echo $order_type_label; ?> order total :</span> <?php echo ' ' . number_format($order_total, 2); ?> </p>
			<p><span> Discount (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo ' ' . number_format($discount_amount, 2); ?></p>
			<p><span>VAT :</span> <?php echo ' ' . number_format($vat_amount, 2); ?></p>
			<p><span>Shipping Amount :</span> <?php echo ' ' . number_format($shipping_charge, 2); ?></p>
			<?php if (isset($OrderData->payment_gateway_charges)) { ?>
				<p><span> Payment gateway charges :</span> <?php echo ' ' . number_format($gateway_charges, 2); ?></p>
			<?php } ?>
			<p><span>YM Processing Fees :</span> <?php echo number_format($publisher_commision_per, 2) . '%'; ?></p>
			<p><span>Processing Amount :</span> <?php echo ' ' . number_format($processing_amount, 2); ?></p>
			<p><span><?php echo $order_type_label; ?> Net Payable Amount :</span> <?php echo ' ' . number_format($Payable_Amount, 2); ?></p>
		<?php } ?>
	</div>
	<div class="col-sm-4 order-id">
		<?php
		$actual_payment_method = '';
		$payment_mode_type = 'Online';
		if (!empty($order_payment_method)) {
			if (!empty($order_payment_method->payment_method_name)) {
				$actual_payment_method = $order_payment_method->payment_method_name;
			} elseif (!empty($order_payment_method->payment_method)) {
				$actual_payment_method = str_replace('_', ' ', $order_payment_method->payment_method);
			}
			if (isset($order_payment_method->payment_type)) {
				if ($order_payment_method->payment_type == 2 || strtolower($order_payment_method->payment_type) == 'offline') {
					$payment_mode_type = 'Offline';
				} elseif ($order_payment_method->payment_type == 1 || strtolower($order_payment_method->payment_type) == 'online') {
					$payment_mode_type = 'Online';
				} else {
					$payment_mode_type = ucfirst($order_payment_method->payment_type);
				}
			}
		}
		if (empty($actual_payment_method)) {
			$actual_payment_method = '-';
		}
		?>
		<p><span>Payment Mode :</span> <?php echo htmlspecialchars($actual_payment_method); ?> </p>
		<p><span>Customer Payment Mode :</span> <?php echo htmlspecialchars($actual_payment_method); ?> </p>
		<p class="position-relative"><textarea placeholder="Note" readonly class="form-control col-sm-10 "><?= isset($sales_order->internal_notes) ? $sales_order->internal_notes : '' ?></textarea>
	</div>
	<div class="form-group">
	

	<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
		<div class="col-sm-4 order-id">
			<p><span>Split order total </span> <?php echo ' ' . number_format($OrderData->subtotal, 2); ?> </p>
			<p><span> Discount (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo ' ' . number_format($OrderData->discount_amount, 2); ?></p>
			<p><span>VAT :</span> <?php echo ' ' . number_format($OrderData->tax_amount, 2); ?></p>
			<p><span><?php echo $order_type_label; ?> Net Payable Amount For Split Order :</span> <?php echo ' ' . number_format($OrderData->grand_total, 2); ?></p>
		</div>
	<?php } ?>
</div><!-- barcode-qty-box -->

