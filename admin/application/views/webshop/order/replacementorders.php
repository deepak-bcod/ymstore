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
										<?php if($order['status'] == 0 || $order['status'] == 18){ ?>
											<button class="btn btn-primary btn-view-order yelllo-text"
												data-id="<?= $order['replacement_order_id'] ?>"
												data-status="<?= $order['replacement_status'] ?>"
												data-products="<?= htmlspecialchars(json_encode($order['products']), ENT_QUOTES, 'UTF-8') ?>">
												View
											</button>
										<?php } ?>
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
		// Locate this section in your script:
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
				emptyTable: "No records available", // <--- Added missing comma
				search: "_INPUT_",                 // <--- Hides the external "Search:" label
				searchPlaceholder: "Search"      // <--- Adds text inside the box
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
					<tr><th>Product</th><th>Qty</th><th>Price (MUR)</th><th>Total (MUR)</th></tr>
				</thead>
				<tbody>
		`;

		var hasYM = false;
		var hasOwn = false;

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
			productHtml += `
				<tr>
					<td>${p.product_name}${variantDisplay}</td>
					<td>${p.qty}</td>
					<td>${p.price}</td>
					<td>${p.total_price}</td>
				</tr>
			`;
			if (p.item_status == 2) hasYM = true;
			if (p.item_status == 1) hasOwn = true;
		});

		productHtml += `</tbody></table>`;

		$('#order-action-modal-body').html(productHtml);

		// FOOTER BUTTON CONDITION BASED ON STATUS AND ITEMS
		let footerHtml = '';

		if (status == 18) {
			if (hasYM) {
				footerHtml = `<button class="btn btn-success" id="confirm-ym-replacement-done">YM Replacement Done</button>`;
			} 
			// else if (hasOwn) {
			// 	footerHtml = `<button class="btn btn-success" id="confirm-own-replacement-done">Own Replacement Done</button>`;
			// }
		}

		$('#order-action-modal-footer').html(footerHtml);

		var myModal = new bootstrap.Modal(document.getElementById('order-action-modal'));
		myModal.show();
	});
	$(document).on('click', '#confirm-ym-replacement-done', function () {
		updateStatus(6);
	});
	$(document).on('click', '#confirm-own-replacement-done', function () {
		updateStatus(5);
	});
	// AJAX FUNCTION
	function updateStatus(status) {
		var id = $('#order-action-modal').data('id');
		$.post('<?= base_url("WebshopOrdersController/replacement_update_status") ?>',
			{ id: id, status: status },
			function () {
				location.reload();
			},
			'json'
		);
	}

	

</script>