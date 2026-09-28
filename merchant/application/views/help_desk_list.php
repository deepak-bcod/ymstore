<?php $this->load->view('common/fbc-user/header'); 

// echo "<pre>";print_r($help_desk);die;

?> 


<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <div class="content-main form-dashboard helf-section">
    
            <div class="table-responsive text-center">
        
                <h2><?php echo lang('your_tickets') ?></h2>
                <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
                <?php
                    // Group tickets by ticket_id to avoid collapsing separate tickets for the same order
                    $grouped_tickets = [];
    foreach ($help_desk as $ticket) {
        $key = !empty($ticket['ticket_id']) ? $ticket['ticket_id'] : ($ticket['order_id'] . '_' . $ticket['products'] . '_' . ($ticket['id'] ?? ''));
        if (!isset($grouped_tickets[$key])) {
            $grouped_tickets[$key] = [];
        }
        $grouped_tickets[$key][] = $ticket;
    }

    // 2. SORT each group so the NEWEST record (highest id) is at the top ([0])
    foreach ($grouped_tickets as $key => &$tickets) {
        usort($tickets, function($a, $b) {
            $idA = (int)($a['id'] ?? 0);
            $idB = (int)($b['id'] ?? 0);
            return $idB <=> $idA; 
        });
    }
    unset($tickets); // Clean up reference
                    ?>
    
                    <table class="table table-bordered table-style">
                        <thead>
                            <tr>
                                <th><?php echo lang('sr_no'); ?></th>
                                <th><?php echo lang('ticket_id'); ?></th>
                                <th><?php echo lang('order_id');?></th>
                                <th><?php echo lang('subject'); ?></th>
                                <th><?php echo lang('category'); ?></th>
                                <th><?php echo lang('status');?></th>
                                <th><?php echo lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
    <?php $sr = 1; ?>
    <?php foreach($grouped_tickets as $tickets): ?>
        <?php $first_ticket = $tickets[0]; ?>
        <tr>
            <td><?= $sr++; ?></td>
            <td><?= $first_ticket['ticket_id']; ?></td>
            <td><?= (!empty($first_ticket['order_id']) && $first_ticket['order_id'] != 0) ? str_pad($first_ticket['order_id'], 4, '0', STR_PAD_LEFT) : 'N/A'; ?></td>
            <td><?= $first_ticket['subject']; ?></td>
            <td>
    <?php
     $subject_types  = [
            1 => lang('order_issue'),
            2 => lang('refund_request'),
            3 => lang('replacement_request'),
            4 => lang('merchant_delivery'),  
            5 => lang('ym_delivery'),
            6 => lang('resolution_request'),
            7 => lang('general_support'),
            8 => lang('technical_issue')
        ];

    // This checks the DB value (1-6). If not found, it shows the raw value or 'unknown'
    echo $subject_types[$first_ticket['priority']] ?? $first_ticket['priority'] ?? lang('unknown');
    ?>
</td>
            <td>
    <?php
    // Define the status mapping
    $status_map = [
        0 => ['label' => lang('status_not_opened'), 'class' => 'text-danger'],
        1 => ['label' => lang('status_open'),       'class' => 'text-primary'],
        2 => ['label' => lang('status_closed'),     'class' => 'text-success']
    ];

    // Get current status safely
    $currentStatus = (int)($first_ticket['status'] ?? -1);

    // Get the config, or default to 'Unknown' if the status code doesn't exist
    $status = $status_map[$currentStatus] ?? ['label' => lang('status_unknown'), 'class' => 'text-muted'];
    ?>

    <span class="<?= $status['class'] ?>">
        <?= $status['label'] ?>
    </span>
</td>
            <td>
                <div class="d-flex justify-content-center" style="gap: 5px;">
                    <a href="<?= base_url('UserController/view/' . $first_ticket['order_id'] . '/' . $first_ticket['ticket_id'] . '/' . $first_ticket['products']); ?>" class="btn btn-sm btn-primary">
                        <?php echo lang('view'); ?>
                    </a>

                    
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                    </table>
    
            </div>
    
        </div>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>   
