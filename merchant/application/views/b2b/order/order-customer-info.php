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
$ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->webshop_order_id, 'address_type' => 2), '');
?>
<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<?php
		$sales_order = $CI->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->webshop_order_id), '');
		$order_payment_method = $CI->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->webshop_order_id), '');
		?>
		<p><span><?=lang('order_number')?> :</span> <?php echo $OrderData->increment_id; ?></p>
		<?php
		if ($OrderData->main_parent_id > 0 || $OrderData->parent_id > 0) {
			$purchaseOn = $CI->CommonModel->getSingleShopDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id, 'order_id' => $OrderData->main_parent_id), 'created_at');
			if (isset($purchaseOn) && $purchaseOn->created_at) {
				$purchaseOnDate = date('d/m/Y', $purchaseOn->created_at) . ' | ' . date('h:i A', $purchaseOn->created_at);
			}
		} else {
			$purchaseOnDate = date('d/m/Y', $OrderData->created_at) . ' | ' . date('h:i A', $OrderData->created_at);
		}
		?>
		<p><span><?=lang('purchased_on')?> :</span> <?php echo $purchaseOnDate; ?></p>
		<p><span><?=lang('order_status')?> :</span> 
			<?php echo $CI->CommonModel->getOrderStatusLabel($OrderData->status); ?>
		</p>

		<p><span><?=lang('shipping_address')?> :</span> 	<span class="order-address-inner"><?php
		if (isset($ShippingAddress) && $ShippingAddress->address_id != '') {
			$shipName = $ShippingAddress->first_name . ' ' . $ShippingAddress->last_name;
			$shipMobile = $ShippingAddress->mobile_no;
			if ($shipName) {
				echo $shipName . '<br/>';
			}
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
			if ($shipMobile) {
				echo '<br/>'.lang('mobile').':';
				echo $shipMobile;
			}
		} else {
			echo '-';
		}
		if (isset($ShippingAddress) && $ShippingAddress->company_name != '') {
			echo '<br/>'.lang('company_name').':';
			echo $ShippingAddress->company_name;
		}
		?></span></p>
	</div>
	<div class="col-sm-6 order-id">
		<p><span class="huge-name"><?=lang('customer_name')?> :</span> <?php echo $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname; ?> </p>
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

		foreach ($product_data as $keyprod => $valprod) {
			$product_name = $valprod->name ?? null;
		}
		$total_webshop_price = 0;
		foreach ($new_product_data as $keyprod => $valprod) {
			if (isset($valprod->webshop_price)) {
				$total_webshop_price += (float)$valprod->webshop_price;
			}
		}
		
		$publisherdetails = $CI->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
		$publication_name = '';

		$publication_name = $PublisherDetails->publication_name ?? null;
		if ($publication_name === 'Amar Chitra Katha (Books)') {
			$order_total=$total_webshop_price;
		} elseif($getCategory){
			$order_total=$total_webshop_price;
		}else{
			$order_total=$OrderData->subtotal;
		}
// print_r($order_total);die;
		if($OrderData->order_id == '1895' ||  $OrderData->order_id == '1732' || $OrderData->order_id == '1739' || $OrderData->order_id == '1737' || $OrderData->order_id == '1735' || $OrderData->order_id == '1745' || $OrderData->order_id == '1682' || $OrderData->order_id == '1979' || $OrderData->order_id == '1999' || $OrderData->order_id == '2007' || $OrderData->order_id == '2008' || $OrderData->order_id == '1805' || $OrderData->order_id == '1777' || $OrderData->order_id == '1780' || $OrderData->order_id == '1794'){ 
			$order_total=$OrderData->subtotal + ($OrderData->shipping_amount * $OrderData->total_qty_ordered);
		}
		if($OrderData->order_id == '3137'){
			$order_total=$OrderData->subtotal;
		}

		if ($product_name === 'Business Manager Magazine' || $product_name === 'Business Manager Magazine Digital') {
			// echo "hi2";
			$order_total=$OrderData->subtotal - $OrderData->shipping_amount ;
			// print_r($whuso_income);
		}
		$webshopurl = '';
		if ($OrderData->publisher_id) {
			$encodedId = rtrim(strtr(base64_encode($OrderData->publisher_id), '+/', '-_'), '=');
			$webshopurl = base_url() . "PublisherController/editMerchant/" . $encodedId;
		}
		?>
		<p><span class="huge-name"><?=lang('merchant_name')?> :</span> <a href="<?= $webshopurl ?>" target="_blank"><?php echo $CI->CommonModel->getWebShopNameByShopId($OrderData->publisher_id); ?> </a> </p>
		<p><span class="huge-name"><?=lang('shipment')?> :</span> <?php echo $CI->CommonModel->getOrderShipmentLabel($OrderData->shipment_type); ?> </p>

		<form id="docForm" enctype="multipart/form-data">
			<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
		
			<div class="form-group">
				<label for="" style="font-weight: bold;">
					<?php echo lang('merchant_upload'); ?> 
					<span class="text-danger"></span>
				</label>
			</div>

			<div class="form-group form-setion-new">
				<input type="file" id="document_file" name="document_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
				<p><?=lang('file_upload_validation')?></p>
			</div>
			<button type="button" id="saveBtn" class="btn btn-primary"><?php echo lang('upload'); ?></button>
			<a href="javascript:void(0);" 
    id="downloadBtn"
    class="download-link" 
    data-id="<?php echo $b2b_orders->order_id; ?>" 
    data-error-msg="<?php echo lang('invoice_not_uploaded'); ?>"
    style="text-decoration: underline; font-weight: bold; color: #f0ad4e;">
    <?php echo lang('preview'); ?>
</a>


		</form>
	</div>
	
</div>


	<div class="col-6 order-id order-b2b-info-span">
		<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
	<p><span><?=lang('b2b_order_total')?> :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->subtotal, 2); ?> </p>
<p><span><?=lang('discount')?> (<?php echo $ParentOrder->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->discount_amount, 2); ?></p>
<p><span><?=lang('b2b_taxes_amount')?> :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->tax_amount, 2); ?></p>
<p><span><?=lang('b2b_net_payable')?> :</span> <?php echo $currency_code . ' ' . number_format($ParentOrder->grand_total, 2); ?></p>
<?php } else { ?>
<p><span><?=lang('b2b_order_total')?> :</span> <?php echo $currency_code . ' ' . number_format($order_total, 2); ?> </p>
<p><span><?=lang('discount')?> (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($OrderData->discount_amount, 2); ?></p>
<p><span><?=lang('b2b_taxes_amount')?> :</span> <?php echo $currency_code . ' ' . number_format($OrderData->tax_amount, 2); ?></p>
<?php

			// echo "<pre>";
			// print_r($OrderData);die;
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
			// $shipping_charge = 0;
			// echo "<pre>";
			// print_r($product_data);
			// die;

			$B2b_items = $CI->ShopProductModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
			$getCategory = $CI->B2BOrdersModel->getCategory($B2b_items->parent_product_id);
			
			// $getCategory = $CI->getCategory($item['parent_product_id']);
			// echo "<pre>";
			// print_r($getCategory);
			// die;
			

			
			foreach ($product_data as $keyprod => $valprod) {
				$product_name = $valprod->name ?? null;
			}
			$total_webshop_price = 0;
			foreach ($new_product_data as $keyprod => $valprod) {
				if (isset($valprod->webshop_price)) {
					$total_webshop_price += (float)$valprod->webshop_price;
				}
			}
			// $shipping_charge = $OrderData->shipping_amount;  // commented on 23-09-24
			// $shipping_charge = $OrderData->shipping_amount  * $B2b_items->qty_ordered; // written on 23-09-24
			// print_r($shipping_charge);
			// die;

			$shipping_charge = $OrderData->shipping_amount; // written on 03-10-24
			$pub_ids = $CI->CommonModel->getShopsForBTwoBOrders($OrderData->webshop_order_id);
			// print_r($pub_ids);
			// echo $OrderData->publisher_id;
			$publisher_commision_per = $CI->CommonModel->getWebShopCommisionByShopId($OrderData->publisher_id);
			//$publisher_commision_per;
			$publisher_commision_per = 4;
			 $total_grand_ = $OrderData->grand_total - $shipping_charge;
			if($publisher_commision_per){
 				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}else{
				$whuso_income = 0;
			}
			
			// $Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;

			$publisherdetails = $CI->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
			$publication_name = '';

			$publication_name = $PublisherDetails->publication_name ?? null;
			if ($publication_name === 'Amar Chitra Katha (Books)') {
				$total_grand_=$total_webshop_price;
			} elseif($getCategory){
				$total_grand_ = $total_webshop_price;
			}else{
				$total_grand_ = $OrderData->grand_total - ($OrderData->shipping_amount);
			}
			// $total_grand_ = $OrderData->grand_total - $shipping_charge;

			// $total_grand_ = ($OrderData->grand_total+$shipping_charge) - $shipping_charge;

			// print_r($total_grand_);
			// die;

			
			
			// print_r($total_grand_);die;


			
			$Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;
			// print_r($Payable_Amount);
			// die;

			?>
		<p><span><?=lang('shipping_amount')?> :</span> 
			<?php echo $currency_code . ' ' . number_format($shipping_charge ?? 0, 2); ?>
		</p>

		<?php if (isset($OrderData->payment_gateway_charges)) { ?>
			<p><span><?=lang('payment_gateway_charges')?> :</span> 
				<?php echo $currency_code . ' ' . number_format($OrderData->payment_gateway_charges ?? 0, 2); ?>
			</p>
		<?php } ?>

		<p><span><?=lang('merchant_commission')?> :</span> 
			<?php echo $currency_code . ' ' . number_format($publisher_commision_per ?? 0, 2) . '%'; ?>
		</p>

		<p><span><?=lang('yellowmarket_income')?> :</span> 
			<?php echo $currency_code . ' ' . number_format($whuso_income ?? 0, 2); ?>
		</p>

		<p><span><?=lang('b2b_net_payable')?> :</span> 
			<?php echo $currency_code . ' ' . number_format($Payable_Amount ?? 0, 2); ?>
		</p>


		<?php } ?>
	</div>
	<?php
	 $deliveryAttempts = $CI->B2BOrdersModel->getMultiDataById('b2b_orders_delivery_details', array('order_id' => $OrderData->order_id), '', 'id', 'ASC');
	 //echo "<pre>";
	 //print_r($deliveryData);
	 //exit;
	 ?>
	<div class="col-6 order-id order-b2b-info-span">
		<!-- <p><span>Payment Mode :</span> Offline </p> -->
		<p><span><?=lang('customer_payment_mode')?> :</span> 
			<?php  
			if($order_payment_method->payment_method == "Cheque_FundsTransfer"){
			?>
				<?=lang('cheque_fund_transfer')?>
			<?php }else {?>
				<?=lang('cc_avenue')?>
			<?php } ?>
			
		</p>
		
    
        </div>
		
		<!-- <p class="position-relative"><textarea placeholder="Note" readonly class="form-control col-sm-10 "><?= $sales_order->internal_notes ?></textarea> -->
		 
<?php if (!empty($deliveryAttempts)) : ?>
	
    <?php 
        // Get the latest attempt (last row)
        $lastAttempt = end($deliveryAttempts);
    ?>
    <table class="table table-bordered deliverytable">
        <thead>
            <tr>
				<th><?=lang('attempt_no')?></br></th>
				<th><?=lang('delivery_person')?></br></th>
				<th><?=lang('delivery_date')?></th>
				<!-- <th><?=lang('driver_id')?></th> -->
				<th><?=lang('status')?></th>
				<th><?=lang('remarks')?></th>
				<th><?=lang('success_failure_reason')?></th>

				<?php if($OrderData->shipment_type != 2) { ?>
                <th><?=lang('action')?></th>
				<?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveryAttempts as $attempt) : ?>
                <tr>
                    <td><?= $attempt->delivery_attempt_no; ?></td>
					<td><?= $attempt->own_delivery_person_name; ?></td>
                    <td><?= date('d M Y', strtotime($attempt->delivery_date)); ?></td>
                    <!-- <td><?= $attempt->driver_id; ?></td> -->
                    <td>
						<?php
						switch ($attempt->delivery_status) {
							case 1: echo lang('shipped'); break;
							case 2: echo lang('failed_attempt_1'); break;
							case 3: echo lang('attempt_2'); break;
							case 4: echo lang('attempt_2_failed'); break;
							case 5: echo lang('attempt_3'); break;
							case 6: echo lang('attempt_3_failed'); break;
							case 7: echo lang('collect_from_store'); break;
							case 8: echo lang('delivered'); break;
							default: echo lang('pending'); break;
						}
						?>
					</td>

                    <td><?= !empty($attempt->remarks) ? $attempt->remarks : '-'; ?></td>
                 

					<td>
					<?php
					$reason = !empty($attempt->reason_for_attempt_failed) ? $attempt->reason_for_attempt_failed : '-';

					if($reason != '-'){

						if($this->session->userdata('site_lang') == 'english'){

					echo $reason;

						}else{
							
									if($reason == 'Nobody Answering Call'){ echo lang('nobody_answering_call'); }
							else if($reason == 'Nobody At Home'){ echo lang('nobody_at_home'); }
							else if($reason == 'Danger Condition e.g. Dogs'){ echo lang('danger_condition'); }
							else if($reason == 'Weather Condition e.g. Flooding'){ echo lang('weather_condition'); }
							else if($reason == 'Customer Requested To Reschedule'){ echo lang('customer_requested_reschedule'); }
							else if($reason == 'Success'){ echo lang('success'); }
							else { echo $reason; }
						}

					}else{
						echo "-";
					}
					?>
					</td>

					<?php if($OrderData->shipment_type != 2) { ?>
                    <td>
					<?php if ($attempt->id == $lastAttempt->id && $attempt->delivery_status != 8) : ?>
						<?php if (empty($attempt->reason_for_attempt_failed)) : ?>
							<!-- Latest attempt not failed → Show Mark as Failed -->
						<button class="btn btn-danger btn-sm" 
								onclick="MarkAsFailedPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
							<?= lang('mark_as_failed') ?>
						</button>

						<?php elseif ($attempt->delivery_attempt_no < 2): ?>
							<!-- Latest attempt failed & attempt no < 3 → Show Assign New Delivery -->
							<button class="btn btn-primary btn-sm" 
									onclick="AssignNewDeliveryPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
								<?= lang('attempt') . ' ' . ($attempt->delivery_attempt_no + 1) ?>
							</button>

						<?php else: ?>
							<!-- All attempts used or nothing to assign -->
							<span class="text-muted">-</span>
						<?php endif; ?>
					<?php else: ?>
						<!-- Not the latest attempt -->
						<span class="text-muted">-</span>
					<?php endif; ?>
				</td>
				<?php } ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>

<?php endif; ?>





	</div>
	

	<?php if (isset($OrderData->parent_id) && $OrderData->parent_id > 0) { ?>
	<div class="col-sm-6 order-id">
		<p><span><?= lang('split_order_total') ?> </span> <?php echo $currency_code . ' ' . number_format($OrderData->subtotal, 2); ?> </p>
		<p><span><?= lang('discount') ?> (<?php echo $OrderData->discount_percent; ?>%) :</span> <?php echo $currency_code . ' ' . number_format($OrderData->discount_amount, 2); ?></p>
		<p><span><?= lang('b2b_taxes_amount') ?> :</span> <?php echo $currency_code . ' ' . number_format($OrderData->tax_amount, 2); ?></p>
		<p><span><?= lang('b2b_net_payable_split') ?> :</span> <?php echo $currency_code . ' ' . number_format($OrderData->grand_total, 2); ?></p>
	</div>

	<?php } ?>
</div><!-- barcode-qty-box -->


<script>




	function submitMarkFailedForm() {
    var form = $('#mark_failed_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            $('#modal').modal('hide');
            var res = jQuery.parseJSON(response);

            swal({
                title: res.status == 200 ? "Success" : "Error",
                icon: res.status == 200 ? "success" : "error",
                text: res.message,
                buttons: true,
            }, function() {
                location.reload();
            });
        }
    });
    return false;
}

function MarkAsFailedPopup(order_id, attempt_no) {
    if (order_id != '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/MarkAsFailedPopup",
            type: "POST",
            data: {
                order_id: order_id,
                attempt_no: attempt_no
            },
            success: function(response) {
                if (response != 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                } else {
                    swal("Error", "Something went wrong.", "error");
                }
            }
        });
    } else {
        return false;
    }
}

</script>
<script>
$('#saveBtn').on('click', function(e) {
    e.preventDefault();
    
    var formData = new FormData($('#docForm')[0]);
    
    // Explicitly pull the value from the hidden input
    var order_id = $('input[name="order_id"]').val();
    formData.append('order_id', order_id);
    
    $.ajax({
        url: '<?php echo base_url("B2BOrdersController/upload_order_document"); ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
    try {
        var res = JSON.parse(response);
        if(res.status === 'success') {
            alert('Success: ' + res.message);
        } else {
            // This line removes the <p> and </p> tags
            var cleanMessage = res.message.replace(/<\/?[^>]+(>|$)/g, "");
            alert('Error: ' + cleanMessage);
        }
    } catch (e) {
        console.error("Could not parse JSON.");
    }
},
        error: function(xhr) {
            console.error(xhr.responseText);
        }
    });
});


</script>
<script>
$(document).on('click', '#downloadBtn', function(e) {
    e.preventDefault();

    var order_id = $(this).attr('data-id') || $('input[name="order_id"]').val();

    console.log("Order ID:", order_id);

    if (!order_id) {
        alert("Error: Order ID is missing!");
        return;
    }

    var downloadUrl = '<?php echo base_url("B2BOrdersController/download_order_document/"); ?>' + order_id;
    
    // Fetch the translated error message from the clicked button
    var errorMessage = $(this).attr('data-error-msg') || "Invoice not uploaded by the merchant";

    // Check via AJAX if the document exists before downloading
    $.ajax({
        url: downloadUrl,
        type: 'HEAD',
        success: function() {
            window.location.href = downloadUrl;
        },
        error: function(xhr) {
            alert(errorMessage); // Displays the localized language alert
        }
    });
});
</script>

