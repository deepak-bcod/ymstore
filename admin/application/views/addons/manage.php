<?php $this->load->view('common/fbc-user/header'); ?>

<style>
    /* 1. Ensure main container expands properly next to sidebar */
    .manage-addon-container {
        margin-left: 260px; /* Adjust to match your exact sidebar width */
        padding: 20px;
        width: calc(100% - 260px);
    }

    /* 2. Prevent table from creating vertical/horizontal scroll box */
    .table-responsive {
        overflow-x: visible !important;
        overflow-y: visible !important;
    }

    /* 3. Allow text to wrap and fix table layout */
    .table {
        table-layout: auto;
        width: 100% !important;
    }

    .table th, .table td {
        white-space: normal; /* Prevents rows/columns from becoming unnecessarily wide */
        vertical-align: middle;
    }

    .table thead th {
        background-color: #ffc107 !important;
        color: #000 !important;
        border-color: #dee2e6;
    }

    @media (max-width: 768px) {
        .manage-addon-container {
            margin-left: 0;
            width: 100%;
        }
        .table-responsive {
            overflow-x: auto !important; /* Re-enable smooth mobile scrolling */
        }
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
                            <td><?php echo html_escape($row->addon_service_name ?? ''); ?></td>
                            <td><?php echo html_escape($row->addon_name ?? ''); ?></td>
                            <td><?php echo html_escape($row->date ?? ''); ?></td>
                            <td><?php echo number_format($row->amount ?? 0, 2); ?></td>
                            <td>
                                <a href="<?php echo base_url('merchant/addons/download_invoice/' . $row->purchase_id); ?>" class="text-primary font-weight-bold" target="_blank">
                                    <i class="fa fa-download"></i> Invoice
                                </a>
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