<?php $this->load->view('common/fbc-user/header'); ?>


<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name"> Payouts </h1>
				
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<div class="d-flex justify-content-end align-items-center mb-2">
					<div class="w-25" style="margin-right:30px">
						<input type="text" id="search_order" class="form-control form-control-sm mb-0 me-3" placeholder="Search">
					</div>

					<div>
						<button id="bulk_to_pay" class="btn btn-success btn-sm">To Pay</button>
						<!-- <button id="bulk_hold" class="btn btn-warning btn-sm">Hold</button> -->
					</div>
				</div>


				<div class="table-responsive text-center" style="overflow-x: auto; white-space: nowrap;">

					<table class="table table-bordered table-style payouts-table" id="DataTables_Table_B2BOrders">

						<thead>
						<tr>
							<th><br><input type="checkbox" id="select_all"></th>
                            <th>Action</th>
							<th>Status</th>
							<th>ES <br>Order No.</th>
							<th>YM <br>Order No.</th>
							<th>Merchant<br>Name</th>
							<th>Purchased</th>
							<th>Total<br> Sales</th>
							<th>Fees</th>
							<th>Merchant<br>Amount</th>
							
						</tr>
						</thead>
						<tbody>

							<?php if(!empty($orders)) { ?>
							<?php foreach ($orders as $order) {

								$purchaseDate = date('d/m/Y', $order['purchase_timestamp']);
								//$purchaseTime = date('h:i A', $order['purchase_timestamp']);
								$purchaseOnFull = $purchaseDate;
                                $order_total = $order['subtotal'] + $order['ym_charges'];


								$publisher_commission_per = 4;
								$whuso_income = ($publisher_commission_per / 100) * ($order['subtotal']);
								$Payable_Amount = ($order['subtotal'] - $whuso_income);

								// payout status rules
								$today = strtotime(date('Y-m-d'));
								$orderDate = $order['purchase_timestamp'];
								$daysDiff = floor(($today - $orderDate) / (60*60*24));
								$payout_status = $order['payout_status'];

								// Rule: If Order Status = 2, 8, or 9 → Payout must NOT be on hold, Payout should be Active, Admin can process payout
								if (in_array((int)$order['status'], [2, 8, 9], true)) {
									if ($payout_status == 3) {
										$payout_status = 1;
									}
								}

								if($payout_status == 4){
									$status = "Paid";
								} elseif($payout_status == 3){
									$status = "On Hold";
								} elseif($payout_status == 2){
									$status = "Requested";
								} elseif(in_array((int)$order['status'], [2, 8, 9, 17, 19, 20, 21], true) || $daysDiff >= 1){
									$status = "To Pay";
								} elseif($daysDiff == 0){
									$status = "Pending";
								} else {
									$status = "Pending";
								}

								$is_allowed = isset($order['is_payout_allowed']) ? (bool)$order['is_payout_allowed'] : true;
								if (in_array((int)$order['status'], [2, 8, 9], true)) {
									$is_allowed = true;
									$blocked_reason = '';
								} else {
									$blocked_reason = !empty($order['payout_blocked_reason']) ? $order['payout_blocked_reason'] : ($payout_status == 3 ? 'On Hold' : '');
								}
								$is_refunded = !empty($order['is_refunded']);

								$disableCheckbox = ($payout_status == 4 || $payout_status == 3 || !$is_allowed) ? 'disabled' : '';

							?>

							<tr class="<?= $disableCheckbox ? 'table-secondary' : '' ?>">
    <td>
        <input type="checkbox" class="order_select" value="<?= $order['order_id']; ?>" 
               data-publisher="<?= $order['publisher_id']; ?>" 
               data-shipping_charge="<?= $order['ym_charge']; ?>" <?= $disableCheckbox; ?>>
    </td>
    
    <td>
        <?php if ($payout_status == 4) { ?>
            -
        <?php } elseif ($payout_status == 3 || !$is_allowed) { ?>
            -
        <?php } elseif ($status == "To Pay" || $status == "Requested") { ?>
            <button type="button" class="btn btn-primary btn-sm single-to-pay-btn" 
                    data-id="<?= $order['order_id']; ?>" 
                    data-publisher="<?= $order['publisher_id']; ?>" 
                    data-shipping_charge="<?= $order['ym_charge']; ?>">
                To Pay
            </button>
        <?php } else { echo "-"; } ?>
    </td>
     <td>
        <?php if ($payout_status == 3 || !$is_allowed) { ?>
            <span class="text-danger font-weight-bold" title="<?= htmlspecialchars($blocked_reason ?: 'On Hold'); ?>"><?= htmlspecialchars($blocked_reason ?: 'On Hold'); ?></span>
        <?php } else { ?>
            <span class="<?= strtolower($status); ?>"><?= $status; ?></span>
        <?php } ?>
    </td>


    

    <td><?= $order['increment_id']; ?></td>

    <td><?= $order['shopper_order_id']; ?></td>

    <td><?= $order['publication_name']; ?></td>

    <td><?= $purchaseDate; ?></td>

    <td>MUR <?= number_format($order_total, 2); ?></td>

    <td>MUR <?= number_format($whuso_income, 2); ?></td>

    <td data-b2b-status="<?= $order['status']; ?>">
        MUR <?= number_format($Payable_Amount, 2); ?>
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

<!-- Delivery Details Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Payment Details</h5>
        <button type="button" class="btn-close" data-dismiss="modal" style="border: 0; font-weight: bold; background: transparent;">X</button>
      </div>

      <div class="modal-body">

        <!-- Payment Details Section -->
        <div id="paymentDetailsWrapper"></div>

        <hr>

        <!-- Bank Details Section -->
        <h5>Bank Details</h5>

        <div class="row g-2 mt-2">
          <div class="col-md-4">
            <label class="form-label">Bank Name*</label>
            <input type="text" class="form-control" id="bankName" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Bank Branch No.*</label>
            <input type="text" class="form-control" id="branchNo">
          </div>

          <div class="col-md-4">
            <label class="form-label">Beneficiary Name*</label>
            <input type="text" class="form-control" id="beneficiaryName" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Bank Swift Code*</label>
            <input type="text" class="form-control" id="swiftCode">
          </div>
          <div class="col-md-4">
                <label class="form-label">Beneficiary Acc No.*</label>
                <input type="text" class="form-control" id="beneficiaryAccNo" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Bank Address*</label>
            <input type="text" class="form-control" id="bank_address">
          </div>
          <div class="col-md-4">
            <label class="form-label">IBan*</label>
            <input type="text" class="form-control" id="iban">
          </div>

         	 <div class="col-md-4">
				<label class="form-label">Comment</label>
				<textarea class="form-control" id="comment" rows="2" placeholder="Enter comment"></textarea>
			</div>


          <!-- <div class="col-md-4">
            <label class="form-label">Payment Mode*</label>
            <select class="form-select" id="paymentMode">
              <option value="offline">Offline Paid</option>
              <option value="online">Online</option>
            </select>
          </div> -->
        </div>

        <input type="hidden" id="selectedOrderIds">

        <hr>

        <div class="d-flex justify-content-between fw-bold">
			<div class="">
				<label class="form-label fw-bold">Transaction ID</label>
				 <input type="text" class="form-control" id="transactionId" required>
			</div>
			<div class="">
				<span id="totalPayableText"></span>
			</div>
		</div>

      </div>

      	<div class="modal-footer">
        	<button class="btn btn-success" id="payConfirmBtn">PAID</button>
      	</div>

    </div>
  </div>
</div>

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
				emptyTable: "No records found",
				zeroRecords: "No matching records found"
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
			$(".single-to-pay-btn, .single-hold-btn").attr("disabled", true);
		} else {
			$(".single-to-pay-btn, .single-hold-btn").attr("disabled", false);
		}
	});
	
    // Select / Unselect All checkbox
	$(document).on("change", "#select_all", function() {
    let isChecked = $(this).is(":checked");
    $(".order_select:not(:disabled)").prop("checked", isChecked).trigger("change");
});

    function renderPaymentRows(orderRows) {
        let html = `<div class="row mb-2">`;

        orderRows.forEach(row => {
            html += `
            <div class="col-6 mb-2">
                <div><b>B2B Order no :</b> ${row.b2b}</div>
                <div><b>Processing Fees :</b> ${row.fee}</div>
            </div>
            <div class="col-6 mb-2">
                <div><b>Order Amt :</b> ${row.amount}</div>
                <div><b>YM Delivery :</b> MUR ${row.shipping_charge.toFixed(2)}</div>
                <div><b>Total Payable :</b> ${row.payable}</div>
                <div id="refund-${row.order_id}" class="text-danger fw-bold"></div>
            </div>`;
        });

        html += `</div>`;
        return html;
    }

// OPEN MODAL + FETCH REFUND
    function openPayModal(rows, ids) {
        $("#paymentDetailsWrapper").html(renderPaymentRows(rows));
        $("#selectedOrderIds").val(ids.join(","));

        let totalPayable = rows.reduce((sum, r) => {
            const num = parseFloat(String(r.payable).replace(/[^0-9.-]+/g, ''));
            return sum + (isNaN(num) ? 0 : num);
        }, 0);

        let refundTotal = 0;

        let refundCalls = rows.map(row => {
            return $.ajax({
                url: BASE_URL + "B2BOrdersController/get_refund_amount",
                type: "POST",
                data: { order_id: row.order_id },
                dataType: "json",
                success: function (res) {

                    // ⭐ Correct real status check (17 = Refund Paid)
                    if (res.status === 200 && parseInt(row.status) === 17) {

                        let refundAmt = parseFloat(res.amount);
                        refundTotal += refundAmt;

                        $("#refund-" + row.order_id).html(
                            `(less refund amount): MUR ${refundAmt.toFixed(2)}`
                        );
                    }
                }
            });
        });

        $.when.apply($, refundCalls).then(function () {
            let finalTotal = totalPayable - refundTotal;

            $("#totalPayableText").text(
                `Total Amount: MUR ${finalTotal.toFixed(2)}`
            );

            new bootstrap.Modal(document.getElementById("payModal")).show();
        });
    }

    // Single Pay
    //$(document).on("click", ".single-to-pay-btn", function () {
     $(document).on("click", ".single-to-pay-btn", function () {
    let row = $(this).closest("tr");
    let orderId = $(this).data("id");
    let publisherId = $(this).data("publisher");
    let shipping_charge = $(this).data("shipping_charge");

    let data = [{
    // Index 4 = YM Order No. (B2B No)
    b2b: row.find("td:eq(3)").text().trim(),           
    
    // Index 8 = Fees
    fee: row.find("td:eq(8)").text().trim(),           
    
    // Index 7 = Total Sales (Order Amt)
    amount: row.find("td:eq(7)").text().trim(),        
    
    // Index 9 = Merchant Amount (Total Payable)
    payable: row.find("td:eq(9)").text().trim(),       
    
    order_id: orderId,
    shipping_charge: shipping_charge,
    status: row.find("td:eq(9)").data("b2b-status")
}];
        $.ajax({
            url: BASE_URL + "B2BOrdersController/get_publisher_bank_details",
            type: "POST",
            data: { publisher_id: publisherId },
            dataType: "json",
            success: function (res) {
                if (res.status === 200) {

    $("#bankName").val(res.data.bank_name || '');
    $("#branchNo").val(res.data.bank_branch_number || '');
    $("#beneficiaryName").val(res.data.beneficiary_name || '');
    $("#swiftCode").val(res.data.beneficiary_ifsc_code || '');

    // ADD THIS
    $("#beneficiaryAccNo").val(res.data.beneficiary_acc_no || '');

    $("#bank_address").val(res.data.bank_address || '');
    $("#iban").val(res.data.iban || '');

} else {

    $("#bankName, #branchNo, #beneficiaryName, #swiftCode, #beneficiaryAccNo, #bank_address, #iban").val('');
}
                openPayModal(data, [orderId]);
            }
        });
    });

    // Bulk Pay
    //$("#bulk_to_pay").click(function () {
    $("#bulk_to_pay").click(function () {
    let rows = [];
    let ids = [];
    let publishers = [];

    $(".order_select:checked").each(function () {
        let row = $(this).closest("tr");
        let shipping_charge = $(this).data("shipping_charge");

        ids.push($(this).val());
        publishers.push($(this).data("publisher"));

       rows.push({
    b2b: row.find("td:eq(4)").text().trim(),     // Changed from eq(1) to eq(4)
    fee: row.find("td:eq(8)").text().trim(),     // Changed from eq(6) to eq(8)
    amount: row.find("td:eq(7)").text().trim(),  // Changed from eq(5) to eq(7)
    payable: row.find("td:eq(9)").text().trim(), // Changed from eq(7) to eq(9)
    order_id: $(this).val(),
    shipping_charge: shipping_charge,
    status: row.find("td:eq(9)").data("b2b-status")
});
    });

        if (ids.length === 0) {
            alert("Select at least 1 order");
            return;
        }

        let first = publishers[0];
        let allSame = publishers.every(pub => pub == first);

        if (!allSame) {
            alert("Selected orders belong to different publishers.\nPlease select orders of same publisher only.");
            return;
        }

        $.ajax({
            url: BASE_URL + "B2BOrdersController/get_publisher_bank_details",
            type: "POST",
            data: { publisher_id: first },
            dataType: "json",
            success: function (res) {
                if (res.status === 200) {

    $("#bankName").val(res.data.bank_name || '');
    $("#branchNo").val(res.data.bank_branch_number || '');
    $("#beneficiaryName").val(res.data.beneficiary_name || '');
    $("#swiftCode").val(res.data.beneficiary_ifsc_code || '');

    // ADD THIS
    $("#beneficiaryAccNo").val(res.data.beneficiary_acc_no || '');

    $("#bank_address").val(res.data.bank_address || '');
    $("#iban").val(res.data.iban || '');

} else {

    $("#bankName, #branchNo, #beneficiaryName, #swiftCode, #beneficiaryAccNo, #bank_address, #iban").val('');
}
                openPayModal(rows, ids);
            }
        });
    });

    // Pay Confirm
    $("#payConfirmBtn").click(function () {

        let ids = $("#selectedOrderIds").val();

        let bankName = $("#bankName").val();
        let branchNo = $("#branchNo").val();
        let beneficiary = $("#beneficiaryName").val();
        let beneficiaryAccNo = $("#beneficiaryAccNo").val();
        let swift = $("#swiftCode").val();
        let bank_address = $("#bank_address").val();
        let iban = $("#iban").val();

        let comment = $("#comment").val();
        let utr = $("#transactionId").val();
        let total_amount = $("#totalPayableText").text();

        $.ajax({
            url: BASE_URL + "B2BOrdersController/pay_payout",
            type: "POST",
            data: {
                ids: ids,
                bank_name: bankName,
                branch_no: branchNo,
                beneficiary: beneficiary,
                beneficiary_acc_no: beneficiaryAccNo,
                bank_address: bank_address,
                iban: iban,
                swift: swift,
                comment: comment,
                utr_no: utr,
                total_amount: total_amount
            },
            success: function (res) {
                location.reload();
            }
        });
    });


	$("#bulk_hold").click(function(){
		let orders = [];
		$(".order_select:checked").each(function(){ orders.push($(this).val()); });

		if(orders.length === 0){
			alert("Select at least 1 order");
			return;
		}

		Swal.fire({
			title: "Hold selected payouts?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Hold All",
			cancelButtonText: "Cancel"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: BASE_URL + "B2BOrdersController/hold_payout_bulk",
					type: "POST",
					data: {order_ids: orders},
					success: function (response) {
						location.reload();
					}
				});
			}
		});
	});

	
	$(document).on("click", ".single-hold-btn", function(){

		let id = $(this).data("id");

		Swal.fire({
			title: "Hold this payout?",
			text: "The payout status will be changed to HOLD.",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Yes, Hold it",
			cancelButtonText: "Cancel"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: BASE_URL + "B2BOrdersController/hold_payout",
					type: "POST",
					data: {order_id: orderId},
					success: function (response) {
						location.reload();
					}
				});
			}
		});
	});



</script>
<?php $this->load->view('common/fbc-user/footer'); ?>