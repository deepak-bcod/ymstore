<ul class="nav nav-pills">

	<li class="<?php echo (isset($current_tab) && $current_tab == 'specialPricing')?'active':''?> "><a href="<?= base_url('webshop/special-pricing') ?>"><?php echo $this->lang->line('special_pricing'); ?></a></li>

	<!-- <li class="<?php echo (isset($current_tab) && $current_tab == 'catDiscount')?'active':''?> "><a href="<?= base_url('webshop/catalogue-discounts') ?>"><?php echo $this->lang->line('catalogue_discounts'); ?></a></li>

	<li class="<?php echo (isset($current_tab) && $current_tab == 'prodDiscount')?'active':''?> "><a href="<?= base_url('webshop/product-discounts') ?>"><?php echo $this->lang->line('product_discounts'); ?></a></li>

	<li class="<?php echo (isset($current_tab) && $current_tab == 'cpCode')?'active':''?> "><a href="<?= base_url('webshop/coupon-discounts') ?>"><?php echo $this->lang->line('coupon_code'); ?></a></li>

	<li class="<?php echo (isset($current_tab) && $current_tab == 'emlCoupon')?'active':''?> "><a href="<?= base_url('webshop/email-coupon') ?>"><?php echo $this->lang->line('email_coupon'); ?></a></li> -->

</ul>
