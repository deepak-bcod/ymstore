<?php if(!empty($shops)): ?>

<div class="category-product products wrapper grid products-grid">

    <ol class="products list items product-items row">

        <?php foreach($shops as $product):
            
            
              //echo '<pre>'; print_r($shops);exit;
            ?>

            <li class="item product product-item col-md-3">

                <div class="product-item-info" data-container="product-grid">

                    <div class="item-inner">

                        <div class="box-image">

                            <!-- product image -->

                            <a href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>" 

                               class="product photo product-item-photo">

                                <!-- <span class="product-image-container" style="width: 240px;">

                                    <span class="product-image-wrapper" style="padding-bottom: 100%;">

                                        <img class="product-image-photo <?php echo BASE_URL .'uploads/products/thumb/' . $product->base_image ; ?>" src="<?php echo BASE_URL .'uploads/products/thumb/' . $product->base_image ; ?>" alt="<?php echo $product->name; ?>">

                                    </span>

                                </span> -->

                            </a>

                        </div>



                        <!-- product details -->

                        <div class="product details product-item-details box-info">

                            <h2 class="product name product-item-name product-name">
                                <a class="product-item-link" 
                                href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>">
                                    <?php 
                                        // Check current language session or setting and display lang_title if French
                                        $current_lang = $this->session->userdata('site_lang'); // Adjust according to your language session variable
                                        if ($current_lang == 'french' && !empty($product->lang_title)) {
                                            echo $product->lang_title;
                                        } else {
                                            echo $product->name;
                                        }
                                    ?>
                                </a>
                            </h2>



                            <div class="price-box price-final_price">
                                    <?php
                                    if ($product->product_type == 'configurable') {
                                        $finalPrice = !empty($product->price_sorting_configurable)
                                        ? $product->price_sorting_configurable
                                        : $product->webshop_price;
                                
                                        $specialPrice = $product->special_price ?? 0;
                                    } else {
                                        $finalPrice = !empty($product->price_sorting_simple)
                                        ? $product->price_sorting_simple
                                        : $product->webshop_price;
                                
                                        $specialPrice = $product->special_price ?? 0;
                                        }
                                
                                    if (!empty($specialPrice) && $specialPrice > 0): ?>
                                        <span class="special-price">
                                            <span class="price"> MUR <?php echo number_format($product->special_price, 2); ?></span>
                                        </span>
                                        <span class="old-price">
                                            <span class="price" style="text-decoration: line-through;"> MUR <?php echo number_format($product->webshop_price, 2); ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="regular-price">
                                            <span class="price"> MUR <?php echo number_format($product->webshop_price, 2); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>



                            <!-- Add to cart button -->

                            <!-- <div class="bottom-action">

                                <button type="submit" id="add_to_cart" class="btn btn-primary subscr-now pull-left">Add To Cart</button>

                            </div> -->
<!-- View Product Button -->
<div class="bottom-action" style="margin-top: 15px; text-align: center;">

    <a href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>"
       class="btn btn-primary"
       style="display: inline-block; padding: 10px 25px; border-radius: 4px; text-decoration: none;">
        View Product
    </a>

</div>
                        </div>

                    </div>

                </div>

            </li>

        <?php endforeach; ?>

    </ol>

</div>



<div class="pagination-wrap">

    <?php echo $pagination; ?>

</div>

<?php else: ?>

    <p><?php echo $this->lang->line('no_products_shop'); ?></p>

<?php endif; ?>