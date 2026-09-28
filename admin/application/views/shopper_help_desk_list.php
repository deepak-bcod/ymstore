<?php $this->load->view('common/fbc-user/header'); ?> 

<?php
 $subject_types = [
        1 => 'Order Issue', 
        2 =>'Refund Request', 
        3 => 'Replacement Request', 
        4 => 'Merchant Delivery', 
        5 => ' YM Delivery', 
        6 =>'Resolution Request', 
        7 => ' General Support',
        8 => 'Technical Issue'
    ];

?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">

            <h2>Shopper Tickets</h2>
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

                foreach ($grouped_tickets as $key => &$tickets) {
                    usort($tickets, function($a, $b) {
                        $idA = (int)($a['id'] ?? 0);
                        $idB = (int)($b['id'] ?? 0);
                        return $idB <=> $idA;
                    });
                }
                unset($tickets);
                ?>

                <table class="table table-bordered table-style">
                    <thead>
    <tr>
        <th>SR No</th>
        <th>Ticket Id</th>
        <th>Order No</th> <th>Subject</th>
        <th>Subject Type</th>
        <th>Status</th> <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php $sr = 1; ?>
    <?php foreach($grouped_tickets as $tickets): ?>
        <?php $first_ticket = $tickets[0]; ?>
        <tr>
            <td><?= $sr++; ?></td>      
            <td><?= $first_ticket['ticket_id']; ?></td>
            <td><?= (!empty($first_ticket['order_id']) && $first_ticket['order_id'] != 0 && $first_ticket['order_id'] != '0') ? str_pad($first_ticket['order_id'], 4, '1', STR_PAD_LEFT) : 'N/A'; ?></td>
            <td><?= $first_ticket['subject']; ?></td>
        

    <td>
        <?php 
        
        
        $type_id = (int)($first_ticket['priority'] ?? 0); 
        //  print_r($first_ticket);exit;
        echo $subject_types[$type_id] ?? 'Unknown (' . $type_id . ')';
        ?>
    </td>
            <td>
    <?php
    
    $status_map = [
        0 => ['label' => 'Waiting Reply', 'class' => 'text-danger'],
        1 => ['label' => 'In Progress',       'class' => 'text-primary'],
        2 => ['label' => 'Resolved',     'class' => 'text-success']
    ];

    $status_val = $first_ticket['status'] ?? null;
    $status_info = $status_map[$status_val] ?? ['label' => 'Unknown', 'class' => 'text-muted'];

    // Display the status with a class for color
    echo '<span class="' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
    ?>
</td>
            <td>
                <a href="<?= base_url('CustomerController/view/' . $first_ticket['order_id'] . '/' . $first_ticket['ticket_id'] . '/' . ($first_ticket['products'] ?? '')); ?>" class="btn btn-sm btn-primary">View</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                </table>



        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

