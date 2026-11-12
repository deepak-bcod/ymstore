<?php $this->load->view('common/fbc-user/header'); ?>

<style>
    /* 1. Ensure main container expands properly next to sidebar */
    .manage-addon-container {
        margin-left: 260px; /* Adjust to match your exact sidebar width */
        padding: 20px;
        width: calc(100% - 260px); /* FIXED: added missing minus operator */
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

    a.yellow-button, .yellow-button {
        color: #ff9e0c !important;
        background: transparent !important;
        border: none !important;
        font-weight: 500;
        font-size: 14px;
        text-decoration: none;
    }

    a.yellow-button:hover, .yellow-button:hover {
        color: #e08b0a !important;
        text-decoration: underline !important;
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
                    <th>PURCHASE DETAILS</th>
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
                            <!-- Unbolded Merchant Name -->
                            <td><?php echo html_escape($row->merchant_name ?? ''); ?></td>
                            <td><?php echo html_escape($row->addon_service_name ?? ''); ?></td>
                            <!-- Unbolded Addon Name -->
                            <td><?php echo html_escape($row->addon_name ?? ''); ?></td>
                            <td>
                                <?php if (!empty($row->order_number)): ?>
                                    <span class="badge badge-info" style="font-size: 13px;">Order #<?php echo html_escape($row->order_number); ?></span>
                                    <?php if (!empty($row->ticket_number)): ?>
                                        <br><small class="text-muted"><a href="<?php echo base_url('order_resolution/view/' . $row->ticket_number . '?as=help'); ?>" style="color:#1E7EC8; font-weight:600;">Ticket #<?php echo html_escape($row->ticket_number); ?></a></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">General Add-On</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo html_escape($row->date ?? ''); ?></td>
                            <!-- Unbolded Amount (removed font-weight-bold and text-dark) -->
                            <td>MUR <?php echo number_format($row->amount ?? 0, 2); ?></td>
                            <td>
                                <a href="<?php echo base_url('addons/download_invoice/' . $row->purchase_id); ?>" class="btn btn-sm btn-primary yellow-button" target="_blank" style="color: #ff9e0c !important; background: transparent !important; border: none !important; font-weight: 500; font-size: 14px; text-decoration: none;">
                                    <i class="fas fa-file-invoice mr-1"></i>Download Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>