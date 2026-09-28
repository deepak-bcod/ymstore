<?php $this->load->view('common/header'); ?>
<?php 
// echo "<pre>"; print_r($navCatData); die;
$lang = $this->session->userdata('site_lang'); // or whatever key you use for language
// print_r($lang);die;
?>
<div class="main">
    <div class="container-fluid">

        <!-- BEGIN SALE PRODUCT & NEW ARRIVALS -->
        <div class="row margin-bottom-40">

            <!-- BEGIN SALE PRODUCT -->
            <div class="col-md-12 sale-product homepage-top">

                <div class="row">
                    <div class="col-md-12">
                        <div class="margin-bottom-25 home-top-banner">
                            <?php //(new HomeCategoryBanners('topbanner'))->render(); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12 home-top-banner-main">
                <div class="container">
                    <div class="slidershow home-toped">
                        <div class="owl-carousel owl-theme">

                            <div class="item"><a title="<?= $this->lang->line('jewellery_accessories') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-computer_1_.jpg"
                                        alt="<?= $this->lang->line('jewellery_accessories') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('flash_sales') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-flash-sale-a_1_.jpg"
                                        alt="<?= $this->lang->line('flash_sales') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('new_arrivals') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-new-arrivals_2_.jpg"
                                        alt="<?= $this->lang->line('new_arrivals') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('daily_deals') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-daily-deals-launch_1_.jpg"
                                        alt="<?= $this->lang->line('daily_deals') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('home_appliances') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-home-appliances_1_.jpg"
                                        alt="<?= $this->lang->line('home_appliances') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('food_groceries') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-groceries-1_1_.jpg"
                                        alt="<?= $this->lang->line('food_groceries') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('art_craft') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-craft_1_.jpg"
                                        alt="<?= $this->lang->line('art_craft') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('electronics') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-computer_1_.jpg"
                                        alt="<?= $this->lang->line('electronics') ?>"></a></div>

                        </div>
                    </div>

                    <!-- second-section -->
                    <div class="banner-1">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="banner-image m-none">
                                  <!--  <a title="<?php// $this->lang->line('probieau_solution') ?>" href="#" target="_blank" rel="noopener">
                                         <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/item-1.jpg"
                                             alt="">  
                                        </a>     
                                             -->
                                    
                                    <?php
                                    if ($lang == 'french') { ?>
                                        <iframe id='a915d20d' name='a915d20d' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2525&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='614' height='117' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a0070c29&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2525&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a0070c29' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='aab55e89' name='aab55e89' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2290&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='614' height='117' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a61f3364&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2290&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                    
                                </div>
                                <div class="banner-image d-none">
                                  <!--  <a title="<?php// $this->lang->line('probieau_solution') ?>" href="#" target="_blank" rel="noopener">
                                         <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/item-1.jpg"
                                             alt="">  
                                        </a>     
                                             -->

                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='acf47cb6' name='acf47cb6' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2527&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a1ccdc7e&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2527&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a1ccdc7e' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a773719f' name='a773719f' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2519&cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=ad37a50e&cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2519&cb=INSERT_RANDOM_NUMBER_HERE&n=ad37a50e' border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                    
                                    
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="banner-image m-none">
                                   <!-- <a title="<?php // $this->lang->line('nama_boutique') ?>" href="#" target="_blank" rel="noopener">
                                         <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/nama_boutique_banner_sales.jpg"
                                             alt="" width="" height="150">
                                        </a>
                                                                                
                                    -->
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='afcbf90c' name='afcbf90c' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2526&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='614' height='117' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=aabaf56a&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2526&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=aabaf56a' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>    
                                        <iframe id='ac224216' name='ac224216' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2291&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='614' height='117' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=af9b48b2&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2291&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>                               
                                </div>
                                <div class="banner-image d-none">
                                   <!-- <a title="<?php // $this->lang->line('nama_boutique') ?>" href="#" target="_blank" rel="noopener">
                                         <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/nama_boutique_banner_sales.jpg"
                                             alt="" width="" height="150">
                                        </a>
                                                                                
                                    -->
                                    
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='a81cdaa3' name='a81cdaa3' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2528&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a23ada76&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2528&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a23ada76' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a3996126' name='a3996126' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2520&cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=aee2cbf1&cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2520&cb=INSERT_RANDOM_NUMBER_HERE&n=aee2cbf1' border='0' alt='' /></a></iframe>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- second-section -->

                    <!-- Daily Deals Section -->
                    <?php if (!empty($daily_deals_products)) : ?>
                        <div class="product-slider product-slider-1 home-products-section">
                            <div class="title-block">
                                <h2><?= $this->lang->line('daily_deals') ?></h2>
                            </div>

                            <div id="sm_filterproducts_daily_deals" class="products-list-full home-trending-slider">
                                <div class="owl-carousel owl-theme owl-loaded owl-drag">
                                    <div class="owl-stage-outer">
                                        <div class="owl-stage" style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1715px;">
                                            <?php 
                                            $count = 0; 
                                            foreach ($daily_deals_products as $deal_prod) :
                                                if ($count >= 3) break;
                                                $deal_prod = ProductPresenter::from($deal_prod);
                                                $feat_image = $deal_prod->product_image('thumb');
                                                (new ProductList())->productListData($deal_prod, $feat_image, 'DailyDealsListing', '', '');
                                                $count++;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="view-more-wrap" style="text-align:center; margin-top:20px;">
                                <a href="<?= base_url('daily-deals') ?>" class="btn btn-primary">
                                    <?= $this->lang->line('view_more') ?>
                                </a>
                            </div>

                        </div>
                    <?php endif; ?>

                    <!-- Flash Sales Section -->
                    <!-- <?php if (!empty($flash_sale_products)) : ?>
                        <div class="product-slider product-slider-1 home-products-section">
                            <div class="title-block">
                                <h2><?= $this->lang->line('flash_sales') ?></h2>
                            </div>

                            <div id="sm_filterproducts_flash_sales" class="products-list-full home-trending-slider">
                                <div class="owl-carousel owl-theme owl-loaded owl-drag">
                                    <div class="owl-stage-outer">
                                        <div class="owl-stage">
                                            <?php 
                                            $count = 0; 
                                            foreach ($flash_sale_products as $flash_sale) :
                                                if ($count >= 3) break;
                                                $flash_sale = ProductPresenter::from($flash_sale);
                                                $feat_image = $flash_sale->product_image('thumb');
                                                (new ProductList())->productListData($flash_sale, $feat_image, 'FlashSaleListing', '', '');
                                                $count++;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           <div class="view-more-wrap" style="text-align:center; margin-top:20px;">
                                <a href="<?= base_url('flash-sale') ?>" class="btn btn-primary">
                                    <?= $this->lang->line('view_more') ?>
                                </a>
                            </div>

                        </div>
                    <?php endif; ?> -->

                    <!-- New Arrivals Section -->
                    <div class="product-slider product-slider-1 home-products-section">
                        <?php (new NewArrivalProducts(20, ""))->new_arrivals_products(); ?>
                    </div>

                    <!-- Banner 2 -->
                    <div class="banner-2">
                        <div class="row">
                            <div class="col-lg-4 col-md-4">
                                <div class="banner-image m-none">
                                   <!-- <a title="<?= $this->lang->line('support_os') ?>" 
                                    href="<?php  //base_url('merchant_shop/support-operating-system-ltd.html') ?>" 
                                    target="_blank" 
                                    rel="noopener">

                                        <img class="mark-lazy" 
                                            src="<?php// base_url('statis_pages/home_page_files/3-banner-ad-520x580.png') ?>" 
                                            alt="<?php// $this->lang->line('support_os') ?>">
                                    </a> -->

                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='a5d8fe71' name='a5d8fe71' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2529&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='400' height='448' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a81e2f3f&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2529&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a81e2f3f' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a0634a68' name='a0634a68' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2292&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='400' height='448' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=afe4ff05&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2292&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                   
                                </div>
                                <div class="banner-image d-none">
                                   <!-- <a title="<?= $this->lang->line('support_os') ?>" 
                                    href="<?php  //base_url('merchant_shop/support-operating-system-ltd.html') ?>" 
                                    target="_blank" 
                                    rel="noopener">

                                        <img class="mark-lazy" 
                                            src="<?php// base_url('statis_pages/home_page_files/3-banner-ad-520x580.png') ?>" 
                                            alt="<?php// $this->lang->line('support_os') ?>">
                                    </a> -->
                                    
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='aaa71fe2' name='aaa71fe2' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2532&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='320' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a527d843&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2532&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a527d843' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='abd69b79' name='abd69b79' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2524&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='320' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a42d5f79&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2524&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a42d5f79' border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-lg-5 col-md-5">
                                <div class="banner-image m-none">
                                    <!-- <a title="<?php // $this->lang->line('joulsy_joys') ?>"
                                    href="<?php // base_url('merchant_shop/joulsy-joys.html') ?>"
                                    target="_blank"
                                    rel="noopener">

                                        <img class="mark-lazy"
                                            src="<?php // base_url('statis_pages/home_page_files/4-ad-banner-655x280_1_.jpg') ?>"
                                            alt="<?php // $this->lang->line('joulsy_joys') ?>">
                                    </a> -->
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='a43bce89' name='a43bce89' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2530&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='508' height='217' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=abec6646&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2530&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=abec6646' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a2fafb93' name='a2fafb93' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2293&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='508' height='217' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=af2d1ad2&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2293&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                    

                                </div>
                                <div class="banner-image d-none">
                                    <!-- <a title="<?php // $this->lang->line('joulsy_joys') ?>"
                                    href="<?php // base_url('merchant_shop/joulsy-joys.html') ?>"
                                    target="_blank"
                                    rel="noopener">

                                        <img class="mark-lazy"
                                            src="<?php // base_url('statis_pages/home_page_files/4-ad-banner-655x280_1_.jpg') ?>"
                                            alt="<?php // $this->lang->line('joulsy_joys') ?>">
                                    </a> -->
                                    
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='a6d6fed7' name='a6d6fed7' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2533&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='300' height='150' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a0df57cd&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2533&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a0df57cd' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='ab7eb3b3' name='ab7eb3b3' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2521&cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='300' height='150' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a0efa25a&cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2521&cb=INSERT_RANDOM_NUMBER_HERE&n=a0efa25a' border='0' alt='' /></a></iframe>
                                    <?php } ?>

                                </div>
                                <div class="banner-image m-none">
                                    <!-- <a title="<?php // $this->lang->line('ad_banner') ?>" href="#" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/5-ad-banner-655x280.jpg"
                                             alt="<?php // $this->lang->line('ad_banner') ?>">
                                    </a> -->
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='ac93aca2' name='ac93aca2' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2531&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='508' height='217' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a6bf781c&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2531&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a6bf781c' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a9f553ed' name='a9f553ed' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2294&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='508' height='217' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a66d5726&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2294&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                   
                                </div>
                                <div class="banner-image d-none">
                                    <!-- <a title="<?php // $this->lang->line('ad_banner') ?>" href="#" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/5-ad-banner-655x280.jpg"
                                             alt="<?php // $this->lang->line('ad_banner') ?>">
                                    </a> -->
                                    
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='a8ec0308' name='a8ec0308' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2534&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='300' height='150' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a5a1c353&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2534&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a5a1c353' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='adebae82' name='adebae82' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2522&cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='300' height='150' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a59442a8&cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2522&cb=INSERT_RANDOM_NUMBER_HERE&n=a59442a8' border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3">
                                <div class="banner-image">
                                    <!-- <a title="<?php // $this->lang->line('cleanera') ?>"
                                    href="<?php // base_url('merchant_shop/cleanera-ltd.html') ?>"
                                    target="_blank"
                                    rel="noopener">

                                        <img class="mark-lazy"
                                            src="<?php // base_url('statis_pages/home_page_files/6-banner-ad-385x580.jpg') ?>"
                                            alt="<?php // $this->lang->line('cleanera') ?>">
                                    </a> -->
                                    <?php if ($lang == 'french') { ?>
                                        <iframe id='aa7b4a98' name='aa7b4a98' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2535&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='295' height='444' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a76c8e53&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2535&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a76c8e53' border='0' alt='' /></a></iframe>
                                    <?php } else { ?>
                                        <iframe id='a1efcaca' name='a1efcaca' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2295&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='295' height='444' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a8ddeb92&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2295&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                                    <?php } ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Products Section -->
                    <div class="product-slider product-slider-2 trending-products-section">
                        <?php (new FeaturedProducts(70))->render(); ?>
                    </div>
                </div>
            </div>
            <!-- END SALE PRODUCT & NEW ARRIVALS -->

        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
