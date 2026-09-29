<?php $this->load->view('common/fbc-user/header');

$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');
// echo "<pre>";
// print_r($orders);
// die;
?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="inner-nav">
		<ul class="nav nav-pills">
			<li class="active">
				<a href="<?php echo base_url() ?>webshop/return-orders/"><?php echo lang('returns_tab_label'); ?></a></li>
			<li >
				<a href="<?php echo base_url() ?>webshop/replacement-orders/"><?php echo lang('replacements_tab_label'); ?></a>
			</li>
		</ul>
		<!-- <div class="filter_order_seaching dataTables_filter">
			<label><input id="global-order-search" type="search" class="form-control form-control-sm" placeholder="Search by order number"></label>
		</div> -->
	</div>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			
			<div class="content-main form-dashboard return-section">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_Returns">

					
						<thead>
							<th><?php echo $this->lang->line('es_order_no'); ?></th>
							<th><?php echo $this->lang->line('ym_order_no'); ?></th>
							<th><?php echo $this->lang->line('purchased_no'); ?></th>
							<th><?php echo $this->lang->line('shopper_name'); ?></th>
							<th><?php echo $this->lang->line('refund_status'); ?></th>
							<th><?php echo $this->lang->line('action'); ?></th>
						</thead>
						<tbody>
							
							<?php if(!empty($orders)) { ?>
								<?php foreach ($orders as $order) {

									$purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
									$purchaseTime = date('h:i A', $order['purchase_timestamp']);
									$purchaseOnFull = $purchaseDate ;

									$returnStatus = (int)$order['return_status'];
									$refundStatus = isset($order['refund_status']) ? (int)$order['refund_status'] : 0;
									$b2bStatus = isset($order['b2b_order_status']) ? (int)$order['b2b_order_status'] : 0;

									// Check item-level statuses if items exist
									$hasRejectedItem = false;
									$hasApprovedItem = false;
									$hasRefundPaidItem = false;
									if (!empty($order['products'])) {
										foreach ($order['products'] as $prod) {
											$ist = isset($prod['item_status']) ? (int)$prod['item_status'] : 0;
											if ($ist === 20) {
												$hasRejectedItem = true;
											} elseif ($ist === 22) {
												$hasApprovedItem = true;
											} elseif ($ist === 17) {
												$hasRefundPaidItem = true;
											}
										}
									}

									if ($returnStatus === 4 || $refundStatus === 1 || $hasRefundPaidItem) {
										$statusText = "Refund Paid";
										$statusClass = "green";
									} else if ($returnStatus === 5 || $returnStatus === 2 || $returnStatus === 20 || $refundStatus === 2 || $hasRejectedItem) {
										$statusText = "Rejected";
										$statusClass = "red";
									} else if ($returnStatus === 3) {
										$statusText = "Approved (Warehouse)";
										$statusClass = "blue";
									} else if ($returnStatus === 1 || $hasApprovedItem) {
										$statusText = "Approved";
										$statusClass = "blue";
									} else {
										$statusText = "Pending";
										$statusClass = "purple";
									}

								?>
								<?php echo $this->lang->line('oroginal_order_id'); ?>
								<tr>
									<td>
										<?php 
										$order_link_id = !empty($order['b2b_order_id']) ? $order['b2b_order_id'] : (!empty($order['webshop_order_id']) ? $order['webshop_order_id'] : '');
										if (!empty($order_link_id)) { ?>
											<a class="link-purple" href="<?= base_url('webshop/b2b/order/detail/' . $order_link_id); ?>" target="_blank"><?= $order['original_order_id']; ?></a>
										<?php } else { ?>
											<?= $order['original_order_id']; ?>
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
											data-id="<?= $order['return_order_id'] ?>"
											data-status="<?= (int)$order['return_status'] ?>"
											data-refund-status="<?= isset($order['refund_status']) ? (int)$order['refund_status'] : 0 ?>"
											data-b2b-status="<?= isset($order['b2b_order_status']) ? (int)$order['b2b_order_status'] : 0 ?>"
											data-products='<?= htmlspecialchars(json_encode($order['products']), ENT_QUOTES, "UTF-8") ?>'>
											
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
<div class="modal fade" id="orderViewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
							
            <div class="modal-body">
                <div id="product-list"></div>
            </div>

           <div class="modal-footer">
    <button class="btn btn-success" id="btn-approve">
        <?php echo $this->lang->line('approve_label') ? $this->lang->line('approve_label') : 'Approve'; ?>
    </button>
    <button class="btn btn-danger" id="btn-reject">
        <?php echo $this->lang->line('reject_label') ? $this->lang->line('reject_label') : 'Reject'; ?>
    </button>
</div>

        </div>
    </div>
</div>


<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script type="text/javascript">
	
	$(document).ready(function () {

		if ($.fn.DataTable.isDataTable('#DataTables_Table_Returns')) {
			$('#DataTables_Table_Returns').DataTable().clear().destroy();
		}

		$('#DataTables_Table_Returns').DataTable({
			responsive: true,
			autoWidth: false,
			pageLength: 10,
			ordering: true,
			searching: true,
			lengthChange: true,
			info: true,
			order: [[0, 'desc']],
			"language": {
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
				}
			},
			columnDefs: [
				{
					targets: -1, 
					orderable: false
				}
			]
		});

	});

	
	// Add 'labels' here as a parameter
function renderProductList(products, labels) {
    if (!Array.isArray(products)) {
        products = [];
    }

    // Fallback in case labels aren't passed correctly
    labels = labels || {
        title: 'Returned Items Details',
        product: 'Product',
        qty: 'Qty',
        price: 'Price (MUR)',
        total: 'Total (MUR)'
    };

    var html = `
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="m-0">${labels.title}</h5>
      <button type="button" class="btn-close" data-dismiss="modal"
        style="border: 0; font-size: 18px; max-width: 800px; background: transparent;">X</button>
    </div>
    <hr>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>${labels.product}</th>
          <th>${labels.qty}</th>
          <th>${labels.price}</th>
          <th>${labels.total}</th>
        </tr>
      </thead>
      <tbody>
  `;

    products.forEach(function(prod) {
        let variantDisplay = '';
        if (prod.product_type === 'conf-simple' && prod.product_variants && prod.product_variants.trim() !== '') {
            try {
                const variants = JSON.parse(prod.product_variants);
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
            } catch (e) { /* ignore */ }
        }

        html += `
      <tr>
        <td><strong>${prod.product_name}${variantDisplay}</strong></td>
        <td>${prod.qty}</td>
        <td>${prod.price}</td>
        <td>${prod.total_price}</td>
      </tr>
    `;
    });

    html += `</tbody></table>`;
    $("#product-list").html(html);
}



	$(document).on('click', '.btn-view-order', function() {
    var products = $(this).data('products');
    var returnStatus = parseInt($(this).data('status'), 10);
    var refundStatus = parseInt($(this).data('refund-status'), 10) || 0;
    var b2bStatus = parseInt($(this).data('b2b-status'), 10) || 0;
    var orderId = $(this).data('id');

    
   var langLabels = {
       
        title: "<?php echo $this->lang->line('return_details') ? $this->lang->line('return_details') : 'Returned Items Details'; ?>",
        product: "<?php echo $this->lang->line('product_name'); ?>",
        qty: "<?php echo $this->lang->line('qty'); ?>",
        price: "<?php echo $this->lang->line('price'); ?>",
        total: "<?php echo $this->lang->line('total'); ?>",
		approve: "<?php echo $this->lang->line('approve_label'); ?>", 
        reject: "<?php echo $this->lang->line('reject_label'); ?>"
    };

    // Pass the labels to the render function
   
    $("#btn-approve").text(langLabels.approve);
    $("#btn-reject").text(langLabels.reject);

    renderProductList(products, langLabels);

    $("#btn-approve").attr('data-id', orderId);
    $("#btn-reject").attr('data-id', orderId);

    // Show/Hide logic: only show approve/reject buttons if status is pending (0)
    var isRejected = (returnStatus === 2 || returnStatus === 5 || returnStatus === 20 || refundStatus === 2);
    var isApproved = (returnStatus === 1 || returnStatus === 3 || returnStatus === 4 || refundStatus === 1);
    if (products && Array.isArray(products)) {
        products.forEach(function(p) {
            var ist = parseInt(p.item_status, 10);
            if (ist === 20) isRejected = true;
            if (ist === 22 || ist === 17) isApproved = true;
        });
    }
    var isPending = (returnStatus === 0 && refundStatus === 0 && !isRejected && !isApproved);

    if (isPending) {
        $("#btn-approve").show();
        $("#btn-reject").show();
    } else {
        $("#btn-approve").hide();
        $("#btn-reject").hide();
    }

    $("#orderViewModal").modal('show');
});



	$("#btn-approve").on('click', function() {
		updateRefundStatus($(this).data('id'), 1); // Completed
	});

	$("#btn-reject").on('click', function() {
		updateRefundStatus($(this).data('id'), 2); // Rejected
	});


	function updateRefundStatus(orderId, status) {

		$.ajax({
			url: "<?= base_url('WebshopOrdersController/return_update_status'); ?>",
			type: 'POST',
			data: { id: orderId, status: status },
			dataType: 'json',
			success: function(res) {
				if (res.success === true) {
					location.reload();
				} else {
					alert("Something went wrong!");
				}

			}
		});
	}

</script>
