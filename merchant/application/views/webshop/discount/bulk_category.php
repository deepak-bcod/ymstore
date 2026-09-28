<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name"><?php echo $this->lang->line('download_csv_heading'); ?> <?php  echo (($type=='importAll') ? $this->lang->line('download_all_text') : '' );?> CSV </h1>
		
		 <div class="download-discard-small">
			<button class="white-btn" type="button"data-dismiss="modal"><?php echo $this->lang->line('discard_btn'); ?></button>
			<?php 
			if($type == 'importAll')
			{ ?>
				<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadAllProductCSV();"><?php echo $this->lang->line('download_btn'); ?></button>
			<?php }
			else
			{ ?>
				<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadProductCSV();"><?php echo $this->lang->line('download_btn'); ?></button>
			<?php 	}
			?>
			
		 </div>
		 </div>
		 <!-- add-bulk-inner2 -->
		 </div>
