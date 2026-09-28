
 <?php
 $ci = get_instance();
 $id	=	$this->session->userdata('LoginID');
 $FBCData=$this->CommonModel->getSingleDataByID('adminusers',array('id'=>$id),'email,id');
 $ci->load->model('Notification_model');
 $unread_notifications = $ci->Notification_model->get_for('admin', $id, 3, 0, true);
//  echo "<pre>";print_r($unread_notifications);die;
$unread_count = $ci->Notification_model->unread_count('admin', $id);
 ?>
 <nav class="navbar sticky-top flex-md-nowrap p-0">

  <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?php echo base_url();?>dashboard" style="font-size: 27px;
    font-weight: 600;
    letter-spacing: 1px; color:#b20b1d;"><img src="<?php echo base_url();?>public/images/yellow-markets-logo-white.png" width="180px"></a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>
  </button>
  
  <div class="d-flex top-right right-nav-top">
    
    <div class="left-line pro-info">
    <a href="<?php echo base_url();?>adminuser/edit_user/<?php echo $_SESSION["LoginID"];?>"> <div class="d-flex align-items-center"><span class="pro-name"><?php echo $FBCData->email;?></span><i class="fa fa-user"></i> </div></a>
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
              <!-- <li class="list-group-item text-center">
                <a href="<?= base_url('notifications') ?>">See all notifications</a>
              </li> -->
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
        <a href="<?php echo base_url(); ?>logout" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</nav>
<div class="ajax-spinner" id="ajax-spinner"><div class="ajax-spinner-inner"></div></div>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notification-bell');
    const popup = document.getElementById('notification-popup');

    // Toggle popup
    bell.addEventListener('click', function(e) {
        e.preventDefault();
        popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
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