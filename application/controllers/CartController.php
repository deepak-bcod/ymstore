<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CartController extends CI_Controller
{
      public function __construct() {

        parent::__construct();

        $this->load->helper(['url', 'form', 'language']);

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }

    public function index()
    {
        $data['PageTitle']= 'Cart';

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $quote_id = '';
        $session_id = '';
        $customer_id = '';

        if ($this->session->userdata('QuoteId')) {
            $quote_id=$this->session->userdata('QuoteId');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id=$this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id=$this->session->userdata('LoginID');
        }

        if (!empty($customer_id)) {
            $has_items = false;
            if (!empty($quote_id)) {
                $item_count = $this->db->from('sales_quote_items')
                    ->where('quote_id', $quote_id)
                    ->count_all_results();
                $has_items = ($item_count > 0);
            }
            if (!$has_items) {
                $active_quote = $this->db->select('SQ.quote_id, SQ.session_id')
                    ->from('sales_quote SQ')
                    ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                    ->where('SQ.customer_id', $customer_id)
                    ->order_by('SQ.quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
                if (!$active_quote) {
                    $active_quote = $this->db->select('quote_id, session_id')
                        ->from('sales_quote')
                        ->where('customer_id', $customer_id)
                        ->order_by('quote_id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                }
                if ($active_quote) {
                    $quote_id = $active_quote->quote_id;
                    $this->session->set_userdata('QuoteId', $quote_id);
                    if (!empty($active_quote->session_id)) {
                        $session_id = $active_quote->session_id;
                        $this->session->set_userdata('sis_session_id', $session_id);
                        $this->session->set_userdata('LoginToken', $session_id);
                    }
                }
            }
        }

        $lang_code='';
		if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
			$lang_code=$this->session->userdata('lcode');
		}

        $data['QuoteId'] = $quote_id;
        $this->template->load('cart/cart', $data);
    }

    public function addtocart()
    {
        $error='';

        if ($this->session->userdata('QuoteId')) {
            $quote_id=$this->session->userdata('QuoteId');
        } else {
            $quote_id='';
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
            $session_id = $this->session->userdata('LoginToken');
            if (empty($session_id)) {
                if ($this->session->userdata('sis_session_id')) {
                    $session_id = $this->session->userdata('sis_session_id');
                } else {
                    $session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
                    $this->session->set_userdata('sis_session_id', $session_id);
                }
            }
        } else {
            $customer_id = '';
            if ($this->session->userdata('sis_session_id')) {
                $session_id = $this->session->userdata('sis_session_id');
            } else {
                $session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
                $this->session->set_userdata('sis_session_id', $session_id);
            }
        }
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
        
        if (!empty($_POST)) {
            if (empty($_POST['product_id'])|| empty($_POST['quantity'])) {
                echo json_encode(array('status'=>403, 'msg'=>"Please enter all mandatory / compulsory fields."));
                exit;
            } else {
                $product_id=$_POST['product_id'];
                $quantity=$_POST['quantity'];
                $ResponseData = CommonRepository::basic_product_detail($product_id);
                if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode=='200') {
                    $ProductData=$ResponseData->ProductData;

                    if (isset($ProductData->stock_status) && $ProductData->stock_status !== 'Instock') {
                        echo json_encode(array('status'=>403, 'message'=>"Product is Out of Stock."));
                        exit;
                    }
                    if ($ProductData->product_type == 'simple' && isset($ProductData->total_qty) && (int)$ProductData->total_qty <= 0) {
                        echo json_encode(array('status'=>403, 'message'=>"Product is Out of Stock."));
                        exit;
                    }

                    if ($ProductData->product_type=='configurable') {
                        $conf_simple_pid=$_POST['conf_simple_pid'];

                        $AddtoCartParam=array('session_id'=>$session_id,'product_id'=>$product_id,'quantity'=>$quantity,'conf_simple_pid'=>$conf_simple_pid,'quote_id'=> $quote_id,'customer_id'=>$customer_id,'customer_type_id'=>$customer_type_id);
                    }else if($ProductData->product_type=='bundle'){
						$conf_simple_pid= (isset($_POST['conf_simple_pid'])? $_POST['conf_simple_pid']:array());
						$bundle_child_id= (isset($_POST['bundle_child_id'])? $_POST['bundle_child_id']:array());
						$AddtoCartParam=array('session_id'=>$session_id,'product_id'=>$product_id,'quantity'=>$quantity,'conf_simple_pid'=>$conf_simple_pid,'bundle_child_id'=>$bundle_child_id,'quote_id'=> $quote_id,'customer_id'=>$customer_id,'customer_type_id'=>$customer_type_id);
					} else {
                        $AddtoCartParam=array('session_id'=>$session_id,'product_id'=>$product_id,'quantity'=>$quantity,'quote_id'=> $quote_id,'customer_id'=>$customer_id,'customer_type_id'=>$customer_type_id);
                    }
                    //print_R($AddtoCartParam);
                    $CartResponseData = CartRepository::add_to_cart($AddtoCartParam);
                    //echo "<pre>";print_R($CartResponseData);die();
                    if (!empty($CartResponseData) && isset($CartResponseData->statusCode) && $CartResponseData->statusCode=='200') {
                        $QuoteId=(isset($CartResponseData->QuoteId) && $CartResponseData->QuoteId!='')?$CartResponseData->QuoteId:'';
                        if ($QuoteId) {
                            $this->session->set_userdata('QuoteId', $QuoteId);
                        }
                        $this->AutoApplyCouponCode();
                        echo json_encode(array('status'=>200, 'message'=>$CartResponseData->message,'QuoteId'=>$QuoteId));
                        exit;
                    } else {
                        echo json_encode(array('status'=>403, 'message'=>$CartResponseData->message));
                        exit;
                    }
                } else {
                    echo json_encode(array('status'=>403, 'message'=>"Product not exist."));
                    exit;
                }
            }
        } else {
            redirect('/cart');
        }
    }
    
    /*
    public function removecartitem()
    {
        if (isset($_POST['item_id']) && $_POST['item_id']!='') {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            $quote_id=$this->session->userdata('QuoteId');
            $item_id=$_POST['item_id'];

            $lang_code = '';
            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                $lang_code = $this->session->userdata('lcode');
            }


            $removeCartItemParam=array('item_id'=>$item_id,'quote_id'=> $quote_id,'lang_code'=> $lang_code);
            $CartResponseData = CartRepository::remove_cart_item($shopcode, $shop_id, $removeCartItemParam);
            if (!empty($CartResponseData) && isset($CartResponseData) && $CartResponseData->statusCode=='200') {
                echo json_encode(array('status'=>200, 'message'=>$CartResponseData->message));
                exit;
            } else {
                echo json_encode(array('status'=>403, 'message'=>$CartResponseData->message));
                exit;
            }
        } else {
            echo json_encode(array('status'=>403, 'message'=>"Unable to remove item."));
            exit;
        }
    }
    */

    public function removecartitem()
    {
        if (isset($_POST['item_id']) && $_POST['item_id'] != '') {

            $shopcode = SHOPCODE;
            $shop_id  = SHOP_ID;

            $quote_id = $this->session->userdata('QuoteId');
            $item_id  = $_POST['item_id'];

            $lang_code = '';
            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                $lang_code = $this->session->userdata('lcode');
            }

            $removeCartItemParam = array(
                'item_id'   => $item_id,
                'quote_id'  => $quote_id,
                'lang_code' => $lang_code
            );

            $CartResponseData = CartRepository::remove_cart_item($shopcode, $shop_id, $removeCartItemParam);

            if (!empty($CartResponseData) && $CartResponseData->statusCode == '200') {

                echo json_encode(array(
                    'status'  => 200,
                    'message' => !empty($CartResponseData->message)
                        ? $CartResponseData->message
                        : $this->lang->line('cart_item_removed_successfully')
                ));
                exit;

            } else {

                echo json_encode(array(
                    'status'  => 403,
                    'message' => !empty($CartResponseData->message)
                        ? $CartResponseData->message
                        : $this->lang->line('unable_to_remove_cart_item')
                ));
                exit;
            }

        } else {

            echo json_encode(array(
                'status'  => 403,
                'message' => $this->lang->line('unable_to_remove_cart_item')
            ));
            exit;
        }
    }

    public function updateWholeCartItems()
    {
        if ($this->session->userdata('QuoteId') !='') {
            if (isset($_POST)) {
                $shopcode = SHOPCODE;
                $shop_id = SHOP_ID;

                if ($this->session->userdata('LoginID')) {
                    $customer_id=$this->session->userdata('LoginID');
                    $session_id=$this->session->userdata('LoginToken');
                } else {
                    $customer_id='';
                    $session_id=$this->session->userdata('sis_session_id');
                }

				//$exceed_qty = 0;
                $quote_id=$this->session->userdata('QuoteId');
                $item_id = (isset($_POST['item_id']) && count($_POST['item_id']) > 0)?$_POST['item_id']:array();
                $quantity = (isset($_POST['quantity']) && count($_POST['quantity']) > 0)?$_POST['quantity']:array();
				$max_qty = (isset($_POST['max_qty']) && count($_POST['max_qty']) > 0)?$_POST['max_qty']:array();
                if (is_array($item_id) && count($item_id) > 0) {
                    $itemsArr = array();
                    foreach ($item_id as $key=>$val) {
                        $qty = $quantity[$key];
						$max_quantity = $max_qty[$key];
						if($qty > $max_quantity){
							echo json_encode(array('flag'=>0, 'msg'=>'The requested quantity exceeds the maximum quantity allowed in shopping cart.'));
                        	exit;
						}
                        $itemsArr[] = array('item_id'=>$val,'qty'=>$qty);
                    }
                }
                $itemsArr = json_encode($itemsArr);
                $postArr=array('session_id'=>$session_id,'quote_id'=>$quote_id,'cart_items'=>$itemsArr);
                $response = CartRepository::update_whole_cart($shopcode, $shop_id, $postArr);
                if (!empty($response) && isset($response)) {
                    $message = $response->message;
                    if (!empty($response) && isset($response) && $response->statusCode=='200') {
                        $this->AutoApplyCouponCode();
                        echo json_encode(array('flag'=>1, 'msg'=>$message));
                        exit;
                    } else {
                        echo json_encode(array('flag'=>0, 'msg'=>$message));
                        exit;
                    }
                } else {
                    echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
                    exit;
                }
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
                exit;
            }
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
            exit;
        }
    }

	public function updateCartItems()
    {
        if ($this->session->userdata('QuoteId') !='') {
            if (isset($_POST)) {

				$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    			$currency_symbol = $this->session->userdata('currency_symbol');
    			$default_currency_flag = $this->session->userdata('default_currency_flag');
				$price_main = $_POST['price'] ?? 0;
				if ($_POST['quantity'] > $_POST['max_qty']) {

					$previous_qty = (isset($_POST['previous_qty']))?$_POST['previous_qty']:1;
					$price = floatval(preg_replace('/[^\d.]/', '', $price_main));
					$total_price = $price*$previous_qty;
					$price = (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($total_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($total_price, 2));
					echo json_encode(array('flag'=>0, 'exceed_flag'=>1, 'previous_qty'=> $previous_qty, 'price'=> $price, 'msg'=>"The requested quantity exceeds the maximum quantity allowed in shopping cart."));
                    exit;
				}
                $shopcode = SHOPCODE;
                $shop_id = SHOP_ID;

                if ($this->session->userdata('LoginID')) {
                    $customer_id=$this->session->userdata('LoginID');
                    $session_id=$this->session->userdata('LoginToken');
                } else {
                    $customer_id='';
                    $session_id=$this->session->userdata('sis_session_id');
                }

                $quote_id=$this->session->userdata('QuoteId');
                $cart_item_id = (isset($_POST['item_id']))?$_POST['item_id']:'';

                $quantity = (isset($_POST['quantity']) && $_POST['quantity'] > 0)?$_POST['quantity']:1;
                $postArr=array('session_id'=>$session_id,'quote_id'=>$quote_id,'cart_item_id'=>$cart_item_id,'qty'=>$quantity);
                // echo "<pre>";
                // print_R($postArr);
                // die();
                $response = CartRepository::update_cart_item($shopcode, $shop_id, $postArr);
                if (!empty($response) && isset($response)) {
                    $message = $response->message;
                    if (!empty($response) && isset($response) && $response->statusCode=='200') {
						$price = floatval(preg_replace('/[^\d.]/', '', $price_main));
						$total_price = $price*$quantity;
						$price = (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($total_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($total_price, 2));
                        $this->AutoApplyCouponCode();
                        echo json_encode(array('flag'=>1, 'msg'=>$message, 'price'=> $price));
                        exit;
                    } else {
                        echo json_encode(array('flag'=>0, 'msg'=>$message));
                        exit;
                    }
                } else {
                    echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
                    exit;
                }
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
                exit;
            }
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to update item."));
            exit;
        }
    }

    public function ApplyCouponCode()
    {

        // $shopcode = SHOPCODE;
        // $shop_id = SHOP_ID;

        $quote_id = '';
        $session_id = '';
        $customer_id = '';

        if ($this->session->userdata('QuoteId')) {
            $quote_id=$this->session->userdata('QuoteId');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id=$this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id=$this->session->userdata('LoginID');
        }

        if (!empty($_POST)) {
            if (empty($_POST['coupon_code'])) {
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter valid coupon code."));
                exit;
            } else if($_POST['coupon_type'] ==''){
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter valid coupon code."));
                exit;
            }else {
                $coupon_code=$_POST['coupon_code'];
                $coupon_type = (isset($_POST['coupon_type']) && $_POST['coupon_type'] != '') ? $_POST['coupon_type'] : '';
                $couponArr = array('session_id'=>$session_id,'quote_id'=>$quote_id,'coupon_code'=>$coupon_code,'coupon_type'=>$coupon_type,'customer_id'=>$customer_id);
                //print_r($couponArr);die();
                $ResponseData = CartRepository::apply_coupon_code($couponArr);
                //print_r($ResponseData);die();
                if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode=='200') {
                    $success = $ResponseData->message;
                    echo json_encode(array('flag'=>1, 'msg'=>$success));
                    exit;
                } else {
                    $error = $ResponseData->message;
                    echo json_encode(array('flag'=>0, 'msg'=>$error));
                    exit;
                }
            }
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
            exit;
        }
    }

    public function removeCouponCode()
    {
        if (isset($_POST['coupon_code']) && $_POST['coupon_code']!='') {
            $coupon_code = $_POST['coupon_code'];
            $coupon_type = $_POST['coupon_type'];
            $quote_id=$this->session->userdata('QuoteId');

            $removeCouponArr=array('coupon_code'=>$coupon_code,'coupon_type'=>$coupon_type,'quote_id'=> $quote_id);
            $CartResponseData = CartRepository::remove_coupon_code($removeCouponArr);
            if (!empty($CartResponseData) && isset($CartResponseData) && $CartResponseData->statusCode=='200') {
                echo json_encode(array('flag'=>1, 'msg'=>$CartResponseData->message));
                exit;
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>$CartResponseData->message));
                exit;
            }
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to remove coupon."));
            exit;
        }
    }

    public function updateminicart()
{
    $lang_code = $this->input->post('lcode') ?: ($this->session->userdata('lcode') ?: 'en');

    $cc_post_arr = array(
        'shopcode'    => SHOPCODE,
        'shopid'      => SHOP_ID,
        'session_id'  => $this->session->userdata('sis_session_id'),
        'quote_id'    => $this->session->userdata('QuoteId') ?: '',
        'customer_id' => $this->session->userdata('LoginID') ?: '',
        'lcode'       => $lang_code 
    );

    $CartListingResp = CartRepository::cart_listing($cc_post_arr);

    // echo "<pre>";print_r($CartListingResp);die;

    // FIX: Fallback to an empty array so render() doesn't fail
    $cart_items = (isset($CartListingResp->CartData)) ? $CartListingResp->CartData : array();

    // This renders the actual HTML for the mini-cart
    (new MiniCartList($cart_items))->render();
}

    public function AutoApplyCouponCode()
    {

        $quote_id = '';
        $session_id = '';
        $customer_id = '';

        if ($this->session->userdata('QuoteId')) {
            $quote_id=$this->session->userdata('QuoteId');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id=$this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id=$this->session->userdata('LoginID');
        }

        // coupon code

        /*if (!empty($_POST)) {
            if (empty($_POST['coupon_code'])) {
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter valid coupon code."));
                exit;
            } else if($_POST['coupon_type'] ==''){
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter valid coupon code."));
                exit;
            }else {*/


                $couponValue = 0;
                $qty_identifier = 'coupon_code_discount';
                $QtyApiResponse = GlobalRepository::get_custom_variable($qty_identifier);
                if (!empty($QtyApiResponse) && isset($QtyApiResponse) && $QtyApiResponse->statusCode=='200') {
                    
                    $RowCV = $QtyApiResponse->custom_variable;
                    $couponValue = $RowCV->value ?? $couponValue;

                }

                if ($couponValue == 1) { 

                    $coupon_code = 'WDIM04';
                    $_POST['coupon_type'] = 0;
                    /*$coupon_code=$_POST['coupon_code'];
                    $coupon_type = (isset($_POST['coupon_type']) && $_POST['coupon_type'] != '') ? $_POST['coupon_type'] : '';
                    $couponArr = array('session_id'=>$session_id,'quote_id'=>$quote_id,'coupon_code'=>$coupon_code,'coupon_type'=>$coupon_type,'customer_id'=>$customer_id);*/

                    $coupon_type = (isset($_POST['coupon_type']) && $_POST['coupon_type'] != '') ? $_POST['coupon_type'] : '';
                    $couponArr = array('session_id'=>$session_id,'quote_id'=>$quote_id,'coupon_code'=>$coupon_code,'coupon_type'=>$coupon_type,'customer_id'=>$customer_id);

                    $ResponseData = CartRepository::apply_coupon_code($couponArr);

                }

               // print_r($ResponseData);die();
                /*if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode=='200') {
                    $success = $ResponseData->message;
                    echo json_encode(array('flag'=>1, 'msg'=>$success));
                    exit;
                } else {
                    $error = $ResponseData->message;
                    echo json_encode(array('flag'=>0, 'msg'=>$error));
                    exit;
                }*/
            /*}
        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
            exit;
        }*/
    }
}
