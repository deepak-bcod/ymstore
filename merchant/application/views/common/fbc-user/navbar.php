<?php
$ci = get_instance();
$id = $this->session->userdata('LoginID');

// Get user info
$FBCData = $this->CommonModel->getSingleDataByID('publisher', ['id' => $id], 'email,id');

// Get unread notifications (latest 3)
$ci->load->model('Notification_model');
$unread_notifications = $ci->Notification_model->get_for('merchant', $id, 3, 0, true);
$unread_count = $ci->Notification_model->unread_count('merchant', $id);
?>
<?php 
    $CI =& get_instance();
    $current_lang = $CI->session->userdata('site_lang') ?? 'english';
?>
<nav class="navbar sticky-top flex-md-nowrap p-0">

  <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?= base_url('dashboard') ?>" style="font-size:27px;font-weight:600;letter-spacing:1px;color:#ffd703;">
    <img src="<?= SKIN_IMG ?>yellow-markets-logo-yellow-white.png" alt="YellowMarket" width="170px" height="34px">
  </a>

  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>
  </button>

  <div class="d-flex top-right right-nav-top">

    <div class="left-line pro-info">
      <a href="<?= base_url('PublisherController/editMerchant/' . rtrim(strtr(base64_encode($id), '+/', '-_'), '=')) ?>">
        <div class="d-flex align-items-center">
          <span class="pro-name"><?= $FBCData->email ?></span>
          <i class="fa fa-user"></i>
        </div>
      </a>
    </div>

    <!-- Notification Bell -->
    <div class="left-line bell-right position-relative">
      <a href="<?= base_url('notifications') ?>" id="notification-bell">
        <i class="fas fa-bell"></i>
        <?php if($unread_count > 0): ?>
          <span id="notification-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle"><?= $unread_count ?></span>
        <?php endif; ?>
      </a>

      <!-- Notification Popup -->
      <!-- Notification Popup -->
<div id="notification-popup" class="card position-absolute" style="right:0; top:35px; width:300px; display:none; z-index:9999;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= $this->lang->line('notifications') ?></span>
    </div>
    <ul class="list-group list-group-flush">
        <?php if(!empty($unread_notifications)): ?>
            <?php foreach($unread_notifications as $n): 

            $display_title = $n['title'];
            $display_msg   = $n['message'];

            $type = isset($n['type']) ? strtolower(trim($n['type'])) : '';
            $subtype = isset($n['subtype']) ? strtolower(trim($n['subtype'])) : '';

            if ($current_lang == 'french') {

                switch ($type) {

                    case 'product':

                        // Product approved
                        if ($subtype == 'approve') {

                            $display_title = 'Produit approuvé';

                            $display_msg = str_replace(
                                [
                                    'Your product "',
                                    '" has been approved by admin.'
                                ],
                                [
                                    'Votre produit "',
                                    '" a été approuvé par l’administrateur.'
                                ],
                                $display_msg
                            );

                        }

                        // Product rejected
                        elseif ($subtype == 'reject') {

                            $display_title = 'Produit rejeté';

                            $display_msg = str_replace(
                                [
                                    'Your product "',
                                    '" has been rejected by admin.'
                                ],
                                [
                                    'Votre produit "',
                                    '" a été rejeté par l’administrateur.'
                                ],
                                $display_msg
                            );

                        }

                        // Product badge received
                        elseif ($subtype == 'approved') {

                            $display_title = 'Badge de produit reçu';

                            $display_msg = str_replace(
                                [
                                    'Your product badge for "',
                                    '" has been received.'
                                ],
                                [
                                    'Votre badge de produit pour "',
                                    '" a été reçu.'
                                ],
                                $display_msg
                            );

                        }

                        // Product badge rejected
                        elseif ($subtype == 'rejected') {

                            $display_title = 'Badge de produit rejeté';

                            $display_msg = str_replace(
                                [
                                    'Your product badge request for "',
                                    '" has been rejected by the admin.'
                                ],
                                [
                                    'Votre demande de badge de produit pour "',
                                    '" a été rejetée par l’administrateur.'
                                ],
                                $display_msg
                            );
                        }

                        break;


                    case 'order':

    

                        if (stripos($display_msg, 'Delivered to Shopper') !== false) {

                            preg_match('/ES-\d+/i', $display_msg, $matches);

                            $orderNo = !empty($matches[0]) ? $matches[0] : '';

                            $display_title = 'Commande livrée';

                            if ($orderNo != '') {

                                $display_msg = 'La commande ' . $orderNo . ' a été livrée au client.';

                            } else {

                                $display_msg = 'La commande a été livrée au client.';
                            }

                        } else {


                        $display_title = 'Nouvelle commande ES';

                        $display_msg = str_ireplace(
                            [
                                'You have received a new ES order',
                                'You have received a new B2B order',
                                'from'
                            ],
                            [
                                'Vous avez reçu une nouvelle commande ES',
                                'Vous avez reçu une nouvelle commande B2B',
                                'de'
                            ],
                            $display_msg
                        );
                    }

                    break;


                    case 'helpdesk':

                        $display_title = $this->lang->line('notif_helpdesk_title');

                        $display_msg = str_replace(
                            [
                                'A new help desk ticket has been submitted for your product',
                                'by Shopper.',
                                'by Merchant.'
                            ],
                            [
                                'Un nouveau ticket d’assistance a été soumis pour votre produit',
                                'par Shopper.',
                                'par Merchant.'
                            ],
                            $display_msg
                        );

                        break;


                    case 'return':

                        $display_title = 'Nouvelle demande de retour';

                        $display_msg = str_replace(
                            'Shopper has submitted a return request order #',
                            'Le client a soumis une demande de retour pour la commande #',
                            $display_msg
                        );

                        break;


                    case 'replacement':

                        $display_title = 'Nouvelle demande de remplacement';

                        $display_msg = str_replace(
                            'Shopper has submitted a replacement request order #',
                            'Le client a soumis une demande de remplacement pour la commande #',
                            $display_msg
                        );

                        break;


                    case 'product_question':

                        $display_title = "Nouveau message d'un acheteur";

                        $display_msg = str_replace(
                            [
                                'New message from shopper',
                                'You have received a new question for your product'
                            ],
                            [
                                "Nouveau message d'un acheteur",
                                "Vous avez reçu une nouvelle question pour votre produit"
                            ],
                            $display_msg
                        );

                        break;
                    case 'es_order':

   
                        if ($subtype == 'pickup_received') {

                            preg_match('/ES-\d+/i', $display_title, $matches);

                            $orderNo = !empty($matches[0]) ? $matches[0] : '';

                            $display_title = '#' . $orderNo . ' reçu à l’entrepôt YM';

                            $display_msg = '#' . $orderNo . ' reçu à l’entrepôt YM';
                        }

                        break;
                    case 'payout':

                        $display_title = 'Paiement effectué';

                        $display_msg = str_replace(
                            'Your payout has been successfully paid for',
                            'Votre paiement a été effectué avec succès pour',
                            $display_msg
                        );

                        break;
                    case 'faq':

                        if ($subtype == 'admin_reply') {

                            if ($current_lang == 'french') {

                                $display_title = 'Réponse à la FAQ';

                                $display_msg = "L'administrateur a répondu à votre question FAQ.";

                            } else {

                                $display_title = 'FAQ Reply';

                                $display_msg = 'Admin has replied to your FAQ.';

                            }

                        }

                        break;

                    case 'addon_purchase':
                    case 'addon':

                        $display_title = 'Service Add-on Activé';

                        $display_msg = str_replace(
                            [
                                'Your add-on service "',
                                'Your add-on service \'',
                                '" has been successfully activated.',
                                '\' has been successfully activated.',
                                'has been successfully activated.'
                            ],
                            [
                                'Votre service add-on "',
                                'Votre service add-on \'',
                                '" a été activé avec succès.',
                                '\' a été activé avec succès.',
                                'a été activé avec succès.'
                            ],
                            $display_msg
                        );

                        break;

                    case 'subscription':
                    case 'subscription_renewal':
                    case 'subscription_order':

                        if (stripos($display_title, 'Subscription Renewal') !== false) {
                            $display_title = "Renouvellement d'abonnement";
                        } elseif (stripos($display_title, 'Subscription Expired') !== false) {
                            $display_title = "Abonnement expiré";
                        } elseif (stripos($display_title, 'Subscription Expiry Reminder') !== false) {
                            $display_title = "Rappel d'expiration d'abonnement";
                        } elseif (stripos($display_title, 'Subscription Activated') !== false) {
                            $display_title = "Abonnement activé";
                        }

                        $display_msg = str_ireplace(
                            [
                                'Your Subscription is renewed to ',
                                'Your subscription is renewed to ',
                                'Your subscription plan has been renewed to ',
                                'Your subscription will expire in ',
                                'Your subscription has expired.',
                                'Your subscription has expired',
                                'Your subscription for plan ',
                                ' has been activated.',
                                'Subscription payment confirmed for '
                            ],
                            [
                                'Votre abonnement est renouvelé pour ',
                                'Votre abonnement est renouvelé pour ',
                                'Votre plan d’abonnement a été renouvelé pour ',
                                'Votre abonnement expirera dans ',
                                'Votre abonnement a expiré.',
                                'Votre abonnement a expiré',
                                'Votre abonnement pour le plan ',
                                ' a été activé.',
                                'Paiement de l’abonnement confirmé pour '
                            ],
                            $display_msg
                        );

                        break;

                    case 'order_resolution':
                    case 'resolution':
                    case 'order_resolution_ticket':

                        if ($subtype == 'new_ticket' || stripos($display_title, 'New Order Resolution Ticket') !== false) {
                            $display_title = 'Nouveau ticket de résolution de commande';
                        } elseif ($subtype == 'ticket_closed' || stripos($display_title, 'Order Resolution Closed') !== false) {
                            $display_title = 'Ticket de résolution fermé';
                        } elseif ($subtype == 'closed_final' || stripos($display_title, 'Closed (Final)') !== false) {
                            $display_title = 'Résolution de commande - Fermée (Finale)';
                        } elseif ($subtype == 'shopper_dispute_decision' || stripos($display_title, 'Shopper Dispute Decision') !== false) {
                            $display_title = 'Résolution de commande - Décision contestée par l’acheteur';
                        } elseif ($subtype == 'dispute_refund_decision' || stripos($display_title, 'Dispute Refund Decision') !== false) {
                            $display_title = 'Résolution de commande - Contestation de la décision de remboursement';
                        } elseif ($subtype == 'addon_purchase_requested' || stripos($display_title, 'Purchase Replacement Delivery AddOn') !== false) {
                            $display_title = 'Action requise : Acheter le service de livraison de remplacement';
                        } elseif ($subtype == 'admin_message' || stripos($display_title, 'Admin Message on Ticket') !== false) {
                            $display_title = preg_replace('/Admin Message on Ticket\s*#(\S+)/i', 'Message de l’administrateur sur le ticket #$1', $display_title);
                        } elseif (stripos($display_title, 'Order Resolution Reply on Ticket') !== false) {
                            $display_title = preg_replace('/Order Resolution Reply on Ticket\s*#(\S+)/i', 'Réponse à la résolution de commande sur le ticket #$1', $display_title);
                        } elseif (stripos($display_title, 'Order Refund Approved Update') !== false) {
                            $display_title = 'Mise à jour : Remboursement de commande approuvé';
                        } elseif (stripos($display_title, 'Order Replacement Approved Update') !== false) {
                            $display_title = 'Mise à jour : Remplacement de commande approuvé';
                        } elseif (stripos($display_title, 'Order Replacement Completed Update') !== false) {
                            $display_title = 'Mise à jour : Remplacement de commande terminé';
                        } elseif (stripos($display_title, 'Resolution ticket is closed') !== false) {
                            $display_title = 'Le ticket de résolution est fermé';
                        } elseif (stripos($display_title, 'Resolution ticket approved') !== false) {
                            $display_title = 'Ticket de résolution approuvé';
                        } elseif (stripos($display_title, 'Resolution ticket denied') !== false) {
                            $display_title = 'Ticket de résolution refusé';
                        }

                        break;

                }

                // Global / Fallback check for Subscription & Resolution notifications
                // Ensures older notifications with empty/generic type or unhandled subtypes are also translated
                if (stripos($display_title, 'Subscription Renewal') !== false) {
                    $display_title = "Renouvellement d'abonnement";
                } elseif (stripos($display_title, 'New Order Resolution Ticket') !== false) {
                    $display_title = "Nouveau ticket de résolution de commande";
                } elseif (stripos($display_title, 'Order Resolution Closed') !== false) {
                    $display_title = "Ticket de résolution fermé";
                } elseif (stripos($display_title, 'Order Resolution - Closed (Final)') !== false) {
                    $display_title = "Résolution de commande - Fermée (Finale)";
                } elseif (stripos($display_title, 'Order Resolution - Dispute Refund Decision') !== false) {
                    $display_title = "Résolution de commande - Contestation de la décision de remboursement";
                } elseif (stripos($display_title, 'Order Resolution - Shopper Dispute Decision') !== false) {
                    $display_title = "Résolution de commande - Décision contestée par l’acheteur";
                } elseif (stripos($display_title, 'Action Required: Purchase Replacement Delivery AddOn') !== false) {
                    $display_title = "Action requise : Acheter le service de livraison de remplacement";
                } elseif (preg_match('/Admin Message on Ticket\s*#(\S+)/i', $display_title)) {
                    $display_title = preg_replace('/Admin Message on Ticket\s*#(\S+)/i', 'Message de l’administrateur sur le ticket #$1', $display_title);
                } elseif (preg_match('/Order Resolution Reply on Ticket\s*#(\S+)/i', $display_title)) {
                    $display_title = preg_replace('/Order Resolution Reply on Ticket\s*#(\S+)/i', 'Réponse à la résolution de commande sur le ticket #$1', $display_title);
                }

                // Subscription messages
                if (stripos($display_msg, 'Your Subscription is renewed to') !== false) {
                    $display_msg = str_ireplace('Your Subscription is renewed to ', 'Votre abonnement est renouvelé pour ', $display_msg);
                } elseif (stripos($display_msg, 'Your subscription is renewed to') !== false) {
                    $display_msg = str_ireplace('Your subscription is renewed to ', 'Votre abonnement est renouvelé pour ', $display_msg);
                } elseif (stripos($display_msg, 'Your subscription plan has been renewed to') !== false) {
                    $display_msg = str_ireplace('Your subscription plan has been renewed to ', 'Votre plan d’abonnement a été renouvelé pour ', $display_msg);
                } elseif (stripos($display_msg, 'Your subscription will expire in') !== false) {
                    $display_msg = str_ireplace('Your subscription will expire in ', 'Votre abonnement expirera dans ', $display_msg);
                    $display_msg = preg_replace('/\s*days?\./i', ' jours.', $display_msg);
                } elseif (stripos($display_msg, 'Your subscription has expired') !== false) {
                    $display_msg = str_ireplace(['Your subscription has expired.', 'Your subscription has expired'], 'Votre abonnement a expiré.', $display_msg);
                }

                // Resolution messages
                if (stripos($display_msg, 'New Resolution Ticket') !== false && (stripos($display_msg, 'raised for Order') !== false || stripos($display_msg, 'created for Order') !== false)) {
                    $display_msg = preg_replace_callback(
                        '/New Resolution Ticket\s*(#\S+)?\s*(raised for Order|created for Order)\s*(#\S+)?(?:\s*by\s*(.*?))?\./i',
                        function($m) {
                            $ticket = $m[1] ?? '';
                            $order = $m[3] ?? '';
                            $by = !empty($m[4]) ? ' par ' . trim($m[4]) : '';
                            return 'Nouveau ticket de résolution ' . $ticket . ' créé pour la commande ' . $order . $by . '.';
                        },
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Order Resolution Ticket') !== false && stripos($display_msg, 'has been marked as Close') !== false) {
                    $display_msg = preg_replace(
                        '/Order Resolution Ticket\s*(#\S+)?\s*has been marked as Close(?:d)? by Yellow Markets Support \(@Help\)\./i',
                        'Le ticket de résolution de commande $1 a été marqué comme fermé par le support Yellow Markets (@Help).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Order Resolution Ticket') !== false && stripos($display_msg, 'has been closed (Final)') !== false) {
                    $display_msg = preg_replace(
                        '/Order Resolution Ticket\s*(#\S+)?\s*has been closed \(Final\)\./i',
                        'Le ticket de résolution de commande $1 a été fermé (Final).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Shopper has disputed refund') !== false) {
                    $display_msg = preg_replace(
                        '/Shopper has disputed refund decision for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\.\s*Case is under Yellow Markets review\./i',
                        'L’acheteur a contesté la décision de remboursement pour le ticket $1 (Commande $2). Le dossier est en cours d’examen par Yellow Markets.',
                        $display_msg
                    );
                    $display_msg = preg_replace(
                        '/Shopper has disputed refund denial for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\.\s*Investigation required\./i',
                        'L’acheteur a contesté le refus de remboursement pour le ticket $1 (Commande $2). Enquête requise.',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Dispute resolved in your favour for Ticket') !== false) {
                    $display_msg = preg_replace(
                        '/Dispute resolved in your favour for Ticket\s*(#\S+)?\.\s*Ticket is closed \(Final\)\./i',
                        'Litige résolu en votre faveur pour le ticket $1. Le ticket est fermé (Final).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'After investigation, resolution was decided in favour of merchant for Ticket') !== false) {
                    $display_msg = preg_replace(
                        '/After investigation,\s*resolution was decided in favour of merchant for Ticket\s*(#\S+)?\.\s*Ticket is closed \(Final\)\./i',
                        'Après enquête, la décision a été prise en faveur du marchand pour le ticket $1. Le ticket est fermé (Final).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Accounting (@Acct) has requested you to purchase') !== false) {
                    $display_msg = preg_replace(
                        '/Accounting \(@Acct\) has requested you to purchase the replacement delivery AddOn Service for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\./i',
                        'La comptabilité (@Acct) vous a demandé d’acheter le service complémentaire de livraison de remplacement pour le ticket $1 (Commande $2).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'YellowMarkets Admin posted a message on Ticket') !== false) {
                    $display_msg = preg_replace(
                        '/YellowMarkets Admin posted a message on Ticket\s*(#\S+)?\./i',
                        'L’administrateur YellowMarkets a publié un message sur le ticket $1.',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Merchant replied to your Order Resolution request') !== false) {
                    $display_msg = str_ireplace(
                        'Merchant replied to your Order Resolution request.',
                        'Le marchand a répondu à votre demande de résolution de commande.',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Your refund request for Ticket') !== false) {
                    $display_msg = preg_replace(
                        '/Your refund request for Ticket\s*(#\S+)?\s*has been approved \(Order\s*(#\S+)?\)\./i',
                        'Votre demande de remboursement pour le ticket $1 a été approuvée (Commande $2).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Your replacement request for Ticket') !== false) {
                    $display_msg = preg_replace(
                        '/Your replacement request for Ticket\s*(#\S+)?\s*has been approved \(Order\s*(#\S+)?\)\./i',
                        'Votre demande de remplacement pour le ticket $1 a été approuvée (Commande $2).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Your replacement for Ticket') !== false && stripos($display_msg, 'has been marked as completed') !== false) {
                    $display_msg = preg_replace(
                        '/Your replacement for Ticket\s*(#\S+)?\s*has been marked as completed \(Order\s*(#\S+)?\)\./i',
                        'Votre remplacement pour le ticket $1 a été marqué comme terminé (Commande $2).',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Yellow Market team has replied to your resolution ticket') !== false || stripos($display_msg, 'Yellow Markets Support team has replied to your resolution ticket') !== false) {
                    $display_msg = preg_replace(
                        '/Yellow Markets?\s*(?:Support\s*)?team has replied to your resolution ticket\s*\((.*?)\)/i',
                        'L’équipe de support Yellow Markets a répondu à votre ticket de résolution ($1)',
                        $display_msg
                    );
                }

                if (stripos($display_msg, 'Your resolution ticket no') !== false) {
                    $display_msg = preg_replace(
                        '/Your resolution ticket no\s*\((.*?)\)\s*is closed\./i',
                        'Votre ticket de résolution n° ($1) est fermé.',
                        $display_msg
                    );
                    $display_msg = preg_replace(
                        '/Your resolution ticket no\s*\((.*?)\)\s*is approved by The yellow market team,?\s*Team is working on it and will update you asap\./i',
                        'Votre ticket de résolution n° ($1) a été approuvé par l’équipe Yellow Market. L’équipe y travaille et vous informera dès que possible.',
                        $display_msg
                    );
                    $display_msg = preg_replace(
                        '/Your resolution ticket no\s*\((.*?)\)\s*is denied by The yellow market team\./i',
                        'Votre ticket de résolution n° ($1) a été refusé par l’équipe Yellow Market.',
                        $display_msg
                    );
                }

            }
        ?>
                <li class="list-group-item small d-flex justify-content-between align-items-start" data-id="<?= $n['id'] ?>">
                    <div class="flex-grow-1">
                        <strong><?= html_entity_decode(html_escape($display_title), ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <span><?= nl2br(html_entity_decode(html_escape($display_msg), ENT_QUOTES, 'UTF-8')) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item text-center small">
                <?= $this->lang->line('no_notifications') ?>
            </li>
        <?php endif; ?>
        <li class="list-group-item text-center">
            <a href="<?= base_url('notifications') ?>">
                <?= ($current_lang == 'french') ? 'Voir toutes les notifications' : 'See all notifications' ?>
            </a>
        </li>
    </ul>
</div>
    </div>

    <div class="left-line bell-right">
      <a href="<?= base_url('logout') ?>" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    
    <form method="post" action="<?= base_url('language/switch') ?>" style="display:inline;" class="lang-form">
        <select name="site_lang" onchange="this.form.submit()" class="form-select" style="display:inline;">
            <option value="english" <?= ($current_lang == 'english') ? 'selected' : '' ?>>English</option>
            <option value="french" <?= ($current_lang == 'french') ? 'selected' : '' ?>>French</option>
        </select>
    </form>

  </div>
</nav>

<div class="ajax-spinner" id="ajax-spinner"><div class="ajax-spinner-inner"></div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notification-bell');
    const popup = document.getElementById('notification-popup');

    // Function to mark all notifications as read
    function markAllAsRead() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= base_url('notifications/mark_all') ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'ok') {
                        // Update the badge count to 0
                        const countEl = document.getElementById('notification-count');
                        if (countEl) {
                            countEl.style.display = 'none';
                        }
                        // Remove all notification items from popup
                        const listItems = popup.querySelectorAll('li[data-id]');
                        listItems.forEach(item => item.remove());
                        // Update popup content
                        const ul = popup.querySelector('.list-group');
                        ul.innerHTML = '<li class="list-group-item text-center small">No unread notifications</li>';
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        xhr.send();
    }

    // Function to mark single notification as read
    function markAsRead(notificationId, listItem) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        formData.append('id', notificationId);
        formData.append('is_read', '1');
        
        xhr.open('POST', '<?= base_url('notifications/mark') ?>', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'ok') {
                        // Remove this notification from popup
                        listItem.remove();
                        // Update badge count
                        const countEl = document.getElementById('notification-count');
                        if (countEl) {
                            const currentCount = parseInt(countEl.textContent, 10);
                            if (currentCount > 1) {
                                countEl.textContent = currentCount - 1;
                            } else {
                                countEl.style.display = 'none';
                            }
                        }
                        // If no more notifications, show empty message
                        const remainingItems = popup.querySelectorAll('li[data-id]');
                        if (remainingItems.length === 0) {
                            const ul = popup.querySelector('.list-group');
                            ul.innerHTML = '<li class="list-group-item text-center small">No unread notifications</li>';
                        }
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        xhr.send(formData);
    }

    // Toggle popup
    bell.addEventListener('click', function(e) {
        e.preventDefault();
        popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
    });

    // Mark all button click
    /*
    document.getElementById('mark-all-popup').addEventListener('click', function(e) {
        e.stopPropagation();
        markAllAsRead();
    });
    */

    // Individual mark read buttons
    popup.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-read-btn') || e.target.closest('.mark-read-btn')) {
            e.stopPropagation();
            const button = e.target.classList.contains('mark-read-btn') ? e.target : e.target.closest('.mark-read-btn');
            const notificationId = button.getAttribute('data-id');
            const listItem = button.closest('li[data-id]');
            if (notificationId && listItem) {
                markAsRead(notificationId, listItem);
            }
        }
    });

    // Hide popup if clicked outside
    document.addEventListener('click', function(e) {
        if (!bell.contains(e.target) && !popup.contains(e.target)) {
            popup.style.display = 'none';
        }
    });
});
</script>

<style>
#notification-count {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
    border-radius: 50%;
}
#notification-popup {
    max-height: 400px;
    overflow-y: auto;
}
#notification-popup ul li {
    word-wrap: break-word;
}
</style>
