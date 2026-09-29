<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<style>
	.purple { color: purple; font-weight: bold; }
	.green { color: green; font-weight: bold; }
	.black { color: black; font-weight: bold; }
	.blue { color: blue; font-weight: bold; }
	.red { color: red; font-weight: bold; }
	.btn.btn-success {
		color: #fff !important;
		background-color: #28a745 !important;
		border-color: #28a745 !important;
	}
</style>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="inner-nav">
		<ul class="nav nav-pills">
			<li >
				<a href="<?php echo base_url() ?>webshop/return-orders/"><?php echo lang('returns_tab_label'); ?></a></li>
			<li class="active">
				<a href="<?php echo base_url() ?>webshop/replacement-orders/"><?php echo lang('replacements_tab_label'); ?></a>
			</li>
		</ul>
	</div>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			
			<div class="content-main form-dashboard return-section">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_Replacements">
						<thead>
							<th><?php echo $this->lang->line('es_order_no'); ?></th>
							<th><?php echo $this->lang->line('ym_order_no'); ?></th>
							<th><?php echo $this->lang->line('purchased_no'); ?></th>
							<th><?php echo $this->lang->line('shopper_name'); ?></th>
							<th><?php echo $this->lang->line('replacement_status'); ?></th>
							<th><?php echo $this->lang->line('action'); ?></th>
						</thead>
						<tbody>
							<?php if(!empty($orders)) { ?>
								<?php foreach ($orders as $order) {

									$purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
									$purchaseTime = date('h:i A', $order['purchase_timestamp']);
									$purchaseOnFull = $purchaseDate ;

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
									<td>
										<span style="color: <?= $statusClass ?>; font-weight: bold;">
											<?= $statusText ?>
										</span>
									</td>
									<td>
										<button class="btn btn-primary btn-view-order yello-text"
											data-id="<?= $order['replacement_order_id'] ?>"
											data-status="<?= $order['replacement_status'] ?>"
											data-products="<?= htmlspecialchars(json_encode($order['products']), ENT_QUOTES, 'UTF-8') ?>">
											<?php echo $this->lang->line('action_view') ? $this->lang->line('action_view') : 'View'; ?>
										</button>
									</td>
								</tr>
								<?php } ?>
							<?php } else { ?>
							<tr><td colspan="6" class="text-center"><?php echo lang('no_records_found_label'); ?></td></tr>
							<?php } ?>
						</tbody>

					</table>

				</div>
			</div>
			<!--end form-->
		</div>
	</div>
</main>
 
<!-- Order Action Modal -->
<!-- Single modal for all actions -->


<div class="modal fade" id="order-action-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <h5><?php echo $this->lang->line('replacement_details_title') ? $this->lang->line('replacement_details_title') : 'Replacement Item Details'; ?></h5>
         
         <button type="button" class="btn-close" data-dismiss="modal" style="border: 0; font-weight: bold; background: transparent;">X</button>
       </div>
       
       <div class="modal-body" id="order-action-modal-body"></div>
       
       <div class="modal-footer" id="order-action-modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">
            <?php echo $this->lang->line('close_label') ? $this->lang->line('close_label') : 'Close'; ?>
          </button>
       </div>
     </div>
  </div>
</div>




<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
<!-- <script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_list.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->

<script type="text/javascript">
	$(document).ready(function () {
		$.fn.dataTable.ext.errMode = 'none';

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
				"emptyTable": DT_LANG.emptyTable,
				"zeroRecords": DT_LANG.zeroRecords,
				"processing": DT_LANG.processing,
				"info": (typeof DT_LANG !== 'undefined' && DT_LANG.info) ? DT_LANG.info : DT_LANG.info,
				"infoEmpty": (typeof DT_LANG !== 'undefined' && DT_LANG.infoEmpty) ? DT_LANG.infoEmpty : DT_LANG.infoEmpty,
				"infoFiltered": (typeof DT_LANG !== 'undefined' && DT_LANG.infoFiltered) ? DT_LANG.infoFiltered : DT_LANG.infoFiltered,
				"lengthMenu": (typeof DT_LANG !== 'undefined' && DT_LANG.lengthMenu) ? DT_LANG.lengthMenu : DT_LANG.lengthMenu,
				"search": "",
				"searchPlaceholder": (typeof DT_LANG !== 'undefined' && DT_LANG.searchPlaceholder) ? DT_LANG.searchPlaceholder : "",
				"paginate": {
					next: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.next) ? DT_LANG.paginate.next : '<i class="fas fa-angle-right"></i>',
					previous: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.previous) ? DT_LANG.paginate.previous : '<i class="fas fa-angle-left"></i>'
				}     // <--- Adds text inside the box
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

		var products = $(this).attr('data-products');   // IMPORTANT
		var id = $(this).attr('data-id');
		var status = $(this).attr('data-status');

		products = JSON.parse(products);

		$('#order-action-modal').data('id', id);
		$('#order-action-modal').data('status', status);

		var productHtml = `
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('product_name'); ?></th>
                <th><?php echo $this->lang->line('qty'); ?></th>
                <th><?php echo $this->lang->line('price'); ?></th>
                <th><?php echo $this->lang->line('total'); ?></th>
                <th><?php echo $this->lang->line('replacement_status'); ?></th>
                <th><?php echo $this->lang->line('action'); ?></th>
            </tr>
        </thead>
        <tbody>
`;

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

			// Status mapping for per-item status
			let statusText = '';
			let statusClass = '';
			let actionButtons = '';

			let shipmentType = '';
			if (p.shipment_type !== undefined && p.shipment_type !== null) {
				shipmentType = String(p.shipment_type).trim();
			}

			switch (parseInt(p.item_status)) {
				case 0:
					if (shipmentType === '2') {
						// YM Replacement is managed exclusively by Admin Panel
						statusText = "Pending (YM Delivery)";
						statusClass = "purple";
						actionButtons = '<span class="badge bg-secondary" style="font-size: 11px; padding: 4px 8px; background: #6c757d; color: #fff; border-radius: 4px;">Managed by Admin</span>';
					} else {
						// Own Replacement managed by Merchant Panel
						statusText = "Pending (Own Delivery)";
						statusClass = "purple";
						actionButtons = `
							<button class="btn btn-success btn-sm approve-item" data-item-id="${p.replacement_item_id}">Approve</button>
							<button class="btn btn-danger btn-sm reject-item" data-item-id="${p.replacement_item_id}">Reject</button>
						`;
					}
					break;
				case 1:
					statusText = "Own Replacement";
					statusClass = "green";
					actionButtons = `
						<button class="btn btn-success btn-sm complete-own" data-item-id="${p.replacement_item_id}">Done</button>
					`;
					break;
				case 2:
					statusText = "YM Replacement";
					statusClass = "black";
					// YM Replacement is processed exclusively by Admin Panel; Merchant cannot complete it
					actionButtons = ' - ';
					break;
				case 3:
					statusText = "Replaced";
					statusClass = "blue";
					actionButtons = '';
					break;
				case 4:
				case 21:
					statusText = "Rejected";
					statusClass = "red";
					actionButtons = '';
					break;
				case 5:
					statusText = "Replaced (Own)";
					statusClass = "green";
					actionButtons = '';
					break;
				case 6:
					statusText = "Replaced (YM)";
					statusClass = "black";
					actionButtons = '';
					break;
				default:
					if (shipmentType === '2') {
						statusText = "Pending (YM Delivery)";
						statusClass = "purple";
						actionButtons = '<span class="badge bg-secondary" style="font-size: 11px; padding: 4px 8px; background: #6c757d; color: #fff; border-radius: 4px;">Managed by Admin</span>';
					} else {
						statusText = "Pending";
						statusClass = "purple";
						actionButtons = `
							<button class="btn btn-success btn-sm approve-item" data-item-id="${p.replacement_item_id}">Approve</button>
							<button class="btn btn-danger btn-sm reject-item" data-item-id="${p.replacement_item_id}">Reject</button>
						`;
					}
					break;
			}

			productHtml += `
				<tr>
					<td>${p.product_name}${variantDisplay}</td>
					<td>${p.qty}</td>
					<td>${p.price}</td>
					<td>${p.total_price}</td>
					<td><span style="color: ${statusClass === 'purple' ? '#800080' : statusClass === 'green' ? '#008000' : statusClass === 'black' ? '#000000' : statusClass === 'blue' ? '#0000FF' : '#FF0000'}; font-weight: bold;">${statusText}</span></td>
					<td>${actionButtons}</td>
				</tr>
			`;
		});

		productHtml += `</tbody></table>`;

		$('#order-action-modal-body').html(productHtml);

		let footerHtml = `
			<button class="btn btn-secondary" data-dismiss="modal">
        <?php echo $this->lang->line('close_label') ? $this->lang->line('close_label') : 'Close'; ?>
    </button>
		`;

		$('#order-action-modal-footer').html(footerHtml);

		var myModal = new bootstrap.Modal(document.getElementById('order-action-modal'));
		myModal.show();
	});

	// Per-item status update handlers for Own Replacement
	$(document).on('click', '.approve-item', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Approve this item for Own Replacement?")) {
			updateItemStatus(itemId, 1); // 1 = Own Replacement Approved
		}
	});

	$(document).on('click', '.reject-item', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Reject this replacement request item?")) {
			updateItemStatus(itemId, 4); // 4 = Rejected
		}
	});

	$(document).on('click', '.complete-own', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Mark Own Replacement as completed?")) {
			updateItemStatus(itemId, 5); // 5 = Replaced (Own)
		}
	});

	// AJAX function for per-item status updates
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

	
</script>
