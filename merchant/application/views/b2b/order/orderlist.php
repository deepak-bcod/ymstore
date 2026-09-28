<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('b2b/order/breadcrums'); ?>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name"><?php echo lang('b2b_details'); ?> </h1>
				<div class="float-right product-filter-div ">
					<div class="search-div d-none" id="pro-search-div">
						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="" aria-label="Search">
						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
					</div>

					<!-- <div class="filter">
						<button>
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z" />
							</svg>
							<?php //echo lang('filter'); ?>
						</button>
					</div> -->

					<div class="filter-section">
						<span class="reset-arrow"><a href="javascript:void(0);" onclick="location.reload();"><?php echo lang('reset'); ?></a></span>
						<div class="close-arrow"> <i class="fa fa-angle-left"></i> </div>

						<div class="filter filter-inside">
							<button>
								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"></path>
								</svg>
								<?php echo lang('filter'); ?>
							</button>
						</div>

						<div class="justify-content-center my-4 status-box">
							<h3><?php echo lang('order_status'); ?></h3>
							<div class="col-md-12">
								<select class="form-control" name="order_status" id="order_status">
									<option value=""><?php echo lang('select'); ?></option>
									<?php if ($current_tab == 'shipped-orders') { ?>
										<option value="4"><?php echo lang('tracking_missing'); ?></option>
										<option value="5"><?php echo lang('tracking_incomplete'); ?></option>
										<option value="6"><?php echo lang('tracking_complete'); ?></option>
									<?php } else { ?>
										<option value="0"><?php echo lang('to_be_processed'); ?></option>
										<option value="1"><?php echo lang('processing'); ?></option>
										<option value="2"><?php echo lang('complete'); ?></option>
										<option value="3"><?php echo lang('cancelled'); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="justify-content-center my-4 status-box">
							<h3><?php echo lang('payment_method'); ?></h3>
							<div class="col-md-12">
								<select class="form-control" name="payment_method" id="payment_method">
									<option value=""><?php echo lang('select'); ?></option>
									<?php if ($current_tab == 'shipped-orders') { ?>
										<option value="2">Cc Avenue</option>
										<option value="8"><?php echo lang('cheque_transfer'); ?></option>
									<?php } else { ?>
										<option value="2">Cc Avenue</option>
										<option value="8"><?php echo lang('cheque_transfer'); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="justify-content-center my-4 price-range">
							<h3><?php echo lang('grand_total_range'); ?></h3>
							<form class="range-field w-100">
								<input id="slider11" class="border-0" value="0" type="range" min="0" max="100000" />
							</form>
							<span class="zero-value">0</span>
							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan"></span>
						</div>

						<div class="justify-content-center my-4 supplier-box">
							<h3><?php echo lang('shipment_type'); ?></h3>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="1"><span class="checked"></span> <?php echo lang('buy_in'); ?></label></div>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="2"><span class="checked"></span> <?php echo lang('dropship'); ?></label></div>
						</div>

						<div class="justify-content-center my-4 last-updated">
							<h3><?php echo lang('last_updated'); ?></h3>
							<div class="col-md-5"><input type="text" class="form-control" id="from_date"></div>
							<div class="col-md-2"><?php echo lang('to'); ?></div>
							<div class="col-md-5"><input type="text" class="form-control" id="to_date"></div>
						</div>

						<div class="filter-btn-box">
							<button class="filter-btn" onclick="FilterOrdersDataTable();"><?php echo lang('filter'); ?></button>
						</div>
					</div>

				</div>
			</div>

			<div class="content-main form-dashboard">
				<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_B2BOrders">
						<thead>
							<tr>
								<th><?= str_replace('|', '<br>', $this->lang->line('b2b_order_number')); ?></th>
								<th><?= str_replace('|', '<br>', $this->lang->line('webshop_order_no')); ?></th>
								<th><?php echo lang('purchased_on'); ?></th>
								<th><?php echo lang('shopper_name'); ?></th>
								<th><?php echo lang('status'); ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>

		</div>

	</div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS ?>b2b_order_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>
