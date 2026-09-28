<?php $is_french = ($this->session->userdata('site_lang') == 'french'); ?>
<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name"><?= $is_french ? 'Télécharger tous les fichiers CSV' : 'Download All CSV'; ?> </h1>
		
		 <div class="download-discard-small">
			<button class="white-btn" type="button" data-dismiss="modal"><?= $is_french ? 'Annuler' : 'Discard'; ?></button>
			<button class="download-btn" type="button" name="bulk_cat_save" id="bulk_cat_save" onclick="DownloadWeightLocationCSV();"><?= $is_french ? 'Télécharger' : 'Download'; ?></button>
		 </div>
	 </div>
	 <!-- add-bulk-inner2 -->
</div>