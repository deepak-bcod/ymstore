<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyGuestOrdersController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->library('email');
    }


    public function trackingOrder()
    {

        $data['PageTitle'] = 'Order-Track';

        $this->template->load('guestprofile/tracking_order_details');
    }

    public function show_guest_tracking_details()
    {
        // $shopcode = SHOPCODE;
        // $shop_id = SHOP_ID;

        if (isset($_POST['formData'])) {
            $order_number = $_POST['formData'];
            $postArr = array('order_id' => $order_number);

            $response = OrdersRepository::tracking_guest_order_details($postArr);

            if (!empty($response) && isset($response) && $response->statusCode == '200') {

                $tracking_data = $response;
                echo json_encode(array('flag' => 1, 'data' => $tracking_data));
                exit;
            } else {

                echo json_encode(array('flag' => 2, 'msg' => "No order Found "));
                exit;
                //$this->template->load('myprofile/order_tracking_detail_ajax', $tracking_data);
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Unable to post request."));
            exit;
        }
    }


    public function getGuestOrders()
    {

        $data['PageTitle'] = 'Orders Details';
        $this->template->load('guestprofile/order_details', $data);
    }


    
	public function getEmagazine($encoded_id) {
        $decoded_id = base64_decode($encoded_id); // Decode the ID
        $order_id = preg_replace('/[^0-9]/', '', $decoded_id); // Keep only digits
        // print_r($order_id);
        // die;

        $orderDetails = OrdersRepository::my_order_detail($order_id);
        // echo "<pre>";print_r($orderDetails);die;

        if (!$orderDetails) {
            die("Invalid access.");
        }
    
        $orderItems = $orderDetails->OrderData->order_items;  // Get the order items array
    
        if (!empty($orderItems)) {
            $product_id = $orderItems[0]->parent_product_id;  // Get the product_id of the first item
            // You can loop through $orderItems if there are multiple items, e.g.
            foreach ($orderItems as $item) {
                $product_id = $item->parent_product_id;
                $base_image = $item->base_image;

                // Process further as needed
            }
        }
        $this->session->set_userdata('product_id', $product_id);
        
        // Generate OTP and store it in the session
        $otp = $this->generateOtp($orderDetails->OrderData->customer_email);
        $this->session->set_userdata('otp', $otp);
        $this->session->set_userdata('otp_valid', false); // Initially, OTP is not valid
        
        // Passing the encoded ID in the view
        $data['encoded_id'] = $encoded_id;
        $data['product_id'] = $product_id;
        $data['base_image'] = $base_image;
    
        $this->load->view('otp_form', $data); // Load OTP form with modal
    }
    
    public function validateOtp() {
        // Retrieve 'encoded_id' from the GET request
        $encoded_id = $this->input->get('encoded_id');
        
        // Retrieve the OTP and product ID from the POST request
        $enteredOtp = trim($this->input->post('otp'));
        $storedOtp = trim($this->session->userdata('otp'));
        $product_id = $this->input->post('product_id');
        
        // Validate the OTP
        if ($enteredOtp === $storedOtp) {
            // OTP is valid
            $this->session->set_userdata('otp_valid', true);
        
            // Load PDF viewer with product images
            $data['images'] = $this->getImagesForProduct($product_id);
            $data['product_id'] = $product_id;
        
            $this->load->view('pdf_viewer', $data);
        } else {
            // If OTP is incorrect, regenerate the eMagazine
            $this->getEmagazine($encoded_id);
        }
    }
    
    
    
    
    private function generateOtp($email) {
        $otp = rand(100000, 999999); // Generate 6-digit OTP
        
        $htmlContent = "Your OTP is: $otp";
        $this->load->library('email');
        
        $config = array(
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 465,
            'smtp_user' => 'care@indiamags.com',
            'smtp_pass' => 'rlhbawwjqezsiqjn',
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'smtp_crypto' => 'ssl',
        );
        
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");
        $this->email->from('care@indiamags.com'); // change it to yours
        $this->email->to($email); // change it to yours
        $this->email->subject('Your OTP for Digital Magazine Access');
        $this->email->message($htmlContent);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return $otp;
        } else {
            echo 'Failed to send the email. Error: ' . $this->email->print_debugger();
        }

        
    }
    
    private function getImagesForProduct($product_id) {
        $imagePath = SIS_SERVER_PATH . "uploads/digit_pdf/{$product_id}/";
        // print_r($imagePath);die;
        $images = glob($imagePath . "*.png");
        return $images;
    }

    protected function getMerchantsForRequest($order_id, $selected_item = [], $created_ids = [], $type = 'return')
    {
        $merchants = [];
        $b2b_order_ids = [];
        $b2b_item_ids = [];
        $direct_publisher_ids = [];

        if (!empty($selected_item) && is_array($selected_item)) {
            foreach ($selected_item as $item) {
                if (!empty($item['b2b_order_id'])) {
                    $b2b_order_ids[] = (int)$item['b2b_order_id'];
                }
                if (!empty($item['item_id'])) {
                    $b2b_item_ids[] = (int)$item['item_id'];
                }
                if (!empty($item['publisher_id'])) {
                    $direct_publisher_ids[] = (int)$item['publisher_id'];
                }
            }
        }

        if (!empty($created_ids) && is_array($created_ids)) {
            $q = $this->db->select('order_id')
                ->from('sales_order_return')
                ->where_in('return_order_id', $created_ids)
                ->get();
            if ($q && is_object($q)) {
                foreach ($q->result() as $cr) {
                    if (!empty($cr->order_id)) {
                        $b2b_order_ids[] = (int)$cr->order_id;
                    }
                }
            }
        }

        if (!empty($b2b_item_ids)) {
            $item_q = $this->db->select('boi.order_id, bo.publisher_id')
                ->from('b2b_order_items boi')
                ->join('b2b_orders bo', 'bo.order_id = boi.order_id', 'left')
                ->where_in('boi.item_id', array_unique($b2b_item_ids))
                ->get();
            if ($item_q && is_object($item_q)) {
                foreach ($item_q->result() as $ir) {
                    if (!empty($ir->order_id)) {
                        $b2b_order_ids[] = (int)$ir->order_id;
                    }
                    if (!empty($ir->publisher_id)) {
                        $direct_publisher_ids[] = (int)$ir->publisher_id;
                    }
                }
            }
        }

        $b2b_order_ids = array_values(array_unique(array_filter($b2b_order_ids)));

        if (!empty($b2b_order_ids)) {
            $bo_q = $this->db->select('order_id, publisher_id, order_barcode, increment_id')
                ->from('b2b_orders')
                ->where_in('order_id', $b2b_order_ids)
                ->get();
            if ($bo_q && is_object($bo_q)) {
                foreach ($bo_q->result_array() as $bo) {
                    if (!empty($bo['publisher_id'])) {
                        $pub_id = (int)$bo['publisher_id'];
                        $merchants[$pub_id] = [
                            'publisher_id'  => $pub_id,
                            'b2b_order_id'  => (int)$bo['order_id'],
                            'order_barcode' => !empty($bo['order_barcode']) ? $bo['order_barcode'] : (!empty($bo['increment_id']) ? $bo['increment_id'] : ''),
                            'increment_id'  => !empty($bo['increment_id']) ? $bo['increment_id'] : (!empty($bo['order_barcode']) ? $bo['order_barcode'] : '')
                        ];
                    }
                }
            }
        }

        if (empty($merchants) && !empty($order_id)) {
            $fb_q = $this->db->select('order_id, publisher_id, order_barcode, increment_id')
                ->from('b2b_orders')
                ->where('webshop_order_id', $order_id)
                ->limit(1)
                ->get();
            if ($fb_q && is_object($fb_q)) {
                $fallback = $fb_q->row_array();
                if (!empty($fallback) && !empty($fallback['publisher_id'])) {
                    $pub_id = (int)$fallback['publisher_id'];
                    $merchants[$pub_id] = [
                        'publisher_id'  => $pub_id,
                        'b2b_order_id'  => (int)$fallback['order_id'],
                        'order_barcode' => !empty($fallback['order_barcode']) ? $fallback['order_barcode'] : (!empty($fallback['increment_id']) ? $fallback['increment_id'] : ''),
                        'increment_id'  => !empty($fallback['increment_id']) ? $fallback['increment_id'] : (!empty($fallback['order_barcode']) ? $fallback['order_barcode'] : '')
                    ];
                }
            }
        }

        return $merchants;
    }

    public function addReturnRequest()
    {
        $LoginID = 'guest';
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['selected_item'])) {
            $order_id = $_POST['order_id'];
            $increment_id = $_POST['increment_id'];
            $flag = 'return';
            $selected_item = $_POST['selected_item'];

            $postArr = array('order_id' => $order_id, 'increment_id' => $increment_id, 'selected_item' => $selected_item, 'flag' => $flag, 'customer_id' => $LoginID);
            // print_R($selected_item);die();
            $response = OrdersRepository::return_order_request($shopcode, $shop_id, $postArr);
            if (!empty($response) && isset($response) && $response->statusCode == '200') {
                try {
                    $created_ret_ids = [];
                    if (!empty($response->return_order_id)) {
                        $created_ret_ids = is_array($response->return_order_id)
                            ? $response->return_order_id
                            : [$response->return_order_id];
                    }

                    $target_merchants = $this->getMerchantsForRequest($order_id, $selected_item, $created_ret_ids, 'return');
                    foreach ($target_merchants as $merchant_id => $m_info) {
                        $actual_b2b_order_id = (int)$m_info['b2b_order_id'];
                        $this->db->insert('notifications', [
                            'type'           => 'return',
                            'recipient_type' => 'merchant',
                            'recipient_id'   => (int)$merchant_id,
                            'title'          => 'New Return Request',
                            'message'        => 'Shopper has submitted a return request order #' . $increment_id . '.',
                            'data'           => json_encode([
                                'order_id'     => $order_id,
                                'b2b_order_id' => $actual_b2b_order_id,
                                'increment_id' => $increment_id
                            ]),
                            'is_read'        => 0,
                            'created_at'     => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Guest return notification error: ' . $e->getMessage());
                }

                $message = $response->message;
                $first_ret_id = is_array($response->return_order_id) ? reset($response->return_order_id) : $response->return_order_id;
                $redirect_to = base_url() . 'customer/my-guest-orders/return-detail/' . $first_ret_id;
                echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect_to' => $redirect_to));
                exit;
            } else {
                $message = $response->message;
                echo json_encode(array('flag' => 0, 'msg' => $message));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Unable to post request."));
            exit;
        }
    }

    public function confirmReturn()
    {
        $LoginID = 'guest';

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        //print_r($_POST);

        if (isset($_POST['order_id']) && isset($_POST['reason_for_return']) && (isset($_POST['item_qty']) &&  count($_POST['item_qty']) > 0)) {
            $order_id = $_POST['order_id'];

            $postArr = array('customer_id' => $LoginID);
            foreach ($_POST as $postkey => $post_field) {
                $postArr[$postkey] = $post_field;
            }


            $response = OrdersRepository::return_order_confirm($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response) && $response->statusCode == '200') {
                $message = $response->message;
                $redirect_to = base_url() . 'customer/my-guest-orders/return-detail/' . $response->return_order_id;
                echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect_to' => $redirect_to));
                exit;
            } else {
                $message = $response->message;
                echo json_encode(array('flag' => 0, 'msg' => $message));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Unable to post request."));
            exit;
        }
    }

    public function cancelOrder()
    {
        $LoginID    =    'guest';
        // print_r($_POST);die();
        if (isset($_POST)) {
            if (empty($_POST['cancel_reason']) && empty($_POST['order_id'])) {
                echo json_encode(array('flag' => 0, 'msg' => "Please  one enter all mandatory / compulsory fields."));
                exit;
            } else {
                $shopcode = SHOPCODE;
                $shop_id = SHOP_ID;
                $reason_for_cancel = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';
                $order_id = $_POST['order_id'];
                $site_logo = '';
                $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
                if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                    $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                }

                $webshopname = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';
                $shop_logo = SITE_LOGO . '/' . $shop_logo;
                $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
							<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
						</a>';
                $postArr = array('order_id' => $order_id, 'reason_for_cancel' => $reason_for_cancel, 'site_logo' => $site_logo, 'currency_code' => CURRENCY_CODE);
                // echo "<pre>";print_r($postArr);die();
                $response = OrdersRepository::cancel_order_request($shopcode, $shop_id, $postArr);
                if (!empty($response) && isset($response) && $response->is_success == 'true') {
                    $redirect_to = base_url() . 'customer/my-orders/';
                    echo json_encode(array('flag' => 1, 'msg' => $response->message, 'redirect_to' => $redirect_to));
                    exit;
                } else {
                    echo json_encode(array('flag' => 0, 'msg' => $response->message));
                    exit;
                }
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Please two enter all mandatory / compulsory fields."));
            exit;
        }
    }

    public function returnOrderDetail()
    {
        $data['PageTitle'] = 'My Profile - Return Order';

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'my_orders';
        $return_order_id = $this->uri->segment(4);

        if ($return_order_id != '') {
            $shopDataflag = GlobalRepository::get_fbc_users_shop()?->result;
            if (!empty($shopDataflag)) {
                $data['shop_flag'] = $shopDataflag->shop_flag;
                $data['country_code'] = $shopDataflag->country_code;
            } else {
                $data['shop_flag'] = '';
                $data['country_code'] = '';
            }

            $data['online_stripe_payment_refund'] = GlobalRepository::get_custom_variable($shopcode, $shop_id, 'online_stripe_payment_refund', true) ?? 'no';

            $response = OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);
            if (!empty($response) && $response->is_success == 'true') {
                $OrderData = $response->OrderData;
                $data['OrderData'] = $OrderData;
            } else {
                $data['OrderData'] = array();
            }

            $this->template->load('guestprofile/return_order_detail', $data);
        } else {
            redirect('customer/my-orders');
        }
    }

    public function printReturnOrder()
    {
        $data['PageTitle'] = 'My Profile - Return Order';

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $return_order_id = $this->uri->segment(3);
        if ($return_order_id != '') {
            $response = OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);
            /*new return address*/
            $return_address_field = 'order_return_address';
            $ReturnApiResponse = GlobalRepository::get_custom_variable($shopcode, $shop_id, $return_address_field);
            if (!empty($ReturnApiResponse) && isset($ReturnApiResponse) && $ReturnApiResponse->statusCode == '200') {
                $ReturnValue = $ReturnApiResponse->custom_variable;
                $return_address = $ReturnValue->value;
                $data['returnAddress'] = $return_address;
            } else {
                $return_address = '';
                $data['returnAddress'] = $return_address;
            }
            /*end return address*/
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $OrderData = $response->OrderData;
                $data['OrderData'] = $OrderData;
            } else {
                $data['OrderData'] = array();
            }

            $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
            $this->template->load('guestprofile/print_return_order', $data);
        } else {
            redirect('customer/my-orders');
        }
    }

    public function show_tracking_details()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id'])) {
            $order_id = $_POST['order_id'];
            $postArr = array('order_id' => $order_id);
            $response = OrdersRepository::tracking_details_request($shopcode, $shop_id, $postArr);
            if (!empty($response) && isset($response) && $response->statusCode == '200') {
                $tracking_data = $response;
                $this->template->load('myprofile/order_tracking_detail_ajax', $tracking_data);
            } else {
                $tracking_data = $response;
                $this->template->load('myprofile/order_tracking_detail_ajax', $tracking_data);
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "Unable to post request."));
            exit;
        }
    }
}
