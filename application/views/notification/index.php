<style>
    /* Limits the message cell to a maximum of 2 lines cleanly inside the box */
    .message-cell {
        max-width: 450px; /* Adjust width as needed */
    }
    .message-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal !important;
    }
</style>

<div class="container" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?= $this->lang->line('notifications_title'); ?></h2>

        <a href="<?= base_url('notification/mark_all_read'); ?>"
           class="btn btn-dark"
           style="background: #222; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px;">
            <?= $this->lang->line('mark_all_read'); ?>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered"
               style="background: #fff; border-collapse: collapse; width: 100%;">

            <thead>
                <tr style="background: #ffeb3b; color: #000;">
                    <th style="padding: 12px; border: 1px solid #ddd;"><?= $this->lang->line('notification_table_title'); ?></th>
                    <th style="padding: 12px; border: 1px solid #ddd;"><?= $this->lang->line('notification_table_message'); ?></th>
                    <!-- <th style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;">Type</th> -->
                    <th style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;"><?= $this->lang->line('notification_table_date'); ?></th>
                    <th style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;"><?= $this->lang->line('notification_table_action'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($notifications)): ?>

    <?php foreach ($notifications as $row): ?>
    <?php
$current_lang = $this->session->userdata('site_lang') ?? 'english';

$title = $row->title;
$message = $row->message;
$type = $row->type;
$subtype = isset($row->subtype) ? strtolower(trim($row->subtype)) : '';

if ($current_lang === 'french') {
    // 1. Product Question / Ask a Question
    if ($row->title === 'Ask a Question') {
        $title = 'Poser une question';
    }
    if ($row->message === 'You got a reply to your Ask a Question message.') {
        $message = "Vous avez reçu une réponse à votre message 'Poser une question'.";
    }

    // 2. Return Request
    if ($type === 'return' || strpos($row->title, 'Return Request') !== false) {
        $title = 'Demande de retour approuvée';
        if (strpos($row->message, 'is accepted') !== false) {
            $message = str_replace(['Your return for', 'is accepted.'], ['Votre retour pour', 'a été accepté.'], $row->message);
        }
    }

    // 3. Replacement Request (Approved or Rejected)
    if ($type === 'replacement' || strpos($row->title, 'Replacement Approved') !== false || strpos($row->title, 'Order Replacement Approved') !== false || strpos($row->title, 'Replacement') !== false) {
        if (strpos($row->title, 'Rejected') !== false || strpos($row->message, 'was rejected') !== false) {
            $title = 'Demande de remplacement rejetée';
            $message = str_replace(
                ['Your replacement request for', 'Order No', 'was rejected.'], 
                ['Votre demande de remplacement pour', 'Commande n°', 'a été rejetée.'], 
                $message
            );
        } else {
            $title = 'Demande de remplacement approuvée';
            $message = str_replace(
                ['Your replacement request for', 'has been approved.'], 
                ['Votre demande de remplacement pour', 'a été approuvée.'], 
                $message
            );
        }
    }

    // 3b. Refund Request
    if ($type === 'refund' || strpos($row->title, 'Refund Approved') !== false || strpos($row->title, 'Order Refund Approved') !== false) {
        $title = 'Demande de remboursement approuvée';
        
        $message = str_replace(
            ['Your refund request for', 'has been approved.'], 
            ['Votre demande de remboursement pour', 'a été approuvée.'], 
            $message
        );
    }

    // 3c. Order Resolution Reply & Denied Requests
    if (strpos($row->title, 'Order Resolution Reply') !== false) {
        $title = str_replace('Order Resolution Reply on Ticket', 'Réponse à la résolution de la commande sur le ticket', $row->title);
        $title = str_replace('Order Resolution Reply', 'Réponse à la résolution de la commande', $title);
        
        $message = str_replace(
            ['Merchant reply your Order Resolution', 'Merchant reply on your Order Resolution', 'Merchant replied to your Order Resolution request.'],
            ['Réponse du vendeur à votre résolution de commande', 'Réponse du vendeur à votre résolution de commande', 'Le vendeur a répondu à votre demande de résolution de commande.'],
            $message
        );
    }

    // 3d. Order Resolution - No Refund / No Replacement / Merchant Denied
    if (strpos($row->title, 'Order Resolution - No Refund') !== false || strpos($row->title, 'Order Resolution - No Replacement') !== false) {
        $title = str_replace(
            ['Order Resolution - No Refund for Product', 'Order Resolution - No Replacement for Product'], 
            ['Résolution de commande - Aucun remboursement pour le produit', 'Résolution de commande - Aucun remplacement pour le produit'], 
            $row->title
        );
        
        if (strpos($row->message, 'Merchant has denied') !== false) {
            $message = str_replace(
                ['Merchant has denied the refund request for', 'Merchant has denied the replacement request for'],
                ['Le vendeur a refusé la demande de remboursement pour', 'Le vendeur a refusé la demande de remplacement pour'],
                $message
            );
        }
    }

    // 3e. Order Resolution Subtypes (mark_done, close, close_final, resolution_approved, resolution_denied)
    if ($type === 'order_resolution') {
        if ($subtype === 'reply') {
            // Translate Titles like "Order resolution reply (RES-0037)"
            if (strpos($row->title, 'Order resolution reply') !== false) {
                $title = preg_replace('/Order resolution reply \((.*?)\)/i', 'Réponse à la résolution de la commande ($1)', $row->title);
            }
            // Translate Messages from both Yellow Market team & Support team
            if (strpos($row->message, 'has replied to your resolution ticket') !== false) {
                $message = preg_replace(
                    '/(Yellow Market team|Yellow Markets Support team) has replied to your resolution ticket \((.*?)\)/i',
                    'L’équipe d’assistance Yellow Markets a répondu à votre ticket de résolution ($2)',
                    $message
                );
            }
        } elseif ($subtype === 'mark_done') {
            if (strpos($row->title, 'Your Resolution for Order is refunded') !== false) {
                $title = 'Votre résolution de commande a été remboursée';
            }
            if (strpos($row->message, 'is refunded by Yellow markets team') !== false) {
                $message = preg_replace(
                    '/Your resolution \((.*?)\) for Order item\((.*?)\) is refunded by Yellow markets team\./',
                    'Votre résolution ($1) pour l’article de commande ($2) a été remboursée par l’équipe Yellow Markets.',
                    $row->message
                );
            }
        } elseif ($subtype === 'close') {
            if (strpos($row->title, 'Resolution ticket is closed') !== false) {
                $title = 'Le ticket de résolution est fermé';
            }
            if (strpos($row->message, 'is closed. If you wish to escalate') !== false) {
                $message = preg_replace(
                    '/Your resolution ticket no \((.*?)\) is closed\. If you wish to escalate with Yellow Market team plz go back inside you tick and raise resolution with yellow market team\./',
                    'Votre ticket de résolution n° ($1) est fermé. Si vous souhaitez faire appel auprès de l’équipe Yellow Market, veuillez retourner dans votre ticket et soumettre une résolution.',
                    $row->message
                );
            }
        } elseif ($subtype === 'close_final') {
            if (strpos($row->title, 'closed') !== false) {
                $title = preg_replace('/Resolution \((.*?)\) closed/', 'Résolution ($1) fermée', $row->title);
            }
            if (strpos($row->message, 'is closed') !== false) {
                $message = preg_replace(
                    '/Your resolution ticket no \((.*?)\) is closed\./',
                    'Votre ticket de résolution n° ($1) est fermé.',
                    $row->message
                );
            }
        } elseif ($subtype === 'resolution_approved') {
            if (strpos($row->title, 'Resolution ticket approved') !== false) {
                $title = 'Ticket de résolution approuvé';
            }
            if (strpos($row->message, 'is approved by The yellow market team') !== false) {
                $message = preg_replace(
                    '/Your resolution ticket no \((.*?)\) is approved by The yellow market team, Team is working on it and will update you asap\./',
                    'Votre ticket de résolution n° ($1) a été approuvé par l’équipe Yellow Market. L’équipe y travaille et vous tiendra informé dans les plus brefs délais.',
                    $row->message
                );
            }
        } elseif ($subtype === 'resolution_denied') {
            if (strpos($row->title, 'Resolution ticket denied') !== false) {
                $title = 'Ticket de résolution refusé';
            }
            if (strpos($row->message, 'is denied by The yellow market team') !== false) {
                $message = preg_replace(
                    '/Your resolution ticket no \((.*?)\) is denied by The yellow market team\./',
                    'Votre ticket de résolution n° ($1) a été refusé par l’équipe Yellow Market.',
                    $row->message
                );
            }
        }
    }

    // 4. Orders (Delivered, Shipped, Processing)
    if ($type === 'order' && $subtype !== 'delivery_failed' && $subtype !== 'new_sales_order') {
        if ($row->title === 'Order delivered') {
            $title = 'Commande livrée';
        } elseif ($row->title === 'Order Shipped') {
            $title = 'Commande expédiée';
        } elseif ($row->title === 'Order Processing') {
            $title = 'Commande en cours de traitement';
        }

        if (strpos($row->message, 'is delivered') !== false) {
            $message = str_replace(['Your order no.', 'is delivered.'], ['Votre commande n°', 'a été livrée.'], $row->message);
        } elseif (strpos($row->message, 'is shipped') !== false) {
            $message = str_replace(['Your order no.', 'is shipped.'], ['Votre commande n°', 'a été expédiée.'], $row:message ?? $row->message);
        } elseif (strpos($row->message, 'is under process') !== false) {
            $message = str_replace(['Your order no.', 'is under process.'], ['Votre commande n°', 'est en cours de traitement.'], $row->message);
        }
    }

    // 5. Order Failed / Delivery Failed
    if ($row->title === 'Order Failed' || ($type === 'order' && $subtype === 'delivery_failed')) {
        $title = 'Commande échouée';
        if (strpos($row->message, 'could not be delivered') !== false) {
            $message = str_replace(
                [
                    'Your order no.', 
                    'could not be delivered because of 2 unsuccessful attempts. Please contact the Yellow Markets team to collect your order.'
                ], 
                [
                    'Votre commande n°', 
                    "n'a pas pu être livrée en raison de 2 tentatives infructueuses. Veuillez contacter l'équipe Yellow Markets pour récupérer votre commande."
                ], 
                $row->message
            );
        }
    }

    // 6. New Order Placed
    if ($row->title === 'New order placed' || ($type === 'order' && $subtype === 'new_sales_order')) {
        $title = 'Nouvelle commande passée';
        if (strpos($row->message, 'has been successfully placed') !== false) {
            $message = str_replace(
                ['Your order #', 'has been successfully placed.'],
                ['Votre commande n°', 'a été passée avec succès.'],
                $row->message
            );
        }
    }

    // 7. Helpdesk Response & Replies
    if ($type === 'helpdesk') {
        if ($row->title === 'Helpdesk Response') {
            $title = 'Réponse du service d’assistance';
        } elseif ($row->title === 'Help Desk Ticket Reply') {
            $title = 'Réponse au ticket du service d’assistance';
        }

        if (strpos($row->message, 'is closed.') !== false) {
            $message = str_replace(
                ['Your Help desk Ticket', 'is closed.'],
                ['Votre ticket du service d’assistance', 'est fermé.'],
                $row->message
            );
        } elseif (strpos($row->message, 'has replied to your help desk ticket') !== false) {
            $message = str_replace(
                ['has replied to your help desk ticket', '#'],
                ['a répondu à votre ticket du service d’assistance', '#'],
                $row->message
            );
        }
    }
}
?>
                        <tr style="<?= ($row->is_read == 0)
                            ? 'background-color: #fffdf0; font-weight: bold;'
                            : ''; ?>">

                            <td style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;">
                                <?= htmlspecialchars($title); ?>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd;" class="message-cell">
                                <div class="message-text">
                                    <?= htmlspecialchars($message); ?>
                                </div>
                            </td>

                            <!-- <td style="padding: 12px; border: 1px solid #ddd; white-space: nowrap; text-align: center;">
                                <span class="badge badge-secondary" style="background: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                    <?= htmlspecialchars($type); ?>
                                </span>
                            </td> -->

                            <td style="padding: 12px; border: 1px solid #ddd; white-space: nowrap; text-align: center;">
                                <?= date('d-m-Y', strtotime($row->created_at)); ?><br>
                                <span style="font-size: 12px; color: #555;"><?= date('H:i:s', strtotime($row->created_at)); ?></span>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center; white-space: nowrap;">

                                <?php if ($row->is_read == 0): ?>

                                    <a href="<?= base_url('notification/mark_read/' . $row->id); ?>"
                                       class="btn btn-sm btn-primary"
                                       style="background: #007bff; color: #fff; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px;">
                                        <?= $this->lang->line('mark_read'); ?>
                                    </a>

                                <?php else: ?>

                                    <span style="color: #28a745; font-size: 12px;">
                                        <?= $this->lang->line('notification_status_read'); ?>
                                    </span>

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5"
                            style="text-align: center; padding: 20px; color: #777;">
                            <?= $this->lang->line('no_notifications_found'); ?>
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>