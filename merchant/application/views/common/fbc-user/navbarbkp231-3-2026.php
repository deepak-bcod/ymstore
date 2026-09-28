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
      <div id="notification-popup" class="card position-absolute" style="right:0; top:35px; width:300px; display:none; z-index:9999;">
        <div class="card-header">
          Notifications
        </div>
        <ul class="list-group list-group-flush">
          <?php if(!empty($unread_notifications)): ?>
            <?php foreach($unread_notifications as $n): ?>
              <li class="list-group-item small">
                <strong><?= html_escape($n['title']) ?></strong><br>
                <span><?= nl2br(html_escape($n['message'])) ?></span>
              </li>
            <?php endforeach; ?>
            <?php if($unread_count > 3): ?>
              <li class="list-group-item text-center">
                <a href="<?= base_url('notifications') ?>">See all notifications</a>
              </li>
            <?php endif; ?>
          <?php else: ?>
            <li class="list-group-item text-center small">No unread notifications</li>
          <?php endif; ?>
           <li class="list-group-item text-center">
                <a href="<?= base_url('notifications') ?>">See all notifications</a>
              </li>
        </ul>
      </div>
    </div>

    <div class="left-line bell-right">
      <a href="<?= base_url('logout') ?>" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    <?php 
    $CI =& get_instance();
    $current_lang = $CI->session->userdata('site_lang') ?? 'english';
    ?>
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
                        // Optionally, update popup content to show read status
                        // For now, just mark as read
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        xhr.send();
    }

    // Toggle popup
    bell.addEventListener('click', function(e) {
        e.preventDefault();
        const isVisible = popup.style.display === 'block';
        popup.style.display = isVisible ? 'none' : 'block';
        
        // If showing the popup, mark all notifications as read
       /* if (!isVisible) {
            markAllAsRead();
        }*/
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
