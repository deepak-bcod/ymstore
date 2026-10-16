<style>
.link-disabled {
    pointer-events: none !important;
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}
</style>
<?php


$CI =& get_instance();   // get CI superobject
$CI->load->model('UserModel');
$UserDetails = $CI->UserModel->getUserByMerchantId($this->session->userdata('LoginID'));


$profile_incomplete = false;

$requiredFields = [
    'publication_name',
    'merchant_cat',
    'bank_name',
    'bank_branch_number',
    'beneficiary_acc_no',
    'beneficiary_name',
    'beneficiary_ifsc_code',
    'vendor_name',
    'shop_image',
    'commision_percent',
    'phone_no',
    'company_name',
    'company_address',
    'location',
    'state',
    'city',
    'zipcode'
];

foreach ($requiredFields as $field) {
    if (empty($UserDetails->$field)) {
        $profile_incomplete = true;
        break;
    }
}

if ($UserDetails && $UserDetails->vat_status == 'registered' && empty($UserDetails->vat_no)) {
    $profile_incomplete = true;
}

$CI->load->model('Subscription_model');
$subscription_expired = false;
$login_id = $this->session->userdata('LoginID');
if ($login_id) {
    $subscription_expired = $CI->Subscription_model->is_subscription_expired($login_id);
}

$menu_disabled = ($profile_incomplete || $subscription_expired);
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse"> 
   <div class="sidebar-sticky pt-3">

      <div class="side-arrowicon"><img src="<?php echo SKIN_IMG; ?>sidearrow-icon.png"></div>

      <ul class="nav flex-column">

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='dashboard'))?'has-submenu show':''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? base_url('DashboardController/index') : 'javascript:void(0);' ?>">
               <?= $this->lang->line('sidebar_dashboard') ?>
            </a>
         </li>

         <!-- Subscription Menu (Always active so merchant can subscribe/renew) -->
         <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'subscription')) ? 'active' : ''; ?>">

   <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'subscription')) ? 'has-submenu' : 'collapsed'; ?>"
      href="#SubMenuSubscription"
      data-toggle="collapse"
      data-parent="#SubMenuSubscription">

      <?= $this->lang->line('sidebar_subscriptions') ?>
      <i class="fas fa-angle-down"></i>
   </a>

   <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'subscription')) ? 'collapse show' : 'collapse'; ?>" id="SubMenuSubscription">

      <!-- Subscription Plans -->
      <a class="list-submenu"
         href="<?php echo base_url('subscription'); ?>">
         <?= $this->lang->line('sidebar_subscription_plans') ?>
      </a>

      <!-- Subscription List -->
      <a class="list-submenu"
         href="<?php echo base_url('manage_subscription'); ?>">
         <?= $this->lang->line('sidebar_subscription_list') ?>
      </a>

   </div>
</li>

         <li class="nav-item <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'active':''; ?>">
            <a class="nav-link <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'has-submenu collapsed':''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? '#SubMenu1' : 'javascript:void(0);'; ?>" 
               <?php if(!$menu_disabled): ?>data-toggle="collapse" data-parent="#SubMenu1"<?php endif; ?>>
               <?= $this->lang->line('sidebar_cataloging') ?>
               <i class="fas fa-angle-down"></i>
            </a>

            <div class="submenu <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'collapse show':'collapse'; ?>" id="SubMenu1">
               <a href="<?php echo !$menu_disabled ? base_url('seller/warehouse/') : 'javascript:void(0);'; ?>" 
                  class="list-submenu <?php echo $menu_disabled ? ' link-disabled' : ''; ?>" 
                  data-parent="#SubMenu1"><?= $this->lang->line('sidebar_product') ?></a>
            </div>
         </li>

         <li class="nav-item <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUs','webshopPromoTextBanners']))?'active':''; ?>">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop',$this->session->userdata('userPermission'))){ ?>
               <a class="nav-link <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUs','webshopPromoTextBanners']))?'has-submenu collapsed':''; ?>
                  <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
                  href="<?php echo !$menu_disabled ? '#SubMenu2' : 'javascript:void(0);'; ?>" 
                  <?php if(!$menu_disabled): ?>data-toggle="collapse" data-parent="#SubMenu2"<?php endif; ?>>
                  <?= $this->lang->line('sidebar_eshop') ?>
                  <i class="fas fa-angle-down"></i>
               </a>
            <?php } ?>

            <div class="submenu <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUsRequests','webshopPromoTextBanners']))?'collapse show':'collapse'; ?>" id="SubMenu2">
               <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders',$this->session->userdata('userPermission'))){ ?>
                  <a href="<?php echo !$menu_disabled ? base_url('webshop/b2b-orders') : 'javascript:void(0);'; ?>" 
                     class="list-submenu <?php echo $menu_disabled ? ' link-disabled' : ''; ?>" 
                     data-parent="#SubMenu2"><?= $this->lang->line('sidebar_orders') ?></a>
               <?php } ?>
               <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/special-pricing', $this->session->userdata('userPermission'))) { ?>
                     <a href="<?php echo !$menu_disabled ? base_url('webshop/special-pricing') : 'javascript:void(0);'; ?>" class="list-submenu <?php echo $menu_disabled ? ' link-disabled' : ''; ?>" data-parent="#SubMenu2"><?= $this->lang->line('sidebar_discounts') ?></a>
                  <?php } ?>
            </div>
         </li>
         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'payouts')) ? 'has-submenu  show' : ''; ?> <?php echo $menu_disabled ? ' link-disabled' : ''; ?>" href="<?php echo !$menu_disabled ? base_url('payouts') : 'javascript:void(0);'; ?>">
               <?= $this->lang->line('sidebar_payouts') ?>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'webshop/return-orders')) ? 'has-submenu  show' : ''; ?> <?php echo $menu_disabled ? ' link-disabled' : ''; ?>" href="<?php echo !$menu_disabled ? base_url('webshop/return-orders') : 'javascript:void(0);'; ?>">
               <?= $this->lang->line('sidebar_return_replace') ?>
            </a>
         </li>

       
<li class="nav-item">
    <!-- Main Add-On Services Menu with Submenu Toggle & Arrow Icon -->
    <a class="nav-link d-flex justify-content-between align-items-center <?php echo (isset($side_menu) && in_array($side_menu, ['addons', 'manage_addons'])) ? 'has-submenu show' : ''; ?> <?php echo (isset($profile_incomplete) && $profile_incomplete) ? ' link-disabled' : ''; ?>" 
        href="javascript:void(0);" 
        data-toggle="collapse" 
        data-target="#addonsSubmenu"
        aria-expanded="<?php echo (isset($side_menu) && in_array($side_menu, ['addons', 'manage_addons'])) ? 'true' : 'false'; ?>">
        <span><?= $this->lang->line('sidebar_addons_services') ?></span>
        <i class="fa fa-angle-down"></i>
    </a>
    
    <!-- Submenu List -->
    <div id="addonsSubmenu" class="collapse <?php echo (isset($side_menu) && in_array($side_menu, ['addons', 'manage_addons'])) ? 'show' : ''; ?>">
        <ul class="nav flex-column sub-menu pl-3">
            <!-- Link to Browse Add-ons -->
            <li class="nav-item">
                
                <a class="nav-link <?php echo (isset($side_menu) && $side_menu == 'addons') ? 'active' : ''; ?>" 
                    href="<?php echo (!isset($profile_incomplete) || !$profile_incomplete) ? base_url('addons') : 'javascript:void(0);'; ?>">
                    <?= $this->lang->line('sidebar_browse_addons') ?>
                </a>
            </li>
            <!-- Link to Manage Addon -->
            <li class="nav-item">
               
                <a class="nav-link <?php echo (isset($side_menu) && $side_menu == 'manage_addons') ? 'active' : ''; ?>" 
                    href="<?php echo (!isset($profile_incomplete) || !$profile_incomplete) ? base_url('addons/manage') : 'javascript:void(0);'; ?>">
                    <?= $this->lang->line('sidebar_manage_addons') ?>
                </a>
            </li>
        </ul>
    </div>
</li>
         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='mydocuments')) ? 'has-submenu show' : ''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? base_url('mydocuments') : 'javascript:void(0);'; ?>">
               <?= $this->lang->line('sidebar_my_documents') ?>
            </a>
         </li>

        <!-- Help Desk Menu (Always active) -->
        <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'help_desk')) ? 'active' : ''; ?>">

    <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'help_desk')) ? 'has-submenu collapsed' : ''; ?>"
       href="#HelpDeskMenu"
       data-toggle="collapse"
       data-parent="#HelpDeskMenu">

        <?php echo lang('menu_help_desk'); ?>
        <i class="fas fa-angle-down"></i>
    </a>

    <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'help_desk')) ? 'collapse show' : 'collapse'; ?>"
         id="HelpDeskMenu">

        <a class="list-submenu"
           data-parent="#HelpDeskMenu"
           href="<?php echo base_url() ?>help_desk/shopper">
            <?php echo lang('menu_shopper_helpdesk'); ?>
        </a>

        <a class="list-submenu"
           data-parent="#HelpDeskMenu"
           href="<?php echo base_url() ?>help_desk/merchant">
            <?php echo lang('menu_merchant_helpdesk'); ?>
        </a>

    </div>
</li>

<!-- Order Resolution Menu (Always active) -->
<li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'order_resolution')) ? 'active' : ''; ?>">
    <a class="nav-link" href="<?php echo base_url('order_resolution'); ?>">
        <i class="fa fa-ticket mr-1"></i> <?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?>
    </a>
</li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='messaging')) ? 'has-submenu show' : ''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? base_url('messaging') : 'javascript:void(0);'; ?>">
               <?= $this->lang->line('sidebar_messaging') ?>
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='productbadges')) ? 'has-submenu show' : ''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? base_url('productbadges') : 'javascript:void(0);'; ?>">
               <?= $this->lang->line('sidebar_product_badges') ?>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='merchantfaqs')) ? 'has-submenu show' : ''; ?>
               <?php echo $menu_disabled ? ' link-disabled' : ''; ?>"
               href="<?php echo !$menu_disabled ? base_url('merchantfaqs') : 'javascript:void(0);'; ?>">
              <?= $this->lang->line('sidebar_merchant_faq') ?>
            </a>
         </li>
 

         <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url('logout'); ?>">
               <?= $this->lang->line('sidebar_logout') ?>
            </a>
         </li>

      </ul>
   </div>
</nav>

