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
    <!-- <link href="<?php echo SKIN_CSS; ?>bootstrap.min.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet"> -->
  <!-- <link rel="stylesheet" type="text/css" media="all" href="<?php echo SKIN_CSS ?>new/font-awesome.min.css"> -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<!-- <script src="<?php echo SKIN_URL ?>bootstrap.min.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->
  <!-- Custom style CSS -->



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

    $_sis_session_id = $this->session->userdata('LoginToken');
    if (empty($_sis_session_id)) {
      $_sis_session_id = $this->session->userdata('sis_session_id');
      if (empty($_sis_session_id)) {
        $_sis_session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
      }
      $this->session->set_userdata('LoginToken', $_sis_session_id);
    }
    $this->session->set_userdata('sis_session_id', $_sis_session_id);

  } else {

    if ($this->session->userdata('sis_session_id')) {

      $_sis_session_id = $this->session->userdata('sis_session_id');

    } else {

      $_sis_session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));

      $this->session->set_userdata('sis_session_id', $_sis_session_id);

    }

  }
  



  $first_segment = $this->uri->segment(1);

  $search_term = '';



  ?>

  <div class="header-container header-style-1">

    <div class="header-top">

      <div class="container">
		<?php
                $CI =& get_instance();

                $CI->load->database();

                $current_lang = $CI->session->userdata('site_lang') ?? 'english';

                $sticker = $CI->db
                    ->get_where('sticker_text', ['id' => 1])
                    ->row();

                $sticker_text = '';

                if ($sticker) {
                    $sticker_text = ($current_lang === 'french' && !empty($sticker->text_fr))
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
            $current_lang = $this->session->userdata('site_lang') ?? 'english';
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


                <?php if ($this->session->userdata('LoginID')) {
                  ?>
           

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

      <div class="row">

        <div class="col-lg-2">

         <div class="deliver">
  <a href="/page/delivery-information">
    <span class="de-top"><?= $this->lang->line('deliver_in'); ?></span>
    <span class="de-bottom"><?= $this->lang->line('deliver_country'); ?></span>
  </a>
</div>

                </div>


        <div class="col-xl-10 col-lg-12  serach-container-header">

          <div class="nav-destkop middle-searchbox">

            <div class="vertical-megamenu search-wrapper form minisearch">

              <nav class="sm_megamenu_wrapper_vertical_menu sambar" id="sm_megamenu_menu688b583b37556"
                data-sam="8291055031753962555">

               <!-- Categories -->
              <div class="block-title"><?= $this->lang->line('categories'); ?></div>

                <div class="header-navigation">
                  
                <?php (new TopMenu('top-menu'))->render(); ?>



                </div>

<div class="mobile-hamburger" id="mobileHamburger">
  <span></span>
  <span></span>
  <span></span>
</div>



              </nav>

<script>
document.addEventListener('DOMContentLoaded', function() {

  const hamburger = document.getElementById('mobileHamburger');
  const menu = document.querySelector('.bottom-section-menu');

  // Stop if elements not found
  if (!hamburger || !menu) return;

  // Toggle menu
  hamburger.addEventListener('click', function(e) {
    e.stopPropagation();
    menu.classList.toggle('show');
  });

  // Prevent menu clicks from closing
  menu.addEventListener('click', function(e) {
    e.stopPropagation();
  });

  // Close on outside click
  document.addEventListener('click', function() {
    menu.classList.remove('show');
  });

});
</script>





<script>
document.addEventListener('DOMContentLoaded', function () {

  if (window.innerWidth > 1024) return; // desktop untouched

  document.querySelectorAll('.bottom-section-menu .dropdown-toggle')
    .forEach(function (category) {

      category.addEventListener('click', function (e) {
        e.preventDefault();   // stop link
        e.stopPropagation();  // stop bootstrap

        this.closest('.dropdown').classList.toggle('open');
      });

    });

});
</script>








              <script type="text/javascript">

                require(["jquery", "mage/template"], function ($) {

                  var menu_width = $('.sm_megamenu_wrapper_horizontal_menu').width();

                  $('.sm_megamenu_wrapper_horizontal_menu .sm_megamenu_menu > li > div').each(function () {

                    $this = $(this);

                    var lv2w = $this.width();

                    var lv2ps = $this.position();

                    var lv2psl = $this.position().left;

                    var sw = lv2w + lv2psl;

                    if (sw > menu_width) {

                      $this.css({ 'right': '0' });

                    }

                  });

                  var _item_active = $('div.sm_megamenu_actived');

                  if (_item_active.length) {

                    _item_active.each(function () {

                      var _self = $(this), _parent_active = _self.parents('.sm_megamenu_title'), _level1 = _self.parents('.sm_megamenu_lv1');

                      if (_parent_active.length) {

                        _parent_active.each(function () {

                          if (!$(this).hasClass('sm_megamenu_actived'))

                            $(this).addClass('sm_megamenu_actived');

                        });

                      }



                      if (_level1.length && !_level1.hasClass('sm_megamenu_actived')) {

                        _level1.addClass('sm_megamenu_actived');

                      }

                    });

                  }

                });

              </script>



              <script type="text/javascript">

                require([

                  'jquery',

                  'domReady!'

                ], function ($) {

                  var limit;

                  limit = 13;

                  var i;

                  i = 0;

                  var items;

                  items = $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').length;



                  if (items > limit) {

                    $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').each(function () {

                      i++;

                      if (i > limit) {

                        $(this).css('display', 'none');

                      }

                    });



                    $('.sm_megamenu_wrapper_vertical_menu .sambar-inner .more-w > .more-view').click(function () {

                      if ($(this).hasClass('open')) {

                        i = 0;

                        $('.sm_megamenu_wrapper_vertical_menu .sm_megamenu_menu > li').each(function () {

                          i++;

                          if (i > limit) {

                            $(this).slideUp(200);

                          }

                        });

                        $(this).removeClass('open');

                        $('.more-w').removeClass('active-i');

                        $(this).html('More Categories');

                      } else {

                        i = 0;

                        $('.sm_megamenu_wrapper_vertical_menu ul.sm_megamenu_menu > li').each(function () {

                          i++;

                          if (i > limit) {

                            $(this).slideDown(200);

                          }

                        });

                        $(this).addClass('open');

                        $('.more-w').addClass('active-i');

                        $(this).html('Close Menu');

                      }

                    });



                  } else {

                    $(".more-w").css('display', 'none');

                  }



                });

              </script>

            </div>



            <div class="horizontal-megamenu">

              <nav class="sm_megamenu_wrapper_horizontal_menu sambar" id="sm_megamenu_menu688b58507dbc2"
                data-sam="18755110001753962576">

                <div class="sambar-inner">

                  <div class="mega-content">

                    <ul class="horizontal-type sm-megamenu-hover sm_megamenu_menu sm_megamenu_menu_black"
                      data-jsapi="on">




                    </ul>

                  </div>

                </div>

              </nav>



              <script type="text/javascript">

                require(["jquery", "mage/template"], function ($) {

                  var menu_width = $('.sm_megamenu_wrapper_horizontal_menu').width();

                  $('.sm_megamenu_wrapper_horizontal_menu .sm_megamenu_menu > li > div').each(function () {

                    $this = $(this);

                    var lv2w = $this.width();

                    var lv2ps = $this.position();

                    var lv2psl = $this.position().left;

                    var sw = lv2w + lv2psl;

                    if (sw > menu_width) {

                      $this.css({ 'right': '0' });

                    }

                  });

                  var _item_active = $('div.sm_megamenu_actived');

                  if (_item_active.length) {

                    _item_active.each(function () {

                      var _self = $(this), _parent_active = _self.parents('.sm_megamenu_title'), _level1 = _self.parents('.sm_megamenu_lv1');

                      if (_parent_active.length) {

                        _parent_active.each(function () {

                          if (!$(this).hasClass('sm_megamenu_actived'))

                            $(this).addClass('sm_megamenu_actived');

                        });

                      }



                      if (_level1.length && !_level1.hasClass('sm_megamenu_actived')) {

                        _level1.addClass('sm_megamenu_actived');

                      }

                    });

                  }





                  $(".home-item-parent > a").attr("href", "http://localhost:8081/mu/");





                });

              </script>

            </div>

          </div>





        </div>

      </div>

    </div>

  </div>

  </div>