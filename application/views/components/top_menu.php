<style>
.notification-menu {
    position: relative;
    margin-left: 15px;
}

.notification-bell {
    position: relative;
    display: inline-block;
    color: #fff !important;
    font-size: 20px;
    padding: 10px;
    cursor: pointer;
}

.notification-count {
    position: absolute;
    top: 2px;
    right: 0;

    background: #e53935;
    color: #fff;

    min-width: 18px;
    height: 18px;

    border-radius: 50%;

    font-size: 10px;
    line-height: 18px;
    text-align: center;
    font-weight: bold;
}

/* Notification dropdown */

.notification-dropdown {
    display: none;
    position: absolute;
    top: 48px;
    right: -20px;
    width: 350px;
    background: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    z-index: 99999;
    color: #333;
}

.notification-dropdown.show {
    display: block !important;
}


.notification-dropdown.show {
    display: block !important;
}


.notification-dropdown-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    font-size: 16px;
}

.notification-dropdown-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-dropdown-item:hover {
    background: #f7f7f7;
}

.notification-dropdown-item.unread {
    background: #fff8d8;
}

.notification-dropdown-title {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 5px;
}

.notification-dropdown-message {
    font-size: 13px;
    color: #666;
}

.notification-dropdown-date {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
}

.notification-dropdown-footer {
    padding: 12px;
    text-align: center;
    border-top: 1px solid #eee;
}

.notification-dropdown-footer a {
    color: #0066cc;
    text-decoration: none;
}

.notification-loading,
.no-notifications {
    padding: 20px;
    text-align: center;
    color: #777;
}

</style>
<?php 
// echo "<pre>"; print_r($navCatData); die;
$allNavgte = array_column($navCatData, 'slug'); 
$currNav = $this->uri->segment(2);
$currNav1 = $this->uri->segment(3);
$currNav2 = $this->uri->segment(4);
$lang = $this->session->userdata('site_lang');
?>

<ul class="bottom-section-menu">

    <?php if (!empty($navCatData) && $menuType === 'category_menu') : ?>
        <li class="dropdown dropdown-megamenu <?= in_array($currNav, $allNavgte) ? 'active' : ''; ?>">
            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">
                <?= $this->lang->line('categories'); ?>
            </a>

            <ul class="dropdown-menu">
                <li>
                    <?php 
                        $lang = $this->session->userdata('site_lang');
                        //echo "<pre>"; print_r($navCatData);echo $lang;die;
                    ?>
                    <div class="header-navigation-content">
                        <div class="row">
                            <?php foreach (array_chunk($navCatData, 12) as $navCat) : ?>
                                <div class="col-md-3 header-navigation-col">
                                    <ul>
                                        <?php foreach ($navCat as $nav) :
                                            // ✅ Choose name based on language
                                            //echo "<pre>"; print_r($lang); die;
                                            $nvName = ($lang === 'french' && !empty($nav->lang_title)) ? $nav->lang_title : $nav->menu_name;

                                            $nvName = ucwords(strtolower($nvName));
                                            
                                        ?>
                                            <li class="<?= ($currNav === $nav->slug) ? 'active' : ''; ?>">
                                                <a href="<?= linkUrl('category/' . $nav->slug) ?>">
                                                    <?= $nvName ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </li>
            </ul>
        </li>
    <?php endif; ?>

    <!-- Static items -->
<li class="menu-item-new <?= ($currNav === 'newarrival-products') ? 'active' : ''; ?>">
  
    <a href="<?= linkUrl(base_url('newarrival-products')); ?>">
        <?= $this->lang->line('new_arrivals'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'trending-products') ? 'active' : ''; ?>">
    <a href="<?= linkUrl(base_url('trending-products')); ?>">
        <?= $this->lang->line('trending_products'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'daily-deals') ? 'active' : ''; ?>">
    <a href="<?= base_url('daily-deals'); ?>">
        <?= $this->lang->line('daily_deals'); ?>
    </a>
</li>

<li class="menu-item-new <?= ($currNav === 'flash-sale') ? 'active' : ''; ?>">
    <a href="<?= base_url('flash-sale'); ?>">
        <?= $this->lang->line('flash_sales'); ?>
    </a>
</li>

<li class="menu-item-new <?= ($currNav === 'blog') ? 'active' : ''; ?>">
    <a href="<?= base_url('blogs'); ?>">
        <?= $this->lang->line('blog'); ?>
    </a>
</li>
<!-- Notification Bell -->
<li class="notification-menu">

    <a href="javascript:void(0);"
       class="notification-bell"
       id="notificationBell">

        <i class="fa fa-bell"></i>

        <span class="notification-count"
              id="notificationCount"
              style="display:none;">
            0
        </span>

    </a>

    <div class="notification-dropdown" id="notificationDropdown">

        <div class="notification-dropdown-header">
            <strong>Notifications</strong>
        </div>

        <div id="notificationList">

            <div class="notification-loading">
                Loading...
            </div>

        </div>

        <div class="notification-dropdown-footer">

            <a href="<?= site_url('notifications'); ?>">
                View All Notifications
            </a>

        </div>

    </div>

</li>








</ul>
