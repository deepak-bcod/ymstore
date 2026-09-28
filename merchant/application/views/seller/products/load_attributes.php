<?php
$fbc_user_id = $this->session->userdata('LoginID');
$is_french = ($this->session->userdata('site_lang') == 'french');

if (isset($AttributesList) && count($AttributesList) > 0) {
    foreach ($AttributesList as $attr) {

        $attr_properties = $attr['attr_properties'];

        if ($attr['is_default'] == 1) {
            continue;
        }

        $attribute_name = ($is_french && !empty($attr['lang_attr_name']))
            ? $attr['lang_attr_name']
            : $attr['attr_name'];
?>
<tr id="attr_row_<?php echo $attr['id']; ?>" class="at-row">

    <td><?php echo $attribute_name; ?></td>

    <td>

    <?php
    if ($attr_properties == 1 || $attr_properties == 3) {

        $extra_class = ($attr_properties == 3) ? 'sis-datepicker' : '';
    ?>

        <input
            type="text"
            name="attributes[<?php echo $attr['id']; ?>]"
            <?php echo ($attr_properties == 3) ? 'readonly' : ''; ?>
            class="form-control required-field <?php echo $extra_class; ?>"
            id="attr_tf_<?php echo $attr['id']; ?>"
            value=""
            placeholder="<?php echo $is_french ? 'Entrer une valeur' : 'Enter value'; ?>">

    <?php
    }
    else if ($attr_properties == 2) {
    ?>

        <textarea
            name="attributes[<?php echo $attr['id']; ?>]"
            class="form-control required-field"
            id="attr_tf_<?php echo $attr['id']; ?>"
            rows="5"
            cols="3"
            placeholder="<?php echo $is_french ? 'Entrer une valeur' : 'Enter value'; ?>"></textarea>

    <?php
    }
    else if ($attr_properties == 4) {
    ?>

        <div class="radio">
            <label>
                <input type="radio"
                       name="attributes[<?php echo $attr['id']; ?>]"
                       checked
                       value="Yes">
                <?php echo $is_french ? 'Oui' : 'Yes'; ?>
                <span class="checkmark"></span>
            </label>
        </div>

        <div class="radio">
            <label>
                <input type="radio"
                       name="attributes[<?php echo $attr['id']; ?>]"
                       value="No">
                <?php echo $is_french ? 'Non' : 'No'; ?>
                <span class="checkmark"></span>
            </label>
        </div>

    <?php
    }
    else if ($attr_properties == 5) {

        $OptionList = $this->EavAttributesModel->get_attributes_options_by_seller($attr['id']);
    ?>

        <select class="form-control required-field"
        name="attributes[<?php echo $attr['id']; ?>]">

    <option value="">
        <?php echo $is_french ? 'Sélectionner' : 'Select'; ?>
    </option>

    <?php
    if (!empty($OptionList)) {
        foreach ($OptionList as $option) {
    ?>

        <option value="<?php echo $option['id']; ?>">
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
    else if ($attr_properties == 6) {

        $OptionList = $this->EavAttributesModel->get_attributes_options_by_seller($attr['id']);
    ?>

        <select
    class="form-control required-field multiple-selection"
    name="attributes[<?php echo $attr['id']; ?>][]"
    multiple>

    <option value="">
        <?php echo $is_french ? 'Sélectionner' : 'Select'; ?>
    </option>

    <?php
    if (!empty($OptionList)) {
        foreach ($OptionList as $option) {
    ?>

        <option value="<?php echo $option['id']; ?>">
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

    <td>
        <a class="link-red"
           href="javascript:void(0);"
           onclick="removeAttrRow(<?php echo $attr['id']; ?>)">
            <?php echo $is_french ? 'Supprimer' : 'Delete'; ?>
        </a>
    </td>

</tr>

<?php
    }
}
?>

<script>

$(document).ready(function(){

    $(".sis-datepicker").datepicker({
        autoclose:true,
        todayHighlight:true,
        format:'dd-mm-yyyy'
    });

    $('.required-field').each(function () {

        $(this).rules("add",{
            required:true,
            messages:{
                required:"<?= $is_french ? 'Ce champ est obligatoire' : 'Field is required'; ?>"
            }
        });

    });

});

</script>