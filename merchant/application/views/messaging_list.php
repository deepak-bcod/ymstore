<?php $this->load->view('common/fbc-user/header'); ?>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard helf-section">

        <div class="table-responsive text-center">

            <h2><?= $this->lang->line('your_messages'); ?></h2>
            <table class="table table-bordered table-style message-table">
                <thead class="text-center">
                    <tr>
                        <th><?= $this->lang->line('sr_no'); ?></th>
                        <th><?= $this->lang->line('product_name'); ?></th>
                        <th><?= $this->lang->line('customer_name_'); ?></th>
                        <th><?= $this->lang->line('category'); ?></th>
                        <th><?= $this->lang->line('status'); ?></th>

                        <th><?= $this->lang->line('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sr = 1; ?>
                    <?php foreach ($messaging as $msg): ?>
                        <tr>
                            <td><?= $sr++; ?></td>
                            <td><?= htmlspecialchars_decode($msg['product_name']); ?></td>
                            <!-- <td><?= htmlspecialchars($msg['product_name']); ?></td> -->
                            <td><?= htmlspecialchars($msg['name']); ?></td>
                            <td><?= htmlspecialchars($msg['category']); ?></td>
                            <td class="status-cell">
    <?php 
        // 1. Get status from DB
        $db_status = strtolower(trim($msg['status'])); 
        
        // 2. Check if a merchant reply actually exists in this row
        $has_reply = !empty(trim($msg['merchant_reply']));

        if ($db_status == 'answered' || $has_reply) {
            echo '<span style="color: green;">Answered</span>';
        } else {
            echo '<span style="color: #e91e63;">Not Answered</span>';
        }
    ?>
</td>
            
                            <td>
                                <a href="<?= base_url('Mydocuments/messages_view/' . $msg['product_id'] . '/' . $msg['customer_id']); ?>" class="btn btn-sm btn-primary"><?= $this->lang->line('view_messages'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>

    </div>
    <style>
			table.message-table td:nth-child(2), table.message-table td:nth-child(3)  {text-align: left;}
		</style>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
