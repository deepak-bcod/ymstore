<?php if (!empty($shops)): ?>

<style>
    /* Product grid */
    .category-product .product-item {
        height: auto !important;
        min-height: 0 !important;
    }

    .category-product .product-item-info {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
    }

    .category-product .item-inner {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
    }

    .category-product .box-info {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        padding-bottom: 15px !important;
    }

    .category-product .product-item-details {
        height: auto !important;
        overflow: visible !important;
    }

    /* View Product button */
    .category-product .view-product-wrapper {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        margin-top: 15px !important;
        margin-bottom: 15px !important;
        padding: 0 !important;
        text-align: center !important;
        clear: both !important;
        overflow: visible !important;
    }

    .category-product .view-product-btn {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100% !important;
        height: auto !important;
        min-height: 40px !important;
        padding: 10px 15px !important;
        margin: 0 !important;

        background: #ff8c00 !important;
        border: 1px solid #ff8c00 !important;
        border-radius: 3px !important;

        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 20px !important;
        text-align: center !important;
        text-decoration: none !important;

        box-sizing: border-box !important;
        cursor: pointer !important;
    }

    .category-product .view-product-btn:hover,
    .category-product .view-product-btn:focus {
        background: #e67e00 !important;
        border-color: #e67e00 !important;
        color: #ffffff !important;
        text-decoration: none !important;
    }

    /* Make sure product content is not clipped */
    .category-product .product-item,
    .category-product .product-item-info,
    .category-product .item-inner,
    .category-product .box-info {
        overflow: visible !important;
    }
</style>


<div class="category-product products wrapper grid products-grid">

    <ol class="products list items product-items row">

        <?php foreach ($shops as $product): ?>

            <li class="item product product-item col-md-3">

                <div class="product-item-info" data-container="product-grid">

                    <div class="item-inner">

                        <!-- =========================
                             PRODUCT IMAGE
                        ========================== -->
                        <div class="box-image">

                            <a href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                               class="product photo product-item-photo">

                                <span class="product-image-container" style="width: 240px;">

                                    <span class="product-image-wrapper"
                                          style="padding-bottom: 100%;">

                                        <img
                                            class="product-image-photo"
                                            src="<?php echo BASE_URL . 'uploads/products/thumb/' . $product->base_image; ?>"
                                            alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                    </span>

                                </span>

                            </a>

                        </div>


                        <!-- =========================
                             PRODUCT DETAILS
                        ========================== -->
                        <div class="product details product-item-details box-info">

                            <!-- PRODUCT NAME -->
                            <h2 class="product name product-item-name product-name">

                                <a class="product-item-link"
                                   href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>">

                                    <?php

                                    $current_lang = $this->session->userdata('site_lang');

                                    if (
                                        $current_lang == 'french'
                                        && !empty($product->lang_title)
                                    ) {
                                        echo htmlspecialchars(
                                            $product->lang_title,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    } else {
                                        echo htmlspecialchars(
                                            $product->name,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    }

                                    ?>

                                </a>

                            </h2>


                            <!-- =========================
                                 PRICE
                            ========================== -->
                            <div class="price-box price-final_price">

                                <?php

                                /*
                                 * Get product price
                                 */
                                if ($product->product_type == 'configurable') {

                                    $finalPrice = !empty($product->price_sorting_configurable)
                                        ? $product->price_sorting_configurable
                                        : $product->webshop_price;

                                } else {

                                    $finalPrice = !empty($product->price_sorting_simple)
                                        ? $product->price_sorting_simple
                                        : $product->webshop_price;
                                }


                                /*
                                 * Special price
                                 */
                                $specialPrice = !empty($product->special_price)
                                    ? $product->special_price
                                    : 0;


                                /*
                                 * Display special price
                                 */
                                if ($specialPrice > 0):

                                ?>

                                    <span class="special-price">

                                        <span class="price">
                                            MUR <?php echo number_format($specialPrice, 2); ?>
                                        </span>

                                    </span>


                                    <span class="old-price">

                                        <span class="price"
                                              style="text-decoration: line-through;">

                                            MUR <?php echo number_format($product->webshop_price, 2); ?>

                                        </span>

                                    </span>


                                <?php else: ?>


                                    <!-- REGULAR PRICE -->
                                    <span class="regular-price">

                                        <span class="price">
                                            MUR <?php echo number_format($finalPrice, 2); ?>
                                        </span>

                                    </span>


                                <?php endif; ?>

                            </div>


                            <!-- =========================
                                 VIEW PRODUCT BUTTON
                            ========================== -->
                            <div class="view-product-wrapper">

                                <a
                                    href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                    class="view-product-btn"
                                >
                                    View Product
                                </a>

                            </div>


                        </div>
                        <!-- END PRODUCT DETAILS -->

                    </div>
                    <!-- END ITEM INNER -->

                </div>
                <!-- END PRODUCT ITEM INFO -->

            </li>

        <?php endforeach; ?>

    </ol>

</div>


<!-- =========================
     PAGINATION
========================== -->
<div class="pagination-wrap">

    <?php echo $pagination; ?>

</div>


<?php else: ?>

    <p>
        <?php echo $this->lang->line('no_products_shop'); ?>
    </p>

<?php endif; ?>