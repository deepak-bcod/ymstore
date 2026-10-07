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
    }

    /**
     * @Help: All Resolution Tickets Listing
     */
    public function index()
    {
        $status_filter = $this->input->get('status', true);

        $this->db->select('r.*, p.name as product_name, pub.publication_name as merchant_name, c.first_name, c.last_name, c.email_id as customer_email');
        $this->db->from('order_resolutions r');
        $this->db->join('products p', 'p.id = r.product_id', 'left');
        $this->db->join('publisher pub', 'pub.id = r.merchant_id', 'left');
        $this->db->join('customers c', 'c.id = r.customer_id', 'left');

        if (!empty($status_filter) && in_array($status_filter, ['Open', 'Processing', 'Done', 'Close', 'ReOpen', 'Close (Final)'], true)) {
            $this->db->where('r.status', $status_filter);
        }

        $this->db->order_by('r.id', 'DESC');
        $data['resolutions']   = $this->db->get()->result();
        $data['status_filter'] = $status_filter;

        $data['side_menu'] = 'order_resolution';
        $data['PageTitle'] = 'Order Resolution Tickets (@Help)';
        $this->load->view('order_resolution/list', $data);
    }

    /**
     * @Acct: Assigned Order Resolution Tickets
     */
    public function acct_list()
    {
        $this->db->select('r.*, p.name as product_name, pub.publication_name as merchant_name, c.first_name, c.last_name, c.email_id as customer_email');
        $this->db->from('order_resolutions r');
        $this->db->join('products p', 'p.id = r.product_id', 'left');
        $this->db->join('publisher pub', 'pub.id = r.merchant_id', 'left');
        $this->db->join('customers c', 'c.id = r.customer_id', 'left');
        $this->db->where('r.assigned_role', 'Acct');
        $this->db->order_by('r.id', 'DESC');
        $data['resolutions'] = $this->db->get()->result();

        $data['side_menu'] = 'order_resolution_acct';
        $data['PageTitle'] = 'Assigned Order Resolution (@Acct)';
        $this->load->view('order_resolution/acct_list', $data);
    }

    /**
     * View Resolution Details, Audit Trail, Conversation, and Management Controls
     */
    public function view($ticket_number = '')
    {
        $ticket_number = trim($ticket_number);
        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);

        if (!$res) {
            $this->session->set_flashdata('error', 'Resolution ticket not found.');
            redirect('order_resolution');
            return;
        }

        $data['resolution'] = $res;
        $data['messages']   = $this->OrderResolutionModel->get_messages($res->id);
        $data['audit_logs'] = $this->OrderResolutionModel->get_audit_log($res->id);

        // Fetch Order, Product, Merchant, Customer
        $data['order']    = $this->db->where('order_id', $res->order_id)->get('sales_order')->row();
        $data['product']  = !empty($res->product_id) ? $this->db->where('id', $res->product_id)->get('products')->row() : null;
        $data['merchant'] = !empty($res->merchant_id) ? $this->db->where('id', $res->merchant_id)->get('publisher')->row() : null;
        $data['customer'] = !empty($res->customer_id) ? $this->db->where('id', $res->customer_id)->get('customers')->row() : null;

        $data['side_menu'] = ($res->assigned_role === 'Acct') ? 'order_resolution_acct' : 'order_resolution';
        $data['PageTitle'] = 'Order Resolution #' . $res->ticket_number;
        $this->load->view('order_resolution/view', $data);
    }

    /**
     * Admin Reply in conversation
     */
    public function reply()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $message       = trim($this->input->post('message', true));
        $role          = trim($this->input->post('role', true)) ?: 'help';

        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
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

        $sender_name = ($role === 'acct') ? 'Accounting Team (@Acct)' : 'Support Team (@Help)';
        $this->OrderResolutionModel->add_reply($ticket_number, $role, $admin_id, $sender_name, $message, $attachment, $this->input->ip_address());

        $this->session->set_flashdata('success', 'Reply posted successfully.');
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * @Help assigns ticket to @Acct (Status -> Processing)
     */
    public function assign_to_acct()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        $res = $this->OrderResolutionModel->admin_assign_to_acct($ticket_number, $admin_id, $notes);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * @Acct marks refund/payment completed (Status -> Done)
     */
    public function mark_done()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        $res = $this->OrderResolutionModel->admin_mark_done($ticket_number, $admin_id, $notes);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * @Help closes ticket with Shopper Resolution Option (Status -> Close)
     */
    public function close_ticket()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        $res = $this->OrderResolutionModel->admin_close_ticket($ticket_number, $admin_id, $notes);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * @Help finally closes resolution request with no further options (Status -> Close (Final))
     */
    public function close_final()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        $res = $this->OrderResolutionModel->admin_close_final($ticket_number, $admin_id, $notes);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }

    /**
     * Resolution Decision: Resolution Approved / Resolution Denied (@Help only)
     */
    public function resolution_decision()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $decision      = trim($this->input->post('decision', true)); // 'approved' or 'denied'
        $notes         = trim($this->input->post('notes', true));

        $res = $this->OrderResolutionModel->admin_resolution_decision($ticket_number, $decision, $admin_id, $notes);
        if ($res['status']) {
            $this->session->set_flashdata('success', $res['message']);
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }
}
