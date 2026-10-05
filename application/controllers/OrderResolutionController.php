<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'services/OrderResolutionService.php';

/**
 * OrderResolutionController (Shopper Webshop)
 */
class OrderResolutionController extends CI_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => false, 'message' => 'Please login to continue.', 'redirect' => BASE_URL . 'customer/login']);
                exit;
            }
            redirect(BASE_URL . 'customer/login');
        }

        $this->load->model('OrderResolutionModel');
        $this->service = new OrderResolutionService();

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $this->lang->load('content', $site_lang);
    }

    /**
     * Shopper creates ticket from My Orders modal
     */
    public function create()
    {
        $customer_id = (int)$this->session->userdata('LoginID');
        $order_id = (int)$this->input->post('order_id');
        $order_item_id = (int)$this->input->post('order_item_id');
        $product_id = (int)$this->input->post('product_id');
        $merchant_id = (int)$this->input->post('merchant_id');
        $category = trim($this->input->post('category'));
        $priority = trim($this->input->post('priority'));
        $message = trim($this->input->post('message'));

        if (empty($category) || empty($priority) || empty($order_id) || empty($product_id) || empty($message)) {
            echo json_encode(['status' => false, 'message' => 'All mandatory fields (Category, Priority, Order Number, Product, Message) are required.']);
            return;
        }

        // Check if active ticket already exists on this order item
        if (!empty($order_item_id)) {
            $existing = $this->OrderResolutionModel->get_active_item_ticket($order_item_id);
            if ($existing) {
                echo json_encode([
                    'status' => false, 
                    'message' => 'An active ticket (#' . $existing->ticket_id . ') already exists for this product item.',
                    'ticket_id' => $existing->ticket_id
                ]);
                return;
            }
        }

        // Get main order and sub-order details
        $order = $this->db->select('order_id, increment_id')->from('sales_order')->where('order_id', $order_id)->get()->row();
        if (!$order) {
            echo json_encode(['status' => false, 'message' => 'Order not found.']);
            return;
        }

        $b2b_order = $this->db->select('order_id')->from('b2b_orders')
            ->where('webshop_order_id', $order_id)
            ->where('merchant_id', $merchant_id)
            ->get()->row();

        // Handle optional attachment upload
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $upload_path = './uploads/help_desk_attachment/';
            if (!is_dir($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp|pdf',
                'max_size'      => 5120, // 5MB
                'encrypt_name'  => true
            ];
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('attachment')) {
                $uploadData = $this->upload->data();
                $attachment = $uploadData['file_name'];
            }
        }

        $customer_name = $this->session->userdata('FirstName') . ' ' . $this->session->userdata('LastName');

        $ticket_id = $this->OrderResolutionModel->create_ticket([
            'order_id'              => $order_id,
            'order_increment_id'    => $order->increment_id,
            'order_item_id'         => $order_item_id,
            'b2b_order_id'          => $b2b_order ? $b2b_order->order_id : null,
            'product_id'            => $product_id,
            'merchant_id'           => $merchant_id,
            'customer_id'           => $customer_id,
            'customer_name'         => trim($customer_name) ?: 'Shopper',
            'category'              => $category,
            'priority'              => $priority,
            'subject'               => 'Order Resolution #' . $order->increment_id . ' - ' . $category,
            'message'               => $message,
            'attachment'            => $attachment
        ]);

        if ($ticket_id) {
            // Send initial notifications
            $ticket_obj = $this->OrderResolutionModel->get_ticket_by_number($ticket_id);
            $this->service->send_new_ticket_notifications($ticket_obj, $message);

            echo json_encode([
                'status' => true,
                'message' => 'Ticket #' . $ticket_id . ' created successfully.',
                'ticket_id' => $ticket_id,
                'redirect' => base_url('order-resolution/view/' . $ticket_id)
            ]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to create ticket. Please try again.']);
        }
    }

    /**
     * View Ticket conversation (Shopper)
     */
    public function view($ticket_id)
    {
        $customer_id = (int)$this->session->userdata('LoginID');
        $ticket = $this->OrderResolutionModel->get_ticket_by_number($ticket_id);

        if (!$ticket || (int)$ticket->customer_id !== $customer_id) {
            $this->session->set_flashdata('error', 'Ticket not found or unauthorized.');
            redirect('my-orders');
        }

        $data['ticket'] = $ticket;
        $data['messages'] = $this->OrderResolutionModel->get_messages($ticket_id);
        $data['PageTitle'] = 'Order Resolution - Ticket #' . $ticket->ticket_id;
        $data['side_tab'] = 'my_orders';

        $this->template->load('order_resolution/conversation', $data);
    }

    /**
     * Shopper replies to ticket
     */
    public function reply()
    {
        $customer_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $message = trim($this->input->post('message'));

        $ticket = $this->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket || (int)$ticket->customer_id !== $customer_id) {
            echo json_encode(['status' => false, 'message' => 'Ticket not found or unauthorized.']);
            return;
        }

        if (in_array($ticket->status_code, ['Close', 'Close (Final)'])) {
            echo json_encode(['status' => false, 'message' => 'This ticket is closed. You cannot send replies.']);
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

        $customer_name = $this->session->userdata('FirstName') . ' ' . $this->session->userdata('LastName');
        $ok = $this->OrderResolutionModel->add_message($ticket_id, 'shopper', $customer_id, trim($customer_name) ?: 'Shopper', $message, $attachment);

        if ($ok) {
            // Notify Merchant
            $this->service->send_shopper_reply_notification($ticket);

            echo json_encode(['status' => true, 'message' => 'Reply sent successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to send reply.']);
        }
    }

    /**
     * Shopper submits Resolution Request (ReOpen)
     */
    public function request_resolution()
    {
        $customer_id = (int)$this->session->userdata('LoginID');
        $ticket_id = trim($this->input->post('ticket_id'));
        $reason = trim($this->input->post('dispute_reason'));

        if (empty($reason)) {
            echo json_encode(['status' => false, 'message' => 'Please provide a reason for the Resolution Request.']);
            return;
        }

        $res = $this->service->shopper_resolution_request($ticket_id, $customer_id, $reason);
        echo json_encode($res);
    }
}
