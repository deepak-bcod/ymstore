<?php $this->load->view('common/fbc-user/header'); ?>

<!-- Main Content Wrapper to prevent sidebar overlap -->
<div id="content-wrapper" class="d-flex flex-column" style="background-color: #f8f9fc;">
    <div id="content">
        
        <div class="container-fluid" style="padding: 30px;">
            <h2 class="mb-4">Manage Addon</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr style="background-color: #ffc107; color: #000;">
                            <th>ID</th>
                            <th>CATEGORY</th>
                            <th>TITLE</th>
                            <th>FINAL PRICE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($manage_addons)): ?>
                            <?php foreach($manage_addons as $row): ?>
                                <tr>
                                    <td><?php echo $row->id; ?></td>
                                    <td><?php echo html_escape($row->category_name); ?></td>
                                    <td><?php echo html_escape($row->title); ?></td>
                                    <td><?php echo number_format($row->final_price, 2); ?></td>
                                    <td><?php echo ($row->status == 1) ? 'Active' : 'Inactive'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>