
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

        /*
         * Main category is active when:
         * /category/main-slug
         * OR any sub/child category belongs to this main category
         */
        $mainActive = ($currNav == $main_cat->slug);

    ?>

        <li class="list-group-item clearfix
            <?= $mainActive ? 'active' : ''; ?>
            <?= $hasLevel1 ? 'has-children' : ''; ?>">

            <div class="category-link-wrapper">

                <!-- MAIN CATEGORY LINK -->
                <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>">
                    <i class="fa fa-angle-right"></i>
                    <?= $mainName; ?>
                </a>

                <?php if ($hasLevel1) { ?>

                    <!-- SEPARATE EXPAND/COLLAPSE BUTTON -->
                    <button type="button"
                            class="category-toggle"
                            aria-label="Toggle <?= htmlspecialchars($mainName); ?>">
                        <i class="fa fa-chevron-down"></i>
                    </button>

                <?php } ?>

            </div>


            <?php if ($hasLevel1) { ?>

                <ul class="dropdown-menu">

                    <?php foreach ($main_cat->menu_level_1 as $cat_level1) {

                        $level1Name = ($lang === 'french' && !empty($cat_level1->lang_title))
                            ? $cat_level1->lang_title
                            : $cat_level1->menu_name;

                        $hasLevel2 = !empty($cat_level1->menu_level_2);

                        /*
                         * Sub-category is active when:
                         * /category/main/sub
                         * OR when one of its children is selected
                         */
                        $level1Active = (
                            $currNav == $main_cat->slug &&
                            $currNav1 == $cat_level1->slug
                        );

                    ?>

                        <li class="list-group-item clearfix
                            <?= $level1Active ? 'active' : ''; ?>
                            <?= $hasLevel2 ? 'has-children' : ''; ?>">

                            <div class="category-link-wrapper">

                                <!-- SUB CATEGORY LINK -->
                                <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>">
                                    <i class="fa fa-angle-right"></i>
                                    <?= $level1Name; ?>
                                </a>

                                <?php if ($hasLevel2) { ?>

                                    <!-- SEPARATE EXPAND/COLLAPSE BUTTON -->
                                    <button type="button"
                                            class="category-toggle"
                                            aria-label="Toggle <?= htmlspecialchars($level1Name); ?>">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>

                                <?php } ?>

                            </div>


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

                                            <!-- CHILD CATEGORY LINK -->
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
.sidebar-menu .category-link-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sidebar-menu .category-link-wrapper > a {
    flex: 1;
    text-decoration: none;
}

.sidebar-menu .category-toggle {
    border: 0;
    background: transparent;
    padding: 5px 8px;
    cursor: pointer;
    color: inherit;
}

.sidebar-menu .category-toggle i {
    transition: transform 0.2s ease;
}

/* Hide child menus by default */
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

/* Show menu when opened */
.sidebar-menu .has-children.open > .dropdown-menu {
    display: block;
}

/* Rotate arrow */
.sidebar-menu .has-children.open > .category-link-wrapper .category-toggle i {
    transform: rotate(180deg);
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.sidebar-menu .category-toggle').forEach(function (toggle) {

        toggle.addEventListener('click', function (e) {

            // Prevent the toggle click from affecting the category link
            e.preventDefault();
            e.stopPropagation();

            const parent = this.closest('.has-children');

            if (!parent) {
                return;
            }

            parent.classList.toggle('open');

        });

    });

});
</script>

