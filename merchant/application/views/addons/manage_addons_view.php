<style>
    .manage-addons-wrapper {
        margin-left: 260px; 
        padding: 20px 30px;
        box-sizing: border-box;
        width: calc(100% - 260px);
        margin-top: 15px; /* Ensures space below the top header bar so the title is fully visible */
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .table {
        width: 100% !important;
    }
    .table thead th {
        background-color: #ffc107; 
        color: #000;
        text-align: center;
        vertical-align: middle;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
    }
    @media (max-width: 768px) {
        .manage-addons-wrapper {
            margin-left: 0;
            width: 100%;
            margin-top: 10px;
            padding: 10px;
        }
    }
</style>
<div class="manage-addons-wrapper">
    <div class="main-content w-100">
        
        <h3 class="mb-4"><?php echo lang('manage_addon_title'); ?></h3>
        
        <div class="table-responsive w-100">
            <table class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th><?php echo lang('addon_sr_no'); ?></th>
                        <th><?php echo lang('addon_service'); ?></th>
                        <th><?php echo lang('addon_name'); ?></th>
                        <th><?php echo lang('addon_date'); ?></th>
                        <th><?php echo lang('addon_amount'); ?></th>
                        <th><?php echo lang('addon_actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($manage_addons)): ?>
                        <?php $sr = 1; foreach($manage_addons as $row): ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><?php echo isset($row->addon_service_name) ? $row->addon_service_name : ''; ?></td>
                            <td><?php echo isset($row->addon_name) ? $row->addon_name : ''; ?></td>
                            <td><?php echo isset($row->date) ? $row->date : ''; ?></td>
                            <td><?php echo isset($row->amount) ? $row->amount : ''; ?></td>
                            <td>
                                <!-- Download Invoice Link -->
                                <a href="<?php echo base_url('addons/download_invoice/' . $row->purchase_id); ?>" 
                                   class="btn btn-sm btn-primary yellow-button" 
                                   target="_blank" 
                                   title="<?php echo lang('download_invoice') ? lang('download_invoice') : 'Download Invoice'; ?>">
                                    <i class="fa fa-download mr-1"></i><?php echo lang('download_invoice') ? lang('download_invoice') : 'Download Invoice'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo lang('addon_no_records'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>