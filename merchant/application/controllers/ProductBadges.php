<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductBadges extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ProductBadges_model');
        $this->load->library('session');
        if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}

        $site_lang = $this->session->userdata('site_lang');

		if ($site_lang) {
			$this->lang->load('content', $site_lang);
		} else {
			$this->lang->load('content', 'english'); // default
		}
    }

    // List page
   public function index()
{
    $publisher_id = $this->session->userdata('LoginID');
    
    // Detect language from session
    $site_lang = $this->session->userdata('site_lang');

    // Pass the full session language string (e.g., 'french' or 'english') 
    // to the model so the model's if($lang == 'french') check works.
    $data['pb_category'] = $this->ProductBadges_model->get_product_badges_categories($site_lang);
    
    $data['productList'] = $this->ProductBadges_model->getProductBlockList($publisher_id);
    $data['documentList'] = $this->ProductBadges_model->getDocumentList($publisher_id);
    $data['merchantDetails'] = $this->ProductBadges_model->getMerchantDetails($publisher_id);

    $this->load->view('productbadges/index', $data);
}
    public function getAppliedProducts($catId)
    {
        $publisher_id  = $this->session->userdata('LoginID');
        $products = $this->ProductBadges_model->getAppliedProductsByCategory($catId,$publisher_id);
        //  echo "<pre>";
        // print_r($products);
        // die;
        // return as JSON for AJAX
        echo json_encode($products);
    }

    public function submitApply()
    {
        $company_name      = $this->input->post('company_name');
        $brn               = $this->input->post('brn');
        $contact_person    = $this->input->post('contact_person');
        $mobile            = $this->input->post('mobile');
        $email             = $this->input->post('email');
        $location          = $this->input->post('location');
        $prod_badge_cat_id = $this->input->post('prod_badge_cat_id');
        $terms             = $this->input->post('terms');
        $merchant_id             = $this->input->post('merchant_id');
        $status            = "pending";

        $documentList_arr = $this->input->post('documentList') ?? [];
        $documentListing  = !empty($documentList_arr) ? ',' . implode(',', $documentList_arr) . ',' : '';

        $ProductList_arr = $this->input->post('productList') ?? [];

        if (!empty($ProductList_arr)) {
            foreach ($ProductList_arr as $productId) {
                $insertdata = [
                    'company_name'      => $company_name,
                    'prod_badge_cat_id' => $prod_badge_cat_id,
                    'merchant_id' => $merchant_id,
                    'brn'               => $brn,
                    'contact_person'    => $contact_person,
                    'mobile'            => $mobile,
                    'email'             => $email,
                    'location'          => $location,
                    'documents'         => $documentListing,
                    'terms'             => $terms,
                    'assigned_products' => $productId,
                    'created_by'        => $_SESSION['LoginID'],
                    'created_at'        => time(),
                    'status'            => $status,
                    'ip'                => $_SERVER['REMOTE_ADDR']
                ];

                $this->db->insert('products_badge_apply', $insertdata);

                $statusColumns = [
                    1 => 'made_in_maurituus_approval_status',
                    2 => 'social_empowerment_approval_status',
                    3 => 'environment_friendly_approval_status',
                    4 => 'health_friendly_approval_status'
                ];

                if (isset($statusColumns[$prod_badge_cat_id])) {
                    $column = $statusColumns[$prod_badge_cat_id];
                    $this->db->where('id', $productId);
                    $this->db->set($column, $status);
                    $this->db->update('products');
                }
            }
            // ================================
            // ADD NOTIFICATION (ADMIN)
            // ================================
            $this->db->insert('notifications', [
                'type'           => 'product_badge',
                'subtype'        => 'product_badge_approval_request',
                'recipient_type' => 'admin',
                'recipient_id'   => 1, // admin id
                'title'          => 'New product badge approval request',
                'message'        => 'New product badge approval request has been submitted by merchant',
                'data'           => json_encode([
                    'merchant_id'       => $merchant_id,
                    'prod_badge_cat_id' => $prod_badge_cat_id,
                    'product_ids'       => $ProductList_arr
                ]),
                'is_read'        => 0,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ]);
        }

        // ✅ Set flashdata
        $this->session->set_flashdata('success', 'Application submitted successfully!');

        redirect('productbadges');
    }



}
