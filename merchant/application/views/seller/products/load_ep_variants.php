<?php
$conf_simple=array();
$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();

$giftsTo = $this->SellerProductModel->get_gifts_data();

if(isset($VariantProducts) && count($VariantProducts)>0){
	foreach($VariantProducts as $variant){
// 		echo "<pre>";
// print_r($VariantProducts); 
// echo "</pre>";
		
		
			 $random_id=rand(1,9999);
			 
						 
			 $price_input_prop='';

			//  if($variant['shop_id']>0){
				 
			// 	 $seller_id = $variant['shop_id'];
			// 	 $owner_id =	$this->session->userdata('ShopID');
 
			// 	 $Price_permission_by_shopid = $this->SellerProductModel->getPricePermissionByShopID($seller_id,$owner_id);
			// 	 $shop_perm_to_change_price=$Price_permission_by_shopid->perm_to_change_price;
			// 	 if($shop_perm_to_change_price==0){
			// 		 $price_input_prop='readonly';
			// 	 }else{
			// 		 $price_input_prop='';
			// 	 }
					 
			//  }else{
			// 	 $price_input_prop='';
			//  }
			
			$available_qty=$variant['qty']-$variant['available_qty'];
			 
			 
			  ?>
			<?php 
						if($ProductData->product_inv_type == 'buy') {
							  if($ProductData->id < 0) { ?>
							  	<!-- <input type="checkbox" id="ckb_Variant"> -->
							 <td>
							  	<label class="checkbox">
	                              <input type="checkbox"  name="ckb_Variant[]" value="<?php echo $variant['id']; ?>"  > 
	                              <span class="checked"></span>
	                            </label>
	                        </td>
						<?php   } else {  } // No checkbox
							}elseif($ProductData->product_inv_type == 'virtual') { ?>
							  	
							  
							 		
    <?php } ?>

    <!-- <td>
        <select name="variant_type[]" class="form-control">
             <option value="simple">Simple</option>
             </select>
    </td> -->

    <?php 
    if(isset($VariantMaster) && count($VariantMaster)>0){ 
// 			echo "<pre>";
// print_r($VariantMaster); 
// echo "</pre>";
					foreach($VariantMaster as $attr){
					
					 if(isset($side_menu) && $side_menu=='product_view'){				
						$OptionSelected=$this->ShopProductModel->getSingleDataByID('products_variants',array('product_id'=>$variant['id'],'parent_id'=>$ProductData->id,'attr_id'=>$attr['attr_id']),'attr_value');
					 }else{
						 $OptionSelected=$this->SellerProductModel->getSingleDataByID('products_variants',array('product_id'=>$variant['id'],'parent_id'=>$ProductData->id,'attr_id'=>$attr['attr_id']),'attr_value');
					 }
					
					$attr_option_selected=(isset($OptionSelected) && $OptionSelected->attr_value!='')?$OptionSelected->attr_value:'';
					
					//$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr['attr_id']),'id,attr_id,attr_options_name');
					$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('id'=>$attr['attr_id']),'id,attr_name,attr_code');
					//$OptionList=$this->CommonModel->GetDropDownOptions($OptionData->attr_id);	
					
					 if(isset($side_menu) && $side_menu=='product_view'){	
						
						$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['attr_id']);					 
					 }else{
						$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['attr_id']);
					 }					

					$attr_code=$AttrData->attr_code;
					$attr_code=strtolower($attr_code);
					$input_name='variant_'.$attr_code;
					?>
					
					 <td>
					 <?php // echo $attr['attr_id'].'=='.$attr_option_selected; ?>
					 <select name="<?php echo $input_name; ?>[]" class="form-control" >
					 <?php
						if(isset($OptionList) && count($OptionList)>0){
					 foreach($OptionList as $option){ ?>
						<option value="<?php echo $option['id']; ?>"  <?php echo ($attr_option_selected==$option['id'])?'selected':''; ?> ><?php echo $option['attr_options_name']; ?></option>
					<?php } } ?>
					 </select>
					 </td>
    <?php } } ?>

    <td>
        <input type="number" name="variant_stock[]" class="form-control" value="<?php echo $variant['qty']; ?>">
    </td>

    <td>
        <input type="number" name="variant_cost_price[]" class="form-control" value="<?php echo $variant['cost_price']; ?>">
    </td>

    <td>
        <input type="number" name="variant_price[]" id="variant_price_<?php echo $random_id; ?>" 
               class="form-control selling-price" value="<?php echo $variant['price']; ?>" 
               onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);">
    </td>

    <td>
        <input type="number" 
           name="variant_tax_percent[]" 
           id="variant_tax_percent_<?php echo $random_id; ?>" 
           class="form-control tax-percent" 
           value="<?php echo ($variant['tax_percent'] > 100) ? 100 : $variant['tax_percent']; ?>" 
           min="0" 
           max="100" 
           step="0.01"
           oninput="if(parseFloat(value) > 100) value = 100; if(parseFloat(value) < 0) value = 0;"
           onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);">
    </td>

    <td>
        <input type="number" name="variant_webshop_price[]" id="variant_webshop_price_<?php echo $random_id; ?>" 
               class="form-control" value="<?php echo $variant['webshop_price']; ?>" readonly>
    </td>
    <input type="hidden" name="conf_simple[]" class="form-control "  value="<?php echo $variant['id']; ?>" >
    <td>
        <input type="text" name="variant_sku[]" class="form-control" value="<?php echo $variant['sku']; ?>">
    </td>

    <!-- <td>
        <select class="form-control" name="gifts[]">
            <option value="">Select Gifts</option>
            <?php foreach($giftsTo as $gifts):?>
                <option value="<?php echo $gifts['id']?>" <?php echo ($variant['gift_id'] == $gifts['id']) ? 'selected' : '' ?>><?php echo $gifts['name']?></option>
            <?php endforeach;?>
        </select>
    </td> -->

    <!-- <td>
        <input type="text" 
               name="sub_issue[]" 
               class="form-control input-sm" 
               value="<?php echo isset($variant['sub_issues']) ? $variant['sub_issues'] : ''; ?>" 
               placeholder="Enter issues">
    </td> -->

    <td>
    <a href="javascript:void(0);" class="link-red delete-variant" data-id="<?php echo $variant['id']; ?>">Delete</a>
</td>
</tr>
			 
		  <?php 
		  // onblur="barcodedbcompare(this);"  unique-barcode
		 
	}
}  
?>
  
  
  
<script>
$(document).on('click', '.delete-variant', function(e) {
    e.preventDefault();
    var variantId = $(this).data('id');
    var $row = $(this).closest('tr');

    if(confirm("Are you sure you want to delete this variant?")) {
        // Send AJAX request to your server
        $.ajax({
            url: '<?php echo base_url("Sellerproduct/delete_variant_data"); ?>',
            type: 'POST',
            data: { variant_id: variantId },
            success: function(response) {
                var res = JSON.parse(response);
                if(res.status == 200) {
                    // Only remove from view AFTER server confirms deletion
                    $row.remove();
                    alert('Variant deleted successfully');
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting.');
            }
        });
    }
});
</script>
