<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <?php $this->load->view('product/breadcrum'); ?>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <span class="filter-op"><i class="fa fa-filter" aria-hidden="true"></i> <?= $this->lang->line('filter'); ?></span>
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters($current_category_id))->render();
                }
                ?>
                <h2><?= $this->lang->line('categories'); ?></h2>
                <?php (new TopMenu('categorymenu'))->render(); ?>
            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-12 col-sm-12">
                        <h2><?= $this->lang->line('new_arrivals'); ?></h2>
                    </div>
                </div>
                <!-- BEGIN PRODUCT LIST -->
                <div class="products-list-full grid-view">
                    <div class="row">
                        <?php foreach ($newarrival_product as $prod): 
                            $prod = ProductPresenter::from($prod);
                            $feat_image = $prod->product_image('thumb');
                        ?>
                            <div class="col-md-4 mb-4">
                                <?php (new ProductList())->productListData($prod, $feat_image, 'NewArrivalListing', '', ''); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper text-center mt-4">
                    <?php echo $pagination_links; ?>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script src="<?=base_url('public/js/recliner.js')?>"></script>

  <script type="text/javascript">
   $(function() {

            // instantiate recliner
            $('.lazy').recliner({
                attrib: "data-src", // selector for attribute containing the media src
                throttle: 300,      // millisecond interval at which to process events
                threshold: 100,     // scroll distance from element before its loaded
                live: true          // auto bind lazy loading to ajax loaded elements
            });

            // handle lazyload events
            $(document).on('lazyload', '.lazy', function() {
                var $e = $(this);
                // do something with the element to be loaded...
                console.log('lazyload', $e);
            });

            // handle lazyshow events
            $(document).on('lazyshow', '.lazy', function() {
                var $e = $(this);
                // do something with the loaded element...
                console.log('lazyshow', $e);
            });
        });
  </script>
  <?php $this->load->view('common/footer'); ?>

  </body>
</html>

<script type="text/javascript">
    function openFeedbackWindow(openFeedbackWindow){
        if(openFeedbackWindow != ''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"CustomerController/open_feedback_popup",
            //data: {customer_id:customer_id},
            //async:false,
            complete: function () {
            },
            beforeSend: function(){
                // $('#ajax-spinner').show();
            },
            success: function(response) {
                $("#WebShopCommonModal").modal();
                $("#modal-content").html(response);
            }
        });
    }else{
        return false;
    }
    }
</script>

