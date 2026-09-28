<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">

            <h2>Merchant Tickets</h2>
            <?php
                // Group tickets by ticket_id or fallback to unique record id
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
            <th>Subject</th>
            <th>Subject Type</th>
            <th>Status</th> 
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $sr = 1; ?>
        <?php foreach($grouped_tickets as $tickets): ?>
            <?php $ticket = $tickets[0]; ?>
            <tr>
                <td><?= $sr++; ?></td>
                <td><?= $ticket['ticket_id']; ?></td>
                <td><?= $ticket['subject']; ?></td>
                <td>
                    <?php
                    $subject_types  = [
                        1 => 'Accounting',
                        2 => 'Technical Issue',
                        3 => 'General Support'
                    ];

                    echo $subject_types[$ticket['priority']] ?? $ticket['priority'] ?? 'Unknown';
                    ?>
                </td>
                <td>
    <?php
    // 1. Define the mapping
    $status_map = [
        0 => ['label' => 'Waiting Reply', 'class' => 'text-danger'],
        1 => ['label' => 'In Progress',   'class' => 'text-primary'],
        2 => ['label' => 'Resolved',      'class' => 'text-success']
    ];

    // 2. Get the value from ticket array
    $status_val = $ticket['status'] ?? null;

    // 3. Perform the lookup
    $status_info = $status_map[$status_val] ?? ['label' => 'Unknown', 'class' => 'text-muted'];

    // 4. Echo status
    echo '<span class="' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
    ?>
</td>
                <td>
                    <a href="<?= base_url('CustomerController/view/' . $ticket['order_id'] . '/' . $ticket['ticket_id'] . '/' .($ticket['products'] ?? '')); ?>" class="btn btn-sm btn-primary">View</a>
                </td>   
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>



        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

