
<?php 
// echo "<pre>"; print_r($navCatData); die;

$allNavgte = array_column($navCatData, 'slug'); 
$currNav   = $this->uri->segment(2);
$currNav1  = $this->uri->segment(3);
$currNav2  = $this->uri->segment(4);
$lang      = $this->session->userdata('site_lang');
?>

<ul class="bottom-section-menu">

    <?php if (!empty($navCatData) && $menuType === 'category_menu') : ?>

        <li class="dropdown dropdown-megamenu <?= in_array($currNav, $allNavgte) ? 'active' : ''; ?>">
            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">
                <?= $this->lang->line('categories'); ?>
            </a>

            <ul class="dropdown-menu">
                <li>
                    <?php $lang = $this->session->userdata('site_lang'); ?>

                    <div class="header-navigation-content">
                        <div class="row">

                            <?php foreach (array_chunk($navCatData, 12) as $navCat) : ?>

                                <div class="col-md-3 header-navigation-col">
                                    <ul>

                                        <?php foreach ($navCat as $nav) :

                                            $nvName = (
                                                $lang === 'french' && 
                                                !empty($nav->lang_title)
                                            ) 
                                            ? $nav->lang_title 
                                            : $nav->menu_name;

                                            $nvName = ucwords(strtolower($nvName));

                                        ?>

                                            <li class="<?= ($currNav === $nav->slug) ? 'active' : ''; ?>">
                                                <a href="<?= linkUrl('category/' . $nav->slug); ?>">
                                                    <?= $nvName; ?>
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


    <!-- New Arrivals -->
    <li class="menu-item-new <?= ($currNav === 'newarrival-products') ? 'active' : ''; ?>">
        <a href="<?= linkUrl(base_url('newarrival-products')); ?>">
            <?= $this->lang->line('new_arrivals'); ?>
        </a>
    </li>


    <!-- Trending Products -->
    <li class="menu-item-new <?= ($currNav === 'trending-products') ? 'active' : ''; ?>">
        <a href="<?= linkUrl(base_url('trending-products')); ?>">
            <?= $this->lang->line('trending_products'); ?>
        </a>
    </li>


    <!-- Daily Deals -->
    <li class="menu-item-new <?= ($currNav === 'daily-deals') ? 'active' : ''; ?>">
        <a href="<?= base_url('daily-deals'); ?>">
            <?= $this->lang->line('daily_deals'); ?>
        </a>
    </li>


    <!-- Flash Sale -->
    <li class="menu-item-new <?= ($currNav === 'flash-sale') ? 'active' : ''; ?>">
        <a href="<?= base_url('flash-sale'); ?>">
            <?= $this->lang->line('flash_sales'); ?>
        </a>
    </li>


    <!-- Blog -->
    <li class="menu-item-new <?= ($currNav === 'blog') ? 'active' : ''; ?>">
        <a href="<?= base_url('blogs'); ?>">
            <?= $this->lang->line('blog'); ?>
        </a>
    </li>


    <!-- =========================================
         NOTIFICATION BELL
         Click Bell -> Notification Page
         Count shown on Bell
         ========================================= -->

    <?php if ($this->session->userdata('LoginID')): ?>

        <li class="menu-item-new notification-menu">

            <a href="<?= base_url('notification'); ?>"
               class="notification-bell"
               title="Notifications">

                <i class="fa fa-bell" aria-hidden="true"></i>

                <?php if (!empty($notification_count) && $notification_count > 0): ?>

                    <span class="notification-count">
                        <?= $notification_count; ?>
                    </span>

                <?php endif; ?>

            </a>

        </li>

    <?php endif; ?>

</ul>


<style>

.notification-menu {
    position: relative;
    display: inline-block;
    margin-left: 10px;
}

.notification-bell {
    position: relative;
    display: inline-block;
    padding: 10px;
    font-size: 18px;
    color: #333 !important;
    text-decoration: none !important;
    line-height: 1;
}

.notification-bell:hover {
    color: #f60 !important;
}

.notification-count {
    position: absolute;
    top: 2px;
    right: 0;

    min-width: 18px;
    height: 18px;

    padding: 2px 5px;

    background: red;
    color: #fff;

    border-radius: 50%;

    font-size: 10px;
    font-weight: bold;

    line-height: 14px;
    text-align: center;

    z-index: 10;
}

</style>

