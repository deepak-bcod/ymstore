<?php $this->load->view('common/header'); ?>

<main class="container py-4">
  <h1><?= lang('giftcards.buy_giftcard') ?></h1>

  <div class="row">
    <?php foreach ($values as $v): ?>

      <?php
      $title = $v->title;

      if ($this->session->userdata('site_lang') == 'french') {
          $title = str_replace('Gift Card', 'Carte Cadeau', $title);
      }
      ?>

      <div class="col-md-4 giftcard">
        <div class="card mb-3">
          <div class="card-body text-center">

            <h5><?= htmlspecialchars($title) ?></h5>

            <p>
              <strong><?= lang('giftcards.amount') ?>: MUR</strong>
              <?= number_format($v->amount, 2) ?>
            </p>

            <a href="<?= site_url('Giftcards/purchase/'.$v->id) ?>" class="btn btn-primary">
              <?= lang('giftcards.buy_button') ?>
            </a>

          </div>
        </div>
      </div>

    <?php endforeach; ?>
  </div>
</main>

<?php $this->load->view('common/footer'); ?>