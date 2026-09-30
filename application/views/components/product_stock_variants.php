<?php 
// $lang = $this->session->userdata('site_lang');
//echo "<pre>";print_r($ProductData);echo "</pre>"; 
?>
<?php if (isset($ProductData)) {
    // echo "<pre>";
    // print_R($ProductData);
    // die();
    if ($ProductData->stock_status == 'Instock' && (!isset($ProductData->total_qty) || (int)$ProductData->total_qty > 0)) {
        if ($ProductData->product_type == "configurable") {
            if (isset($ProductData->product_variants) && count($ProductData->product_variants) > 0 && isset($ProductData->childProducts) && count($ProductData->childProducts) > 0) { ?>
                <input type="hidden" id="variant_main_count" value="<?= count($ProductData->product_variants) ?>">
                <div class="table-responsive">
                    <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                        <thead>
                            <tr class="active">
                                <th>&nbsp;</th>
                                <?php 
                                    $lang = $this->session->userdata('site_lang');

                                    foreach ($ProductData->product_variants as $variant) { ?>
                                        <th class="">
                                            <?php 
                                            if ($lang == 'french' && !empty($variant->variant_name_lang)) {
                                                echo $variant->variant_name_lang;
                                            } else {
                                                echo $variant->variant_name;
                                            }
                                            ?>
                                        </th>
                                    <?php } ?>

                                <?php if($ProductData->id != '4300') { ?>
                                <th>EShop Price (MUR)</th>
                                <?php } ?>
                                <?php
                                    $hasAnySpecial = (isset($ProductData->special_price) && floatval($ProductData->special_price) > 0)
                                        || (isset($ProductData->special_min_price) && floatval($ProductData->special_min_price) > 0)
                                        || (isset($ProductData->special_max_price) && floatval($ProductData->special_max_price) > 0);

                                    if (!$hasAnySpecial && !empty($ProductData->childProducts)) {
                                        foreach ($ProductData->childProducts as $child) {
                                            if (isset($child->special_price) && floatval($child->special_price) > 0) {
                                                $hasAnySpecial = true;
                                                break;
                                            }
                                        }
                                    }
                                ?>

                                <?php if ($hasAnySpecial) { ?>
                                    <th>
                                        Special Price (MUR)
                                     
                                    </th>
                                <?php } ?>

                                <!-- <th>Issues</th>
                                <th>Gift</th> -->
                            </tr>
                        </thead>
                        <tbody>

                            <?php $variant_options = [];
                            $count = 1;
                            $first_instock_checked = false;
                            foreach ($ProductData->childProducts as $child_key => $child_val) {
                                $child_qty = isset($child_val->qty) ? (int)$child_val->qty : 0;
                                $is_child_in_stock = ($child_qty > 0);
                                if ($is_child_in_stock && !$first_instock_checked) {
                                    $selected = 'checked';
                                    $first_instock_checked = true;
                                } else {
                                    $selected = '';
                                }
                                $disabled = $is_child_in_stock ? '' : 'disabled';
                            ?>
                                <tr class="<?php echo $is_child_in_stock ? '' : 'variant-out-of-stock text-muted'; ?>" style="<?php echo $is_child_in_stock ? '' : 'opacity: 0.55;'; ?>">
                                    <td>
                                        <label>
                                            <input id="<?php echo $variant_code; ?>" type="radio" name="variant_options_data" <?php echo $selected; ?> <?php echo $disabled; ?> value="<?php echo $child_val->id ?>,<?php echo $ProductData->id ?>" class="single_variant required-field" onclick="GetVariantProduct()" />
                                        </label>
                                        <input type="hidden" id="variant_option_count" value="">

                                    </td>
                                    <?php
                                        foreach ($child_val->variant_options as $variantkey => $variant) {
                                            $variant_options = $variant[0]->attr_value;
                                            $variant_name = $variant[0]->attr_options_name;
                                        ?>
                                            <?php
                                            $value_attr = $variant[0]->attr_value;
                                            $attr_options_name = $variant[0]->attr_options_name;
                                            
                                            ?>
                                            <td class="subscription-li <?php echo $attr_options_name ?>">
                                                <?php
                                                if (isset($attr_options_name) && !empty($attr_options_name)) {
                                                    echo $attr_options_name ;
                                                }
                                                if (!$is_child_in_stock) {
                                                    echo ' <span class="label label-danger" style="margin-left: 5px; font-size: 10px; padding: 2px 5px;">' . ($this->lang->line('out_of_stock_label') ? $this->lang->line('out_of_stock_label') : 'Out of Stock') . '</span>';
                                                }
                                                ?>
                                            </td>
                                        <?php } ?>

                                        <?php
                                        $childSpecialPrice = '';
                                        if (isset($child_val->special_price) && floatval($child_val->special_price) > 0) {
                                            $childSpecialPrice = $child_val->special_price;
                                        } elseif (isset($ProductData->special_price) && floatval($ProductData->special_price) > 0) {
                                            $childSpecialPrice = $ProductData->special_price;
                                        }
                                        $hasSpecial = !empty($childSpecialPrice);
                                        ?>

                                        <?php if ($ProductData->id != '4300') { ?>
                                            <td class="cover-price" 
                                                style="<?php echo $hasSpecial ? 'text-decoration: line-through;' : ''; ?>">
                                                <?php echo (isset($child_val->webshop_price) && $child_val->webshop_price !== '') ? number_format($child_val->webshop_price, 2, '.', ',') : ''; ?>
                                            </td>
                                        <?php } ?>

                                    
                                    <td class="offer-price">
                                        <?php if($ProductData->id != '4315' && $ProductData->id != '4300'  &&  $ProductData->id != '4313' && $ProductData->id != '4311') { 
                                                $offPercentPrice = ' <span>(' . $child_val->off_percent_price . '% Off)</span>';
                                            }else{
                                               $offPercentPrice = ''; 
                                            }
                                       ?> 
                                    <?php echo $hasSpecial ? number_format($childSpecialPrice, 2, '.', ',') . $offPercentPrice : ''; ?></td>
                                    
                                    <!-- <td><?php echo (isset($child_val->sub_issues)) ? $child_val->sub_issues . ' Issues' : ''; ?></td>
                                    <td><?php echo (isset($child_val->gift_master_name)) ? $child_val->gift_master_name : ''; ?></td> -->




                                </tr>
                            <?php
                                $count++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        <?php } ?>

        <!-- bundle product-variants start -->
        <?php if ($ProductData->product_type == "bundle") {
            $product_id_bundle_child = array();
            $bundle_child_id = array();
        ?>
            <h3>Bundle Price : <?php echo $ProductData->webshop_price; ?></h3>
            <?php foreach ($ProductData->childProducts as $childProducts) {
                if ($childProducts->product_type == 'configurable') {
                    $bundle_child_id[] = $childProducts->bundle_child_id;
            ?>
                    <div class="table-responsive">

                        <h2><?php echo $childProducts->name . " X " . $childProducts->default_qty ?></h2>
                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                            <thead>
                                <tr class="active">
                                    <th>&nbsp;</th>
                                    <th>Subscription</th>
                                    <th>Cover Price </th>
                                    <th>Offer Price </th>
                                    <th>Issues</th>
                                    <th>Gift</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $childProducts_variants = (new ProductDetails())->sortProductDataVariants($childProducts->product_variants); ?>
                                <input type="hidden" id="variant_main_count_<?php echo $childProducts->bundle_child_id ?>" value="<?= count($childProducts_variants) ?>">
                                <input type="hidden" name="product_id_child_main[]" class="product_id_bundle" id="product_id_child_main_<?php echo $childProducts->bundle_child_id ?>" value="<?php echo $childProducts->id ?>">
                                <input type="hidden" name="conf_simple_pid[]" id="conf_simple_pid_<?php echo $childProducts->bundle_child_id ?>" value="">
                                <input type="hidden" name="conf_simple_price[]" id="conf_simple_price_<?php echo $childProducts->bundle_child_id ?>" value="">
                                <input type="hidden" name="conf_simple_qty[]" id="conf_simple_qty_<?php echo $childProducts->bundle_child_id ?>" value="">
                                <input type="hidden" name="bundle_child_id[]" class="bundle_child_id" id="bundle_child_id_<?php echo $childProducts->bundle_child_id ?>" value="<?php echo $childProducts->bundle_child_id ?>">
                                <?php $variant_options = array();
                                foreach ($childProducts_variants as $variant) {
                                    $variant_options = $variant->variant_options;
                                    $variant_code = $variant->variant_code;
                                    $variant_name = $variant->variant_name;
                                    $attr_id = $variant->variant_id;
                                    if ($variant_options == false) { ?>
                                        <input type="hidden" id="variant_option_count" value=0>
                                    <?php } else { ?>
                                        <?php if (isset($variant_options) && count($variant_options) > 0) { ?>
                                            <?php $attr_value = $variant_options[0]->attr_value;
                                            foreach ($variant_options as $vopt) { ?>
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input id="variant_<?php echo $variant_code; ?>_<?php echo $childProducts->bundle_child_id; ?>" class="single_variant_<?php echo $childProducts->bundle_child_id ?> required-field" type="radio" name="variant_<?php echo $variant_code; ?>[<?php echo $childProducts->bundle_child_id ?>]" <?php if ($attr_value == $vopt->attr_value) echo "checked ='checked'"; ?> value="<?php echo $vopt->attr_value . "," . $attr_id . "," . $childProducts->default_qty; ?>" data-bundle_child_id="<?php echo $childProducts->bundle_child_id; ?>" <?php echo $this->form_validation->set_radio('attr_value', $vopt->attr_value); ?> onclick="GetVariantProductForBundle(<?php echo $childProducts->bundle_child_id ?>)">
                                                        </label>
                                                    </td>
                                                    <td><?php echo $vopt->attr_options_name; ?></td>
                                                    <td><?php echo $vopt->webshop_price; ?></td>
                                                    <td><?php echo $childProducts->webshop_price; ?></td>
                                                    <td><?php echo $vopt->sub_issues; ?> Issues</td>
                                                    <td><?php echo $vopt->gift_master_name; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <?php $product_id_bundle_child[] = $childProducts->id;
                    $variants = '';
                    $product_variants = ((isset($childProducts->variants) && $childProducts->variants != '') ? json_decode($childProducts->variants) : '');
                    if (isset($product_variants) && $product_variants != '') {
                        foreach ($product_variants as $pk => $single_variant) {
                            foreach ($single_variant as $key => $val) {
                                $variants .= $key . ': ' . $val . ', ';
                            }
                        }
                        $variants = rtrim($variants, ", ");

                        $variants = '  (' . $variants . ')';
                    } ?>
                    <div class="table-responsive">
                        <h2><?php echo $childProducts->name . $variants . " X " . $childProducts->default_qty ?></h2>
                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                            <thead>
                                <tr class="active">
                                    <th>Cover Price </th>
                                    <th>Issues</th>
                                    <th>Gift</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo ($childProducts->webshop_price) ? $childProducts->webshop_price : ''; ?></td>
                                    <td><?php echo ($childProducts->sub_issues) ? $childProducts->sub_issues . '  Issues' : ''; ?></td>
                                    <td><?php echo ($childProducts->gift_master_name) ? $childProducts->gift_master_name : ''; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            <?php } ?>

            <input type='hidden' id="bundle-child-ids-merge" value="<?php echo implode(',', $bundle_child_id) ?>">
            <input type='hidden' id="bundle-child-ids" value="<?php echo implode(',', $product_id_bundle_child) ?>">

        <?php } ?>
        <!-- bundle end -->

        <?php if ($ProductData->product_type == "configurable") { ?>
            <input type="hidden" name="product_id" id="product_id" value="<?php echo $ProductData->id; ?>">
            <input type="hidden" name="conf_simple_pid" id="conf_simple_pid" value="">
            <input type="hidden" name="conf_simple_price" id="conf_simple_price" value="">
            <input type="hidden" name="conf_simple_qty" id="conf_simple_qty" value="">
            <input type="hidden" name="selected_variant_count" id="selected_variant_count" value="">
            <input type="hidden" name="quantity" id="quantity" value="1">
        <?php } else { ?>
            <input type="hidden" name="product_id" id="product_id" value="<?php echo $ProductData->id; ?>">
            <input type="hidden" name="quantity" id="quantity" value="1">
        <?php } ?>

    <?php } ?>
    <div class="error " id="addtocart_error"></div>
<?php } ?>