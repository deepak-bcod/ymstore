<?php
$is_french = ($this->session->userdata('site_lang') == 'french');

$selected_attributes = array();

if(isset($side_menu) && $side_menu=='product_view'){
    $shop_id = $shop_id;
}else{
    $shop_id = $this->session->userdata('ShopID');
}

if(isset($AttributesList) && count($AttributesList)>0){

foreach($AttributesList as $attr){

    $AttrInfo = $this->CommonModel->getSingleDataByID(
        'eav_attributes',
        array('id'=>$attr->attr_id),
        'id,attr_name,lang_attr_name,attr_properties'
    );

    if(empty($AttrInfo->id)){
        continue;
    }

    $attr_properties = $AttrInfo->attr_properties;

    $attr_name = ($is_french && !empty($AttrInfo->lang_attr_name))
        ? trim($AttrInfo->lang_attr_name)
        : trim($AttrInfo->attr_name);

    $attr_value = $attr->attr_value;
    $attr_id = $attr->attr_id;

    $selected_attributes[] = $attr_id;
?>

<tr id="attr_row_<?php echo $attr_id; ?>" class="at-row">

    <td><?php echo $attr_name; ?></td>

    <td>

    <?php
    if($attr_properties==1 || $attr_properties==3){

        $extra_class='';

        if($attr_properties==3){
            $extra_class='sis-datepicker';
        }
    ?>

        <input
            type="text"
            name="attributes[<?php echo $attr_id; ?>]"
            value="<?php echo $attr_value; ?>"
            <?php echo ($attr_properties==3)?'readonly':''; ?>
            class="form-control required-field <?php echo $extra_class; ?>"
            placeholder="<?php echo $is_french ? 'Saisir une valeur' : 'Enter value'; ?>">

    <?php
    }
    else if($attr_properties==2){
    ?>

        <textarea
            name="attributes[<?php echo $attr_id; ?>]"
            class="form-control"
            rows="5"
            placeholder="<?php echo $is_french ? 'Saisir une valeur' : 'Enter value'; ?>"><?php echo $attr_value; ?></textarea>

    <?php
    }
    else if($attr_properties==4){
    ?>

        <div class="radio">
            <label>
                <input
                    type="radio"
                    name="attributes[<?php echo $attr_id; ?>]"
                    value="Yes"
                    <?php echo ($attr_value=='Yes')?'checked':''; ?>>

                <?php echo $is_french ? 'Oui' : 'Yes'; ?>

                <span class="checkmark"></span>
            </label>
        </div>

        <div class="radio">
            <label>
                <input
                    type="radio"
                    name="attributes[<?php echo $attr_id; ?>]"
                    value="No"
                    <?php echo ($attr_value=='No')?'checked':''; ?>>

                <?php echo $is_french ? 'Non' : 'No'; ?>

                <span class="checkmark"></span>
            </label>
        </div>

    <?php
    }
    else if($attr_properties==5){

        $OptionList = $this->EavAttributesModel->get_attributes_options_by_seller($attr_id);
    ?>

        <select class="form-control required-field"
                name="attributes[<?php echo $attr_id; ?>]">

            <option value="">
                <?php echo $is_french ? 'Sélectionner' : 'Select'; ?>
            </option>

            <?php
            if(!empty($OptionList)){
                foreach($OptionList as $option){
            ?>

                <option
                    value="<?php echo $option['id']; ?>"
                    <?php echo ($attr_value==$option['id'])?'selected':''; ?>>

                    <?php
                    echo ($is_french && !empty($option['lang_attr_options_name']))
                        ? $option['lang_attr_options_name']
                        : $option['attr_options_name'];
                    ?>

                </option>

            <?php
                }
            }
            ?>

        </select>

    <?php
    }
    else if($attr_properties==6){

        $OptionList = $this->EavAttributesModel->get_attributes_options_by_seller($attr_id);

        $selected_values = explode(',',$attr_value);
    ?>

        <select
            class="form-control required-field multiple-selection"
            name="attributes[<?php echo $attr_id; ?>][]"
            multiple>

            <option value="">
                <?php echo $is_french ? 'Sélectionner' : 'Select'; ?>
            </option>

            <?php
            if(!empty($OptionList)){
                foreach($OptionList as $option){
            ?>

                <option
                    value="<?php echo $option['id']; ?>"
                    <?php echo in_array($option['id'],$selected_values)?'selected':''; ?>>

                    <?php
                    echo ($is_french && !empty($option['lang_attr_options_name']))
                        ? $option['lang_attr_options_name']
                        : $option['attr_options_name'];
                    ?>

                </option>

            <?php
                }
            }
            ?>

        </select>

    <?php } ?>

    </td>

    <td class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>">

        <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>

            <a class="link-red"
               href="javascript:void(0);"
               onclick="removeAttrRow(<?php echo $attr_id; ?>)">

                <?php echo $is_french ? 'Supprimer' : 'Delete'; ?>

            </a>

        <?php } ?>

    </td>

</tr>

<?php
}
}
?>