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

        $user_role = strtolower(trim((string)($this->session->userdata('UserRole') ?: ($_SESSION['UserRole'] ?? ''))));
        $is_super_admin = (strpos($user_role, 'super') !== false || strpos($user_role, 'administrator') !== false || $user_role === 'admin' || empty($user_role));

        $hide_acct = in_array($res->merchant_action, ['replacement_completed', 'replacement_denied'], true);

        // Determine current viewing mode: 'acct' vs 'help'
        $as_param = strtolower(trim((string)$this->input->get('as', true)));
        if (!$hide_acct && ($as_param === 'acct' || $as_param === 'help')) {
            $current_role = $as_param;
        } elseif (!$hide_acct && (strpos($user_role, 'account') !== false || strpos($user_role, 'acct') !== false)) {
            $current_role = 'acct';
        } elseif (strpos($user_role, 'help') !== false || strpos($user_role, 'support') !== false) {
            $current_role = 'help';
        } else {
            // Default for Super Admin / General Admin:
            $current_role = (!$hide_acct && $res->status === 'Processing' && $res->assigned_role === 'Acct') ? 'acct' : 'help';
        }

        $data['resolution']     = $res;
        $data['current_role']   = $current_role;
        $data['is_super_admin'] = $is_super_admin;
        $data['messages']       = $this->OrderResolutionModel->get_messages($res->id);
        $data['audit_logs']     = $this->OrderResolutionModel->get_audit_log($res->id);

        // Fetch Order, Product, Merchant, Customer
        $data['order']    = $this->db->where('order_id', $res->order_id)->get('sales_order')->row();
        $data['product']  = !empty($res->product_id) ? $this->db->where('id', $res->product_id)->get('products')->row() : null;
        $data['merchant'] = !empty($res->merchant_id) ? $this->db->where('id', $res->merchant_id)->get('publisher')->row() : null;
        $data['customer'] = !empty($res->customer_id) ? $this->db->where('id', $res->customer_id)->get('customers')->row() : null;

        $data['side_menu'] = ($current_role === 'acct') ? 'order_resolution_acct' : 'order_resolution';
        $data['PageTitle'] = 'Order Resolution #' . $res->ticket_number . ' (' . ($current_role === 'acct' ? '@Acct' : '@Help') . ')';
        $this->load->view('order_resolution/view', $data);
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
            $this->json_response(false, 'Invalid ticket.', [], 'order_resolution');
            return;
        }

        if ($res->status === 'Close (Final)') {
            $this->json_response(false, 'Ticket is permanently closed (Final). New replies are disabled.', ['requires_reload' => true], 'order_resolution/view/' . $ticket_number);
            return;
        }

        if (empty($message)) {
            $this->json_response(false, 'Message cannot be empty.', [], 'order_resolution/view/' . $ticket_number);
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
        $ok = $this->OrderResolutionModel->add_reply($ticket_number, $role, $admin_id, $sender_name, $message, $attachment, $this->input->ip_address());

        if ($ok) {
            // Get the customer ID from the resolution record
            $customer_id = $res->customer_id ?? null;

            if ($customer_id) {
                $title        = "Order resolution reply (" . $ticket_number . ")";
                $message_text = "Yellow Market team has replied to your resolution ticket (" . $ticket_number . ")";

                $notification_data = [
                    'type'           => 'order_resolution',
                    'subtype'        => 'reply',
                    'recipient_type' => 'customer',
                    'recipient_id'   => $customer_id,
                    'title'          => $title,
                    'message'        => $message_text,
                    'data'           => json_encode(['ticket_number' => $ticket_number]),
                    'is_read'        => 0,
                    'created_at'     => date('Y-m-d H:i:s')
                ];

                // Safely insert the notification into the database
                try {
                    $this->db->insert('notifications', $notification_data);
                } catch (\Exception $e) {
                    log_message('error', 'Notification insert failed: ' . $e->getMessage());
                }
            }

            $this->json_response(true, 'Reply posted successfully.', ['reload' => true], 'order_resolution/view/' . $ticket_number . '?as=' . $role);
        } else {
            $this->json_response(false, 'Unable to post reply.', [], 'order_resolution/view/' . $ticket_number . '?as=' . $role);
        }
    }

    /**
     * @Help assigns ticket to @Acct (Status -> Processing)
     */
    public function assign_to_acct()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        // Guard: Only @Help or Super Admin can assign
        $user_role = strtolower(trim((string)($this->session->userdata('UserRole') ?: ($_SESSION['UserRole'] ?? ''))));
        if (!empty($user_role) && strpos($user_role, 'account') !== false && strpos($user_role, 'admin') === false) {
            $this->json_response(false, 'Unauthorized: Only Support (@Help) can assign tickets to Accounting.', [], 'order_resolution/view/' . $ticket_number . '?as=acct');
            return;
        }

        $res = $this->OrderResolutionModel->admin_assign_to_acct($ticket_number, $admin_id, $notes);
        $this->json_response($res['status'], $res['message'], $res, 'order_resolution/view/' . $ticket_number . '?as=help');
    }

    /**
     * @Acct marks refund/payment completed (Status -> Done)
     */
    public function mark_done()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        // Permission Guard: Only Accounting (@Acct) or Super Admin/Administrator can mark done
        $user_role = strtolower(trim((string)($this->session->userdata('UserRole') ?: ($_SESSION['UserRole'] ?? ''))));
        if (!empty($user_role) && strpos($user_role, 'help') !== false && strpos($user_role, 'admin') === false) {
            $this->json_response(false, 'Unauthorized: Only Accounting (@Acct) can process refunds and mark tickets as Done.', [], 'order_resolution/view/' . $ticket_number . '?as=help');
            return;
        }

        $res = $this->OrderResolutionModel->admin_mark_done($ticket_number, $admin_id, $notes);
        $this->json_response($res['status'], $res['message'], $res, 'order_resolution/view/' . $ticket_number . '?as=acct');
    }

    /**
     * @Help closes ticket with Shopper Resolution Option (Status -> Close)
     */
   public function close_ticket()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        // Permission Guard: Only Support (@Help) or Super Admin/Administrator can close ticket
        $user_role = strtolower(trim((string)($this->session->userdata('UserRole') ?: ($_SESSION['UserRole'] ?? ''))));
        if (!empty($user_role) && strpos($user_role, 'account') !== false && strpos($user_role, 'admin') === false) {
            $this->json_response(false, 'Unauthorized: Only Support (@Help) can close tickets.', [], 'order_resolution/view/' . $ticket_number . '?as=acct');
            return;
        }

        // Get ticket details to retrieve the customer_id
        $res = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);

        $result = $this->OrderResolutionModel->admin_close_ticket($ticket_number, $admin_id, $notes);

        if ($result['status'] && $res && !empty($res->customer_id)) {
            $title        = "Resolution ticket is closed";
            $message_text = "Your resolution ticket no (" . $ticket_number . ") is closed. If you wish to escalate with Yellow Market team plz go back inside you tick and raise resolution with yellow market team.";

            $notification_data = [
                'type'           => 'order_resolution',
                'subtype'        => 'close',
                'recipient_type' => 'customer',
                'recipient_id'   => $res->customer_id,
                'title'          => $title,
                'message'        => $message_text,
                'data'           => json_encode(['ticket_number' => $ticket_number]),
                'is_read'        => 0,
                'created_at'     => date('Y-m-d H:i:s')
            ];

            // Safely insert the notification into the database
            try {
                $this->db->insert('notifications', $notification_data);
            } catch (\Exception $e) {
                log_message('error', 'Close ticket notification insert failed: ' . $e->getMessage());
            }
        }

        $this->json_response($result['status'], $result['message'], $result, 'order_resolution/view/' . $ticket_number . '?as=help');
    }

    /**
     * @Help finally closes resolution request with no further options (Status -> Close (Final))
     */
    public function close_final()
    {
        $admin_id      = $this->session->userdata('LoginID') ?: ($_SESSION['LoginID'] ?? 1);
        $ticket_number = trim($this->input->post('ticket_number', true));
        $notes         = trim($this->input->post('notes', true));

        // Permission Guard: Only Support (@Help) or Super Admin/Administrator can close ticket (Final)
        $user_role = strtolower(trim((string)($this->session->userdata('UserRole') ?: ($_SESSION['UserRole'] ?? ''))));
        if (!empty($user_role) && strpos($user_role, 'account') !== false && strpos($user_role, 'admin') === false) {
            $this->json_response(false, 'Unauthorized: Only Support (@Help) can finalize and close Resolution tickets.', [], 'order_resolution/view/' . $ticket_number . '?as=acct');
            return;
        }

        $res = $this->OrderResolutionModel->admin_close_final($ticket_number, $admin_id, $notes);
        $this->json_response($res['status'], $res['message'], $res, 'order_resolution/view/' . $ticket_number . '?as=help');
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

        // Get ticket details to retrieve customer_id before updating decision
        $res_ticket = $this->OrderResolutionModel->get_resolution_by_ticket_number($ticket_number);

        $res = $this->OrderResolutionModel->admin_resolution_decision($ticket_number, $decision, $admin_id, $notes);

        // If action was successful, send notification to the customer
        if (isset($res['status']) && $res['status'] && $res_ticket && !empty($res_ticket->customer_id)) {
            
            if ($decision === 'approved') {
                $title        = "Resolution ticket approved";
                $message_text = "Your resolution ticket no (" . $ticket_number . ") is approved by The yellow market team, Team is working on it and will update you asap.";
                $subtype      = 'resolution_approved';
            } else {
                $title        = "Resolution ticket denied";
                $message_text = "Your resolution ticket no (" . $ticket_number . ") is denied by The yellow market team.";
                $subtype      = 'resolution_denied';
            }

            $notification_data = [
                'type'           => 'order_resolution',
                'subtype'        => $subtype,
                'recipient_type' => 'customer',
                'recipient_id'   => $res_ticket->customer_id,
                'title'          => $title,
                'message'        => $message_text,
                'data'           => json_encode(['ticket_number' => $ticket_number, 'decision' => $decision]),
                'is_read'        => 0,
                'created_at'     => date('Y-m-d H:i:s')
            ];

            try {
                $this->db->insert('notifications', $notification_data);
            } catch (\Exception $e) {
                log_message('error', 'Resolution decision notification insert failed: ' . $e->getMessage());
            }
        }

        $this->json_response($res['status'], $res['message'], $res, 'order_resolution/view/' . $ticket_number);
    }
}
