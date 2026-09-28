<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <h1 class="head-name mb-4"><?= lang('notifications','Notifications') ?></h1>
        <?php $merchant_id = $this->session->userdata('merchant_id') ?: $this->session->userdata('RsUserId') ?: $this->session->userdata('user_id'); ?>
        <input type="hidden" id="merchant-id" value="<?= html_escape($merchant_id) ?>">
        <div class="text-end mb-3">
            <!-- Mark all read button -->
             <button id="mark-all" type="button" class="btn btn-primary purple-btn">
                <?= lang('mark_all_read','Mark all read') ?>
            </button> 
        </div>

        <div class="table-responsive">
        <table class="plan-table table table-bordered table-striped toped-table" border="1" cellpadding="8" cellspacing="0">
            <thead class="text-center ym-basic-merchant-plan-2 ym-basic-plan-merchant-1 merchant-test">
                <tr>
                    <th><?= lang('sr_no','Sr No#') ?></th>
                    <th><?= lang('title','Title') ?></th>
                    <th><?= lang('message','Message') ?></th>
                    <!-- <th><?= lang('type','Type') ?></th> -->
                    <th><?= lang('date','Date') ?></th>
                   <th class="action-new"><?= lang('action','Action') ?></th> 
                </tr>
            </thead>
            <tbody class="latest-section">
                <?php if (!empty($notifications)): ?>
                    <?php foreach($notifications as $index => $n): ?>
                        <?php

                            $row_class = $n['is_read'] ? '' : 'table-warning';

                        

                            $raw_title = isset($n['title']) ? $n['title'] : '';

                            $raw_msg   = isset($n['message']) ? $n['message'] : '';

                            $type      = isset($n['type']) ? $n['type'] : '';
                            $subtype   = isset($n['subtype']) ? strtolower(trim($n['subtype'])) : '';



                            $current_lang = $this->session->userdata('site_lang') ?? 'english';

                            $title = $raw_title;

                            $msg = $raw_msg;



                            if ($current_lang == 'french') {

                                switch ($type) {

                                    case 'helpdesk':

                                        $title = "Nouveau ticket d'assistance";

                                        $msg = str_replace(
                                            [
                                                "A new help desk ticket has been submitted for your product",
                                                "A new help desk ticket #",
                                                "has been submitted by Shopper.",
                                                "has been submitted by Merchant.",
                                                "by Shopper.",
                                                "by Merchant."
                                            ],
                                            [
                                                "Un nouveau ticket d'assistance a été soumis pour votre produit",
                                                "Un nouveau ticket d'assistance #",
                                                "a été soumis par Shopper.",
                                                "a été soumis par Merchant.",
                                                "par Shopper.",
                                                "par Merchant."
                                            ],
                                            $raw_msg
                                        );

                                        break;
                                    case 'product':

                                        if ($subtype == 'approve') {

                                            $title = "Produit approuvé";

                                            $msg = str_replace(
                                                [
                                                    'Your product "',
                                                    '" has been approved by admin.'
                                                ],
                                                [
                                                    'Votre produit "',
                                                    '" a été approuvé par l’administrateur.'
                                                ],
                                                $raw_msg
                                            );
                                        }

                                        elseif ($subtype == 'reject') {

                                            $title = "Produit rejeté";

                                            $msg = str_replace(
                                                [
                                                    'Your product "',
                                                    '" has been rejected by admin.'
                                                ],
                                                [
                                                    'Votre produit "',
                                                    '" a été rejeté par l’administrateur.'
                                                ],
                                                $raw_msg
                                            );
                                        }

                                        elseif ($subtype == 'approved') {

                                            $title = "Badge de produit reçu";

                                            $msg = str_replace(
                                                [
                                                    'Your product badge for "',
                                                    '" has been received.'
                                                ],
                                                [
                                                    'Votre badge de produit pour "',
                                                    '" a été reçu.'
                                                ],
                                                $raw_msg
                                            );
                                        }

                                        elseif ($subtype == 'rejected') {

                                            $title = "Badge de produit rejeté";

                                            $msg = str_replace(
                                                [
                                                    'Your product badge request for "',
                                                    '" has been rejected by the admin.'
                                                ],
                                                [
                                                    'Votre demande de badge de produit pour "',
                                                    '" a été rejetée par l’administrateur.'
                                                ],
                                                $raw_msg
                                            );
                                        }

                                        break;

            
                                    case 'order':

                                    // ==========================================
                                    // ORDER DELIVERED
                                    // ==========================================
                                    if (stripos($raw_msg, 'Delivered to Shopper') !== false) {

                                        // Get ES order number
                                        preg_match('/ES-\d+/i', $raw_msg, $matches);

                                        $orderNo = !empty($matches[0]) ? $matches[0] : '';

                                        // French title
                                        $title = 'Commande livrée';

                                        // French message
                                        if ($orderNo != '') {

                                            $msg = 'La commande ' . $orderNo . ' a été livrée au client.';

                                        } else {

                                            $msg = 'La commande a été livrée au client.';
                                        }

                                    } else {

                                        // ==========================================
                                        // NORMAL NEW ES ORDER
                                        // ==========================================

                                        $title = lang('notif_order_title');

                                        $msg = str_replace(
                                            [
                                                "You have received a new ES order",
                                                "from"
                                            ],
                                            [
                                                "Vous avez reçu une nouvelle commande ES ",
                                                "de"
                                            ],
                                            $raw_msg
                                        );
                                    }

                                    break;



                                    case 'payout':

                                    

                                        $title = 'Paiement effectué';

                                        $msg = str_replace("Your payout has been successfully paid for", "Votre paiement a été effectué avec succès pour", $raw_msg);

                                        break;



                                   
                                    
                                    case 'return':

                                        $title = 'Nouvelle demande de retour';

                                        $msg = str_replace(
                                            'Shopper has submitted a return request order #',
                                            'Le client a soumis une demande de retour pour la commande #',
                                            $raw_msg
                                        );

                                        break;


                                    case 'replacement':

                                        $title = 'Nouvelle demande de remplacement';

                                        $msg = str_replace(
                                            'Shopper has submitted a replacement request order #',
                                            'Le client a soumis une demande de remplacement pour la commande #',
                                            $raw_msg
                                        );

                                        break;
                                    case 'product_question':

                                        $title = "Nouveau message d'un acheteur";

                                        $msg = str_replace(
                                            [
                                                'New message from shopper',
                                                'You have received a new question for your product'
                                            ],
                                            [
                                                "Nouveau message d'un acheteur",
                                                "Vous avez reçu une nouvelle question pour votre produit"
                                            ],
                                            $raw_msg
                                        );

                                        break;
                                    case 'es_order':

                                        // Pickup received
                                        if ($subtype == 'pickup_received') {

                                            preg_match('/ES-\d+/i', $raw_title, $matches);

                                            $orderNo = !empty($matches[0]) ? $matches[0] : '';

                                            $title = '#' . $orderNo . ' reçu à l’entrepôt YM';

                                            $msg = '#' . $orderNo . ' reçu à l’entrepôt YM';
                                        }

                                        
                                        break;


                                    

                                    
                                }

                            }



                            $created = isset($n['created_at']) && !empty($n['created_at'])

                                ? date('d-m-Y H:i:s', strtotime($n['created_at']))

                                : '';

                        ?>
                        <tr data-id="<?= $n['id'] ?>" class="<?= $row_class ?>">
                            <td class="text-center"><?= ($index + 1) ?></td>
                            <td><?= html_entity_decode($title, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= nl2br(html_entity_decode($msg, ENT_QUOTES, 'UTF-8')) ?></td>
                            <!-- <td class="text-center"><?= html_escape($type) ?></td> -->
                            <td class="text-center"><?= html_escape($created) ?></td>
                             <td class="test-section-new text-center">
                                <!-- <button type="button" class="btn btn-sm btn-primary view-notification" data-id="<?= $n['id'] ?>">
                                    <?= lang('view','View') ?>
                                </button> -->

                                <?php if ($n['is_read']): ?>
                                    <!-- <button type="button" class="btn btn-sm btn-outline-primary mark-unread" data-id="<?= $n['id'] ?>">
                                        <?= lang('mark_unread','Mark unread') ?>
                                    </button> -->
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary mark-read" data-id="<?= $n['id'] ?>" style="cursor: pointer !important;">
                                        <?= lang('mark_read','Mark read') ?>
                                    </button>
                                <?php endif; ?>
                            </td> 
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center"><?= lang('no_notifications','No notifications') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

    </div>
</main>

<style>
    .mark-read,
    .mark-unread,
    .test-section-new button {
        cursor: pointer !important;
    }
</style>

<?php $this->load->view('common/fbc-user/footer'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
        var LANG_MARK_READ = <?= json_encode(strip_tags(lang('mark_read','Mark read'))) ?>;
        var LANG_MARK_UNREAD = <?= json_encode(strip_tags(lang('mark_unread','Mark unread'))) ?>;
        var LANG_ERROR = <?= json_encode(strip_tags(lang('error_occurred','An error occurred'))) ?>;
        var LANG_CONFIRM_MARK_ALL = <?= json_encode(strip_tags(lang('confirm_mark_all','Mark all notifications as read?'))) ?>;
    
    function post(url, data, cb) {
        var xhr = new XMLHttpRequest();
        var params = new URLSearchParams();
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var csrfInput = document.querySelector('input[name*="csrf"]');

        if (csrfToken) params.append('csrf_token', csrfToken.getAttribute('content'));
        else if (csrfInput) params.append(csrfInput.name, csrfInput.value);

        for (var k in data) params.append(k, data[k]);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            var res = {};
            try { res = JSON.parse(xhr.responseText); } catch (e) { res = { status: 'error' }; }
            if (typeof cb === 'function') cb(res);
        };
        xhr.send(params.toString());
    }

    function updateUnreadCount(delta) {
        var el = document.getElementById('notification-count');
        if (!el) return;
        var cur = parseInt(el.innerText, 10) || 0;
        cur = Math.max(0, cur + delta);
        el.innerText = cur;
        el.style.display = (cur === 0) ? 'none' : '';
    }

    function buildActionButtons(id, isRead) {
        if (isRead) return ''; 
        return '<button type="button" class="btn btn-sm btn-primary mark-read" data-id="'+id+'">'+LANG_MARK_READ+'</button>';
    }

   
    var markAllBtn = document.getElementById('mark-all');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm(LANG_CONFIRM_MARK_ALL)) return;

            post('notifications/mark_all', {}, function (res) {
                if (res.status === 'ok') {
                    document.querySelectorAll('tr[data-id]').forEach(function (tr) {
                        tr.classList.remove('table-warning');
                        var actionCell = tr.querySelector('.test-section-new');
                        if (actionCell) actionCell.innerHTML = '';
                    });
                    updateUnreadCount(-999); // Reset counter
                } else {
                    alert(LANG_ERROR);
                }
            });
        });
    }

    
    document.addEventListener('click', function (e) {
        var markReadBtn = e.target.closest('.mark-read');
        
        if (markReadBtn) {
            e.preventDefault();
            var id = markReadBtn.getAttribute('data-id');
            var tr = markReadBtn.closest('tr');

            post('notifications/mark', { id: id, is_read: 1 }, function (res) {
                if (res.status === 'ok') {
                    if (tr) {
                        tr.classList.remove('table-warning');
                        var actionCell = tr.querySelector('.test-section-new');
                        if (actionCell) actionCell.innerHTML = '';
                    }
                    updateUnreadCount(-1);
                } else {
                    alert(LANG_ERROR);
                }
            });
        }
    });
});
</script>