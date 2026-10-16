<?php $this->load->view('common/fbc-user/header'); ?>

<style>
    /* Custom layout fixes for sidebar overlap and table appearance */
    .manage-addon-container {
        margin-left: 240px; /* Adjust width to match your admin sidebar */
        padding: 30px;
        background-color: #f8f9fc;
        min-height: 100vh;
    }
    @media (max-width: 768px) {
        .manage-addon-container {
            margin-left: 0; /* Remove margin on mobile screens */
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
                    <th>ID</th>
                    <th>CATEGORY</th>
                    <th>TITLE</th>
                    <th>FINAL PRICE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($manage_addons)): ?>
                    <?php foreach($manage_addons as $row): ?>
                        <tr>
                            <td><?php echo $row->id; ?></td>
                            <td><?php echo html_escape($row->category_name); ?></td>
                            <td><?php echo html_escape($row->title); ?></td>
                            <td><?php echo number_format($row->final_price, 2); ?></td>
                            <td><?php echo ($row->status == 1) ? 'Active' : 'Inactive'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>