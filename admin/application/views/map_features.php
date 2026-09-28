<?php $this->load->view('common/fbc-user/header'); ?> 

<style>
.sort-arrows span{
    color:#007bff;
    font-weight:bold;
    line-height:16px;
}

.sort-arrows span:hover{
    color:#0056b3;
}
</style>

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <h1 class="head-name">Map Features to Plans</h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('subscription/save_feature_mapping') ?>">
        <div class="table-responsive">
            <table class="table table-bordered table-style text-center">
                <thead>
                    <tr>
                        <th>Feature Name</th>
                        <th>Sorting Order</th>
                        <?php foreach($plans as $plan): ?>
                            <th><?= $plan['name'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                     $totalFeatures = count($features);
                     foreach($features as $index => $feature): ?>
                        <tr>
                            <td><?= $feature['feature_name'] ?></td>
                            <td class="sorting-cell">
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="hidden"
                                        name="feature[<?= $feature['id'] ?>]"
                                        value="<?= $feature['sort_order'] ?>"
                                        class="form-control sort-order"
                                        readonly
                                        style="width:70px;">

                                    <div class="ml-2 sort-arrows">
                                        <span class="move-up"
                                            style="cursor:pointer;display:block;font-size:20px;">
                                            ↑
                                        </span>
                                        <span class="move-down"
                                            style="cursor:pointer;display:block;font-size:20px;">
                                            ↓
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <?php foreach($plans as $plan): ?>
                                <td>
                                    <input type="text" name="mapping[<?= $feature['id'] ?>][<?= $plan['id'] ?>]" 
                                           value="<?= isset($mapped[$feature['id']][$plan['id']]) ? $mapped[$feature['id']][$plan['id']] : '' ?>" 
                                           class="form-control" placeholder="Enter value">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="actions-toolbar mt-3">
            <button type="submit" class="btn btn-primary">Save Mapping</button>
            <a href="<?= base_url('subscription') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script>
function refreshArrows() {

    var rows = $('tbody tr');

    rows.find('.move-up, .move-down').show();

    rows.first().find('.move-up').hide();
    rows.last().find('.move-down').hide();

}

function updateSortOrders() {

    $('tbody tr').each(function(index) {

        $(this)
            .find('.sort-order')
            .val(index + 1);

    });

    refreshArrows();
}

$(document).on('click', '.move-up', function() {

    var row = $(this).closest('tr');
    var prev = row;

    if(prev.length) {
        row.insertBefore(prev);
        updateSortOrders();
    }
});

$(document).on('click', '.move-down', function() {

    var row = $(this).closest('tr');
    var next = row;

    if(next.length) {
        row.insertAfter(next);
        updateSortOrders();
    }
});

$(document).ready(function() {
    updateSortOrders();
});
</script>
