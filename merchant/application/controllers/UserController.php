<?php

defined('BASEPATH') or exit('No direct script access allowed');

class UserController extends CI_Controller

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

		$this->load->model('UserModel');

		$this->load->model('CommonModel');

		$this->load->model('Subscription_model');

		$site_lang = $this->session->userdata('site_lang');

		if ($site_lang) {
			$this->lang->load('content', $site_lang);
			$this->lang->load('datatables', $site_lang);
		} else {
			$this->lang->load('content', 'english'); // default
		}
	}



	public function index()

	{

		if(is_logged_in()){

			redirect(BASE_URL('dashboard'));

		}



		$data['PageTitle']= 'Login';

		$this->load->view('login', $data);



	}



	public function register()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['countryList'] = $this->CommonModel->get_countries();

		$data['countryCode'] = $countryCode = $this->UserModel->ip_visitor_country();

		$data['currencyList'] = $this->CommonModel->get_currency();



		$data['currencySymbol'] = $currencySymbol = $this->CommonModel->getCurrencySymbolByCountryCode($countryCode);

		$data['PageTitle']= 'Register';

		$this->load->view('register', $data);



	}



	public function getCongratulationsView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Congratulations';

		$this->load->view('congratulations', $data);



	}



	private function generateToken($length = 20)

	{

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$charactersLength = strlen($characters);

		$randomString = '';

		for ($i = 0; $i < $length; $i++) {

			$randomString .= $characters[rand(0, $charactersLength - 1)];

		}

		return $randomString;

	}



	public function signUpPostData()

	{

		// echo "<pre>";

		// print_r($_POST);die;

		if (isset($_POST) && !empty($_POST)) {

			if(empty($_POST['inputFirstName']) || empty($_POST['inputLastName']) || empty($_POST['inputTradeName']) || empty($_POST['inputShopUrl']) || empty($_POST['inputBrnNumber']) || empty($_POST['inputEmail']) || empty($_POST['inputPassword']) || empty($_POST['inputConfirmPassword'])) {

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

				exit;

			} elseif($_POST['inputPassword'] !== $_POST['inputConfirmPassword']) {

				echo json_encode(array('flag' => 0, 'msg' => "Passwords do not match."));

				exit;

			} else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["inputEmail"])) {

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter a valid Email address."));

				exit;

			}else{



				// If reCAPTCHA response is valid

				$email = $this->CommonModel->custom_filter_input($_POST['inputEmail']);

				$hashPassword = md5($_POST['inputPassword']);

				$existing = $this->db->get_where('publisher', ['email' => $email, 'remove_flag' => 0])->row();
				if ($existing) {
					if ($_POST['lang'] == "french") {
						echo json_encode([
							'flag' => 0,
							'msg' => 'Cet e-mail existe déjà.'
						]);
					}else{
						echo json_encode([
							'flag' => 0,
							'msg' => 'Email already exists'
						]);
					}
					return;
				}

				$insertdata = array(

					'email'					=> $email,

					'password'				=> $hashPassword,

					'first_name'		=> $_POST['inputFirstName'],

					'last_name'		=> $_POST['inputLastName'],

					'publication_name'		=> $_POST['inputTradeName'],

					'vendor_name'		=> $_POST['inputFirstName'].' '.$_POST['inputLastName'],

					'shop_url'		=> $_POST['inputShopUrl'],

					'brn_no'		=> $_POST['inputBrnNumber'],

					'vat_no'		=> $_POST['inputVatNumber'],

					'frontend_register' => $_POST['frontend_register'],

					'created_at'			=> strtotime(date('Y-m-d H:i:s')),

					'ip'					=> $_SERVER['REMOTE_ADDR']

				);

				// Process policy document uploads if present
				$policy_types = ['delivery_policy', 'return_policy', 'refund_policy', 'banner_img'];
				$allowed_exts_map = [
					'delivery_policy' => ['doc','docx','pdf','xlsx','jpg','jpeg','png','rtf'],
					'return_policy'   => ['doc','docx','pdf','xlsx','jpg','jpeg','png','rtf'],
					'refund_policy'   => ['doc','docx','pdf','xlsx','jpg','jpeg','png','rtf'],
					'banner_img'      => ['jpg','jpeg','png']
				];

				foreach ($policy_types as $policy_type) {
					if (!empty($_FILES[$policy_type]['name'])) {
						$config = [];
						$config['upload_path']   = SIS_SERVER_PATH . '/uploads/' . $policy_type . '/';
						$config['allowed_types'] = '*';
						$config['max_size']      = 5120;
						$config['detect_mime']   = FALSE;

						if (!is_dir($config['upload_path'])) {
							mkdir($config['upload_path'], 0777, true);
						}

						$this->load->library('upload');
						$this->upload->initialize($config, true);

						if ($this->upload->do_upload($policy_type)) {
							$uploadData = $this->upload->data();
							$ext = strtolower(pathinfo($uploadData['file_name'], PATHINFO_EXTENSION));
							if (in_array($ext, $allowed_exts_map[$policy_type])) {
								$insertdata[$policy_type] = $uploadData['file_name'];
							}
						}
					}
				}

				//create user

				$this->db->insert('publisher', $insertdata);
				$merchant_id = $this->db->insert_id(); // 🔥 important

				// ================================
				// INSERT NOTIFICATION
				// ================================
				$notification = [
					'type'           => 'account',
					'subtype'        => 'new_merchant',
					'recipient_type' => 'admin',
					'recipient_id'   => 1, // admin id
					'title'          => 'New merchant approval request',
					'message'        => 'New merchant approval request from '.$_POST['inputTradeName'].'.',
					'data'           => json_encode(['merchant_id' => $merchant_id]),
					'is_read'        => 0,
					'created_at'     => date('Y-m-d H:i:s'),
					'updated_at'     => date('Y-m-d H:i:s')
				];

				$this->db->insert('notifications', $notification);

				// Insert default Daily Deals JUPITER entry
				if ($merchant_id) {
					$this->db->insert('merchant_addon_purchases', [
						'merchant_id' => $merchant_id,
						'service_id'  => 4, // Daily Deals JUPITER
						'qty'         => 1,
						'status'      => 'pending',
						'created_at'  => date('Y-m-d H:i:s'),
						'ip'          => $_SERVER['REMOTE_ADDR']
					]);

					$selected_plan_id = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
					if ($selected_plan_id > 0) {
						$valid_plan = $this->Subscription_model->get_plan_by_id($selected_plan_id);
						if (!$valid_plan) {
							$selected_plan_id = 0;
						}
					}
					if (!$selected_plan_id) {
						$starter_plan = $this->Subscription_model->get_default_starter_plan();
						$selected_plan_id = $starter_plan ? (int)$starter_plan['id'] : 2;
					}

					$this->Subscription_model->subscribe_plan($merchant_id, $selected_plan_id, $_SERVER['REMOTE_ADDR']);
				}

				if (isset($_POST['newsletter_signin']) && $_POST['newsletter_signin'] == 'on') {

					$Record = $this->db->where('email', $email)->get('newsletter_subscriber')->row_array();

					

					if ($Record != false) {

						if ($Record['status'] == 2) {

							// Reactivate subscription

							$updateData = [

								'status'     => 1,

								'updated_at' => strtotime(date('Y-m-d H:i:s')),

							];

							$this->db->where('email', $email)->update('newsletter_subscriber', $updateData);

						}

						// else already subscribed → do nothing

					} else {

						// Fresh insert

						$insertdata = [

							'email'      => $email,

							'status'     => 1,

							'created_at' => strtotime(date('Y-m-d H:i:s')),

							'ip'         => $_SERVER['REMOTE_ADDR']

						];

						$this->db->insert('newsletter_subscriber', $insertdata);

					}

				}

				// ================================
				// EMAIL SENDING
				// ================================
				$redirect = BASE_URL2 . "merchants/login";
				$publisherName = $_POST['inputFirstName'] . ' ' . $_POST['inputLastName'];
				$lang = $_POST['lang'] ?? 'english';

				$TempVars = ["##NAME##", "##EMAILID##"];
				$DynamicVars = [$publisherName, $email];

				// ADMIN EMAIL
				$adminEmail = 'anu@bcod.co.in';
				$adminTemplate = ($lang == "french")
					? 'admin-notification-new-merchant-register-fr'
					: 'admin-notification-new-merchant-register';
				
				$adminMailSent = $this->CommonModel->sendCommonHTMLEmail($adminEmail, $adminTemplate, $TempVars, $DynamicVars);
				// print_r($adminMailSent);die;
				if (!$adminMailSent) {
					echo json_encode([
						'flag' => 0,
						'msg'  => 'Admin email failed',
						'smtp_error' => $this->CommonModel->lastEmailError
					]);
					exit;
				}

				// MERCHANT EMAIL
				$merchantTemplate = ($lang == "french")
					? 'website-merchant-registration-fr'
					: 'website-merchant-registration';

				$merchantMailSent = $this->CommonModel->sendCommonHTMLEmail($email, $merchantTemplate, $TempVars, $DynamicVars);

				if (!$merchantMailSent) {
					echo json_encode([
						'flag' => 0,
						'msg'  => 'Merchant email failed',
						'smtp_error' => $this->CommonModel->lastEmailError
					]);
					exit;
				}

				// ================================
				// SUCCESS RESPONSE
				// ================================
				echo json_encode([
					'flag' => 1,
					'msg'  => ($lang == "french")
						? 'Merci pour votre inscription. Veuillez vérifier votre e-mail.'
						: 'Thank you for registering. Please check your email.',
					'redirect' => $redirect
				]);

				exit;

			}



		}else {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

			exit;

		}

	}



	public function setEmailVerificationFlag(){

		$urlData = $this->uri->segment(2);

		$isValid = false;

		if(!empty($urlData)){

			$decoded_data = json_decode(base64_decode($urlData),true);

			if(is_array($decoded_data) && count($decoded_data)> 0){

				$email_id  = $decoded_data['email'];

				$fbc_user_id = $decoded_data['id'];

				if(preg_match("/^[1-9][0-9]*$/",$fbc_user_id) || preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email_id)){

					$userData = $this->UserModel->getUserDetails($fbc_user_id,$email_id);

					if(!empty($userData)){

						$isValid = true;

						$isVerified = $userData->email_verification_status;

						$database_name = 'shopinshop_shop_'.$userData->shop_id;



						if($isVerified == 0){

							$updateData = array(

								'status' => 1,

								'email_verification_status' => 1,

								//'database_name' => $database_name,

								'email_verified_on' => strtotime(date('Y-m-d H:i:s')),

							);



							$this->db->where(array('fbc_user_id' => $fbc_user_id));

							$this->db->update('fbc_users', $updateData);



							if($this->db->affected_rows() > 0)

							{

								 $templateId ='fbcuser-register-successful';

								 $to = $email_id;



								$ShopDetails = $this->UserModel->getShopDetailsByShopId($userData->shop_id);

								$username = $ShopDetails->org_shop_name;

								// $TempVars = array();

								// $DynamicVars = array();



								 $TempVars = array("##USERNAME##" );

								 $DynamicVars   = array($username);

								// //echo $to;

								 $mailSent = $this->CommonModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars);



								redirect(BASE_URL('email/verification-successful'));

								//return true;

							}else{

								redirect(BASE_URL('email/verification-unsuccessful'));

							}

						}else if($isVerified == 1){

							redirect(BASE_URL('email/already-verified'));

						}

					}

				}

			}

		}



		if($isValid == false){

			redirect(BASE_URL);

		}

	}



	public function getEmailVerifySuccessView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Veification Successful';

		$this->load->view('email_verification/email_verification_successful', $data);

	}



	public function getEmailVerifyUnsuccessView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Veification Unsuccessful';

		$this->load->view('email_verification/email_verification_unsuccessful', $data);

	}



	public function getEmailAlreadyVerifiedView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Already Verified';

		$this->load->view('email_verification/email_already_verified', $data);

	}



	public function loginPost()

	{

		if (empty($_POST['inputEmail'])) {
			echo json_encode([
				'flag' => 0,
				'msg'  => "Email is required."
			]);
			exit;
		}

		if (empty($_POST['inputPassword'])) {
			echo json_encode([
				'flag' => 0,
				'msg'  => "Password is required."
			]);
			exit;
		}




		$email    = trim($_POST['inputEmail'] ?? '');

		$password = trim($_POST['inputPassword'] ?? '');

		$remember = isset($_POST['remember']);



		if (empty($email) || empty($password)) {

			echo json_encode(['flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."]);

			exit;

		}



		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

			echo json_encode(['flag' => 0, 'msg' => "Please enter a valid Email address."]);

			exit;

		}



		$email      = $this->CommonModel->custom_filter_input($email);

		$enc_pass   = md5($password); // TODO: switch to password_hash in future

		$sess_pass  = $password;



		$anyUser = $this->db->get_where('publisher', ['email' => $email])->row();
		if ($anyUser && isset($anyUser->remove_flag) && $anyUser->remove_flag == 1) {
			if (($_POST['lang'] ?? '') === 'french'){
				echo json_encode(['flag' => 0, 'msg' => "Votre compte a été supprimé."]);
			} else {
				echo json_encode(['flag' => 0, 'msg' => "Your account has been deleted."]);
			}
			exit;
		}

		$UserDetails = $this->UserModel->getUserByEmail($email);
		$UserdataDetails = $this->UserModel->getUserdataByEmail($email);
		



		if (!$UserDetails) {

			echo json_encode(['flag' => 0, 'msg' => "User has not registered with this email address."]);

			exit;

		}


		

		if ($UserDetails->status != 1) {
			if ($_POST['lang'] === 'french'){
				echo json_encode(['flag' => 0, 'msg' => "Votre profil est actuellement en cours de vérification. Veuillez revenir plus tard."]);
			} else {
				echo json_encode(['flag' => 0, 'msg' => "Your profile is currently under review. Please check back later."]);
				// exit;
			}
			exit;
		}




		if ($UserDetails->password !== $enc_pass) {

			echo json_encode(['flag' => 0, 'msg' => "Invalid Email or Password."]);

			exit;

		}



		// -------------------------------

		// Remember Me functionality

		// -------------------------------

		if ($remember) {

			$remember_token = $this->generateToken();



			$this->UserModel->updateRememberToken($UserDetails->id, $remember_token);



			// Cookies automatically get "merchant_" prefix

			set_cookie('login_email',    $email,       10 * 365 * 24 * 60 * 60);

			set_cookie('login_password', $sess_pass,   10 * 365 * 24 * 60 * 60);

			set_cookie('remember_token', $remember_token, 10 * 365 * 24 * 60 * 60);



		} else {

			$this->UserModel->updateRememberToken($UserDetails->id, null);



			delete_cookie('login_email');

			delete_cookie('login_password');

			delete_cookie('remember_token');

		}



		// -------------------------------

		// Session Creation

		// -------------------------------

		$LoginToken = $this->generateToken();

		$LoginID    = $UserDetails->id;



		$this->session->set_userdata([

			'LoginID'    => $LoginID,

			'LoginToken' => $LoginToken

		]);



		$this->UserModel->insertIntoLoginSession($LoginToken, $LoginID);



		// Save Last login

		$this->db->where('id', $LoginID)->update('adminusers', [

			'last_login_at' => time()

		]);


		/*echo "<pre>";
		print_r($UserDetails);
		exit;*
		/*$redirect = base_url("dashboard");
		echo json_encode([
			'flag'     => 1,
			'msg'      => "Logged in Successfully",
			'redirect' => $redirect
		]);*/

		$redirect = base_url("dashboard");

		// Required fields
		$requiredFields = [
			'publication_name',
			'merchant_cat',
			'bank_name',
			'bank_branch_number',
			'beneficiary_acc_no',
			'beneficiary_name',
			'beneficiary_ifsc_code',
			'vendor_name',
			'shop_image',
			'commision_percent',
			'phone_no',
			'company_name',
			'company_address',
			'location',
			'state',
			'city',
			'zipcode'
		];

		// Check if any required field is empty
		foreach ($requiredFields as $field) {
			if (empty($UserdataDetails->$field)) {
				$encodedId = rtrim(strtr(base64_encode($UserDetails->id), '+/', '-_'), '=');
				$redirect = base_url("PublisherController/editMerchant/" . $encodedId);
				break;
			}
		}

		// Extra check: If VAT is registered, vat_no must not be empty
		if ($UserDetails->vat_status == 'registered' && empty($UserDetails->vat_no)) {
			$encodedId = rtrim(strtr(base64_encode($UserDetails->id), '+/', '-_'), '=');
			$redirect = base_url("PublisherController/editMerchant/" . $encodedId);
		}	

		if ($_POST['lang'] === 'french'){
			// echo json_encode(['flag' => 0, 'msg' => "Votre profil est actuellement en cours de vérification. Veuillez revenir plus tard."]);
			echo json_encode([
				'flag'     => 1,
				'msg'      => "Connexion réussie",
				'redirect' => $redirect
			]);
		} else {
			echo json_encode([
				'flag'     => 1,
				'msg'      => "Logged in Successfully",
				'redirect' => $redirect
			]);
		}
		exit;

		


		exit;

	}



    public function logout()

    {

        if ($this->session->userdata('LoginToken') != '') {

			$LoginToken = $this->session->userdata('LoginToken');

			$LoginID = $this->session->userdata('LoginID');





			$updatetime = array('logout_time' => strtotime(date('Y-m-d H:i:s')));

			$this->db->where(array('id' => $LoginID, 'sessionid' => $LoginToken));

			$this->db->update('adminsession', $updatetime);

			session_destroy();

			redirect(BASE_URL);

        } else {

			redirect(BASE_URL);

        }

    }



	public function forgotPassword(){

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		if(empty($_POST)){

			$data['PageTitle']= 'Forgot Password';

			$this->load->view('forgot_password/forgot_password', $data);

		}else{

			if (empty($_POST['inputEmail'])) {

                echo json_encode(array('flag' => 0,'msg' => "Please enter all mandatory / compulsory fields." ));

				exit;

            } else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["inputEmail"])) {

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter a valid Email address."));

				exit;

			} else {

				$email = $this->CommonModel->custom_filter_input($_POST['inputEmail']);

				$userExist = $this->UserModel->checkUserExistByEmail($email);

				if($userExist == 0){

					echo json_encode(array('flag'=>0, 'msg'=>"User has not registered with this email address."));

					exit;

				}



				$UserDetails = $this->UserModel->getUserByEmail($email);

				$fbc_user_id = $UserDetails->fbc_user_id;

				$shop_id = $UserDetails->shop_id;



				$isActive = $UserDetails->status;

				$isVerified = $UserDetails->email_verification_status;

				if($isActive == 0){

					echo json_encode(array('flag' => 0,'msg' => "Please verify your email id first."));

					exit;

				}





				$ShopDetails = $this->UserModel->getShopDetailsByShopId($shop_id);

				$name = $ShopDetails->org_shop_name;

				$data['id'] = $fbc_user_id;

				//$data['token'] = sha1($email);

				$data['token'] = $email;

				//$data['expTime'] = date('Y-m-d h:i:s', time() + (60*60*1));



				$this->UserModel->updatePasswordResetToken($fbc_user_id, sha1($email));



				// encode

				//$encoded_data = rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

				$encoded_data = rtrim(base64_encode(json_encode($data)), '=');

				$reset_url = BASE_URL."reset-password/".$encoded_data;

				$this->session->set_flashdata('reset_link',$reset_url);

				// decode

				$email_decoded = base64_decode(strtr($encoded_data, '-_', '+/'));



				 $templateId ='fbcuser-reset_password';

				 $to = $email;

				 $link = BASE_URL."reset-password/".$encoded_data;

				 $username = $name;

				// $TempVars = array();

				// $DynamicVars = array();



				 $TempVars = array("##USERNAME##" ,"##RESETPASSWORDLINK##");

				 $DynamicVars   = array($username,$link);

				// //echo $to;

				 $mailSent = $this->CommonModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars);

				// print_r($mailSent);



				$redirect = base_url() . "reset-password";

				echo json_encode(array('flag' => 1, 'msg' => "Password reset link has been sent successfully", 'redirect' => $redirect));

				exit;

			}

		}

	}

	public function forgotPasswordNew()

	{

		$data['PageTitle'] = 'Forgot Password';

		$this->load->view('forgot_password/forgot_password_new', $data);

	}

	public function forgotPasswordNew1()

	{

		// print_r('hiiii');die;

		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';

		// print_r($LogindID);

		$inputPassword = $this->input->post('inputPassword');

		// print_r($inputPassword);die;

		$inputConfPassword = $this->input->post('inputConfPassword');

		$password = md5($inputConfPassword);



		$res = $this->UserModel->updatePassword($LogindID, $inputPassword, $password);



		$arrResponse  = array('status' =>200 ,'message'=>'Updated Successfully!');

		echo json_encode($arrResponse);exit;

	}

	public function resetPassword(){

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$urlData = $this->uri->segment(2);

		if(empty($urlData)){

			$data['PageTitle']= 'Reset Password';

			$this->load->view('forgot_password/reset_password_link', $data);

		}else{

			$isValid = false;

			// decode

			$decoded_data = json_decode(base64_decode($urlData),true);

			if(is_array($decoded_data) && count($decoded_data)> 0){

				/*$currTime = strtotime(date('Y-m-d h:i:s'));

				$expTime = strtotime($decoded_data['expTime']);

				if($currTime > $expTime){

					redirect(BASE_URL('reset-password-invalid-link'));

					//echo json_encode(array('flag'=>0, 'msg'=>"Token has expired."));

					//exit;

				}else{*/



					$fbc_user_id = $decoded_data['id'];

					$userDetails = $this->UserModel->getUserByUserId($fbc_user_id);

					if(!empty($userDetails) && $userDetails->status == 1){



						$token = $userDetails->password_reset_token;

						//if($decoded_data['token'] == $token){

						if(sha1($decoded_data['token']) == $token){

							$isValid = true;

							if(empty($_POST)){

								$data['urlData']= $urlData;

								$data['PageTitle']= 'Reset Password';

								$this->load->view('forgot_password/reset_password', $data);

							}else{

								if($_POST['reset-pass-btn'] == 'Submit'){

									if(empty($_POST['inputPassword']) || empty($_POST['inputConfPassword'])){

										echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

										exit;

									}else if($_POST['inputPassword'] != $_POST['inputConfPassword']){

										echo json_encode(array('flag'=>0, 'msg'=>"Confirm Password does not match."));

										exit;

									}else{

										$password = $_POST['inputPassword'];

										$hashPassword = password_hash($password, PASSWORD_DEFAULT);

										$this->UserModel->updatePassword($fbc_user_id,$hashPassword);



										if($this->db->affected_rows() > 0)

										{

											$redirect = base_url() . "reset-password-successful";

											echo json_encode(array('flag' => 1, 'msg' => "Password reset successful", 'redirect' => $redirect));

											exit;

										}else{

											$redirect = base_url() . "reset-password-unsuccessful";

											echo json_encode(array('flag' => 1, 'msg' => "Password reset unsuccessful", 'redirect' => $redirect));

											exit;

										}

									}

								}

							}

						}

					}

				//}

			}

			if($isValid == false){

				redirect(BASE_URL('reset-password-invalid-link'));

			}

		}

	}



	public function getPasswordSuccessView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Password Reset Successful';

		$this->load->view('forgot_password/reset_password_successful', $data);

	}



	public function getPasswordUnsuccessView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Password Reset Unsuccessful';

		$this->load->view('forgot_password/reset_password_unsuccessful', $data);

	}



	public function getPasswordInvalidLinkView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Reset Invalid Link';

		$this->load->view('forgot_password/reset_password_invalid_link.php', $data);

	}



	public function getPasswordLinkView()

	{

		if(isset($_SESSION['LoginID']) && $_SESSION['LoginID']!=''){

			redirect(BASE_URL('dashboard'));

		}

		$data['PageTitle']= 'Sent Reset Link';

		$this->load->view('forgot_password/reset_password_link.php', $data);

	}



	//**

	public function settings()

	{

		if($_SESSION['UserRole'] !== 'Super Admin') {

			if(!empty($this->session->userdata('userPermission')) && !in_array('system/settings',$this->session->userdata('userPermission'))){ 

				redirect('dashboard');

			}

		}

		

		$data['PageTitle']= 'Settings';

		$data['countryList'] = $this->CommonModel->get_countries();



		$fbc_user_id	=	$this->session->userdata('LoginID');

		$shop_id		=	$this->session->userdata('ShopID');



		//$data['FBCUserData']=$FBCUserData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'country_code,currency_code,currency_symbol,fbc_user_id,shop_flag,zumba_api_flag,vat_flag');

		// $args['shop_id']	=	$shop_id;

		// $args['fbc_user_id']	=	$fbc_user_id;



		$this->load->model('ShopProductModel');



		// $this->ShopProductModel->init($args);

		// echo 'bhh';exit;



		// $data['ProductCount'] = $ProductCount = $this->ShopProductModel->getSellerProductCount();



		$return_identifier= 'product_return_duration';

		$delivery_identifier = 'product_delivery_duration';

		$vies_checker_identifier = 'vies_checker_time_in_hr';

		$delay_warehouse_identifier ='delay_warehouse';

		$review_contact_recipient_identifier ='review_contact_recipient';

		$data['product_return_duration'] = $product_return_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>$return_identifier),'cv.*');

		$data['zin_customer_type_id'] = $zin_customer_type_id = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>'zin_customer_type_id'),'cv.*');

		$data['product_delivery_duration'] = $product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>$delivery_identifier),'cv.*');

		$data['delay_warehouse'] = $delay_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>$delay_warehouse_identifier),'cv.*');

		$data['vies_checker_time_in_hr'] = $vies_checker_time_in_hr = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>$vies_checker_identifier),'cv.*');

		$data['review_contact_recipient'] = $review_contact_recipient = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>$review_contact_recipient_identifier),'cv.*');

		$data['custom_variables'] =  $this->CommonModel->get_custom_variables();

		$data['customer_types'] =  $this->CommonModel->get_customer_types();

		$data['country_master'] =  $this->CommonModel->get_shop_country_master();

		$data['cms_pages']= $this->CommonModel->get_cms_pages();

		$data['customers_info']= $this->CommonModel->get_customers_info();

		$data['webshopcust_def_inv_altemail']=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');

		$data['rounded_webshop_prices']=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'rounded_webshop_prices'),'value');

		$data['use_advanced_warehouse']=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

		$data['use_base_colors']=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_base_colors'),'value');

		// echo "<pre>";print_r($data['webshopcust_def_inv_altemail']->value);

		// die();

		$this->load->view('settings', $data);

	}



	public function update_settings()

	{

		// echo "<pre>";print_r($_POST);die;
		$LoginID= $this->session->userdata('LoginID');



			if(isset($_POST['shipment_countries']) && $_POST['shipment_countries'] != '')

			{

				$countries = implode(",",$_POST['shipment_countries']);

				$data['update_variable']['shipping_country'] = $countries ;

			}

			else

			{

				$countries = '';

				$data['update_variable']['shipping_country'] = $countries ;

			}

			foreach($_POST as $key=>$val)

			{



				if($key != 'user_id' && $key != 'currency'  && $key != 'country')

				{



					$data['update_variable'][$key] = $val;

				}



			}





			if(!isset($_POST['browse_by_gender_enabled']) ||  $_POST['browse_by_gender_enabled'] == '')

			{

				$data['update_variable']['browse_by_gender_enabled'] = "no";

			}

			else

			{

				$data['update_variable']['browse_by_gender_enabled'] = "yes";

			}



			if(!isset($_POST['request_for_invoice_default_webcust']) ||  $_POST['request_for_invoice_default_webcust'] == '')

			{

				$data['update_variable']['request_for_invoice_default_webcust'] = "no";

			}

			else

			{

				$data['update_variable']['request_for_invoice_default_webcust'] = "yes";

			}

			if(!isset($_POST['pickinglist_show_cust_addr']) ||  $_POST['pickinglist_show_cust_addr'] == '')

			{

				$data['update_variable']['pickinglist_show_cust_addr'] = "no";

			}

			else

			{

				$data['update_variable']['pickinglist_show_cust_addr'] = "yes";

			}



			if(isset($_POST['smtp_host']) ||  $_POST['smtp_host'] != '')

			{

				$data['update_variable']['smtp_host'] = $_POST['smtp_host'];

			}

			if(isset($_POST['smtp_port']) ||  $_POST['smtp_port'] != '')

			{

				$data['update_variable']['smtp_port'] = $_POST['smtp_port'];

			}

			if(isset($_POST['smtp_username']) ||  $_POST['smtp_username'] != '')

			{

				$data['update_variable']['smtp_username'] = $_POST['smtp_username'];

			}



			if(isset($_POST['smtp_password']) ||  $_POST['smtp_password'] != '')

			{

				$data['update_variable']['smtp_password'] = $_POST['smtp_password'];

			}



			if(isset($_POST['smtp_secure']) ||  $_POST['smtp_secure'] != '')

			{

				$data['update_variable']['smtp_secure'] = $_POST['smtp_secure'];

			}



			if(!isset($_POST['out_of_stock']) ||  $_POST['out_of_stock'] == '')

			{

				$data['update_variable']['out_of_stock'] = "no";

			}

			else

			{

				$data['update_variable']['out_of_stock'] = "yes";

			}



			if(!isset($_POST['restricted_access']) ||  $_POST['restricted_access'] == '')

			{

				$data['update_variable']['restricted_access'] = "no";

			}

			else

			{

				$data['update_variable']['restricted_access'] = "yes";

			}



			if(isset($_POST['msg_for_customer']) ||  $_POST['msg_for_customer'] != '')

			{

				$data['update_variable']['msg_for_customer'] = $_POST['msg_for_customer'];

			}



			if(!isset($_POST['order_check_termsconditions']) ||  $_POST['order_check_termsconditions'] == '')

			{

				$data['update_variable']['order_check_termsconditions'] = "no";

			}

			else

			{

				$data['update_variable']['order_check_termsconditions'] = "yes";

			}



			if(!isset($_POST['general_log_zinapi']) ||  $_POST['general_log_zinapi'] == '')

			{

				$data['update_variable']['general_log_zinapi'] = "no";

			}

			else

			{

				$data['update_variable']['general_log_zinapi'] = "yes";

			}



				 // echo "<pre>";print_r($data['update_variable']);die();



			//invoice new

			if(!isset($_POST['invoice_logo']) ||  $_POST['invoice_logo'] == '')

			{

				$data['update_variable']['invoice_logo'] = "no";

			}

			else

			{

				$data['update_variable']['invoice_logo'] = "yes";

			}



			if(!isset($_POST['invoice_webshop_name']) ||  $_POST['invoice_webshop_name'] == '')

			{

				$data['update_variable']['invoice_webshop_name'] = "no";

			}

			else

			{

				$data['update_variable']['invoice_webshop_name'] = "yes";

			}

			if(!isset($_POST['online_stripe_payment_refund']) ||  $_POST['online_stripe_payment_refund'] == '')

			{

				$data['update_variable']['online_stripe_payment_refund'] = "no";

			}

			else

			{

				$data['update_variable']['online_stripe_payment_refund'] = "yes";

			}

			if(!isset($_POST['captcha_check_flag']) ||  $_POST['captcha_check_flag'] == '')

			{

				$data['update_variable']['captcha_check_flag'] = "no";

			}

			else

			{

				$data['update_variable']['captcha_check_flag'] = "yes";

			}

			if(!isset($_POST['use_advanced_warehouse']) ||  $_POST['use_advanced_warehouse'] == '')

			{

				$data['update_variable']['use_advanced_warehouse'] = "no";

			}

			else

			{

				$data['update_variable']['use_advanced_warehouse'] = "yes";

			}

			if(isset($_POST['invoice_bottom_message']) ||  $_POST['invoice_bottom_message'] != '')

			{

				$data['update_variable']['invoice_bottom_message'] = $_POST['invoice_bottom_message'];

			}



			if(isset($_POST['shipping_method_not_available']) ||  $_POST['shipping_method_not_available'] != '')

			{

				$data['update_variable']['shipping_method_not_available'] = $_POST['shipping_method_not_available'];

			}



			if(isset($_POST['review_contact_recipient']) ||  $_POST['review_contact_recipient'] != '')

			{

				$data['update_variable']['review_contact_recipient'] = $_POST['review_contact_recipient'];

			}



			if(!isset($_POST['rounded_webshop_prices']) ||  $_POST['rounded_webshop_prices'] == ''){

				$data['update_variable']['rounded_webshop_prices'] = 0;

			}else{

				$data['update_variable']['rounded_webshop_prices'] = 1;

			}



			if (!isset($_POST['use_base_colors']) || $_POST['use_base_colors'] == '')

			{

				$data['update_variable']['use_base_colors'] = "no";

			}

			else

			{

				$data['update_variable']['use_base_colors'] = "yes";

			}

			//end invoice new

			

		$this->CommonModel->update_custom_variable_master($data['update_variable']);

		$ym_charge = $this->input->post('ym_charge');  // Get POST value
		$exists = $this->db->get_where('custom_variables', ['identifier' => 'ym_charge'])->row();

		// ✅ Case 1: Value is posted (not empty)
		if ($ym_charge !== null && $ym_charge !== '') {

			if ($exists) {
				// Update existing record — ensure name exists
				$updateData = [
					'value'      => $ym_charge,
					'updated_at' => time(),
					'updated_by' => $LoginID
				];

				if (empty($exists->name)) {
					$updateData['name'] = 'YM Charge';
				}

				$this->db->where('identifier', 'ym_charge')
						->update('custom_variables', $updateData);

			} else {
				// Insert new record
				$this->db->insert('custom_variables', [
					'identifier'      => 'ym_charge',
					'name'            => 'YM Charge',
					'value'           => $ym_charge,
					'created_by_type' => 0,
					'created_at'      => time(),
					'created_by'      => $LoginID,
					'updated_at'      => time(),
					'updated_by'      => $LoginID,
					'ip'              => $_SERVER['REMOTE_ADDR']
				]);
			}

		} else {

			// ✅ Case 2: No value posted (unchecked/empty)
			if (!$exists) {
				// Insert default = 0
				$this->db->insert('custom_variables', [
					'identifier'      => 'ym_charge',
					'name'            => 'YM Charge',
					'value'           => 0,
					'created_by_type' => 0,
					'created_at'      => time(),
					'created_by'      => $LoginID,
					'updated_at'      => time(),
					'updated_by'      => $LoginID,
					'ip'              => $_SERVER['REMOTE_ADDR']
				]);
			} else {
				// Update to default = 0 — ensure name exists
				$updateData = [
					'value'      => 0,
					'updated_at' => time(),
					'updated_by' => $LoginID
				];

				if (empty($exists->name)) {
					$updateData['name'] = 'YM Charge';
				}

				$this->db->where('identifier', 'ym_charge')
						->update('custom_variables', $updateData);
			}
		}




		//$fbc_user_id = $this->CommonModel->custom_filter_input($_POST['user_id']);



		// if(isset($_POST['country']) && isset($_POST['currency'])){



		// 	$country_code = $this->CommonModel->custom_filter_input($_POST['country']);

		// 	$curValue= explode('/', $_POST['currency']);



		// 		$insertShopdata = array(

		// 			'country_code'			=> $country_code,

		// 			'currency_code'		=> $curValue[0],

		// 			'currency_symbol'		=> $curValue[1],

		// 		);

		// 		$this->db->where('fbc_user_id',$fbc_user_id);

		// 		$this->db->update('fbc_users_shop', $insertShopdata);

		// }



		$return_identifier= 'product_return_duration';

		$delivery_identifier = 'product_delivery_duration';

		$vies_checker_identifier = 'vies_checker_time_in_hr';

		$delay_warehouse_identifier ='delay_warehouse';

		if(isset($_POST['product_return_duration']) && $_POST['product_return_duration']!=''){

			$product_return_duration = $this->CommonModel->custom_filter_input($_POST['product_return_duration']);

			$product_return_duration_arr = array(

					'value' => $product_return_duration,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);



			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> $return_identifier),$product_return_duration_arr);

			}



		if(isset($_POST['product_delivery_duration']) && $_POST['product_delivery_duration']!=''){

			$product_delivery_duration = $this->CommonModel->custom_filter_input($_POST['product_delivery_duration']);

			$product_delivery_duration_arr = array(

					'value' => $product_delivery_duration,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);



			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> $delivery_identifier),$product_delivery_duration_arr);

			}



		if(isset($_POST['delay_warehouse']) && $_POST['delay_warehouse']!=''){

			$delay_warehouse = $this->CommonModel->custom_filter_input($_POST['delay_warehouse']);

			$delay_warehouse_arr = array(

					'value' => $delay_warehouse,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);



			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> $delay_warehouse_identifier),$delay_warehouse_arr);

		}



		if(isset($_POST['vies_checker_time_in_hr']) && $_POST['vies_checker_time_in_hr']!=''){

			$vies_checker_time_in_hr = $this->CommonModel->custom_filter_input($_POST['vies_checker_time_in_hr']);

			$vies_checker_time_in_hr_arr = array(

					'value' => $vies_checker_time_in_hr,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);



			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> $vies_checker_identifier),$vies_checker_time_in_hr_arr);

		}



		$customer_id = "";

		if(isset($_POST['customer_id']) && $_POST['customer_id']!=''){

			$customer_id = $_POST['customer_id'];

		}



		$cust_identifier= 'webshopcust_def_inv_altemail';

		$update_arr = array(

				'value' => $customer_id,

				'updated_at' => time(),

				'updated_by' => $LoginID,

				'ip' =>$_SERVER['REMOTE_ADDR']

			);



		$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> $cust_identifier),$update_arr);



		if(isset($_POST['zin_customer_type_id']) &&  $_POST['zin_customer_type_id'] != '')

			{

				$id_val_arr = explode('|', $_POST['zin_customer_type_id']);

				$update_arr = array(

					'name' => $id_val_arr[0],

					'value' => $id_val_arr[1],

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);

				$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> 'zin_customer_type_id'),$update_arr);

			}



		// invoice

		if(isset($_POST['invoice_add_field1_name']) && isset($_POST['invoice_add_field2_name'])){

			$invoice_add_field1_name=$this->CommonModel->custom_filter_input($_POST['invoice_add_field1_name']);

			$invoice_add_field1_value=$this->CommonModel->custom_filter_input($_POST['invoice_add_field1_value']);

			$invoice_add_field2_name=$this->CommonModel->custom_filter_input($_POST['invoice_add_field2_name']);

			$invoice_add_field2_value=$this->CommonModel->custom_filter_input($_POST['invoice_add_field2_value']);

			$invoice_field1 = array(

					'name' => $invoice_add_field1_name,

					'value' => $invoice_add_field1_value,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);

			$invoice_field2 = array(

					'name' => $invoice_add_field2_name,

					'value' => $invoice_add_field2_value,

					'updated_at' => time(),

					'updated_by' => $LoginID,

					'ip' =>$_SERVER['REMOTE_ADDR']

				);

			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> 'invoice_add_field1','identifier'=> 'invoice_add_field1'),$invoice_field1);

			// print_r($invoiceFiled1);exit();

			$this->CommonModel->update_custom_variable('custom_variables',array('identifier'=> 'invoice_add_field2'),$invoice_field2);

		}





		redirect(base_url()."UserController/settings");

	}



	public function exceptional_taxes_settings()

	{



		$data['PageTitle']= 'Exceptional Taxes Settings';

		$data['side_menu']='System';

		$fbc_user_id	=	$this->session->userdata('LoginID');

		$shop_id		=	$this->session->userdata('ShopID');

		$this->load->model('WebshopModel');

		$data['exceptional_tax_set_info']=$FBCUserData=$this->CommonModel->getSingleDataByID('global_custom_variables as gcv',array('identifier'=>'exceptional-tax-set-info'),'gcv.*');



		$data['exceptional_taxes_set_info'] = $this->CommonModel->get_exceptional_taxes_set();



		// print_r($data['exceptional_taxes_set_info']);exit;

		if(isset($data['exceptional_taxes_set_info']) && $data['exceptional_taxes_set_info'] !='')

		{

			$data['categoryMenu'] = $this->CommonModel->get_exceptional_CatMenus($data['exceptional_taxes_set_info']->id);

			$data['browse_category'] = $this->WebshopModel->getAllCategories_Exceptional($data['exceptional_taxes_set_info']->id);



		}else{

			$data['browse_category'] = $this->WebshopModel->getAllCategories_Exceptional();

		}



		// echo "<pre>";		print_r($data['categoryMenu']); 		die();



		$this->load->view('exceptional_taxes_settings', $data);

	}



	public function update_exceptional_tax_set()

	{

		$fbc_user_id = $this->session->userdata('LoginID');

		$this->load->model('WebshopModel');

		if(isset($_POST))

		{

			// $data['exceptional_taxes_set_info'] = $this->UserModel->get_exceptional_taxes_set();

			$row_id = isset($_POST['row_id']) ? $this->CommonModel->custom_filter_input($_POST['row_id']) : '';

			if($row_id =='')

			{

				$insertdata=array(

							'less_than_amount' => isset($_POST['less_than_amount']) ? $_POST['less_than_amount'] : '',

							'less_than_tax_percent'=> isset($_POST['less_than_tax_percent']) ? $_POST['less_than_tax_percent'] : '',

							'created_by'=>$fbc_user_id,

							'created_at'=>time(),

							'ip'=>$_SERVER['REMOTE_ADDR']

							);

				$rowAffected = $this->WebshopModel->insertData('exceptional_taxes_set',$insertdata);



				$chkMenuArray = isset($_POST['chk_cat_menu']) ? $_POST['chk_cat_menu'] : array();



				if(empty($chkMenuArray) && $row_id ==''){

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));exit;

				}else

				{

					$chkMenuArray = $_POST['chk_cat_menu'];



					$where_arr=array('exc_taxes_id'=>$rowAffected);



					$selectedRow = $this->WebshopModel->getWhere('exceptional_taxes_set_details',$where_arr);

					if(count($selectedRow) > 0){



						$del_rowAffected = $this->WebshopModel->deleteData('exceptional_taxes_set_details',$where_arr);

						if($del_rowAffected){

							foreach ($chkMenuArray as $value) {

								$insertdata=array(

									'exc_taxes_id' => $rowAffected,

									'category_id'=> $value

								);

								$cat_menu = $this->WebshopModel->insertData('exceptional_taxes_set_details',$insertdata);

							}

						}

					}else{

						foreach ($chkMenuArray as $value) {

							$insertdata=array(

								'exc_taxes_id' => $rowAffected,

								'category_id'=> $value

							);

							$cat_menu = $this->WebshopModel->insertData('exceptional_taxes_set_details',$insertdata);

						}

					}



					if($cat_menu){

						$redirect = base_url('UserController/exceptional_taxes_settings');

						echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));exit;

					}else{

						echo json_encode(array('flag' => 0, 'msg' => "went somthing wrong!"));exit;

					}



				}





			}else

			{

				$where_arr=array('id'=>$row_id);

				$updatedata=array(

							'less_than_amount' => isset($_POST['less_than_amount']) ? $_POST['less_than_amount'] : '',

							'less_than_tax_percent'=> isset($_POST['less_than_tax_percent']) ? $_POST['less_than_tax_percent'] : '',

							'created_by'=>$fbc_user_id,

							'updated_at'=>time(),

							'ip'=>$_SERVER['REMOTE_ADDR']

							);

				$rowAffected = $this->WebshopModel->updateNewData('exceptional_taxes_set',$where_arr,$updatedata);



				$chkMenuArray = isset($_POST['chk_cat_menu']) ? $_POST['chk_cat_menu'] : array();

				if(empty($chkMenuArray) || $row_id ==''){

					echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));exit;

				}else

				{

				$where_arr=array('exc_taxes_id'=>$row_id);



					$selectedRow = $this->WebshopModel->getWhere('exceptional_taxes_set_details',$where_arr);

					if(count($selectedRow) > 0){



						$del_rowAffected = $this->WebshopModel->deleteData('exceptional_taxes_set_details',$where_arr);

						if($del_rowAffected){

							foreach ($chkMenuArray as $value) {

								$insertdata=array(

									'exc_taxes_id' => $row_id,

									'category_id'=> $value

								);

								$cat_menu = $this->WebshopModel->insertData('exceptional_taxes_set_details',$insertdata);

							}

						}

					}else{

						foreach ($chkMenuArray as $value) {

							$insertdata=array(

								'exc_taxes_id' => $row_id,

								'category_id'=> $value

							);

							$cat_menu = $this->WebshopModel->insertData('exceptional_taxes_set_details',$insertdata);

						}

					}



					if($cat_menu){

						$redirect = base_url('UserController/exceptional_taxes_settings');

						echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));exit;

					}else{

						echo json_encode(array('flag' => 0, 'msg' => "went somthing wrong!"));exit;

					}

				}

			}





		}else{

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory field"));exit;

		}



	}



	public function autologin()

	{



		$email= $_GET['email'];

		if(!empty($email))

		{

			$email = $this->CommonModel->custom_filter_input($email);

			$UserDetails = $this->UserModel->getUserByEmail($email);

			$LoginToken = $this->generateToken();

			$LoginID = $UserDetails->fbc_user_id;

			$shop_id = $UserDetails->shop_id;

			$ShopDetails = $this->UserModel->getShopDetailsByShopId($shop_id);

			$ShopOwnerId  = $ShopDetails->fbc_user_id;



			$emp_detail=$this->CommonModel->getSingleDataByID('fbc_users_emp_details',array('fbc_user_id'=>$LoginID),'');

			if(isset($emp_detail) &&  $emp_detail->id!=''){

				$role_in_company=$emp_detail->role_in_company;

			}else{

				$role_in_company='';

			}

			$sessionArr = array('LoginID' => $LoginID, 'LoginToken' => $LoginToken, 'ShopID' => $shop_id, 'ShopOwnerId' => $ShopOwnerId,'UserRole'=>$role_in_company);

			$this->session->set_userdata($sessionArr);

			$this->session->set_userdata('LoginID', $UserDetails->fbc_user_id);

			$this->session->set_userdata('LoginToken', $LoginToken);

			$this->UserModel->insertIntoLoginSession($LoginToken, $LoginID, $shop_id);

			//Save Last login

			$data	=  array('last_login_at' => strtotime(date('Y-m-d H:i:s')));

			$this->db->where('fbc_user_id',$LoginID);

			$this->db->update('fbc_users',$data);

			$data['user_details1'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);

		$data['PageTitle']= 'Dashboard';

		if($data['user_details1']->parent_id == 0)

		{

			$data['shop_employees'] = $this->UserModel->getShopEmployeesDetails($data['user_details1']->fbc_user_id);

			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details1']->fbc_user_id);

			$data['country_list'] = $this->CommonModel->get_countries();

			// $this->load->view('dashboard', $data);

			redirect(base_url() . "dashboard");

		}

			//$redirect = base_url() . "dashboard";



		}



	}

	public function store() {
        // Allow only POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            show_error('Method not allowed', 405);
            return;
        }

        // Get input (JSON or POST)
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $this->input->post();
        }

        if (empty($input)) {
            echo json_encode(['status' => 'error', 'message' => 'No input received']);
            return;
        }

        // Collect input
        $publication_name = $input['publication_name'] ?? '';
        $vendor_name      = $input['vendor_name'] ?? '';
        $email            = $input['email'] ?? '';
        $password_hash    = md5('Merchant@yellowmarket1');
        $phone_no         = $input['phone_no'] ?? '';
        $merchant_cat_id  = $input['merchant_cat_id'] ?? 0;
        
        $created_at       = strtotime(date('Y-m-d H:i:s'));

		$recaptcha_response = $input['g-recaptcha-response'] ?? '';
        if (empty($recaptcha_response)) {
			if ($input['lang'] == "french") {
				echo json_encode([
					'status' => 'Erreur',
					'message' => 'Veuillez vérifier le formulaire captcha.'
				]);
			}else{
				echo json_encode([
					'status' => 'error',
					'message' => 'Please check the the captcha form.'
				]);
			}
            return;
        }

        // 2️⃣ Check if email already exists
        $existing = $this->db->get_where('publisher', ['email' => $email, 'remove_flag' => 0])->row();
        if ($existing) {
			if ($input['lang'] == "french") {
				echo json_encode([
					'status' => 'Erreur',
					'message' => 'Cet e-mail existe déjà.'
				]);
			}else{
				echo json_encode([
					'status' => 'error',
					'message' => 'Email already exists'
				]);
			}
            return;
        }

		// Insert into DB
        $data = [
            'publication_name' => $publication_name,
            'vendor_name'      => $vendor_name,
            'email'            => $email,
            'password'         => $password_hash,
            'phone_no'         => $phone_no,
            'merchant_cat'     => $merchant_cat_id,
            'lang_flag'     => $input['lang'] ?? Null,
            'created_at'       => $created_at
        ];

        
        $this->db->insert('publisher', $data);

		$merchant_id = $this->db->insert_id(); // Get the newly created merchant ID

		// Insert default Daily Deals JUPITER entry
		if ($merchant_id) {
			$this->db->insert('merchant_addon_purchases', [
				'merchant_id' => $merchant_id,
				'service_id'  => 4, // Daily Deals JUPITER
				'qty'         => 1,
				'status'      => 'pending',
				'created_at'  => date('Y-m-d H:i:s'),
				'ip'          => $_SERVER['REMOTE_ADDR']
			]);

			$selected_plan_id = !empty($input['plan_id']) ? (int)$input['plan_id'] : 0;
			if ($selected_plan_id > 0) {
				$valid_plan = $this->Subscription_model->get_plan_by_id($selected_plan_id);
				if (!$valid_plan) {
					$selected_plan_id = 0;
				}
			}
			if (!$selected_plan_id) {
				$starter_plan = $this->Subscription_model->get_default_starter_plan();
				$selected_plan_id = $starter_plan ? (int)$starter_plan['id'] : 2;
			}

			$this->Subscription_model->subscribe_plan($merchant_id, $selected_plan_id, $_SERVER['REMOTE_ADDR']);
		}

        // Prepare email placeholders
        // 1️⃣ Send email to admin
		$webshop_name= "Yellow Market";
		$site_logo = '<img src="https://mu.yellowmarkets.com/uploads/yellow-markets-logo.png" alt="Yellow Markets Commerce" width="248" height="auto">';
		if($input['lang']=="french"){
			$TempVars = array("##NAME##" ,"##EMAILID##");
			$DynamicVars = array($publication_name, $email);
			$adminEmail = 'anu@bcod.co.in';
			$templateId = 'admin-notification-new-merchant-register-fr'; // Admin email template ID
			$adminMailSent = $this->CommonModel->sendCommonHTMLEmail($adminEmail, $templateId, $TempVars, $DynamicVars);
	
			$TempVars1 = array("##NAME##" ,"##EMAILID##");
			$DynamicVars2 = array($publication_name, $email);
			// 2️⃣ Send email to merchant
			$merchantTemplateId = 'merchant-registration-fr'; // Merchant email template ID
			$merchantMailSent = $this->CommonModel->sendCommonHTMLEmail($email, $merchantTemplateId, $TempVars1, $DynamicVars2);

		}else{
			$TempVars = array("##NAME##" ,"##EMAILID##");
			$DynamicVars = array($publication_name, $email);
			$adminEmail = 'anu@bcod.co.in';
			$templateId = 'admin-notification-new-merchant-register'; // Admin email template ID
			$adminMailSent = $this->CommonModel->sendCommonHTMLEmail($adminEmail, $templateId, $TempVars, $DynamicVars);
	
			$TempVars1 = array("##NAME##" ,"##EMAILID##");
			$DynamicVars2 = array($publication_name, $email);
			// 2️⃣ Send email to merchant
			$merchantTemplateId = 'merchant-registration'; // Merchant email template ID
			$merchantMailSent = $this->CommonModel->sendCommonHTMLEmail($email, $merchantTemplateId, $TempVars1, $DynamicVars2);
			
			
		}
		// ================================
		// INSERT NOTIFICATION
		// ================================
		$notification = [
			'type'           => 'account',
			'subtype'        => 'new_merchant',
			'recipient_type' => 'admin',
			'recipient_id'   => 1, // admin id
			'title'          => 'New merchant approval request',
			'message'        => 'New merchant approval request from '.$publication_name.'.',
			'data'           => json_encode(['merchant_id' => $merchant_id]),
			'is_read'        => 0,
			'created_at'     => date('Y-m-d H:i:s'),
			'updated_at'     => date('Y-m-d H:i:s')
		];

		$this->db->insert('notifications', $notification);

        // Return response
        if ($adminMailSent && $merchantMailSent) {
			if ($input['lang'] == "french") {
				echo json_encode([
					'status' => 'Succès',
					'message' => 'Merci pour votre inscription. Votre compte est en cours d’approbation. Veuillez vérifier votre e-mail.',
				]);
			} else {
				echo json_encode([
					'status' => 'success',
					'message' => 'Thank you for Registering. Your account is under approval. Please check your email.',
				]);
			}
        } else {
			if ($input['lang'] == "french") {
				echo json_encode([
					'status' => 'Erreur',
					'message' => 'Éditeur ajouté, mais l’envoi de l’e-mail a échoué',
					'adminMailSent' => $adminMailSent,
					'merchantMailSent' => $merchantMailSent
				]);
			}else{
				echo json_encode([
					'status' => 'error',
					'message' => 'Publisher added but email sending failed',
					'adminMailSent' => $adminMailSent,
					'merchantMailSent' => $merchantMailSent
				]);
			}
        }
    }


	public function help_desk() {
		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';

		$data['help_desk'] = $this->CommonModel->get_help_desk($LogindID);
		// echo "<pre>";print_r($data);die;
		$this->load->view('help_desk_list', $data);
	}

	public function help_desk_edit($id) {
		$data['ticket'] = $this->CommonModel->get_help_desk_details($id);
		$customer_id = $data['ticket']['customer_id'];
		$data['orders'] = $this->CommonModel->get_customer_orders($customer_id);

		// 3. Get all products for the selected order
		$order_id = $data['ticket']['order_id'];
		$data['products'] = $this->CommonModel->get_order_products($order_id); // returns array of products

		// 4. Status labels for orders (if needed)
		$data['status_labels'] = [
			0 => 'Complete',
			1 => 'Pending',
			2 => 'Cancelled',
		];
		// echo "<pre>";
		// print_r($data);die;
		$this->load->view('edit_help_desk', $data);
	}

	public function update_help_desk()
    {
		$id = $this->input->post('id');
		$merchant_id = $this->input->post('merchant_id');
		$ticket_id = $this->input->post('ticket_id');
		$category = $this->input->post('category');
		$admin_reply = trim($this->input->post('admin_reply'));

		if (empty($admin_reply)) {
			$this->session->set_flashdata('error', "Reply cannot be empty.");
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		$this->db->from('help_desk');
		$this->db->where('merchant_id', $merchant_id);
		$this->db->where('id', $id); 
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit(1);
		$last_ticket = $this->db->get()->row();

		if (!$last_ticket) {
			$this->session->set_flashdata('error', "Ticket not found.");
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// CASE 1: Admin reply is empty → update current row
		if (empty($last_ticket->admin_reply) && !empty($last_ticket->message) && $category == 1) {
			$updateData = [
				'admin_reply' => $admin_reply,
				'updated_at'  => strtotime(date('Y-m-d H:i:s')),
				'status'      => 1
			];
			$this->db->where('ticket_id', $ticket_id);
			$this->db->update('help_desk', $updateData);

		} else {
			$is_merchant_ticket = (!empty($last_ticket->category) && $last_ticket->category > 2);
			$insertData = [
				'ticket_id'    => $ticket_id,
				'subject'      => $last_ticket->subject,
				'category'     => $last_ticket->category,
				'priority'     => $last_ticket->priority,
				'customer_id'  => $last_ticket->customer_id,
				'merchant_id'  => !empty($merchant_id) ? $merchant_id : $last_ticket->merchant_id,
				'message'      => $is_merchant_ticket ? $admin_reply : '', 
				'attachment'   => '',
				'order_id'     => $last_ticket->order_id,
				'products'     => $last_ticket->products,
				'admin_reply'  => $is_merchant_ticket ? '' : $admin_reply,
				'status'       => 1,
				'created_at'   => strtotime(date('Y-m-d H:i:s')),
				'updated_at'   => strtotime(date('Y-m-d H:i:s')),
				'ip'           => $_SERVER['REMOTE_ADDR'],
			];

			$this->db->insert('help_desk', $insertData);

			$updateData = [
				'updated_at'  => strtotime(date('Y-m-d H:i:s')),
				'status'      => 1
			];
			$this->db->where('ticket_id', $ticket_id);
			$this->db->update('help_desk', $updateData);
		}

		$this->session->set_flashdata('success', $this->lang->line('reply_added_successfully') ?: "Reply added successfully.");
		
		$order_id   = $last_ticket->order_id ?? null;
		$product_id = $last_ticket->products ?? null;
		$ticket_id = $last_ticket->ticket_id ?? null;
		$url = 'UserController/view/' . $order_id .'/'. $ticket_id;
		if (!empty($product_id)) { $url .= '/' . $product_id; }

		redirect($url, 'refresh');
    }
 
   
    
    // SHOPPER HELP DESK
	public function shopper_helpdesk() {
		$LogindID = $this->session->userdata('LoginID');

		if(empty($LogindID)){
			$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
			
		}

		
		if (empty($LogindID)) {
			redirect('login');
			return;
		}

		//echo $LogindID;die;
		
		$data['page_title'] = 'Shopper Tickets';
		
		$data['help_desk']  = $help_desk = $this->CommonModel->get_shopper_help_desk($LogindID);
		
		//echo "<pre>";print_r($help_desk);die;

		$data['side_menu']  = 'help_desk'; 
		
		$this->load->view('help_desk_list', $data); 
	}

	public function merchant_helpdesk() {
		$LogindID = $this->session->userdata('LoginID');

		if(empty($LogindID)){
			$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
			
		}

		
		if (empty($LogindID)) {
			redirect('login');
			return;
		}
		
		$data['page_title'] = 'Merchant Tickets';
		
		$data['help_desk']  = $this->CommonModel->get_merchant_help_desk($LogindID);
		$data['side_menu']  = 'help_desk';
		
		$this->load->view('merchant_helpdesk', $data);
	}

	public function raise_ticket() {
		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		if (empty($LogindID)) {
			redirect('login');
			return;
		}
		
	
		
		$data['page_title'] = $this->lang->line('raise_ticket'); 
		$data['side_menu']  = 'help_desk';
		
		$data['orders'] = $this->CommonModel->get_merchant_orders($LogindID); 

		$this->load->view('raise_ticket', $data);
	}

	public function get_order_products()
    {
        $order_id = $this->input->post('order_id');


        $products = $this->CommonModel->get_order_products($order_id);
        // echo "<pre>";print_r($products);die;

        echo json_encode($products);
    }

	public function submit_ticket() {
		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		if (empty($LogindID)) {
			redirect('login'); 
			return;
		}

		
		$category = $this->input->post('category', true) ?: 3;
		$order_id = $this->input->post('order_id', true) ?: 0;
		$products = $this->input->post('products', true) ?: ($this->input->post('product_id', true) ?: 0);

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

		
		$attachment_filename = '';
		if (!empty($_FILES['attachment']['name'])) {
			$config['upload_path']   = './uploads/tickets/';
			$config['allowed_types'] = 'jpg|jpeg|png|pdf';
			$config['max_size']      = 1024; // 1MB restriction maximum boundary limits
			$config['encrypt_name']  = TRUE;

			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, true);
			}

			$this->load->library('upload', $config);
			if ($this->upload->do_upload('attachment')) {
				$attachment_filename = $this->upload->data('file_name');
			}
		}

		
		$insertData = [
			'ticket_id'   => $ticket_id,
			'subject'     => $this->input->post('subject', true),
			'category'    => 3,
			'priority'    => $this->input->post('priority', true),
			'message'     => $this->input->post('message', true),
			'order_id'    => $order_id,
			'products'    => $products,
			'attachment'  => $attachment_filename,
			'merchant_id' => $LogindID,
			'customer_id' => $LogindID, 
			'created_at'  => strtotime(date('Y-m-d H:i:s')), 
			'ip'          => $this->input->ip_address(),
		];

		//echo "<pre>";print_r($insertData);die;

		
		$this->db->insert('help_desk', $insertData);

		// ADD NOTIFICATION TO ADMIN
		$this->db->insert('notifications', [
			'type'           => 'helpdesk',
			'subtype'        => 'new_ticket',
			'recipient_type' => 'admin',
			'recipient_id'   => 1,
			'title'          => 'New Help Desk Ticket',
			'message'        => 'A new help desk ticket #' . $ticket_id . ' has been submitted by Merchant.',
			'data'           => json_encode([
									'ticket_id'   => $ticket_id,
									'order_id'    => $order_id,
									'product_id'  => $products,
									'merchant_id' => $LogindID
								]),
			'is_read'        => 0,
			'created_at'     => date('Y-m-d H:i:s'),
			'updated_at'     => date('Y-m-d H:i:s')
		]);

		$this->session->set_flashdata('success', $this->lang->line('ticket_created_successfully'));
		
		
		redirect('help_desk/merchant', 'refresh');
			
	}

	public function view($order_id, $ticket_id, $product_id = null)
	{
		$this->db->where('order_id', $order_id);

		if (!empty($product_id)) {
			$this->db->where('products', $product_id);
		}

		if (!empty($ticket_id)) {
			$this->db->where('ticket_id', $ticket_id);
		}
		
		$this->db->order_by('created_at', 'ASC');
		$help_desk_data = $this->db->get('help_desk')->result(); 

		if (empty($help_desk_data)) {
			$this->session->set_flashdata('error', "No conversation found for this order.");
			redirect('merchant/help_desk');
			return;
		}

		$data['help_desk_data'] = $help_desk_data;
		$data['order'] = $this->CommonModel->get_order_by_id($order_id);
		$data['product'] = $this->CommonModel->get_product_by_order($order_id, $product_id);

		$this->load->view('help_desk_conversation', $data);
	}

	public function close_ticket($order_id = 0, $product_id = 0, $ticket_id = '') {
		$LogindID = $this->session->userdata('LoginID');
		if (empty($LogindID)) {
			$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		}
		if (empty($LogindID)) {
			redirect('login');
			return;
		}

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

		// Strictly isolate merchant tickets (category = 1) for this merchant only
		$this->db->where('category', 1);
		$this->db->where('merchant_id', $LogindID);
		$this->db->update('help_desk', ['status' => 2]);

		$this->session->set_flashdata('success', $this->lang->line('ticket_closed_successfully') ?: 'Ticket closed successfully.');

		redirect('help_desk/shopper', 'refresh'); 
	}
}

