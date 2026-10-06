<?php

$allNavgte = array_column($navCatData, 'slug');

$currNav  = $this->uri->segment(2);
$currNav1 = $this->uri->segment(3);
$currNav2 = $this->uri->segment(4);

$lang = $this->session->userdata('site_lang');

?>

<ul class="list-group margin-bottom-25 sidebar-menu">

    <?php foreach ($navCatData as $main_cat) {

        $mainName = ($lang === 'french' && !empty($main_cat->lang_title))
            ? $main_cat->lang_title
            : $main_cat->menu_name;

        $hasLevel1 = !empty($main_cat->menu_level_1);

        $mainActive = ($currNav == $main_cat->slug);

    ?>

        <li class="list-group-item clearfix
            <?= $mainActive ? 'active' : ''; ?>
            <?= $hasLevel1 ? 'has-children' : ''; ?>">

            <!-- MAIN CATEGORY -->
            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>">
                <i class="fa fa-angle-right"></i>
                <?= $mainName; ?>
            </a>


            <?php if ($hasLevel1) { ?>

                <ul class="dropdown-menu">

                    <?php foreach ($main_cat->menu_level_1 as $cat_level1) {

                        $level1Name = ($lang === 'french' && !empty($cat_level1->lang_title))
                            ? $cat_level1->lang_title
                            : $cat_level1->menu_name;

                        $hasLevel2 = !empty($cat_level1->menu_level_2);

                        $level1Active = (
                            $currNav == $main_cat->slug &&
                            $currNav1 == $cat_level1->slug
                        );

                    ?>

                        <li class="list-group-item clearfix
                            <?= $level1Active ? 'active' : ''; ?>
                            <?= $hasLevel2 ? 'has-children' : ''; ?>">

                            <!-- LEVEL 1 CATEGORY -->
                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>">
                                <i class="fa fa-angle-right"></i>
                                <?= $level1Name; ?>
                            </a>


                            <?php if ($hasLevel2) { ?>

                                <ul class="dropdown-menu">

                                    <?php foreach ($cat_level1->menu_level_2 as $cat_level2) {

                                        $level2Name = ($lang === 'french')
                                            ? (
                                                !empty($cat_level2->lang_title)
                                                    ? $cat_level2->lang_title
                                                    : (
                                                        !empty($cat_level2->lang_cat_name)
                                                            ? $cat_level2->lang_cat_name
                                                            : $cat_level2->menu_name
                                                    )
                                            )
                                            : $cat_level2->menu_name;

                                        $level2Active = (
                                            $currNav == $main_cat->slug &&
                                            $currNav1 == $cat_level1->slug &&
                                            $currNav2 == $cat_level2->slug
                                        );

                                    ?>

                                        <li class="<?= $level2Active ? 'active' : ''; ?>">

                                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>/<?= $cat_level2->slug ?>">
                                                <i class="fa fa-angle-right"></i>
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


<style>

.sidebar-menu .dropdown-menu {
    display: none;
    position: static;
    float: none;
    width: 100%;
    margin: 0;
    padding: 0;
    border: 0;
    box-shadow: none;
}

.sidebar-menu .has-children.open > .dropdown-menu {
    display: block;
}

.sidebar-menu li.active > a,
.sidebar-menu li.active > a:hover,
.sidebar-menu li.active > a:focus {
    background-color: #ffd200 !important;
    color: #000 !important;
}

.sidebar-menu li.active > a i,
.sidebar-menu li.active > a:hover i,
.sidebar-menu li.active > a:focus i {
    color: #000 !important;
}


.sidebar-menu li.active:hover > a {
    background-color: #ffd200 !important;
    color: #000 !important;
}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.sidebar-menu .has-children').forEach(function (item) {

        if (item.classList.contains('active')) {
            item.classList.add('open');
        }

        const activeChild = item.querySelector('.dropdown-menu .active');

        if (activeChild) {
            item.classList.add('open');

            const parent = activeChild.closest('.has-children');

            if (parent) {
                parent.classList.add('open');
            }
        }

    });

});
</script>