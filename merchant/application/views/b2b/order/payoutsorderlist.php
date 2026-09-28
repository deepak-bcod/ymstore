<?php $this->load->view('common/fbc-user/header'); ?>

<script>
    var LANG = "<?= $this->session->userdata('site_lang') ?? 'english'; ?>";
</script>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480 common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name"> <?php echo lang('payouts'); ?> </h1>
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<div class="request-header d-flex justify-content-end align-items-center mb-2">
    <div class="me-2">
        <input type="text"
               id="search_order"
               class="form-control form-control-sm"
               placeholder="<?php echo lang('search_placeholder'); ?>">
    </div>

    <div>
        <button type="button"
                id="bulk_request"
                class="btn btn-info btn-sm">
            <?php echo lang('request'); ?>
        </button>
    </div>
</div>

				<div class="table-responsive text-center" style="overflow-x: auto; white-space: nowrap;">
					<table class="table table-bordered table-style" id="DataTables_Table_MerchantPayouts">
						<thead>
						<tr>
							<th><?php echo lang(''); ?> <br><input type="checkbox" id="select_all"></th>
							<th><?php echo lang('action'); ?></th>
							<th><?php echo lang('payment_status'); ?></th>
							<th><?= str_replace('|', '<br>', $this->lang->line('merchant_order_no')); ?></th>
							<th><?= str_replace('|', '<br>', $this->lang->line('shopper_order_no')); ?></th>
							<th><?php echo lang('purchased_date'); ?></th>
							<th><?php echo lang('total_amount'); ?></th>
							<th><?php echo lang('processing_fees'); ?></th>
							<th><?php echo lang('merchant_amount'); ?></th>
						</tr>
						</thead>
						<tbody>
    <?php if(!empty($orders)) { ?>
        <?php foreach ($orders as $order) {
            $purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
            $purchaseOnFull = $purchaseDate;

            $publisher_commission_per = $order['commision_percent'];
            $whuso_income = ($publisher_commission_per / 100) * ($order['grand_total']);
            $Payable_Amount = ($order['grand_total'] - $whuso_income);
			

            $payout_status = $order['payout_status'];

            // Rule: If Order Status = 2, 8, or 9 → Payout must NOT be on hold, Payout should be Active, Merchant can request
            if (in_array((int)$order['status'], [2, 8, 9], true)) {
                if ($payout_status == 3) {
                    $payout_status = 1;
                }
            }

            if($payout_status == 4){
                $status = lang('paid');
            } elseif($payout_status == 3){
                $status = 'On Hold';
            } elseif($payout_status == 2){
                $status = lang('requested');
            } else {
                $status = lang('not_paid');
            }

            // Return, Refund, and Replacement Rules for Payment Request
            $is_allowed = isset($order['is_payout_allowed']) ? (bool)$order['is_payout_allowed'] : true;
            if (in_array((int)$order['status'], [2, 8, 9], true)) {
                $is_allowed = true;
                $disable_reason = '';
            } else {
                $disable_reason = !empty($order['payout_blocked_reason']) ? $order['payout_blocked_reason'] : ($payout_status == 3 ? 'On Hold' : '');
            }
            $is_refunded = !empty($order['is_refunded']);

            if (isset($LANG) && $LANG == 'french' && !empty($disable_reason)) {
                $disable_reason = "En attente";
            }

            $allow_request = $is_allowed && $payout_status != 3 && $payout_status != 4;
        ?>
        <tr class="<?= ($payout_status == 4 || $payout_status == 3) ? 'table-secondary' : '' ?>">
            <td>
				<input type="checkbox"
					class="order_select"
					value="<?= $order['order_id']; ?>"
					data-publisher="<?= $order['publisher_id']; ?>"
					<?= ($payout_status == 2 || $payout_status == 4 || $payout_status == 3 || !$allow_request) ? 'disabled' : ''; ?>>
			</td>

            <td>
    <?php if ($payout_status == 4) { ?>

        -

    <?php } elseif ($payout_status == 2) { ?>

        <button type="button"
                class="btn btn-secondary btn-sm"
                disabled
                style="opacity: 0.65; cursor: not-allowed;">
            <?= lang('requested'); ?>
        </button>

    <?php } elseif ($payout_status == 3 || !$allow_request) { ?>

        <button type="button"
                class="btn btn-secondary btn-sm"
                disabled
                style="opacity: 0.65; cursor: not-allowed;"
                title="<?= htmlspecialchars($disable_reason ?: 'On Hold'); ?>">
            <?= htmlspecialchars($disable_reason ?: 'On Hold'); ?>
        </button>

    <?php } else { ?>

        <button type="button"
                class="btn btn-outline-primary btn-sm single-request-btn"
                data-id="<?= $order['order_id']; ?>"
                data-publisher="<?= $order['publisher_id']; ?>">
            <?= lang('request'); ?>
        </button>

    <?php } ?>
</td>

            <td><span class="status-text <?= ($payout_status == 3) ? 'text-danger font-weight-bold' : '' ?>"><?= $status; ?></span></td>
			<td><?= $order['increment_id']; ?></td> 
            <td><?= $order['shopper_order_id']; ?></td>
            <td><?= $purchaseOnFull; ?></td>
            <td>MUR <?= number_format($order['subtotal'], 2); ?></td>
            <td>MUR <?= number_format($whuso_income, 2); ?></td>
            <td>MUR <?= number_format($Payable_Amount, 2); ?></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td colspan="9" class="text-center"><?php echo lang('no_records_found'); ?></td></tr>
    <?php } ?>
</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</main>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	$(document).ready(function () {
		// Explicitly clear any legacy DataTables state stored in localStorage for old table IDs
		try {
			localStorage.removeItem('DataTables_DataTables_Table_B2BOrders_/b2b/orders/payouts');
		} catch(e) {}
			$.fn.dataTable.ext.errMode = 'none';

		var table = $('#DataTables_Table_MerchantPayouts').DataTable({
			stateSave: false,
			lengthMenu: [10, 20, 50, 100],
			pageLength: 20,
			ordering: true,
			searching: true,
			dom: 'lrtip',
			columnDefs: [
				{ targets: [0, 1], orderable: false }
			],
			"language": {
				"emptyTable": (typeof DT_LANG !== 'undefined' && DT_LANG.emptyTable) ? DT_LANG.emptyTable : "No data available in table",
				"zeroRecords": (typeof DT_LANG !== 'undefined' && DT_LANG.zeroRecords) ? DT_LANG.zeroRecords : "No matching records found",
				"processing": (typeof DT_LANG !== 'undefined' && DT_LANG.processing) ? DT_LANG.processing : "Processing...",
				"info": (typeof DT_LANG !== 'undefined' && DT_LANG.info) ? DT_LANG.info : "Showing _START_ to _END_ of _TOTAL_ entries",
				"infoEmpty": (typeof DT_LANG !== 'undefined' && DT_LANG.infoEmpty) ? DT_LANG.infoEmpty : "Showing 0 to 0 of 0 entries",
				"infoFiltered": (typeof DT_LANG !== 'undefined' && DT_LANG.infoFiltered) ? DT_LANG.infoFiltered : "(filtered from _MAX_ total entries)",
				"lengthMenu": (typeof DT_LANG !== 'undefined' && DT_LANG.lengthMenu) ? DT_LANG.lengthMenu : "Show _MENU_ entries",
				"search": "",
				"paginate": {
					next: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.next) ? DT_LANG.paginate.next : '<i class="fas fa-angle-right"></i>',
					previous: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.previous) ? DT_LANG.paginate.previous : '<i class="fas fa-angle-left"></i>'
				}
			}
		});

		$("#search_order").on("keyup", function () {
			table.search(this.value).draw();
		});

		$("#select_all").on("click", function () {
			let isChecked = $(this).is(":checked");
			$(".order_select:not(:disabled)").prop("checked", isChecked);
		});
	});

	$(document).on("click", "#bulk_request", function () {
		let orders = [];
		$(".order_select:checked").each(function () { orders.push($(this).val()); });

		if (orders.length === 0) {
			Swal.fire({
				icon: 'warning',
				title: LANG === 'french' ? "Attention" : "Warning",
				text: LANG === 'french' ? "Veuillez sélectionner au moins une commande !" : "Please select at least one order!"
			});
			return;
		}

		Swal.fire({
			title: LANG === 'french' ? "Demander un paiement" : "Request Payouts",
			text: LANG === 'french' ? "Voulez-vous vraiment demander les paiements sélectionnés ?" : "Are you sure you want to request payout for selected orders?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: LANG === 'french' ? "Tout demander" : "Request All",
			cancelButtonText: LANG === 'french' ? "Annuler" : "Cancel"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: BASE_URL + "B2BOrdersController/request_payout_bulk",
					type: "POST",
					data: { order_ids: orders },
					success: function (response) {
						Swal.fire({
							title: LANG === 'french' ? "Succès" : "Success",
							text: LANG === 'french' ? "Demande envoyée avec succès !" : "Payout requested successfully!",
							icon: "success"
						}).then(() => {
							location.reload();
						});
					}
				});
			}
		});
	});

	$(document).on("click", ".single-request-btn", function () {
		let orderId = $(this).data("id");

		Swal.fire({
			title: LANG === 'french' ? "Demander un paiement" : "Request Payout",
			text: LANG === 'french' ? "Voulez-vous vraiment demander le paiement pour cette commande ?" : "Are you sure you want to request payout for this order?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: LANG === 'french' ? "Oui, le demander" : "Yes, Request it",
			cancelButtonText: LANG === 'french' ? "Annuler" : "Cancel"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: BASE_URL + "B2BOrdersController/request_payout",
					type: "POST",
					data: { order_id: orderId },
					success: function (response) {
						Swal.fire({
							title: LANG === 'french' ? "Succès" : "Success",
							text: LANG === 'french' ? "Demande envoyée avec succès !" : "Payout requested successfully!",
							icon: "success"
						}).then(() => {
							location.reload();
						});
					}
				});
			}
		});
	});
</script>
