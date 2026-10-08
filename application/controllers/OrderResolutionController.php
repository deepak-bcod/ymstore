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
        $this->db->order_by('r.id', 'DESC');
        $data['resolutions'] = $this->db->get()->result();

        $data['PageTitle'] = 'Order Resolution Requests';
        $this->load->view('order_resolution/list', $data);
    }

    /**
     * Raise a new Order Resolution Request
     */
    /**
     * Raise a new Order Resolution Request (Complete orders only)
     */
    public function create($order_id = 0, $product_id = 0)
    {
        $customer_id = $this->session->userdata('LoginID') ?: $_SESSION['LoginID'];
        $order_id = (int)$order_id;
        $product_id = (int)$product_id;

        // If specific order is requested, verify it is Complete
        if ($order_id > 0) {
            if (!$this->OrderResolutionModel->is_order_complete($order_id)) {
                $this->session->set_flashdata('error', $this->lang->line('resolution_complete_only') ?: 'Order Resolution (Refund, Return, Replacement) is only available for orders with status "Complete". This order is not yet Complete.');
                redirect('customer/my-orders');
                return;
            }
        }

        // Fetch customer's COMPLETED orders only for dropdown
        $data['orders'] = $this->OrderResolutionModel->get_customer_completed_orders($customer_id);

        $data['selected_order_id']   = $order_id;
        $data['selected_product_id'] = $product_id;
        $data['products']            = [];

        if ($order_id > 0) {
            $data['products'] = $this->CommonModel->get_order_products($order_id);
        }

        $data['categories'] = ['Delivery', 'Refund', 'Replacement', 'Others'];
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
            $this->session->set_flashdata('error', 'Please select a valid order.');
            redirect('order_resolution/create');
            return;
        }

        $valid_priorities = ['High', 'Medium', 'Low'];
        if (empty($priority) || !in_array($priority, $valid_priorities, true)) {
            $this->session->set_flashdata('error', $this->lang->line('err_priority_required') ?: 'Please select a valid priority (High, Medium, or Low).');
            redirect("order_resolution/create/{$order_id}/{$product_id}");
            return;
        }

        // Verify order belongs to customer
        $order_check = $this->db->where('order_id', $order_id)->where('customer_id', $customer_id)->get('sales_order')->row();
        if (!$order_check) {
            $this->session->set_flashdata('error', 'The selected order does not belong to your account.');
            redirect('order_resolution/create');
            return;
        }

        // Eligibility check: Order status must be Complete
        if (!$this->OrderResolutionModel->is_order_complete($order_check)) {
            $this->session->set_flashdata('error', $this->lang->line('resolution_complete_only') ?: 'Order Resolution (Refund, Return, Replacement) can only be raised for orders with status "Complete".');
            redirect('order_resolution/create');
            return;
        }

        if (empty($message)) {
            $this->session->set_flashdata('error', 'Message cannot be empty.');
            redirect("order_resolution/create/{$order_id}/{$product_id}");
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
            $this->session->set_flashdata('success', "Order resolution ticket #{$resolution->ticket_number} created successfully.");
            redirect('order_resolution/view/' . $resolution->ticket_number);
        } else {
            $this->session->set_flashdata('error', 'Failed to submit order resolution request. Please try again.');
            redirect("order_resolution/create/{$order_id}/{$product_id}");
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
        $ticket_number = trim($this->input->post('ticket_number', true));
        $message       = trim($this->input->post('message', true));

        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->customer_id !== (int)$customer_id) {
            $this->session->set_flashdata('error', 'Invalid ticket.');
            redirect('order_resolution');
            return;
        }

        if (empty($message)) {
            $this->session->set_flashdata('error', 'Reply message cannot be empty.');
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

        $c_row = $this->db->select('first_name, last_name')->where('id', $customer_id)->get('customers')->row();
        $shopper_name = $c_row ? trim(($c_row->first_name ?? '') . ' ' . ($c_row->last_name ?? '')) : 'Shopper';

        $this->OrderResolutionModel->add_reply($ticket_number, 'shopper', $customer_id, $shopper_name, $message, $attachment, $this->input->ip_address());

        $this->session->set_flashdata('success', 'Your reply has been sent.');
        redirect('order_resolution/view/' . $ticket_number);
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
        if ($res['status']) {
            $this->session->set_flashdata('success', 'Resolution escalation requested successfully.');
        } else {
            $this->session->set_flashdata('error', $res['message']);
        }
        redirect('order_resolution/view/' . $ticket_number);
    }
}