<?php
$is_french = ($this->session->userdata('site_lang') == 'french');
?>

<div class="main-inner">
    <div class="variant-common-block variant-list" id="sc_attr_list_inner">
        <h1 class="head-name"><?= lang('attributes_list') ?></h1>

        <div class="select-attributes">
            <ul>

            <?php if ($flag == 'edit_attr') { ?>

                <?php
                if (isset($AttributesList) && count($AttributesList)) {
                    foreach ($AttributesList as $attr) {
                    }
                }

                if (isset($AttributesBySeller) && count($AttributesBySeller)) {
                    foreach ($AttributesBySeller as $attr) {
                ?>

                    <li>
                        <label class="checkbox">
                            <input
                                type="checkbox"
                                data-id="<?php echo $attr['id']; ?>"
                                value="<?php echo $attr['id']; ?>"
                                class="select_attr_main"
                                name="select_attr_main[]"
                                <?php echo ($attr['is_default'] == 1) ? 'checked' : ''; ?>
                                <?php echo ($attr['is_default'] == 1) ? 'onclick="return false"' : ''; ?>
                            >

                            <?php
                            echo ($is_french && !empty($attr['lang_attr_name']))
                                ? $attr['lang_attr_name']
                                : $attr['attr_name'];
                            ?>

                            <span class="checked"></span>
                        </label>
                    </li>

                <?php
                    }
                }
                ?>

            <?php } else { ?>

                <?php
                if (isset($AttributesList) && count($AttributesList)) {
                    foreach ($AttributesList as $attr) {
                ?>

                    <li>
                        <label class="checkbox">
                            <input
                                type="checkbox"
                                value="<?php echo $attr['id']; ?>"
                                class="select_attr_main"
                                name="select_attr_main[]"
                                <?php echo ($attr['is_default'] == 1) ? 'checked' : ''; ?>
                                <?php echo ($attr['is_default'] == 1) ? 'onclick="return false"' : ''; ?>
                            >

                            <?php
                            echo ($is_french && !empty($attr['lang_attr_name']))
                                ? $attr['lang_attr_name']
                                : $attr['attr_name'];
                            ?>

                            <span class="checked"></span>
                        </label>
                    </li>

                <?php
                    }
                }
                ?>

                <?php
                if (isset($AttributesBySeller) && count($AttributesBySeller)) {
                    foreach ($AttributesBySeller as $attr) {
                ?>

                    <li>
                        <label class="checkbox">
                            <input
                                type="checkbox"
                                value="<?php echo $attr['id']; ?>"
                                class="select_attr_main"
                                name="select_attr_main[]"
                                <?php echo ($attr['is_default'] == 1) ? 'checked' : ''; ?>
                                <?php echo ($attr['is_default'] == 1) ? 'onclick="return false"' : ''; ?>
                            >

                            <?php
                            echo ($is_french && !empty($attr['lang_attr_name']))
                                ? $attr['lang_attr_name']
                                : $attr['attr_name'];
                            ?>

                            <span class="checked"></span>
                        </label>
                    </li>

                <?php
                    }
                }
                ?>

            <?php } ?>

            </ul>

            <div class="download-discard-small">

                <?php if (isset($flag) && $flag == 'add_attr') { ?>

                    <button class="white-btn" type="button" data-dismiss="modal"><?= lang('discard') ?></button>

                    <button class="download-btn" type="button" onclick="LoadExtraAttribute();">
                        <?= lang('save') ?>
                    </button>

                <?php } elseif (isset($flag) && $flag == 'edit_attr') { ?>

                    <button class="white-btn" type="button" data-dismiss="modal"><?= lang('discard') ?></button>

                    <button class="download-btn" type="button" onclick="LoadExtraAttribute();">
                        <?= lang('save') ?>
                    </button>

                <?php } elseif (isset($flag) && $flag == 'bulk-add') { ?>

                    <button class="white-btn" type="button" data-dismiss="modal"><?= lang('discard') ?></button>

                    <button class="download-btn" type="button"
                        onclick="SaveAttributeForCategory('<?php echo $CategoryDetail->id; ?>','attributes');">
                        <?= lang('save') ?>
                    </button>

                <?php } else { ?>

                    <button class="white-btn" type="button"
                        onclick="EditSubCatRow(<?php echo $CategoryDetail->id; ?>);">
                        <?= lang('discard') ?>
                    </button>

                    <button class="download-btn" type="button"
                        onclick="SaveAttributeForCategory('<?php echo $CategoryDetail->id; ?>','attributes');">
                        <?= lang('save') ?>
                    </button>

                <?php } ?>

            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    if ($('#added_attr').length) {

        var added_attr = $('#added_attr').val();

        if (added_attr != '') {

            var temp = added_attr.split(",");

            $('.select_attr_main').each(function () {

                let cur_val = $(this).val();

                if (inArray(cur_val, temp)) {
                    $(this).prop('checked', true);
                }

            });
        }
    }

});
</script>