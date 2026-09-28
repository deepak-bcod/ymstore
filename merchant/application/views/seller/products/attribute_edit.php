<?php $is_french = ($this->session->userdata('site_lang') == 'french'); ?>
    <div class="main-inner ">
        <form id="attributeForm" method="POST">
            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
			  <input type="hidden" name="attribute_id" id="attribute_id" value="<?php echo  $attribute->id ?>">
			
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20"><?= $is_french ? 'Attribut' : 'Attribute'; ?></h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Nom de l'attribut" : 'Attribute Name'; ?> <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_name" id="attribute_name" value="<?php echo $attribute->attr_name ?>" required <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Code de l'attribut" : 'Attribute Code'; ?> <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_code" id="attribute_code" value="<?php echo $attribute->attr_code ?>" required <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>>
                        </div>
                  </div><!-- form-group -->

                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Description de l'attribut" : 'Attribute Description'; ?></label>
                        <div class="col-sm-7">
                            <textarea class="form-control" name="attribute_description" id="attribute_description" <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>><?php echo $attribute->attr_description ?></textarea>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Propriétés de l'attribut" : 'Attribute Properties'; ?> <span class="required">*</span></label>
                        <div class="col-sm-4">
                            <select class="form-control" name="attribute_properties" id="attribute_properties" <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'disabled'; ?> >
                                <option value=""><?= $is_french ? "Sélectionner les propriétés de l'attribut" : 'Select Attribute Properties'; ?></option>
                                <option value="1" <?php if($attribute->attribute_properties == 1){echo "selected";} ?> ><?= $is_french ? 'Champ texte' : 'Text Field'; ?></option>
                                <option value="2" <?php if($attribute->attribute_properties == 2){echo "selected";} ?>><?= $is_french ? 'Zone de texte' : 'Text Area'; ?></option>
                                <option value="3" <?php if($attribute->attribute_properties == 3){echo "selected";} ?>><?= $is_french ? 'Date' : 'Date'; ?></option>
                                <option value="4" <?php if($attribute->attribute_properties == 4){echo "selected";} ?>><?= $is_french ? 'Oui/Non' : 'Yes/No'; ?></option>
                                <option value="5" <?php if($attribute->attribute_properties == 5){echo "selected";} ?>><?= $is_french ? 'Liste déroulante' : 'Dropdown'; ?></option>
                                <option value="6" <?php if($attribute->attribute_properties == 6){echo "selected";} ?>><?= $is_french ? 'Sélection multiple' : 'Multiselect'; ?></option>

                            </select>
                        </div>
                  </div><!-- form-group -->
                  
                  <?php 
				  $fbc_user_id	=	$this->session->userdata('LoginID');
				  $shop_id	=	$this->session->userdata('ShopID');
					 $options_arr= $this->EavAttributesModel->get_attributes_options_by_seller($shop_id,$attribute->id);
					 $options_arr_selected=array();
					 if(isset($options_arr) && count($options_arr)>0){
					 foreach($options_arr as $option){
						 $options_arr_selected[]=$option['attr_options_name'];
					 }
					 }
					 
					 $opt_str=implode(',',$options_arr_selected);
					  ?>
                  <div class="form-group row" style="<?php if($attribute->attribute_properties == 5 || $attribute->attribute_properties == 6){echo "display: flex;";}else {echo "display: none;";} ?>" id="slectvalue">
                    <label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Valeurs de l'attribut" : 'Attribute Values'; ?> <span class="required">*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsValues" id="tagsValues"  value="<?php echo $opt_str; ?>">
                    </div>
                  </div>
              
			  <?php 
				  $display_on_frontend = (isset($attribute_display->display_on_frontend) && $attribute_display->display_on_frontend==1)?1:'';
				   $filterable_with_results = (isset($attribute_display->filterable_with_results) && $attribute_display->filterable_with_results==1)?1:'';
				   ?>
				   <div class=" switch-onoff">
					  <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? "Afficher sur l'interface publique" : 'Display On Frontend'; ?></label>
						 <div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="display_on_frontend" value="1" autocomplete="off"   <?php if($display_on_frontend == 1){echo "checked";} ?>  > 
								<span class="checked"></span>
								</label>
							</div>
					</div>	
					
					 <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500"><?= $is_french ? 'Filtrable avec les résultats' : 'Filterable With Result'; ?></label>
						 <div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="filterable_with_results" value="1" autocomplete="off"   <?php if($filterable_with_results == 1){echo "checked";} ?>> 
								<span class="checked"></span>
								</label>
								  </div>
					</div>	
				</div><!-- bs-example -->
                 
				  
				
                  <div class="download-discard-small ">
						<?php if(isset($flag) && $flag=='edit'){?>
						 <button class="white-btn" type="button"  data-dismiss="modal"><?= $is_french ? 'Annuler' : 'Discard'; ?></button>
						<?php }else { ?>
                        <button class="white-btn" type="button" onclick="OpenAttributeListPopup('<?php echo $category_id; ?>');"><?= $is_french ? 'Annuler' : 'Discard'; ?></button>
						<?php } ?>
                        <button type="submit" class="download-btn" id="attr_save_btn" ><?= $is_french ? 'Enregistrer' : 'Save'; ?></button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
        </form>

    </div><!-- add new tab -->
	