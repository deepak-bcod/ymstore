<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name"> ES Order Details </h1>
  <div class="float-right">
	<button type="button" class="purple-btn small-btn bg-blue" 
        onclick="downloadInvoice('<?php echo $OrderData->order_id; ?>')">
    Download Merchant Invoice
</button>
  <?php if ($OrderData->parent_id>0) {?>
		<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View Split Orders">View Split Orders </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php if (isset($SplitOrderIds) && count($SplitOrderIds)>0) {
			foreach ($SplitOrderIds as $spo) {?>
			<a id="" class="m-2 dropdown-item" href="<?php echo base_url() ?>b2b/split-order/detail/<?php echo $spo->order_id; ?>"><?php echo $spo->increment_id; ?></a>
			<div class="dropdown-divider"></div>
		<?php }
			} ?>
		</div>

  <?php } ?>

	<!-- <a class="purple-btn small-btn bg-blue ml-2" type="button" target="_blank" href="<?php echo base_url(); ?>webshop/order/print/<?php echo $OrderData->webshop_order_id; ?>">Print</a> -->

	</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function downloadInvoice(order_id) {
    console.log("Order ID:", order_id);

    if (!order_id || order_id == '') {
        alert('No Order ID found.');
        return;
    }

    var downloadUrl = '<?php echo site_url("B2BOrdersController/download_document/"); ?>/' + order_id;

    // Use AJAX to check if the file exists before redirecting
    $.ajax({
        url: downloadUrl,
        type: 'GET',
        dataType: 'json', // Expect JSON back if there's an error
        success: function(response) {
            // If it returns JSON, it means an error occurred
            if (response && response.status === 'error') {
                alert(response.message);
            }
        },
        error: function(xhr) {
            // If the server successfully starts the file download, 
            // the browser intercepts it and triggers an error/parsererror for JSON, 
            // which actually means the file IS present and downloading.
            if (xhr.status === 200) {
                window.location.href = downloadUrl;
            } else {
                alert('An error occurred while trying to download the invoice.');
            }
        }
    });
}
</script>
