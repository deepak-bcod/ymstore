<?php $this->load->view('common/fbc-user/header'); ?>

<script>
    var LANG = "<?= $this->session->userdata('site_lang') ?? 'english'; ?>";
</script>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name"> <?php echo lang('payouts'); ?> </h1>
				
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<div class="w-25">
						<input type="text" id="search_order" class="form-control form-control-sm" placeholder="<?php echo lang('search_placeholder'); ?>">
					</div>

					<div>
						<button id="bulk_request" class="btn btn-success btn-sm"><?php echo lang('request'); ?></button>
						<!-- <button id="bulk_hold" class="btn btn-warning btn-sm">Hold</button> -->
					</div>
				</div>


				<div class="table-responsive text-center" style="overflow-x: auto; white-space: nowrap;">

					<table class="table table-bordered table-style" id="DataTables_Table_B2BOrders">

						<thead>
						<tr>
							<th><?php echo lang('select'); ?> <br><input type="checkbox" id="select_all"></th>
							<th><?php echo lang('action'); ?></th>
							<th><?php echo lang('payment_status'); ?></th>
							
							<th><?= str_replace('|', '<br>', $this->lang->line('merchant_order_no')); ?></th>
							<th><?= str_replace('|', '<br>', $this->lang->line('shopper_order_no')); ?></th>
							<!-- <th><?php echo lang('merchant_name'); ?></th> -->
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
           // $purchaseTime = date('h:i A', $order['purchase_timestamp']); 
            $purchaseOnFull = $purchaseDate . ' ' . $purchaseTime;

            $publisher_commission_per = $order['commision_percent'];
            $whuso_income = ($publisher_commission_per / 100) * ($order['grand_total']);
            $Payable_Amount = ($order['grand_total'] - $whuso_income);

            $today = strtotime(date('Y-m-d'));
            $orderDate = $order['purchase_timestamp'];
            $daysDiff = floor(($today - $orderDate) / (60*60*24));
            $payout_status = $order['payout_status'];

            if($payout_status == 4){
                $status = lang('paid');
            } elseif($payout_status == 3){
                $status = 'On Hold';
            } elseif($payout_status == 2){
                $status = lang('requested');
            } else {
                $status = lang('not_paid');
            }
        ?>
        <tr class="<?= ($order['payout_status'] == 4 || $order['payout_status'] == 3) ? 'table-secondary' : '' ?>">
            <td>
                <input type="checkbox" class="order_select" value="<?= $order['order_id']; ?>" 
                       data-publisher="<?= $order['publisher_id']; ?>" 
                       <?= ($order['payout_status'] == 4 || $order['payout_status'] == 3) ? 'disabled' : ''; ?>>
            </td>

            <td>
                <?php if ($payout_status != 4 && $payout_status != 3) { ?>
                    <button href="javascript:void(0);" 
                       class="single-request-btn" 
                       
                       data-id="<?= $order['order_id']; ?>" 
                       data-publisher="<?= $order['publisher_id']; ?>">
                        <?php echo lang('request'); ?>
                    </button>
                <?php } else { echo "-"; } ?>
            </td>

            <td><span class="status-text"><?= $status; ?></span></td>
			<td><?= $order['increment_id']; ?></td> 

            <td><?= $order['shopper_order_id']; ?></td>

            

            <td><?= $purchaseOnFull; ?></td>

            <td>MUR <?= number_format($order['subtotal'], 2); ?></td>

            <td>MUR <?= number_format($whuso_income, 2); ?></td>

            <td>MUR <?= number_format($Payable_Amount, 2); ?></td>
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

<!-- Delivery Details Modal -->

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- <script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_list.js"></script> -->
<script>
	$(document).ready(function () {
		var table = $('#DataTables_Table_B2BOrders').DataTable({
			lengthMenu: [10, 20, 50, 100],
			pageLength: 20,
			ordering: true,
			searching: true, // DataTables search enabled
			dom: 'lrtip', // remove default search box
			language: {
				lengthMenu: "Show _MENU_ entries",
				emptyTable: "<?= lang('no_records_found'); ?>",
				zeroRecords: "<?= lang('no_records_found'); ?>"
			}
		});

		// Custom search input
		$("#search_order").on("keyup", function () {
			table.search(this.value).draw();
		});
	});

	$(document).on("change", ".order_select, #select_all", function(){

		let selectedCount = $(".order_select:checked").length;

		if(selectedCount > 1){
			$(".single-request-btn, .single-hold-btn").attr("disabled", true);
		} else {
			$(".single-request-btn, .single-hold-btn").attr("disabled", false);
		}
	});

	// Select / Unselect All checkbox
	$(document).on("change", "#select_all", function() {
		let isChecked = $(this).is(":checked");

		// Only toggle enabled checkboxes
		$(".order_select:not(:disabled)").prop("checked", isChecked).trigger("change");
	});


	// function renderPaymentRows(orderRows) {
	// 	let html = `<div class="row mb-2">`;

	// 	orderRows.forEach(row => {
	// 		html += `
	// 		<div class="col-6 mb-2">
	// 			<div><b>B2B Order no :</b> ${row.b2b}</div>
	// 			<div><b>Processing Fees :</b> MUR ${row.fee}</div>
	// 		</div>
	// 		<div class="col-6 mb-2">
	// 			<div><b>Order Amt :</b> MUR ${row.amount}</div>
	// 			<div><b>Total Payable :</b> MUR ${row.payable}</div>
	// 		</div>`;
	// 	});

	// 	html += `</div>`;
	// 	return html;
	// }

	// function openPayModal(rows, ids) {
	// 	$("#paymentDetailsWrapper").html(renderPaymentRows(rows));
	// 	$("#selectedOrderIds").val(ids.join(","));

	// 	let totalPayable = rows.reduce((sum, r) => sum + parseFloat(r.payable), 0);
		
	// 	// $("#transactionId").text("Transaction ID : will generate after");
	// 	$("#totalPayableText").text(`Total Amount: MUR ${totalPayable}`);

	// 	new bootstrap.Modal(document.getElementById("payModal")).show();
	// }

	// ✅ Single Pay
	// $(document).on("click", ".single-request-btn", function () {
	// 	let row = $(this).closest("tr");
	// 	let orderId = $(this).data("id");
	// 	let publisherId = $(this).data("publisher"); // make sure button has data-publisher attribute

	// 	let data = [{
	// 		b2b: row.find("td:eq(1)").text(),
	// 		fee: row.find("td:eq(6)").text(),
	// 		amount: row.find("td:eq(5)").text(),
	// 		payable: row.find("td:eq(7)").text()
	// 	}];

	// 	// ✅ Fetch bank details for that publisher
	// 	$.ajax({
	// 		url: BASE_URL + "B2BOrdersController/get_publisher_bank_details",
	// 		type: "POST",
	// 		data: { publisher_id: publisherId },
	// 		dataType: "json",
	// 		success: function (res) {
	// 			console.log(res);
	// 			if (res.status === 200) {
	// 				$("#bankName").val(res.data.bank_name);
	// 				$("#branchNo").val(res.data.bank_branch_number);
	// 				$("#beneficiaryName").val(res.data.beneficiary_name);
	// 				$("#swiftCode").val(res.data.beneficiary_ifsc_code);
	// 			} else {
	// 				// clear if not found
	// 				$("#bankName, #branchNo, #beneficiaryName, #swiftCode").val("");
	// 			}

	// 			// ✅ Open modal only after setting bank data
	// 			openPayModal(data, [orderId]);
	// 		}
	// 	});
	// });

	// Bulk
	// $("#bulk_to_pay").click(function () {
	// 	let rows = [];
	// 	let ids = [];
	// 	let publishers = [];

	// 	$(".order_select:checked").each(function () {

	// 		let row = $(this).closest("tr");
	// 		ids.push($(this).val());
	// 		publishers.push($(this).data("publisher"));

	// 		rows.push({
	// 			b2b: row.find("td:eq(1)").text(),
	// 			fee: row.find("td:eq(6)").text(),
	// 			amount: row.find("td:eq(5)").text(),
	// 			payable: row.find("td:eq(7)").text()
	// 		});
	// 	});

	// 	if (ids.length === 0) {
	// 		alert("Select at least 1 order");
	// 		return;
	// 	}

	// 	let first = publishers[0];
	// 	let allSame = publishers.every(pub => pub == first);

	// 	if (!allSame) {
	// 		alert("Selected orders belong to different publishers.\nPlease select orders of same publisher only.");
	// 		return;
	// 	}

	// 	// ✅ Get publisher bank details
	// 	$.ajax({
	// 		url: BASE_URL + "B2BOrdersController/get_publisher_bank_details",
	// 		type: "POST",
	// 		data: { publisher_id: first },
	// 		dataType: "json",
	// 		success: function (res) {

	// 			if (res.status === 200) {
	// 				// auto-fill modal bank fields
	// 				$("#bankName").val(res.data.bank_name);
	// 				$("#branchNo").val(res.data.bank_branch_number);
	// 				$("#beneficiaryName").val(res.data.beneficiary_name);
	// 				$("#swiftCode").val(res.data.beneficiary_ifsc_code);
	// 			} else {
	// 				// Clear if no data exists
	// 				$("#bankName, #branchNo, #beneficiaryName, #swiftCode").val("");
	// 			}

	// 			// open modal only after data is set
	// 			openPayModal(rows, ids);
	// 		}
	// 	});
	// });

	// $("#payConfirmBtn").click(function () {

	// 	let ids = $("#selectedOrderIds").val();
	// 	let bankName = $("#bankName").val();
	// 	let branchNo = $("#branchNo").val();
	// 	let beneficiary = $("#beneficiaryName").val();
	// 	let swift = $("#swiftCode").val();
	// 	let comment = $("#comment").val();
	// 	// let mode = $("#paymentMode").val();
	// 	let utr = $("#transactionId").val();
	// 	let total_amount = $("#totalPayableText").text();

	// 	// if (utr.trim() === "") {
	// 	// 	alert("Please enter Transaction / UTR No.");
	// 	// 	return;
	// 	// }

	// 	$.ajax({
	// 		url: BASE_URL + "B2BOrdersController/pay_payout",
	// 		type: "POST",
	// 		data: {
	// 			ids: ids,
	// 			bank_name: bankName,
	// 			branch_no: branchNo,
	// 			beneficiary: beneficiary,
	// 			swift: swift,
	// 			comment: comment,
	// 			// payment_mode: mode,
	// 			utr_no: utr,
	// 			total_amount: total_amount,

	// 		},
	// 		success: function (res) {
	// 			location.reload();
	// 		}
	// 	});
	// });

// Set language variable from backend (example)
// var LANG = "french"; // or "english"

$("#bulk_request").click(function () {
    let orders = [];
    $(".order_select:checked").each(function () { orders.push($(this).val()); });

    // Alert if no order selected
    if (orders.length === 0) {
        alert(LANG === 'french' ? "Sélectionnez au moins 1 commande" : "Select at least 1 order");
        return;
    }

    Swal.fire({
        title: LANG === 'french' ? "Demander un paiement" : "Request Payouts",
        text: LANG === 'french'
            ? "Remarque : 14 jours ne sont pas encore écoulés pour ce paiement. Êtes-vous sûr de vouloir demander ces paiements ?"
            : "Note : 14 days are not completed for this payment. Are you sure you want to request these payments?",
        icon: "warning",
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
                    location.reload();
                }
            });
        }
    });
});


// Single request payout
$(document).on("click", ".single-request-btn", function () {

    let id = $(this).data("id");

    Swal.fire({
        title: LANG === 'french' ? "Demander un paiement" : "Request Payout",
        text: LANG === 'french'
            ? "Remarque : 14 jours ne sont pas encore écoulés pour ce paiement. Êtes-vous sûr de vouloir demander le paiement ?"
            : "Note : 14 days are not completed for this payment. Are you sure you want to request the payment?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: LANG === 'french' ? "Oui, le demander" : "Yes, Request it",
        cancelButtonText: LANG === 'french' ? "Annuler" : "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/request_payout",
                type: "POST",
                data: { order_id: id },
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
});


</script>
<?php $this->load->view('common/fbc-user/footer'); ?>