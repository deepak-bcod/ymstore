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

    // ==========================================
    // ORDER DELIVERED
    // ==========================================

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

        // ==========================================
        // NORMAL ES ORDER
        // ==========================================

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
