<?php 
if(isset($ProductData) && $ProductData->id!='')
{
	$product_id=$ProductData->id;
	$flag='edit';
	
	
}else{
	$product_id='';
	$flag='add';
}
?>

<style>
.custom-accordion .list-gc, 
.custom-accordion .list-gc1, 
.custom-accordion .list-gc2 {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}
.custom-accordion .list-gc1 {
    padding-left: 24px;
    border-left: 2px solid #f0f0f0;
    margin-left: 8px;
    margin-top: 4px;
    margin-bottom: 6px;
}
.custom-accordion .list-gc2 {
    padding-left: 24px;
    border-left: 2px dashed #f0f0f0;
    margin-left: 8px;
    margin-top: 4px;
    margin-bottom: 6px;
}
.custom-accordion .list-gc-item, 
.custom-accordion .list-gcc-item {
    margin-bottom: 6px;
    position: relative;
}
.custom-accordion label.checkbox {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 0;
    cursor: pointer;
    font-weight: 500;
}
.custom-accordion .list-gc1 label.checkbox {
    font-weight: 400;
    color: #495057;
}
.custom-accordion .list-gc2 label.checkbox {
    font-weight: 400;
    font-size: 13px;
    color: #6c757d;
}
.custom-accordion .custom-accordion-title {
    display: inline-block;
    padding: 2px 6px;
    color: #f39c12;
    cursor: pointer;
    text-decoration: none !important;
}
.custom-accordion .custom-accordion-title i.accordion-arrow {
    transition: transform 0.2s ease-in-out;
    font-size: 14px;
}
.custom-accordion .custom-accordion-title[aria-expanded="true"] i.accordion-arrow {
    transform: rotate(180deg);
}
</style>

<div class="accordion custom-accordion" id="custom-accordion-one"> 
<ul class="common-list list-gc">
	<?php if(isset($CategoryTree) && count($CategoryTree)>0){
		foreach($CategoryTree as $parent_cat) {
			$has_sub = isset($parent_cat['sub_category']) && count($parent_cat['sub_category']) > 0;
			$parent_selected = ($flag == 'edit') && count($cat_level_zero_selected) > 0 && in_array($parent_cat['id'], $cat_level_zero_selected);
		?>
		<li class="list-gc-item">
			<div class="custom-control custom-checkbox">
				<label class="checkbox">
					<input type="checkbox" name="category[]" class="form-control" value="<?php echo $parent_cat['id']; ?>" id="level_zero_cat_<?php echo $parent_cat['id']; ?>" onclick="LoadSubCategory('<?php echo $parent_cat['id']; ?>','<?php echo $flag; ?>','<?php echo $product_id; ?>');" <?php echo $parent_selected ? 'checked' : ''; ?> <?php echo (isset($side_menu) && $side_menu=='product_view') ? 'readonly' : ''; ?> >
					<span class="checked"></span>
					<span class="sis-cat-name"><?php echo ($this->session->userdata('site_lang')=='french' && !empty($parent_cat['lang_title'])) ? $parent_cat['lang_title'] : $parent_cat['cat_name']; ?></span>
					<?php if($has_sub){ ?>
					<a class="custom-accordion-title"
					data-toggle="collapse" href="#subCatOuter_<?php echo $parent_cat['id']; ?>"
					aria-expanded="<?php echo $parent_selected ? 'true' : 'false'; ?>" aria-controls="subCatOuter_<?php echo $parent_cat['id']; ?>" onclick="event.stopPropagation();">&nbsp;<i class="accordion-arrow fa fa-angle-down"></i></a>
					<?php } ?>
				</label>
			</div>
			
			<?php if($has_sub){ ?>
			<div id="subCatOuter_<?php echo $parent_cat['id']; ?>" class="collapse <?php echo $parent_selected ? 'show' : ''; ?>">
			<ul class="common-list list-gc1">
				<?php foreach($parent_cat['sub_category'] as $sub_cat) { 
					$has_child = isset($sub_cat['sub_category']) && count($sub_cat['sub_category']) > 0;
					$sub_selected = ($flag == 'edit') && count($cat_level_one_selected) > 0 && in_array($sub_cat['id'], $cat_level_one_selected);
				?>
				<li class="list-gc-item">
					<div class="custom-control custom-checkbox">
						<label class="checkbox">
							<input type="checkbox" name="sub_category[]" class="form-control" value="<?php echo $sub_cat['id']; ?>" id="level_one_cat_<?php echo $sub_cat['id']; ?>" <?php echo $sub_selected ? 'checked' : ''; ?> <?php echo (isset($side_menu) && $side_menu=='product_view') ? 'readonly' : ''; ?> onclick="SelectParentCategory(this,<?php echo $parent_cat['id']; ?>,<?php echo $sub_cat['id']; ?>,'');" >
							<span class="checked"></span>
							<span class="sis-cat-name"><?php echo ($this->session->userdata('site_lang')=='french' && !empty($sub_cat['lang_title'])) ? $sub_cat['lang_title'] : $sub_cat['cat_name']; ?></span>
							<?php if($has_child){ ?>
							<a class="custom-accordion-title"
							data-toggle="collapse" href="#tagsCatOuter_<?php echo $parent_cat['id']; ?>_<?php echo $sub_cat['id']; ?>"
							aria-expanded="<?php echo $sub_selected ? 'true' : 'false'; ?>" aria-controls="tagsCatOuter_<?php echo $parent_cat['id']; ?>_<?php echo $sub_cat['id']; ?>" onclick="event.stopPropagation();">&nbsp;<i class="accordion-arrow fa fa-angle-down"></i></a>
							<?php } ?>
						</label>
					</div>
					
					<?php if($has_child){ ?>
					<div class="tags-category collapse <?php echo $sub_selected ? 'show' : ''; ?>" id="tagsCatOuter_<?php echo $parent_cat['id']; ?>_<?php echo $sub_cat['id']; ?>">
						<ul class="common-list list-gc2">
							<?php foreach($sub_cat['sub_category'] as $tag_cat) { 
								$child_selected = ($flag == 'edit') && count($cat_level_two_selected) > 0 && in_array($tag_cat['id'], $cat_level_two_selected);
							?>
							<li class="list-gcc-item">
								<div class="custom-control custom-checkbox">
									<label class="checkbox">
										<input type="checkbox" name="child_category[]" class="form-control" value="<?php echo $tag_cat['id']; ?>" id="level_two_cat_<?php echo $sub_cat['id']; ?>_<?php echo $tag_cat['id']; ?>" <?php echo $child_selected ? 'checked' : ''; ?> <?php echo (isset($side_menu) && $side_menu=='product_view') ? 'd-none' : ''; ?> onclick="SelectParentCategory(this,<?php echo $parent_cat['id']; ?>,<?php echo $sub_cat['id']; ?>,<?php echo $tag_cat['id']; ?>);" >
										<span class="checked"></span>
										<span class="sis-cat-name"><?php echo ($this->session->userdata('site_lang')=='french' && !empty($tag_cat['lang_title'])) ? $tag_cat['lang_title'] : $tag_cat['cat_name']; ?></span>
									</label>
								</div>
							</li>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>
				</li>
				<?php } ?>
			</ul>
			</div>
			<?php } ?>
		</li>
	<?php 
		}
	} ?>
</ul>
</div>

<script type="text/javascript">

function LoadSubCategory(parent_id,flag,product_id='')
{
	if($('#level_zero_cat_'+parent_id).is(':checked')) {
		$('#subCatOuter_'+parent_id).collapse('show');
	} else {
		$('#subCatOuter_'+parent_id).find('input[type="checkbox"]').prop('checked', false);
	}
}

function SelectParentCategory(elem,level_zero,level_one,level_two=''){
	if($(elem).is(':checked')){
		if(level_two!=''){
			$('#level_zero_cat_'+level_zero).prop('checked',true);
			$('#level_one_cat_'+level_one).prop('checked',true);
			$('#subCatOuter_'+level_zero).collapse('show');
			$('#tagsCatOuter_'+level_zero+'_'+level_one).collapse('show');
			// When a child category is selected, uncheck other sibling child categories under this sub-category
			$('#tagsCatOuter_'+level_zero+'_'+level_one).find('input[name="child_category[]"]').not(elem).prop('checked', false);
		}else{
			$('#level_zero_cat_'+level_zero).prop('checked',true);
			$('#subCatOuter_'+level_zero).collapse('show');
			$('#tagsCatOuter_'+level_zero+'_'+level_one).collapse('show');
		}
	} else {
		if(level_two==''){
			// If sub-category was unchecked, uncheck its child categories
			$('#tagsCatOuter_'+level_zero+'_'+level_one).find('input[name="child_category[]"]').prop('checked', false);
		}
	}
}

function ConfirmCategoryDelete(id,cat_level,flag=''){
	
	var pcount=0;
	if(id!=''){
		
		$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/getcatproductcount/",
				data: {id:id,cat_level:cat_level,flag:flag},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					if(response !='error'){
						pcount=response;
						if(pcount>0){

							var conf_message="There are some products already assigned to this category, Still you want to delete this category? You won't be able to revert this.";
						}else{
							var conf_message="You won't be able to revert this!";
							
						}
						
						
						swal({
							title: "Are you sure? ",
							text: conf_message,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#3085d6",
							 cancelButtonColor: '#d33',
							confirmButtonText: "Yes, delete it!",
							cancelButtonText: "Cancel",
							closeOnConfirm: false,
							closeOnCancel: false
						}, function(isConfirm) {
							if (isConfirm) {
								
								DeleteCategory(id,cat_level);
								
							} else {
								swal.close();
							}
						});
						
					}else{
						swal('Error','Something went wrong!','error');
					}
					
					
				}
			});
	}
}

function DeleteCategory(id,cat_level){
	if(id!=''){
		
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/deletecategory/",
				data: {id:id,cat_level:cat_level},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					
					if(response=='success'){
						RefreshCategoryTree();

						swal('Success','Category deleted successfully!','success');
						
					}else{
						return false;
					}
				}
			});
	}else{
		return false;
	}
					
	
}
</script>