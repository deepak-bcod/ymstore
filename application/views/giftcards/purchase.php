<?php $this->load->view('common/header'); ?>

<main class="container py-4">
  <h1><?= lang('giftcards.purchase_title') ?>: <?= htmlspecialchars($value->title) ?></h1>

  <div class="card">
    <div class="card-body">
      <form id="giftPurchaseForm" method="post" action="<?= site_url('Giftcards/processPurchase') ?>">
        <input type="hidden" name="value_id" value="<?= $value->id ?>">

        <div class="form-group mb-3">
          <label><?= lang('giftcards.amount_mur') ?></label>
          <input type="text" class="form-control" value="<?= number_format($value->amount,2) ?>" readonly>
        </div>

        <div class="form-group mb-3">
          <label><?= lang('giftcards.receiver_name') ?></label>
          <input type="text" name="receiver_name" id="receiver_name" class="form-control">
          <small class="text-danger error" id="receiver_name_error"></small>
        </div>

        <div class="form-group mb-3">
          <label><?= lang('giftcards.receiver_email') ?></label>
          <input type="text" name="receiver_email" id="receiver_email" class="form-control">
          <small class="text-danger error" id="receiver_email_error"></small>

        </div>

        <div class="form-group mb-3">
          <label><?= lang('giftcards.greeting_message') ?></label>
          <textarea name="message" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-100">
          <?= lang('giftcards.buy_giftcard_button') ?>
        </button>
      </form>
    </div>
  </div>
</main>
<?php $this->load->view('common/footer'); ?>
<script>
$(document).ready(function () {
    var receiver_name_req = "<?= lang('giftcards.receiver_name_req') ?>";
    var receiver_name_len = "<?= lang('giftcards.receiver_name_len') ?>";
    var receiver_email_req = "<?= lang('giftcards.receiver_email_req') ?>";
    var receiver_email_valid = "<?= lang('giftcards.receiver_email_valid') ?>";

    // Remove error messages when typing
    $('#receiver_name, #receiver_email').on('keyup change', function () {
        $(this).removeClass('is-invalid');
        $(this).next('.error').text('');
    });

    // Form validation before submit
    $('#giftPurchaseForm').on('submit', function (e) {
        let isValid = true;

        // Clear previous errors
        $('.error').text('');
        $('.form-control').removeClass('is-invalid');

        let receiver_name  = $('#receiver_name').val().trim();
        let receiver_email = $('#receiver_email').val().trim();

        // Receiver name validation
        if (receiver_name === '') {
            $('#receiver_name_error').text(receiver_name_req);
            $('#receiver_name').addClass('is-invalid');
            isValid = false;
        } else if (receiver_name.length < 3) {
            $('#receiver_name_error').text(receiver_name_len);
            $('#receiver_name').addClass('is-invalid');
            isValid = false;
        }

        // Receiver email validation
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (receiver_email === '') {
            $('#receiver_email_error').text(receiver_email_req);
            $('#receiver_email').addClass('is-invalid');
            isValid = false;
        } else if (!emailPattern.test(receiver_email)) {
            $('#receiver_email_error').text(receiver_email_valid);
            $('#receiver_email').addClass('is-invalid');
            isValid = false;
        }

        // Stop submission if invalid
        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Form will submit normally and redirect to MyT Money
    });
});
</script>
