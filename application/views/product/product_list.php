<?php
// echo "<pre>";
// print_r($current_category_id);
// die;

$attributeListing = (isset($catalogFilter->productCatalogFilter->attribute_listing->language__Language) && is_iterable($catalogFilter->productCatalogFilter->attribute_listing->language__Language))
    ? $catalogFilter->productCatalogFilter->attribute_listing->language__Language
    : [];
// echo "<pre>";
// print_r($attributeListing);
// die;
?>

<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <?php $this->load->view('product/breadcrum'); ?>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <span class="filter-op"><i class="fa fa-filter" aria-hidden="true"></i> Filter</span>
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters($current_category_id))->render();
                }
                ?>
                <h2><?php echo lang('categories'); ?></h2>
                <?php (new TopMenu('categorymenu'))->render(); ?>

            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-12 col-sm-12">
                        <?php $this->load->view('product/category_heading'); ?>
                        <div class="check-flex">
                            <div class="pull-left" id="language_dropdown_new">
                                <label class="control-label"><?php echo lang('language_label'); ?>:</label>
                                <select class="form-control input-sm " id="language">
                                    <option value=""><?php echo lang('text_all'); ?></option>
                                    <?php if (!empty($attributeListing) && is_iterable($attributeListing)) : ?>
                                        <?php foreach ($attributeListing as $language) : ?>
                                            <?php $selected = (isset($language->attr_value) && $language->attr_value == '') ? 'selected' : ""; ?>
                                            <option value="<?php echo isset($language->attr_value) ? $language->attr_value : ''; ?>" <?php echo $selected; ?>><?php echo isset($language->attr_options_name) ? $language->attr_options_name : ''; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                            </div>
                            <div class="pull-left" style="margin-left: 15px;">
                                <label><?php echo lang('sort_by_label'); ?>:</label>
                                <select class="form-control input-sm" id="sort-by">
                                    <option value="newest"><?php echo lang('sort_newest'); ?></option>
                                    <option value="popular"><?php echo lang('sort_popularity'); ?></option>
                                    <option value="price_des"><?php echo lang('sort_price_high_low'); ?></option>
                                    <option value="price_asc"><?php echo lang('sort_price_low_high'); ?></option>
                                </select>
                            </div>
                            <div class="pull-left" style="margin-left: 15px;">
                                <label><?php echo lang('show_label'); ?>:</label>
                                <select class="form-control input-sm" id="show-limit">
                                    <?php if (isset($show_limit) && count($show_limit) > 0) { ?>
                                        <?php foreach ($show_limit as $limit) { ?>
                                            <option value="<?php echo $limit; ?>" <?php echo ($show_limit_selected == $limit) ? 'selected' : "" ?>><?php echo $limit; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="category-id" name="category-id" value="<?php echo $cat_obj->id; ?>">
                        <input type="hidden" name="page_sort_type" id="page_sort_type" value="Listing">
                    </div>
                </div>
                <!-- BEGIN PRODUCT LIST -->
                
            <div class="product-list-section" id="product-list-section">

                <?php if (!empty($product_list->ProductList)) { ?>

                    <div class="row product-list" style="display: flex; flex-wrap: wrap;">
                        <?php foreach ($product_list->ProductList as $prod) { ?>
                            <div class="col-md-4 col-sm-6 col-xs-12 margin-bottom-20" style="float: left;">
                                <?php
                                $prod->current_category_id = $current_category_id;
                                $prod = ProductPresenter::from($prod);
                                $prod_image = $prod->product_image('thumb');
                                (new ProductList())->productListData($prod, $prod_image, 'Listing');
                                ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="clearfix"></div>

                        <?php } else { ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-warning text-center" style="margin-top:30px;padding:30px;font-size:16px;">
                                    <i class="fa "></i>
                                    <?php echo lang('no_products_found_category'); ?>
                                </div>
                            </div>
                        </div>

                        <?php } ?>

                <!-- END PRODUCT LIST -->


                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="pagination pull-right">
                            <?php
                            if (!empty($PaginationLink)) {
                                echo $PaginationLink;
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>


<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script>
    var language_attr_options_name = "<?= $attributeListing[0]->attr_options_name ?>";
</script>

