<ul class="nav nav-pills">
		<li class="<?php echo (isset($side_menu) && $side_menu=='warehouse')?'active':''; ?>"><a  href="<?php echo base_url() ?>seller/warehouse/"><?php echo lang('warehouse'); ?></a></li>
		<?php if(isset($side_menu) && $side_menu!='warehouse'){?>
		<li class="<?php echo (isset($side_menu) && ($side_menu=='add_product' || $side_menu=='bulk-add'))?'active':''; ?>"><a  href="<?php echo base_url() ?>seller/warehouse/?goto=add_new"><?php echo lang('add_new'); ?></a></li>
		<?php }else { ?>
		<li class="<?php echo (isset($side_menu) && ($side_menu=='add_product' || $side_menu=='bulk-add'))?'active':''; ?>"><a data-toggle="pill" id="add_product_link" href="#addnew"><?php echo lang('add_new'); ?></a></li>
		<?php } ?>
		<!-- <li class="<?php //echo (isset($side_menu) && $side_menu=='dropship')?'active':''; ?>"><a  href="<?php // echo base_url(); ?>seller/warehouse/dropship"><?php //echo lang('dropshipping_products'); ?></a></li> -->
		<?php if(isset($side_menu) && $side_menu=='edit_product'){
			$base_url =  base_url();
			$ShopID =   $this->session->userdata('ShopID'); 
			$replacement ="shop".$ShopID.".";
			$url=  substr_replace($base_url, $replacement, 8,0); 
		}
		?>	
</ul>
