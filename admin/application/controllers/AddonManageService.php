<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AddonManageService extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load model using the exact file name
        $this->load->model('Addon_manage_service_model', 'manage_model');
    }

    public function index() {
        $data['title'] = 'Manage Addon Services';
        $data['side_menu'] = 'Addons'; // Highlights sidebar dropdown
        $data['services'] = $this->manage_model->get_all_services();
        
        // Load views directly from the views root folder based on your screenshot
        $this->load->view('common_head', $data); // Assuming common_head or header exists
        $this->load->view('sidebar', $data);
        $this->load->view('addon_manage_service/manage', $data);
        $this->load->view('footer');
    }
}