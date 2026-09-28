<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="inner-nav">
		<ul class="nav nav-pills">
			<li class="active">
				<a href="<?php echo base_url() ?>webshop/return-orders/">Returns</a></li>
			<li >
				<a href="<?php echo base_url() ?>webshop/replacement-orders/">Replacements</a>
			</li>
		</ul>
		<!-- <div class="filter_order_seaching dataTables_filter">
			<label><input id="global-order-search" type="search" class="form-control form-control-sm" placeholder="Search by order number"></label>
		</div> -->
	</div>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			
			<div class="content-main form-dashboard yet-section">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_Returns">
						<thead>
							<tr>
								<!-- <th>Select <br><input type="checkbox" id="select_all"></th> -->
								<th>ES <br>Order No.</th>
								<th>YM <br>Order No.</th>
								<th>Purchased<br>ON</th>
								<th>Customer<br>Name</th>
								<th>Merchant<br>Name</th>
								<th>Refund Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>

							<?php if(!empty($orders)) { ?>
								<?php foreach ($orders as $order) {

									$purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
									//$purchaseTime = date('h:i A', $order['purchase_timestamp']);
									$purchaseOnFull = $purchaseDate;

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
										$statusText = "Merchant Approved";
										$statusClass = "blue";
									} else {
										$statusText = "Pending";
										$statusClass = "purple";
									}

								?>
								<tr>
									<td><?= $order['original_order_id']; ?></td>
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
											data-id="<?= $order['return_order_id'] ?>"
											data-status="<?= (int)$order['return_status'] ?>"
											data-refund-status="<?= isset($order['refund_status']) ? (int)$order['refund_status'] : 0 ?>"
											data-products='<?= htmlspecialchars(json_encode($order['products']), ENT_QUOTES, "UTF-8") ?>'>
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
<!-- Return Order Popup -->
<div class="modal fade" id="orderViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-body">

        <div id="product-list"></div>

        <!-- APPROVED Section Only -->
        <div id="approved-section" style="display:none;">
          <hr>
          <div class="row mb-3">
            <div class="col-md-4">
              <label><strong>Payment Mode</strong></label>
              <input type="text" id="payment_mode" class="form-control" value="offline">
            </div>
            <div class="col-md-8">
              <label><strong>Comments</strong></label>
              <textarea id="comments" class="form-control" rows="2" placeholder=" Search"></textarea>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">

        <button type="button"
                class="btn btn-success"
                id="btn-refund-done"
                style="display:none;">
          Mark as Refund Done
        </button>

      </div>
    </div>
  </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
	
	$(document).ready(function () {

		// Destroy existing instance if already initialized
		if ($.fn.DataTable.isDataTable('#DataTables_Table_Returns')) {
			$('#DataTables_Table_Returns').DataTable().clear().destroy();
		}

		// Initialize DataTable
		$('#DataTables_Table_Returns').DataTable({
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

	function renderProductList(products) {
  var html = `
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="m-0">Returned Items Details </h5>
      <button type="button" class="btn-close" data-dismiss="modal"
        style="border: 0; font-size: 18px; background: transparent;">X</button>
    </div>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price (MUR)</th>
          <th>Total (MUR)</th>
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
				} catch (e) {
					// JSON parse error - ignore
				}
			}

    html += `
      <tr>
        <td>
          <strong>${prod.product_name}${variantDisplay}</strong><br>
          
        </td>
        <td>${prod.qty}</td>
        <td>${prod.price}</td>
        <td>${prod.total_price}</td>
      </tr>
    `;
  });

  html += `
      </tbody>
    </table>
  `;

  $("#product-list").html(html);
}


	$(document).on("click", ".btn-view-order", function () {
		var products = $(this).data("products");
		var status = parseInt($(this).data("status"), 10);
		var refundStatus = parseInt($(this).data("refund-status"), 10) || 0;
		var orderId = $(this).data("id");

		renderProductList(products);

		$("#btn-refund-done").attr("data-id", orderId);
		$("#refund-status-box").html("");

		$("#approved-section").hide();
		$("#btn-refund-done").hide();

		var isItemApproved = false;
		var isItemRejected = false;
		var isItemRefundPaid = false;
		if (products && Array.isArray(products)) {
			products.forEach(function(p) {
				var ist = parseInt(p.item_status, 10);
				if (ist === 20) isItemRejected = true;
				if (ist === 22) isItemApproved = true;
				if (ist === 17) isItemRefundPaid = true;
			});
		}

		if (status === 4 || refundStatus === 1 || isItemRefundPaid) {
			$("#refund-status-box").html(`
				<div class="alert alert-success text-center">
					<strong>Refund Paid</strong>
				</div>
			`);
		}
		else if (status === 2 || status === 5 || status === 20 || refundStatus === 2 || isItemRejected) {
			$("#refund-status-box").html(`
				<div class="alert alert-danger text-center">
					<strong>Rejected</strong>
				</div>
			`);
		}
		else if (status === 1 || status === 3 || isItemApproved) {
			$("#refund-status-box").html(`
				<div class="alert alert-info text-center">
					<strong>Approved</strong> — Ready for Refund Payment
				</div>
			`);

			$("#approved-section").show();
			$("#btn-refund-done").show();
		}

		$("#orderViewModal").modal("show");
	});


	$("#btn-refund-done").click(function () { 
		var orderId = $(this).data("id");
		var paymentMode = $("#payment_mode").val();
		var comments = $("#comments").val();

		$.ajax({
			url: "<?= base_url('WebshopOrdersController/return_update_status'); ?>",
			type: "POST",
			data: { 
				id: orderId, 
				status: 4,
				refund_payment_mode: paymentMode,
				internal_notes: comments
			},
			dataType: "json",
			success: function(res) {
				if (res.success) {
					$("#orderViewModal").modal("hide");
					location.reload();
				} else {
					alert("Failed to update status!");
				}
			}
		});
	});


</script>