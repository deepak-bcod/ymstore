<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CustomerController extends CI_Controller

{

	/**

	 * Index Page for this controller.

	 *

	 * Maps to the following URL

	 * 		http://example.com/index.php/welcome

	 *	- or -

	 * 		http://example.com/index.php/welcome/index

	 *	- or -

	 * Since this controller is set as the default controller in

	 * config/routes.php, it's displayed at http://example.com/

	 *

	 * So any other public methods not prefixed with an underscore will

	 * map to /index.php/welcome/<method_name>

	 * @see https://codeigniter.com/user_guide/general/urls.html

	 */

	function __construct()

	{

		parent::__construct();

		$this->load->model('CommonModel');

		$this->load->model('UserModel');

		$this->load->model('CustomerModel');

		$this->load->model('InvoicingModel');

		//$this->load->model('ManagerModel');

		$this->load->helper('url');

		// $this->load->model('NotificationModel');



		if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {

			redirect(BASE_URL);

		}

	}



	public function index()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers_type', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webShop';

		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();

		$this->load->view('customer/customer_type.php', $data);

	}



	public function create_customer_type()

	{

		if (isset($_POST) && !empty($_POST)) {

			$time = time();

			if (empty($_POST['customer_type'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else {

				$insert_array = array(

					"name" => $_POST['customer_type'],

					"created_at" => $time,

					"created_by" =>	$_SESSION['LoginID'],

					"ip" => $_SERVER['REMOTE_ADDR']

				);

				$insert_customer_type = $this->CustomerModel->add_customer_type($insert_array);

				if ($insert_customer_type) {

					echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Success"));

					exit();

				} else {

					echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Adding new Customer type failed"));

					exit();

				}

			}

		} else {

			echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Please Post Data"));

			exit();

		}

	}





	public function customer_type_details($type_id)

	{

		if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers_type', $this->session->userdata('userPermission'))) {

			redirect(base_url('dashboard'));

		}

		$data['side_menu'] = 'webShop';

		$data['type_details'] = $this->CustomerModel->get_single_customer_type_details($type_id);

		$customerType = $this->CustomerModel->get_single_customer_type_details($type_id);

		if ($customerType == '') {

			redirect('dashboard');

		}

		$data['customers_by_type'] = $this->CustomerModel->get_all_customer_by_type($type_id);

		$data['salesrule_info'] = $this->CustomerModel->get_all_salesrule_by_cust_type($type_id);





		// echo "<pre>";print_r($data['salesrule_info']);

		// die();

		$this->load->view('customer/customer_type_details.php', $data);

	}



	public function update_type_details($type_id = false)

	{

		if (isset($_POST) && !empty($_POST)) {

			$time = time();

			if (empty($_POST['customer_type_val'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else if (!$type_id) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else {

				$update_array = array(

					"name" => $_POST['customer_type_val'],

					"updated_at" => $time

				);

				$update_customer_type = $this->CustomerModel->update_customer_type($update_array, $type_id);

				if ($update_customer_type) {

					echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Success"));

					exit();

				} else {

					echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Updating  Customer type failed"));

					exit();

				}

			}

		} else {

			echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Please Post Data"));

			exit();

		}

	}

	//***

	public function customers()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers/read', $this->session->userdata('userPermission'))) {

				// redirect('dashboard');

			}

		}



		$data['PageTitle'] = 'webShop - Customers';

		$data['side_menu'] = 'webShop';

		//$data['customer_listing'] = $this->CustomerModel->get_all_customers();

		// echo "<pre>";print_r($data);

		// die();

		$this->load->view('customer/webshop_customers_listing', $data);

	}



	public function add_customer()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['PageTitle'] = 'webShop - Add Customer';

		$data['side_menu'] = 'webShop';

		$data['customer_types'] =  $this->CommonModel->get_customer_types();

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$this->load->view('customer/add_customer', $data);

	}



	public function save_customer()

	{

		if (isset($_POST)) {

			$loginId = $this->session->userdata('LoginID');

			if (isset($_POST['email'])) {

				$customer_details = $this->CustomerModel->get_customer_details_by_email($_POST['email']);

			}

			if (isset($_POST['dob'])) {

				$date = date_create($_POST['dob']);

			}



			$state = '';

			if (isset($_POST['country'])) {

				if ($_POST['country'] == 'IN') {

					$state = isset($_POST['state_dp']) ? $_POST['state_dp'] : '';

				} else {

					$state = isset($_POST['state']) ? $_POST['state'] : '';

				}

			}



			if (empty($customer_details)) {

				$insertdata = array(

					'first_name' => isset($_POST['first_name']) ? $_POST['first_name'] : '',

					'last_name' => isset($_POST['last_name']) ? $_POST['last_name'] : '',

					'email_id' => isset($_POST['email']) ? $_POST['email'] : '',

					'password' => isset($_POST['password']) ? md5($_POST['password']) : '',

					'mobile_no' => isset($_POST['mobile_no']) ? $_POST['mobile_no'] : '',

					'gender' => isset($_POST['gender']) ? $_POST['gender'] : '',

					'country_code' => isset($_POST['country']) ? $_POST['country'] : '',

					'dob' =>  isset($date) ? date_format($date, "Y-m-d") : '',

					'company_name' => isset($_POST['company_name']) ? $_POST['company_name'] : '',

					'gst_no' => isset($_POST['GST_no']) ? $_POST['GST_no'] : '',

					'customer_type_id' => isset($_POST['customer_type_id']) ? $_POST['customer_type_id'] : '',

					'access_prelanch_product' => isset($_POST['access_prelanch_product']) ? 1 : 0,

					'status' => 1,

					'created_by' => 0,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$customer_id = $this->CustomerModel->insertData('customers', $insertdata);

				if (isset($customer_id)) {

					$address_data = array(

						'customer_id' => $customer_id,

						'first_name' => isset($_POST['first_name']) ? $_POST['first_name'] : '',

						'last_name' => isset($_POST['last_name']) ? $_POST['last_name'] : '',

						'mobile_no' => isset($_POST['mobile_no']) ? $_POST['mobile_no'] : '',

						'address_line1' => isset($_POST['address_line1']) ? $_POST['address_line1'] : '',

						'address_line2' => isset($_POST['address_line2']) ? $_POST['address_line2'] : '',

						'city' => isset($_POST['city']) ? $_POST['city'] : '',

						'state' => $state,

						'country' => isset($_POST['country']) ? $_POST['country'] : '',

						'pincode' => isset($_POST['pincode']) ? $_POST['pincode'] : '',

						'company_name' => isset($_POST['company_name']) ? $_POST['company_name'] : '',

						'vat_no' => isset($_POST['GST_no']) ? $_POST['GST_no'] : '',

						'is_default' => 1,

						'created_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);
					$full_address = $address_data['address_line1'] . ' ' . $address_data['address_line2'] . ' ' .
									$address_data['city'] . ' ' . $address_data['state'] . ' ' .
									$address_data['country'] . ' ' . $address_data['pincode'];

					$encoded_address = urlencode($full_address);
					$apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
					$geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

					// Safer to use cURL instead of file_get_contents
					$response = file_get_contents($geocodeURL);
					$responseData = json_decode($response, true);

					if (!empty($responseData['results'][0]['geometry']['location'])) {
						$latitude  = $responseData['results'][0]['geometry']['location']['lat'];
						$longitude = $responseData['results'][0]['geometry']['location']['lng'];
						$address_data['latitude']  = $latitude;
						$address_data['longitude'] = $longitude;
					} else {
						$address_data['latitude']  = null;
						$address_data['longitude'] = null;
					}
					$customers_address_id = $this->CustomerModel->insertData('customers_address', $address_data);



					//  if(isset($customers_address_id)  && isset($customer_id))

					//  {

					//  	$shop_id =	$this->session->userdata('ShopID');

					//  	$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);

					//  	$webshop_details=$this->CommonModel->get_webshop_details($shop_id);

					//  	$shop_name='BEEPZS05';

					//  	$webshopName = $shop_owner->org_shop_name;

					// 	$webshopName = 'BEEPZS05';

					//  	if($webshopName!=false){

					// 		$webshop_name = $webshopName;

					// 	}else{

					// 		$webshop_name = '';

					// 	}

					// 	$site_logo = '';

					// 	if(isset($webshop_details)){

					// 	 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);

					// 	}

					// 	else{

					// 		$shop_logo = '';

					// 	}

					// 	$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';

					// 	$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';

					// 	$customer_email = isset($_POST['email']) ? $_POST['email'] : '';

					// 	$customer_password = isset($_POST['password']) ? $_POST['password'] : '';

					// 	$emailTo= isset($_POST['email']) ? $_POST['email'] : '' ;

					// 	$name = $first_name.' '.$last_name;



					// 	$identifier = "customer-register-successful-admin";

					// 	$TempVars=array('##CUSTOMERNAME##','##WEBSHOPNAME##','##CUSTOMEREMAIL##','##CUSTOMERPASSWORD##');

					// 	$DynamicVars=array($name, $webshop_name,$customer_email, $customer_password);

					// 	$burl= base_url();

					//  	$shop_logo = get_s3_url($shop_logo, $shop_id);

					// 	$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">

					// 		<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />

					// 	</a>';

					//  	 $shop_logo = '';





					// 	$language_code = isset($_POST['lang_code']) ? $_POST['lang_code'] : '';



					// 	$lang_code='';

					// 	if($language_code !='')

					// 	{

					// 		$languageData = $this->Multi_Languages_Model->getSingleDataByID('multi_languages',array('code'=>$language_code), '');

					// 		$is_default_language=isset($languageData) ? $languageData->is_default_language : '';

					// 		if($is_default_language == 0){

					// 		$lang_code=$language_code;

					// 		}

					// 	}



					// 	$CommonVars=array($site_logo, $shop_name);

					// 	if(isset($identifier)){

					// 		$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($identifier,$shop_id);

					// 		if($emailSendStatusFlag==1){

					// 			$send_email=$this->CustomerModel->sendCommonHTMLEmail($emailTo,$identifier,$TempVars,$DynamicVars,$webshop_name,$CommonVars,$lang_code);

					// 		}

					// 	}

					//  }

					$redirect = base_url('customers');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));

					exit;

				}

			} else {

				$redirect = base_url('add-customer');

				echo json_encode(array('flag' => 0, 'msg' => "User already registered with this email address", 'redirect' => $redirect));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));

			exit;

		}

	}



	public function customer_details($customer_id)

	{

		$data['PageTitle'] = 'webShop - Customers Details';

		$data['side_menu'] = 'webShop';

		$data['customer_id'] = $customer_id;

		// print_r($customer_id);die();



		$CustomerID = $this->CustomerModel->get_single_customer_details($customer_id);



		$data['customer_details'] = $this->CustomerModel->get_customer_details($customer_id);

		//$data['account_manager_details'] = $this->ManagerModel->get_account_manager_details();

		$shop_id =	$this->session->userdata('ShopID');

		// $ShopData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'currency_symbol,currency_code,org_website_address');

		//$data['org_website_address']= $ShopData->org_website_address;

		//$currency_symbol=(isset($ShopData->currency_symbol))?$ShopData->currency_symbol:$ShopData->currency_code;

		$data['currency_symbol'] = 'INR';

		$data['customer_types'] = $this->CustomerModel->get_customer_type_details();

		$data['customer_order_details'] = $this->CustomerModel->get_Customer_order_details($customer_id);

		$data['customer_country'] = $this->CommonModel->get_country_name_by_code($data['customer_details'][0]['country_code']);

		$data['customer_address_book'] = $this->CustomerModel->get_Customer_AddressBook($customer_id);

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['InvoiceList'] = $this->CustomerModel->getinvoicesbycustomerId($customer_id);

		$data['InvoiceGenerateList'] = $this->InvoicingModel->get_Customer_invoicing_list($customer_id);

		$data['customerId'] = $customer_id;

		$data['customers_info'] = $this->CommonModel->get_customers_info();

		$data['webshopcust_def_inv_altemail'] = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');

		$data['customer_return_order_list'] = $this->CustomerModel->getCustomerOrderReturnDataById($customer_id);

		$this->load->view('customer/webshop-customer-details-order.php', $data);

	}



	public function update_customer_detail()

	{

		if (isset($_POST['submit']) && !empty($_POST['submit'])) {

			$customer_type = $this->input->post('customer_type');

			$customer_id = $this->input->post('text_hidden');





			if (isset($_POST['access_prelaunch']) && $_POST['access_prelaunch'] == 'on') {

				$access_prelanch_product = 1;

			} else {

				$access_prelanch_product = 0;

			}



			if (isset($_POST['allow_catlog_builder']) && $_POST['allow_catlog_builder'] == 'on') {

				$allow_catlog_builder = 1;

			} else {

				$allow_catlog_builder = 0;

			}



			$update_array = array(

				"customer_type_id" => $customer_type,



				"access_prelanch_product" => $access_prelanch_product,

				"allow_catlog_builder" => $allow_catlog_builder



			);

			$update_customer_detail = $this->CustomerModel->update_customer_detail($update_array, $customer_id);



			// $this->load->view('customer/webshop-customer-details-order.php',$data);

			redirect(base_url() . 'CustomerController/customer_details/' . $customer_id, 'refresh');

		}

	}



	public function getwebshopCustomerList()

	{

		if (isset($_POST)) {

			// print_r($_POST);

			// die();

			$search_param = array();

			// Shop, owner, created date - keyword

			if (!empty($_POST['search'])) {

				$search_param['keyword'] = $_POST['search'];

			}

			$data['customer_listing'] = $customer_listing = $this->CustomerModel->get_all_customers($search_param);

			// print_r($data );

			// die();

			$this->load->view('customer/webshopcustomerlist', $data);

		}

	}



	//***



	public function postwebshopCustomerInvoice()

	{

		// $seller_db= $this->seller_db->database;

		$LoginID = $_SESSION['LoginID'];

		$ShopID = $_SESSION['ShopID'];

		$ShopOwnerId = $_SESSION['ShopOwnerId'];



		if (isset($_POST['customerId'])) {

			$invoice = isset($_POST['invoice']) ? $_POST['invoice'] : '';

			$customer_id = isset($_POST['customerId']) ? $_POST['customerId'] : '';

			$webshopCustomerInvoiceData = $this->CustomerModel->getInvoiceBywebshopCustomerId($customer_id);

			if (isset($customer_id)) {

				$invDailyAmt = '';

				$invWeeklyAmt = '';

				$invMonthlyAmt = '';

				$invoice_to = $this->CommonModel->custom_filter_input($_POST['invoice_to']);

				$payment_term = $this->CommonModel->custom_filter_input($_POST['payment_term']);



				if (isset($invoice)) {

					$invoiceType = $invoice;

					if ($invoice == 2) {

						$invDailyAmt = $this->CommonModel->custom_filter_input($_POST['invDailyAmt']);

					} else if ($invoice == 3) {

						$invWeeklyAmt = $this->CommonModel->custom_filter_input($_POST['invWeeklyAmt']);

					} else if ($invoice == 4) {

						$invMonthlyAmt = $this->CommonModel->custom_filter_input($_POST['invMonthlyAmt']);

					}

				} else {

					$invoiceType = '0';

				}



				if ($invoice_to == '1') {

					$alternate_email = $this->CommonModel->custom_filter_input($_POST['alternate_email']);

				} else {

					$alternate_email = '';

				}

				$customerId = $this->CommonModel->custom_filter_input($_POST['customerId']);





				if (empty($webshopCustomerInvoiceData)) {

					$insertData = array(

						'customer_id' => $customerId,

						'invoice_type' => $invoiceType,

						'inv_daily_max_inv_amt' => $invDailyAmt,

						'inv_weekly_max_inv_amt' => $invWeeklyAmt,

						'inv_monthly_max_inv_amt' => $invMonthlyAmt,

						'invoice_to_type' => $invoice_to,

						'alternative_email_id' => $alternate_email,

						'payment_term' => $payment_term,

						'created_by' => $LoginID,

						'created_at' => strtotime(date('Y-m-d H:i:s')),

						// 'updated_at' 		=> ,

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$rowAffected = $this->seller_db->insert('customers_invoice', $insertData);

				} else {

					$updateData = array(

						'invoice_type' => $invoiceType,

						'inv_daily_max_inv_amt' => $invDailyAmt,

						'inv_weekly_max_inv_amt' => $invWeeklyAmt,

						'inv_monthly_max_inv_amt' => $invMonthlyAmt,

						'invoice_to_type' => $invoice_to,

						'alternative_email_id' => $alternate_email,

						'payment_term' => $payment_term,

						// 'created_by' => $LoginID,

						// 'created_at' => strtotime(date('Y-m-d H:i:s')),

						'updated_at' => strtotime(date('Y-m-d H:i:s'))

						// 'ip' =>$_SERVER['REMOTE_ADDR']



					);

					$this->seller_db->where(array('id' => $webshopCustomerInvoiceData->id));

					$rowAffected = $this->seller_db->update('customers_invoice', $updateData);

				}

				//}

			}

			//redirect(base_url()."b2b/customer/detail-invoice/".$shop_id);

			if ($rowAffected) {

				echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success",));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!"));

				exit;

			}

		}

	}



	function OpenEditPersonalInfoPopup()

	{

		$data['customer_id'] = $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

		$fbc_user_id	=	$this->session->userdata('LoginID');

		$shop_id		=	$this->session->userdata('ShopID');

		$data['customer_details'] = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_id), '*');

		// echo "<pre>" ; print_r($data['customer_details']);die();

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$View = $this->load->view('customer/edit_customer', $data, true);

		$this->output->set_output($View);

	}



	function OpenEditAddressPopup()

	{

		$data['customer_id'] = $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

		$data['address_id'] = $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';

		$data['customer_address'] = $this->CommonModel->getSingleShopDataByID('customers_address', array('customer_id' => $customer_id, 'id' => $address_id), '*');

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$View = $this->load->view('customer/edit_customer_address', $data, true);

		$this->output->set_output($View);

	}



	public function update_customer_info()

	{

		if (isset($_POST)) {

			if (isset($_POST['dob'])) {

				$dob = date_create($_POST['dob']);

			}

			$first_name = $this->input->post('first_name');

			$last_name = $this->input->post('last_name');

			$company_name = $this->input->post('company_name');

			$GST_no = $this->input->post('GST_no');

			// $dob= $this->input->post('dob');

			$mobile_no = $this->input->post('mobile_no');

			$gender = $this->input->post('gender');

			$country = $this->input->post('country');

			$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';



			$update_array = array(

				"first_name" => $first_name,

				"last_name" => $last_name,

				"company_name" => $company_name,

				"gst_no" => $GST_no,

				"dob" => isset($dob) ? date_format($dob, "Y-m-d") : '',

				"mobile_no" => $mobile_no,

				"gender" => $gender,

				"country_code" => $country,

			);



			$rowAffected = $this->CustomerModel->update_customer_detail($update_array, $customer_id);

		}

		if (isset($_POST['company_name']) || isset($_POST['gst_no'])) {

			$update_add = array(

				"company_name" => $company_name,

				"vat_no" => $GST_no,

			);

			$update_customer_com_get = $this->CustomerModel->update_customer_deault_billaddr($update_add, $customer_id);

		}



		if ($rowAffected) {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success", 'redirect' => $redirect));

			exit();

		} else {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!", 'redirect' => $redirect));

			exit;

		}

	}



	public function update_customer_address()

	{

		if (isset($_POST)) {

			$state = '';

			if (isset($_POST['country'])) {

				if ($_POST['country'] == 'IN') {

					$state = isset($_POST['state_dp']) ? $_POST['state_dp'] : '';

				} else {

					$state = isset($_POST['state']) ? $_POST['state'] : '';

				}

			}

			$first_name = $this->input->post('first_name');

			$last_name = $this->input->post('last_name');

			$address_line1 = $this->input->post('address_line1');

			$address_line2 = $this->input->post('address_line2');

			$city = $this->input->post('city');

			$country = $this->input->post('country');

			$pincode = $this->input->post('pincode');

			$mobile_no = $this->input->post('mobile_no');



			$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

			$address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';

			$update_array = array(

				"first_name" => $first_name,

				"last_name" => $last_name,

				"address_line1" => $address_line1,

				"address_line2" => $address_line2,

				"city" => $city,

				"country" => $country,

				"pincode" => $pincode,

				"state" => $state,

				"mobile_no" => $mobile_no,

			);
			$full_address = $update_array['address_line1'] . ' ' . $update_array['address_line2'] . ' ' .
				$update_array['city'] . ' ' . $update_array['state'] . ' ' .
				$update_array['country'] . ' ' . $update_array['pincode'];

			$encoded_address = urlencode($full_address);
			$apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
			$geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

			// Safer to use cURL instead of file_get_contents
			$response = file_get_contents($geocodeURL);
			$responseData = json_decode($response, true);

			if (!empty($responseData['results'][0]['geometry']['location'])) {
				$latitude  = $responseData['results'][0]['geometry']['location']['lat'];
				$longitude = $responseData['results'][0]['geometry']['location']['lng'];
				$update_array['latitude']  = $latitude;
				$update_array['longitude'] = $longitude;
			} else {
				$update_array['latitude']  = null;
				$update_array['longitude'] = null;
			}


			$rowAffected = $this->CustomerModel->update_customer_address($update_array, $customer_id, $address_id);

		}



		if ($rowAffected) {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success", 'redirect' => $redirect));

			exit();

		} else {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!", 'redirect' => $redirect));

			exit;

		}

	}



	public function loadcustomerssajax()

	{



		$customer_listing = $this->CustomerModel->get_datatables_customer_details();

		// echo "<pre>";

		// print_r($customer_listing);

		// die();

		$data = array();



		foreach ($customer_listing as $readData) {



			$row  = array();

			$row[] = $readData->id;

			$row[] = $readData->first_name . ' ' . $readData->last_name;



			$customer_details = $this->CustomerModel->get_customer_details($readData->id);

			$lastrow =  (count($customer_details) - 1);

			if (isset($customer_details[$lastrow]['created_at']) && $customer_details[$lastrow]['created_at'] != '') {

				$row[] = date("d-m-y", $customer_details[$lastrow]['created_at']);

			}



			$row[] = $readData->email_id;

			$row[] = $readData->city . ', ' . $readData->state;



			$view_url = base_url() . 'customer-details/' . $readData->id;

			$row[] = '<a class="link-purple " target="_blank" href="' . $view_url . '">View</a>';



			$data[] = $row;

		}



		$output = array(

			"draw" => $_POST['draw'],

			"recordsTotal" => $this->CustomerModel->countcustomersrecord(),

			"recordsFiltered" => $this->CustomerModel->countfiltercustomersrecord(),

			"data" => $data,

		);



		echo json_encode($output);

		exit;

	}

	public function faqs() {
		$this->db->order_by('id', 'DESC');
		$faqs = $this->CommonModel->get_faqs();
		$data['faqs'] = $faqs; // Wrap in an array to pass to view
		// echo "<pre>";
		// print_r($data);
		// die();
		$this->load->view('faqs_list', $data);
	}

	public function faq_edit($id) {
		$data['faqs'] = $this->CommonModel->get_faqs_details($id);
		$this->load->view('edit_faq', $data);
	}

	public function update_faqs()
{
    $id        = (int) $this->input->post('id');
    $answer    = $this->input->post('answer');
    $answer_fr = $this->input->post('answer_fr');
    $status    = $this->input->post('status');

    // Load notification model
    $this->load->model('Notification_model');

    // Get existing FAQ details
    $faqDetails = $this->CommonModel->get_faqs_details($id);

    if (empty($faqDetails)) {
        $this->session->set_flashdata('error', 'FAQ not found.');
        redirect(base_url('faqs'));
        return;
    }

    // Prepare update data
    $postArr = [
        'answer'     => $answer,
        'answer_fr'  => $answer_fr,
        'status'     => $status,
        'updated_at' => time()
    ];

    if ($this->input->post('question') !== null) {
        $postArr['question'] = $this->input->post('question');
    }

    if ($this->input->post('question_fr') !== null) {
        $postArr['question_fr'] = $this->input->post('question_fr');
    }

    if ($this->input->post('faq_type') !== null) {
        $postArr['faq_type'] = $this->input->post('faq_type');
    }

    // Update FAQ
    $this->db->where('id', $id);
    $updated = $this->db->update('faqs', $postArr);

    if ($updated) {

        // ==========================================
        // 1. MERCHANT FAQ REPLY NOTIFICATION
        // ==========================================

        // Only notify merchant FAQs when admin has provided an answer
        $faqType = $faqDetails['faq_type'] ?? '';

        if (
            strtolower($faqType) === 'merchant' &&
            (!empty(trim((string)$answer)) || !empty(trim((string)$answer_fr)))
        ) {

            // Find merchant using FAQ email
            $merchant = $this->db
                ->select('id, vendor_name, email')
                ->from('publisher')
                ->where('email', trim($faqDetails['email']))
                ->limit(1)
                ->get()
                ->row();

            if (!empty($merchant)) {

                $notification_data = [
                    'type'           => 'faq',
                    'subtype'        => 'admin_reply',
                    'recipient_type' => 'merchant',
                    'recipient_id'   => $merchant->id,

                    'title'          => 'FAQ Reply',

                    'message'        => 'Admin has replied to your FAQ.',

                    'data'           => [
                        'faq_id'        => $id,
                        'merchant_id'   => $merchant->id,
                        'merchant_name' => $merchant->vendor_name,
                        'merchant_email'=> $merchant->email,
                        'question'      => $faqDetails['question'] ?? '',
                        'question_fr'   => $faqDetails['question_fr'] ?? '',
                        'answer'        => $answer,
                        'answer_fr'     => $answer_fr,
                        'status'        => $status
                    ],

                    'is_read' => 0
                ];

                $this->Notification_model->insert($notification_data);
            }
        }

        // ==========================================
        // 2. EMAIL NOTIFICATION
        // ==========================================

        $userEmail = !empty($faqDetails['email'])
            ? trim($faqDetails['email'])
            : '';

        if (!empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {

            $name = !empty($faqDetails['name'])
                ? $faqDetails['name']
                : 'User';

            $q_en = !empty($postArr['question'])
                ? $postArr['question']
                : ($faqDetails['question'] ?? '');

            $q_fr = !empty($postArr['question_fr'])
                ? $postArr['question_fr']
                : ($faqDetails['question_fr'] ?? '');

            $a_en = !empty($answer)
                ? $answer
                : ($faqDetails['answer'] ?? '');

            $a_fr = !empty($answer_fr)
                ? $answer_fr
                : ($faqDetails['answer_fr'] ?? '');

            if (empty($q_en)) {
                $q_en = $q_fr;
            }

            if (empty($q_fr)) {
                $q_fr = $q_en;
            }

            if (empty($a_en) && !empty($a_fr)) {
                $a_en = $a_fr;
            }

            if (empty($a_fr) && !empty($a_en)) {
                $a_fr = $a_en;
            }

            $statusText_en = ($status == 1)
                ? 'Approved & Answered'
                : (($status == 2) ? 'Rejected' : 'Pending Review');

            $statusText_fr = ($status == 1)
                ? 'Approuvé & Répondu'
                : (($status == 2) ? 'Rejeté' : 'En attente de révision');

            // ==========================================
            // English email template
            // ==========================================

            $tplEn = $this->CommonModel->getEmailTemplateByIdentifier(
                'faq_update_notification_en'
            );

            if (!$tplEn && $this->db->table_exists('email_template')) {

                $this->db->insert('email_template', [
                    'title'      => 'FAQ Update Notification (English)',
                    'email_code' => 'faq_update_notification_en',
                    'subject'    => 'YellowMarkets - Update on Your FAQ Question',

                    'content' => '<p>Hello {name},</p>
                    <p>We have updated the status of your FAQ question on <strong>{site_name}</strong>.</p>
                    <div style="background-color:#f8f9fa;border-left:4px solid #1E7EC8;padding:15px;margin:15px 0;border-radius:4px;">
                    <p><strong>Status:</strong> {status}</p>
                    <p><strong>Question:</strong> {question}</p>
                    <p><strong>Answer:</strong> {answer}</p>
                    </div>
                    <p>Thank you for using {site_name}.</p>',

                    'status'     => 1,
                    'created_at' => time()
                ]);
            }

            // ==========================================
            // French email template
            // ==========================================

            $tplFr = $this->CommonModel->getEmailTemplateByIdentifier(
                'faq_update_notification_fr'
            );

            if (!$tplFr && $this->db->table_exists('email_template')) {

                $this->db->insert('email_template', [
                    'title'      => 'FAQ Update Notification (French)',
                    'email_code' => 'faq_update_notification_fr',
                    'subject'    => 'YellowMarkets - Mise à jour de votre question FAQ',

                    'content' => '<p>Bonjour {name},</p>
                    <p>Le statut de votre question FAQ sur <strong>{site_name}</strong> a été mis à jour.</p>
                    <div style="background-color:#f8f9fa;border-left:4px solid #1E7EC8;padding:15px;margin:15px 0;border-radius:4px;">
                    <p><strong>Statut:</strong> {status}</p>
                    <p><strong>Question:</strong> {question}</p>
                    <p><strong>Réponse:</strong> {answer}</p>
                    </div>
                    <p>Merci d’utiliser {site_name}.</p>',

                    'status'     => 1,
                    'created_at' => time()
                ]);
            }

            // ==========================================
            // English variables
            // ==========================================

            $tempVars_en = [
                '{name}',
                '{status}',
                '{question}',
                '{answer}',
                '{site_name}',
                '{site_url}'
            ];

            $dynamicVars_en = [
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($statusText_en, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($q_en, ENT_QUOTES, 'UTF-8'),
                !empty($a_en)
                    ? nl2br(htmlspecialchars_decode(htmlspecialchars($a_en, ENT_QUOTES, 'UTF-8')))
                    : '-',
                'YellowMarkets',
                base_url()
            ];

            // ==========================================
            // French variables
            // ==========================================

            $tempVars_fr = [
                '{name}',
                '{status}',
                '{question}',
                '{answer}',
                '{site_name}',
                '{site_url}'
            ];

            $dynamicVars_fr = [
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($statusText_fr, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($q_fr, ENT_QUOTES, 'UTF-8'),
                !empty($a_fr)
                    ? nl2br(htmlspecialchars_decode(htmlspecialchars($a_fr, ENT_QUOTES, 'UTF-8')))
                    : '-',
                'YellowMarkets',
                base_url()
            ];

            // ==========================================
            // Send email according to FAQ language
            // ==========================================

            if (
                !empty($faqDetails['question_fr']) &&
                !empty($faqDetails['question'])
            ) {

                // Both languages available
                $this->CommonModel->sendCommonHTMLEmail(
                    $userEmail,
                    'faq_update_notification_fr',
                    $tempVars_fr,
                    $dynamicVars_fr
                );

                $this->CommonModel->sendCommonHTMLEmail(
                    $userEmail,
                    'faq_update_notification_en',
                    $tempVars_en,
                    $dynamicVars_en
                );

            } elseif (
                !empty($faqDetails['question_fr']) &&
                empty($faqDetails['question'])
            ) {

                // French only
                $this->CommonModel->sendCommonHTMLEmail(
                    $userEmail,
                    'faq_update_notification_fr',
                    $tempVars_fr,
                    $dynamicVars_fr
                );

            } else {

                // English only
                $this->CommonModel->sendCommonHTMLEmail(
                    $userEmail,
                    'faq_update_notification_en',
                    $tempVars_en,
                    $dynamicVars_en
                );
            }
        }

        $this->session->set_flashdata(
            'success',
            'FAQ has been updated successfully and email notification sent.'
        );

        redirect(base_url('faqs'));

    } else {

        $this->session->set_flashdata(
            'error',
            'Failed to update FAQ. Please try again.'
        );

        redirect($_SERVER['HTTP_REFERER']);
    }
}

	public function faq_add() {
		$this->load->view('add_faq');
	}

	public function faq_save() {
		$question = $this->input->post('question');
		$question_fr = $this->input->post('question_fr');
		$answer = $this->input->post('answer');
		$answer_fr = $this->input->post('answer_fr');
		$faq_type = $this->input->post('faq_type') ?: 'Merchant';
		$status = $this->input->post('status') !== null ? (int)$this->input->post('status') : 1;

		$adminEmail = isset($_SESSION['UserEmail']) ? $_SESSION['UserEmail'] : 'admin@yellowmarkets.com';

		$insertArr = [
			'name'        => 'Admin',
			'email'       => $adminEmail,
			'question'    => $question,
			'question_fr' => $question_fr,
			'answer'      => $answer,
			'answer_fr'   => $answer_fr,
			'faq_type'    => $faq_type,
			'status'      => $status,
			'created_at'  => time(),
			'updated_at'  => strtotime(date('Y-m-d H:i:s')),
			'ip'          => $this->input->ip_address()
		];

		$inserted = $this->db->insert('faqs', $insertArr);

		if($inserted){
			$this->session->set_flashdata('success', "FAQ has been added successfully.");
			redirect(base_url('faqs'));
		} else {
			$this->session->set_flashdata('error', "Failed to add FAQ. Please try again.");
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function faq_delete($id) {
		$this->db->where('id', (int)$id);
		$deleted = $this->db->delete('faqs');
		if($deleted){
			$this->session->set_flashdata('success', "FAQ deleted successfully.");
		} else {
			$this->session->set_flashdata('error', "Failed to delete FAQ.");
		}
		redirect(base_url('faqs'));
	}
	// --- KEEP YOUR EXISTING METHODS ---

	//----Sticker --------------

	public function sticker()
	{

		$data['side_menu'] = 'sticker';

		$this->db->where('id', 1);
		$data['sticker'] = $this->db->get('sticker_text')->row();

		$this->load->view('sticker.php', $data);
	}

	public function update_sticker()
    {

		if (isset($_POST) && !empty($_POST)) {

			$time = time();

			if (empty($_POST['text'])) {
				
				$this->session->set_flashdata('error', "Please enter all mandatory / compulsory fields.");
				redirect($_SERVER['HTTP_REFERER']);

			} else {

				$updateData = array("text" => $_POST['text'],"text_fr" => $_POST['text_fr']);

				$this->db->where('id', 1);
		        $updated = $this->db->update('sticker_text', $updateData);

				if($updated){
					$this->session->set_flashdata('success', "Sticker has been updated successfully.");
					redirect(base_url('sticker'));
				} else {
					$this->session->set_flashdata('error', "Failed to update Sticker. Please try again.");
					redirect($_SERVER['HTTP_REFERER']);
				}
			}
		} else {
			$this->session->set_flashdata('error', "Please Post Data");
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	

    public function help_desk() {
        $data['help_desk'] = $this->CommonModel->get_help_desk();
        $this->load->view('help_desk_list', $data);
    }

    public function help_desk_edit($id) {
        $data['ticket'] = $this->CommonModel->get_help_desk_details($id);
        $customer_id = $data['ticket']['customer_id'];
        $data['orders'] = $this->CommonModel->get_customer_orders($customer_id);

        $order_id = $data['ticket']['order_id'];
        $data['products'] = $this->CommonModel->get_order_products($order_id); 

        $data['status_labels'] = [
            0 => 'Complete',
            1 => 'Pending',
            2 => 'Cancelled',
        ];
        $this->load->view('edit_help_desk', $data);
    }

    public function update_help_desk()
    {
		// 1. Get the primary key 'id' sent from the form
		$id = $this->input->post('ticket_id'); // This receives the value of the hidden input
		$admin_reply = trim($this->input->post('admin_reply'));

		if (empty($admin_reply)) {
			$this->session->set_flashdata('error', "Reply cannot be empty.");
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// 2. QUERY BY THE CORRECT COLUMN ('id')
		$ticket = $this->db->where('id', $id)->get('help_desk')->row();

		if (!$ticket) {
			$this->session->set_flashdata('error', "Ticket not found. ID provided: " . $id);
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// 3. Prepare data (ensure you use $ticket->order_id for the redirect later)
		$insertData = [
			'ticket_id'           => $ticket->ticket_id, 
			'subject'             => $ticket->subject,
			'category'            => $ticket->category,
			'priority'            => $ticket->priority,
			'customer_id'         => $ticket->customer_id,
			'merchant_id'         => $ticket->merchant_id,
			'message'             => '', // Keep empty or store reference if needed
			'attachment'          => '',
			'order_id'            => $ticket->order_id,
			'products'            => $ticket->products,
			'admin_reply'         => $admin_reply,
			'status'              => 1,
			'created_at'          => time(),
			'updated_at'          => time(),
			'ip'                  => $_SERVER['REMOTE_ADDR'],
		];

		$this->db->insert('help_desk', $insertData);

		$updateData = [
			'updated_at'  => strtotime(date('Y-m-d H:i:s')),
			'status'      => 1
		];
		$this->db->where('ticket_id', $ticket->ticket_id);
		$this->db->update('help_desk', $updateData);
		

		// 4. Correct Redirect
		// Ensure this path matches the one defined in your routes
		$this->session->set_flashdata('success', "Reply added successfully.");
		// Change the redirect line in update_help_desk:
		$redirect_path = 'CustomerController/view/' . $ticket->order_id . '/' . $ticket->ticket_id . '/' . $ticket->products;
		redirect($redirect_path);
    }

    // ==========================================================
    // ADD THESE NEW METHODS FOR YOUR SUBMENUS BELOW
    // ==========================================================

    
    
    
    public function shopper_help_desk() {
        
        $data['side_menu'] = 'help_desk'; 
        
       
        $data['help_desk'] = $this->CommonModel->get_help_desk('shopper'); 
        
        
        $this->load->view('shopper_help_desk_list', $data);
    }

    public function merchant_help_desk() {
        
        $data['side_menu'] = 'help_desk'; 
        
        
        $data['help_desk'] = $this->CommonModel->get_help_desk('merchant'); 
        
        
        $this->load->view('merchant_help_desk_list', $data);
    }

	public function close_ticket($order_id = 0, $category = 0, $ticket_id = 0, $product_id = 0)
	{
		// Fetch ticket info first to check if notification is needed
		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		} else {
			if (!empty($order_id)) {
				$this->db->where('order_id', $order_id);
			}
			if (!empty($product_id)) {
				$this->db->where('products', $product_id);
			}
		}
		if (!empty($category)) {
			$this->db->where('category', $category);
		}
		$ticket = $this->db->get('help_desk')->row();

		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		} else {
			if (!empty($order_id)) {
				$this->db->where('order_id', $order_id);
			}
			if (!empty($product_id)) {
				$this->db->where('products', $product_id);
			}
		}

		if (!empty($category)) {
			$this->db->where('category', $category);
		}

		$this->db->update('help_desk', array(
			'status'      => 2,
			'status_code' => 'Close',
			'closed_at'   => time(),
			'closed_by'   => 'admin',
			'updated_at'  => time(),
		));

		// Stage 4: If this ticket is part of the refund resolution workflow, notify the merchant
		if ($ticket && (!empty($ticket->merchant_action) && $ticket->merchant_action === 'refund_approved' || !empty($ticket->refund_amount) && (float)$ticket->refund_amount > 0 || (!empty($ticket->status_code) && $ticket->status_code === 'Done'))) {
			$ctx = $this->_get_ticket_email_context($ticket, $order_id, $product_id);
			if (!empty($ctx['merchant_email'])) {
				$merchantDynamicVars = [
					$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
					$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
					$ctx['product_name'], $ctx['product_name'],
					$ctx['merchant_name'], $ctx['merchant_name'],
					$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
					$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
					$ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'],
					'Refund Closed', 'Refund Closed',
					$ctx['merchant_ticket_url'], $ctx['merchant_ticket_url'],
					'Yellow Markets', 'Yellow Markets'
				];

				$lang_code = ($this->session->userdata('site_lang') === 'french' || $this->session->userdata('site_lang') === 'fr') ? 'fr' : 'en';

				$this->CommonModel->sendCommonHTMLEmail(
					$ctx['merchant_email'],
					'order-resolution-refund-closed-merchant',
					$ctx['tempVars'],
					$merchantDynamicVars,
					$lang_code
				);
			}
		}

		// Replacement Resolution Workflow Closure: Notify Merchant and Shopper
		if ($ticket && in_array($ticket->merchant_action, ['replacement_completed', 'replacement_approved'], true)) {
			$ctx = $this->_get_ticket_email_context($ticket, $order_id, $product_id);
			$lang_code = ($this->session->userdata('site_lang') === 'french' || $this->session->userdata('site_lang') === 'fr') ? 'fr' : 'en';

			// 1. Send order-resolution-replacement-closed-merchant to Merchant
			if (!empty($ctx['merchant_email'])) {
				$merchantDynamicVars = [
					$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
					$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
					$ctx['product_name'], $ctx['product_name'],
					$ctx['merchant_name'], $ctx['merchant_name'],
					$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
					$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
					$ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'],
					'Replacement Closed', 'Replacement Closed',
					$ctx['merchant_ticket_url'], $ctx['merchant_ticket_url'],
					'Yellow Markets', 'Yellow Markets'
				];

				$this->CommonModel->sendCommonHTMLEmail(
					$ctx['merchant_email'],
					'order-resolution-replacement-closed-merchant',
					$ctx['tempVars'],
					$merchantDynamicVars,
					$lang_code
				);
			}

			// 2. Send order-resolution-replacement-completed-shopper to Shopper
			if (!empty($ctx['shopper_email'])) {
				$shopperDynamicVars = [
					$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
					$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
					$ctx['product_name'], $ctx['product_name'],
					$ctx['merchant_name'], $ctx['merchant_name'],
					$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
					$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
					$ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'], $ctx['delivery_method'],
					'Replacement Completed', 'Replacement Completed',
					$ctx['shopper_ticket_url'], $ctx['shopper_ticket_url'],
					'Yellow Markets', 'Yellow Markets'
				];

				$this->CommonModel->sendCommonHTMLEmail(
					$ctx['shopper_email'],
					'order-resolution-replacement-completed-shopper',
					$ctx['tempVars'],
					$shopperDynamicVars,
					$lang_code
				);
			}
		}

		$this->session->set_flashdata('success', 'Ticket closed successfully.');

		if ($category > 2) {
			redirect('help_desk/merchant');
		} else {
			redirect('help_desk/shopper');
		}
	}

	/**
	 * Stage 2: Support (@help) assigns ticket to Accounts (@acct) for refund processing
	 */
	public function assign_to_acct($order_id = 0, $ticket_id = '', $product_id = 0)
	{
		if (empty($ticket_id) && empty($order_id)) {
			$this->session->set_flashdata('error', "Invalid ticket parameters.");
			redirect($_SERVER['HTTP_REFERER'] ?? 'help_desk/shopper');
			return;
		}

		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		} else {
			if (!empty($order_id)) $this->db->where('order_id', $order_id);
			if (!empty($product_id)) $this->db->where('products', $product_id);
		}
		$ticket = $this->db->get('help_desk')->row();

		if (!$ticket) {
			$this->session->set_flashdata('error', "Ticket not found.");
			redirect($_SERVER['HTTP_REFERER'] ?? 'help_desk/shopper');
			return;
		}

		$actual_ticket_id = !empty($ticket->ticket_id) ? $ticket->ticket_id : $ticket_id;

		// 1. Update ticket status and assigned role
		$updateData = [
			'status_code'   => 'Processing',
			'assigned_role' => 'Account',
			'status'        => 1,
			'updated_at'    => time(),
		];
		$this->db->where('ticket_id', $actual_ticket_id);
		$this->db->update('help_desk', $updateData);

		// 2. Insert audit conversation message
		$reply_msg = "Action: Ticket assigned to Accounts (@acct) for refund processing.";
		$insertMsg = [
			'ticket_id'                     => $actual_ticket_id,
			'subject'                       => $ticket->subject,
			'category'                      => $ticket->category,
			'priority'                      => $ticket->priority,
			'customer_id'                   => $ticket->customer_id,
			'merchant_id'                   => $ticket->merchant_id,
			'message'                       => '',
			'attachment'                    => '',
			'order_id'                      => $ticket->order_id,
			'products'                      => $ticket->products,
			'admin_reply'                   => $reply_msg,
			'status'                        => 1,
			'status_code'                   => 'Processing',
			'assigned_role'                 => 'Account',
			'merchant_action'               => !empty($ticket->merchant_action) ? $ticket->merchant_action : 'refund_approved',
			'refund_amount'                 => !empty($ticket->refund_amount) ? $ticket->refund_amount : 0.00,
			'refund_deducted_from_holdback' => !empty($ticket->refund_deducted_from_holdback) ? $ticket->refund_deducted_from_holdback : 0,
			'created_at'                    => time(),
			'updated_at'                    => time(),
			'ip'                            => $this->input->ip_address(),
		];
		$this->db->insert('help_desk', $insertMsg);

		// 3. Prepare email context
		$ctx = $this->_get_ticket_email_context($ticket, $order_id, $product_id);
		$lang_code = ($this->session->userdata('site_lang') === 'french' || $this->session->userdata('site_lang') === 'fr') ? 'fr' : 'en';

		// 4. Send email to Merchant (order-resolution-refund-initiated-merchant)
		if (!empty($ctx['merchant_email'])) {
			$merchantDynamicVars = [
				$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
				$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
				$ctx['product_name'], $ctx['product_name'],
				$ctx['merchant_name'], $ctx['merchant_name'],
				$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
				$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
				'Refund Initiated', 'Refund Initiated',
				$ctx['merchant_ticket_url'], $ctx['merchant_ticket_url'],
				'Yellow Markets', 'Yellow Markets'
			];

			$this->CommonModel->sendCommonHTMLEmail(
				$ctx['merchant_email'],
				'order-resolution-refund-initiated-merchant',
				$ctx['tempVars'],
				$merchantDynamicVars,
				$lang_code
			);
		}

		// 5. Send email to @acct (order-resolution-refund-request-acct)
		$acct_row = $this->CommonModel->get_custom_variable('accounting_email');
		if (empty($acct_row) || empty($acct_row->value)) {
			$acct_row = $this->CommonModel->get_custom_variable('acct_email');
		}
		$acct_email = (!empty($acct_row) && !empty($acct_row->value)) ? $acct_row->value : 'accounts@yellowmarkets.com';

		if (!empty($acct_email)) {
			$acctDynamicVars = [
				$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
				$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
				$ctx['product_name'], $ctx['product_name'],
				$ctx['merchant_name'], $ctx['merchant_name'],
				$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
				$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
				'Refund Request', 'Refund Request',
				$ctx['admin_ticket_url'], $ctx['admin_ticket_url'],
				'Yellow Markets', 'Yellow Markets'
			];

			$this->CommonModel->sendCommonHTMLEmail(
				$acct_email,
				'order-resolution-refund-request-acct',
				$ctx['tempVars'],
				$acctDynamicVars,
				'en'
			);
		}

		$this->session->set_flashdata('success', "Ticket successfully assigned to Accounts (@acct) for refund processing.");
		$redirect_path = 'CustomerController/view/' . $ticket->order_id . '/' . $actual_ticket_id . '/' . $ticket->products;
		redirect($redirect_path);
	}

	/**
	 * Stage 3: Accounts (@acct) completes refund processing
	 */
	public function complete_refund($order_id = 0, $ticket_id = '', $product_id = 0)
	{
		if (empty($ticket_id) && empty($order_id)) {
			$this->session->set_flashdata('error', "Invalid ticket parameters.");
			redirect($_SERVER['HTTP_REFERER'] ?? 'help_desk/shopper');
			return;
		}

		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		} else {
			if (!empty($order_id)) $this->db->where('order_id', $order_id);
			if (!empty($product_id)) $this->db->where('products', $product_id);
		}
		$ticket = $this->db->get('help_desk')->row();

		if (!$ticket) {
			$this->session->set_flashdata('error', "Ticket not found.");
			redirect($_SERVER['HTTP_REFERER'] ?? 'help_desk/shopper');
			return;
		}

		$actual_ticket_id = !empty($ticket->ticket_id) ? $ticket->ticket_id : $ticket_id;

		// 1. Update ticket status to Done and mark hold-back deducted
		$updateData = [
			'status_code'                   => 'Done',
			'refund_deducted_from_holdback' => 1,
			'status'                        => 1,
			'updated_at'                    => time(),
		];
		$this->db->where('ticket_id', $actual_ticket_id);
		$this->db->update('help_desk', $updateData);

		// 2. Insert audit conversation message
		$refund_formatted = number_format((float)$ticket->refund_amount, 2);
		$reply_msg = "Action: Refund Completed (Done). Amount: " . $refund_formatted . " processed and deducted from merchant 15-day hold-back sales balance.";
		$insertMsg = [
			'ticket_id'                     => $actual_ticket_id,
			'subject'                       => $ticket->subject,
			'category'                      => $ticket->category,
			'priority'                      => $ticket->priority,
			'customer_id'                   => $ticket->customer_id,
			'merchant_id'                   => $ticket->merchant_id,
			'message'                       => '',
			'attachment'                    => '',
			'order_id'                      => $ticket->order_id,
			'products'                      => $ticket->products,
			'admin_reply'                   => $reply_msg,
			'status'                        => 1,
			'status_code'                   => 'Done',
			'assigned_role'                 => !empty($ticket->assigned_role) ? $ticket->assigned_role : 'Account',
			'merchant_action'               => !empty($ticket->merchant_action) ? $ticket->merchant_action : 'refund_approved',
			'refund_amount'                 => !empty($ticket->refund_amount) ? $ticket->refund_amount : 0.00,
			'refund_deducted_from_holdback' => 1,
			'created_at'                    => time(),
			'updated_at'                    => time(),
			'ip'                            => $this->input->ip_address(),
		];
		$this->db->insert('help_desk', $insertMsg);

		// 3. Prepare email context
		$ctx = $this->_get_ticket_email_context($ticket, $order_id, $product_id);
		$lang_code = ($this->session->userdata('site_lang') === 'french' || $this->session->userdata('site_lang') === 'fr') ? 'fr' : 'en';

		// 4. Send email to Shopper (order-resolution-refund-completed-shopper)
		if (!empty($ctx['shopper_email'])) {
			$shopperDynamicVars = [
				$ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'], $ctx['ticket_id'],
				$ctx['order_number'], $ctx['order_number'], $ctx['order_number'], $ctx['order_number'],
				$ctx['product_name'], $ctx['product_name'],
				$ctx['merchant_name'], $ctx['merchant_name'],
				$ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'], $ctx['shopper_name'],
				$ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'], $ctx['refund_formatted'],
				'Refund Completed', 'Refund Completed',
				$ctx['shopper_ticket_url'], $ctx['shopper_ticket_url'],
				'Yellow Markets', 'Yellow Markets'
			];

			$this->CommonModel->sendCommonHTMLEmail(
				$ctx['shopper_email'],
				'order-resolution-refund-completed-shopper',
				$ctx['tempVars'],
				$shopperDynamicVars,
				$lang_code
			);
		}

		$this->session->set_flashdata('success', "Refund marked as completed (Done). Shopper has been notified.");
		$redirect_path = 'CustomerController/view/' . $ticket->order_id . '/' . $actual_ticket_id . '/' . $ticket->products;
		redirect($redirect_path);
	}

	/**
	 * Helper to build email context and recipient information for ticket workflows
	 */
	private function _get_ticket_email_context($ticket, $order_id = 0, $product_id = 0)
	{
		$ticket_id = !empty($ticket->ticket_id) ? $ticket->ticket_id : '';
		$order_id_val = !empty($ticket->order_id) ? $ticket->order_id : $order_id;
		$product_id_val = !empty($ticket->products) ? $ticket->products : $product_id;
		$refund_amount_val = !empty($ticket->refund_amount) ? (float)$ticket->refund_amount : 0.0;
		$refund_formatted = number_format($refund_amount_val, 2);

		// Shopper details
		$shopper_name = 'Shopper';
		$shopper_email = '';
		if (!empty($ticket->customer_id)) {
			$cust = $this->db->select('first_name, last_name, email_id')->where('id', $ticket->customer_id)->get('customers')->row();
			if ($cust) {
				$shopper_name = trim(($cust->first_name ?? '') . ' ' . ($cust->last_name ?? ''));
				$shopper_email = $cust->email_id ?? '';
			}
		}

		// Order details
		$order_number = (string)$order_id_val;
		if (!empty($order_id_val)) {
			$order_row = $this->db->select('increment_id, customer_email, customer_firstname, customer_lastname')->where('order_id', $order_id_val)->get('sales_order')->row();
			if ($order_row) {
				$order_number = !empty($order_row->increment_id) ? $order_row->increment_id : (string)$order_id_val;
				if (empty($shopper_email) && !empty($order_row->customer_email)) {
					$shopper_email = $order_row->customer_email;
				}
				if (($shopper_name === 'Shopper' || empty($shopper_name)) && (!empty($order_row->customer_firstname) || !empty($order_row->customer_lastname))) {
					$shopper_name = trim(($order_row->customer_firstname ?? '') . ' ' . ($order_row->customer_lastname ?? ''));
				}
			}
		}
		if (empty($shopper_name)) $shopper_name = 'Shopper';

		// Merchant details
		$merchant_name = 'Merchant';
		$merchant_email = '';
		if (!empty($ticket->merchant_id)) {
			$merch = $this->db->select('publication_name, email')->where('id', $ticket->merchant_id)->get('publisher')->row();
			if ($merch) {
				if (!empty($merch->publication_name)) $merchant_name = $merch->publication_name;
				if (!empty($merch->email)) $merchant_email = $merch->email;
			}
		}

		// Product details
		$product_name = 'N/A';
		if (!empty($product_id_val)) {
			$prod = $this->db->select('name')->where('id', $product_id_val)->get('products')->row();
			if ($prod && !empty($prod->name)) {
				$product_name = html_entity_decode($prod->name, ENT_QUOTES, 'UTF-8');
			}
		}

		$delivery_methods = [
			'own_delivery' => 'Own Delivery Service',
			'self_pickup'  => 'Self Pickup',
			'ym_delivery'  => 'YM Delivery Service'
		];
		$delivery_method_name = $delivery_methods[$ticket->delivery_option ?? ''] ?? 'Replacement';

		// URLs
		$shopper_base = 'https://mu.yellowmarkets.com/';
		$shopper_ticket_url = $shopper_base . "MyProfileController/viewTicket/" . $order_id_val . "/" . $ticket_id . ($product_id_val ? '/' . $product_id_val : '');
		$merchant_base = 'https://mu.yellowmarkets.com/merchant/';
		$merchant_ticket_url = $merchant_base . "UserController/view/" . $order_id_val . "/" . $ticket_id . ($product_id_val ? '/' . $product_id_val : '');
		$admin_base = 'https://mu.yellowmarkets.com/admin/';
		$admin_ticket_url = $admin_base . "CustomerController/view/" . $order_id_val . "/" . $ticket_id . ($product_id_val ? '/' . $product_id_val : '');

		$tempVars = [
			'##TICKET_NUMBER##', '##TICKET_ID##', '{ticket_number}', '{ticket_id}',
			'##ORDER_NUMBER##', '##ORDER_NO##', '{order_number}', '{order_no}',
			'##PRODUCT_NAME##', '{product_name}',
			'##MERCHANT_NAME##', '{merchant_name}',
			'##SHOPPER_NAME##', '##CUSTOMER_NAME##', '{shopper_name}', '{customer_name}',
			'##REFUND_AMOUNT##', '##AMOUNT##', '{refund_amount}', '{amount}',
			'##REPLACEMENT_METHOD##', '##DELIVERY_METHOD##', '{replacement_method}', '{delivery_method}',
			'##ACTION##', '{action}',
			'##TICKET_URL##', '{ticket_url}',
			'##WEBSHOPNAME##', '{webshop_name}'
		];

		return [
			'ticket_id'           => $ticket_id,
			'order_id'            => $order_id_val,
			'product_id'          => $product_id_val,
			'order_number'        => $order_number,
			'product_name'        => $product_name,
			'shopper_name'        => $shopper_name,
			'shopper_email'       => $shopper_email,
			'merchant_name'       => $merchant_name,
			'merchant_email'      => $merchant_email,
			'delivery_method'     => $delivery_method_name,
			'refund_amount'       => $refund_amount_val,
			'refund_formatted'    => $refund_formatted,
			'shopper_ticket_url'  => $shopper_ticket_url,
			'merchant_ticket_url' => $merchant_ticket_url,
			'admin_ticket_url'    => $admin_ticket_url,
			'tempVars'            => $tempVars,
		];
	}
	
	public function view($order_id, $ticket_id, $product_id = null) // Added default null
	{
		
		$customer_id = $_SESSION['LoginID'];
		
		// Database query
		
		if (!empty($order_id)) {
			$this->db->where('order_id', $order_id);
		}

		if (!empty($product_id)) {
			$this->db->where('products', $product_id);
		}
		
		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		}

		$this->db->order_by('GREATEST(created_at, IFNULL(updated_at, created_at)) ASC', NULL, FALSE);
		$data['help_desk_data'] = $this->db->get('help_desk')->result();
		
		// Fetch order
		$data['order'] = $this->CommonModel->get_order_by_id($order_id);
		
		// Check if order exists to prevent errors in view
		if (empty($data['order'])) {
			//show_error('Order not found', 404);
		}

		$data['product'] = null;
		if (!empty($product_id)) {
			$data['product'] = $this->CommonModel->get_product_by_order($order_id, $product_id);
		}

		$this->load->view('help_desk_conversation', $data);
	}
}

