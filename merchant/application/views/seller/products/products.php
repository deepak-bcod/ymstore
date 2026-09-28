<div id="Warehouse" class="tab-pane fade in active admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name"><?php echo lang('product_list'); ?> </h1>
				
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				
					<div class="table-responsive text-center">
						<table  class="table table-bordered table-style data-tbl dataTable dtr-inline product-list-tbl warehouse-table" id="DataTables_Table_WProducts" role="grid" aria-describedby="DataTables_Table_WProducts_info" >
							<thead>
								<tr>
									<th><?php echo lang('product_name'); ?>  </th>
									<th><?php echo lang('categories'); ?> </th>
									<!-- <th><?php echo lang('product_code'); ?>  </th> -->
									<th><?php echo lang('inventory'); ?> </th>
									<th><?php echo lang('price'); ?></th>
									
									<th><?php echo lang('eshop_price'); ?></th>
									<th><?php echo lang('last_updated'); ?> </th>
									<th><?php echo lang('details'); ?> </th>
									
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						
					</div>
				
			</div>
			<!--end form-->
		</div>
		<script type="text/javascript">
		$(document).ready(function(){
			<?php if(isset($_GET['goto']) && $_GET['goto']=='add_new'){?>
			$('#add_product_link').click();
			<?php } ?>
			
		});
		
		</script>
		<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller_product_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>
		<style>
			table.warehouse-table {width:1500px !important}
			table.warehouse-table th {text-align: center !important;}
			table.warehouse-table th.sorting:first-child {width:300px !important; text-align: left !important;}
			table.warehouse-table tbody td:first-child {
					text-align: left !important;
			}
		</style>
		
