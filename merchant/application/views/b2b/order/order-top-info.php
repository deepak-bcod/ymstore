<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name"><?=lang('order_details')?> </h1>
  <div class="float-right">
  <?php if ($OrderData->parent_id>0) {?>
		<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?=lang('view_split_orders')?>"><?=lang('view_split_orders')?> </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php if (isset($SplitOrderIds) && count($SplitOrderIds)>0) {
			foreach ($SplitOrderIds as $spo) {?>
			<a id="" class="m-2 dropdown-item" href="<?php echo base_url() ?>b2b/split-order/detail/<?php echo $spo->order_id; ?>"><?php echo $spo->increment_id; ?></a>
			<div class="dropdown-divider"></div>
		<?php }
			} ?>
		</div>

  <?php } ?>

	<?php 
	$CI =& get_instance();
    $CI->load->model('B2BOrdersModel');
	
	if ($current_tab=='order' || $current_tab=='split-order') { ?>
	<?php if($OrderData->status == 0){ ?>
	 <button class="purple-btn blue-color" 
            type="button" 
            onclick="markAsProcessing('<?= $OrderData->order_id ?>')">
        <?=lang('processing')?>
    </button>
	<?php } ?>
	<?php if($OrderData->status == 7 && $OrderData->shipment_type == 1){ ?>
	   <button class="purple-btn bg-green" 
            type="button" 
            onclick="markAsCollected('<?= $OrderData->order_id ?>')">
        <?=lang('mark_as_collected')?>
    </button>
	<?php } ?>
	<?php
	 $deliveryAttempts = $CI->B2BOrdersModel->getMultiDataById('b2b_orders_delivery_details', array('order_id' => $OrderData->order_id), '', 'id', 'ASC');
	 if (!empty($deliveryAttempts)) : 
        $lastAttempt = end($deliveryAttempts);
     endif; 
	 ?>
		<?php if(($OrderData->status == 4 || $OrderData->status == 5 || $OrderData->status == 6 )  && $OrderData->shipment_type == 1  && $lastAttempt->delivery_status != 2 && $lastAttempt->delivery_status != 4){ ?>
	   <button class="purple-btn bg-green" 
            type="button" 
            onclick="markAsDelivered('<?= $OrderData->order_id ?>')">
        <?=lang('mark_as_delivered')?>
    </button>
	<?php } ?>
	<?php if($OrderData->status == 1 && $OrderData->shipment_type == 1){ ?>
	<button class="purple-btn blue-color" id="initiate-shipment-btn" onclick="InitiateShipment(<?php echo $OrderData->order_id; ?>,'<?php echo $OrderData->increment_id; ?>',<?php echo $OrderData->publisher_id; ?> );"><?=lang('ship_order')?></button>
	<?php } ?>

    <?php if($OrderData->status == 1 && $OrderData->shipment_type == 2){ ?>
   <button class="purple-btn blue-color" 
            type="button" 
            onclick="generatePickup('<?= $OrderData->order_id ?>')">
        <?=lang('generate_pickup')?>
    </button>
	<?php } ?>
	<?php } elseif ($current_tab=='shipped-order') { ?>
	<?php } ?>

	</div>
</div>
<script>
const LANG = {
    areYouSure: "<?= lang('are_you_sure'); ?>",
    yes: "<?= lang('yes'); ?>",
    cancel: "<?= lang('cancel'); ?>",
    success: "<?= lang('success'); ?>",
    error: "<?= lang('error'); ?>",
    somethingWrong: "<?= lang('something_went_wrong'); ?>",
    markDelivered: "<?= lang('confirm_mark_delivered'); ?>",
    generatePickup: "<?= lang('confirm_generate_pickup'); ?>",
    markProcessing: "<?= lang('confirm_mark_processing'); ?>",
    markCollected: "<?= lang('confirm_mark_collected'); ?>"
};

function markAsDelivered(orderId) {
    orderStatusUpdate(
        orderId,
        "B2BOrdersController/markDelivered",
        LANG.markDelivered
    );
}

function generatePickup(orderId) {
    orderStatusUpdate(
        orderId,
        "B2BOrdersController/generatePickup",
        LANG.generatePickup
    );
}

function markAsProcessing(orderId) {
    orderStatusUpdate(
        orderId,
        "B2BOrdersController/markProcessing",
        LANG.markProcessing
    );
}

function markAsCollected(orderId) {
    orderStatusUpdate(
        orderId,
        "B2BOrdersController/markCollected",
        LANG.markCollected
    );
}


function orderStatusUpdate(orderId, url, confirmText) {
    Swal.fire({
        title: LANG.areYouSure,
        text: confirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: LANG.yes,
        cancelButtonText: LANG.cancel
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + url,
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200
                            ? LANG.success
                            : LANG.error,
                        text: res.message,
                        icon: res.status == 200 ? "success" : "error"
                    }).then(() => {
                        if (res.status == 200) {
                            location.reload();
                        }
                    });
                },
                error: function() {
                    Swal.fire(
                        LANG.error,
                        LANG.somethingWrong,
                        "error"
                    );
                }
            });
        }
    });
}
</script>

<script>
/*
function markAsDelivered(orderId) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you really want to mark this order as delivered?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markDelivered",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? "Success" : "Error",
                        text: res.message,
                        icon: res.status == 200 ? "success" : "error"
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}

function generatePickup(orderId) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you really want to generate pickup request?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/generatePickup",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? "Success" : "Error",
                        text: res.message,
                        icon: res.status == 200 ? "success" : "error"
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}

function markAsProcessing(orderId) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you really want to mark this order as processing?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markProcessing",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? "Success" : "Error",
                        text: res.message,
                        icon: res.status == 200 ? "success" : "error"
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}

function markAsCollected(orderId) {
    Swal.fire({
        title: "Are you sure?",
        text: "Do you really want to mark this order as collected?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + "B2BOrdersController/markCollected",
                type: "POST",
                data: { order_id: orderId },
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: res.status == 200 ? "Success" : "Error",
                        text: res.message,
                        icon: res.status == 200 ? "success" : "error"
                    }).then(() => {
                        if (res.status == 200) location.reload();
                    });
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                }
            });
        }
    });
}

*/

function initiateshipmentform(e) {
    if (e) e.preventDefault();

    var form = $('#initiate_shipment_form');

    form.validate({
        ignore: [],
        rules: {
            delivery_person: { required: true },
            delivery_date: { required: true }
        }
    });

    if (!form.valid()) return false;

    $.ajax({
        url: BASE_URL + "B2BOrdersController/InitiateShipment",
        type: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function(response) {
            if (response.status == 200) {
                $('#FBCUserCommonModal').modal('hide');

                Swal.fire({
                    title: "Success",
                    text: response.message,
                    icon: "success"
                }).then(() => {
                    location.reload(true);
                });
            } else {
                Swal.fire("Error", response.message, "error");
            }
        },
        error: function() {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        }
    });

    return false;
}

function InitiateShipment(order_id, inc_id, publisher_id) {
    if (order_id != '') {
        $.ajax({
            url: BASE_URL + "B2BOrdersController/InitiateShipmentPopup",
            type: "POST",
            data: {
                publisher_id: publisher_id,
                order_id: order_id,
                inc_id: inc_id
            },
            success: function(response) {
                if (response != 'error') {
                    $("#FBCUserCommonModal").modal();
                    $("#modal-content").html(response);
                }
            }
        });
    }
}
</script>
