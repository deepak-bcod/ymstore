<div class="modal-header">
    <h4 class="head-name"><?= lang('assign_new_delivery_attempt') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="assign_delivery_form">

   
    <div class="form-group">
        <label><?= lang('delivery_date') ?></label>
        <input type="date" name="delivery_date" id="delivery_date" class="form-control" required>
    </div>

    <!-- Delivery Person -->
    <label for="delivery_person"><?= lang('enter_delivery_person') ?> <span class="required">*</span></label>
    <input type="text" name="delivery_person" id="delivery_person" class="form-control" placeholder="<?= lang('enter_delivery_person_placeholder') ?>" required>

    <div class="form-group">
        <label for="remarks"><?= lang('delivery_remarks') ?></label>
        <textarea class="form-control" name="remarks" id="remarks" placeholder="<?= lang('delivery_remarks_placeholder') ?>" maxlength="250"></textarea>
    </div>

    <!-- hidden fields -->
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="attempt_no" value="<?= $attempt_no ?>">

    <div class="clear pad-bt-20"></div>

    <button type="button" class="btn btn-success" onclick="submitAssignDeliveryForm()"><?= lang('assign_delivery') ?></button>
</form>


<script>
function submitAssignDeliveryForm() {
    var form = $('#assign_delivery_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/AssignNewDelivery",
        type: "POST",
        data: form.serialize(),
        success: function(response) {
            $('#FBCUserCommonModal').modal('hide');
            var res = jQuery.parseJSON(response);

            swal({
                title: res.status == 200 ? "Success" : "Error",
                icon: res.status == 200 ? "success" : "error",
                text: res.message,
                buttons: true,
            }, function() {
                if (res.status == 200) location.reload();
            });
        }
    });
    return false;
}
</script>
