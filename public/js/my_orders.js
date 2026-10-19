function openReturnPopup(order_id, increment_id) {
    $.ajax({
        type: 'POST',
        url: BASE_URL + 'MyOrdersController/getOrderItems',
        dataType: 'json',
        data: {order_id: order_id},
        success: function(response) {

            if(response.status == 1) {

                $("#returnItemsBox").html(response.html);
                $("#returnPopup").modal("show");

                // If all items already have a return/replacement status, disable and hide submit buttons
                if ($("#returnPopup .return_chk").length === 0) {
                    $("#submitReturnRequest, #submitReplacementRequest").prop("disabled", true).hide();
                } else {
                    $("#submitReturnRequest, #submitReplacementRequest").prop("disabled", false).show();
                }

                // Attach button actions
                $("#submitReturnRequest").off("click").on("click", function() {
                    submitReturn(order_id, increment_id);
                });

                $("#submitReplacementRequest").off("click").on("click", function() {
                    submitReplacement(order_id, increment_id);
                });

            } else {
                alert(response.html);
            }
        }
    });
}

function submitReturn(order_id, increment_id) {
    var selected_item = [];
    var common_b2b_order_id = 0;

    $(".return_chk:checked").each(function() {
        var sales_item_id = $(this).data("id");
        var b2b_item_id = $(this).data("b2b-item");
        var qty = parseInt($("#return_qty_" + sales_item_id).val(), 10);
        var b2b_order_id = $(this).data("b2b");
        var publisher_id = $(this).data("publisher");

        if (!b2b_order_id || b2b_order_id === "0") {
            b2b_order_id = $("#b2b_order_id_" + sales_item_id).val();
        }
        if (!publisher_id || publisher_id === "0") {
            publisher_id = $("#publisher_id_" + sales_item_id).val();
        }
        if (b2b_order_id && b2b_order_id !== "0" && !common_b2b_order_id) {
            common_b2b_order_id = b2b_order_id;
        }

        if(qty > 0){
            selected_item.push({
                item_id: (b2b_item_id && b2b_item_id !== "0") ? b2b_item_id : sales_item_id,
                sales_order_item_id: sales_item_id,
                qty_return: qty,
                qty_ordered: qty,
                b2b_order_id: b2b_order_id || 0,
                publisher_id: publisher_id || 0
            });
        }
    });

    if(selected_item.length === 0){
        swal("Error", "Please select at least one item.", "error");
        return;
    }

    var $btn = $("#submitReturnRequest");
    $btn.prop("disabled", true);

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'MyOrdersController/addReturnRequest',
        dataType: 'json',
        data: {
            order_id: order_id,
            increment_id: increment_id,
            selected_item: selected_item,
            b2b_order_id: common_b2b_order_id
        },
        success: function(response){
            $btn.prop("disabled", false);
            if(response.flag == 1){
                swal("Success", response.msg || "Return Requested!", "success");
                $("#returnPopup").modal("hide");
                location.reload();
            } else {
                swal("Error", response.msg || "Unable to submit return request.", "error");
            }
        },
        error: function(){
            $btn.prop("disabled", false);
            swal("Error", "Server error while submitting return request.", "error");
        }
    });
}

function submitReplacement(order_id, increment_id) {
    var selected_item = [];
    var common_b2b_order_id = 0;

    $(".return_chk:checked").each(function() {
        var sales_item_id = $(this).data("id");
        var b2b_item_id = $(this).data("b2b-item");
        var qty = parseInt($("#return_qty_" + sales_item_id).val(), 10);
        var b2b_order_id = $(this).data("b2b");
        var publisher_id = $(this).data("publisher");

        if (!b2b_order_id || b2b_order_id === "0") {
            b2b_order_id = $("#b2b_order_id_" + sales_item_id).val();
        }
        if (!publisher_id || publisher_id === "0") {
            publisher_id = $("#publisher_id_" + sales_item_id).val();
        }
        if (b2b_order_id && b2b_order_id !== "0" && !common_b2b_order_id) {
            common_b2b_order_id = b2b_order_id;
        }

        if(qty > 0){
            selected_item.push({
                item_id: (b2b_item_id && b2b_item_id !== "0") ? b2b_item_id : sales_item_id,
                sales_order_item_id: sales_item_id,
                qty_return: qty,
                qty_ordered: qty,
                b2b_order_id: b2b_order_id || 0,
                publisher_id: publisher_id || 0
            });
        }
    });

    if (selected_item.length === 0) {
        swal("Error", "Please select at least one item.", "error");
        return;
    }

    var $btn = $("#submitReplacementRequest");
    $btn.prop("disabled", true);

    $.ajax({
        type: 'POST',
        url: BASE_URL + 'MyOrdersController/addReplacementRequest',
        dataType: 'json',
        data: {
            order_id: order_id,
            increment_id: increment_id,
            selected_item: selected_item,
            flag: 'replacement',
            b2b_order_id: common_b2b_order_id
        },
        success: function(response) {
            $btn.prop("disabled", false);
            if (response.flag == 1) {
                swal("Success", response.msg || "Replacement Requested!", "success");
                $("#returnPopup").modal("hide");
                location.reload();
            } else {
                swal("Error", response.msg || "Unable to submit replacement request.", "error");
            }
        },
        error: function() {
            $btn.prop("disabled", false);
            swal("Error", "Server error while submitting replacement request.", "error");
        }
    });
}


$(document).on("change", ".return_chk", function () {
    var id = $(this).data("id");
    $("#return_qty_" + id).prop("disabled", !$(this).is(":checked"));
});





$(document).on("click", ".modalLink", function () {
	var passedID = $(this).data('id');
	$("#order_id").val(passedID);
});

$(document).on("click", ".tracking_details_btn", function () {

	var order_id = $(this).attr('orderid');
	$('#tracking_details_div_'+order_id).toggleClass('d-none');

	$.ajax({
		type: 'POST',
		url: BASE_URL+'MyOrdersController/show_tracking_details',
		dataType: 'html',
		data: {order_id:order_id},
		beforeSend: function(){
			// $('#ajax-spinner').show();
		},
		success: function(response){
			$('#tracking_details_div_'+order_id).html(response);
			
		}
	});

});