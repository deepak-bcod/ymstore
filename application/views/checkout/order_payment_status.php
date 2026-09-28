<?php $this->load->view('common/header'); ?>

<div class="main">
    <div class="container">

        <?php
        // Get increment ID
        if (empty($increment_id)) {
            if (isset($_GET['sessionId']) && isset($_GET['keys'])) {
                $increment_id = base64_decode($_GET['keys']);
            } elseif (isset($_GET['key'])) {
                $increment_id = base64_decode($_GET['key']);
            } else {
                $increment_id = '';
            }
        }

        // Payment status
        $payment_status = strtolower($status ?? 'pending');
        ?>

        <?php if ($payment_status === 'success'): ?>

            <!-- ================= SUCCESS ================= -->

            <ul class="breadcrumb">
                <li>
                    <a href="<?php echo base_url(); ?>">
                        <?= lang('home') ?>
                    </a>
                </li>
                <li><?= lang('checkout') ?></li>
                <li class="active"><?= lang('order_placed') ?></li>
            </ul>

            <div class="row margin-bottom-40">
                <div class="col-md-12">
                    <div class="content-page shadow">

                        <div class="PymtGtwMsgContainer">

                            <img
                                src="<?php echo SKIN_URL; ?>images/thankyou-check.png"
                                alt="Thank You"
                                class="img-responsive thankyou-check"
                            >

                            <h3 class="thanky">
                                <?= lang('order_thanks') ?>
                            </h3>

                            <h4 class="thankyou-order-msg">
                                <?= lang('order_success') ?>
                            </h4>

                            <?php if (!empty($increment_id)): ?>
                                <p class="ordnum thankyou-order-msg">
                                    <?= lang('order_number') ?>:
                                    #<?= htmlspecialchars($increment_id); ?>
                                </p>
                            <?php endif; ?>

                            <p class="thankyou-email-msg">
                                <?= lang('order_track') ?>
                            </p>

                            <a
                                class="btn btn-primary"
                                role="button"
                                href="<?php echo base_url(); ?>"
                            >
                                <?= lang('continue_shopping') ?>
                            </a>

                        </div>

                    </div>
                </div>
            </div>


        <?php elseif ($payment_status === 'failed'): ?>

            <!-- ================= FAILED ================= -->

            <ul class="breadcrumb">
                <li>
                    <a href="<?php echo base_url(); ?>">
                        <?= lang('home') ?>
                    </a>
                </li>
                <li><?= lang('checkout') ?></li>
                <li class="active"><?= lang('order_failed') ?></li>
            </ul>

            <div class="row margin-bottom-40">
                <div class="col-md-12">
                    <div class="content-page shadow">

                        <div class="PymtGtwMsgContainer">

                            <img
                                src="<?php echo SKIN_URL; ?>images/failure.png"
                                alt="Payment Failed"
                                class="img-responsive thankyou-check"
                            >

                            <h3 class="thanky">
                                Something Went Wrong. Please Try Again.
                            </h3>

                            <a
                                class="btn btn-primary"
                                role="button"
                                href="<?php echo base_url(); ?>"
                            >
                                <?= lang('continue_shopping') ?>
                            </a>

                        </div>

                    </div>
                </div>
            </div>


        <?php elseif ($payment_status === 'pending'): ?>

            <!-- ================= PENDING ================= -->

            <ul class="breadcrumb">
                <li>
                    <a href="<?php echo base_url(); ?>">
                        <?= lang('home') ?>
                    </a>
                </li>
                <li><?= lang('checkout') ?></li>
                <li class="active"><?= lang('payment_status') ?></li>
            </ul>

            <div class="row margin-bottom-40">
                <div class="col-md-12">
                    <div class="content-page shadow">

                        <div class="PymtGtwMsgContainer">

                            <img
                                src="<?php echo SKIN_URL; ?>images/process-tick.png"
                                alt="Payment Pending"
                                class="img-responsive thankyou-check"
                            >

                            <h3 class="thanky">
                                Payment Pending
                            </h3>

                            <h4 class="thankyou-order-msg">
                                Your payment is currently being processed.
                                Please wait for the payment confirmation.
                            </h4>

                            <?php if (!empty($increment_id)): ?>
                                <p class="ordnum thankyou-order-msg">
                                    <?= lang('order_number') ?>:
                                    #<?= htmlspecialchars($increment_id); ?>
                                </p>
                            <?php endif; ?>

                            <a
                                class="btn btn-primary"
                                role="button"
                                href="<?php echo base_url(); ?>"
                            >
                                <?= lang('continue_shopping') ?>
                            </a>

                        </div>

                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php $this->load->view('common/footer'); ?>

<script type="text/javascript"
        src="<?php echo SKIN_JS; ?>checkout.js"></script>

</body>
</html>