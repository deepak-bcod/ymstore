<?php $this->load->view('common/header'); ?>

<main class="container py-4">
    <div class="card">
        <div class="card-body text-center">
            <h1 class="text-danger mb-3"><?= lang('gift_failed_title') ?></h1>

            <p class="mb-3">
                <?= lang('gift_failed_message') ?>
            </p>

            <?php if (!empty($order_number)): ?>
                <p><?= lang('gift_failed_order_number') ?>: 
                    <strong><?= htmlspecialchars($order_number) ?></strong>
                </p>
            <?php endif; ?>

            <p><?= lang('gift_failed_try_again') ?></p>

            <a href="<?= site_url('Giftcards') ?>" class="btn btn-primary mt-3">
                <?= lang('gift_failed_back_button') ?>
            </a>
        </div>
    </div>
</main>

<?php $this->load->view('common/footer'); ?>
