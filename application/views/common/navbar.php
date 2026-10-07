<?php
if ($this->session->userdata('LoginID')) {
    $_sis_session_id = $this->session->userdata('LoginToken');
    $this->session->set_userdata('sis_session_id', $_sis_session_id);
} else {
    if ($this->session->userdata('sis_session_id')) {
        $_sis_session_id = $this->session->userdata('sis_session_id');
    } else {
        $_sis_session_id = generateToken('50');
        $this->session->set_userdata('sis_session_id', $_sis_session_id);
    }
}

$first_segment = $this->uri->segment(1);
$search_term = '';

/*
|--------------------------------------------------------------------------
| SHOPPER NOTIFICATION COUNT
|--------------------------------------------------------------------------
*/
$notification_count = 0;

if ($this->session->userdata('LoginID')) {

    $LoginID = $this->session->userdata('LoginID');

    $notification_count = $this->db
        ->where('recipient_type', 'shopper')
        ->where('recipient_id', $LoginID)
        ->where('is_read', 0)
        ->count_all_results('notifications');
}
?>


<!-- =========================================================
     NOTIFICATION CSS
========================================================= -->
<style>
.header-notification {
    position: relative;
    display: inline-block;
    margin-left: 15px;
    vertical-align: middle;
}

.notification-bell {
    position: relative;
    display: inline-block;
    padding: 8px 10px;
    font-size: 20px;
    line-height: 1;
    color: #fff !important;
    text-decoration: none !important;
    cursor: pointer;
}

.notification-bell i {
    color: #fff !important;
}

.notification-bell:hover,
.notification-bell:focus,
.notification-bell:active,
.notification-bell:visited {
    color: #fff !important;
    text-decoration: none !important;
}

.notification-bell:hover i,
.notification-bell:focus i,
.notification-bell:active i {
    color: #fff !important;
}

.notification-count {
    position: absolute;
    top: -4px;
    right: -3px;

    min-width: 18px;
    height: 18px;

    padding: 2px 5px;

    background: #ff0000;
    color: #fff !important;

    border-radius: 50%;

    font-size: 10px;
    font-weight: bold;
    line-height: 14px;

    text-align: center;

    z-index: 9999;
    box-sizing: border-box;
}
</style>


<!-- =========================================================
     BEGIN TOP BAR
========================================================= -->
<div class="pre-header">
    <div class="container-fluid">
        <div class="row">

            <!-- BEGIN TOP BAR LEFT PART -->
            <div class="col-md-6 col-sm-6 additional-shop-info">
                
            </div>
            <!-- END TOP BAR LEFT PART -->


            <!-- BEGIN TOP BAR MENU -->
            <div class="col-md-6 col-sm-6 additional-nav">

                <?php if ($this->session->userdata('LoginID')) { ?>

                    <div class="site-top-buttons">

                        <ul class="list-unstyled list-inline pull-right" id="user-section">

                            <li>
                                <a href="<?php echo base_url(); ?>customer/account">
                                    <span>
                                        <!--<img src="<?php echo TEMP_SKIN_IMG; ?>/my-profile-icon.png">-->
                                    </span>
                                    My Profile
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo base_url(); ?>customer/logout">
                                    <span class="icon icon-sign-out">&nbsp; </span>
                                    Logout
                                </a>
                            </li>

                        </ul>

                    </div>

                <?php } else { ?>

                    <ul class="list-unstyled list-inline pull-right">

                        <li>
                            <a href="<?php echo base_url() . 'customer/login'; ?>">
                                Log In
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url() . 'customer/register'; ?>">
                                Register
                            </a>
                        </li>

                    </ul>

                <?php } ?>

            </div>
            <!-- END TOP BAR MENU -->

        </div>
    </div>
</div>
<!-- END TOP BAR -->


<!-- =========================================================
     BEGIN HEADER
========================================================= -->
<div class="header">

    <div class="container-fluid">


        <!-- =====================================================
             MAIN LOGO
        ====================================================== -->
        <div class="main-logo">

            <a href="<?php echo base_url(); ?>">

                <img src="<?php echo SITE_LOGO; ?>" alt="IndiaMags.com">

                <span>
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    + 91 8850533661
                </span>

            </a>

        </div>


        <!-- =====================================================
             MOBILE TOGGLER
        ====================================================== -->
        <a href="javascript:void(0);" class="mobi-toggler">
            <i class="fa fa-bars"></i>
        </a>


        <!-- =====================================================
             SEARCH + CART
        ====================================================== -->
        <div class="search-mini">

            <div class="sear-com">

                <form
                    action="<?= linkUrl('searchresult') ?>"
                    method="GET"
                    class="site-block-top-search-M"
                    autocomplete="off"
                >

                    <div class="input-group">

                        <input
                            type="text"
                            id="search_M"
                            name="s"
                            placeholder="Search"
                            class="form-control"
                            value="<?php echo urldecode($search_term); ?>"
                        >

                        <span class="input-group-btn">

                            <button
                                class="btn btn-primary submit-search-M"
                                type="submit"
                            >
                                <i class="fa fa-search search-btn"></i>
                            </button>

                        </span>

                    </div>

                    <div id="livesearch_M"></div>

                </form>

            </div>


            <!-- =================================================
                 BEGIN CART
            ================================================== -->
            <div id="mini-cart-main-container">

                <?php
                (new MiniCartList())->render();
                ?>

            </div>
            <!-- END CART -->

        </div>


        <!-- =====================================================
             OPTIONAL PHONE
        ====================================================== -->
        <!--
        <div class="top-phone">
            <span>
                <i class="fa fa-phone" aria-hidden="true"></i>
                + 91 8097002217
            </span>
        </div>
        -->


        <!-- =====================================================
             BEGIN NAVIGATION
        ====================================================== -->
        <div class="header-navigation">

            <?php
            (new TopMenu('top-menu'))->render();
            ?>


            <!-- =================================================
                 NOTIFICATION BELL
            ================================================== -->
            <?php if ($this->session->userdata('LoginID')) { ?>

                <div class="header-notification">

                    <a
                        href="<?php echo base_url('notification'); ?>"
                        class="notification-bell"
                        title="Notifications"
                    >

                        <i class="fa fa-bell" aria-hidden="true"></i>

                        <?php if ($notification_count > 0) { ?>

                            <span class="notification-count">
                                <?php echo $notification_count; ?>
                            </span>

                        <?php } ?>

                    </a>

                </div>

            <?php } ?>
            <!-- END NOTIFICATION BELL -->


        </div>
        <!-- END NAVIGATION -->


    </div>
</div>
<!-- Header END -->