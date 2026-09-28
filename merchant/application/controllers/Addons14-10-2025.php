<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Addons extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Addon_model');
        $this->load->library('session');
        $this->lang->load('your_lang_file', 'english');
        if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
    }

    // List page
    public function index()
{
    $addons = $this->Addon_model->get_addons_with_services();

    $data['addon_data'] = [];
    foreach ($addons as $row) {
        // This line converts "Editorial Assistance" to "editorial_assistance"
        $clean_key = strtolower(str_replace(' ', '_', $row['name']));
        
        // Store the clean key so the view can use it for translation
        $row['lang_key'] = $clean_key; 
        
        $data['addon_data'][$row['category_name']][] = $row;
    }

    $this->load->view('addons/index', $data);
}

   public function buy($service_id)
    {
        $publisher_id = $this->session->userdata('LoginID');

        // Get quantity from POST request
        $qty = $this->input->post('qty');  
        $qty = !empty($qty) && $qty > 0 ? $qty : 1; // default 1 if invalid

        // save purchase
        $insert = [
            'merchant_id' => $publisher_id,
            'service_id'  => $service_id,
            'qty'         => $qty,           // save quantity
            'status'      => 'pending',      // later update after payment
            'created_at'  => date('Y-m-d H:i:s'),
            'ip'          => $this->input->ip_address()
        ];
        $this->db->insert('merchant_addon_purchases', $insert);

        $this->session->set_flashdata('success', 'Addon purchased successfully.');
        redirect('addons');
    }
    // Service details page
    public function details($service_id)
    {
        $service = $this->Addon_model->get_service_by_id($service_id);

        if (!$service) {
            show_404();
            return;
        }

        $data['service'] = $service;
        $this->load->view('addons/service_details', $data);
    }
}
