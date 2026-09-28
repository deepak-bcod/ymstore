<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Addons extends CI_Controller
{



    public function __construct()
    {

        parent::__construct();

        $this->load->model('Addon_model');

        $this->load->model('AddonCategory_model'); // Load category model

        $this->load->helper(array('form', 'url'));

        $this->load->library('session');
    }



    // List all addon services

    public function index()
    {

        $data['addons'] = $this->Addon_model->get_all();

        $this->load->view('addons/list', $data);
    }



    // Create new addon

    public function create()
{
    $data['categories'] = $this->AddonCategory_model->get_all();

    if ($this->input->post()) {
        $price = (float)$this->input->post('price');
        $vat_percent = (float)$this->input->post('vat_percent');

        // 1. Calculate VAT and final price
        $tax_amount = 0;
        $final_price = $price;
        if ($price > 0 && $vat_percent > 0) {
            $tax_amount = ($vat_percent / 100) * $price;
            $final_price = $price + $tax_amount;
        }

        // 2. Handle Image Upload
        $image_name = ''; // Default empty
        if (!empty($_FILES['addon_image']['name'])) {
            $config['upload_path']   = './public/uploads/addons/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size']      = 100; // 100KB as per your view text
            $config['encrypt_name']  = TRUE; // Recommended to avoid duplicate names

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('addon_image')) {
                $uploadData = $this->upload->data();
                $image_name = $uploadData['file_name'];
            } else {
                // Optional: Show error if upload fails
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
            }
        }

        // 3. Prepare Insert Array (Including 'image')
        $insert = [
            'category_id'    => $this->input->post('category_id'),
            'title'          => $this->input->post('title'),
            'title_fr'       => $this->input->post('title_fr'),
            'description'    => $this->input->post('description'),
            'description_fr' => $this->input->post('description_fr'),
            'price'          => $price,
            'vat_percent'    => $vat_percent,
            'final_price'    => $final_price,
            'image'          => $image_name, // SAVE FILENAME TO DB
            'status'         => $this->input->post('status'),
            'ip'             => $this->input->ip_address(),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        $this->Addon_model->insert($insert);
        $this->session->set_flashdata('success', 'Addon created successfully');
        redirect('addons');
    }

    $this->load->view('addons/create', $data);
}



    // Edit existing addon

    public function edit($id)
    {
        // print_r($_POST); exit;


        $data['addon'] = $this->Addon_model->get($id);
        $data['categories'] = $this->AddonCategory_model->get_all();

        if (!$data['addon']) {
            show_404();
        }

        if ($this->input->post()) {
            $price = (float)$this->input->post('price');
            $vat_percent = (float)$this->input->post('vat_percent');

            // Calculate VAT & final price
            $tax_amount = 0;
            $final_price = $price;

            if ($price > 0 && $vat_percent > 0) {
                $tax_amount = ($vat_percent / 100) * $price;
                $final_price = $price + $tax_amount;
            }

            $update = [
                'category_id' => $this->input->post('category_id'),
                'title'       => $this->input->post('title'),
                'title_fr'        => $this->input->post('title_fr'),   
                'description' => $this->input->post('description'),
                'description_fr'  => $this->input->post('description_fr'), 
                'price'       => $price,
                'vat_percent' => $vat_percent,
                'final_price' => $final_price,
                'status'      => $this->input->post('status'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];
            if (!empty($_FILES['addon_image']['name'])) {
    // FIX: Change path to include public/
    $config['upload_path']   = './public/uploads/addons/'; 
    $config['allowed_types'] = 'gif|jpg|png|jpeg';
    $config['file_name']     = time() . '_' . $_FILES['addon_image']['name'];


    if (!is_dir($config['upload_path'])) {
        mkdir($config['upload_path'], 0777, TRUE);
    }
    // ADD THESE TWO LINES HERE:
    $config['encrypt_name']  = TRUE; 
    $config['remove_spaces'] = TRUE;

    $this->load->library('upload', $config);

    if ($this->upload->do_upload('addon_image')) {
        $uploadData = $this->upload->data();
        $update['image'] = $uploadData['file_name'];

        // FIX: Ensure old image deletion also looks in public/
        if (!empty($data['addon']->image) && file_exists('./public/uploads/addons/' . $data['addon']->image)) {
            unlink('./public/uploads/addons/' . $data['addon']->image);
        }
    } else {
        $this->session->set_flashdata('error', $this->upload->display_errors());
        redirect('addons/edit/' . $id);
    }
}

            $this->Addon_model->update($id, $update);

            $this->session->set_flashdata('success', 'Addon updated successfully');
            redirect('addons');
        }

        $this->load->view('addons/edit', $data);
    }




    // Delete addon

    public function delete($id)
    {

        $this->Addon_model->delete($id);

        $this->session->set_flashdata('success', 'Addon deleted successfully');

        redirect('addons');
    }
}
