<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?= base_url() ?>"><?= lang('home') ?></a></li>
                <li class="active"><?= lang('giftcards.my_gift_cards') ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">

                    <!-- Current Balance -->
                    <div class="card mb-4 gift-card-box">
                        <div class="card-body text-center">
                            <h2><?= lang('giftcards.current_balance') ?></h2>
                            <h3 class="text-success">MUR <?= number_format($balance ?? 0, 2) ?></h3>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="card">
                        <div class="card-body">
                            <h2><?= lang('giftcards.transactions_title') ?></h2>

                            <?php if (!empty($transactions)) : ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mt-3">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><?= lang('giftcards.sr_no') ?></th>
                                                <th><?= lang('giftcards.card_number') ?></th>
                                                <th><?= lang('giftcards.order_number') ?></th>
                                                <th><?= lang('giftcards.type') ?></th>
                                                <th><?= lang('giftcards.amount') ?></th>
                                                <th><?= lang('giftcards.status') ?></th>
                                                <th><?= lang('giftcards.date') ?></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $sr = 1; ?>
                                            <?php foreach ($transactions as $txn) : ?>
                                                <?php
                                                $gcard_code = !empty($txn->gift_card_code)
                                                    ? $txn->gift_card_code
                                                    : (isset($gift_cards_map[$txn->gift_card_id]) ? $gift_cards_map[$txn->gift_card_id]->code : '-');

                                                $order_display = !empty($txn->order_number)
                                                    ? $txn->order_number
                                                    : (!empty($txn->order_id) ? $txn->order_id : '-');

                                                $statusLabels = [
                                                    0 => lang('giftcards.status_pending'),
                                                    1 => lang('giftcards.status_completed'),
                                                    2 => lang('giftcards.status_failed')
                                                ];

                                                $display_date = '-';
                                                if (!empty($txn->created_at) && $txn->created_at != '0000-00-00 00:00:00') {
                                                    try {
                                                        $dt = new DateTime($txn->created_at, new DateTimeZone('Asia/Kolkata'));
                                                        $dt->setTimezone(new DateTimeZone('Indian/Mauritius'));
                                                        $display_date = $dt->format('d M Y, H:i');
                                                    } catch (Exception $e) {
                                                        $display_date = date('d M Y, H:i', strtotime($txn->created_at));
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $sr++; ?></td>
                                                    <td><?= htmlspecialchars($gcard_code) ?></td>
                                                    <td><?= htmlspecialchars($order_display) ?></td>
                                                    <td>
                                                        <?= lang('giftcards.type_' . strtolower($txn->type)) ?>
                                                    </td>

                                                    <td><?= number_format($txn->amount, 2) ?></td>
                                                    <td><?= $statusLabels[$txn->status] ?? '-' ?></td>
                                                    <td><?= $display_date ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>

                                    </table>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-info mt-3" role="alert">
                                    <?= lang('giftcards.no_transactions') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div><!-- row -->
    </div><!-- container -->
</div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>
