<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OrderResolutionController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (empty($this->session->userdata('LoginID')) && empty($_SESSION['LoginID'])) {
            redirect(base_url('customer/login'));
            return;
        }

        $this->load->model('CommonModel');
        $this->load->model('OrderResolutionModel');

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $this->lang->load('content', $site_lang);
    }

    /**
     * List Shopper's Order Resolution Tickets
     */
    public function index()
    {
        $customer_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];

        $this->db->select('r.*, p.name as product_name, pub.publication_name as merchant_name');
        $this->db->from('order_resolutions r');
        $this->db->join('products p', 'p.id = r.product_id', 'left');
        $this->db->join('publisher pub', 'pub.id = r.merchant_id', 'left');
        $this->db->where('r.customer_id', $customer_id);
        $this->db->order_by('r.created_at', 'DESC');
        $this->db->order_by('r.id', 'DESC');
        $data['resolutions'] = $this->db->get()->result();

        $data['PageTitle'] = 'Order Resolution Requests';
        $this->load->view('order_resolution/list', $data);
    }

    /**
     * Raise a new Order Resolution Request
     */
    /**
     * Raise a new Order Resolution Request (Exclusively for rejected return/replacement items)
     */
    public function create($order_id = 0, $product_id = 0)
    {
        $customer_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $order_id = (int)$order_id;
        $product_id = (int)$product_id;

        // Fetch customer's REJECTED return/replacement items
        $rejected_items = $this->OrderResolutionModel->get_customer_rejected_items($customer_id);

        // If specific order is requested, verify it has a merchant-rejected request
        $matched_item = null;
        if ($order_id > 0) {
            $matched_item = $this->OrderResolutionModel->is_item_eligible_for_resolution($order_id, $product_id, $customer_id);
            if (!$matched_item) {
                // Check if an active ticket already exists for this item
                $existing_ticket = $this->OrderResolutionModel->get_active_resolution_for_item($order_id, $product_id);
                if ($existing_ticket) {
                    $msg = sprintf($this->lang->line('active_resolution_exists') ?: 'An active Order Resolution ticket (#%s) already exists for this item.', $existing_ticket->ticket_number);
                    $this->session->set_flashdata('error', $msg);
                    redirect('order_resolution/view/' . $existing_ticket->ticket_number);
                    return;
                }

                $this->session->set_flashdata('error', $this->lang->line('resolution_gate_error') ?: 'Order Resolution is only available for items where a Return or Replacement request has been rejected by the Merchant.');
                redirect('customer/my-orders');
                return;
            }

            if (!empty($matched_item->product_id)) {
                $product_id = (int)$matched_item->product_id;
            }
        }

        $data['rejected_items']      = $rejected_items;
        $data['selected_order_id']   = $order_id;
        $data['selected_product_id'] = $product_id;
        $data['matched_item']        = $matched_item;

        $default_category = 'Refund';
        if ($matched_item && !empty($matched_item->category)) {
            $default_category = $matched_item->category;
        }
        $data['default_category'] = $default_category;

        $data['categories'] = ['Refund', 'Replacement', 'Delivery', 'Others'];
        $data['PageTitle']  = 'Raise Order Resolution Request';

        $this->load->view('order_resolution/create', $data);
    }

    /**
     * AJAX: Get products for selected order
     */
    public function get_order_products_ajax($order_id = 0)
    {
        $order_id = (int)$order_id;
        $products = $this->CommonModel->get_order_products($order_id);
        echo json_encode(['status' => true, 'products' => $products]);
    }

    /**
     * Helper to return JSON for AJAX or redirect with flashdata for standard requests
     */
    protected function json_response($status, $message, $extra = [], $fallback_redirect = null)
    {
        $is_ajax = $this->input->is_ajax_request()
            || ($this->input->get_request_header('X-Requested-With') === 'XMLHttpRequest')
            || ($this->input->post('is_ajax') == 1);

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(array_merge([
                'status'  => (bool)$status,
                'message' => $message,
            ], $extra));
            exit;
        }

        if ($status) {
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $message);
        }

        if ($fallback_redirect) {
            redirect($fallback_redirect);
        }
    }

    /**
     * Submit Order Resolution ticket
     */
    public function store()
    {
        $customer_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $order_id    = (int)$this->input->post('order_id', true);
        $product_id  = (int)$this->input->post('product_id', true);
        $category    = trim($this->input->post('category', true));
        $priority    = trim($this->input->post('priority', true));
        $message     = trim($this->input->post('message', true));

        // Validation
        if (empty($order_id)) {
            $this->json_response(false, 'Please select a valid order item.', [], 'order_resolution/create');
            return;
        }

        $valid_priorities = ['High', 'Medium', 'Low'];
        if (empty($priority) || !in_array($priority, $valid_priorities, true)) {
            $errMsg = $this->lang->line('err_priority_required') ?: 'Please select a valid priority (High, Medium, or Low).';
            $this->json_response(false, $errMsg, [], "order_resolution/create/{$order_id}/{$product_id}");
            return;
        }

        // Verify order belongs to customer
        $order_check = $this->db->where('order_id', $order_id)->where('customer_id', $customer_id)->get('sales_order')->row();
        if (!$order_check) {
            $this->json_response(false, 'The selected order does not belong to your account.', [], 'order_resolution/create');
            return;
        }

        // Mandatory Prerequisite: Item must have a rejected Return or Replacement request
        $matched_item = $this->OrderResolutionModel->is_item_eligible_for_resolution($order_id, $product_id, $customer_id);
        if (!$matched_item) {
            $existing_ticket = $this->OrderResolutionModel->get_active_resolution_for_item($order_id, $product_id);
            if ($existing_ticket) {
                $msg = sprintf($this->lang->line('active_resolution_exists') ?: 'An active Order Resolution ticket (#%s) already exists for this item.', $existing_ticket->ticket_number);
                $this->json_response(false, $msg, ['redirect' => base_url('order_resolution/view/' . $existing_ticket->ticket_number), 'requires_reload' => true], 'order_resolution/view/' . $existing_ticket->ticket_number);
                return;
            }

            $errMsg = $this->lang->line('resolution_gate_error') ?: 'Order Resolution is only available for items where a Return or Replacement request has been rejected by the Merchant.';
            $this->json_response(false, $errMsg, [], 'order_resolution/create');
            return;
        }

        // Prevent duplicate active tickets
        $existing_ticket = $this->OrderResolutionModel->get_active_resolution_for_item($order_id, $product_id);
        if ($existing_ticket) {
            $msg = sprintf($this->lang->line('active_resolution_exists') ?: 'An active Order Resolution ticket (#%s) already exists for this item.', $existing_ticket->ticket_number);
            $this->json_response(false, $msg, ['redirect' => base_url('order_resolution/view/' . $existing_ticket->ticket_number), 'requires_reload' => true], 'order_resolution/view/' . $existing_ticket->ticket_number);
            return;
        }

        if (empty($message)) {
            $this->json_response(false, $this->lang->line('err_message_empty') ?: 'Message cannot be empty.', [], "order_resolution/create/{$order_id}/{$product_id}");
            return;
        }

        // Handle attachment upload
        $attachment = '';
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
            $upload_path = './uploads/order_resolution/';
            if (!is_dir($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }
            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png|pdf|webp';
            $config['max_size']      = 5120; // 5MB
            $config['encrypt_name']  = true;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('attachment')) {
                $fileData = $this->upload->data();
                $attachment = $fileData['file_name'];
            }
        }

        $valid_categories = ['Delivery', 'Refund', 'Replacement', 'Others'];
        $postData = [
            'order_id'    => $order_id,
            'product_id'  => $product_id,
            'customer_id' => $customer_id,
            'category'    => in_array($category, $valid_categories, true) ? $category : 'Refund',
            'priority'    => $priority,
            'message'     => $message,
            'attachment'  => $attachment,
            'ip'          => $this->input->ip_address(),
        ];

        $resolution = $this->OrderResolutionModel->create_resolution($postData);

        if ($resolution) {
            $msg = "Order resolution ticket #{$resolution->ticket_number} created successfully.";
            $this->json_response(true, $msg, [
                'redirect'      => base_url('order_resolution/view/' . $resolution->ticket_number),
                'ticket_number' => $resolution->ticket_number
            ], 'order_resolution/view/' . $resolution->ticket_number);
        } else {
            $this->json_response(false, 'Failed to submit order resolution request. Please try again.', [], "order_resolution/create/{$order_id}/{$product_id}");
        }
    }

    /**
     * View Order Resolution Details & Conversation
     */
    public function view($ticket_number = '')
    {
        $customer_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number = trim($ticket_number);

        $resolution = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$resolution || (int)$resolution->customer_id !== (int)$customer_id) {
            $this->session->set_flashdata('error', 'Resolution ticket not found or unauthorized access.');
            redirect('order_resolution');
            return;
        }

        $data['resolution'] = $resolution;
        $data['messages']   = $this->OrderResolutionModel->get_messages($resolution->id);
        $data['audit_logs'] = $this->OrderResolutionModel->get_audit_log($resolution->id);

        // Product info
        $data['product'] = null;
        if (!empty($resolution->product_id)) {
            $data['product'] = $this->db->where('id', $resolution->product_id)->get('products')->row();
        }

        // Merchant info
        $data['merchant'] = null;
        if (!empty($resolution->merchant_id)) {
            $data['merchant'] = $this->db->where('id', $resolution->merchant_id)->get('publisher')->row();
        }

        $data['PageTitle'] = 'Order Resolution #' . $resolution->ticket_number;
        $this->load->view('order_resolution/view', $data);
    }

    /**
     * Shopper replies to Merchant within Order Resolution module
     */
    public function reply()
    {
        $customer_id   = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number   = trim($this->input->post('ticket_number', true));
        $message         = trim($this->input->post('message', true));
        $conversion_type = trim($this->input->post('conversion_type', true));

        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->customer_id !== (int)$customer_id) {
            $this->json_response(false, 'Invalid ticket.', [], 'order_resolution');
            return;
        }

        if (empty($conversion_type) || !in_array($conversion_type, ['shopper_merchant', 'shopper_admin'], true)) {
            $conversion_type = ($res->resolution_status === 'resolution_requested' || $res->status === 'ReOpen') ? 'shopper_admin' : 'shopper_merchant';
        }

        if ($res->status === 'Close (Final)') {
            $this->json_response(false, 'Ticket is permanently closed (Final). New replies are disabled.', ['requires_reload' => true], 'order_resolution/view/' . $ticket_number);
            return;
        }

        if (empty($message)) {
            $this->json_response(false, 'Reply message cannot be empty.', [], 'order_resolution/view/' . $ticket_number);
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

        $c_row = $this->db->select('first_name, last_name')->where('id', $customer_id)->get('customers')->row();
        $shopper_name = $c_row ? trim(($c_row->first_name ?? '') . ' ' . ($c_row->last_name ?? '')) : 'Shopper';

        $ok = $this->OrderResolutionModel->add_reply($ticket_number, 'shopper', $customer_id, $shopper_name, $message, $attachment, $this->input->ip_address(), $conversion_type);

        if ($ok) {
            $msg = $this->lang->line('reply_posted_success') ?: 'Your reply has been sent.';
            $this->json_response(true, $msg, ['reload' => true], 'order_resolution/view/' . $ticket_number);
        } else {
            $this->json_response(false, $this->lang->line('err_unable_post_reply') ?: 'Unable to post reply.', [], 'order_resolution/view/' . $ticket_number);
        }
    }

    /**
     * Shopper clicks "Resolution Request"
     */
    public function request_resolution()
    {
        $customer_id   = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $ticket_number = trim($this->input->post('ticket_number', true));
        $message       = trim($this->input->post('message', true));

        $res = $this->OrderResolutionModel->shopper_resolution_request($ticket_number, $customer_id, $message);
        $this->json_response($res['status'], $res['message'], $res, 'order_resolution/view/' . $ticket_number);
    }
}