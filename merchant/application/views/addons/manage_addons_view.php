<style>
    /* Ensure the main content area respects the fixed sidebar width */
    .main-content {
        margin-left: 250px; /* Matches your sidebar width */
        width: calc(100% - 250px);
        padding: 20px;
        box-sizing: border-box;
    }
    
    /* Make sure the table takes up full width cleanly and scrolls horizontally if needed */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
</style>

<div class="main-content">
    <div class="container-fluid">
        <h3>Manage Addon</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 60px;">SR NO.</th>
                                <th style="width: 80px;">IMAGE</th>
                                <th>CATEGORY NAME</th>
                                <th>Addon Service</th>
                                <th style="width: 100px;">STATUS</th>
                                <th style="width: 90px;">SERVICE ID</th>
                                <th style="width: 100px;">PRICE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($manage_addons)): ?>
                                <?php $sr = 1; foreach($manage_addons as $row): ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td class="text-center">
                                        <?php if(!empty($row->image)): ?>
                                            <img src="<?php echo base_url('uploads/addons/' . $row->image); ?>" alt="Service" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row->category_name; ?></td>
                                    <td><?php echo $row->title; ?></td>
                                    <td>Active</td>
                                    <td><?php echo $row->service_id; ?></td>
                                    <td><?php echo $row->final_price; ?></td>
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
        </div>
    </div>
</div>