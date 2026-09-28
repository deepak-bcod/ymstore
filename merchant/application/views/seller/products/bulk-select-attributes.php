<?php $is_french = ($this->session->userdata('site_lang') == 'french'); ?>
 <div class="add-bulk-inner3">
		<h1 class="head-name"><?= $is_french ? 'Télécharger CSV - Sélectionner la catégorie' : 'Download CSV - Select Category'; ?></h1>
			<div class="add-bulk-inner2-form">
			   <div class="col-md-5 pr-5">
                     <div class="form-group row">
                        <label for="" class="col-sm-4 col-form-label font-500"><?= $is_french ? 'Catégorie' : 'Category'; ?></label>
                        <div class="col-sm-8">
                          <select class="form-control">
							<option>Footware</option>
						  </select>
                        </div>
                      </div>
                </div><!-- col-md-5 -->
				
				 <div class="col-md-5 pr-5">
                     <div class="form-group row">
                        <label for="" class="col-sm-4 col-form-label font-500"><?= $is_french ? 'Sous-catégorie' : 'Sub-Category'; ?></label>
                        <div class="col-sm-8">
                           <select class="form-control">
							<option>Shoes</option>
						  </select>
                        </div>
                      </div>
                </div><!-- col-md-5 -->
				
				
				<div class="select-attributes">
					<h3><?= $is_french ? 'Sélectionner des attributs' : 'Select Attributes'; ?> <span><?= $is_french ? '( Double-cliquez pour modifier )' : '( Double Click to edit )'; ?></span> <span class="add-new-attributes"><a href=""><?= $is_french ? '+ Ajouter un nouvel attribut' : '+ Add New Attribute'; ?></a></span></h3>
					<ul>
						<li> <label class="checkbox"><input type="checkbox"> Product Title <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Product Highlights <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Product Media <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Shipping <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Product Description <span class="checked"></span></label> </li>
					</ul>
				</div><!-- select-attributes -->
				
				
				<div class="select-attributes">
					<h3><?= $is_french ? 'Sélectionner des variantes' : 'Select Variants'; ?> <span><?= $is_french ? '( Double-cliquez pour modifier )' : '( Double Click to edit )'; ?></span> <span class="add-new-attributes"> <a href=""><?= $is_french ? '+ Ajouter une nouvelle variante' : '+ Add New Variants'; ?></a></span></h3>
					<ul>
						<li> <label class="checkbox"><input type="checkbox"> Color <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Size <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Material <span class="checked"></span></label> </li>
						<li> <label class="checkbox"><input type="checkbox"> Weight <span class="checked"></span></label> </li>
					</ul>
				</div><!-- select-attributes -->
				
			</div>
		 
		
		 <div class="download-discard-small">
			<button class="white-btn"><?= $is_french ? 'Annuler' : 'Discard'; ?></button>
			<button class="download-btn"><?= $is_french ? 'Télécharger' : 'Download'; ?></button>
		 </div>
		 </div>
		 <!-- add-bulk-inner3 -->
		 