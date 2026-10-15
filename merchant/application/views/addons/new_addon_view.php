<div class="content-wrapper">
    <section class="content-header">
        <h1>New Add-On Transactions</h1>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SR NO.</th>
                            <th>MERCHANT NAME</th>
                            <th>STATUS</th>
                            <th>DATE</th>
                            <th>AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($addons_list)) { ?>
                            <?php $sr = 1; foreach($addons_list as $row) { ?>
                                <tr>
                                    <td><?= $sr++; ?></td>
                                    <td><?= html_escape($row->merchant_name); ?></td>
                                    <td>
                                        <?php if($row->status == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($row->date)); ?></td>
                                    <td>$<?= number_format($row->amount, 2); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No records found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>