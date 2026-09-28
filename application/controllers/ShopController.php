<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class ShopController extends CI_Controller {



    public function __construct() {

        parent::__construct();

        $this->load->model('Shop_model');

        $this->load->library('pagination');
        $this->load->helper(['url', 'form', 'language']);

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }



    public function index() {

        // Load the view

        $data['state'] = $this->Shop_model->get_states();

        $data['city'] = $this->Shop_model->get_city();

        $this->load->view('shops_view',$data);

    }



    // AJAX fetch shops

    // public function fetch_shops($page = 0) {

    //     $limit = 5; // shops per page

    //     $offset = ($page > 0) ? ($page - 1) * $limit : 0;



    //     $state   = $this->input->get('state');

    //     $city    = $this->input->get('city');

    //     $zipcode = $this->input->get('zipcode');



    //     // Fetch shops with filters

    //     $data['shops'] = $this->Shop_model->get_shops($limit, $offset, $state, $city, $zipcode);

    //     $total_rows    = $this->Shop_model->get_count($state, $city, $zipcode);

    //     // echo "<pre>";print_r($total_rows);die;



    //     // Pagination config

    //     $config['base_url'] = base_url('shops/fetch');

    //     $config['total_rows'] = $total_rows;

    //     $config['per_page'] = $limit;

    //     $config['use_page_numbers'] = TRUE;

    //     $config['num_links'] = 3;

    //     $config['full_tag_open'] = '<ul class="pagination">';

    //     $config['full_tag_close'] = '</ul>';

    //     $config['cur_tag_open'] = '<li class="active"><a href="#">';

    //     $config['cur_tag_close'] = '</a></li>';

    //     $config['num_tag_open'] = '<li>';

    //     $config['num_tag_close'] = '</li>';

    //     $config['prev_tag_open'] = '<li>';

    //     $config['prev_tag_close'] = '</li>';

    //     $config['next_tag_open'] = '<li>';

    //     $config['next_tag_close'] = '</li>';



    //     $this->pagination->initialize($config);



    //     $data['pagination'] = $this->pagination->create_links();



    //     // Return as AJAX

    //     $this->load->view('shops_ajax', $data);

    // }


   public function fetch_shops()
    {
        $this->load->library('pagination');

        $page = $this->input->get('page');
        $page = (!empty($page) && $page > 0) ? $page : 1;

        $limit  = 5;
        $offset = ($page - 1) * $limit;

        $state   = $this->input->get('state');
        $city    = $this->input->get('city');
        $zipcode = $this->input->get('zipcode');

        // Fetch shops
        $data['shops'] = $this->Shop_model->get_shops($limit, $offset, $state, $city, $zipcode);
        $total_rows = $this->Shop_model->get_count($state, $city, $zipcode);

        // Pagination Config
        $config['base_url'] = site_url('shops/fetch');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;

        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;

        // Label text
        $config['first_link'] = 'First';
        $config['last_link']  = 'Last';
        $config['next_link']  = '>';
        $config['prev_link']  = '<';

        // Bootstrap Pagination Styling
        $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul>';
        $config['attributes']      = ['class' => 'page-link'];

        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';

        $config['cur_tag_open']    = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']   = '</span></li>';

        $config['num_tag_open']    = '<li class="page-item">';
        $config['num_tag_close']   = '</li>';


        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $this->load->view('shops_ajax', $data);
    }





    public function shop_details($id){

        $data['shop_details'] = $this->Shop_model->shop_details($id);
        $data['rating'] = $this->Shop_model->get_avg_ratings_by_merchant($id);

        // echo "<pre>";print_r($data);die;

        // Return as AJAX

        $this->load->view('shops_details', $data);

    }



        public function fetch_shops_products($shop_id, $page = 0) {

            $limit = 8;

            $offset = ($page > 0) ? ($page - 1) * $limit : 0;



            // Get products of this shop only

            $data['shops'] = $this->Shop_model->get_shops_products($shop_id, $limit, $offset);
            //echo "<pre>"; print_r($data['shops']); die;



            $total_rows = $this->Shop_model->get_count_products($shop_id);



            // Pagination config

            $config['base_url'] = base_url('shops/fetch/' . $shop_id);

            $config['total_rows'] = $total_rows;

            $config['per_page'] = $limit;

            $config['suffix'] = '?' . http_build_query($_GET, '', "&");
            
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($_GET, '', "&");

            $config['use_page_numbers'] = TRUE;

            $config['num_links'] = 3;

            $config['full_tag_open'] = '<ul class="pagination">';

            $config['full_tag_close'] = '</ul>';

            $config['cur_tag_open'] = '<li class="active"><a href="#">';

            $config['cur_tag_close'] = '</a></li>';

            $config['num_tag_open'] = '<li>';

            $config['num_tag_close'] = '</li>';

            $config['prev_tag_open'] = '<li>';

            $config['prev_tag_close'] = '</li>';

            $config['next_tag_open'] = '<li>';

            $config['next_tag_close'] = '</li>';



            $this->pagination->initialize($config);



            $data['pagination'] = $this->pagination->create_links();
            // echo "<pre>"; print_r($data);die;




            // AJAX view with shop’s products

            $this->load->view('shops_products_ajax', $data);

        }



}

