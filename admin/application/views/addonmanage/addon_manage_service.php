<div class="content-wrapper">
    <section class="content-header">
        <h3>Manage Addon Services</h3>
    </section>

    <section class="content">
        <div class="box">
            

            <div class="box-body">
                <table id="addonManageTable" class="table table-bordered table-striped">
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
                        <?php if(!empty($services)): ?>
                            <?php $sr = 1; foreach($services as $row): ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td><?php echo html_escape($row['merchant_name'] ?? ''); ?></td>
                                    <td><?php echo html_escape($row['service_name'] ?? $row['title'] ?? ''); ?></td>
                                    <td><?php echo html_escape($row['status'] ?? ''); ?></td>
                                    <td><?php echo html_escape($row['order_id'] ?? ''); ?></td>
                                    <td><?php echo html_escape($row['date'] ?? ''); ?></td>
                                    <td><?php echo html_escape($row['amount'] ?? ''); ?></td>
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
    </section>
</div>



<script>
    $(document).ready(function() {
        var table = $('#addonManageTable').DataTable({
            "responsive": true,
            "autoWidth": false
        });
        
        $('#customSearchBox').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>