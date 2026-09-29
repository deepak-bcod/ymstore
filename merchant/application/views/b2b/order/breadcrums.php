<ul class="nav nav-pills">
		<li class="<?php echo ((isset($current_tab) && in_array($current_tab, ['orders', 'order', 'b2b-orders', 'ES-orders'])) || (isset($current_tabs) && in_array($current_tabs, ['orders', 'order', 'b2b-orders', 'ES-orders']))) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b-orders/"><?php echo $this->lang->line('b2b_orders'); ?></a></li>
		<!-- <li class="<?php echo (isset($current_tabs) && ($current_tabs=='split-orders' || $current_tabs=='split-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b/split-orders/"><?php echo $this->lang->line('split_orders'); ?></a></li> -->
		<!-- <li class="<?php echo (isset($current_tabs) && ($current_tabs=='shipped-orders' || $current_tabs=='shipped-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b/shipped-orders/"><?php echo $this->lang->line('shipped_orders'); ?></a></li> -->
		<!-- <li class="<?php echo (isset($current_tabs) && ($current_tabs=='cancel-orders' || $current_tabs=='cancel-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>webshop/cancel-orders/"><?php echo $this->lang->line('cancel_orders'); ?></a></li> -->
		<!-- <div class="filter_order_seaching dataTables_filter">
			<label><input id="global-b2b-order-search" type="search" class="form-control form-control-sm" placeholder="<?php echo $this->lang->line('search_by_order_number'); ?>"></label>
		</div> -->
</ul>
