<?php $this->load->view('common/fbc-user/header'); ?>

<div class="container py-5 text-center">
  <?php if ($transaction['status'] == 'success'): ?>
    <h3 class="text-success">✅ <?= lang('payment_successful'); ?></h3>
    <p><?= lang('addon_activated'); ?></p>
  <?php elseif ($transaction['status'] == 'failed'): ?>
    <h3 class="text-danger">❌ <?= lang('payment_failed'); ?></h3>
    <p><?= lang('payment_failed_desc'); ?></p>
  <?php else: ?>
    <h3 class="text-warning">⏳ <?= lang('payment_pending'); ?></h3>
    <p><?= lang('payment_pending_desc'); ?></p>
  <?php endif; ?>

  <hr>
  <h5><?= lang('transaction_reference'); ?></h5>
  <p><?= htmlspecialchars($transaction['transaction_ref'] ?? '-') ?></p>
  <p><a href="<?= base_url('addons'); ?>" class="btn btn-primary mt-3"><?= lang('back_to_addons'); ?></a></p>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>