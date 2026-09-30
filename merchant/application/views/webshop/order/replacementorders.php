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

<!-- Replacement Approval Workflow Selection Modal -->
<div class="modal fade" id="replacement-approval-modal" tabindex="-1" role="dialog" aria-labelledby="replacementApprovalModalLabel" aria-hidden="true" style="z-index: 1070;">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
    <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border: none;">
      <div class="modal-header" style="background: #f8f9fa; border-top-left-radius: 12px; border-top-right-radius: 12px; border-bottom: 1px solid #e9ecef; padding: 16px 20px;">
        <h5 class="modal-title" id="replacementApprovalModalLabel" style="font-weight: 700; color: #333; margin: 0; font-size: 18px;">
          <i class="fas fa-exchange-alt" style="color: #6f42c1; margin-right: 8px;"></i> Approve Replacement
        </h5>
        <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="border: 0; font-weight: bold; background: transparent; font-size: 18px; cursor: pointer;">&times;</button>
      </div>
      <div class="modal-body" style="padding: 24px 20px;">
        <p style="color: #555; font-size: 14px; margin-bottom: 18px;">
          Please select the replacement workflow for this item:
        </p>

        <!-- Option 1: Own Replacement -->
        <label class="replacement-option-card" id="opt-card-own" for="rep_opt_own" style="display: block; border: 2px solid #28a745; border-radius: 10px; padding: 14px 16px; margin-bottom: 14px; cursor: pointer; background: #f9fff9; transition: all 0.2s ease;">
          <div style="display: flex; align-items: flex-start;">
            <input type="radio" name="replacement_type_choice" id="rep_opt_own" value="1" checked style="width: 18px; height: 18px; margin-top: 3px; margin-right: 12px; cursor: pointer; accent-color: #28a745;">
            <div>
              <strong style="color: #28a745; font-size: 15px; display: block; margin-bottom: 4px;">
                1. Own Replacement
              </strong>
            </div>
          </div>
        </label>

        <!-- Option 2: YM Replacement -->
        <label class="replacement-option-card" id="opt-card-ym" for="rep_opt_ym" style="display: block; border: 2px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 0; cursor: pointer; background: #fff; transition: all 0.2s ease;">
          <div style="display: flex; align-items: flex-start;">
            <input type="radio" name="replacement_type_choice" id="rep_opt_ym" value="2" style="width: 18px; height: 18px; margin-top: 3px; margin-right: 12px; cursor: pointer; accent-color: #6f42c1;">
            <div>
              <strong style="color: #6f42c1; font-size: 15px; display: block; margin-bottom: 4px;">
                2. YM Replacement
              </strong>
            </div>
          </div>
        </label>

        <input type="hidden" id="selected_replacement_item_id" value="">
      </div>
      <div class="modal-footer" style="background: #f8f9fa; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; border-top: 1px solid #e9ecef; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" style="padding: 7px 16px; border-radius: 6px;">Cancel</button>
        <button type="button" class="btn btn-success" id="btn-confirm-replacement-approval" style="padding: 7px 20px; border-radius: 6px; font-weight: 600;">
          Confirm Approval
        </button>
      </div>
    </div>
  </div>
</div>



<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

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

		openModal('order-action-modal');
	});

	// Helper functions to open and close modals cleanly across Bootstrap versions
	function openModal(id) {
		var el = document.getElementById(id);
		if (!el) return;
		if (typeof $.fn !== 'undefined' && typeof $.fn.modal !== 'undefined') {
			$('#' + id).modal('show');
			return;
		}
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			try {
				var inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
				inst.show();
				return;
			} catch(e) {}
		}
		// Pure DOM fallback
		el.classList.add('show');
		el.style.display = 'block';
		document.body.classList.add('modal-open');
		if (!$('.modal-backdrop').length) {
			$('<div class="modal-backdrop fade show"></div>').appendTo('body');
		}
	}

	function closeModal(id) {
		var el = document.getElementById(id);
		if (!el) return;
		if (typeof $.fn !== 'undefined' && typeof $.fn.modal !== 'undefined') {
			$('#' + id).modal('hide');
			return;
		}
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			try {
				var inst = bootstrap.Modal.getInstance(el);
				if (inst) {
					inst.hide();
					return;
				}
			} catch(e) {}
		}
		// Pure DOM fallback
		el.classList.remove('show');
		el.style.display = 'none';
		document.body.classList.remove('modal-open');
		$('.modal-backdrop').remove();
	}

	// Trigger replacement approval popup
	$(document).on('click', '.approve-item', function () {
		var itemId = $(this).data('item-id');
		$('#selected_replacement_item_id').val(itemId);

		// Reset to default (Own Replacement)
		$('#rep_opt_own').prop('checked', true);
		$('.replacement-option-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
		$('#opt-card-own').css({'border-color': '#28a745', 'background': '#f9fff9'});

		// Close the details modal first to prevent modal backdrop and focus trap conflicts
		closeModal('order-action-modal');
		setTimeout(function () {
			openModal('replacement-approval-modal');
		}, 250);
	});

	// Return to details modal if approval modal is closed or cancelled
	$(document).on('click', '#replacement-approval-modal .btn-secondary, #replacement-approval-modal .btn-close', function () {
		closeModal('replacement-approval-modal');
		setTimeout(function () {
			openModal('order-action-modal');
		}, 250);
	});

	// Selection styling on click
	$(document).on('click', '.replacement-option-card', function () {
		var radio = $(this).find('input[type="radio"]');
		radio.prop('checked', true);
		$('.replacement-option-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
		if (radio.val() === '1') {
			$(this).css({'border-color': '#28a745', 'background': '#f9fff9'});
		} else {
			$(this).css({'border-color': '#6f42c1', 'background': '#faf7ff'});
		}
	});

	$(document).on('change', 'input[name="replacement_type_choice"]', function () {
		$('.replacement-option-card').css({'border-color': '#e2e8f0', 'background': '#fff'});
		if ($(this).val() === '1') {
			$('#opt-card-own').css({'border-color': '#28a745', 'background': '#f9fff9'});
		} else {
			$('#opt-card-ym').css({'border-color': '#6f42c1', 'background': '#faf7ff'});
		}
	});

	// Confirm approval selection
	$(document).on('click', '#btn-confirm-replacement-approval', function () {
		var itemId = $('#selected_replacement_item_id').val();
		var selectedStatus = parseInt($('input[name="replacement_type_choice"]:checked').val() || 1);

		if (!itemId) {
			alert('No replacement item selected.');
			return;
		}

		var typeLabel = (selectedStatus === 1) ? 'Own Replacement (Merchant Panel)' : 'YM Replacement (Admin Panel)';
		if (!confirm('Confirm approval as ' + typeLabel + '?')) {
			return;
		}

		closeModal('replacement-approval-modal');
		updateItemStatus(itemId, selectedStatus);
	});

	// Reject item handler
	$(document).on('click', '.reject-item', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Reject this replacement request item?")) {
			updateItemStatus(itemId, 4); // 4 = Rejected
		}
	});

	// Complete Own Replacement handler
	$(document).on('click', '.complete-own', function () {
		var itemId = $(this).data('item-id');
		if (confirm("Mark Own Replacement as completed?")) {
			updateItemStatus(itemId, 5); // 5 = Replaced (Own)
		}
	});

	// AJAX function for per-item status updates
	function updateItemStatus(itemId, status) {
		var repType = (status === 1 || status === 5) ? 'own' : ((status === 2 || status === 6) ? 'ym' : '');
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
					location.reload();
				} else {
					alert(response.error || 'Failed to update status');
				}
			},
			error: function (xhr, status, error) {
				console.error('AJAX Error:', xhr.responseText);
				alert('An error occurred while updating status. Please try again.');
			}
		});
	}
</script>
