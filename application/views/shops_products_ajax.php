<?php if (!empty($shops)): ?>

<style>

/* =========================================================
   PRODUCT HEIGHT FIX
   ========================================================= */

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


/* =========================================================
   PRODUCT IMAGE
   ========================================================= */

.category-product .box-image {
    position: relative !important;
    overflow: hidden !important;
    text-align: center !important;
}

.category-product .box-image .product-item-photo {
    display: block !important;
    position: relative !important;
}

.category-product .quick-view-btn {
    position: absolute !important;

    top: 50% !important;
    left: 50% !important;

    transform: translate(-50%, -50%) !important;

    /* Normal state */
    background: transparent !important;
    color: #fff !important;

    padding: 9px 14px !important;

    border: 1px solid #fff !important;
    border-radius: 3px !important;

    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 20px !important;

    text-decoration: none !important;

    cursor: pointer !important;

    z-index: 20 !important;

    /* Hidden normally */
    opacity: 0 !important;
    visibility: hidden !important;

    transition: all 0.2s ease !important;
}


/* =========================================================
   SHOW VIEW BUTTON WHEN IMAGE IS HOVERED
   ========================================================= */

.category-product .box-image:hover .quick-view-btn {
    opacity: 1 !important;
    visibility: visible !important;
}


/* =========================================================
   HOVER ON VIEW BUTTON
   Yellow background + black text
   ========================================================= */

.category-product .quick-view-btn:hover,
.category-product .quick-view-btn:focus {

    background: #ffd200 !important;
    color: #000 !important;

    border-color: #ffd200 !important;

    text-decoration: none !important;

    outline: none !important;
}


/* =========================================================
   IMAGE HOVER
   ========================================================= */

.category-product .box-image:hover .product-image-photo {
    opacity: 0.85 !important;
}

/* =========================================================
   EXISTING VIEW PRODUCT BUTTON
   ========================================================= */

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

    background: #ffffff !important;
    border: 1px solid #777777 !important;
    border-radius: 3px !important;

    color: #555555 !important;
    font-size: 16px !important;
    font-weight: 400 !important;
    line-height: 20px !important;

    text-align: center !important;
    text-decoration: none !important;

    box-sizing: border-box !important;
    cursor: pointer !important;

    transition: all 0.2s ease-in-out !important;
}


/* EXISTING BUTTON HOVER */

.category-product .view-product-btn:hover,
.category-product .view-product-btn:focus {
    background: #1261d5 !important;
    border-color: #1261d5 !important;

    color: #ffffff !important;
    text-decoration: none !important;

    outline: none !important;
}


/* =========================================================
   OVERFLOW FIX
   ========================================================= */

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


                        <!-- =================================================
                             PRODUCT IMAGE
                             ================================================= -->

                        <div class="box-image">

                            <a href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                               class="product photo product-item-photo">

                                <span class="product-image-container"
                                      style="width: 240px;">

                                    <span class="product-image-wrapper"
                                          style="padding-bottom: 100%;">

                                        <img
                                            class="product-image-photo"
                                            src="<?php echo BASE_URL . 'uploads/products/thumb/' . $product->base_image; ?>"
                                            alt="<?php echo htmlspecialchars(
                                                $product->name,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                        >

                                    </span>

                                </span>

                            </a>


                            <!-- =================================================
                                 YELLOW VIEW BUTTON ON IMAGE HOVER
                                 ================================================= -->

                            <a
                                href="javascript:void(0);"
                                class="quick-view-btn"
                                onclick="QuickViewProdDetails(
                                    '<?php echo htmlspecialchars(
                                        $product->url_key,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>',
                                    '<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>'
                                );"
                            >
                                <?php echo $this->lang->line('view_label'); ?>
                            </a>


                        </div>
                        <!-- END PRODUCT IMAGE -->


                        <!-- =================================================
                             PRODUCT DETAILS
                             ================================================= -->

                        <div class="product details product-item-details box-info">


                            <!-- PRODUCT NAME -->

                            <h2 class="product name product-item-name product-name">

                                <a
                                    class="product-item-link"
                                    href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                >

                                    <?php

                                    $current_lang =
                                        $this->session->userdata('site_lang');

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


                            <!-- =================================================
                                 PRICE
                                 ================================================= -->

                            <div class="price-box price-final_price">

                                <?php

                                if ($product->product_type == 'configurable') {

                                    $finalPrice =
                                        !empty(
                                            $product->price_sorting_configurable
                                        )
                                        ? $product->price_sorting_configurable
                                        : $product->webshop_price;

                                } else {

                                    $finalPrice =
                                        !empty(
                                            $product->price_sorting_simple
                                        )
                                        ? $product->price_sorting_simple
                                        : $product->webshop_price;

                                }


                                $specialPrice =
                                    !empty($product->special_price)
                                    ? $product->special_price
                                    : 0;


                                if ($specialPrice > 0):

                                ?>


                                    <!-- SPECIAL PRICE -->

                                    <span class="special-price">

                                        <span class="price">

                                            MUR
                                            <?php echo number_format(
                                                $specialPrice,
                                                2
                                            ); ?>

                                        </span>

                                    </span>


                                    <!-- OLD PRICE -->

                                    <span class="old-price">

                                        <span
                                            class="price"
                                            style="text-decoration: line-through;"
                                        >

                                            MUR
                                            <?php echo number_format(
                                                $product->webshop_price,
                                                2
                                            ); ?>

                                        </span>

                                    </span>


                                <?php else: ?>


                                    <!-- REGULAR PRICE -->

                                    <span class="regular-price">

                                        <span class="price">

                                            MUR
                                            <?php echo number_format(
                                                $finalPrice,
                                                2
                                            ); ?>

                                        </span>

                                    </span>


                                <?php endif; ?>

                            </div>


                            <!-- =================================================
                                 EXISTING VIEW DETAILS BUTTON
                                 ================================================= -->

                            <div class="view-product-wrapper">

                                <a
                                    href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
                                    class="view-product-btn"
                                >

                                    <?php
                                    echo $this->lang->line('view_details');
                                    ?>

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


<!-- =========================================================
     PAGINATION
     ========================================================= -->

<div class="pagination-wrap">

    <?php echo $pagination; ?>

</div>


<?php else: ?>


    <p>

        <?php
        echo $this->lang->line('no_products_shop');
        ?>

    </p>


<?php endif; ?>