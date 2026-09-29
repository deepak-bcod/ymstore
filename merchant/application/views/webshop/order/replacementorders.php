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
									$hasOwnApproved = false;
									$hasYmApproved = false;
									$hasApprovedItem = false;
									if (!empty($order['products'])) {
										foreach ($order['products'] as $prod) {
											$ist = isset($prod['item_status']) ? (int)$prod['item_status'] : 0;
											if (in_array($ist, [4, 21])) {
												$hasRejectedItem = true;
											} elseif (in_array($ist, [3, 5, 6, 19])) {
												$hasReplacedItem = true;
											} elseif ($ist === 1) {
												$hasOwnApproved = true;
											} elseif ($ist === 2) {
												$hasYmApproved = true;
											} elseif (in_array($ist, [18])) {
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
									} else if ($hasOwnApproved || $repStatus === 1 || $sorStatus === 1) {
										$statusText = "Own Replacement";
										$statusClass = "green";
									} else if ($hasYmApproved || $repStatus === 2 || $sorStatus === 2) {
										$statusText = "YM Replacement";
										$statusClass = "black";
									} else if ($repStatus === 18 || $hasApprovedItem) {
										$statusText = $this->lang->line('model_status_replacement_approved') ?: "Replacement Approved";
										$statusClass = "green";
									} else {
										$statusText = "Pending";
										$statusClass = "purple";
									}


								?>
								<tr>
									<td>
										<?php 
										$order_link_id = !empty($order['b2b_order_id']) ? $order['b2b_order_id'] : (!empty($order['webshop_order_id']) ? $order['webshop_order_id'] : '');
										if (!empty($order_link_id)) { ?>
											<a class="link-purple" href="<?= base_url('webshop/b2b/order/detail/' . $order_link_id); ?>" target="_blank"><?= $order['order_barcode']; ?></a>
										<?php } else { ?>
											<?= $order['order_barcode']; ?>
										<?php } ?>
									</td>
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
         
         <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" style="border: 0; font-weight: bold; background: transparent;">X</button>
       </div>
       
       <div class="modal-body" id="order-action-modal-body"></div>
       
       <div class="modal-footer" id="order-action-modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">
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

			let itemStatus = parseInt(p.item_status || 0);

			switch (itemStatus) {
				case 0:
					statusText = "Pending";
					statusClass = "purple";
					actionButtons = `
						<button class="btn btn-success btn-sm approve-item" data-item-id="${p.replacement_item_id}">Approve</button>
						<button class="btn btn-danger btn-sm reject-item" data-item-id="${p.replacement_item_id}">Reject</button>
					`;
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
					actionButtons = '<span class="badge" style="font-size: 11px; padding: 4px 8px; background: #6f42c1; color: #fff; border-radius: 4px;">Sent to Admin</span>';
					break;
				case 3:
					statusText = "Replaced";
					statusClass = "blue";
					actionButtons = '<span class="badge" style="font-size: 11px; padding: 4px 8px; background: #007bff; color: #fff; border-radius: 4px;">Replaced</span>';
					break;
				case 4:
				case 21:
					statusText = "Rejected";
					statusClass = "red";
					actionButtons = '<span class="badge" style="font-size: 11px; padding: 4px 8px; background: #dc3545; color: #fff; border-radius: 4px;">Rejected</span>';
					break;
				case 5:
					statusText = "Replaced (Own)";
					statusClass = "green";
					actionButtons = '<span class="badge" style="font-size: 11px; padding: 4px 8px; background: #28a745; color: #fff; border-radius: 4px;">Replaced</span>';
					break;
				case 6:
					statusText = "Replaced (YM)";
					statusClass = "black";
					actionButtons = '<span class="badge" style="font-size: 11px; padding: 4px 8px; background: #343a40; color: #fff; border-radius: 4px;">Replaced</span>';
					break;
				default:
					statusText = "Pending";
					statusClass = "purple";
					actionButtons = `
						<button class="btn btn-success btn-sm approve-item" data-item-id="${p.replacement_item_id}">Approve</button>
						<button class="btn btn-danger btn-sm reject-item" data-item-id="${p.replacement_item_id}">Reject</button>
					`;
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
			<button class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">
				<?php echo $this->lang->line('close_label') ? $this->lang->line('close_label') : 'Close'; ?>
			</button>
		`;

		$('#order-action-modal-footer').html(footerHtml);

		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			try {
				var modalEl = document.getElementById('order-action-modal');
				var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
				myModal.show();
			} catch(e) {
				$('#order-action-modal').modal('show');
			}
		} else {
			$('#order-action-modal').modal('show');
		}
	});

	// Trigger replacement approval popup using SweetAlert2
	$(document).on('click', '.approve-item', function () {
		var itemId = $(this).data('item-id');
		
		Swal.fire({
			title: '<div style="font-size: 20px; font-weight: 700; color: #2d3748; padding-top: 4px;"><i class="fas fa-exchange-alt" style="color: #6f42c1; margin-right: 8px;"></i> Approve Replacement</div>',
			html: `
				<div style="text-align: left; margin-top: 10px;">
					<p style="color: #4a5568; font-size: 14px; margin-bottom: 14px;">Please select the replacement fulfillment workflow for this item:</p>
					
					<!-- Option 1: Own Replacement -->
					<label class="swal-rep-card" id="swal-card-own" style="display: block; border: 2px solid #28a745; border-radius: 10px; padding: 13px 15px; margin-bottom: 12px; cursor: pointer; background: #f9fff9; transition: all 0.2s ease;">
						<div style="display: flex; align-items: flex-start;">
							<input type="radio" name="swal_replacement_choice" id="swal_rep_own" value="1" checked style="width: 18px; height: 18px; margin-top: 2px; margin-right: 12px; cursor: pointer; accent-color: #28a745;">
							<div>
								<strong style="color: #28a745; font-size: 15px; display: block; margin-bottom: 3px;">1. Own Replacement</strong>
								<div style="font-size: 13px; color: #4a5568; line-height: 1.4;">
									Completed <strong>entirely from Merchant Panel</strong>. You can proceed with and complete the replacement without involving Admin.
								</div>
								<span style="background: #28a745; color: #fff; font-size: 11px; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 6px; font-weight: 600;">Merchant Panel Only</span>
							</div>
						</div>
					</label>

					<!-- Option 2: YM Replacement -->
					<label class="swal-rep-card" id="swal-card-ym" style="display: block; border: 2px solid #e2e8f0; border-radius: 10px; padding: 13px 15px; margin-bottom: 0; cursor: pointer; background: #fff; transition: all 0.2s ease;">
						<div style="display: flex; align-items: flex-start;">
							<input type="radio" name="swal_replacement_choice" id="swal_rep_ym" value="2" style="width: 18px; height: 18px; margin-top: 2px; margin-right: 12px; cursor: pointer; accent-color: #6f42c1;">
							<div>
								<strong style="color: #6f42c1; font-size: 15px; display: block; margin-bottom: 3px;">2. YM Replacement</strong>
								<div style="font-size: 13px; color: #4a5568; line-height: 1.4;">
									Approved by merchant, but <strong>actual replacement process is handled from Admin Panel</strong>. Forwarded to Admin for fulfillment.
								</div>
								<span style="background: #6f42c1; color: #fff; font-size: 11px; padding: 2px 7px; border-radius: 4px; display: inline-block; margin-top: 6px; font-weight: 600;">Admin Panel Only</span>
							</div>
						</div>
					</label>
				</div>
			`,
			showCancelButton: true,
			confirmButtonText: '<i class="fas fa-check"></i> Confirm Approval',
			cancelButtonText: 'Cancel',
			confirmButtonColor: '#28a745',
			cancelButtonColor: '#6c757d',
			focusConfirm: false,
			customClass: {
				popup: 'swal2-custom-popup'
			},
			didOpen: () => {
				$(Swal.getPopup()).on('click', '.swal-rep-card', function () {
					var radio = $(this).find('input[type="radio"]');
					radio.prop('checked', true);
					$('.swal-rep-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
					if (radio.val() === '1') {
						$('#swal-card-own').css({'border-color': '#28a745', 'background': '#f9fff9'});
					} else {
						$('#swal-card-ym').css({'border-color': '#6f42c1', 'background': '#faf7ff'});
					}
				});
				$(Swal.getPopup()).on('change', 'input[name="swal_replacement_choice"]', function () {
					$('.swal-rep-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
					if ($(this).val() === '1') {
						$('#swal-card-own').css({'border-color': '#28a745', 'background': '#f9fff9'});
					} else {
						$('#swal-card-ym').css({'border-color': '#6f42c1', 'background': '#faf7ff'});
					}
				});
			},
			preConfirm: () => {
				var selected = $('input[name="swal_replacement_choice"]:checked').val();
				if (!selected) {
					Swal.showValidationMessage('Please select a replacement option');
					return false;
				}
				return parseInt(selected);
			}
		}).then((result) => {
			if (result.isConfirmed) {
				updateItemStatus(itemId, result.value);
			}
		});
	});

	// Reject item handler
	$(document).on('click', '.reject-item', function () {
		var itemId = $(this).data('item-id');
		Swal.fire({
			title: 'Reject Replacement Request?',
			text: 'Are you sure you want to reject this replacement request item?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="fas fa-times"></i> Yes, Reject',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.isConfirmed) {
				updateItemStatus(itemId, 4); // 4 = Rejected
			}
		});
	});

	// Complete Own Replacement handler
	$(document).on('click', '.complete-own', function () {
		var itemId = $(this).data('item-id');
		Swal.fire({
			title: 'Complete Own Replacement?',
			text: 'Mark this replacement as completed by merchant?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#28a745',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="fas fa-check-circle"></i> Yes, Mark as Done',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.isConfirmed) {
				updateItemStatus(itemId, 5); // 5 = Replaced (Own)
			}
		});
	});

	// AJAX function for per-item status updates
	function updateItemStatus(itemId, status) {
		var repType = (status === 1 || status === 5) ? 'own' : ((status === 2 || status === 6) ? 'ym' : '');

		Swal.fire({
			title: 'Updating status...',
			text: 'Please wait...',
			allowOutsideClick: false,
			didOpen: () => {
				Swal.showLoading();
			}
		});

		$.ajax({
			url: '<?= base_url("WebshopOrdersController/replacement_update_item_status") ?>',
			type: 'POST',
			data: { 
				replacement_item_id: itemId, 
				status: status,
				replacement_type: repType
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: 'Replacement status updated successfully.',
						timer: 1500,
						showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Failed',
						text: response.error || 'Failed to update status.'
					});
				}
			},
			error: function (xhr, status, error) {
				console.error('AJAX Error:', xhr.responseText);
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'An error occurred while updating the status. Please try again.'
				});
			}
		});
	}
</script>
