<?php $this->load->view('common/header'); ?>
<main class="container py-4">
  <h1><?= lang('gift_success_title') ?></h1>

  <p><?= lang('gift_success_order') ?>: <?= htmlspecialchars($order->order_number) ?></p>
  <p><?= lang('gift_success_amount') ?>: <?= number_format($order->amount,2) ?></p>
  <p><?= lang('gift_success_receiver') ?>: <?= htmlspecialchars($order->receiver_name) ?> (<?= htmlspecialchars($order->receiver_email) ?>)</p>

  <?php if (!empty($gift_card)): ?>
    <div class="alert alert-success">
      <h4><?= lang('gift_success_card_issued') ?></h4>
      <p><strong><?= lang('gift_success_code') ?>:</strong> <?= htmlspecialchars($gift_card->code) ?></p>
      <p><strong><?= lang('gift_success_balance') ?>:</strong> <?= number_format($gift_card->balance,2) ?></p>
    </div>
  <?php else: ?>
    <p class="text-info"><?= lang('gift_success_pending') ?></p>
  <?php endif; ?>

</main>
<?php $this->load->view('common/footer'); ?>
