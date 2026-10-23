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

/* PRODUCT IMAGE */
.product-image {
    position: relative;
    overflow: hidden;
    text-align: center;
    width: 100%;
    background: #fff;
}

/* IMAGE */
.product-image .product-image-photo {
    width: 100%;
    height: auto;
    display: block;
}

/* DARK GREY OVERLAY */
.product-image::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0.2s ease;
    z-index: 5;
    pointer-events: none;
}

/* SHOW OVERLAY ON IMAGE HOVER */
.product-image:hover::after {
    opacity: 1;
    visibility: visible;
}

/* SHOW OVERLAY WHEN VIEW DETAILS IS HOVERED */
.product-item:has(.view-product-btn:hover) .product-image::after {
    opacity: 1;
    visibility: visible;
}

/* QUICK VIEW BUTTON */
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
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

/* SHOW QUICK VIEW ON IMAGE HOVER */
.product-image:hover .quick-view-btn {
    opacity: 1;
    visibility: visible;
}

/* QUICK VIEW HOVER */
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
    flex-direction: row;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.old-price {
    order: 1;
    color: #999;
    font-size: 14px;
    white-space: nowrap;
}

.special-price {
    order: 2;
    color: #ff7a00;
    font-size: 17px;
    font-weight: 500;
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
    font-weight: 700;
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
    color: #333 !important;
    border: 1px solid #999;
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
    border-color: #0f5cd0;
}
/* SHOW GREY OVERLAY WHEN VIEW DETAILS IS HOVERED */
.product-item:has(.view-product-btn:hover) .product-image::after {
    opacity: 1;
    visibility: visible;
}

/* SHOW QUICK VIEW WHEN VIEW DETAILS IS HOVERED */
.product-item:has(.view-product-btn:hover) .product-image .quick-view-btn {
    opacity: 1;
    visibility: visible;
}

/* QUICK VIEW BUTTON HOVER */
.product-image .quick-view-btn:hover {
    background: #ffd200 !important;
    color: #000 !important;
    border-color: #ffd200 !important;
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


                            <?php foreach ($products as $p): 
                                if (!empty($p->daily_deal_ends_at) && (int)$p->daily_deal_ends_at <= time()) {
                                    continue; // Expired, remove from Daily Deals section
                                }
                            ?>


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

                                            <?php
                                            $is_out_of_stock = false;
                                            $product_stock = null;
                                            if (isset($p->qty) && is_numeric($p->qty)) {
                                                $product_stock = (float)$p->qty;
                                            } elseif (isset($p->stock_qty) && is_numeric($p->stock_qty)) {
                                                $product_stock = (float)$p->stock_qty;
                                            } elseif (isset($p->total_qty) && is_numeric($p->total_qty)) {
                                                $product_stock = (float)$p->total_qty;
                                            } elseif (isset($p->quantity) && is_numeric($p->quantity)) {
                                                $product_stock = (float)$p->quantity;
                                            }

                                            if ($product_stock !== null && $product_stock <= 0) {
                                                $is_out_of_stock = true;
                                            } elseif (isset($p->stock_status) && (strtolower(trim($p->stock_status)) == 'out of stock' || strtolower(trim($p->stock_status)) == 'outofstock')) {
                                                $is_out_of_stock = true;
                                            }

                                            $regular_price = !empty($p->webshop_price) ? (float)$p->webshop_price : 0;
                                            $special_price = !empty($p->special_price) ? (float)$p->special_price : 0;
                                            $discount_percent = 0;
                                            if ($special_price > 0 && $regular_price > $special_price) {
                                                $discount_percent = (int)round((($regular_price - $special_price) / $regular_price) * 100);
                                            }
                                            $is_deal_active = (!empty($p->daily_deals) && $p->daily_deals == 1 && (empty($p->daily_deal_ends_at) || (int)$p->daily_deal_ends_at > time()));
                                            $is_flash_active = (!empty($p->flash_sale) && $p->flash_sale == 1 && (empty($p->flash_sale_ends_at) || (int)$p->flash_sale_ends_at > time()));
                                            ?>

                                            <!-- Badges inside product image at TOP (Daily Deals, Flash Sales, Discount Pill, Out of Stock) -->
                                            <span class="pi-img-top-badges" style="position: absolute !important; top: 6px !important; left: 6px !important; right: 6px !important; display: flex !important; align-items: flex-start !important; justify-content: space-between !important; gap: 4px !important; z-index: 8 !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; width: calc(100% - 12px) !important; background: transparent !important;">
                                                <span style="display: flex !important; align-items: center !important; gap: 4px !important; flex-wrap: wrap !important;">
                                                    <?php if ($is_out_of_stock): ?>
                                                        <span class="badge-out-of-stock" style="background-color: #dc3545 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">OUT OF STOCK</span>
                                                    <?php else: ?>
                                                        <?php if ($is_flash_active): ?>
                                                            <span class="badge-deal-flash" style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;"><i class="fa fa-bolt" style="color: #ffd200 !important; font-size: 11px !important;"></i> FLASH SALE</span>
                                                        <?php endif; ?>
                                                        <?php if ($is_deal_active): ?>
                                                            <span class="badge-deal-daily" style="background-color: #ffd200 !important; color: #000000 !important; font-size: 10px !important; font-weight: 800 !important; padding: 2.5px 7px !important; border-radius: 3px !important; text-transform: uppercase !important; letter-spacing: 0.3px !important; line-height: 1.25 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;"><i class="fa fa-star" style="color: #000000 !important; font-size: 10px !important;"></i> DAILY DEAL</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </span>

                                                <?php if ($discount_percent > 0 && !$is_out_of_stock): ?>
                                                    <span style="margin-left: auto !important; display: inline-block !important;">
                                                        <span class="badge-discount-pill" style="background-color: #ff0000 !important; color: #ffffff !important; font-size: 11px !important; font-weight: 800 !important; padding: 2px 7px !important; border-radius: 12px !important; line-height: 1.2 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important; white-space: nowrap !important; display: inline-block !important;">-<?= $discount_percent ?>%</span>
                                                    </span>
                                                <?php endif; ?>
                                            </span>

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