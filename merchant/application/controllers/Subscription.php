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
        $this->load->library('session'); // publisher login stored in session
        if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {
            redirect(BASE_URL);
        }

        $site_lang = $this->session->userdata('site_lang');

        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english'); // default
        }
    }

    // Show plans and active plan
    public function index()
    {
        log_message('info', 'Subscription session data: ' . json_encode($this->session->userdata()));

        $publisher_id = $this->session->userdata('LoginID'); // corrected

        $data['plans'] = $this->Subscription_model->get_plans();
        $data['features'] = $this->Subscription_model->get_features();
        $data['plan_features'] = $this->Subscription_model->get_plan_features();
        $data['active_plan'] = $this->Subscription_model->get_active_plan($publisher_id);
        $data['is_expired']  = $this->Subscription_model->is_subscription_expired($publisher_id);

        // Run scheduled daily batch if at or after 3:20 PM and not run today
        try {
            $this->Subscription_model->maybe_run_scheduled_daily_reminders();
        } catch (Exception $e) {
            log_message('error', 'Subscription expiry check error on subscription page: ' . $e->getMessage());
        }

        // Restructure plan_features for easy lookup
        $plan_features = [];
        foreach ($data['plan_features'] as $pf) {
            $plan_features[$pf['feature_id']][$pf['plan_id']] = $pf['value'];
        }
        $data['plan_features'] = $plan_features;

        $this->load->view('subscription_table', $data);
    }

    // Handle subscription request
    public function subscribe()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $plan_id = $this->input->post('plan_id') ?: $this->input->get('plan_id');

        if (!$publisher_id || !$plan_id) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('subscription');
        }

        // Get plan details
        $plan = $this->Subscription_model->get_plan_by_id($plan_id);
        if (!$plan) {
            $this->session->set_flashdata('error', 'Invalid plan selected.');
            redirect('subscription');
        }

        // Prevent renewing or selecting Starter plan when subscription is expired or active
        $plan_name_clean = strtolower(trim(($plan['name'] ?? '') . ' ' . ($plan['name_fr'] ?? '')));
        $is_starter = ($plan_name_clean === 'starter' || strpos($plan_name_clean, 'starter') !== false);
        $active_plan = $this->Subscription_model->get_active_plan($publisher_id);
        $is_expired = $this->Subscription_model->is_subscription_expired($publisher_id);
        if ($is_starter && ($is_expired || !empty($active_plan['plan_id']))) {
            $is_fr = ($this->session->userdata('site_lang') == 'french');
            $msg = $is_fr 
                ? "Le forfait Starter ne peut pas être renouvelé. Veuillez choisir un autre forfait."
                : "The Starter plan cannot be renewed. Please select a different plan.";
            $this->session->set_flashdata('error', $msg);
            redirect('subscription');
            return;
        }

        // If renewing currently active plan, ensure it is within 30 days prior to expiry
        if ($active_plan && $active_plan['plan_id'] == $plan_id && !empty($active_plan['end_date'])) {
            $days_remaining = (strtotime($active_plan['end_date']) - time()) / 86400;
            if ($days_remaining > 30) {
                $this->session->set_flashdata('error', 'Renewal is only available within 30 days prior to your plan expiry date.');
                redirect('subscription');
                return;
            }
        }

        // Create a temporary order record before redirecting to My.T
        $orderData = [
            'publisher_id' => $publisher_id,
            'plan_id'      => $plan_id,
            'amount'       => 1,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('subscription_orders', $orderData);
        $order_id = $this->db->insert_id();

        // Redirect to payment controller
        redirect('paymentGateway/mytMoney/' . $order_id);
    }

    public function manage_subscription()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $data['subscription_orders'] = $this->Subscription_model->get_subscription_orders($publisher_id);
        $data['is_expired']          = $this->Subscription_model->is_subscription_expired($publisher_id);

        // Load the main listing view

        $this->load->view('manage_subscription_list', $data);
    }

    public function download_invoice($order_id)
    {
        $publisher_id = $this->session->userdata('LoginID');
        $order = $this->Subscription_model->get_order_by_id($order_id, $publisher_id);
        if (!$order) show_404();

        $saved_pdf = rtrim(FCPATH, '/\\') . '/uploads/subscription_invoices/subscription_invoice_' . $order['id'] . '.pdf';
        if (file_exists($saved_pdf) && filesize($saved_pdf) > 0) {
            $filename = 'subscription_invoice_' . $order['id'] . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($saved_pdf));
            readfile($saved_pdf);
            exit;
        }

        // If not cached on disk yet, generate and stream
        $pdf_res = $this->Subscription_model->generate_subscription_invoice_pdf($order['id']);
        if ($pdf_res && file_exists($pdf_res['file_path'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $pdf_res['filename'] . '"');
            header('Content-Length: ' . filesize($pdf_res['file_path']));
            readfile($pdf_res['file_path']);
            exit;
        }

        show_error('Unable to generate invoice.');
    }

    /**
     * Test or manually trigger invoice generation and confirmation email
     */
    public function test_invoice_email($order_id = null)
    {
        $publisher_id = $this->session->userdata('LoginID');
        if (!$publisher_id) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => 'error',
                'message' => 'Not logged in'
            ]));
            return;
        }

        if (!$order_id) {
            // Find latest subscription order for merchant
            $latest = $this->db
                ->where('publisher_id', $publisher_id)
                ->order_by('id', 'DESC')
                ->get('subscription_orders')
                ->row_array();
            if ($latest) {
                $order_id = $latest['id'];
            }
        }

        if (!$order_id) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => 'error',
                'message' => 'No subscription orders found for this merchant'
            ]));
            return;
        }

        $sent = $this->Subscription_model->generate_and_send_subscription_invoice($order_id);
        $order = $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'     => $sent ? 'success' : 'failed',
            'order_id'   => $order_id,
            'email_sent' => $sent,
            'order'      => $order
        ]));
    }

    /**
     * Test or manually trigger expiry reminders for current merchant
     */
    public function check_expiry_reminders()
    {
        $publisher_id = $this->session->userdata('LoginID');
        if (!$publisher_id) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status'  => 'error',
                'message' => 'Not logged in'
            ]));
            return;
        }

        $force = $this->input->get('force'); // '1_month' or '1_week'
        $result = $this->Subscription_model->check_and_send_expiry_reminders($publisher_id, $force);
        $active_sub = $this->Subscription_model->get_active_plan($publisher_id);

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'       => 'success',
            'email_sent'   => $result,
            'force'        => $force ?: 'none',
            'reason'       => $this->Subscription_model->last_expiry_error,
            'publisher_id' => $publisher_id,
            'active_sub'   => $active_sub
        ], JSON_PRETTY_PRINT));
    }
}
