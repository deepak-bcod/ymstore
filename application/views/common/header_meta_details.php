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

    // 1. Default Fallbacks (Rule 3: Others Link Sharing -> YM Logo)
    $default_og_image = base_url('uploads/yellow-markets-logo.png');
    $og_type = 'website';
    $og_title = (isset($PageMetaTitle) && $PageMetaTitle != '') ? $PageMetaTitle : ((isset($SiteMetaTitle) && $SiteMetaTitle != '') ? $SiteMetaTitle : 'Yellow Markets');
    $og_desc = (isset($PageMetaDesc) && $PageMetaDesc != '') ? $PageMetaDesc : ((isset($SiteMetaDesc) && $SiteMetaDesc != '') ? $SiteMetaDesc : 'Shop online with Yellow Markets - authentic products, verified local merchants, and top deals.');
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

        // Product Description (checks meta_description, short_description, full description, lang_description)
        if (!empty($ProductData->meta_description)) {
            $og_desc = $ProductData->meta_description;
        } elseif (!empty($ProductData->short_description)) {
            $og_desc = $ProductData->short_description;
        } elseif (!empty($ProductData->description)) {
            $og_desc = $ProductData->description;
        } elseif (!empty($ProductData->lang_description)) {
            $og_desc = $ProductData->lang_description;
        }

        // Product Image Resolution
        // NOTE: WhatsApp scraper drops images > 300KB.
        // We prioritize smaller, highly-compressed sizes (medium/thumb <= 300KB) so WhatsApp always displays the card.
        $prod_img = !empty($ProductData->base_image) ? $ProductData->base_image : (!empty($ProductData->image) ? $ProductData->image : '');
        if (!empty($prod_img)) {
            $path_medium = FCPATH . 'uploads/products/medium/' . $prod_img;
            $path_thumb  = FCPATH . 'uploads/products/thumb/' . $prod_img;
            $path_large  = FCPATH . 'uploads/products/large/' . $prod_img;
            $path_root   = FCPATH . 'uploads/products/' . $prod_img;

            if (file_exists($path_medium) && filesize($path_medium) > 0 && filesize($path_medium) <= 300000) {
                $og_image = base_url('uploads/products/medium/' . $prod_img);
            } elseif (file_exists($path_thumb) && filesize($path_thumb) > 0 && filesize($path_thumb) <= 300000) {
                $og_image = base_url('uploads/products/thumb/' . $prod_img);
            } elseif (file_exists($path_thumb)) {
                $og_image = base_url('uploads/products/thumb/' . $prod_img);
            } elseif (file_exists($path_medium)) {
                $og_image = base_url('uploads/products/medium/' . $prod_img);
            } elseif (file_exists($path_large)) {
                $og_image = base_url('uploads/products/large/' . $prod_img);
            } elseif (file_exists($path_root)) {
                $og_image = base_url('uploads/products/' . $prod_img);
            } else {
                $og_image = base_url('uploads/products/thumb/' . $prod_img);
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
                $og_desc = $shop_details->company_description;
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

    // Determine image MIME type
    $og_image_type = 'image/jpeg';
    if (!empty($og_image)) {
        $ext = strtolower(pathinfo(parse_url($og_image, PHP_URL_PATH), PATHINFO_EXTENSION));
        if ($ext === 'png') {
            $og_image_type = 'image/png';
        } elseif ($ext === 'webp') {
            $og_image_type = 'image/webp';
        } elseif ($ext === 'gif') {
            $og_image_type = 'image/gif';
        }
    }

    // Clean up title: decode entities first then encode to prevent double &amp;amp;
    $og_title = htmlspecialchars(html_entity_decode(trim($og_title), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

    // Clean up description: strip HTML tags, decode entities, normalize whitespace, and truncate to ~220 chars
    $og_desc = strip_tags(html_entity_decode($og_desc, ENT_QUOTES, 'UTF-8'));
    $og_desc = preg_replace('/\s+/', ' ', trim($og_desc));
    if (mb_strlen($og_desc, 'UTF-8') > 220) {
        $og_desc = mb_substr($og_desc, 0, 217, 'UTF-8') . '...';
    }
    $og_desc = htmlspecialchars($og_desc, ENT_QUOTES, 'UTF-8');
?>
<!-- Open Graph Meta Tags (Facebook, WhatsApp, LinkedIn, Pinterest) -->
<meta property="og:site_name" content="Yellow Markets" />
<meta property="og:type" content="<?php echo $og_type; ?>" />
<meta property="og:title" content="<?php echo $og_title; ?>" />
<meta property="og:description" content="<?php echo $og_desc; ?>" />
<meta property="og:url" content="<?php echo $og_url; ?>" />
<meta property="og:image" content="<?php echo $og_image; ?>" />
<meta property="og:image:secure_url" content="<?php echo $og_image; ?>" />
<meta property="og:image:type" content="<?php echo $og_image_type; ?>" />

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
