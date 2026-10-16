<div class="container-fluid">
    <h2 class="mb-4" style="margin-top: 20px;">Manage Addon</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr style="background-color: #ffc107; color: #000;">
                    <th>SR NO.</th>
                    <th>MERCHANT NAME</th>
                    <th>ADDON SERVICE</th>
                    <th>ADDON NAME</th>
                    <th>DATE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($manage_addons)): ?>
                    <?php $sr = 1; foreach($manage_addons as $row): ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><?php echo html_escape($row->merchant_name); ?></td>
                            <td><?php echo html_escape($row->service_title); ?></td>
                            <td><?php echo html_escape($row->addon_name); ?></td>
                            <td><?php echo html_escape($row->date); ?></td>
                            <td><?php echo number_format($row->amount, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>