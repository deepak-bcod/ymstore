<style>
.category-tree .toggle {
    display: inline-block;
    min-width: 18px;
    text-align: center;
    border: none;
    border-radius: 3px;
    line-height: 16px;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    margin-right: 6px;
    position: relative;
    z-index: 2;
    color: #444d5c;
}

.product-item {
    background: #fff;
    position: relative;
}

.product-image {
    position: relative;
    overflow: hidden;
    text-align: center;
    width: 100%;
    background: #fff;
}


.product-image .product-image-photo {
    width: 100%;
    height: auto;
    display: block;
}


.product-image::after {
    content: "";

    position: absolute;

    top: 0;
    left: 0;
    right: 0;
    bottom: 0;

    /* Transparent dark overlay */
    background: rgba(0, 0, 0, 0.35);

    opacity: 0;
    visibility: hidden;

    transition:
        opacity 0.2s ease,
        visibility 0.2s ease;

    z-index: 5;

    pointer-events: none;
}

.product-image:hover::after {
    opacity: 1;
    visibility: visible;
}


.product-image .quick-view-btn {
    position: absolute;

    top: 50%;
    left: 50%;

    transform: translate(-50%, -50%);

    color: #fff;

    background: rgba(0, 0, 0, 0.35);

    padding: 9px 14px;

    border: 1px solid #fff;
    border-radius: 3px;

    font-size: 14px;
    font-weight: 500;

    text-decoration: none;
    cursor: pointer;

    z-index: 10;

    opacity: 0;
    visibility: hidden;

    transition:
        opacity 0.2s ease,
        visibility 0.2s ease,
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}

.product-image:hover .quick-view-btn {
    opacity: 1;
    visibility: visible;
}

.product-image .quick-view-btn:hover {
    background: #ffd200;
    color: #000;
    border-color: #ffd200;
}

.product-name {
    font-size: 14px;
    font-weight: 600;
    margin: 10px 0 6px;
}

.price-box {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.special-price {
    color: #ff7a00;
    font-size: 17px;
    font-weight: 500;
    margin-right: 0;
    white-space: nowrap;
}

.old-price {
    color: #999;
    font-size: 14px;
    white-space: nowrap;
}

.regular-price {
    color: #ff7a00;
    font-size: 17px;
    font-weight: 500;
    white-space: nowrap;
}

.deal-ends {
    font-size: 13px;
    margin-bottom: 10px;
}

.view-product-wrapper {
    margin-top: 12px;
    text-align: center;
}

.view-product-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    background: #fff;
    color: #0f5cd0 !important;
    border: 1px solid #0f5cd0;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
    text-align: center;
    text-decoration: none !important;
    cursor: pointer;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.view-product-btn:hover {
    background: #0f5cd0;
    color: #fff !important;
    border-color: #0d1218;
}
</style>

<?php $this->load->view('common/header'); ?>

<div class="daily-deal-intro">

    <h1>
        <?php echo $this->lang->line('daily_watch_space'); ?>
    </h1>

    <table class="daily-deal-table">

        <tbody>

            <tr>

                <td class="deal-img">

                    <img
                        src="<?php echo base_url('public/images/daily-deals-417x306.jpg'); ?>"
                        alt="24 Hours Daily Deal"
                    >

                </td>

                <td class="deal-text">

                    <p>
                        <?php echo $this->lang->line('daily_para1'); ?>
                    </p>

                    <p>
                        <?php echo $this->lang->line('daily_para2'); ?>
                    </p>

                    <p>
                        <span class="important">
                            <strong>
                                <?php echo $this->lang->line('daily_important'); ?>
                            </strong>
                        </span>

                        <?php echo $this->lang->line('daily_para3'); ?>
                    </p>

                    <p>
                        <strong>
                            <?php echo $this->lang->line('daily_para4'); ?>
                        </strong>
                    </p>

                </td>

            </tr>

        </tbody>

    </table>

</div>


<?php

function buildCategoryTree($categories, $parent_id = 0)
{
    $html = '';
    $hasChild = false;

    foreach ($categories as $cat) {

        if ($cat->parent_id == $parent_id) {

            if (!$hasChild) {

                $html .= '<ul>';

                $hasChild = true;
            }


            $children = array_filter(
                $categories,
                function($c) use ($cat) {

                    return $c->parent_id == $cat->id;

                }
            );


            $hasChildren = !empty($children);


            $html .= '<li class="tree-node' .
                ($hasChildren ? ' dropdown' : '') .
                '">';


            if ($hasChildren) {

                $html .= '<span class="toggle">></span>';

            }


            $html .= '<a href="' .
                site_url('daily-deals/category/' . $cat->id) .
                '">' .
                '<i class="fa fa-angle-right"></i> ' .
                $cat->cat_name .
                '</a>';


            if ($hasChildren) {

                $html .= buildCategoryTree(
                    $categories,
                    $cat->id
                );

            }


            $html .= '</li>';

        }

    }


    if ($hasChild) {

        $html .= '</ul>';

    }


    return $html;
}

?>


<main id="maincontent" class="page-main">

    <div class="container-fluid">

        <div class="row">


            <div class="col-md-3 order-md-1">

                <div class="sidebar sidebar-main mb-4">

                    <h2>
                        <?php echo $this->lang->line('categories'); ?>
                    </h2>

                    <?php
                    (new TopMenu('categorymenu'))->render();
                    ?>

                </div>

            </div>


            <div class="col-md-9 order-md-2">

                <div class="page-title-wrapper mb-4">

                    <h2 class="page-title">
                        <?php echo $this->lang->line('daily_deals'); ?>
                    </h2>

                </div>


                <div class="category-products">

                    <div class="row">


                        <?php if (!empty($products)): ?>


                            <?php foreach ($products as $p): ?>


                                <div class="col-md-4 col-sm-6 mb-4">


                                    <div class="product-item border p-2 h-100">


                                        <div class="product-image text-center mb-2">


                                            <?php

                                            $imgPath =
                                                FCPATH .
                                                'uploads/products/thumb/' .
                                                $p->base_image;


                                            if (
                                                !empty($p->base_image) &&
                                                file_exists($imgPath)
                                            ):

                                            ?>


                                                <img
                                                    class="product-image-photo img-fluid"
                                                    src="<?php
                                                        echo base_url(
                                                            'uploads/products/thumb/' .
                                                            $p->base_image
                                                        );
                                                    ?>"
                                                    alt="<?php
                                                        echo htmlspecialchars(
                                                            $p->name,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                    ?>"
                                                >


                                            <?php else: ?>


                                                <img
                                                    class="product-image-photo img-fluid"
                                                    src="https://via.placeholder.com/300x300?text=No+Image"
                                                    alt="No Image"
                                                >


                                            <?php endif; ?>

                                            <a
                                                href="javascript:void(0);"
                                                class="quick-view-btn"
                                                onclick="QuickViewProdDetails(
                                                    '<?php
                                                    echo htmlspecialchars(
                                                        $p->url_key,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    );
                                                    ?>',
                                                    '<?php
                                                    echo site_url(
                                                        'product-detail/' .
                                                        $p->url_key
                                                    );
                                                    ?>'
                                                );"
                                            >

                                                <?php
                                                echo $this->lang->line(
                                                    'view_label'
                                                );
                                                ?>

                                            </a>


                                        </div>

                                        <div class="product-details text-center">


                                            <h3 class="product-name">

                                                <?php
                                                echo $p->name;
                                                ?>

                                            </h3>


                                            <!-- PRICE -->

                                            <div class="price-box mb-2">


                                                <?php if (!empty($p->special_price)): ?>


                                                    <span class="special-price">

                                                        MUR
                                                        <?php
                                                        echo number_format(
                                                            $p->special_price,
                                                            2
                                                        );
                                                        ?>

                                                    </span>


                                                    <span class="old-price text-muted">

                                                        <s>

                                                            MUR
                                                            <?php
                                                            echo number_format(
                                                                $p->webshop_price,
                                                                2
                                                            );
                                                            ?>

                                                        </s>

                                                    </span>


                                                <?php else: ?>


                                                    <span class="regular-price">

                                                        MUR
                                                        <?php
                                                        echo number_format(
                                                            $p->webshop_price,
                                                            2
                                                        );
                                                        ?>

                                                    </span>


                                                <?php endif; ?>


                                            </div>


                                            <!-- DEAL ENDS -->

                                            <p class="deal-ends mb-2">

                                                <?php
                                                echo $this->lang->line(
                                                    'deal_ends'
                                                );
                                                ?>

                                                <?php
                                                echo date(
                                                    "d M Y, H:i",
                                                    $p->daily_deal_ends_at
                                                );
                                                ?>

                                            </p>


                                            <!-- VIEW PRODUCT -->

                                            <!-- VIEW PRODUCT -->
<div class="view-product-wrapper">
    <a
        href="<?php echo BASE_URL . 'product-detail/' . $p->url_key; ?>"
        class="view-product-btn"
    >
        <?php
        echo $this->lang->line('view_details');
        ?>
    </a>
</div>


                                        </div>


                                    </div>


                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <div class="col-12">

                                <div class="alert alert-info">

                                    <?php
                                    echo $this->lang->line('no_deals');
                                    ?>

                                </div>

                            </div>


                        <?php endif; ?>


                    </div>

                    <div class="pagination-wrapper mt-4">

                        <?php
                        echo $pagination ?? '';
                        ?>

                    </div>


                </div>

            </div>

        </div>

    </div>

</main>

<script>

(function (w, d) {

    function boot() {

        if (!w.jQuery) {

            setTimeout(boot, 50);

            return;
        }


        var $ = w.jQuery;


        /* Collapse child categories */

        $('.category-tree .tree-node > ul').hide();


        /* Auto-open active category */

        var $active =
            $('.category-tree a.active');


        if ($active.length) {

            $active.parents('ul').show();

            $active
                .parents('li.tree-node')
                .children('.toggle')
                .text('-')
                .attr(
                    'aria-expanded',
                    true
                );

        }


        /* Category toggle */

        $(d).on(
            'click',
            '.category-tree .toggle',
            function (e) {

                e.preventDefault();

                e.stopPropagation();


                var $li =
                    $(this).closest(
                        'li.tree-node'
                    );


                var $child =
                    $li.children('ul').first();


                if (!$child.length) {

                    $child =
                        $li.find('> ul').first();

                }


                if ($child.length) {

                    var isVisible =
                        $child.is(':visible');


                    $child.slideToggle(150);


                    /*
                     * Keep your existing arrow behavior
                     */

                    $(this)
                        .text(isVisible ? '>' : '>')
                        .attr(
                            'aria-expanded',
                            !isVisible
                        );

                }

            }
        );

    }


    boot();

})(window, document);

</script>


<?php $this->load->view('common/footer'); ?>