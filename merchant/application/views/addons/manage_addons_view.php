
<div class="main-content">
    <div class="container-fluid">
        <h3>Manage Addon</h3>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped addon-table">
                        <thead>
                           <tr>
                            <th>SR NO.</th>
                            <th>MERCHANT NAME</th>
                            <th>Addon Service</th>
                            <th>STATUS</th>
                            <th>Order</th>
                            <th>DATE</th>
                            <th>AMOUNT</th>
                        </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($manage_addons)): ?>
                                <?php $sr = 1; foreach ($manage_addons as $row): ?>
                                    <tr>
                                        <td><?php echo $sr++; ?></td>

                                        <td class="text-center">
                                            <?php if (!empty($row->image)): ?>
                                                <img
                                                    src="<?php echo base_url('uploads/addons/' . $row->image); ?>"
                                                    alt="Service"
                                                    class="addon-image">
                                            <?php else: ?>
                                                <span class="text-muted">No Image</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php echo html_escape($row->category_name); ?>
                                        </td>

                                        <td>
                                            <?php echo html_escape($row->title); ?>
                                        </td>

                                        <td>Active</td>

                                        <td>
                                            <?php echo html_escape($row->service_id); ?>
                                        </td>

                                        <td>
                                            <?php echo number_format((float)$row->final_price, 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        No records found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    width: 100%;
    min-width: 0;
}

.main-content .container-fluid {
    width: 100%;
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

.main-content .card {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.main-content .card-body {
    width: 100%;
    min-width: 0;
}

.main-content .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.addon-table {
    width: 100%;
    min-width: 900px;
    margin-bottom: 0;
}

.addon-table th,
.addon-table td {
    vertical-align: middle;
    white-space: normal;
    padding: 12px 10px;
}

.addon-table th {
    white-space: nowrap;
}

.addon-image {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 4px;
}
</style>
