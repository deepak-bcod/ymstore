<div class="main-content">
    <div class="container-fluid">
        <h3>Manage Addon</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                            <?php if(!empty($manage_addons)): ?>
                                <?php $sr = 1; foreach($manage_addons as $row): ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td><?php echo $row->merchant_name; ?></td>
                                    <td><?php echo $row->addon_service_name; ?></td>
                                    <td><?php echo $row->status; ?></td>
                                    <td><?php echo $row->order_id; ?></td>
                                    <td><?php echo $row->date; ?></td>
                                    <td><?php echo $row->amount; ?></td>
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