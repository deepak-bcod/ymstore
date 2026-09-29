<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<style>
	.purple { color: purple; font-weight: bold; }
	.green { color: green; font-weight: bold; }
	.black { color: black; font-weight: bold; }
	.blue { color: blue; font-weight: bold; }
	.red { color: red; font-weight: bold; }

</style>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="inner-nav">
		<ul class="nav nav-pills">
			<li >
				<a href="<?php echo base_url() ?>webshop/return-orders/">Returns</a></li>
			<li class="active">
				<a href="<?php echo base_url() ?>webshop/replacement-orders/">Replacements</a>
			</li>
		</ul>
	</div>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			
			<div class="content-main form-dashboard yet-section">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_Replacements">
						<thead>
							<tr>
								<th>Merchant <br>Order No.</th>
								<th>YM <br>Order No.</th>
								<th>Purchased On</th>
								<th>Customer Name</th>
								<th>Merchant<br>Name</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php if(!empty($orders)) { ?>
								<?php foreach ($orders as $order) {

									$purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
									$purchaseTime = date('h:i A', $order['purchase_timestamp']);
									$purchaseOnFull = $purchaseDate . ' | ' . $purchaseTime;

									$repStatus = isset($order['replacement_status']) ? (int)$order['replacement_status'] : 0;
									$sorStatus = isset($order['replacement_order_status']) ? (int)$order['replacement_order_status'] : 0;
									$b2bStatus = isset($order['b2b_order_status']) ? (int)$order['b2b_order_status'] : 0;

									// Check item-level statuses if items exist
									$hasRejectedItem = false;
									$hasReplacedItem = false;
									$hasApprovedItem = false;
									if (!empty($order['products'])) {
										foreach ($order['products'] as $prod) {
											$ist = isset($prod['item_status']) ? (int)$prod['item_status'] : 0;
											if (in_array($ist, [4, 21])) {
												$hasRejectedItem = true;
											} elseif (in_array($ist, [3, 5, 6, 19])) {
												$hasReplacedItem = true;
											} elseif (in_array($ist, [1, 2, 18])) {
												$hasApprovedItem = true;
											}
										}
									}

									if ($repStatus === 19 || in_array($repStatus, [3, 5, 6]) || in_array($sorStatus, [3, 5, 6]) || $hasReplacedItem) {
										$statusText = "Replaced";
										$statusClass = "blue";
									} else if ($repStatus === 21 || $repStatus === 4 || $sorStatus === 4 || $sorStatus === 21 || $hasRejectedItem) {
										$statusText = $this->lang->line('model_status_replacement_rejected') ?: "Replacement Rejected";
										$statusClass = "red";
									} else if ($repStatus === 18 || in_array($repStatus, [1, 2]) || in_array($sorStatus, [1, 2]) || $hasApprovedItem) {
										$statusText = $this->lang->line('model_status_replacement_approved') ?: "Replacement Approved";
										$statusClass = "green";
									} else {
										$statusText = "Pending";
										$statusClass = "purple";
									}

								?>
								<tr>
									<td><?= $order['order_barcode']; ?></td>
									<td><?= $order['shopper_order_id']; ?></td>
									<td><?= $purchaseOnFull; ?></td>
									<td><?= $order['customer_name']; ?></td>
									<td><?= $order['publication_name']; ?></td>
									<td>
										<span style="color: <?= $statusClass ?>; font-weight: bold;">
											<?= $statusText ?>
										</span>
									</td>
									<td>
										<button class="btn btn-primary btn-view-order yelllo-text"
											data-id="<?= $order['replacement_order_id'] ?>"
											data-status="<?= $order['replacement_status'] ?>"
											data-products="<?= htmlspecialchars(json_encode($order['products']), ENT_QUOTES, 'UTF-8') ?>">
											View
										</button>
									</td>
								</tr>
								<?php } ?>
							<?php } ?>
						</tbody>

					</table>

				</div>
			</div>
			<!--end form-->
		</div>
	</div>
</main>
<div class="modal fade" id="order-action-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header"><h5>Replacement Items Details </h5><button type="button" class="btn-close" data-dismiss="modal" style="border: 0; font-weight: bold; background: transparent;">X</button></div>

       <div class="modal-body" id="order-action-modal-body"></div>
       <div class="modal-footer" id="order-action-modal-footer"></div>
     </div>
  </div>
</div>




<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
	$(document).ready(function () {

		// Destroy existing instance if already initialized
		if ($.fn.DataTable.isDataTable('#DataTables_Table_Replacements')) {
			$('#DataTables_Table_Replacements').DataTable().clear().destroy();
		}

		// Initialize DataTable
		$('#DataTables_Table_Replacements').DataTable({
			responsive: true,
			autoWidth: false,
			pageLength: 10,
			ordering: true,
			searching: true,
			lengthChange: true,
			info: true,
			order: [[0, 'desc']], 
			language: {
				emptyTable: "No records available",
				search: "_INPUT_",
				searchPlaceholder: "Search"
			},
			columnDefs: [
				{
					targets: -1, 
					orderable: false
				}
			]
		});

	});

	
	$(document).on('click', '.btn-view-order', function () {

		var products = $(this).data('products');
		var id = $(this).data('id');
		var status = $(this).data('status');

		$('#order-action-modal').data('id', id);
		$('#order-action-modal').data('status', status);

		if (typeof products === "string") {
			products = JSON.parse(products);
		}

		var productHtml = `
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Product</th>
						<th>Qty</th>
						<th>Price (MUR)</th>
						<th>Total (MUR)</th>
						<th>Replacement Type</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
		`;

		var hasYM = false;
		var hasOwn = false;
		var hasPendingYM = false;

		products.forEach(p => {

			// Extract and format variant information if product is conf-simple
			let variantDisplay = '';
			if (p.product_type === 'conf-simple' && p.product_variants && p.product_variants.trim() !== '') {
				try {
					const variants = JSON.parse(p.product_variants);
					const variantNames = [];
					if (Array.isArray(variants)) {
						variants.forEach(variant => {
							for (let key in variant) {
								variantNames.push(key + ': ' + variant[key]);
							}
						});
						if (variantNames.length > 0) {
							variantDisplay = '<br><small style="color: #666;">' + variantNames.join(', ') + '</small>';
						}
					}
				} catch (e) {
					// JSON parse error - ignore
				}
			}

			let shipmentType = (p.shipment_type !== undefined && p.shipment_type !== null) ? String(p.shipment_type).trim() : '';
			let itemStatus = parseInt(p.item_status || 0);

			let itemTypeLabel = '';
			let itemStatusLabel = '';
			let itemStatusClass = '';
			let itemActionButtons = '';

			if (shipmentType === '2' || itemStatus === 2 || itemStatus === 6) {
				hasYM = true;
				itemTypeLabel = '<span style="font-weight: bold; color: #000;">YM Replacement</span>';

				switch (itemStatus) {
					case 0: // Pending
						hasPendingYM = true;
						itemStatusLabel = 'Pending';
						itemStatusClass = 'purple';
						itemActionButtons = `
							<button class="btn btn-success btn-sm admin-approve-ym" data-item-id="${p.replacement_item_id}">Approve</button>
							<button class="btn btn-danger btn-sm admin-reject-ym" data-item-id="${p.replacement_item_id}">Reject</button>
						`;
						break;
					case 2: // Approved (YM Replacement)
						itemStatusLabel = 'YM Replacement Approved';
						itemStatusClass = 'green';
						itemActionButtons = '<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px; background: #28a745; color: #fff; border-radius: 4px;">Approved</span>';
						break;
					case 3:
					case 6: // Replaced (YM)
						itemStatusLabel = 'Replaced (YM)';
						itemStatusClass = 'black';
						itemActionButtons = '<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px; background: #28a745; color: #fff; border-radius: 4px;">Replaced</span>';
						break;
					case 4:
					case 21: // Rejected
						itemStatusLabel = 'Rejected';
						itemStatusClass = 'red';
						itemActionButtons = '<span class="badge bg-danger" style="font-size: 11px; padding: 4px 8px; background: #dc3545; color: #fff; border-radius: 4px;">Rejected</span>';
						break;
					default:
						itemStatusLabel = 'Pending';
						itemStatusClass = 'purple';
						itemActionButtons = `
							<button class="btn btn-success btn-sm admin-approve-ym" data-item-id="${p.replacement_item_id}">Approve</button>
							<button class="btn btn-danger btn-sm admin-reject-ym" data-item-id="${p.replacement_item_id}">Reject</button>
						`;
						break;
				}
			} else {
				hasOwn = true;
				itemTypeLabel = '<span style="font-weight: bold; color: green;">Own Replacement</span>';

				switch (itemStatus) {
					case 0:
						itemStatusLabel = 'Pending';
						itemStatusClass = 'purple';
						break;
					case 1:
						itemStatusLabel = 'Own Replacement Approved';
						itemStatusClass = 'green';
						break;
					case 5:
					case 3:
						itemStatusLabel = 'Replaced (Own)';
						itemStatusClass = 'green';
						break;
					case 4:
					case 21:
						itemStatusLabel = 'Rejected';
						itemStatusClass = 'red';
						break;
					default:
						itemStatusLabel = 'Pending';
						itemStatusClass = 'purple';
						break;
				}

				// The Admin Panel should NOT provide the option to process or approve the Own Replacement.
				itemActionButtons = '<span class="badge bg-secondary" style="font-size: 11px; padding: 4px 8px; background: #6c757d; color: #fff; border-radius: 4px;">Managed by Merchant</span>';
			}

			productHtml += `
				<tr>
					<td>${p.product_name}${variantDisplay}</td>
					<td>${p.qty}</td>
					<td>${p.price}</td>
					<td>${p.total_price}</td>
					<td>${itemTypeLabel}</td>
					<td><span style="color: ${itemStatusClass === 'purple' ? '#800080' : itemStatusClass === 'green' ? '#008000' : itemStatusClass === 'black' ? '#000000' : itemStatusClass === 'blue' ? '#0000FF' : '#FF0000'}; font-weight: bold;">${itemStatusLabel}</span></td>
					<td>${itemActionButtons}</td>
				</tr>
			`;
		});

		productHtml += `</tbody></table>`;

		$('#order-action-modal-body').html(productHtml);

		// FOOTER BUTTON: Only for YM Replacement Done at order level if approved
		let footerHtml = `
			<button class="btn btn-secondary" data-dismiss="modal">Close</button>
		`;

		if (hasYM && (status == 18 || status == 2)) {
			footerHtml = `
				<button class="btn btn-success" id="confirm-ym-replacement-done">YM Replacement Done</button>
				<button class="btn btn-secondary" data-dismiss="modal">Close</button>
			`;
		}

		$('#order-action-modal-footer').html(footerHtml);

		var myModal = new bootstrap.Modal(document.getElementById('order-action-modal'));
		myModal.show();
	});

	// Item-level handlers for YM Replacement in Admin Panel
	$(document).on('click', '.admin-approve-ym', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Approve this item for YM Replacement?")) {
			updateItemStatus(itemId, 2); // 2 = YM Replacement Approved
		}
	});

	$(document).on('click', '.admin-reject-ym', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Reject this YM Replacement request item?")) {
			updateItemStatus(itemId, 4); // 4 = Rejected
		}
	});

	$(document).on('click', '.admin-complete-ym', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Mark YM Replacement as completed for this item?")) {
			updateItemStatus(itemId, 6); // 6 = Replaced (YM)
		}
	});

	// Order-level handler for YM Replacement Done in Admin Panel
	$(document).on('click', '#confirm-ym-replacement-done', function () {
		if (confirm("Confirm YM Replacement Done for this order?")) {
			updateOrderStatus(6);
		}
	});

	// AJAX FUNCTIONS
	function updateItemStatus(itemId, status) {
		$.post('<?= base_url("WebshopOrdersController/replacement_update_item_status") ?>',
			{ replacement_item_id: itemId, status: status },
			function (response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.error || 'Failed to update status');
				}
			},
			'json'
		);
	}

	function updateOrderStatus(status) {
		var id = $('#order-action-modal').data('id');
		$.post('<?= base_url("WebshopOrdersController/replacement_update_status") ?>',
			{ id: id, status: status },
			function (response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.error || 'Failed to update status');
				}
			},
			'json'
		);
	}
</script>