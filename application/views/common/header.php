<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title>Yellow Market</title>
  <link id="appFavicon" rel="icon" type="image/x-icon"
    href="<?php echo TEMP_SKIN_IMG . '/favicon/yellow-markets-icon.png'; ?>">

  <link
    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">

  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" type="text/css" href="<?php echo SKIN_CSS ?>new/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <?php
  $this->template->load('common_for_all/header_meta_details');
  ?>

  <?php $this->template->load('common_for_all/favicon'); ?>
  <?php $this->template->load('common_for_all/header_link_details'); ?>
  <link rel="stylesheet" type="text/css" href="<?php echo SKIN_CSS ?>new/ymstyle.css?v50">
  <style>
    .search-box {
      position: relative;
    }
    .search-autocomplete, #livesearch, #livesearch_M {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 99999;
      background: #ffffff;
      border: 1px solid #ddd;
      border-radius: 0 0 6px 6px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      max-height: 320px;
      overflow-y: auto;
      display: none;
      text-align: left;
    }
    .search-autocomplete ul, #livesearch ul, #livesearch_M ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .search-autocomplete ul li, #livesearch ul li, #livesearch_M ul li {
      border-bottom: 1px solid #eee;
    }
    .search-autocomplete ul li:last-child, #livesearch ul li:last-child, #livesearch_M ul li:last-child {
      border-bottom: none;
    }
    .search-autocomplete ul li a, #livesearch ul li a, #livesearch_M ul li a {
      display: block;
      padding: 10px 15px;
      color: #333;
      text-decoration: none;
      font-size: 14px;
    }
    .search-autocomplete ul li a:hover, #livesearch ul li a:hover, #livesearch_M ul li a:hover {
      background: #f5f5f5;
      color: #000;
    }
  </style>
</head>

<script>
if (location.pathname === '/daily-deals') {
    document.documentElement.classList.add('daily-deals-page');
}
</script>
<body>

  <?php
  if ($this->session->userdata('LoginID')) {
    $_sis_session_id =$this->session->userdata('LoginToken');
    if (empty($_sis_session_id)) {
      $_sis_session_id =$this->session->userdata('sis_session_id');
      if (empty($_sis_session_id)) {$_sis_session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
      }
      $this->session->set_userdata('LoginToken',$_sis_session_id);
    }
    $this->session->set_userdata('sis_session_id',$_sis_session_id);
  } else {
    if ($this->session->userdata('sis_session_id')) {
      $_sis_session_id =$this->session->userdata('sis_session_id');
    } else {
      $_sis_session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
      $this->session->set_userdata('sis_session_id',$_sis_session_id);
    }
  }

  $first_segment = $this->uri->segment(1);$search_term = '';
  ?>

  <div class="header-container header-style-1">
    <div class="header-top">
      <div class="container">
    <?php
                $CI =& get_instance();$CI->load->database();
                $current_lang =$CI->session->userdata('site_lang') ?? 'english';
                $sticker =$CI->db
                    ->get_where('sticker_text', ['id' => 1])
                    ->row();
                $sticker_text = '';
                if ($sticker) {$sticker_text = ($current_lang === 'french' && !empty($sticker->text_fr))
                        ? $sticker->text_fr
                        : $sticker->text;
                }
                ?>
    <div class="row row-topmarquee">
    <div class="col-12">
      <marquee>
                <p>
                  <span><em><strong><?= $sticker_text; ?></strong></em></span>
                </p>
              </marquee>
    </div>
    </div>

        <div class="row row-topheader">
          <div class="col-9 col-lg-7 left-top-header">
            <div>
              <p>
                <span><em><strong><?= $this->lang->line('header_tagline'); ?></strong></em></span>
              </p>
            </div>
          </div>

          <div class="col-3 col-lg-5 right-top-header">
            <?php
            $current_lang =$this->session->userdata('site_lang') ?? 'english';
            $current_lang_display = ucfirst($current_lang);
            $current_flag = ($current_lang == 'french')
              ? base_url('public/images/flag_french.png')
              : base_url('public/images/flag_default.png');
            ?>

            <div class="language-wrapper">
              <div class="switcher language switcher-language" data-ui-id="language-switcher"
                id="switcher-language-nav">
                <strong class="label switcher-label"><span>Language</span></strong>
                <div class="actions dropdown options switcher-options">
                  <div class="action toggle switcher-trigger" id="switcher-language-trigger-nav">
                    <strong style="background-image:url('<?= $current_flag ?>');" class="view-default">
                      <span><?= $current_lang_display ?></span>
                    </strong>
                  </div>
                  <div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front mage-dropdown-dialog"
                    tabindex="-1" role="dialog" aria-describedby="ui-id-1" style="display: none;">
                    <ul class="dropdown switcher-dropdown ui-dialog-content ui-widget-content" id="ui-id-1"
                      style="display: block;">

                      <li class="view-english switcher-option">
                        <a style="background-image:url('<?= base_url('pub/static/frontend/YellowMarket/Marketsplace/en_GB/images/flags/flag_default.png'); ?>');"
                          href="javascript:void(0);" class="changeLanguage" data-id="1">
                          English
                        </a>
                      </li>

                      <li class="view-french switcher-option">
                        <a style="background-image:url('<?= base_url('pub/static/frontend/YellowMarket/Marketsplace/en_GB/images/flags/flag_french.png'); ?>');"
                          href="javascript:void(0);" class="changeLanguage" data-id="2">
                          French
                        </a>
                      </li>

                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="osm-images-link">
              <div class="fa-osm-linkedin">
                <a href="https://www.linkedin.com/company/yellowmarkets" aria-label="LinkedIn" target="_blank">
                  <span class="fa fa-linkedin"></span>
                </a>
              </div>
              <div class="fa-osm-facebook">
                <a href="https://www.facebook.com/yellowmarkets" aria-label="facebook" target="_blank">
                  <span class="fa fa-facebook ms"></span>
                </a>
              </div>
              <div class="fa-osm-instagram">
                <a href="https://www.instagram.com/mu.yellowmarkets/" aria-label="Instagram" target="_blank">
                  <span class="fa fa-instagram"></span>
                </a>
              </div>
              <div class="fa-osm-youtube">
                <a href="https://www.youtube.com/@MU.YellowMarkets" aria-label="YouTube" target="_blank">
                  <span class="fa fa-youtube-play"></span>
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="header-middle">
      <div class="container">
        <div class="row">
          <div class="col-lg-2 col-md-3 left-header-middle">
            <div class="logo-wrapper">
              <h1 class="logo-content">
                <strong class="logo">
                  <a class="logo" href="<?php echo BASE_URL; ?>" title="Yellow Markets Commerce">
                    <img src="<?php echo SITE_LOGO; ?>" alt="Yellow Markets Commerce" width="248" height="auto">
                  </a>
                </strong>
              </h1>
            </div>
          </div>
          
          <div class="col-lg-4 col-md-4 serach-container-header">
            <div class="middle-searchbox">
              <div class="search-box">
                <form action="<?php echo base_url(); ?>searchresult" method="get" id="search_form" class="site-block-top-search" autocomplete="off">
                  <div class="input-group">
                    <input type="text" placeholder="<?php echo $this->lang->line('search_for_products_and_more'); ?>" class="form-control" name="s" id="search" autocomplete="off" value="<?php echo isset($search_term) ? htmlspecialchars(urldecode($search_term)) : ''; ?>">
                    <span class="input-group-btn">
                      <button class="btn btn-primary submit-search" type="submit"><i class="fa fa-search search-btn"></i></button>
                    </span>
                  </div>
                  <div id="searchbox_autocomplete" class="search-autocomplete"></div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6 col-md-9 right-header-middle">
            <div class="right-middle-header">
              <div class="customer-action admin-sign">

                <?php if ($this->session->userdata('LoginID')) { ?>
                  <ul class="header-link-profile">
                    <li class="myprofile"><a href="<?php echo base_url() ?>customer/account">
                      <div class="icon-white"><i class="fa fa-user"></i></div>
                      <?= $this->lang->line('my_profile'); ?>
                    </a></li>
                    <li class="signout"><a class="login-link" href="<?php echo base_url() . 'customer/logout' ?>">
                      <div class="icon-white"><i class="fa fa-sign-out"></i></div>
                      <?= $this->lang->line('sign_out'); ?>
                    </a></li>
                  </ul>
                <?php } else { ?>
                  <div class="icon-white"><i class="fa fa-user"></i></div>
                  <div class="link-customer-action">
                    <a class="login-link" href="<?php echo base_url() . 'customer/login' ?>">
                      <?= $this->lang->line('shopper_signin'); ?>
                    </a>
                    <a class="register-link" href="<?php echo base_url() . 'customer/register' ?>">
                      <?= $this->lang->line('register_as_shopper'); ?>
                    </a>
                  </div>
                <?php } ?>

              </div>

              <!-- Notification Bell Icon Section -->
              <div class="customer-action notification-sign" style="display: flex; align-items: center; margin: 0 15px;">
                <a href="javascript:void(0);" class="notification-link" style="color: #fff; text-decoration: none; display: flex; align-items: center;">
                  <div class="icon-white" style="font-size: 18px;"><i class="fa fa-bell"></i></div>
                </a>
              </div>

              <!-- Merchant -->
              <div class="customer-action merchant-sign">
                <div class="icon-white"><i class="fa fa-street-view"></i></div>
                <div class="link-customer-action">
                  <a class="login-link" href="<?php echo base_url() . 'merchants/login' ?>">
                    <?= $this->lang->line('merchant_signin'); ?>
                  </a>
                  <a class="register-link" href="<?php echo base_url() . 'merchants/register' ?>">
                    <?= $this->lang->line('register_as_merchant'); ?>
                  </a>
                </div>
              </div>

              <!-- BEGIN CART -->
              <div id="mini-cart-main-container">
                <?php (new MiniCartList())->render(); ?>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="header-bottom ontop-element">
    <div class="container">