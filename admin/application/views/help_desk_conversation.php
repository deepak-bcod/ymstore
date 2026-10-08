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

				<!-- Help Desk Ticket Status -->
				<div class="p-3 mb-3 bg-light border rounded">
					<div class="row align-items-center">
						<div class="col-md-6">
							<strong>Status:</strong>
							<?php
							$label = ($first_ticket->status == 2) ? 'Closed' : (($first_ticket->status == 1) ? 'Open' : 'Not Opened');
							$badgeClass = ($first_ticket->status == 2) ? 'badge-success' : (($first_ticket->status == 1) ? 'badge-info' : 'badge-danger');
							?>
							<span class="badge <?= $badgeClass; ?> p-2"><?= $label; ?></span>
						</div>
					</div>
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
						<textarea name="admin_reply" id="admin_reply_field" class="form-control" rows="3" required></textarea>
					</div>
					<div class="d-flex flex-wrap align-items-center" style="gap: 10px;">
						<button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
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

