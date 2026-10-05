<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'application/models/OrderResolutionModel.php';
require_once FCPATH . 'application/services/OrderResolutionService.php';

/**
 * OrderResolutionController (Admin & Account Portal)
 */
class OrderResolutionController extends CI_Controller
{
    protected $resolutionModel;
    protected $service;

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => false, 'message' => 'Session expired. Please login.']);
                exit;
            }
            redirect(base_url('login'));
        }

        $this->resolutionModel = new OrderResolutionModel();
        $this->service = new OrderResolutionService();

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $this->lang->load('content', $site_lang);
    }

    /**
     * Admin & Account ticket listing
     */
    public function index()
    {
        $status_filter = $this->input->get('status');
        $role_filter = $this->input->get('assigned_role');

        $this->db->select('hd.*, so.increment_id as order_increment_id, soi.product_name, u.publication_name as merchant_name, c.first_name, c.last_name')
            ->from('help_desk hd')
            ->join('sales_order so', 'so.order_id = hd.order_id', 'left')
            ->join('sales_order_items soi', 'soi.item_id = hd.order_item_id', 'left')
            ->join('users u', 'u.id = hd.merchant_id', 'left')
            ->join('users c', 'c.id = hd.customer_id', 'left');

        if (!empty($status_filter)) {
            $this->db->where('hd.status_code', $status_filter);
        }
        if (!empty($role_filter)) {
            $this->db->where('hd.assigned_role', $role_filter);
        }

        $tickets = $this->db->order_by('hd.id', 'DESC')->get()->result();

        $data['tickets'] = $tickets;
        $data['side_menu'] = 'help_desk';
        $data['page_title'] = 'Order Resolution Management';
        $data['current_status'] = $status_filter;
        $data['current_role'] = $role_filter;

        $this->load->view('order_resolution/index', $data);
    }

    /**
     * Admin & Account ticket conversation and controls
     */
    public function view($ticket_id)
    {
        $ticket = $this->resolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            $this->session->set_flashdata('error', 'Ticket not found.');
            redirect('admin/order-resolution');
        }

        $data['ticket'] = $ticket;
        $data['messages'] = $this->resolutionModel->get_messages($ticket_id);
        
        // Fetch audit logs
        $data['audit_logs'] = $this->db->from('help_desk_audit_log')
            ->where('ticket_id', $ticket_id)
            ->order_by('created_at', 'ASC')
            ->get()->result();

        // Fetch merchant hold-back balance preview
        $data['merchant_holdback_balance'] = $this->resolutionModel->get_merchant_holdback_balance($ticket->merchant_id);

        // Fetch list of admin/account users for assignment dropdown
        $data['account_users'] = $this->db->select('id, name, email')->from('adminusers')->get()->result();

        $data['side_menu'] = 'help_desk';
        $data['page_title'] = 'Resolution Ticket #' . $ticket->ticket_id;
        $data['user_role'] = $this->session->userdata('UserRole') ?: 'Administrator';

        $this->load->view('order_resolution/conversation', $data);
    }

    /**
     * Admin / Account replies to ticket
     */
    public function reply()
    {
        $admin_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $message = trim($this->input->post('message'));

        $ticket = $this->resolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            echo json_encode(['status' => false, 'message' => 'Ticket not found.']);
            return;
        }

        if (empty($message) && empty($_FILES['attachment']['name'])) {
            echo json_encode(['status' => false, 'message' => 'Please enter a message or select an image.']);
            return;
        }

        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $upload_path = './uploads/help_desk_attachment/';
            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp|pdf',
                'max_size'      => 5120,
                'encrypt_name'  => true
            ];
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('attachment')) {
                $uploadData = $this->upload->data();
                $attachment = $uploadData['file_name'];
            }
        }

        $admin_name = $this->session->userdata('Name') ?: 'Admin';
        $user_role = strtolower((string)$this->session->userdata('UserRole'));
        $sender_role = (strpos($user_role, 'account') !== false) ? 'account' : 'admin';

        $ok = $this->resolutionModel->add_message($ticket_id, $sender_role, $admin_id, $admin_name, $message, $attachment);

        if ($ok) {
            // Notify both Shopper and Merchant
            $this->service->send_resolution_notification($ticket, 'ticket_reply', 'New Admin Reply on Ticket: ' . $ticket_id, 'Admin replied to Ticket #' . $ticket_id, 'shopper');
            $this->service->send_resolution_notification($ticket, 'ticket_reply', 'Admin Update on Ticket: ' . $ticket_id, 'Admin replied to Ticket #' . $ticket_id, 'merchant');

            echo json_encode(['status' => true, 'message' => 'Reply sent successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to send reply.']);
        }
    }

    /**
     * @Admin assigns ticket to @Account
     */
    public function assign_account()
    {
        $admin_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $account_user_id = $this->input->post('account_user_id');

        $res = $this->service->admin_assign_account($ticket_id, $admin_id, $account_user_id);
        echo json_encode($res);
    }

    /**
     * @Account completes refund deduction from hold-back and marks Done
     */
    public function account_done()
    {
        $account_user_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));

        $params = [
            'notes'             => trim($this->input->post('notes')),
            'refund_amount'     => $this->input->post('refund_amount'),
            'refund_reference'  => trim($this->input->post('refund_reference'))
        ];

        $res = $this->service->account_mark_done($ticket_id, $account_user_id, $params);
        echo json_encode($res);
    }

    /**
     * @Admin normal ticket Close
     */
    public function admin_close()
    {
        $admin_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $notes = trim($this->input->post('close_notes') ?: $this->input->post('notes'));

        $res = $this->service->admin_close_ticket($ticket_id, $admin_id, $notes);
        echo json_encode($res);
    }

    /**
     * @Admin Dispute Resolution Decision (Approved or Denied)
     */
    public function admin_resolve()
    {
        $admin_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $decision = trim($this->input->post('decision')); // 'approved' or 'denied'
        $notes = trim($this->input->post('notes'));

        if (!in_array($decision, ['approved', 'denied'])) {
            echo json_encode(['status' => false, 'message' => 'Invalid resolution decision.']);
            return;
        }

        $res = $this->service->admin_resolution_decision($ticket_id, $admin_id, $decision, $notes);
        echo json_encode($res);
    }

    /**
     * @Admin Final Closure (Close Final) post-resolution refund
     */
    public function admin_close_final()
    {
        $admin_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $notes = trim($this->input->post('notes'));

        $res = $this->service->admin_close_final($ticket_id, $admin_id, $notes);
        echo json_encode($res);
    }
}
