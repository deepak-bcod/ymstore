<?php
	$shopcode = SHOPCODE;
    $shop_id = SHOP_ID;
    
    $webshop_details = CommonRepository::get_webshop_details($shopcode, $shop_id);
    
    $FinalPageTitle = "";
    if (isset($webshop_details) && $webshop_details->is_success=='true') {
        $SiteMetaTitle = ($webshop_details->FbcWebShopDetails->meta_title!='')?$webshop_details->FbcWebShopDetails->meta_title:$FinalPageTitle;
        $SiteMetaKey = ($webshop_details->FbcWebShopDetails->meta_keywords!='')?$webshop_details->FbcWebShopDetails->meta_keywords:$FinalPageTitle;
        $SiteMetaDesc = ($webshop_details->FbcWebShopDetails->meta_description!='')?$webshop_details->FbcWebShopDetails->meta_description:$FinalPageTitle;
    }else{
        $SiteMetaTitle = $FinalPageTitle;
        $SiteMetaKey = "";
        $SiteMetaDesc = "";
    }
?>
<title><?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')?$PageMetaTitle:$SiteMetaTitle; ?></title>
<meta name="title" content="<?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:$SiteMetaTitle; ?>" />
<meta name="keywords" content="<?php echo (isset($PageMetaKey) && $PageMetaKey!='')? strip_tags($PageMetaKey):strip_tags($SiteMetaKey); ?>" />
<meta name="description" content="<?php echo (isset($PageMetaDesc) && $PageMetaDesc!='')? strip_tags($PageMetaDesc):strip_tags($SiteMetaDesc); ?>" />

<?php
// Exclude internal search results from social crawling/indexing
if ($this->router->fetch_class() != 'SearchController' && $this->router->fetch_method() != 'searchResultPage') {

    // 1. Default Fallbacks (Others Link Sharing -> YM Logo)
    $default_og_image = base_url('uploads/yellow-markets-logo.png');
    $og_type = 'website';
    $og_title = (isset($PageMetaTitle) && $PageMetaTitle != '') ? $PageMetaTitle : ((isset($SiteMetaTitle) && $SiteMetaTitle != '') ? $SiteMetaTitle : 'Yellow Markets');
    $og_desc = (isset($PageMetaDesc) && $PageMetaDesc != '') ? strip_tags($PageMetaDesc) : ((isset($SiteMetaDesc) && $SiteMetaDesc != '') ? strip_tags($SiteMetaDesc) : 'Shop online with Yellow Markets - authentic products, verified local merchants, and top deals.');
    $og_image = $default_og_image;
    $og_url = current_url();
    $og_extra = array();

    $current_class = $this->router->fetch_class();
    $current_method = $this->router->fetch_method();

    // 2. CASE 1: PRODUCT SHARING (Use Product Image)
    if (($current_class == 'ProductsController' && $current_method == 'productDetails') || isset($ProductData)) {
        $og_type = 'product';

        // Product Title
        if (!empty($ProductData->other_lang_name)) {
            $og_title = $ProductData->other_lang_name;
        } elseif (!empty($ProductData->name)) {
            $og_title = $ProductData->name;
            if (!empty($ProductData->publication_name)) {
                $og_title .= ' - ' . $ProductData->publication_name;
            }
        }

        // Product Description
        if (!empty($ProductData->meta_description)) {
            $og_desc = strip_tags($ProductData->meta_description);
        } elseif (!empty($ProductData->short_description)) {
            $og_desc = strip_tags($ProductData->short_description);
        }

        // Product Image Resolution
        $prod_img = !empty($ProductData->base_image) ? $ProductData->base_image : (!empty($ProductData->image) ? $ProductData->image : '');
        if (!empty($prod_img)) {
            if (file_exists(FCPATH . 'uploads/products/large/' . $prod_img)) {
                $og_image = base_url('uploads/products/large/' . $prod_img);
            } elseif (file_exists(FCPATH . 'uploads/products/' . $prod_img)) {
                $og_image = base_url('uploads/products/' . $prod_img);
            } elseif (file_exists(FCPATH . 'uploads/products/medium/' . $prod_img)) {
                $og_image = base_url('uploads/products/medium/' . $prod_img);
            } elseif (defined('PRODUCT_LARGE_IMG')) {
                $og_image = PRODUCT_LARGE_IMG . $prod_img;
            } else {
                $og_image = base_url('uploads/products/large/' . $prod_img);
            }
        }

        // Product Pricing
        if (isset($ProductData->special_price) && (float)$ProductData->special_price > 0) {
            $og_extra['product:price:amount'] = $ProductData->special_price;
        } elseif (isset($ProductData->price) && (float)$ProductData->price > 0) {
            $og_extra['product:price:amount'] = $ProductData->price;
        }
        $og_extra['product:price:currency'] = 'MUR';
    }

    // 3. CASE 2: STORE / SHOP SHARING (Use Store Image)
    elseif (($current_class == 'ShopController' && $current_method == 'shop_details') || isset($shop_details)) {
        $og_type = 'profile';

        // Auto-fetch shop details if not already passed in view scope
        if (empty($shop_details)) {
            $shop_id = (int)$this->uri->segment(3);
            if ($shop_id > 0) {
                $ci =& get_instance();
                $ci->load->model('Shop_model');
                $shop_details = $ci->Shop_model->shop_details($shop_id);
            }
        }

        if (!empty($shop_details)) {
            $shop_name = !empty($shop_details->publication_name) ? $shop_details->publication_name : (!empty($shop_details->company_name) ? $shop_details->company_name : 'Merchant Store');
            $og_title = $shop_name . ' | Yellow Markets';

            if (!empty($shop_details->company_description)) {
                $og_desc = strip_tags($shop_details->company_description);
            } else {
                $og_desc = 'Explore authentic products, verified reviews, and exclusive offers from ' . $shop_name . ' on Yellow Markets.';
            }

            // Store Image Resolution: Prefer store image/logo, then store banner, then YM logo fallback
            if (!empty($shop_details->shop_image) && file_exists(FCPATH . 'merchant/public/images/shop_images/' . $shop_details->shop_image)) {
                $og_image = base_url('merchant/public/images/shop_images/' . $shop_details->shop_image);
            } elseif (!empty($shop_details->banner_img) && file_exists(FCPATH . 'uploads/banner_img/' . $shop_details->banner_img)) {
                $og_image = base_url('uploads/banner_img/' . $shop_details->banner_img);
            } elseif (!empty($shop_details->shop_image)) {
                $og_image = base_url('merchant/public/images/shop_images/' . $shop_details->shop_image);
            } elseif (!empty($shop_details->banner_img)) {
                $og_image = base_url('uploads/banner_img/' . $shop_details->banner_img);
            }
        }
    }

    // Clean up title and description strings
    $og_title = htmlspecialchars(trim($og_title), ENT_QUOTES, 'UTF-8');
    $og_desc = htmlspecialchars(trim(preg_replace('/\s+/', ' ', $og_desc)), ENT_QUOTES, 'UTF-8');
?>
<!-- Open Graph Meta Tags (Facebook, WhatsApp, LinkedIn, Pinterest) -->
<meta property="og:site_name" content="Yellow Markets" />
<meta property="og:type" content="<?php echo $og_type; ?>" />
<meta property="og:title" content="<?php echo $og_title; ?>" />
<meta property="og:description" content="<?php echo $og_desc; ?>" />
<meta property="og:url" content="<?php echo $og_url; ?>" />
<meta property="og:image" content="<?php echo $og_image; ?>" />
<meta property="og:image:secure_url" content="<?php echo $og_image; ?>" />

<?php foreach ($og_extra as $og_prop => $og_val): ?>
<meta property="<?php echo $og_prop; ?>" content="<?php echo htmlspecialchars($og_val, ENT_QUOTES, 'UTF-8'); ?>" />
<?php endforeach; ?>

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@YellowMarkets" />
<meta name="twitter:title" content="<?php echo $og_title; ?>" />
<meta name="twitter:description" content="<?php echo $og_desc; ?>" />
<meta name="twitter:image" content="<?php echo $og_image; ?>" />

<!-- Canonical URL -->
<link rel="canonical" href="<?php echo $og_url; ?>" />
<?php } ?>
