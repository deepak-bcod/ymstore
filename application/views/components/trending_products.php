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
