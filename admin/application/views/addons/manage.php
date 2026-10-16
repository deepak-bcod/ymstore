<?php $this->load->view('common/fbc-user/header'); ?>

<style>
    .manage-addon-container {
        margin-left: 240px; 
        padding: 30px;
        background-color: #f8f9fc;
        min-height: 100vh;
    }
    @media (max-width: 768px) {
        .manage-addon-container {
            margin-left: 0; 
        }
    }
    .table thead th {
        background-color: #ffc107 !important;
        color: #000 !important;
        border-color: #dee2e6;
    }
</style>

<div class="manage-addon-container">
    <h2 class="mb-4">Manage Addon</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>SR NO.</th>
                    <th>MERCHANT NAME</th>
                    <th>ADDON SERVICE</th>
                    <th>ADDON NAME</th>
                    <th>DATE</th>
                    <th>AMOUNT</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($manage_addons)): ?>
                    <?php $sr = 1; foreach($manage_addons as $row): ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><?php echo html_escape($row->merchant_name ?? ''); ?></td>
                            <td><?php echo html_escape($row->service_title ?? ''); ?></td>
                            <td><?php echo html_escape($row->addon_name ?? ''); ?></td>
                            <td><?php echo html_escape($row->date ?? ''); ?></td>
                            <td><?php echo number_format($row->amount ?? 0, 2); ?></td>
                            <td>
                                <a href="<?php echo base_url('addons/manage/edit/' . $row->id); ?>" class="text-primary font-weight-bold mr-2">Edit</a>
                                <a href="<?php echo base_url('addons/manage/delete/' . $row->id); ?>" class="text-danger font-weight-bold" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>