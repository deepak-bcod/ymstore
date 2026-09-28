<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/discount/breadcrums');?>
	<div class="tab-content">
<div id="Warehouse" class="tab-pane fade active show">

	<div class="product-details-block">
		<div class="row">
			<div class="add-bulk-block">
				<input type="hidden" value="bulk-add" id="current_page" name="current_page">
				<input type="hidden" value="" id="pid" name="pid">

				<div class="add-bulk-inner1">
				<h1 class="head-name"><?= lang('add_bulk_special_pricing') ?></h1>

				 <div class="save-discard-btn upload-csv">
				<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
					<button class="purple-btn" type="button" onclick="OpenBulkUploadPopup();"><?= lang('upload_csv') ?></button>
				<?php } ?>
					<button class="white-btn"  type="button" onclick="OpenBulkSelectCategory('import');"><?= lang('download_csv') ?></button>
					<!-- <button class="white-btn"  type="button" onclick="OpenBulkSelectCategory('importAll');"><?= lang('download_all') ?></button> -->
				 </div>
				 </div>
			 <!-- add-bulk-inner1 -->

		    </div> <!-- add-bulk-block -->

		</div><!-- row -->
	</div><!-- product-details-block -->
</div>


	</div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS; ?>special_pricing.js?v=<?php echo CSSJS_VERSION; ?>"></script>
