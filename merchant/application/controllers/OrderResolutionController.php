<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Require shared service and models from root application
require_once FCPATH . 'application/models/OrderResolutionModel.php';
require_once FCPATH . 'application/services/OrderResolutionService.php';

/**
 * OrderResolutionController (Merchant Portal)
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
     * Merchant ticket listing
     */
    public function index()
    {
        $merchant_id = (int)$this->session->userdata('LoginID');

        $tickets = $this->db->select('hd.*, so.increment_id as order_increment_id, soi.product_name, c.first_name, c.last_name')
            ->from('help_desk hd')
            ->join('sales_order so', 'so.order_id = hd.order_id', 'left')
            ->join('sales_order_items soi', 'soi.item_id = hd.order_item_id', 'left')
            ->join('users c', 'c.id = hd.customer_id', 'left')
            ->where('hd.merchant_id', $merchant_id)
            ->order_by('hd.id', 'DESC')
            ->get()
            ->result();

        $data['tickets'] = $tickets;
        $data['side_menu'] = 'help_desk';
        $data['page_title'] = 'Order Resolution Tickets';

        $this->load->view('order_resolution/index', $data);
    }

    /**
     * Merchant ticket details & decision actions
     */
    public function view($ticket_id)
    {
        $merchant_id = (int)$this->session->userdata('LoginID');
        $ticket = $this->resolutionModel->get_ticket_by_number($ticket_id);

        if (!$ticket || (int)$ticket->merchant_id !== $merchant_id) {
            $this->session->set_flashdata('error', 'Ticket not found or unauthorized.');
            redirect('help_desk/shopper');
        }

        $data['ticket'] = $ticket;
        $data['messages'] = $this->resolutionModel->get_messages($ticket_id);
        $data['side_menu'] = 'help_desk';
        $data['page_title'] = 'Resolution Ticket #' . $ticket->ticket_id;

        $this->load->view('order_resolution/conversation', $data);
    }

    /**
     * Merchant replies
     */
    public function reply()
    {
        $merchant_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $message = trim($this->input->post('message'));

        $ticket = $this->resolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket || (int)$ticket->merchant_id !== $merchant_id) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized or ticket not found.']);
            return;
        }

        if (empty($message) && empty($_FILES['attachment']['name'])) {
            echo json_encode(['status' => false, 'message' => 'Please enter a reply message or upload an image.']);
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

        $merchant_name = $this->session->userdata('PublicationName') ?: 'Merchant';
        $ok = $this->resolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $merchant_name, $message, $attachment);

        if ($ok) {
            // Notify Shopper
            $this->service->send_merchant_reply_notification($ticket);

            echo json_encode(['status' => true, 'message' => 'Reply sent successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to send reply.']);
        }
    }

    /**
     * Merchant decision actions:
     * - refund_approved
     * - refund_denied
     * - replacement_approved (own_delivery, self_pickup, ym_delivery)
     * - replacement_denied
     * - replacement_completed
     */
    public function action()
    {
        $merchant_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $action = trim($this->input->post('action'));

        $params = [
            'reason'            => trim($this->input->post('reason')),
            'delivery_option'   => trim($this->input->post('delivery_option')),
            'dispatch_notes'    => trim($this->input->post('dispatch_notes'))
        ];

        $res = $this->service->handle_merchant_action($ticket_id, $merchant_id, $action, $params);
        echo json_encode($res);
    }
}
