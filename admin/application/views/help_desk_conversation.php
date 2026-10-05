<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



   	<?php if(!empty($help_desk_data)): ?>
		<?php 
		$first_ticket = $help_desk_data[0]; 
		$back_url = (!empty($first_ticket->category) && $first_ticket->category > 2) ? base_url('help_desk/merchant') : base_url('help_desk/shopper');
		?>
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<strong>Ticket: <?= $first_ticket->ticket_id; ?></strong>
				
				<a href="<?= $back_url; ?>" 
				style="text-decoration: none; color: #000000; font-weight: bold; border-bottom: 2px solid #555; padding-bottom: 2px;">
				Back to List
				</a>
			</div>
			<div class="card-body">
				
				<p>
					<strong>Order:</strong> <?= (!empty($order) && !empty($order->increment_id)) ? $order->increment_id : ((!empty($first_ticket->order_id) && $first_ticket->order_id != 0 && $first_ticket->order_id != '0') ? $first_ticket->order_id : 'N/A'); ?> 
					| <strong>Product:</strong> <?= (!empty($product) && !empty($product->product_name)) ? $product->product_name : ((!empty($first_ticket->products) && $first_ticket->products != 0 && $first_ticket->products != '0') ? $first_ticket->products : 'N/A'); ?>
				</p>

				<!-- Status & Refund Workflow Info Banner -->
				<div class="p-3 mb-3 bg-light border rounded">
					<div class="row align-items-center">
						<div class="col-md-3 mb-2 mb-md-0">
							<strong>Status:</strong>
							<?php
							$statusCode = !empty($first_ticket->status_code) ? $first_ticket->status_code : ($first_ticket->status == 2 ? 'Close' : ($first_ticket->status == 1 ? 'Processing' : 'Open'));
							$badgeClass = 'badge-secondary';
							if ($statusCode === 'Open') $badgeClass = 'badge-info';
							elseif ($statusCode === 'Processing') $badgeClass = 'badge-warning';
							elseif ($statusCode === 'Done') $badgeClass = 'badge-success';
							elseif ($statusCode === 'Close' || $statusCode === 'Close (Final)') $badgeClass = 'badge-dark';
							?>
							<span class="badge <?= $badgeClass; ?> p-2"><?= htmlspecialchars($statusCode); ?></span>
						</div>
						<div class="col-md-3 mb-2 mb-md-0">
							<strong>Assigned Role:</strong>
							<span class="badge badge-info p-2"><?= !empty($first_ticket->assigned_role) ? htmlspecialchars($first_ticket->assigned_role) : 'Admin / Support'; ?></span>
						</div>
						<div class="col-md-3 mb-2 mb-md-0">
							<strong>Merchant Action:</strong>
							<?php if (!empty($first_ticket->merchant_action) && $first_ticket->merchant_action === 'refund_approved'): ?>
								<span class="badge badge-success p-2">Refund Approved</span>
							<?php elseif (!empty($first_ticket->merchant_action) && $first_ticket->merchant_action !== 'none'): ?>
								<span class="badge badge-secondary p-2"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $first_ticket->merchant_action))); ?></span>
							<?php else: ?>
								<span class="text-muted">None</span>
							<?php endif; ?>
						</div>
						<div class="col-md-3 mb-2 mb-md-0">
							<strong>Refund Amount:</strong>
							<?php if (!empty($first_ticket->refund_amount) && (float)$first_ticket->refund_amount > 0): ?>
								<span class="text-success font-weight-bold"><?= number_format((float)$first_ticket->refund_amount, 2); ?></span>
								<?php if (!empty($first_ticket->refund_deducted_from_holdback)): ?>
									<br><small class="text-muted">(Deducted from holdback)</small>
								<?php endif; ?>
							<?php else: ?>
								<span class="text-muted">N/A</span>
							<?php endif; ?>
						</div>
					</div>

					<?php if ($first_ticket->status != 2 && !empty($first_ticket->merchant_action) && $first_ticket->merchant_action === 'refund_approved'): ?>
						<hr class="my-2">
						<div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
							<strong class="mr-2">Workflow Actions:</strong>
							<?php if (empty($first_ticket->assigned_role) || $first_ticket->assigned_role !== 'Account'): ?>
								<!-- Stage 2: Assign to @acct -->
								<a href="<?= base_url('CustomerController/assign_to_acct/' . $first_ticket->order_id . '/' . $first_ticket->ticket_id . '/' . $first_ticket->products); ?>"
								   class="btn btn-warning btn-sm"
								   onclick="return confirm('Assign this ticket to Accounts (@acct) for refund processing?');">
									<i class="fa fa-share"></i> Assign to @acct
								</a>
							<?php endif; ?>

							<?php if (!empty($first_ticket->assigned_role) && $first_ticket->assigned_role === 'Account' && (empty($first_ticket->status_code) || $first_ticket->status_code !== 'Done')): ?>
								<!-- Stage 3: Complete Refund (Done) -->
								<a href="<?= base_url('CustomerController/complete_refund/' . $first_ticket->order_id . '/' . $first_ticket->ticket_id . '/' . $first_ticket->products); ?>"
								   class="btn btn-success btn-sm"
								   onclick="return confirm('Confirm refund completion? This will mark the refund as Done, deduct amount from merchant 15-day hold-back balance, and notify the shopper.');">
									<i class="fa fa-check-circle"></i> Complete Refund (Done)
								</a>
							<?php endif; ?>

							<?php if (!empty($first_ticket->status_code) && $first_ticket->status_code === 'Done'): ?>
								<span class="badge badge-success p-2"><i class="fa fa-check"></i> Refund Processed by @acct</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if(!empty($first_ticket->attachment)): ?>
				<?php $attachment = '/uploads/help_desk_attachment/' . $first_ticket->attachment; ?>
                <p>
                    <a href="<?= $attachment ?>" target="_blank">Attachment</a>
                </p>
                <?php endif; ?>   


				<hr>
				<h5>Conversation:</h5>
				<ul class="list-unstyled">
    <?php foreach($help_desk_data as $msg): ?>
        <?php if (!empty($msg->message)): ?>
            <?php 
                $sender = ($msg->category > 2) ? 'Merchant' : 'Shopper';
                $timestamp = $msg->created_at;
            ?>
            <li class="mb-3">
                <div class="bg-light p-2 rounded">
                    <strong><?= $sender; ?></strong> 
                    <small class="text-muted">
                        (<?= date('d M Y, H:i', $timestamp); ?>)
                    </small>
                    <p><?= nl2br($msg->message); ?></p>
                </div>
            </li>
        <?php endif; ?>

        <?php if (!empty($msg->admin_reply)): ?>
            <?php 
                $sender = 'Admin';
                $timestamp = !empty($msg->updated_at) ? $msg->updated_at : $msg->created_at;
            ?>
            <li class="mb-3">
                <div class="bg-primary text-white p-2 rounded">
                    <strong><?= $sender; ?></strong> 
                    <small class="text-white-50">
                        (<?= date('d M Y, H:i', $timestamp); ?>)
                    </small>
                    <p><?= nl2br($msg->admin_reply); ?></p>
                </div>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

				<!-- Admin Reply Form -->
				<form method="POST" action="<?= base_url('CustomerController/update_help_desk'); ?>" class="mt-3">
					<input type="hidden" name="ticket_id" value="<?= $first_ticket->id; ?>">

					<div class="form-group">
						<label>Admin Reply</label>
						<textarea name="admin_reply" class="form-control" rows="3" required></textarea>
					</div>
					<div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
						<button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
						<?php if ($first_ticket->status != 2 && !empty($first_ticket->merchant_action) && $first_ticket->merchant_action === 'refund_approved' && (empty($first_ticket->assigned_role) || $first_ticket->assigned_role !== 'Account')): ?>
							<a href="<?= base_url('CustomerController/assign_to_acct/' . $first_ticket->order_id . '/' . $first_ticket->ticket_id . '/' . $first_ticket->products); ?>"
							   class="btn btn-warning btn-sm"
							   onclick="return confirm('Assign this ticket to Accounts (@acct) for refund processing?');">
								<i class="fa fa-share"></i> Assign to @acct
							</a>
						<?php endif; ?>
						<?php if ($first_ticket->status != 2 && !empty($first_ticket->merchant_action) && $first_ticket->merchant_action === 'refund_approved' && !empty($first_ticket->assigned_role) && $first_ticket->assigned_role === 'Account' && (empty($first_ticket->status_code) || $first_ticket->status_code !== 'Done')): ?>
							<a href="<?= base_url('CustomerController/complete_refund/' . $first_ticket->order_id . '/' . $first_ticket->ticket_id . '/' . $first_ticket->products); ?>"
							   class="btn btn-success btn-sm"
							   onclick="return confirm('Confirm refund completion? This will mark the refund as Done, deduct amount from merchant 15-day hold-back balance, and notify the shopper.');">
								<i class="fa fa-check-circle"></i> Complete Refund (Done)
							</a>
						<?php endif; ?>
						<?php if ($first_ticket->status != 2): ?>
							<a href="<?= base_url('CustomerController/close_ticket/' . $first_ticket->order_id . '/' .
							$first_ticket->category . '/' .
							$first_ticket->ticket_id . '/' . $first_ticket->products); ?>" 
							class="btn btn-danger btn-sm"
							onclick="return confirm('Are you sure you want to close this ticket?');">
							Mark as Close 
							</a>
						<?php endif; ?>
					</div>
				</form>

			</div>
		</div>
	<?php else: ?>
		<p>No conversation found.</p>
	<?php endif; ?>




</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

