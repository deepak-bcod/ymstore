<?php
defined('BASEPATH') or exit('No direct script access allowed');
class TestController extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}

		$this->load->model('CategoryModel');
		$this->load->model('EavAttributesModel');
		$this->load->model('SellerProductModel');
		// $this->load->model('B2BImportModel');
		// $this->load->model('InboundModel');
		//$this->load->model('Multi_Languages_Model');
		$this->load->library('image_lib');
		$this->config->load('facebook');
		// $this->load->library('pagination');
		//$this->load->library('Image_upload');
	}

	public function test_fb()
	{
		$fb_user_access_token = $this->config->item('fb_user_access_token');

		//echo "<pre> config => ";print_r($fb_user_access_token);

		$getPageIdUrl = "https://graph.facebook.com/v25.0/user_id/accounts?access_token=" . urlencode($fb_user_access_token);
		$ch = curl_init($getPageIdUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		echo "<pre>";print_r($response);
		curl_close($ch);
		$result = json_decode($response, true);

		echo "<pre>";print_r($result);die;
			
		// Post to Facebook Page
		$fb_page_access_token = $this->config->item('fb_page_access_token');
		$page_id = "YOUR_PAGE_ID";

		$message = "New Product Approved: ".$product_name;
		$image_url = base_url("uploads/products/".$default_image);

		$fb_url = "https://graph.facebook.com/$page_id/photos";
		
		$postFields = [
			'caption' => $message,
			'url' => $image_url,
			'access_token' => $fb_page_access_token,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fb_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($response, true);

		if(isset($result['id'])){
			// save fb post id & stop future reposts
			$this->SellerProductModel->updateData('products', ['id'=>$product_id], ['fb_post_id'=>$result['id']]);
		}
	}
	
}
