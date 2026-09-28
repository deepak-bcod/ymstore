<div class="modal-header">
    <h4 class="head-name"><?= lang('mark_delivery_as_failed') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<form id="mark_failed_form">
    <div class="form-group">
        <label><?= lang('select_reason_for_marking_failed') ?></label>
        <select name="reason_for_attempt_failed" class="form-control" required>
            <option value=""><?= lang('select_reason_placeholder') ?></option>
            <option value="Nobody Answering Call"><?= lang('nobody_answering_call') ?></option>
            <option value="Nobody At Home"><?= lang('nobody_at_home') ?></option>
            <option value="Danger Condition"><?= lang('danger_condition') ?></option>
            <option value="Weather Condition"><?= lang('weather_condition') ?></option>
            <option value="Customer Requested To Reschedule"><?= lang('customer_requested_reschedule') ?></option>
        </select>
    </div>

    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="attempt_no" value="<?= $attempt_no ?>">


    <div class="clear pad-bt-20"></div>

    <button type="button" class="btn btn-danger" onclick="submitMarkFailedForm()"><?= lang('mark_as_failed') ?></button>
</form>

<script>
function submitMarkFailedForm() {
    var form = $('#mark_failed_form');
    $.ajax({
        url: BASE_URL + "B2BOrdersController/MarkAsFailed",
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
                location.reload();
            });
        }
    });
    return false;
}
</script>
