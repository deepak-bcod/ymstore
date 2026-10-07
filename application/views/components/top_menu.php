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
                    ?>
                    <div class="header-navigation-content">
                        <div class="row">
                            <?php foreach (array_chunk($navCatData, 12) as $navCat) : ?>
                                <div class="col-md-3 header-navigation-col">
                                    <ul>
                                        <?php foreach ($navCat as $nav) :
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

    <!-- Notification Bell Icon placed right after Blog (Visible only when logged in) -->
    <?php if ($this->session->userdata('LoginID')): ?>
    <li class="menu-item-new dropdown dropdown-notification" style="position: relative; display: inline-block;">
        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="padding: 10px; font-size: 16px; color: #333;">
            <i class="fa fa-bell" aria-hidden="true"></i>
            <?php if (!empty($notification_count) && $notification_count > 0): ?>
                <span class="badge" style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; position: absolute; top: 0; right: 0;">
                    <?= $notification_count; ?>
                </span>
            <?php endif; ?>
        </a>
        
       <ul class="dropdown-menu dropdown-menu-right" style="width: 300px; padding: 10px; background: #fff; box-shadow: 0px 5px 15px rgba(0,0,0,0.1); left: auto; right: 0;">
        <li style="font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 5px;">Notifications</li>
        
        <?php if (!empty($top_notifications)): ?>
            <?php foreach ($top_notifications as $notif): ?>
                <li style="padding: 8px 0; border-bottom: 1px solid #f1f1f1;">
                    <a href="<?= base_url('notification'); ?>" style="color: #333; text-decoration: none; display: block;">
                        <strong style="font-size: 13px; display: block;"><?= $notif->title; ?></strong>
                        <small style="color: #777; font-size: 11px;"><?= character_limiter($notif->message, 50); ?></small>
                    </a>
                </li>
            <?php endforeach; ?>
            <li style="text-align: center; padding-top: 8px;">
                <a href="<?= base_url('notification'); ?>" style="color: #f60; font-weight: bold; font-size: 12px; display: block;">See all notifications</a>
            </li>
        <?php else: ?>
            <li style="text-align: center; padding: 5px 0;">
                <a href="<?= base_url('notification'); ?>" style="color: #777; font-size: 12px; text-decoration: none; display: block; padding: 5px;">
                    No new notifications <br><span style="color: #f60; font-size: 11px; font-weight: bold;">Click to view all</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?>

</ul>