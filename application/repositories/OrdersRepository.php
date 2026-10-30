<?php
// shop3/application/repositories/ProductRepository.php

class OrdersRepository
{
    use UsesRestAPI;
   public static function my_orders_listing($LoginID, $limit, $offset)
{
    try {

        if (empty($LoginID)) {
            log_message('error', 'MY ORDERS: LoginID is empty.');
            return null;
        }

        $LoginID = (int) $LoginID;
        $limit   = (int) $limit;
        $offset  = (int) $offset;

        if ($limit <= 0) {
            $limit = 20;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $APIUrl = '/webshop/my_orders_listing_new/'
                . $LoginID . '/'
                . $limit . '/'
                . $offset;

        log_message('error', 'MY ORDERS API URL: ' . $APIUrl);

        // Call API
        $result = self::get_method($APIUrl);

        // Decode if API returns JSON string
        if (is_string($result)) {
            $result = json_decode($result);
        }

        log_message('error', 'MY ORDERS API RESPONSE: ' . print_r($result, true));

        if (!empty($result) && isset($result->is_success)
            && $result->is_success == 'true') {

            return $result;
        }

        log_message('error', 'MY ORDERS API returned unsuccessful response.');

        return $result;

    } catch (\Throwable $e) {

        log_message('error', 'MY ORDERS REPOSITORY ERROR: ' . $e->getMessage());
        log_message('error', 'MY ORDERS ERROR FILE: ' . $e->getFile());
        log_message('error', 'MY ORDERS ERROR LINE: ' . $e->getLine());

        return null;
    }
}

    public static function order_operation_checks($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);

        $APIUrl = '/webshop/order_operation_checks';

        $response = self::post_method($APIUrl, $final_post_arr);
        if(isset($response) && isset($response->is_success) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }

    public static function my_return_orders_listing($shopcode, $shop_id, $order_id)
    {
        $result = self::get_method('/webshop/my_return_orders_listing/'.$shopcode.'/'.$shop_id.'/'.$order_id);
        return $result;
    }

    public static function my_return_order_detail($shopcode, $shop_id, $order_id)
    {
        $result = self::get_method('/webshop/my_return_order_detail/'.$shopcode.'/'.$shop_id.'/'.$order_id);
        return $result;
    }

    public static function return_order_print($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);
         
        $APIUrl = '/webshop/return_order_print';

        $response = self::post_method($APIUrl, $final_post_arr);
        if (isset($response) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }

    public static function return_order_request($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);
        // echo "<pre>";
        // print_r($final_post_arr);
        // exit;
        $APIUrl = '/webshop/return_order_request';

        $response = self::post_method($APIUrl, $final_post_arr);
        if (isset($response) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }

    public static function replacement_order_request($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);
        // echo "<pre>";
        // print_r($final_post_arr);
        // exit;
        $APIUrl = '/webshop/replacement_order_request';

        $response = self::post_method($APIUrl, $final_post_arr);
        if (isset($response) && is_object($response)) {
            return $response;
        }
        return '';
    }

    public static function tracking_details_request( $post_arr)
    {
        
        $final_post_arr = array();
        // $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        // $final_post_arr = array_merge($post_arr1, $post_arr);

        $APIUrl = '/webshop/tracking_details_request';
        // echo $APIUrl;
        // print_r($post_arr); die();
        $response = self::post_method($APIUrl, $post_arr);

        //print_r($response);die();
        if (isset($response) && !empty($response) && isset($response->is_success) && $response->is_success == 'true') {
            return $response;
        }
        return 'Something Went wrong!!!';
    }

    public static function tracking_guest_order_details( $post_arr)
    {
        
        $final_post_arr = array();
        $APIUrl = '/webshop/tracking_guest_order_details';
        // echo $APIUrl;
        // print_r($post_arr); die();
        $response = self::post_method($APIUrl, $post_arr);
       
        if (isset($response)) {
            
            return $response;
        }
        //echo"byyyyyyyyyy";die();
        return '';
    }

    public static function return_order_confirm($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);

        $APIUrl = '/webshop/return_order_confirm';

        $response = self::post_method($APIUrl, $final_post_arr);
        if (isset($response) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }


    public static function cancel_order_request($post_arr)
    {

        $APIUrl = '/webshop/cancel_order_request';

        $response = self::post_method($APIUrl, $post_arr);
        if (isset($response) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }

    public static function my_order_detail($order_id)
    {
        $result = self::get_method('/webshop/my_order_detail/'.$order_id);
        // print_r($result);die;
        return $result;
    }

    public static function get_customer_address_by_order_id($post_arr)
    {

        $APIUrl = '/webshop/get_customer_address_by_order_id';

        $response = self::post_method($APIUrl, $post_arr);
        if (isset($response) && $response->is_success == 'true') {
            return $response;
        }
        return '';
    }
}
