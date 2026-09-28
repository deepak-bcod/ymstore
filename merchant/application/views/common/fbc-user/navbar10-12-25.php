<?php

 $ci = get_instance();

 $id=$this->session->userdata('LoginID');

 $FBCData=$this->CommonModel->getSingleDataByID('publisher',array('id'=>$id),'email,id');

 ?>

 <nav class="navbar sticky-top flex-md-nowrap p-0">



  <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?php echo base_url();?>dashboard" style="font-size: 27px;font-weight: 600;letter-spacing: 1px; color:#ffd703;">

    <img src="<?php echo SKIN_IMG ?>yellow-markets-logo-yellow-white.png" alt="YellowMarket" width="170px" height="34px">

  </a>

  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">

    <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>

  </button>

  

  <div class="d-flex top-right right-nav-top">

    

    <div class="left-line pro-info">

    <a  a href="<?php echo base_url();?>PublisherController/editMerchant/<?php 
      echo rtrim(strtr(base64_encode($_SESSION["LoginID"]), '+/', '-_'), '='); 
      ?>">
          <div class="d-flex align-items-center">
              <span class="pro-name"><?php echo $FBCData->email;?></span>
              <i class="fa fa-user"></i>
          </div>
      </a>

    </div>



 	<div class="left-line bell-right">

        <a href="<?php echo base_url(); ?>logout" title="Logout"><i class="fas fa-sign-out-alt"></i></a>

    </div>

    <?php 
$CI =& get_instance();
$current_lang = $CI->session->userdata('site_lang') ?? 'english';
?>

<form method="post" action="<?php echo base_url('language/switch'); ?>" style="display:inline;" class="lang-form">
    <select name="site_lang" onchange="this.form.submit()" class="form-select" style="display:inline;">
        <option value="english" <?php echo ($current_lang == 'english') ? 'selected' : ''; ?>>English</option>
        <option value="french" <?php echo ($current_lang == 'french') ? 'selected' : ''; ?>>French</option>
    </select>
</form>

  </div>

</nav>

<div class="ajax-spinner" id="ajax-spinner"><div class="ajax-spinner-inner"></div></div>

