<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <?php $this->load->view('product/breadcrum'); ?>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <span class="filter-op"><i class="fa fa-filter" aria-hidden="true"></i> <?= $this->lang->line('filter') ?: 'Filter'; ?></span>
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters($current_category_id ?? ''))->render();
                }
                ?>
                <h2><?= $this->lang->line('categories') ?: 'Categories'; ?></h2>
                <?php (new TopMenu('categorymenu'))->render(); ?>
            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-12 col-sm-12">
                        <h2><?= $this->lang->line('trending_products') ?: 'Trending Products'; ?></h2>
                    </div>
                </div>
<style>
.products-list-full .view-product-btn,
.products-list-full .ym-view-details-btn,
.products-list-full .add2cart {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 100% !important;
    height: auto !important;
    min-height: 40px !important;
    padding: 10px 15px !important;
    margin: 0 auto !important;
    background: #ffffff !important;
    border: 1px solid #777777 !important;
    border-radius: 4px !important;
    color: #555555 !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    line-height: 20px !important;
    text-align: center !important;
    text-decoration: none !important;
    box-sizing: border-box !important;
    cursor: pointer !important;
    transition: all 0.2s ease-in-out !important;
}

.products-list-full .view-product-btn:hover,
.products-list-full .view-product-btn:focus,
.products-list-full .ym-view-details-btn:hover,
.products-list-full .ym-view-details-btn:focus,
.products-list-full .add2cart:hover,
.products-list-full .add2cart:focus {
    background: #0f5cd0 !important;
    border-color: #0f5cd0 !important;
    color: #ffffff !important;
    text-decoration: none !important;
    outline: none !important;
}

.products-list-full .promo-countdown-wrapper,
.products-list-full .promo-stock-wrapper {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    text-align: center !important;
    width: 100% !important;
}
</style>
                <!-- BEGIN PRODUCT LIST -->
                <div class="products-list-full grid-view">
                    <?php if (!empty($featured_product)) { ?>
                        <div class="row">
                            <?php foreach ($featured_product as $prod): 
                                $prod = ProductPresenter::from($prod);
                                $feat_image = $prod->product_image('thumb');
                            ?>
                                <div class="col-md-4 mb-4">
                                    <?php (new ProductList())->productListData($prod, $feat_image, 'TrendingListing', '', ''); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-warning text-center" style="margin-top:20px;padding:25px;font-size:16px;">
                            <?= $this->lang->line('no_products_found_category') ?: 'No products found'; ?>
                        </div>
                    <?php } ?>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination_links)) { ?>
                    <div class="pagination-wrapper text-center mt-4">
                        <?php echo $pagination_links; ?>
                    </div>
                <?php } ?>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
$(document).ready(function () {
    // Remove title tooltips from product cards
    $('.products-list-full [title]').removeAttr('title');

    // Remove Bootstrap tooltip attributes
    $('.products-list-full [data-toggle="tooltip"]').removeAttr('data-toggle');
    $('.products-list-full [data-bs-toggle="tooltip"]').removeAttr('data-bs-toggle');
});
</script>

