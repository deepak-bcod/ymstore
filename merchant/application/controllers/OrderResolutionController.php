<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OrderResolutionController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $LoginID = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? '');
        if (empty($LoginID)) {
            redirect(base_url('login'));
            return;
        }

        $this->load->model('CommonModel');
        $this->load->model('OrderResolutionModel');

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $this->lang->load('content', $site_lang);
    }

    /**
     * Merchant Order Resolution Requests Listing
     */
    public function index()
    {
        $merchant_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];

        $this->db->select('r.*, p.name as product_name, c.first_name, c.last_name, c.email_id as customer_email');
        $this->db->from('order_resolutions r');
        $this->db->join('products p', 'p.id = r.product_id', 'left');
        $this->db->join('customers c', 'c.id = r.customer_id', 'left');
        $this->db->where('r.merchant_id', $merchant_id);
        $this->db->order_by('r.id', 'DESC');
        $data['resolutions'] = $this->db->get()->result();

        $data['side_menu'] = 'order_resolution';
        $data['PageTitle'] = 'Order Resolution Requests';
        $this->load->view('order_resolution/list', $data);
    }

    /**
     * View Order Resolution Details & Conversation
     */
    public function view($ticket_number = '')
    {
        $merchant_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number = trim($ticket_number);

        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->merchant_id !== (int)$merchant_id) {
            $this->session->set_flashdata('error', 'Ticket not found or unauthorized access.');
            redirect('order_resolution');
            return;
        }

        $data['resolution'] = $res;
        $data['messages']   = $this->OrderResolutionModel->get_messages($res->id);
        $data['audit_logs'] = $this->OrderResolutionModel->get_audit_log($res->id);

        // Fetch Order Details
        $data['order'] = $this->db->where('order_id', $res->order_id)->get('sales_order')->row();

        // Fetch Product Details
        $data['product'] = null;
        if (!empty($res->product_id)) {
            $data['product'] = $this->db->where('id', $res->product_id)->get('products')->row();
        }

        // Fetch Customer Details
        $data['customer'] = null;
        if (!empty($res->customer_id)) {
            $data['customer'] = $this->db->where('id', $res->customer_id)->get('customers')->row();
        }

        $data['side_menu'] = 'order_resolution';
        $data['PageTitle'] = 'Order Resolution #' . $res->ticket_number;
        $this->load->view('order_resolution/view', $data);
    }

    /**
     * Merchant Reply to Shopper
     */
    public function reply()
    {
        $merchant_id   = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number = trim($this->input->post('ticket_number', true));
        $message       = trim($this->input->post('message', true));

        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->merchant_id !== (int)$merchant_id) {
            $this->session->set_flashdata('error', 'Invalid ticket.');
            redirect('order_resolution');
            return;
        }

        if (empty($message)) {
            $this->session->set_flashdata('error', 'Message cannot be empty.');
            redirect('order_resolution/view/' . $ticket_number);
            return;
        }

        // Handle attachment
        $attachment = '';
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
            $upload_path = './uploads/order_resolution/';
            if (!is_dir($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }
            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|pdf|webp';
            $config['max_size']      = 5120;
            $config['encrypt_name']  = true;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('attachment')) {
                $fileData = $this->upload->data();
                $attachment = $fileData['file_name'];
            }
        }

        $pub = $this->db->select('publication_name')->where('id', $merchant_id)->get('publisher')->row();
        $sender_name = $pub ? $pub->publication_name : 'Merchant';

        $this->OrderResolutionModel->add_reply($ticket_number, 'merchant', $merchant_id, $sender_name, $message, $attachment, $this->input->ip_address());

        $this->session->set_flashdata('success', 'Your reply has been sent to the shopper.');
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * Merchant Decision Actions:
     * - refund_approved
     * - refund_denied
     * - replacement_approved (requires delivery_option)
     * - replacement_denied
     * - replacement_completed
     */
    public function action()
    {
        $merchant_id   = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number = trim($this->input->post('ticket_number', true));
        $action        = trim($this->input->post('action', true));

        $extra = [];
        if ($action === 'refund_approved') {
            $extra['refund_amount'] = (float)$this->input->post('refund_amount', true);
        } elseif ($action === 'replacement_approved') {
            $extra['delivery_option'] = trim($this->input->post('delivery_option', true));
        }

        $result = $this->OrderResolutionModel->process_merchant_action($ticket_number, $merchant_id, $action, $extra);

        if ($result['status']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('order_resolution/view/' . $ticket_number);
    }
}
