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
}

/* .notification-bell:hover {
    color: #ffd400 !important;
} */

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
        <a href="<?= base_url('notifications'); ?>"
           class="notification-bell">
            <i class="fa fa-bell"></i>
        </a>
    </li>







</ul>
