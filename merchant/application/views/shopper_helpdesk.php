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
                    
                        $grouped_tickets = [];
foreach ($help_desk as $ticket) {
    $key = !empty($ticket['ticket_id']) ? $ticket['ticket_id'] : ($ticket['order_id'] . '_' . $ticket['products'] . '_' . ($ticket['id'] ?? ''));
    if (!isset($grouped_tickets[$key])) {
        $grouped_tickets[$key] = [];
    }
    $grouped_tickets[$key][] = $ticket;
}

// 2. Sort to ensure the latest record is at the top of each group
foreach ($grouped_tickets as $key => &$tickets) {
    usort($tickets, function($a, $b) {
        $idA = (int)($a['id'] ?? 0);
        $idB = (int)($b['id'] ?? 0);
        return $idB <=> $idA;
    });
}
unset($tickets);
uksort($grouped_tickets, function($a, $b) use ($grouped_tickets) {
    // Compare the first ticket of each group
    $firstA = $grouped_tickets[$a][0]['ticket_id'] ?? '';
    $firstB = $grouped_tickets[$b][0]['ticket_id'] ?? '';
    return $firstB <=> $firstA; // Descending order
});
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
                                <?php $sr = count($grouped_tickets); ?>
                                 <!-- <?php print_r($grouped_tickets); exit; ?>  -->
                                <?php foreach($grouped_tickets as $tickets): ?>
                                    <?php $first_ticket = $tickets[0]; ?>
                                    <tr>
                                        <td><?= $sr++; ?></td>
                                        <td><?= $first_ticket['ticket_id']; ?></td>
                                          <!-- <?php print_r($first_ticket); exit; ?>  -->
                                        <td><?= str_pad($first_ticket['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?= $first_ticket['subject']; ?></td>
                                                
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

                                        
                                        echo $subject_types[$first_ticket['priority']] ?? $first_ticket['priority'] ?? lang('unknown');
                                        ?>
                                    
                                        <td>
                                            <?php
                                            if (!empty($first_ticket['status_code'])) {
                                                $badge_map = [
                                                    'Open'          => 'text-primary',
                                                    'Processing'    => 'text-warning',
                                                    'Done'          => 'text-info',
                                                    'Close'         => 'text-success',
                                                    'ReOpen'        => 'text-danger',
                                                    'Close (Final)' => 'text-muted'
                                                ];
                                                $cls = $badge_map[$first_ticket['status_code']] ?? 'text-secondary';
                                                echo '<span class="' . $cls . ' font-weight-bold">' . htmlspecialchars($first_ticket['status_code']) . '</span>';
                                            } else {
                                                $currentStatus = isset($first_ticket['status']) ? (int)$first_ticket['status'] : -1;
                                                if ($currentStatus === 0) {
                                                    echo '<span class="text-danger ">Not Opened</span>';
                                                } elseif ($currentStatus === 1) {
                                                    echo '<span class="text-primary ">Open</span>';
                                                } elseif ($currentStatus === 2) {
                                                    echo '<span class="text-success ">Closed</span>';
                                                } else {
                                                    echo '<span class="text-muted">Unknown</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                                <td>
                                                    <div class="d-flex justify-content-center" style="gap: 5px;">
                                                        <?php
                                                        $view_url = !empty($first_ticket['ticket_id'])
                                                            ? base_url('merchant/order-resolution/view/' . $first_ticket['ticket_id'])
                                                            : base_url('UserController/view/' . $first_ticket['order_id'] . '/' . $first_ticket['products']);
                                                        ?>
                                                        <a href="<?= $view_url; ?>" class="btn btn-sm btn-primary">
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
