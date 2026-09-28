<div id="addnew" class="tab-pane fade addnew-bg">
	<div class="row add-new-section">
		<div class="col-md-6">
			<h3><?php echo lang('add_bulk_product'); ?></h3>
			<button class="puple-btn" type="button" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/bulk-add');" ><?php echo lang('continue'); ?></button>
		</div>
		<div class="col-md-6">
			<h3><?php echo lang('add_single_product'); ?></h3>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
			<button type="button" class="puple-btn" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/add');" ><?php echo lang('continue'); ?></button>
		<?php } ?>
		</div>
		<!-- <div class="col-md-6">
			<h3>Add Bundle Product</h3>
			<button class="puple-btn" type="button" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/add?type=bundle');" >Continue</button>
		</div> -->
	</div>
	<!-- row -->
</div>
