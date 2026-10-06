
<?php

$allNavgte = array_column($navCatData, 'slug');

$currNav  = $this->uri->segment(2); // Main category
$currNav1 = $this->uri->segment(3); // Sub category
$currNav2 = $this->uri->segment(4); // Child category

$lang = $this->session->userdata('site_lang');

?>

<ul class="list-group margin-bottom-25 sidebar-menu">

    <?php foreach ($navCatData as $main_cat) {

        // Main category name
        $mainName = ($lang === 'french' && !empty($main_cat->lang_title))
            ? $main_cat->lang_title
            : $main_cat->menu_name;

        // Main category active
        $mainActive = ($currNav == $main_cat->slug);

    ?>

        <li class="list-group-item clearfix
            <?= $mainActive ? 'active' : ''; ?>
            <?= isset($main_cat->menu_level_1) ? 'dropdown' : ''; ?>">

            <!-- MAIN CATEGORY -->
            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>">
                <i class="fa fa-angle-right"></i>
                <?= $mainName; ?>
            </a>

            <?php if (isset($main_cat->menu_level_1)) { ?>

                <ul class="dropdown-menu">

                    <?php foreach ($main_cat->menu_level_1 as $cat_level1) {

                        // Sub category name
                        $level1Name = ($lang === 'french' && !empty($cat_level1->lang_title))
                            ? $cat_level1->lang_title
                            : $cat_level1->menu_name;

                        // Sub category active
                        $level1Active = ($currNav1 == $cat_level1->slug);

                    ?>

                        <li class="list-group-item clearfix
                            <?= $level1Active ? 'active' : ''; ?>
                            <?= isset($cat_level1->menu_level_2) ? 'dropdown' : ''; ?>">

                            <!-- SUB CATEGORY -->
                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>">
                                <i class="fa fa-angle-right"></i>
                                <?= $level1Name; ?>
                            </a>

                            <?php if (isset($cat_level1->menu_level_2)) { ?>

                                <ul class="dropdown-menu">

                                    <?php foreach ($cat_level1->menu_level_2 as $cat_level2) {

                                        // Child category name
                                        if ($lang === 'french') {

                                            if (!empty($cat_level2->lang_title)) {

                                                $level2Name = $cat_level2->lang_title;

                                            } elseif (!empty($cat_level2->lang_cat_name)) {

                                                $level2Name = $cat_level2->lang_cat_name;

                                            } else {

                                                $level2Name = $cat_level2->menu_name;
                                            }

                                        } else {

                                            $level2Name = $cat_level2->menu_name;
                                        }

                                        // Child category active
                                        $level2Active = ($currNav2 == $cat_level2->slug);

                                    ?>

                                        <li class="<?= $level2Active ? 'active' : ''; ?>">

                                            <!-- CHILD CATEGORY -->
                                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>/<?= $cat_level2->slug ?>">
                                                <?= $level2Name; ?>
                                            </a>

                                        </li>

                                    <?php } ?>

                                </ul>

                            <?php } ?>

                        </li>

                    <?php } ?>

                </ul>

            <?php } ?>

        </li>

    <?php } ?>

</ul>

