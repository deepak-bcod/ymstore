<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Subscription extends CI_Controller
{



    public function __construct()
    {

        parent::__construct();

        $this->load->model('Subscription_model');

        $this->load->library(['session', 'form_validation']);

        $this->load->helper(['url', 'form']);
    }



    // -----------------------------

    // List all plans and features

    // -----------------------------

    public function index()
    {

        $data['plans'] = $this->Subscription_model->get_plans();

        $data['features'] = $this->Subscription_model->get_features();



        // Load the main listing view

        $this->load->view('subscription_list', $data);
    }



    // -----------------------------

    // Add/Edit Plan

    // -----------------------------

    public function edit_plan($id = null)
    {
        if ($this->input->post()) {
            $price = (float)$this->input->post('price');
            $yearly_price = (float)$this->input->post('yearly_price');
            $vat = (float)$this->input->post('vat_percent');

            // ✅ Calculate VAT on yearly_price
            $yearly_price_with_vat = $yearly_price + ($yearly_price * $vat / 100);

            $plan_data = [
                'name'          => $this->input->post('name'),
                'name_fr' => $this->input->post('name_fr') ?? '',
                'price'         => $price,
                'yearly_price'  => $yearly_price, // Store price after VAT
                'vat_percent'   => $vat,
                'final_price'   => $yearly_price_with_vat,
                'status'        => $this->input->post('status')
            ];

            if ($id) {
                $this->Subscription_model->update_plan($id, $plan_data);
                $this->session->set_flashdata('success', 'Plan updated successfully.');
            } else {
                $this->Subscription_model->add_plan($plan_data);
                $this->session->set_flashdata('success', 'Plan added successfully.');
            }

            redirect('subscription');
        }

        $data = [];
        if ($id) {
            $data['plan'] = $this->Subscription_model->get_plan($id);
            if (!$data['plan']) {
                show_404();
            }
        }

        $this->load->view('plan_form', $data);
    }





    // -----------------------------

    // Delete Plan

    // -----------------------------

    public function delete_plan($id)
    {

        $this->Subscription_model->delete_plan($id);

        $this->session->set_flashdata('success', 'Plan deleted successfully.');

        redirect('subscription');
    }



    // -----------------------------

    // Add/Edit Feature

    // -----------------------------

    public function edit_feature($id = null)
    {



        if ($this->input->post()) {

            $feature_data = [

                'feature_name' => $this->input->post('feature_name'),
                'feature_name_fr' => $this->input->post('feature_name_fr')

            ];



            if ($id) {

                $this->Subscription_model->update_feature($id, $feature_data);

                $this->session->set_flashdata('success', 'Feature updated successfully.');
            } else {

                $this->Subscription_model->add_feature($feature_data);

                $this->session->set_flashdata('success', 'Feature added successfully.');
            }



            redirect('subscription');
        }



        $data = [];

        if ($id) {

            $data['feature'] = $this->Subscription_model->get_feature($id);

            if (!$data['feature']) {

                show_404(); // Feature not found

            }
        }



        $this->load->view('feature_form', $data); // Make sure view file exists

    }



    // -----------------------------

    // Delete Feature

    // -----------------------------

    public function delete_feature($id)
    {

        $this->Subscription_model->delete_feature($id);

        $this->session->set_flashdata('success', 'Feature deleted successfully.');

        redirect('subscription');
    }



    public function map_features()
    {

        $data['plans'] = $this->Subscription_model->get_plans();

        $data['features'] = $this->Subscription_model->get_features();



        // fetch existing mapping

        $plan_features = $this->Subscription_model->get_plan_features();

        $mapped = [];

        foreach ($plan_features as $pf) {

            $mapped[$pf['feature_id']][$pf['plan_id']] = $pf['value'];
        }

        $data['mapped'] = $mapped;



        $this->load->view('map_features', $data);
    }



    public function save_feature_mapping()
    {

        $feature = $this->input->post('feature');

        //echo "<pre>";print_r($feature);die;

        if (!empty($feature)) {

            foreach ($feature as $feature_id => $sort_order) {

                $this->db->update('subscription_features', ['sort_order' => $sort_order], ['id' => $feature_id]);
                
            }
        }

        //echo "<pre>";print_r($feature);die;

        $mapping = $this->input->post('mapping'); // matches your input names



        if (!empty($mapping)) {

            foreach ($mapping as $feature_id => $plan_values) {

                foreach ($plan_values as $plan_id => $value) {

                    $value = trim($value);

                    if ($value === '') continue;



                    // check if record exists

                    $existing = $this->db->get_where('plan_features', [

                        'feature_id' => $feature_id,

                        'plan_id' => $plan_id

                    ])->row_array();



                    if ($existing) {

                        $this->db->update('plan_features', ['value' => $value], ['id' => $existing['id']]);
                    } else {

                        $this->db->insert('plan_features', [

                            'feature_id' => $feature_id,

                            'plan_id' => $plan_id,

                            'value' => $value

                        ]);
                    }
                }
            }

            $this->session->set_flashdata('success', 'Feature mapping saved successfully.');
        } else {

            $this->session->set_flashdata('error', 'No values to save.');
        }



        redirect('subscription/map_features');
    }

    public function manage_subscription()
    {

        $data['subscription_orders'] = $this->Subscription_model->get_subscription_orders();

        // Load the main listing view

        $this->load->view('manage_subscription_list', $data);
    }

    public function download_invoice($order_id)
    {
        $order = $this->Subscription_model->get_order_by_id($order_id);
        if (!$order) {
            show_404();
        }

        // Check if pre-generated PDF exists
        $saved_pdf_paths = [
            rtrim(FCPATH, '/\\') . '/../merchant/uploads/subscription_invoices/subscription_invoice_' . $order['id'] . '.pdf',
            rtrim(FCPATH, '/\\') . '/../uploads/subscription_invoices/subscription_invoice_' . $order['id'] . '.pdf',
            rtrim(FCPATH, '/\\') . '/uploads/subscription_invoices/subscription_invoice_' . $order['id'] . '.pdf'
        ];
        foreach ($saved_pdf_paths as $saved_pdf) {
            if (file_exists($saved_pdf) && filesize($saved_pdf) > 0) {
                $filename = 'subscription_invoice_' . $order['id'] . '.pdf';
                if (ob_get_length()) {
                    ob_end_clean();
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($saved_pdf));
                readfile($saved_pdf);
                exit;
            }
        }

        // Get additional data
        $order['plan'] = $this->Subscription_model->get_plan_by_id($order['plan_id']);
        $order['publisher'] = $this->db->get_where('publisher', ['id' => $order['publisher_id']])->row_array();

        $data['order'] = $order;

        // Resolve logo to base64 to avoid external network issues
        $logo_data_uri = '';
        $logo_candidates = [
            rtrim(FCPATH, '/\\') . '/../uploads/yellow-markets-logo.png',
            rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png',
            dirname(FCPATH) . '/uploads/yellow-markets-logo.png'
        ];
        foreach ($logo_candidates as $lpath) {
            if (file_exists($lpath) && filesize($lpath) > 0) {
                $ext = pathinfo($lpath, PATHINFO_EXTENSION);
                $logo_data_uri = 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode(file_get_contents($lpath));
                break;
            }
        }
        $data['logo_src'] = !empty($logo_data_uri) ? $logo_data_uri : (defined('SITE_LOGO') ? SITE_LOGO : '');

        // Load Dompdf autoloader from root or merchant vendor
        if (!class_exists('Dompdf\\Dompdf') && !class_exists('\\Dompdf\\Dompdf')) {
            $autoload_candidates = [
                rtrim(FCPATH, '/\\') . '/../vendor/autoload.php',
                APPPATH . '../../vendor/autoload.php',
                rtrim(FCPATH, '/\\') . '/../merchant/vendor/autoload.php',
                APPPATH . '../vendor/autoload.php'
            ];
            foreach ($autoload_candidates as $cand) {
                if (file_exists($cand)) {
                    require_once $cand;
                    if (class_exists('Dompdf\\Dompdf') || class_exists('\\Dompdf\\Dompdf')) {
                        break;
                    }
                }
            }
        }

        if (class_exists('Dompdf\\Dompdf') || class_exists('\\Dompdf\\Dompdf')) {
            try {
                // Get HTML
                $htmldata = $this->load->view('invoice/subscription_invoice', $data, true);

                // Generate PDF using Dompdf directly
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', TRUE);
                $options->set('isHtml5ParserEnabled', TRUE);
                $options->set('isFontSubsettingEnabled', TRUE);
                $options->set('defaultFont', 'DejaVu Sans');

                $dompdf = new \Dompdf\Dompdf($options);
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => FALSE,
                        'verify_peer_name' => FALSE,
                        'allow_self_signed' => TRUE
                    ]
                ]);
                $dompdf->setHttpContext($context);
                $dompdf->loadHtml($htmldata);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();

                // Save copy to merchant uploads if folder exists or can be created
                $save_dirs = [
                    rtrim(FCPATH, '/\\') . '/../merchant/uploads/subscription_invoices/',
                    rtrim(FCPATH, '/\\') . '/uploads/subscription_invoices/'
                ];
                foreach ($save_dirs as $save_dir) {
                    if (is_dir($save_dir) || @mkdir($save_dir, 0777, true)) {
                        @file_put_contents($save_dir . 'subscription_invoice_' . $order['id'] . '.pdf', $output);
                    }
                }

                // Set headers for download
                $filename = 'subscription_invoice_' . $order['id'] . '.pdf';
                if (ob_get_length()) {
                    ob_end_clean();
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($output));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $output;
                exit;
            } catch (\Throwable $e) {
                log_message('error', "Admin Direct Dompdf generation error: " . $e->getMessage());
            }
        }

        // Fallback: Display print-friendly HTML view directly
        $data['is_html_fallback'] = true;
        if (ob_get_length()) {
            ob_end_clean();
        }
        $this->load->view('invoice/subscription_invoice', $data);
    }
}
