<?php

defined('BASEPATH') or exit('No direct script access allowed');



class MyOrdersController_new extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';

        

        if ($LogindID == '') {
            redirect(BASE_URL.'customer/login');
        }

        $this->load->library("pagination");

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }

    /*

    public function getProfileDetails(){

        $data['PageTitle']= 'My Profile - Personal Information';

        $LoginID = $_SESSION['LoginID'];



        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        $data['side_tab'] = 'account_info';



        $apiUrl = '/webshop/customer_get_personal_info'; //customer_get_personal_info

        $postArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'customer_id'=>$LoginID);

        $response= $this->restapi->post_method($apiUrl,$postArr);

        //echo '<pre>';print_r($response);//exit;

        if($response->is_success=='true'){

            $data['customerData'] = $response->customerData;

        }



        $apiUrl2 = '/webshop/get_table_data'; //get_table_data

        $table = 'country_master';

        $flag = 'own';

        $postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table,'database_flag'=>$flag);

        $response2= $this->restapi->post_method($apiUrl2,$postArr2);

        //echo '<pre>';print_r($response2);//exit;

        if($response2->is_success=='true'){

            $data['countryList'] = $response2->tableData;

        }

        $this->load->view('myprofile/personal_info', $data);

    }

    */

    public function getOrderItems()
    {
        $order_id = $this->input->post('order_id');

        // STEP 1: GET ALL B2B ORDER IDS MAPPED BY PRODUCT
        $b2b_items = $this->db->select('
                boi.product_id AS product_id,
                boi.order_id AS b2b_order_id,
                boi.item_id AS b2b_item_id
            ')
            ->from('b2b_orders AS bo')
            ->join('b2b_order_items AS boi', 'boi.order_id = bo.order_id', 'left')
            ->where('bo.webshop_order_id', $order_id)
            ->get()
            ->result();

        // MAP B2B ITEMS BY PRODUCT
        $b2b_map = [];
        foreach ($b2b_items as $bi) {
            if (!isset($b2b_map[$bi->product_id])) {
                $b2b_map[$bi->product_id] = [];
            }
            $b2b_map[$bi->product_id][] = [
                'b2b_order_id' => $bi->b2b_order_id,
                'b2b_item_id'  => $bi->b2b_item_id
            ];
        }

        // STEP 2: FETCH RETURNABLE ITEMS
        /*$items = $this->db->select('
                soi.item_id,
                soi.product_id,
                soi.product_name,
                soi.qty_ordered,
                soi.price,
                soi.barcode,
                p.base_image
            ')
            ->from('sales_order_items AS soi')
            ->join('products AS p', 'p.id = soi.product_id', 'left')
            ->where('soi.order_id', $order_id)
            ->where('soi.can_be_returned', 1)
            ->get()
            ->result();*/

            $items = $this->db->select('
                soi.item_id,
                soi.product_id,
                soi.product_name,
                soi.qty_ordered,
                soi.price,
                soi.barcode,
                soi.product_type,
                soi.product_variants,
                COALESCE(p.base_image, pp.base_image) AS base_image
            ')
            ->from('sales_order_items AS soi')
            ->join('products AS p', 'p.id = soi.product_id', 'left')
            ->join('products AS pp', 'pp.id = p.parent_id', 'left')
            ->where('soi.order_id', $order_id)
            ->where('soi.can_be_returned', 1)
            ->get()
            ->result();

        if (empty($items)) {
            echo json_encode(['status' => 0, 'html' => '<p>No returnable items found.</p>']);
            return;
        }

        $html = '';
      
        foreach ($items as $item) {
            $b2b_order_id = 0;
            $b2b_item_id  = 0;
            $is_requested = false; // default to NOT requested

            // STEP 3: CHECK IF THIS PRODUCT HAS B2B ITEMS AND IF ANY ARE ALREADY REQUESTED
            if (isset($b2b_map[$item->product_id]) && is_array($b2b_map[$item->product_id])) {
                foreach ($b2b_map[$item->product_id] as $pair) {
                    // Check if THIS B2B ITEM has a return request
                    // order_item_id refers to b2b_order_items.item_id
                    $return_exists = $this->db
                        ->from('sales_order_return_items')
                        ->where('order_item_id', $pair['b2b_item_id'])
                        ->count_all_results();

                    // Check if THIS B2B ITEM has a replacement request
                    // order_item_id refers to b2b_order_items.item_id
                    $replacement_exists = $this->db
                        ->from('sales_order_replacement_items')
                        ->where('order_item_id', $pair['b2b_item_id'])
                        ->count_all_results();

                    // If this B2B item already has request, mark as requested and stop looking
                    if ($return_exists > 0 || $replacement_exists > 0) {
                        $is_requested = true;
                        $b2b_order_id = $pair['b2b_order_id'];
                        $b2b_item_id  = $pair['b2b_item_id'];
                        break;
                    }
                }

                // If no B2B item is requested yet, use the first one for new requests
                /*if (!$is_requested && !empty($b2b_map[$item->product_id])) {
                    $pair = $b2b_map[$item->product_id][0];
                    $b2b_order_id = $pair['b2b_order_id'];
                    $b2b_item_id  = $pair['b2b_item_id'];
                }*/

                    if (!$is_requested && !empty($b2b_map[$item->product_id])) {
    // Take first unused item and REMOVE it (important)
    $pair = array_shift($b2b_map[$item->product_id]);

    $b2b_order_id = $pair['b2b_order_id'];
    $b2b_item_id  = $pair['b2b_item_id'];
}
            }
           
            $product_image_url = !empty($item->base_image)
                ? PRODUCT_THUMB_IMG . $item->base_image
                : base_url('assets/images/noimage.png');

            $html .= '<div class="return-item-box" style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
                        <div class="row align-items-center">

                            <div class="col-md-2 text-center">';
           

            if (!$is_requested) {
                $html .= '<input type="checkbox"
                            class="return_chk"
                            data-id="' . $item->item_id . '"
                            data-b2b="' . $b2b_order_id . '"
                            data-b2b-item="' . $b2b_item_id . '"
                            id="return_chk_' . $item->item_id . '">';
            } else {
                $html .= '<div class="requested-label" style="color:#888;font-size:13px;">Requested</div>';
            }

            $html .= '<input type="hidden"
                        id="b2b_order_id_' . $item->item_id . '"
                        value="' . $b2b_order_id . '">
                    </div>

                    <div class="col-md-3 text-center">
                        <img src="' . $product_image_url . '" style="max-width:100px;">
                    </div>

                    <div class="col-md-7">
                        <p><b>' . $item->product_name . '</b></p>';
            
            // Display variants if available
            if (isset($item->product_variants) && $item->product_variants != '' && $item->product_type == 'conf-simple') {
                $product_variants = json_decode($item->product_variants);
                $variants = [];
                
                if (isset($product_variants) && $product_variants != '') {
                    foreach ($product_variants as $pk => $single_variant) {
                        foreach ($single_variant as $key => $val) {
                            $variants[] = $key . ': ' . $val;
                        }
                    }
                }
                
                if (!empty($variants)) {
                    $html .= '<p style="color: #666; font-size: 12px; margin: 5px 0;">' . implode(', ', $variants) . '</p>';
                }
            }
            
            $html .= '<p>Qty Ordered: ' . $item->qty_ordered . '</p>

                        <input type="number"
                            min="1"
                            max="' . $item->qty_ordered . '"
                            id="return_qty_' . $item->item_id . '"
                            class="form-control"
                            value="1"
                            ' . ($is_requested ? 'disabled' : '') . '>
                    </div>

                </div>
            </div>';
        }

        echo json_encode(['status' => 1, 'html' => $html]);
    }




    public function addReplacementRequest()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['selected_item'])) {

            $order_id = $_POST['order_id'];
            $increment_id = $_POST['increment_id'];
            $b2b_order_id = $_POST['b2b_order_id'];
            $flag = 'replacement'; // 🔥 Important
            $selected_item = $_POST['selected_item'];

            $postArr = array(
                'order_id' => $order_id,
                'increment_id' => $increment_id,
                'selected_item' => $selected_item,
                'flag' => $flag,
                'customer_id' => $LoginID,
                'b2b_order_id'=>$b2b_order_id
            );

            $response = OrdersRepository::replacement_order_request($shopcode, $shop_id, $postArr);
            // echo "<pre>";print_r($response);die();
            if (!empty($response) && isset($response) && $response->statusCode=='200') {
                $redirect_to = base_url().'customer/my-orders';
                echo json_encode(array('flag' => 1, 'msg' => $response->message, 'redirect_to' => $redirect_to));
                exit;
            }

            echo json_encode(array('flag' => 0, 'msg' => $response->message));
            exit;
        }

        echo json_encode(array('flag' => 0, 'msg' => "Unable to post request."));
        exit;
    }


    public function getOrders()
    {
        $data['PageTitle']= 'My Profile - Orders';
        $data['side_tab'] = 'my_orders';

        $this->resetPrintDetails();
        $this->template->load('myprofile/my_orders', $data);
    }



    public function returnOrderDetail()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'my_orders';
        $return_order_id=$this->uri->segment(4);

        if (empty($return_order_id)) {
			redirect('customer/my-orders');
			return;
		}

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
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$OrderData = $response->OrderData;

			$data['OrderData'] = $OrderData;
		} else {
			$data['OrderData'] = array();
		}

		$this->template->load('myprofile/return_order_detail', $data);
	}

  public function download_document($order_id)
{
    
   echo "<pre>";print_r($order_id); exit;
        
    $this->load->helper('download');
    $this->load->model('CommonModel');

    $order = $this->CommonModel->get_document_by_order_id($order_id);

    if ($order && !empty($order->document_file))
    {
        
        
        $file_path = FCPATH . 'var/merchant/uploads/order_documents/' . $order->document_file;

        echo "<pre>";print_r($file_path); exit;
        
        
        
        if (file_exists($file_path))
        {
            $data = file_get_contents($file_path);
            force_download($order->document_file, $data);
            exit;
        }
        else
        {
            die('File not found: ' . $file_path);
        }
    }
    else
    {
        die('No document available.');
    }
}



    public function setTempPrintDetails()
    {
		$this->session->set_userdata('reason_for_return', $_POST['reason_for_return']);
        $this->session->set_userdata('refund_payment_mode', $_POST['refund_payment_mode'] ?? '');
        $this->session->set_userdata('bank_name', $_POST['bank_name']);
        $this->session->set_userdata('bank_branch', $_POST['bank_branch']);
        $this->session->set_userdata('bic_swift', $_POST['bic_swift']);
        $this->session->set_userdata('ifsc_iban', $_POST['ifsc_iban']);
        $this->session->set_userdata('bank_acc_no', $_POST['bank_acc_no']);

        echo "success";
        exit;
    }

    public function resetPrintDetails()
    {
        $this->session->unset_userdata('reason_for_return');
        $this->session->unset_userdata('refund_payment_mode');
        $this->session->unset_userdata('bank_name');
        $this->session->unset_userdata('bank_branch');
        $this->session->unset_userdata('bic_swift');
        $this->session->unset_userdata('ifsc_iban');
        $this->session->unset_userdata('bank_acc_no');
    }

    public function printReturnOrder()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $return_order_id=$this->uri->segment(3);

        if ($return_order_id!='') {
            $postPrintArr = array('return_order_id'=>$return_order_id);
            $printresponse= OrdersRepository::return_order_print($shopcode, $shop_id, $postPrintArr);

            $response= OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);

            $return_address_field='order_return_address';
            $ReturnApiResponse = GlobalRepository::get_custom_variable($shopcode, $shop_id, $return_address_field);
            if (!empty($ReturnApiResponse) && isset($ReturnApiResponse) && $ReturnApiResponse->statusCode=='200') {
                $ReturnValue=$ReturnApiResponse->custom_variable;
                $return_address=$ReturnValue->value;
                $data['returnAddress']=$return_address;
            } else {
                $return_address='';
                $data['returnAddress']=$return_address;
            }
            /*end return address*/

            if ($response->is_success=='true') {
				$data['OrderData']= $response->OrderData;
            } else {
                $data['OrderData'] = array();
            }

            $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);

            $this->template->load('myprofile/print_return_order', $data);
        } else {
            redirect('customer/my-orders');
        }
    }



    public function addReturnRequest()
    {
        // echo "<pre>";print_r($_POST);die;
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['selected_item'])) {
            $order_id=$_POST['order_id'];
            $increment_id=$_POST['increment_id'];
            // $b2b_order_id = $_POST['b2b_order_id'];

            $flag='return';
            $selected_item=$_POST['selected_item'];
            // echo "<pre>";print_r($selected_item);die;
            $postArr=array('order_id'=>$order_id,'increment_id'=>$increment_id,'selected_item'=>$selected_item,'flag'=>$flag,'customer_id'=>$LoginID);
            $response = OrdersRepository::return_order_request($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response) && $response->statusCode=='200') {
				$redirect_to=base_url().'customer/my-orders';
                echo json_encode(array('flag'=>1, 'msg'=> $response->message,'redirect_to'=>$redirect_to));
                exit;
            }

			echo json_encode(array('flag'=>0, 'msg'=> $response->message));
			exit;
		}

		echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
		exit;
	}

    public function show_tracking_details()
    {

        if (isset($_POST['order_id'])) {
            $order_id=$_POST['order_id'];
            $postArr=array('order_id'=>$order_id);
            $response = OrdersRepository::tracking_details_request($postArr);
            // print_r($response);die();

            if (!empty($response) && isset($response) && $response->statusCode=='200') {
                $data['tracking_data'] = $response->tracking_data;
                //  print_r($response);die();

                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            } else {
                $data['tracking_data'] = $response->tracking_data;
                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            }

        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
            exit;
        }

    }



    public function confirmReturn()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['reason_for_return']) && (isset($_POST['item_qty']) &&  count($_POST['item_qty'])>0)) {
            $postArr=array('customer_id'=>$LoginID);

            foreach ($_POST as $postkey=>$post_field) {
                $postArr[$postkey]=$post_field;
            }

            $response = OrdersRepository::return_order_confirm($shopcode, $shop_id, $postArr);
            if (!empty($response)) {
                if ($response->statusCode=='200') {
					$redirect_to=base_url().'customer/my-orders/return-detail/'.$response->return_order_id;
                    echo json_encode(array('flag'=>1, 'msg'=> $response->message,'redirect_to'=>$redirect_to));
                    exit;
                }

				echo json_encode(array('flag'=>0, 'msg'=> $response->message));
				exit;
			}

			echo json_encode(array('flag'=>0, 'msg'=>'Unable to post request.'));
			exit;
		}

		echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
		exit;
	}

    public function cancelOrder()
    {

		if (!isset($_POST)) {
			echo json_encode(array('flag' => 0, 'msg' => "Please two enter all mandatory / compulsory fields."));
			exit;
		}

		if (empty($_POST['cancel_reason']) && empty($_POST['order_id'])) {
			echo json_encode(array('flag' => 0, 'msg' => "Please  one enter all mandatory / compulsory fields."));
			exit;
		}
		$reason_for_cancel = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';
		$order_id = $_POST['order_id'];

        $webshopname = 'India Mags';


        $shop_logo = SITE_LOGO;
		$data['webshop_details'] = CommonRepository::get_webshop_details();
		if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
            $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);

		}


		$site_logo = '<a href="' . base_url() . '" style="color:#1E7EC8;">
					<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
		$lang_code = '';
		if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
			$lang_code = $this->session->userdata('lcode');
		}
        if(!empty($this->session->userdata('CURRENCY_CODE'))){
            $CURRENCY_CODE=$this->session->userdata('CURRENCY_CODE');
        }
        else{
            $CURRENCY_CODE = 'IN';
        }
		$postArr = array('order_id' => $order_id, 'reason_for_cancel' => $reason_for_cancel, 'site_logo' => $site_logo, 'currency_code' => $CURRENCY_CODE, 'lang_code' => $lang_code);
		
        $response = OrdersRepository::cancel_order_request($postArr);
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$redirect_to = base_url() . 'customer/my-orders/';
			echo json_encode(array('flag' => 1, 'msg' => $response->message, 'redirect_to' => $redirect_to));
			exit;
		}

		echo json_encode(array('flag' => 0, 'msg' => $response->message));
		exit;
	}
}
