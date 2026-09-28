<?php



class PublisherController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->model('PublisherModel');
		$this->load->model('CommonModel');
		$this->load->model('Subscription_model');


    }



    public function publisherList()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (

                !empty($this->session->userdata('userPermission')) &&

                !in_array('database/publishers', $this->session->userdata('userPermission'))

            ) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {



            // ✅ check if query string has status

            $status = $this->input->get('status');



            if ($status !== null && $status !== '') {

                $data['getPublishers'] = $this->PublisherModel->get_publishers_by_status($status);

            } else {

                $data['getPublishers'] = $this->PublisherModel->get_publishers();

            }



            $data['PageTitle'] = 'Publishers';

            $data['side_menu'] = 'publishers';



            $this->load->view('publishers/publishers_list', $data);



        } else {

            return redirect('/');

        }

}



    // public function check()

    // {

    //     echo $_SERVER['DOCUMENT_ROOT'];die;

    // }

    public function addPublishers()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (!empty($this->session->userdata('userPermission')) && !in_array('database/publishers', $this->session->userdata('userPermission'))) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $data['plans'] = $this->Subscription_model->get_plans();

            $data['PageTitle'] = 'Publisher Add';

            $data['side_menu'] = 'publisher';

            $this->load->view('publishers/publishers_add', $data);

        } else {

            return redirect('/');

        }

    }



    public function submitPublisher()

    {


        
        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $publisher_id = $_POST['publisher_id'];



            if (empty($_POST['email']) || empty($_POST['publication_name']) || empty($_POST['vendor_name']) || empty($_POST['commision_percent'])) {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                exit;

            } elseif (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {



                echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));

                exit;

            } elseif (($this->PublisherModel->checkEmailidExit($_POST["email"])) != 0 && $publisher_id == '') {



                echo json_encode(array('flag' => 0, 'msg' => "Email id already exist."));

                exit;

            } elseif ($this->PublisherModel->checkPublicationName($_POST["publication_name"]) != 0 && $publisher_id == '') {

                // print_r($this->PublisherModel->checkPublicationName($_POST["publication_name"]));

                // die();

                echo json_encode(array('flag' => 0, 'msg' => "Publication  Name already exist."));

                exit;

            } else {



                if ($publisher_id != '') {



                    if ($_POST['passwordCheck'] == 'check' && empty($_POST["password"])) {

                        echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                        exit;

                    } elseif ($this->PublisherModel->checkEmailIdExitDuringUpdate($_POST["email"], $publisher_id) != 0) {

                        echo json_encode(array('flag' => 0, 'msg' => "Email id already exist."));

                        exit;

                    } else {



                        $isPasswordChecked = $_POST['passwordCheck'];

                        $hashPassword = "";

                        $emails = $this->input->post('emails');

                        $emailsArray = preg_replace('/\s+| /u', '', explode(',', $emails));

                        $implodedString = implode(', ', $emailsArray);

                        $updateData = array();

                        if ($isPasswordChecked == 'check') {

                            $hashPassword = md5($_POST["password"]);

                            $updateData = array(

                                'email'                => $_POST['email'],

                                'cc_email'                => $implodedString,

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'shipment_type'       => $_POST['shipment_type'],

                                'password'            => $hashPassword,

                                'publication_name'    => $_POST['publication_name'],

                                'vendor_name'        => $_POST['vendor_name'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'split_id'          => $_POST['split_id'],

                                'phone_no'             => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'        => $_POST['description'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                            $update_publisher_payment_details_data = array(

                                'publisher_id'                => $publisher_id,

                                'bank_name'            => $_POST['bank_name'],

                                'bank_branch_number'            => $_POST['bank_branch_number'],

                                'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                                'beneficiary_name'    => $_POST['beneficiary_name'],

                                'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],
                                'bank_address'        => $_POST['bank_address'],
                                'iban'        => $_POST['iban'],


                                'created_by'        => $SISA_ID,

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR']

                            );

                        } else {

                            $updateData = array(

                                'email'                => $_POST['email'],

                                'cc_email'                => $implodedString,

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'shipment_type'       => $_POST['shipment_type'],


                                'publication_name'    => $_POST['publication_name'],

                                'vendor_name'        => $_POST['vendor_name'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'split_id'          => $_POST['split_id'],

                                'phone_no'             => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'        => $_POST['description'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                            $update_publisher_payment_details_data = array(

                                'publisher_id'                => $publisher_id,

                                'bank_name'            => $_POST['bank_name'],

                                'bank_branch_number'            => $_POST['bank_branch_number'],

                                'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                                'beneficiary_name'    => $_POST['beneficiary_name'],

                                'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],
                                'bank_address'        => $_POST['bank_address'],
                                'iban'        => $_POST['iban'],

                                'created_by'        => $SISA_ID,

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR']

                            );

                        }

                        $is_success = $this->PublisherModel->update_publishers($updateData, $publisher_id, $update_publisher_payment_details_data);

                        if (!empty($_POST['plan_id'])) {
                            $this->Subscription_model->assign_or_update_plan($publisher_id, (int)$_POST['plan_id'], $_SERVER['REMOTE_ADDR']);
                        }

                        $lang = isset($_POST['lang_flag']) ? strtolower($_POST['lang_flag']) : 'english';

                        if ($is_success) {
                            
                            // if ($_POST['status'] == 1) {

                            //     $TempVars1 = array("##NAME##", "##EMAILID##");
                            //     $DynamicVars2 = array($_POST['publication_name'], $_POST['email']);

                            //     if ($lang == "french") {
                            //         $merchantTemplateId = 'merchant-approval-fr';
                            //     } else {
                            //         $merchantTemplateId = 'merchant-approval';
                            //     }
                            //     $merchant = $this->db->get_where('publisher', ['id' => $publisher_id])->row();
                            //     $merchantMailSent = $this->CommonModel->sendCommonHTMLEmail($_POST['email'], $merchantTemplateId, $TempVars1, $DynamicVars2);
                                
                            //     // echo "<pre>";
                            //     // print_r($merchant);die;

                            //     // ✅ Post to Facebook page now
                            //     // $this->CommonModel->post_to_facebook($merchant);
                            //     // $this->CommonModel->post_to_instagram($merchant);
                            //     // $this->CommonModel->post_to_linkedin($merchant);

                            //     // $page_id = '791397577399444';              // store in DB
                            //     // $page_access_token = null; // store in DB

                            //     // $message = "🛍️ New eShop\n\n"
                            //     //         . "Open For Business – Visit Our Online Shop!\n\n"
                            //     //         . "Welcome to {$merchant->trade_name} 🎉\n"
                            //     //         . "We’re excited to announce the launch of our new online shop!\n\n"
                            //     //         . "🛒 Explore our products\n"
                            //     //         . "💼 Shop anytime, anywhere\n"
                            //     //         . "🚀 Experience hassle-free ordering\n\n"
                            //     //         . "👉 Visit now: https://mu.yellowmarkets.com/";

                            //     // $link = "https://mu.yellowmarkets.com/";

                            //     // // Optional: schedule for future (example: +1 hour)
                            //     // // $schedule_time = time() + 3600;
                            //     // $schedule_time = null; // post immediately

                            //     // $fbResponse = $this->CommonModel->post_to_facebook(
                            //     //     $page_id,
                            //     //     $page_access_token,
                            //     //     $message,
                            //     //     $link,
                            //     //     $schedule_time
                            //     // );

                            // }
                            $merchant = $this->db->get_where('publisher', ['id' => $publisher_id])->row();

                            if ($_POST['status'] == 1) {
                                // $page_id = '791397577399444';              // store in DB
                                // $page_access_token = 'EAANNZCdZBMeksBQzcIAwXN7ihE9ma7ZCZCFnZCWLqDZCuZBnrG9zJ4yMFGhQhyTXiFiNK2zrvLx9YAXqEMhXmS8pBmZAqruiebPhqIHG6Sd4XZCvRZAeKs42TpKRYZC2j3qiRcZB39zOQlkem0ZAtLrHMRTBMpAoeadxZCTFGzo1mffn6JZAkkKAIRl9Ymapf6pbeKiP5w4tJznF48C0YIkAouARemI'; // store in DB

                                // $message = "🛍️ New eShop\n\n"
                                //         . "Open For Business – Visit Our Online Shop!\n\n"
                                //         . "Welcome to {$merchant->trade_name} 🎉\n"
                                //         . "We’re excited to announce the launch of our new online shop!\n\n"
                                //         . "🛒 Explore our products\n"
                                //         . "💼 Shop anytime, anywhere\n"
                                //         . "🚀 Experience hassle-free ordering\n\n"
                                //         . "👉 Visit now: https://mu.yellowmarkets.com/";

                                // $link = "https://mu.yellowmarkets.com/";

                                // // Optional: schedule for future (example: +1 hour)
                                // // $schedule_time = time() + 3600;
                                // $schedule_time = null; // post immediately

                                // $fbResponse = $this->CommonModel->post_to_facebook(
                                //     $page_id,
                                //     $page_access_token,
                                //     $message,
                                //     $link,
                                //     $schedule_time
                                // );

                                $TempVars1 = array("##NAME##", "##EMAILID##");
                                $DynamicVars2 = array($_POST['publication_name'], $_POST['email']);

                                // FRONTEND REGISTER USER
                                if ($merchant->frontend_register == "frontend_register") {

                                    if ($lang == "french") {
                                        $merchantTemplateId = 'website-merchant-approval-fr';
                                    } else {
                                        $merchantTemplateId = 'website-merchant-approval';
                                    }

                                } 
                                // ADMIN / BACKEND REGISTER USER
                                else {

                                    if ($lang == "french") {
                                        $merchantTemplateId = 'merchant-approval-fr';
                                    } else {
                                        $merchantTemplateId = 'merchant-approval';
                                    }
                                }

                                // ✅ Send ONLY ONE email
                                $merchantMailSent = $this->CommonModel->sendCommonHTMLEmail(
                                    $_POST['email'], 
                                    $merchantTemplateId, 
                                    $TempVars1, 
                                    $DynamicVars2
                                );
                                
                            }

                            $url = base_url() . 'publishers';

                            echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated", "url" => $url));
                            // echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated"));
                            // echo json_encode(array('flag' => 1, 'msg' => ($lang == "french" ? "Mis à jour avec succès" : "Successfully Updated")));
                            exit;

                        } else {

                            echo json_encode(array(
                                'flag' => 0, 
                                'msg' => ($lang == "french" 
                                        ? "Quelque chose s'est mal passé. Veuillez réessayer." 
                                        : "Something went wrong. Please try again")
                            ));
                            exit;
                        }


                    }

                } else {



                    // Add publisher

                    $emails = $this->input->post('emails');

                    $emailsArray = preg_replace('/\s+| /u', '', explode(',', $emails));

                    $implodedString = implode(', ', $emailsArray);



                    $hashPassword = md5($_POST["password"]);

                    $insertData = array(

                        'email'                => $_POST['email'],

                        'cc_email'                => $implodedString,

                        'password'            => $hashPassword,

                        'publication_name'    => $_POST['publication_name'],

                        'vendor_name'        => $_POST['vendor_name'],

                        'commision_percent' => $_POST['commision_percent'],

                        'vat_status'             => $_POST['vat_status'],

                        'vat_no'                 => $_POST['vat_no'],

                        'default_vat_percentage' => $_POST['default_vat_percentage'],

                        'split_id'          => $_POST['split_id'],

                        'phone_no'             => $_POST['phone_no'],

                        'description'        => $_POST['description'],

                        'status'            => $_POST['status'],

                        'remove_flag'       => 0,

                        'created_by'        => $SISA_ID,

                        'created_at'        => strtotime(date('Y-m-d H:i:s')),

                        'ip'                => $_SERVER['REMOTE_ADDR']

                    );



                    $is_success = $this->PublisherModel->insert_publishers($insertData);

                    if ($is_success) {

                        $publisher_id = $this->db->insert_id();

                        $insertPublisherPaymentDetailsData = array(

                            'publisher_id'                => $publisher_id,

                            'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                            'beneficiary_name'    => $_POST['beneficiary_name'],

                            'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],
                            'bank_address'        => $_POST['bank_address'],
                            'iban'        => $_POST['iban'],

                            'created_by'        => $SISA_ID,

                            'created_at'        => strtotime(date('Y-m-d H:i:s')),

                            'ip'                => $_SERVER['REMOTE_ADDR']

                        );

                        $is_success = $this->PublisherModel->insert_publisher_payment_details($insertPublisherPaymentDetailsData);

                        if (!empty($_POST['plan_id'])) {
                            $this->Subscription_model->assign_or_update_plan($publisher_id, (int)$_POST['plan_id'], $_SERVER['REMOTE_ADDR']);
                        } else {
                            $this->Subscription_model->assign_or_update_plan($publisher_id, 2, $_SERVER['REMOTE_ADDR']);
                        }

                        echo json_encode("success");

                        exit;

                        // $url = base_url().'publishers';

                        // echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));

                        // exit;	

                    } else {

                        // echo json_encode("error");

                        // exit;

                        echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));

                        exit;

                    }

                }

            }

        } else {

            return redirect('/');

        }

    }



    public function editPublisher($publisherId)

    {
        // echo "<pre>";print_r($_POST);die;
        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            if ($publisherId) {

                $publisherDATA = $this->PublisherModel->getSingleDataByID('publisher', array('id' => $publisherId), '*');

                if ($publisherDATA == '') {

                    return redirect('/');

                }

                $data['publisher'] = $this->PublisherModel->get_publisher_detail($publisherId);

                $data['publisher_payment_details'] = $this->PublisherModel->get_publisher_payment_details($publisherId);

                $data['plans'] = $this->Subscription_model->get_plans();

                $data['active_subscription'] = $this->Subscription_model->get_active_subscription($publisherId);

                $data['PageTitle'] = 'Publisher Edit';

                $data['side_menu'] = 'publisher';

                $this->load->view('publishers/publishers_edit', $data);

            } else {

                return redirect('/');

            }

        } else {

            return redirect('/');

        }

    }



    function deletePublisher()

    {

        $publisherId = $_POST['id'];

        $is_success = $this->PublisherModel->delete_publishers($publisherId);

        if ($is_success) {

            echo 'success';

            exit;

        } else {

            echo 'error';

            exit;

        }

    }



    public function publisherCommissionList()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (!empty($this->session->userdata('userPermission')) && !in_array('database/publishers', $this->session->userdata('userPermission'))) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $data['getPublishers'] = $this->PublisherModel->get_publishers();

            $data['PageTitle'] = 'Publishers';

            $data['side_menu'] = 'publisher_commission';



            // print_r($data);die();

            $this->load->view('publishers/publisher_commission_list', $data);

        } else {



            return redirect('/');

        }

    }

}

