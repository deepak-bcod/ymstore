<?php
class CartRepository
{
    use UsesRestAPI;

    public static function cart_count($cc_post_arr)
    {

        $cart_count_APIUrl = '/webshop/cart_count';
        // print_R($cc_post_arr);die();
        $cart_count = self::post_method($cart_count_APIUrl, $cc_post_arr);
        if (isset($cart_count) && $cart_count->is_success == 'true') {
            return $cart_count;
        }
        return '';
    }

    public static function cart_listing($cc_post_arr)
{
    $cart_count_APIUrl = '/webshop/cart_listing';
    $cart_listing = self::post_method($cart_count_APIUrl, $cc_post_arr);


    // echo "<pre>";
    // print_r($cart_listing);
    // echo "</pre>";

    // echo "<pre>";
    // print_r($cc_post_arr);
    // echo "</pre>";

    // Return a structured object even on failure to prevent Controller crashes
    if (isset($cart_listing) && isset($cart_listing->is_success) && $cart_listing->is_success == 'true') {
        return $cart_listing;
    }
    
    return (object)['is_success' => 'false', 'CartData' => array()];
}

     public static function add_to_cart($cc_post_arr)
{
    // Get language from request/session
    if (!isset($cc_post_arr['lang_code']) || empty($cc_post_arr['lang_code'])) {

        if (isset($_SESSION['site_lang']) && $_SESSION['site_lang'] == 'french') {
            $cc_post_arr['lang_code'] = 'fr';
        } else {
            $cc_post_arr['lang_code'] = 'en';
        }
    }

    $addtocartApiUrl = '/webshop/add_to_cart';

    $CartResponse = self::post_method(
        $addtocartApiUrl,
        $cc_post_arr
    );

    if (isset($CartResponse)) {
        return $CartResponse;
    }

    return '';
}

    public static function remove_cart_item($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $removeCartItemApiUrl = '/webshop/remove_cart_item';

        $CartResponse = self::post_method($removeCartItemApiUrl, $final_post_arr);
        
      
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

    public static function update_whole_cart($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $wholeCartItemApiUrl = '/webshop/update_whole_cart';

        $CartResponse = self::post_method($wholeCartItemApiUrl, $final_post_arr);
        
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

	public static function update_cart_item($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $wholeCartItemApiUrl = '/webshop/update_cart_item';

        $CartResponse = self::post_method($wholeCartItemApiUrl, $final_post_arr);
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

    public static function apply_coupon_code($cc_post_arr)
    {

        $wholeCartItemApiUrl = '/webshop/apply_coupon_code';
       //print_r($wholeCartItemApiUrl);die();
        //print_r($cc_post_arr);die();
        $CartResponse = self::post_method($wholeCartItemApiUrl, $cc_post_arr);
        //print_r($CartResponse);die();
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

    public static function remove_coupon_code($cc_post_arr)
    {
        $final_post_arr = array();
        $wholeCartItemApiUrl = '/webshop/remove_coupon_code';
        $CartResponse = self::post_method($wholeCartItemApiUrl, $cc_post_arr);
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

    public static function cart_listing_check_cod($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $CartListingApiUrl = '/webshop/cart_listing_check_cod';

        $CartResponse = self::post_method($CartListingApiUrl, $final_post_arr);
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }

    public static function payment_charge_updated($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $PaymentChargeApiUrl = '/webshop/payment_charge_updated';

        $CartResponse = self::post_method($PaymentChargeApiUrl, $final_post_arr);
        if (isset($CartResponse)) {
            return $CartResponse;
        }
        return '';
    }
}
