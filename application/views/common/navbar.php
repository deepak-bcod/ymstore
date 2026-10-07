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
| NOTIFICATION COUNT
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
     FONT AWESOME
========================================================= -->
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<!-- =========================================================
     NOTIFICATION CSS
========================================================= -->
<style>

.header-notification-wrapper {
    position: absolute;
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 99999;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.header-notification-bell {
    position: relative;

    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    padding: 0 !important;
    margin: 0 !important;

    color: #ffffff !important;
    background: transparent !important;

    border: none !important;
    outline: none !important;

    text-decoration: none !important;

    cursor: pointer;

    font-size: 20px !important;
    line-height: 1 !important;

    visibility: visible !important;
    opacity: 1 !important;
}

.header-notification-bell i {
    display: inline-block !important;

    color: #ffffff !important;

    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;

    font-size: 20px !important;
    line-height: 1 !important;

    visibility: visible !important;
    opacity: 1 !important;
}

.header-notification-bell:hover,
.header-notification-bell:focus,
.header-notification-bell:active,
.header-notification-bell:visited {
    color: #ffffff !important;

    background: transparent !important;

    border: none !important;
    outline: none !important;

    text-decoration: none !important;
}

.header-notification-bell:hover i,
.header-notification-bell:focus i,
.header-notification-bell:active i {
    color: #ffffff !important;
}


/* Notification count */
.header-notification-count {
    position: absolute;

    top: 1px;
    right: 1px;

    min-width: 18px;
    height: 18px;

    padding: 2px 5px;

    display: flex !important;
    align-items: center;
    justify-content: center;

    background: #ff0000 !important;
    color: #ffffff !important;

    border-radius: 50%;

    font-size: 10px !important;
    font-weight: bold !important;

    line-height: 14px !important;
    text-align: center;

    z-index: 100000;

    visibility: visible !important;
    opacity: 1 !important;
}


/* Keep header area relative */
.header {
    position: relative;
}


/* Prevent notification from being hidden */
.header-notification-wrapper,
.header-notification-wrapper * {
    box-sizing: border-box;
}


/* Mobile */
@media (max-width: 767px) {

    .header-notification-wrapper {
        right: 55px;
    }

    .header-notification-bell {
        width: 38px;
        height: 38px;
        font-size: 18px !important;
    }

    .header-notification-bell i {
        font-size: 18px !important;
    }

    .header-notification-count {
        top: 0;
        right: 0;
        min-width: 17px;
        height: 17px;
        font-size: 9px !important;
    }
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

                        <ul
                            class="list-unstyled list-inline pull-right"
                            id="user-section"
                        >

                            <li>
                                <a href="<?php echo base_url(); ?>customer/account">

                                    <span>
                                        <!--
                                        <img
                                            src="<?php echo TEMP_SKIN_IMG; ?>/my-profile-icon.png"
                                        >
                                        -->
                                    </span>

                                    My Profile

                                </a>
                            </li>


                            <li>

                                <a href="<?php echo base_url(); ?>customer/logout">

                                    <span class="icon icon-sign-out">
                                        &nbsp;
                                    </span>

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

                <img
                    src="<?php echo SITE_LOGO; ?>"
                    alt="IndiaMags.com"
                >

                <span>
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    + 91 8850533661
                </span>

            </a>

        </div>



        <!-- =====================================================
             MOBILE TOGGLER
        ====================================================== -->
        <a
            href="javascript:void(0);"
            class="mobi-toggler"
        >
            <i class="fa fa-bars"></i>
        </a>



        <!-- =====================================================
             SEARCH
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

                <i
                    class="fa fa-phone"
                    aria-hidden="true"
                ></i>

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

        </div>

        <!-- END NAVIGATION -->



        <!-- =====================================================
             NOTIFICATION BELL
             OUTSIDE TOPMENU
        ====================================================== -->

        <?php if ($this->session->userdata('LoginID')) { ?>

            <div class="header-notification-wrapper">

                <a
                    href="<?php echo base_url('notification'); ?>"
                    class="header-notification-bell"
                    title="Notifications"
                    aria-label="Notifications"
                >

                    <i class="fa-solid fa-bell"></i>


                    <?php if ($notification_count > 0) { ?>

                        <span class="header-notification-count">

                            <?php
                            echo $notification_count;
                            ?>

                        </span>

                    <?php } ?>

                </a>

            </div>

        <?php } ?>

        <!-- END NOTIFICATION BELL -->


    </div>

</div>
<!-- Header END -->