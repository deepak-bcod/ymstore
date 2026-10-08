<?php

defined('BASEPATH') or exit('No direct script access allowed');



class MyProfileController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {

            redirect(BASE_URL . 'customer/login');
        }
        $this->load->model('CommonModel');

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }



    public function getProfileDetails()

    {

        $data['PageTitle'] = 'My Profile - Personal Information';

        $LoginID = $_SESSION['LoginID'];





        $data['side_tab'] = 'account_info';



        $postArr = array('customer_id' => $LoginID);

        $response = CustomerRepository::customer_get_personal_info($postArr);

        if (!empty($response) && isset($response) && $response->is_success == 'true') {

            $data['customerData'] = $customerData = $response->customerData;

            $data['profilePercentage'] = $response->profile_percentage;
        }



        $table = 'country_master';

        $flag = 'own';

        $postArr2 = array('table_name' => $table, 'database_flag' => $flag);

        $response2 = CommonRepository::get_table_data($postArr2, 3600);

        if (!empty($response2) && isset($response2) &&  $response2->is_success == 'true') {

            $data['countryList'] = $response2->tableData;
        }



        // $shopDataflag = GlobalRepository::get_fbc_users_shop();

        // $data['shop_flag'] = $shopDataflag->result->shop_flag ?? '';



        $identifier = 'restricted_access';

        $ApiResponse = GlobalRepository::get_custom_variable($identifier);

        if (!empty($ApiResponse) && isset($ApiResponse) && $ApiResponse->statusCode == '200') {

            $RowCV = $ApiResponse->custom_variable;

            $restricted_access = $RowCV->value;
        } else {

            $restricted_access = 'no';
        }

        $data['restricted_access'] = $restricted_access;



        // $apiUrl = '/webshop/customer_get_profile_completion'; //customer_get_personal_info

        // $postArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'customer_id'=>$LoginID);

        // $response= $this->restapi->post_method($apiUrl,$postArr);

        // //echo '<pre>';print_r($response);//exit;

        // if($response->is_success=='true'){

        // 	$data['customerData'] = $response->customerData;

        // }



        $this->template->load('myprofile/personal_info', $data);
    }



    public function updateCustomerInfo()

    {

        if (!empty($_POST)) {

            if (empty($_POST['first_name']) || empty($_POST['last_name'])) {

                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

                exit;
            }

            // else if(!empty($_POST['dob']) && !preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",$_POST['dob'])){

            // 	echo json_encode(array('flag'=>0, 'msg'=>"Date format is incorrect."));

            // 	exit;

            // }

            $LoginID = $_SESSION['LoginID'];

            // $shopcode = SHOPCODE;

            // $shop_id = SHOP_ID;



            $first_name = $_POST['first_name'];

            $last_name = $_POST['last_name'];

            $gender = isset($_POST['gender']) ? $_POST['gender'] : '';

            // $mobile_no = $_POST['mobile_no'];

            $country_code = $_POST['country'];

            $dob = ($_POST['dob'] != '') ? date("Y-m-d", strtotime($_POST['dob'])) : '';

            $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';

            $gst_no = isset($_POST['gst_no']) ? $_POST['gst_no'] : '';



            $postArr = array(

                'customer_id' => $LoginID,

                'first_name' => $first_name,

                'last_name' => $last_name,

                'gender' => $gender,

                // 'mobile_no'=>$mobile_no,

                'country_code' => $country_code,

                'dob' => $dob,

                'company_name' => $company_name,

                'gst_no' => $gst_no,

                'ip' => $_SERVER['REMOTE_ADDR']

            );

            $response = CustomerRepository::customer_update_personal_info($postArr);

            //echo '<pre>';print_r($response);exit;

            $message = $response->message;

            if (!empty($response) && isset($response) && $response->is_success == 'true') {

                $sessionArr = array('FirstName' => $first_name, 'LastName' => $last_name);

                $this->session->set_userdata($sessionArr);



                echo json_encode(array('flag' => 1, 'msg' => $message));

                exit;
            } else {

                echo json_encode(array('flag' => 0, 'msg' => $message));

                exit;
            }
        }
    }

    public function helpDesk()
    {
        $customer_id = $_SESSION['LoginID'];
        $data['PageTitle'] = 'Create New Ticket';
        $data['side_tab']  = 'help_desk';
        $data['orders'] = $this->CommonModel->get_customer_orders($customer_id, 50, 0);
        $data['help_desk_data'] = $this->CommonModel->get_help_desk_data($customer_id);
        $data['merchants'] = $this->db->select('id, publication_name')->where('status', 1)->order_by('publication_name', 'ASC')->get('publisher')->result();
        // echo "<pre>";print_r($data['help_desk_data']);die;
        // echo "<pre>";print_r($data);die;
        $this->template->load('myprofile/help_desk', $data);
    }

    public function messaging()
    {
        // 1. Safety Check: Ensure user is logged in
        $customer_id = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : null;
        if (!$customer_id) {
            redirect('login'); // or handle as you prefer
        }

        $data['PageTitle'] = 'Messages';
        $data['side_tab']  = 'messaging';

        // 2. Build the Query
        $this->db->select('pq.*');
        
        // Check if any message in the whole thread has a reply
        $this->db->select('(SELECT COUNT(*) 
                            FROM product_questions 
                            WHERE product_id = pq.product_id 
                            AND merchant_reply IS NOT NULL 
                            AND merchant_reply != "") as reply_count');

        $this->db->from('product_questions pq');
        
        // 3. The Join filters for ONLY the latest record per product
        $this->db->join('(SELECT product_id, MAX(id) as max_id 
                        FROM product_questions 
                        WHERE customer_id = ' . $this->db->escape($customer_id) . ' 
                        GROUP BY product_id) as latest', 
                        'pq.id = latest.max_id');

        $this->db->where('pq.customer_id', $customer_id);
        
        // 4. Execute
        $query = $this->db->get();
        $data['messaging_data'] = $query->result();

        $this->template->load('myprofile/messaging', $data);
    }

    public function get_order_details()
    {
        $order_id = intval($this->input->post('order_id'));
        $merchant_id = $this->input->post('merchant_id');

        if (empty($order_id)) {
            echo json_encode([
                'status'    => 0,
                'merchants' => [],
                'products'  => [],
                'msg'       => 'Order ID is required'
            ]);
            return;
        }

        $customer_id = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 0);
        $order = $this->db->select('order_id')
            ->from('sales_order')
            ->where('order_id', $order_id)
            ->where('customer_id', $customer_id)
            ->get()
            ->row();

        if (!$order) {
            echo json_encode([
                'status'    => 0,
                'merchants' => [],
                'products'  => [],
                'msg'       => 'Invalid order'
            ]);
            return;
        }

        $merchants = $this->CommonModel->get_order_merchants($order_id);
        $products  = $this->CommonModel->get_order_merchant_products($order_id, $merchant_id);

        echo json_encode([
            'status'    => 1,
            'merchants' => $merchants,
            'products'  => $products
        ]);
    }

    public function get_order_merchants()
    {
        $order_id = intval($this->input->post('order_id'));
        $customer_id = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 0);
        $merchants = [];

        if (!empty($order_id)) {
            if (!empty($customer_id)) {
                $order = $this->db->select('order_id')
                    ->from('sales_order')
                    ->where('order_id', $order_id)
                    ->where('customer_id', $customer_id)
                    ->get()
                    ->row();
                if ($order) {
                    $merchants = $this->CommonModel->get_order_merchants($order_id);
                }
            } else {
                $merchants = $this->CommonModel->get_order_merchants($order_id);
            }
        }

        echo json_encode([
            'status'    => 1,
            'merchants' => $merchants
        ]);
    }

    public function get_order_products()
    {
        $order_id = $this->input->post('order_id');
        $merchant_id = $this->input->post('merchant_id');

        $products = $this->CommonModel->get_order_merchant_products($order_id, $merchant_id);

        $publisher_id = '';
        if (!empty($products) && isset($products[0]->publisher_id)) {
            $publisher_id = $products[0]->publisher_id;
        }

        echo json_encode([
            'status'       => 1,
            'products'     => $products,
            'publisher_id' => $publisher_id
        ]);
    }

    public function get_merchant_products()
    {
        $order_id = $this->input->post('order_id');
        $merchant_id = $this->input->post('merchant_id');
        $products = [];
        if (!empty($merchant_id)) {
            $products = $this->CommonModel->get_order_merchant_products($order_id, $merchant_id);
        }
        echo json_encode([
            'status'   => 1,
            'products' => $products
        ]);
    }

    public function helpDeskPost()
    {
        $subject        = $this->input->post('subject');
        $category       = $this->input->post('category_id');
        $message        = $this->input->post('message');
        $order_id       = $this->input->post('order_id');
        $product_id     = $this->input->post('product_id');
        $merchant_id    = $this->input->post('merchant_id');
        $ticket_id      = $this->input->post('ticket_id');

        $customer_id = $_SESSION['LoginID'];

        if (empty($subject)) {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('err_subject_required')]);
            return;
        }

        // if (strlen($subject) < 5) {
        //     echo json_encode(['flag' => 0, 'msg' => 'Subject must be at least 5 characters.']);
        //     return;
        // }

        if (empty($category)) {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('err_support_type_required')]);
            return;
        }

        if (empty($message)) {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('err_message_required')]);
            return;
        }

        


        // Handle file upload
        $attachment_name = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != '') {
            $config['upload_path']   = './uploads/help_desk_attachment/';
            $config['allowed_types'] = 'jpg|jpeg|png|pdf';
            $config['max_size']      = 1024; // 1MB
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('attachment')) {
                $fileData = $this->upload->data();
                $attachment_name = $fileData['file_name'];
            } else {
                echo json_encode(['flag' => 0, 'msg'  => strip_tags($this->upload->display_errors())]);
                return;
            }
        }

        if ($category == 1) {
            // Category 1: Merchant Support Ticket
            $merchant_input = $this->input->post('merchant_id');
            if (!empty($merchant_input)) {
                $merchant_id = $merchant_input;
            }

            if (empty($merchant_id) && !empty($product_id)) {
                $product = $this->db
                    ->select('publisher_id')
                    ->where('id', $product_id)
                    ->get('products')
                    ->row();
                
                if ($product && !empty($product->publisher_id)) {
                    $merchant_id = $product->publisher_id;
                }
            }

            if (empty($merchant_id) && !empty($order_id)) {
                $orderItem = $this->db
                    ->select('soi.publisher_id')
                    ->from('sales_order_items as soi')
                    ->join('sales_order as so', 'so.order_id = soi.order_id', 'left')
                    ->where('so.order_id', $order_id)
                    ->limit(1)
                    ->get()
                    ->row();

                $merchant_id = $orderItem ? $orderItem->publisher_id : null;
            }

            if (empty($merchant_id) && !empty($ticket_id)) {
                $prev_ticket = $this->db->select('merchant_id')->where('ticket_id', $ticket_id)->get('help_desk')->row();
                if ($prev_ticket && !empty($prev_ticket->merchant_id)) {
                    $merchant_id = $prev_ticket->merchant_id;
                }
            }

            if (empty($merchant_id)) {
                $merchants_list = $this->db->select('id')->where('status', 1)->get('publisher')->result();
                if (count($merchants_list) === 1) {
                    $merchant_id = $merchants_list[0]->id;
                }
            }

            if (empty($merchant_id)) {
                echo json_encode(['flag' => 0, 'msg' => $this->lang->line('select_merchant') ?: 'Please select a merchant.']);
                return;
            }
        } else {
            // Category 2: Yellow Market / Admin Support Ticket
            // Isolate completely from any merchant
            $merchant_id = null;
        }

        // Server-side validation: Ensure Merchant and Product belong to the selected Order
        if (!empty($order_id)) {
            $customer_id = $this->session->userdata('LoginID');
            $order_check = $this->db->select('order_id')
                ->from('sales_order')
                ->where('order_id', $order_id)
                ->where('customer_id', $customer_id)
                ->get()
                ->row();

            if (!$order_check) {
                echo json_encode(['flag' => 0, 'msg' => 'Invalid order selected.']);
                return;
            }

            // 1. If merchant_id is set, ensure it belongs to this order
            if (!empty($merchant_id)) {
                $order_merchants = $this->CommonModel->get_order_merchants($order_id);
                $valid_merchant_ids = array_map(function($m) { return (string)$m->id; }, $order_merchants);
                if (!in_array((string)$merchant_id, $valid_merchant_ids, true)) {
                    echo json_encode(['flag' => 0, 'msg' => 'The selected merchant does not belong to the selected order.']);
                    return;
                }
            }

            // 2. If product_id is set, ensure it belongs to this order (and merchant if specified)
            if (!empty($product_id)) {
                $order_products = $this->CommonModel->get_order_merchant_products($order_id, $merchant_id);
                $valid_product_ids = array_map(function($p) { return (string)$p->product_id; }, $order_products);
                if (!in_array((string)$product_id, $valid_product_ids, true)) {
                    echo json_encode(['flag' => 0, 'msg' => 'The selected product does not belong to the selected order.']);
                    return;
                }
            }
        } elseif (!empty($merchant_id) && !empty($product_id)) {
            // When no order is selected, ensure product belongs to the merchant
            $prod_check = $this->db->select('publisher_id')->where('id', $product_id)->get('products')->row();
            if ($prod_check && !empty($prod_check->publisher_id) && (string)$prod_check->publisher_id !== (string)$merchant_id) {
                echo json_encode(['flag' => 0, 'msg' => 'The selected product does not belong to the selected merchant.']);
                return;
            }
        }

        // REPLACE THIS:
        if ($ticket_id != null) {
            $ticket_id = $ticket_id;
            $merchant_id = $merchant_id;
        }else{
            // WITH THIS (To always generate a new ID):
            $max_ticket = $this->db
                ->select('MAX(CAST(ticket_id AS UNSIGNED)) as max_id')
                ->get('help_desk')
                ->row();

            if ($max_ticket && $max_ticket->max_id > 0) {
                $next_number = $max_ticket->max_id + 1;
                $ticket_id = str_pad($next_number, 4, '0', STR_PAD_LEFT);
            } else {
                $ticket_id = '0001';
            }
        }

        // Insert new ticket message (or follow-up)
        $postArr = [
            'ticket_id'    => $ticket_id,
            'subject'      => $subject,
            'category'     => $category,
            'message'      => $message,
            'order_id'     => $order_id,
            'products'     => $product_id,
            'merchant_id'  => $merchant_id,
            'customer_id'  => $customer_id,
            'attachment'   => $attachment_name,
            'admin_reply'  => '',  // empty for customer
            'status'       => 0,   // Not opened
            'created_at'   => time(),
            'updated_at'   => time(),
            'ip'           => $_SERVER['REMOTE_ADDR'],
        ];

        $this->db->insert('help_desk', $postArr);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $is_followup = !empty($this->input->post('ticket_id'));
            $notif_subtype = $is_followup ? 'ticket_reply' : 'new_ticket';

            $product_name = '';
            if ($product_id) {
                $product_row = $this->db->select('name')->where('id', $product_id)->get('products')->row();
                if ($product_row) {
                    $product_name = $product_row->name;
                }
            }
            $product_name = html_entity_decode($product_name, ENT_QUOTES, 'UTF-8');

            if ($category == 1) {
                // ===========================================
                // NOTIFY MERCHANT ONLY (Category 1: Merchant)
                // ===========================================
                if (!empty($merchant_id)) {
                    if ($is_followup) {
                        $notif_msg = !empty($product_name)
                            ? 'A new reply has been submitted for your product "' . $product_name . '" by Shopper on ticket #' . $ticket_id . '.'
                            : 'A new reply on help desk ticket #' . $ticket_id . ' has been submitted by Shopper.';
                        $notif_title = 'Help Desk Ticket Reply';
                    } else {
                        $notif_msg = !empty($product_name)
                            ? 'A new help desk ticket has been submitted for your product "' . $product_name . '" by Shopper.'
                            : 'A new help desk ticket #' . $ticket_id . ' has been submitted by Shopper.';
                        $notif_title = 'New Help Desk Ticket';
                    }

                    $this->db->insert('notifications', [
                        'type'           => 'helpdesk',
                        'subtype'        => $notif_subtype,
                        'recipient_type' => 'merchant',
                        'recipient_id'   => $merchant_id,
                        'title'          => $notif_title,
                        'message'        => $notif_msg,
                        'data'           => json_encode([
                                                'ticket_id'   => $ticket_id,
                                                'order_id'    => $order_id,
                                                'product_id'  => $product_id,
                                                'customer_id' => $customer_id
                                            ]),
                        'is_read'        => 0,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($category == 2) {
                // ===========================================
                // NOTIFY ADMIN ONLY (Category 2: Yellow Market)
                // ===========================================
                $notif_msg = $is_followup
                    ? 'A new reply on help desk ticket #' . $ticket_id . ' has been submitted by Shopper.'
                    : 'A new help desk ticket #' . $ticket_id . ' has been submitted by Shopper.';
                $notif_title = $is_followup ? 'Help Desk Ticket Reply' : 'New Help Desk Ticket';

                $this->db->insert('notifications', [
                    'type'           => 'helpdesk',
                    'subtype'        => $notif_subtype,
                    'recipient_type' => 'admin',
                    'recipient_id'   => 1,
                    'title'          => $notif_title,
                    'message'        => $notif_msg,
                    'data'           => json_encode([
                                            'ticket_id'   => $ticket_id,
                                            'order_id'    => $order_id,
                                            'product_id'  => $product_id,
                                            'customer_id' => $customer_id
                                        ]),
                    'is_read'        => 0,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s')
                ]);
            }

            echo json_encode(['flag' => 1, 'msg' => $this->lang->line('ticket_submit_success'), 'ticket_id' => $ticket_id]);
        } else {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('ticket_submit_failed')]);
        }
    }

    public function messagingPost()
    {
        $name     = $this->input->post('name');
        $category    = $this->input->post('category');
        $message     = $this->input->post('message');
        $product_id  = $this->input->post('product_id');
        $merchant_id    = $this->input->post('merchant_id');
        $customer_id = $_SESSION['LoginID'];

        if (empty($message)) {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('err_required_field')]);
            return;
        }

        

        
        $postArr = [
            'name'      => $name,
            'category'     => $category,
            'message'      => $message,
            'product_id'     => $product_id,
            'merchant_id'   => $merchant_id,
            'customer_id'  => $customer_id,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
            'ip'           => $_SERVER['REMOTE_ADDR'],
        ];

        $this->db->insert('product_questions', $postArr);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            echo json_encode(['flag' => 1, 'msg' => $this->lang->line('reply_submit_success')]);
        } else {
            echo json_encode(['flag' => 0, 'msg' => $this->lang->line('reply_submit_failed')]);
        }
    }

    public function viewTicket($order_id, $ticket_id,$product_id = null)
    {
        $customer_id = $_SESSION['LoginID'];

        
        $this->db->where('order_id', $order_id);

        if (!empty($product_id)) {
            $this->db->where('products', $product_id);
        }

        if (!empty($ticket_id)) {
            $this->db->where('ticket_id', $ticket_id);
        }

        $this->db->order_by('GREATEST(created_at, IFNULL(updated_at, created_at)) ASC', NULL, FALSE);
        $help_desk_data = $this->db->get('help_desk')->result();

      
        $order = $this->CommonModel->get_order_by_id($order_id);

       
        $product = null;
        if (!empty($product_id)) {
            $product = $this->CommonModel->get_product_by_order($order_id, $product_id);
        }

        
        $data['help_desk_data'] = $help_desk_data;
        $data['order'] = $order;
        $data['product'] = $product;

        $this->load->view('myprofile/help_desk_conversation', $data);
    }

    public function viewMessage($product_id) {

        $customer_id = $_SESSION['LoginID'];
        $this->db->where('customer_id', $customer_id);
        $this->db->where('product_id', $product_id);
        $this->db->update('product_questions', [
            'is_read' => 1,
            'status' => 'answered'
        ]); 

        // Fetch the updated data for the view
        $this->db->where('customer_id', $customer_id);
        $this->db->where('product_id', $product_id);
        $this->db->order_by('id', 'ASC');
        $messaging_data = $this->db->get('product_questions')->result();
        
        $data['messaging_data'] = $messaging_data;
        $this->load->view('myprofile/messaging_conversation', $data);
    }



    public function changeEmail()

    {



        $LoginID = $_SESSION['LoginID'];

        if (empty($_POST)) {

            $data['LoginID'] = $_SESSION['LoginID'];

            $View = $this->load->view('myprofile/change-email-popup', $data, true);

            $this->output->set_output($View);
        } else {

            if (isset($_POST)) {

                if (empty($_POST['new_email'])) {

                    echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

                    exit;
                } else {



                    $email = $_SESSION['EmailID'];

                    $postArr = array('customer_id' => $LoginID);

                    $response = CustomerRepository::customer_get_personal_info($postArr);



                    $customerData = $response->customerData;

                    if ($_POST['new_email'] != $customerData->email_id) {

                        $new_email = $_POST['new_email'];

                        $postArr1 = array('email_id' => $new_email);

                        $EmailExits = CustomerRepository::customer_email_exits($postArr1);



                        if (!isset($EmailExits->customerData)) {

                            $changePostArr = array('email' => $new_email, 'customer_id' => $LoginID);

                            $resetResponse = CustomerRepository::change_email($changePostArr);

                            if (!empty($resetResponse) && isset($resetResponse)) {

                                $message = $resetResponse->message;

                                if ($response->is_success == 'true') {

                                    $this->session->set_userdata('EmailID', $new_email);

                                    echo json_encode(array('flag' => 1, 'msg' => $message));

                                    exit;
                                } else {

                                    echo json_encode(array('flag' => 0, 'msg' => $message));

                                    exit;
                                }
                            } else {

                                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_something_wrong')));

                                exit;
                            }
                        } else {

                            echo json_encode(array("flag" => 2, "msg" => $this->lang->line('err_new_email_exists')));
                        }
                    } else {

                        echo json_encode(array("flag" => 2, "msg" => $this->lang->line('err_email_exists')));

                        exit;
                    }
                }
            }
        }
    }



    public function changePassword()

    {

        $LoginID    =    $_SESSION['LoginID'];

        if (empty($_POST)) {

            $data['LoginID']    =    $_SESSION['LoginID'];

            $View = $this->load->view('myprofile/change_password_popup', $data, true);

            $this->output->set_output($View);
        } else {

            if (isset($_POST)) {

                if (empty($_POST['old_password']) || empty($_POST['new_password']) || empty($_POST['conf_password'])) {

                    echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

                    exit;
                } elseif ($_POST['new_password'] != $_POST['conf_password']) {

                    echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_confirm_password')));

                    exit;
                } else {



                    $password = $_POST['new_password'];

                    $email = $_SESSION['EmailID'];



                    $postArr = array('customer_id' => $LoginID);

                    $response = CustomerRepository::customer_get_personal_info($postArr);



                    if (!empty($response) && isset($response)) {

                        $message = $response->message;

                        if ($response->is_success == 'true') {

                            $customerData = $response->customerData;

                            if (md5($_POST['old_password']) != $customerData->password) {

                                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_old_password')));

                                exit;
                            }



                            $resetPostArr = array('email' => $email, 'password' => $password);

                            $resetResponse = LoginRepository::reset_password($resetPostArr);

                            if (!empty($resetResponse) && isset($resetResponse)) {

                                $message = $resetResponse->message;

                                if ($response->is_success == 'true') {

                                    echo json_encode(array('flag' => 1, 'msg' => $message));

                                    exit;
                                } else {

                                    echo json_encode(array('flag' => 0, 'msg' => $message));

                                    exit;
                                }
                            } else {

                                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_something_wrong')));

                                exit;
                            }
                        } else {

                            echo json_encode(array('flag' => 0, 'msg' => $message));

                            exit;
                        }
                    } else {

                        echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_something_wrong')));

                        exit;
                    }
                }
            } else {

                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

                exit;
            }
        }
    }



    public function getAddressDetails()

    {

        $data['PageTitle'] = 'My Profile - Manage Address';

        $data['side_tab'] = 'my_address';



        $this->template->load('myprofile/manage_address', $data);
    }



    public function openAddressPopup()
    {

        $LoginID    =    $_SESSION['LoginID'];

        //print_r($_POST);exit;

        if (isset($_POST)) {

            $data['LoginID'] = $_SESSION['LoginID'];

            $data['flag'] = $_POST['flag'];

            $data['customer_id'] = $_POST['customer_id'];

            $data['address_id']    = $_POST['address_id'];



            if ($_POST['flag'] == 'edit') {

                $table = 'customers_address';

                $flag = 'own';

                $where = 'id = ? AND customer_id = ?';

                $order_by = 'ORDER BY id DESC';

                $params = array($_POST['address_id'], $LoginID);

                $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);

                $response = CommonRepository::get_table_data($postArr);

                if (!empty($response) && isset($response) && $response->is_success == 'true') {

                    $data['addressData'] = $response->tableData;
                }
            } else {

                $table = 'customers_address';

                $flag = 'own';

                $where = 'customer_id = ?';

                $order_by = 'ORDER BY id DESC';

                $params = array($LoginID);

                $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);

                $response = CommonRepository::get_table_data($postArr);

                if (!empty($response) && isset($response) && $response->is_success == 'true') {

                    $data['addressData'] = $response->tableData;
                }
            }



            $postArr = array('table_name' => 'country_master', 'database_flag' => 'own');

            $response = CommonRepository::get_table_data($postArr, 3600);

            if (!empty($response) && isset($response) && $response->is_success == 'true') {

                $data['countryList'] = $response->tableData;
            }

            $postArr3 = array('table_name' => 'country_state_master_in', 'database_flag' => 'main');

            $response3 = CommonRepository::get_table_data($postArr3, 3600);

            if (!empty($response3) && isset($response3) && $response3->is_success == 'true') {

                $data['stateList'] = $response3->tableData;
            }


            $table = 'city_master';
            $flag = 'main';
            $postArr4 = array('table_name' => $table, 'database_flag' => $flag);
            $response4 = CommonRepository::get_table_data($postArr4, 3600);
            if (!empty($response4) && isset($response4) && $response4->is_success == 'true') {
                $data['cityList'] = $response4->tableData;
            }
            // echo "<pre>";print_r($data);die;
            $View = $this->load->view('myprofile/address_popup', $data, true);
            $this->output->set_output($View);
        }
    }


    public function getCities()
    {
        $state_id = $this->input->post('state_id');
        $cities = $this->CommonModel->getCitiesByState($state_id);
        echo json_encode(['status' => 'success', 'cities' => $cities]);
    }

    public function addEditAddress()
    {
        
        $lang_code = '';

        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {

            $lang_code = $this->session->userdata('lcode');
        }

        if (empty($_POST)  || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['address_line1']) || empty($_POST['city']) || empty($_POST['pincode']) || empty($_POST['country'])) {

            echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

            exit;
        }



        $LoginID = $_SESSION['LoginID'];

        $address_id = $_POST['address_id'];



        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        $first_name = $_POST['first_name'];

        $last_name = $_POST['last_name'];

        $address_line1 = $_POST['address_line1'];

        $address_line2 = $_POST['address_line2'];

        $city = $_POST['city'];

        $state = ($_POST['country'] === 'IN' ? $_POST['state_dp'] : $_POST['state']);

        $pincode = $_POST['pincode'];

        $mobile_no = $_POST['mobile_no'];

        $country = $_POST['country'];



        $company_name = $_POST['company_name'] ?? '';

        $vat_no = $_POST['vat_no'] ?? '';

        $consulation_no = $_POST['consulation_no'] ?? '';

        $res_company_name = $_POST['res_company_name'] ?? '';

        $res_company_address = $_POST['res_company_address'] ?? '';



        $vat_vies_valid_flag = $_POST['vat_flag'] ?? ''; //valid 1,  not valid 0





        $postArr = array(

            'customer_id' => $LoginID,

            'first_name' => $first_name,

            'last_name' => $last_name,

            'address_line1' => $address_line1,

            'address_line2' => $address_line2,

            'city' => $city,

            'state' => $state,

            'pincode' => $pincode,

            'mobile_no' => $mobile_no,

            'country_code' => $country,

            'customer_address_id' => $address_id,

            'company_name' => $company_name,

            'vat_no' => $vat_no,

            'consulation_no' => $consulation_no,

            'res_company_name' => $res_company_name,

            'res_company_address' => $res_company_address,

            'vat_vies_valid_flag' => $vat_vies_valid_flag,

            'lang_code' => $lang_code,

        );
        if ($postArr['country_code'] == 'MU') {
			$postArr['country_code'] = 'mauritius';
		}
        $full_address = $postArr['address_line1'] . ' ' .
                $postArr['address_line2'] . ' ' .
                $postArr['city'] . ' ' .
                $postArr['state'] . ' ' .
                $postArr['country_code'] . ' ' .
                $postArr['pincode'];


        $encoded_address = urlencode($full_address);
        $apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
        $geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

        // Safer to use cURL instead of file_get_contents
        $response = file_get_contents($geocodeURL);
        $responseData = json_decode($response, true);

        if (!empty($responseData['results'][0]['geometry']['location'])) {
            $latitude  = $responseData['results'][0]['geometry']['location']['lat'];
            $longitude = $responseData['results'][0]['geometry']['location']['lng'];
            $postArr['latitude']  = $latitude;
            $postArr['longitude'] = $longitude;
        } else {
            $postArr['latitude']  = null;
            $postArr['longitude'] = null;
        }
        if ($postArr['country_code'] == 'mauritius') {
			$postArr['country_code'] = 'MU';
		}
        $response = CustomerRepository::customer_address_add_edit($shopcode, $shop_id, $postArr);

        if (!empty($response) && isset($response)) {
            $message = $response->message;
            if ($response->is_success == 'true') {
                echo json_encode(array('flag' => 1, 'msg' => $message));
                exit;
            }
            echo json_encode(array('flag' => 0, 'msg' => $message));
            exit;
        }



        echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_something_wrong')));

        exit;
    }



    public function makeDefaultAddress()
    {

        $LoginID    =    $_SESSION['LoginID'];

        //print_r($_POST);exit;

        if (isset($_POST) && $_POST['address_id'] != '') {

            $LoginID = $_SESSION['LoginID'];

            $address_id = $_POST['address_id'];



            $shopcode = SHOPCODE;

            $shop_id = SHOP_ID;


            $lang_code = '';

            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                $lang_code = $this->session->userdata('lcode');
            }


            $postArr = array('customer_id' => $LoginID, 'customer_address_id' => $address_id, 'lang_code' => $lang_code);

            $response = CustomerRepository::customer_address_setdefault($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response)) {

                $message = $response->message;

                if ($response->is_success == 'true') {

                    echo json_encode(array('flag' => 1, 'msg' => $message));

                    exit;
                } else {

                    echo json_encode(array('flag' => 0, 'msg' => $message));

                    exit;
                }
            } else {

                echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_something_wrong')));

                exit;
            }
        } else {

            echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_mandatory')));

            exit;
        }
    }



    public function removeAddress()
    {
        $LoginID = $_SESSION['LoginID'];
        $address_id = $this->input->post('address_id');
        if ($address_id) {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;
            $lang_code = '';

            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                $lang_code = $this->session->userdata('lcode');
            }

            $postArr = array('customer_id' => $LoginID, 'customer_address_id' => $address_id, 'lang_code' => $lang_code);
            $response = CustomerRepository::customer_address_delete($shopcode, $shop_id, $postArr);
            if (!empty($response) && $response->is_success == 'true') {
                echo json_encode(array('flag' => 1, 'msg' => $response->message));
            } else {
                echo json_encode(array('flag' => 0, 'msg' => $response->message ?? $this->lang->line('err_delete_failed')));
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => $this->lang->line('err_invalid_address')));
        }
        exit;
    }
}
