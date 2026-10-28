<style>
    .manage-addons-card {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .table {
        width: 100% !important;
    }
    .table thead th {
        background-color: #ffc107 !important; 
        color: #000 !important;
        text-align: center;
        vertical-align: middle;
        font-weight: 600;
        border: 1px solid #dee2e6;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    a.yellow-button:hover, .yellow-button:hover {
        color: #e08b0a !important;
        text-decoration: underline !important;
    }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner pt-3 pb-4">
        
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
            <h1 class="head-name mb-0"><?php echo lang('manage_addon_title'); ?></h1>
        </div>
        
        <div class="manage-addons-card">
            <div class="table-responsive w-100">
                <table class="table table-bordered table-striped w-100 mb-0">
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
                                <td><?php echo isset($row->addon_service_name) ? htmlspecialchars($row->addon_service_name) : ''; ?></td>
                                <td><?php echo isset($row->addon_name) ? htmlspecialchars($row->addon_name) : ''; ?></td>
                                <td><?php echo isset($row->date) ? htmlspecialchars($row->date) : ''; ?></td>
                                <td>MUR <?php echo isset($row->amount) ? number_format((float)$row->amount, 2) : '0.00'; ?></td>
                                <td>
                                    <!-- Download Invoice Link -->
                                    <a href="<?php echo base_url('addons/download_invoice/' . $row->purchase_id); ?>" 
                                       class="btn btn-sm btn-primary yellow-button" 
                                       target="_blank" 
                                       style="color: #ff9e0c !important; background: transparent !important; border: none !important; font-weight: 500; font-size: 14px; text-decoration: none;"
                                       title="<?php echo lang('download_invoice') ? lang('download_invoice') : 'Download Invoice'; ?>">
                                        <i class="fas fa-download mr-1"></i><?php echo lang('download_invoice') ? lang('download_invoice') : 'Download Invoice'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4"><?php echo lang('addon_no_records'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>