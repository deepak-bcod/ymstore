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
                                        
                                    case 'faq':

                                        if ($subtype == 'admin_reply') {

                                            $title = "Réponse à la FAQ";

                                            $msg = "L'administrateur a répondu à votre question FAQ.";

                                        }

                                        break;

                                    case 'addon_purchase':
                                    case 'addon':

                                        $title = "Service Add-on Activé";

                                        $msg = str_replace(
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
                                            $raw_msg
                                        );

                                        break;

                                    case 'subscription':
                                    case 'subscription_renewal':
                                    case 'subscription_order':

                                        if (stripos($title, 'Subscription Renewal') !== false) {
                                            $title = "Renouvellement d'abonnement";
                                        } elseif (stripos($title, 'Subscription Expired') !== false) {
                                            $title = "Abonnement expiré";
                                        } elseif (stripos($title, 'Subscription Expiry Reminder') !== false) {
                                            $title = "Rappel d'expiration d'abonnement";
                                        } elseif (stripos($title, 'Subscription Activated') !== false) {
                                            $title = "Abonnement activé";
                                        }

                                        $msg = str_ireplace(
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
                                            $msg
                                        );

                                        break;

                                    case 'order_resolution':
                                    case 'resolution':
                                    case 'order_resolution_ticket':

                                        if ($subtype == 'new_ticket' || stripos($title, 'New Order Resolution Ticket') !== false) {
                                            $title = 'Nouveau ticket de résolution de commande';
                                        } elseif ($subtype == 'ticket_closed' || stripos($title, 'Order Resolution Closed') !== false) {
                                            $title = 'Ticket de résolution fermé';
                                        } elseif ($subtype == 'closed_final' || stripos($title, 'Closed (Final)') !== false) {
                                            $title = 'Résolution de commande - Fermée (Finale)';
                                        } elseif ($subtype == 'shopper_dispute_decision' || stripos($title, 'Shopper Dispute Decision') !== false) {
                                            $title = 'Résolution de commande - Décision contestée par l’acheteur';
                                        } elseif ($subtype == 'dispute_refund_decision' || stripos($title, 'Dispute Refund Decision') !== false) {
                                            $title = 'Résolution de commande - Contestation de la décision de remboursement';
                                        } elseif ($subtype == 'addon_purchase_requested' || stripos($title, 'Purchase Replacement Delivery AddOn') !== false) {
                                            $title = 'Action requise : Acheter le service de livraison de remplacement';
                                        } elseif ($subtype == 'admin_message' || stripos($title, 'Admin Message on Ticket') !== false) {
                                            $title = preg_replace('/Admin Message on Ticket\s*#(\S+)/i', 'Message de l’administrateur sur le ticket #$1', $title);
                                        } elseif (stripos($title, 'Order Resolution Reply on Ticket') !== false) {
                                            $title = preg_replace('/Order Resolution Reply on Ticket\s*#(\S+)/i', 'Réponse à la résolution de commande sur le ticket #$1', $title);
                                        } elseif (stripos($title, 'Order Refund Approved Update') !== false) {
                                            $title = 'Mise à jour : Remboursement de commande approuvé';
                                        } elseif (stripos($title, 'Order Replacement Approved Update') !== false) {
                                            $title = 'Mise à jour : Remplacement de commande approuvé';
                                        } elseif (stripos($title, 'Order Replacement Completed Update') !== false) {
                                            $title = 'Mise à jour : Remplacement de commande terminé';
                                        } elseif (stripos($title, 'Resolution ticket is closed') !== false) {
                                            $title = 'Le ticket de résolution est fermé';
                                        } elseif (stripos($title, 'Resolution ticket approved') !== false) {
                                            $title = 'Ticket de résolution approuvé';
                                        } elseif (stripos($title, 'Resolution ticket denied') !== false) {
                                            $title = 'Ticket de résolution refusé';
                                        }

                                        break;

                                }

                                // Global / Fallback check for Subscription & Resolution notifications
                                // Ensures older notifications with empty/generic type or unhandled subtypes are also translated
                                if (stripos($title, 'Subscription Renewal') !== false) {
                                    $title = "Renouvellement d'abonnement";
                                } elseif (stripos($title, 'New Order Resolution Ticket') !== false) {
                                    $title = "Nouveau ticket de résolution de commande";
                                } elseif (stripos($title, 'Order Resolution Closed') !== false) {
                                    $title = "Ticket de résolution fermé";
                                } elseif (stripos($title, 'Order Resolution - Closed (Final)') !== false) {
                                    $title = "Résolution de commande - Fermée (Finale)";
                                } elseif (stripos($title, 'Order Resolution - Dispute Refund Decision') !== false) {
                                    $title = "Résolution de commande - Contestation de la décision de remboursement";
                                } elseif (stripos($title, 'Order Resolution - Shopper Dispute Decision') !== false) {
                                    $title = "Résolution de commande - Décision contestée par l’acheteur";
                                } elseif (stripos($title, 'Action Required: Purchase Replacement Delivery AddOn') !== false) {
                                    $title = "Action requise : Acheter le service de livraison de remplacement";
                                } elseif (preg_match('/Admin Message on Ticket\s*#(\S+)/i', $title)) {
                                    $title = preg_replace('/Admin Message on Ticket\s*#(\S+)/i', 'Message de l’administrateur sur le ticket #$1', $title);
                                } elseif (preg_match('/Order Resolution Reply on Ticket\s*#(\S+)/i', $title)) {
                                    $title = preg_replace('/Order Resolution Reply on Ticket\s*#(\S+)/i', 'Réponse à la résolution de commande sur le ticket #$1', $title);
                                }

                                // Subscription messages
                                if (stripos($msg, 'Your Subscription is renewed to') !== false) {
                                    $msg = str_ireplace('Your Subscription is renewed to ', 'Votre abonnement est renouvelé pour ', $msg);
                                } elseif (stripos($msg, 'Your subscription is renewed to') !== false) {
                                    $msg = str_ireplace('Your subscription is renewed to ', 'Votre abonnement est renouvelé pour ', $msg);
                                } elseif (stripos($msg, 'Your subscription plan has been renewed to') !== false) {
                                    $msg = str_ireplace('Your subscription plan has been renewed to ', 'Votre plan d’abonnement a été renouvelé pour ', $msg);
                                } elseif (stripos($msg, 'Your subscription will expire in') !== false) {
                                    $msg = str_ireplace('Your subscription will expire in ', 'Votre abonnement expirera dans ', $msg);
                                    $msg = preg_replace('/\s*days?\./i', ' jours.', $msg);
                                } elseif (stripos($msg, 'Your subscription has expired') !== false) {
                                    $msg = str_ireplace(['Your subscription has expired.', 'Your subscription has expired'], 'Votre abonnement a expiré.', $msg);
                                }

                                // Resolution messages
                                if (stripos($msg, 'New Resolution Ticket') !== false && (stripos($msg, 'raised for Order') !== false || stripos($msg, 'created for Order') !== false)) {
                                    $msg = preg_replace_callback(
                                        '/New Resolution Ticket\s*(#\S+)?\s*(raised for Order|created for Order)\s*(#\S+)?(?:\s*by\s*(.*?))?\./i',
                                        function($m) {
                                            $ticket = $m[1] ?? '';
                                            $order = $m[3] ?? '';
                                            $by = !empty($m[4]) ? ' par ' . trim($m[4]) : '';
                                            return 'Nouveau ticket de résolution ' . $ticket . ' créé pour la commande ' . $order . $by . '.';
                                        },
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Order Resolution Ticket') !== false && stripos($msg, 'has been marked as Close') !== false) {
                                    $msg = preg_replace(
                                        '/Order Resolution Ticket\s*(#\S+)?\s*has been marked as Close(?:d)? by Yellow Markets Support \(@Help\)\./i',
                                        'Le ticket de résolution de commande $1 a été marqué comme fermé par le support Yellow Markets (@Help).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Order Resolution Ticket') !== false && stripos($msg, 'has been closed (Final)') !== false) {
                                    $msg = preg_replace(
                                        '/Order Resolution Ticket\s*(#\S+)?\s*has been closed \(Final\)\./i',
                                        'Le ticket de résolution de commande $1 a été fermé (Final).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Shopper has disputed refund') !== false) {
                                    $msg = preg_replace(
                                        '/Shopper has disputed refund decision for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\.\s*Case is under Yellow Markets review\./i',
                                        'L’acheteur a contesté la décision de remboursement pour le ticket $1 (Commande $2). Le dossier est en cours d’examen par Yellow Markets.',
                                        $msg
                                    );
                                    $msg = preg_replace(
                                        '/Shopper has disputed refund denial for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\.\s*Investigation required\./i',
                                        'L’acheteur a contesté le refus de remboursement pour le ticket $1 (Commande $2). Enquête requise.',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Dispute resolved in your favour for Ticket') !== false) {
                                    $msg = preg_replace(
                                        '/Dispute resolved in your favour for Ticket\s*(#\S+)?\.\s*Ticket is closed \(Final\)\./i',
                                        'Litige résolu en votre faveur pour le ticket $1. Le ticket est fermé (Final).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'After investigation, resolution was decided in favour of merchant for Ticket') !== false) {
                                    $msg = preg_replace(
                                        '/After investigation,\s*resolution was decided in favour of merchant for Ticket\s*(#\S+)?\.\s*Ticket is closed \(Final\)\./i',
                                        'Après enquête, la décision a été prise en faveur du marchand pour le ticket $1. Le ticket est fermé (Final).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Accounting (@Acct) has requested you to purchase') !== false) {
                                    $msg = preg_replace(
                                        '/Accounting \(@Acct\) has requested you to purchase the replacement delivery AddOn Service for Ticket\s*(#\S+)?\s*\(Order\s*(#\S+)?\)\./i',
                                        'La comptabilité (@Acct) vous a demandé d’acheter le service complémentaire de livraison de remplacement pour le ticket $1 (Commande $2).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'YellowMarkets Admin posted a message on Ticket') !== false) {
                                    $msg = preg_replace(
                                        '/YellowMarkets Admin posted a message on Ticket\s*(#\S+)?\./i',
                                        'L’administrateur YellowMarkets a publié un message sur le ticket $1.',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Merchant replied to your Order Resolution request') !== false) {
                                    $msg = str_ireplace(
                                        'Merchant replied to your Order Resolution request.',
                                        'Le marchand a répondu à votre demande de résolution de commande.',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Your refund request for Ticket') !== false) {
                                    $msg = preg_replace(
                                        '/Your refund request for Ticket\s*(#\S+)?\s*has been approved \(Order\s*(#\S+)?\)\./i',
                                        'Votre demande de remboursement pour le ticket $1 a été approuvée (Commande $2).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Your replacement request for Ticket') !== false) {
                                    $msg = preg_replace(
                                        '/Your replacement request for Ticket\s*(#\S+)?\s*has been approved \(Order\s*(#\S+)?\)\./i',
                                        'Votre demande de remplacement pour le ticket $1 a été approuvée (Commande $2).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Your replacement for Ticket') !== false && stripos($msg, 'has been marked as completed') !== false) {
                                    $msg = preg_replace(
                                        '/Your replacement for Ticket\s*(#\S+)?\s*has been marked as completed \(Order\s*(#\S+)?\)\./i',
                                        'Votre remplacement pour le ticket $1 a été marqué comme terminé (Commande $2).',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Yellow Market team has replied to your resolution ticket') !== false || stripos($msg, 'Yellow Markets Support team has replied to your resolution ticket') !== false) {
                                    $msg = preg_replace(
                                        '/Yellow Markets?\s*(?:Support\s*)?team has replied to your resolution ticket\s*\((.*?)\)/i',
                                        'L’équipe de support Yellow Markets a répondu à votre ticket de résolution ($1)',
                                        $msg
                                    );
                                }

                                if (stripos($msg, 'Your resolution ticket no') !== false) {
                                    $msg = preg_replace(
                                        '/Your resolution ticket no\s*\((.*?)\)\s*is closed\./i',
                                        'Votre ticket de résolution n° ($1) est fermé.',
                                        $msg
                                    );
                                    $msg = preg_replace(
                                        '/Your resolution ticket no\s*\((.*?)\)\s*is approved by The yellow market team,?\s*Team is working on it and will update you asap\./i',
                                        'Votre ticket de résolution n° ($1) a été approuvé par l’équipe Yellow Market. L’équipe y travaille et vous informera dès que possible.',
                                        $msg
                                    );
                                    $msg = preg_replace(
                                        '/Your resolution ticket no\s*\((.*?)\)\s*is denied by The yellow market team\./i',
                                        'Votre ticket de résolution n° ($1) a été refusé par l’équipe Yellow Market.',
                                        $msg
                                    );
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