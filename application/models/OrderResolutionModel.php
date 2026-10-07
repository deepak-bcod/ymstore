<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OrderResolutionModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('CommonModel');
    }

    /**
     * Generate unique sequential ticket number (e.g. RES-0001)
     */
    public function generate_ticket_number()
    {
        $row = $this->db->select('MAX(id) as max_id')->get('order_resolutions')->row();
        $next = ($row && $row->max_id) ? ((int)$row->max_id + 1) : 1;
        return 'RES-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if an order is eligible for resolution (Status must be 'Complete')
     *
     * @param int|object $order_id_or_obj
     * @return bool
     */
    public function is_order_complete($order_id_or_obj)
    {
        if (is_object($order_id_or_obj)) {
            $status = $order_id_or_obj->status ?? null;
        } else {
            $row = $this->db->select('order_id, status')->where('order_id', (int)$order_id_or_obj)->get('sales_order')->row();
            if (!$row) return false;
            $status = $row->status;
        }

        if ($status === null) return false;

        if (is_string($status) && strtolower($status) === 'complete') {
            return true;
        }

        $non_completed_statuses = [0, 1, 3, 4, 5, 6, 7, 10, 11, 12, 13, 16];
        $status_int = (int)$status;

        if (in_array($status_int, $non_completed_statuses, true)) {
            return false;
        }

        // 2 = Completed, 8 = Completed, 9 = Completed, or dispute states (14..23)
        $completed_statuses = [2, 8, 9, 14, 15, 17, 18, 19, 20, 21, 22, 23];
        return in_array($status_int, $completed_statuses, true);
    }

    /**
     * Fetch customer orders eligible for resolution (Only completed orders)
     *
     * @param int $customer_id
     * @return array
     */
    public function get_customer_completed_orders($customer_id)
    {
        $all_orders = $this->db->select('order_id, increment_id, status, created_at, grand_total')
            ->from('sales_order')
            ->where('customer_id', (int)$customer_id)
            ->order_by('order_id', 'DESC')
            ->get()
            ->result();

        $completed_orders = [];
        foreach ($all_orders as $order) {
            if ($this->is_order_complete($order)) {
                $completed_orders[] = $order;
            }
        }
        return $completed_orders;
    }

    /**
     * Create a new Order Resolution ticket
     */
    public function create_resolution($data)
    {
        $ticket_number = $this->generate_ticket_number();
        $order_id      = (int)$data['order_id'];
        $product_id    = (int)($data['product_id'] ?? 0);
        $customer_id   = (int)$data['customer_id'];
        $merchant_id   = (int)($data['merchant_id'] ?? 0);
        $category      = $data['category'] ?? 'Others';
        $priority      = $data['priority'] ?? 'Medium';
        $message       = trim($data['message'] ?? '');
        $attachment    = $data['attachment'] ?? '';
        $ip            = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $time          = time();

        // Resolve order number (increment_id)
        $order_row = $this->db->select('increment_id, customer_email, customer_firstname, customer_lastname')
            ->where('order_id', $order_id)->get('sales_order')->row();
        $order_number = $order_row ? ($order_row->increment_id ?: (string)$order_id) : (string)$order_id;

        // Resolve merchant if missing
        if (empty($merchant_id) && !empty($product_id)) {
            $p_row = $this->db->select('publisher_id')->where('id', $product_id)->get('products')->row();
            if ($p_row && !empty($p_row->publisher_id)) {
                $merchant_id = (int)$p_row->publisher_id;
            }
        }
        if (empty($merchant_id)) {
            $b2b_row = $this->db->select('publisher_id, order_id')->where('webshop_order_id', $order_id)->get('b2b_orders')->row();
            if ($b2b_row && !empty($b2b_row->publisher_id)) {
                $merchant_id = (int)$b2b_row->publisher_id;
            }
        }

        // Resolve b2b_order_id and order_item_id
        $b2b_order_id = null;
        $order_item_id = null;
        if (!empty($merchant_id)) {
            $b2b_row = $this->db->select('order_id')->where('webshop_order_id', $order_id)->where('publisher_id', $merchant_id)->get('b2b_orders')->row();
            if ($b2b_row) {
                $b2b_order_id = $b2b_row->order_id;
            }
        }
        if (!empty($product_id)) {
            $soi_row = $this->db->select('item_id')->where('order_id', $order_id)->where('product_id', $product_id)->get('sales_order_items')->row();
            if ($soi_row) {
                $order_item_id = $soi_row->item_id;
            }
        }

        $resolution_data = [
            'ticket_number'    => $ticket_number,
            'order_id'         => $order_id,
            'order_number'     => $order_number,
            'product_id'       => $product_id ?: null,
            'order_item_id'    => $order_item_id,
            'b2b_order_id'     => $b2b_order_id,
            'merchant_id'      => $merchant_id ?: null,
            'customer_id'      => $customer_id,
            'category'         => $category,
            'priority'         => $priority,
            'status'           => 'Open',
            'merchant_action'  => 'none',
            'delivery_option'  => 'none',
            'resolution_status'=> 'none',
            'assigned_role'    => 'Help',
            'subject'          => $data['subject'] ?? ($category . ' - ' . $order_number),
            'message'          => $message,
            'attachment'       => $attachment,
            'is_active_dispute'=> 1,
            'created_at'       => $time,
            'updated_at'       => $time,
            'ip'               => $ip,
        ];

        $this->db->insert('order_resolutions', $resolution_data);
        $resolution_id = $this->db->insert_id();

        if (!$resolution_id) {
            return false;
        }

        // Insert initial message into order_resolution_messages
        $shopper_name = 'Shopper';
        if (!empty($customer_id)) {
            $c_row = $this->db->select('first_name, last_name')->where('id', $customer_id)->get('customers')->row();
            if ($c_row) {
                $shopper_name = trim(($c_row->first_name ?? '') . ' ' . ($c_row->last_name ?? '')) ?: 'Shopper';
            }
        }

        $this->db->insert('order_resolution_messages', [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'sender_role'   => 'shopper',
            'sender_id'     => $customer_id,
            'sender_name'   => $shopper_name,
            'message'       => $message,
            'attachment'    => $attachment,
            'created_at'    => $time,
            'ip'            => $ip,
        ]);

        // Audit Log
        $this->db->insert('order_resolution_audit_log', [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'from_status'   => '',
            'to_status'     => 'Open',
            'action'        => 'Ticket Created',
            'actor_role'    => 'shopper',
            'actor_id'      => $customer_id,
            'notes'         => 'Order resolution request submitted by shopper.',
            'created_at'    => $time,
        ]);

        // Update product / order status in b2b & sales_order
        if (strtolower($category) === 'replacement') {
            $this->update_order_item_status($order_id, $product_id, $merchant_id, 15); // Replacement Requested
        } elseif (in_array(strtolower($category), ['refund', 'return'], true)) {
            $this->update_order_item_status($order_id, $product_id, $merchant_id, 14); // Return/Refund Requested
        }

        // Send Email Notifications
        $this->send_creation_emails($resolution_data, $shopper_name);

        return $this->get_resolution_by_ticket_number($ticket_number);
    }

    /**
     * Update order and item status in b2b_order_items, b2b_orders, and sales_order_items
     */
    public function update_order_item_status($order_id, $product_id, $merchant_id, $target_status)
    {
        if (empty($order_id) || empty($target_status)) {
            return;
        }

        // 1. Update b2b_orders and b2b_order_items
        $b2b_query = $this->db->select('order_id')->from('b2b_orders')->where('webshop_order_id', $order_id);
        if (!empty($merchant_id)) {
            $b2b_query->where('publisher_id', $merchant_id);
        }
        $b2b_orders = $b2b_query->get()->result();

        if (!empty($b2b_orders)) {
            foreach ($b2b_orders as $b2b) {
                $this->db->where('order_id', $b2b->order_id);
                if (!empty($product_id)) {
                    $this->db->where('product_id', $product_id);
                }
                $this->db->update('b2b_order_items', ['status' => $target_status]);
                $this->db->where('order_id', $b2b->order_id)->update('b2b_orders', ['status' => $target_status]);
            }
        }

        // 2. Update sales_order_items
        $this->db->where('order_id', $order_id);
        if (!empty($product_id)) {
            $this->db->where('product_id', $product_id);
        }
        $this->db->update('sales_order_items', ['status' => $target_status]);
    }

    /**
     * Send initial creation emails to Merchant and @Help
     */
    private function send_creation_emails($res, $shopper_name)
    {
        $ticket_number = $res['ticket_number'];
        $order_number  = $res['order_number'];
        $category      = $res['category'];
        $priority      = $res['priority'];
        $message       = $res['message'];

        // Product Name
        $product_name = 'N/A';
        if (!empty($res['product_id'])) {
            $p_row = $this->db->select('name')->where('id', $res['product_id'])->get('products')->row();
            if ($p_row && !empty($p_row->name)) {
                $product_name = html_entity_decode($p_row->name, ENT_QUOTES, 'UTF-8');
            }
        }

        // Merchant Details
        $merchant_name = 'N/A';
        $merchant_email = '';
        if (!empty($res['merchant_id'])) {
            $m_row = $this->db->select('publication_name, email')->where('id', $res['merchant_id'])->get('publisher')->row();
            if ($m_row) {
                $merchant_name  = $m_row->publication_name;
                $merchant_email = $m_row->email;
            }
        }

        // URLs
        $merchant_url = (defined('BASE_URL2') ? rtrim(BASE_URL2, '/') . '/' : base_url('merchant/')) . 'order_resolution/view/' . $ticket_number;
        $admin_url    = base_url('admin/order_resolution/view/' . $ticket_number);

        // 1. Email to Merchant: order-resolution-merchant
        if (!empty($merchant_email)) {
            $tempVars = [
                '##TICKET_NUMBER##', '{ticket_number}',
                '##ORDER_NUMBER##', '{order_number}',
                '##CATEGORY##', '{category}',
                '##PRODUCT_NAME##', '{product_name}',
                '##PRIORITY##', '{priority}',
                '##SHOPPER_MESSAGE##', '##MESSAGE##', '{shopper_message}', '{message}',
                '##TICKET_URL##', '{ticket_url}',
                '##MERCHANT_NAME##', '{merchant_name}',
                '##SHOPPER_NAME##', '{shopper_name}',
                '##WEBSHOPNAME##', '{webshop_name}'
            ];
            $dynamicVars = [
                $ticket_number, $ticket_number,
                $order_number, $order_number,
                $category, $category,
                $product_name, $product_name,
                $priority, $priority,
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                $merchant_url, $merchant_url,
                $merchant_name, $merchant_name,
                $shopper_name, $shopper_name,
                'Yellow Markets', 'Yellow Markets'
            ];
            $this->CommonModel->sendCommonHTMLEmail($merchant_email, 'order-resolution-merchant', $tempVars, $dynamicVars);
        }

        // 2. Email to @Help: order-resolution-help
        $help_email = $this->CommonModel->get_custom_variable('contact_us_email')
            ?: ($this->CommonModel->get_custom_variable('admin_email') ?: 'help@yellowmarkets.com');

        if (!empty($help_email)) {
            $helpTempVars = [
                '##TICKET_NUMBER##', '{ticket_number}',
                '##ORDER_NUMBER##', '{order_number}',
                '##CATEGORY##', '{category}',
                '##MERCHANT_NAME##', '{merchant_name}',
                '##SHOPPER_NAME##', '{shopper_name}',
                '##PRODUCT_NAME##', '{product_name}',
                '##PRIORITY##', '{priority}',
                '##SHOPPER_MESSAGE##', '##MESSAGE##', '{shopper_message}', '{message}',
                '##TICKET_URL##', '{ticket_url}',
                '##WEBSHOPNAME##', '{webshop_name}'
            ];
            $helpDynamicVars = [
                $ticket_number, $ticket_number,
                $order_number, $order_number,
                $category, $category,
                $merchant_name, $merchant_name,
                $shopper_name, $shopper_name,
                $product_name, $product_name,
                $priority, $priority,
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                $admin_url, $admin_url,
                'Yellow Markets', 'Yellow Markets'
            ];
            $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-help', $helpTempVars, $helpDynamicVars);
        }
    }

    /**
     * Add conversation reply (shopper, merchant, admin/help, acct)
     */
    public function add_reply($ticket_number, $sender_role, $sender_id, $sender_name, $message, $attachment = '', $ip = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return false;
        }

        $time = time();
        $this->db->insert('order_resolution_messages', [
            'resolution_id' => $res->id,
            'ticket_number' => $ticket_number,
            'sender_role'   => $sender_role,
            'sender_id'     => $sender_id,
            'sender_name'   => $sender_name,
            'message'       => $message,
            'attachment'    => $attachment,
            'created_at'    => $time,
            'ip'            => $ip ?: ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);

        $this->db->where('id', $res->id)->update('order_resolutions', ['updated_at' => $time]);

        // Trigger Notifications based on sender
        if ($sender_role === 'merchant') {
            $this->send_merchant_reply_notification($res, $message);
        } elseif ($sender_role === 'shopper') {
            $this->send_shopper_reply_notification($res, $message);
        }

        return true;
    }

    /**
     * Merchant Reply -> Shopper Notification (order-resolution-merchant-reply-shopper)
     */
    public function send_merchant_reply_notification($res, $reply_message)
    {
        $shopper_email = '';
        if (!empty($res->customer_id)) {
            $c_row = $this->db->select('email_id')->where('id', $res->customer_id)->get('customers')->row();
            if ($c_row) {
                $shopper_email = $c_row->email_id;
            }
        }
        if (empty($shopper_email) && !empty($res->order_id)) {
            $so_row = $this->db->select('customer_email')->where('order_id', $res->order_id)->get('sales_order')->row();
            if ($so_row) {
                $shopper_email = $so_row->customer_email;
            }
        }
        if (empty($shopper_email)) {
            return;
        }

        $merchant_name = 'Merchant';
        if (!empty($res->merchant_id)) {
            $m_row = $this->db->select('publication_name')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m_row) {
                $merchant_name = $m_row->publication_name;
            }
        }

        $product_name = 'N/A';
        if (!empty($res->product_id)) {
            $p_row = $this->db->select('name')->where('id', $res->product_id)->get('products')->row();
            if ($p_row) {
                $product_name = html_entity_decode($p_row->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $shopper_url = base_url('order_resolution/view/' . $res->ticket_number);

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##PRODUCT_NAME##', '{product_name}',
            '##REPLY_MESSAGE##', '##REPLY_TEXT##', '##REPLAY_TEXT##', '{reply_message}', '{reply_text}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $merchant_name, $merchant_name,
            $product_name, $product_name,
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            $shopper_url, $shopper_url,
            'Yellow Markets', 'Yellow Markets'
        ];

        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-merchant-reply-shopper', $tempVars, $dynamicVars);
    }

    /**
     * Shopper Reply -> Merchant Notification (order-resolution-shopper-reply-merchant)
     */
    public function send_shopper_reply_notification($res, $reply_message)
    {
        $merchant_email = '';
        $merchant_name  = 'Merchant';
        if (!empty($res->merchant_id)) {
            $m_row = $this->db->select('publication_name, email')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m_row) {
                $merchant_name  = $m_row->publication_name;
                $merchant_email = $m_row->email;
            }
        }
        if (empty($merchant_email)) {
            return;
        }

        $shopper_name = 'Shopper';
        if (!empty($res->customer_id)) {
            $c_row = $this->db->select('first_name, last_name')->where('id', $res->customer_id)->get('customers')->row();
            if ($c_row) {
                $shopper_name = trim(($c_row->first_name ?? '') . ' ' . ($c_row->last_name ?? '')) ?: 'Shopper';
            }
        }

        $product_name = 'N/A';
        if (!empty($res->product_id)) {
            $p_row = $this->db->select('name')->where('id', $res->product_id)->get('products')->row();
            if ($p_row) {
                $product_name = html_entity_decode($p_row->name, ENT_QUOTES, 'UTF-8');
            }
        }

        $merchant_url = (defined('BASE_URL2') ? rtrim(BASE_URL2, '/') . '/' : base_url('merchant/')) . 'order_resolution/view/' . $res->ticket_number;

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##SHOPPER_NAME##', '{shopper_name}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##PRODUCT_NAME##', '{product_name}',
            '##REPLY_MESSAGE##', '##REPLY_TEXT##', '##REPLAY_TEXT##', '{reply_message}', '{reply_text}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $shopper_name, $shopper_name,
            $merchant_name, $merchant_name,
            $product_name, $product_name,
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            nl2br(htmlspecialchars($reply_message, ENT_QUOTES, 'UTF-8')),
            $merchant_url, $merchant_url,
            'Yellow Markets', 'Yellow Markets'
        ];

        $this->CommonModel->sendCommonHTMLEmail($merchant_email, 'order-resolution-shopper-reply-merchant', $tempVars, $dynamicVars);
    }

    /**
     * Merchant Button Action -> @Help Notification (merchant-ticket-button-action)
     */
    public function send_merchant_action_email_to_help($res, $action_name)
    {
        $help_email = $this->CommonModel->get_custom_variable('contact_us_email')
            ?: ($this->CommonModel->get_custom_variable('admin_email') ?: 'help@yellowmarkets.com');
        if (empty($help_email)) {
            return;
        }

        $merchant_name = 'Merchant';
        if (!empty($res->merchant_id)) {
            $m_row = $this->db->select('publication_name')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m_row) {
                $merchant_name = $m_row->publication_name;
            }
        }

        $admin_url = base_url('admin/order_resolution/view/' . $res->ticket_number);
        $action_dt = date('d M Y, h:i A');

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##ACTION##', '{action}',
            '##ACTION_DATE_TIME##', '{action_date_time}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $merchant_name, $merchant_name,
            $action_name, $action_name,
            $action_dt, $action_dt,
            $admin_url, $admin_url,
            'Yellow Markets', 'Yellow Markets'
        ];

        $this->CommonModel->sendCommonHTMLEmail($help_email, 'merchant-ticket-button-action', $tempVars, $dynamicVars);
    }

    /**
     * Process Merchant Decision Actions
     */
    public function process_merchant_action($ticket_number, $merchant_id, $action, $extra = [])
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->merchant_id !== (int)$merchant_id) {
            return ['status' => false, 'message' => 'Invalid ticket or unauthorized merchant.'];
        }

        $time = time();
        $update = ['updated_at' => $time];

        switch ($action) {
            case 'refund_approved':
                $refund_amount = (float)($extra['refund_amount'] ?? 0);
                $update['merchant_action'] = 'refund_approved';
                $update['refund_amount']   = $refund_amount;
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Refund Approved', 'merchant', $merchant_id, "Approved refund of {$refund_amount}");

                // 1. Notify @Help of button action
                $this->send_merchant_action_email_to_help($res, 'Refund Approved');

                // 2. Email to Shopper: order-resolution-refund-approved-shopper
                $this->send_refund_approved_shopper_email($res, $refund_amount);

                // 3. Email to @Help: order-resolution-refund-approved-help
                $this->send_refund_approved_help_email($res, $refund_amount);
                return ['status' => true, 'message' => 'Refund approved successfully.'];

            case 'refund_denied':
                $update['merchant_action'] = 'refund_denied';
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Refund Denied', 'merchant', $merchant_id, 'Merchant denied refund.');
                $this->send_merchant_action_email_to_help($res, 'Refund Denied');
                $this->send_refund_denied_shopper_email($res);
                $this->send_refund_denied_help_email($res);
                return ['status' => true, 'message' => 'Refund denied.'];

            case 'replacement_approved':
                $delivery_option = $extra['delivery_option'] ?? '';
                if (!in_array($delivery_option, ['own_delivery', 'self_pickup', 'ym_delivery'], true)) {
                    return ['status' => false, 'message' => 'Please select a valid delivery option.'];
                }
                $update['merchant_action'] = 'replacement_approved';
                $update['delivery_option'] = $delivery_option;
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                // Update order item status to 18 (Replacement Approved)
                $this->update_order_item_status($res->order_id, $res->product_id, $res->merchant_id, 18);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Approved', 'merchant', $merchant_id, "Selected delivery: {$delivery_option}");

                // Notify @Help of button action
                $this->send_merchant_action_email_to_help($res, 'Replacement Approved');

                if ($delivery_option === 'ym_delivery') {
                    // Send order-resolution-replacement-ym-delivery-approved-help
                    $this->send_replacement_ym_delivery_help_email($res);
                } else {
                    // Send order-resolution-replacement-underway-shopper
                    $this->send_replacement_underway_shopper_email($res, $delivery_option);
                }
                return ['status' => true, 'message' => 'Replacement approved successfully.'];

            case 'replacement_denied':
                $update['merchant_action'] = 'replacement_denied';
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                // Update order item status to 21 (Replacement Rejected)
                $this->update_order_item_status($res->order_id, $res->product_id, $res->merchant_id, 21);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Denied', 'merchant', $merchant_id, 'Merchant denied replacement.');
                $this->send_merchant_action_email_to_help($res, 'Replacement Denied');
                $this->send_replacement_denied_shopper_email($res);
                $this->send_replacement_denied_help_email($res);
                return ['status' => true, 'message' => 'Replacement denied.'];

            case 'replacement_completed':
                if ($res->merchant_action !== 'replacement_approved' || $res->delivery_option === 'none') {
                    return ['status' => false, 'message' => 'Replacement must be approved with a delivery option first.'];
                }
                $update['merchant_action'] = 'replacement_completed';
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                // Update order item status to 19 (Replaced)
                $this->update_order_item_status($res->order_id, $res->product_id, $res->merchant_id, 19);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Completed', 'merchant', $merchant_id, 'Merchant marked replacement completed.');
                $this->send_merchant_action_email_to_help($res, 'Replacement Completed');
                return ['status' => true, 'message' => 'Replacement marked as completed.'];

            default:
                return ['status' => false, 'message' => 'Unknown action.'];
        }
    }

    /**
     * Shopper clicks "Resolution Request" -> status becomes 'ReOpen' and resolution_status = 'resolution_requested'
     */
    public function shopper_resolution_request($ticket_number, $customer_id, $message = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res || (int)$res->customer_id !== (int)$customer_id) {
            return ['status' => false, 'message' => 'Invalid ticket or unauthorized customer.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'                  => 'ReOpen',
            'resolution_status'       => 'resolution_requested',
            'resolution_requested_at' => $time,
            'is_active_dispute'       => 1,
            'updated_at'              => $time,
        ]);

        if (!empty($message)) {
            $this->add_reply($ticket_number, 'shopper', $customer_id, 'Shopper', $message);
        }

        $this->audit_log($res->id, $ticket_number, $res->status, 'ReOpen', 'Resolution Request', 'shopper', $customer_id, 'Shopper requested resolution escalation.');

        // Section 15: Notify @Help and Merchant
        $this->send_dispute_help_email($res, $message);
        $this->send_dispute_merchant_email($res, $message);

        return ['status' => true, 'message' => 'Resolution request submitted successfully.'];
    }

    /**
     * @Help assigns ticket to @Acct -> status becomes 'Processing'
     */
    public function admin_assign_to_acct($ticket_number, $admin_id, $notes = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'        => 'Processing',
            'assigned_role' => 'Acct',
            'assigned_to'   => $admin_id,
            'updated_at'    => $time,
        ]);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Processing', 'Assigned to @acct', 'admin', $admin_id, $notes);

        // 1. Email to Merchant: order-resolution-refund-initiated-merchant
        $this->send_refund_initiated_merchant_email($res);

        // 2. Email to @Acct: order-resolution-refund-request-acct (or YM delivery acct email)
        if ($res->delivery_option === 'ym_delivery') {
            $this->send_ym_delivery_acct_email($res);
        } else {
            $this->send_refund_acct_email($res);
        }

        return ['status' => true, 'message' => 'Ticket assigned to Accounting successfully.'];
    }

    /**
     * @Acct marks refund/payment completed -> status becomes 'Done'
     */
    public function admin_mark_done($ticket_number, $admin_id, $notes = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'     => 'Done',
            'updated_at' => $time,
        ]);

        // Update order status to 17 (Refund Paid) if refund
        if ($res->merchant_action === 'refund_approved' || strtolower($res->category) === 'refund' || $res->resolution_status === 'resolution_approved') {
            $this->update_order_item_status($res->order_id, $res->product_id, $res->merchant_id, 17);
        }

        $this->audit_log($res->id, $ticket_number, $res->status, 'Done', 'Marked Done', 'acct', $admin_id, $notes);

        // Email to Shopper: order-resolution-refund-completed-shopper (or YM delivery shopper completed)
        if ($res->delivery_option === 'ym_delivery') {
            $this->send_ym_delivery_completed_shopper_email($res);
        } else {
            $this->send_refund_completed_shopper_email($res);
        }

        return ['status' => true, 'message' => 'Ticket marked as Done.'];
    }

    /**
     * @Help closes ticket where Shopper has a Resolution Option -> status becomes 'Close'
     */
    public function admin_close_ticket($ticket_number, $admin_id, $notes = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'     => 'Close',
            'closed_at'  => $time,
            'closed_by'  => 'admin',
            'updated_at' => $time,
        ]);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Close', 'Ticket Closed', 'admin', $admin_id, $notes);

        // Email to Merchant: order-resolution-ticket-closed-merchant
        $this->send_ticket_closed_merchant_email($res);

        // If replacement was completed, notify shopper
        if ($res->merchant_action === 'replacement_completed') {
            $this->send_replacement_completed_shopper_email($res);
        }

        return ['status' => true, 'message' => 'Ticket closed successfully.'];
    }

    /**
     * @Help finally closes a Resolution Request with no further options -> status becomes 'Close (Final)'
     */
    public function admin_close_final($ticket_number, $admin_id, $notes = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'            => 'Close (Final)',
            'is_active_dispute' => 0,
            'closed_at'         => $time,
            'closed_by'         => 'admin',
            'updated_at'        => $time,
        ]);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Close (Final)', 'Ticket Closed (Final)', 'admin', $admin_id, $notes);

        // Section 16 & 17: Email to Merchant: Closed (Final)
        $this->send_closed_final_merchant_email($res);

        return ['status' => true, 'message' => 'Ticket finally closed with no further options.'];
    }

    /**
     * @Help Resolution Decision (Resolution Approved / Resolution Denied)
     */
    public function admin_resolution_decision($ticket_number, $decision, $admin_id, $notes = '')
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        $time = time();

        if ($decision === 'approved') {
            // Section 16: Decision in Favour of Shopper -> Assign to @Acct (Status: Processing)
            $this->db->where('id', $res->id)->update('order_resolutions', [
                'status'            => 'Processing',
                'assigned_role'     => 'Acct',
                'resolution_status' => 'resolution_approved',
                'updated_at'        => $time,
            ]);

            $this->audit_log($res->id, $ticket_number, $res->status, 'Processing', 'Resolution Approved (In Favour of Shopper)', 'admin', $admin_id, $notes);

            // Email to @Acct: Refund Request
            $this->send_refund_acct_email($res);

            return ['status' => true, 'message' => 'Resolution decision approved in favour of shopper and assigned to Accounting.'];
        } else {
            // Section 17: Decision in Favour of Merchant -> Status: Close (Final)
            $this->db->where('id', $res->id)->update('order_resolutions', [
                'status'            => 'Close (Final)',
                'resolution_status' => 'resolution_denied',
                'is_active_dispute' => 0,
                'closed_at'         => $time,
                'closed_by'         => 'admin',
                'updated_at'        => $time,
            ]);

            $this->audit_log($res->id, $ticket_number, $res->status, 'Close (Final)', 'Resolution Denied (In Favour of Merchant)', 'admin', $admin_id, $notes);

            // Merchant receives: Order Resolution No.: [TicketNumber] - Closed (Final)
            $this->send_closed_final_merchant_email($res);

            // Shopper receives: Order Resolution No.: [TicketNumber] - Refund Disapproved
            $this->send_refund_disapproved_shopper_email($res);

            return ['status' => true, 'message' => 'Resolution decision recorded in favour of merchant and closed (Final).'];
        }
    }

    // =========================================================================
    // HELPER EMAIL SENDERS
    // =========================================================================

    private function send_refund_approved_shopper_email($res, $amount)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $merchant_name = $this->get_merchant_name($res);
        $shopper_name  = $this->get_shopper_name($res);
        $product_name  = $this->get_product_name($res);
        $ticket_url    = base_url('order_resolution/view/' . $res->ticket_number);

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##SHOPPER_NAME##', '{shopper_name}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##PRODUCT_NAME##', '{product_name}',
            '##REFUND_AMOUNT##', '{refund_amount}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $shopper_name, $shopper_name,
            $merchant_name, $merchant_name,
            $product_name, $product_name,
            CURRENCY_TYPE . ' ' . number_format($amount, 2), CURRENCY_TYPE . ' ' . number_format($amount, 2),
            $ticket_url, $ticket_url,
            'Yellow Markets', 'Yellow Markets'
        ];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-refund-approved-shopper', $tempVars, $dynamicVars);
    }

    private function send_refund_approved_help_email($res, $amount)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $merchant_name = $this->get_merchant_name($res);
        $shopper_name  = $this->get_shopper_name($res);
        $product_name  = $this->get_product_name($res);
        $admin_url     = base_url('admin/order_resolution/view/' . $res->ticket_number);

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##SHOPPER_NAME##', '{shopper_name}',
            '##PRODUCT_NAME##', '{product_name}',
            '##REFUND_AMOUNT##', '{refund_amount}',
            '##ACTION##', '{action}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $merchant_name, $merchant_name,
            $shopper_name, $shopper_name,
            $product_name, $product_name,
            CURRENCY_TYPE . ' ' . number_format($amount, 2), CURRENCY_TYPE . ' ' . number_format($amount, 2),
            'Refund Approved', 'Refund Approved',
            $admin_url, $admin_url,
            'Yellow Markets', 'Yellow Markets'
        ];
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-refund-approved-help', $tempVars, $dynamicVars);
    }

    private function send_replacement_underway_shopper_email($res, $method)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $method_title = ucwords(str_replace('_', ' ', $method));
        $shopper_name = $this->get_shopper_name($res);
        $ticket_url   = base_url('order_resolution/view/' . $res->ticket_number);

        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##SHOPPER_NAME##', '{shopper_name}',
            '##REPLACEMENT_METHOD##', '{replacement_method}',
            '##DELIVERY_METHOD##', '{delivery_method}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $shopper_name, $shopper_name,
            $method_title, $method_title,
            $method_title, $method_title,
            $ticket_url, $ticket_url,
            'Yellow Markets', 'Yellow Markets'
        ];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-underway-shopper', $tempVars, $dynamicVars);
    }

    private function send_replacement_ym_delivery_help_email($res)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $admin_url = base_url('admin/order_resolution/view/' . $res->ticket_number);
        $tempVars = [
            '##TICKET_NUMBER##', '{ticket_number}',
            '##ORDER_NUMBER##', '{order_number}',
            '##MERCHANT_NAME##', '{merchant_name}',
            '##SHOPPER_NAME##', '{shopper_name}',
            '##PRODUCT_NAME##', '{product_name}',
            '##DELIVERY_METHOD##', '{delivery_method}',
            '##ACTION##', '{action}',
            '##TICKET_URL##', '{ticket_url}',
            '##WEBSHOPNAME##', '{webshop_name}'
        ];
        $dynamicVars = [
            $res->ticket_number, $res->ticket_number,
            $res->order_number, $res->order_number,
            $this->get_merchant_name($res), $this->get_merchant_name($res),
            $this->get_shopper_name($res), $this->get_shopper_name($res),
            $this->get_product_name($res), $this->get_product_name($res),
            'YM Delivery Service', 'YM Delivery Service',
            'Replacement Approved', 'Replacement Approved',
            $admin_url, $admin_url,
            'Yellow Markets', 'Yellow Markets'
        ];
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-replacement-ym-delivery-approved-help', $tempVars, $dynamicVars);
    }

    private function send_refund_initiated_merchant_email($res)
    {
        $m_email = $this->get_merchant_email($res);
        if (!$m_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##MERCHANT_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_merchant_name($res), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($m_email, 'order-resolution-refund-initiated-merchant', $tempVars, $dynamicVars);
    }

    private function send_refund_acct_email($res)
    {
        $acct_email = $this->CommonModel->get_custom_variable('account_email') ?: 'acct@yellowmarkets.com';
        $admin_url  = base_url('admin/order_resolution/view/' . $res->ticket_number);

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##REFUND_AMOUNT##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, CURRENCY_TYPE . ' ' . number_format($res->refund_amount, 2), $admin_url, 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($acct_email, 'order-resolution-refund-request-acct', $tempVars, $dynamicVars);
    }

    private function send_ym_delivery_acct_email($res)
    {
        $acct_email = $this->CommonModel->get_custom_variable('account_email') ?: 'acct@yellowmarkets.com';
        $admin_url  = base_url('admin/order_resolution/view/' . $res->ticket_number);

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $admin_url, 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($acct_email, 'order-resolution-replacement-ym-delivery-request-acct', $tempVars, $dynamicVars);
    }

    private function send_refund_completed_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##REFUND_AMOUNT##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), CURRENCY_TYPE . ' ' . number_format($res->refund_amount, 2), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-refund-completed-shopper', $tempVars, $dynamicVars);
    }

    private function send_ym_delivery_completed_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-ym-delivery-completed-shopper', $tempVars, $dynamicVars);
    }

    private function send_ticket_closed_merchant_email($res)
    {
        $m_email = $this->get_merchant_email($res);
        if (!$m_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##MERCHANT_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_merchant_name($res), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($m_email, 'order-resolution-ticket-closed-merchant', $tempVars, $dynamicVars);
    }

    private function send_refund_denied_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-refund-denied-shopper', $tempVars, $dynamicVars);
    }

    private function send_refund_denied_help_email($res)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, base_url('admin/order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-refund-denied-help', $tempVars, $dynamicVars);
    }

    private function send_replacement_denied_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-denied-shopper', $tempVars, $dynamicVars);
    }

    private function send_replacement_denied_help_email($res)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, base_url('admin/order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-replacement-denied-help', $tempVars, $dynamicVars);
    }

    private function send_replacement_completed_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-completed-shopper', $tempVars, $dynamicVars);
    }

    private function send_dispute_help_email($res, $message = '')
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_MESSAGE##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')), base_url('admin/order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-dispute-help', $tempVars, $dynamicVars);
    }

    private function send_dispute_merchant_email($res, $message = '')
    {
        $m_email = $this->get_merchant_email($res);
        if (!$m_email) return;

        $merchant_url = (defined('BASE_URL2') ? rtrim(BASE_URL2, '/') . '/' : base_url('merchant/')) . 'order_resolution/view/' . $res->ticket_number;
        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##MERCHANT_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_merchant_name($res), $merchant_url, 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($m_email, 'order-resolution-dispute-merchant', $tempVars, $dynamicVars);
    }

    private function send_refund_disapproved_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-refund-disapproved-shopper', $tempVars, $dynamicVars);
    }

    private function send_closed_final_merchant_email($res)
    {
        $m_email = $this->get_merchant_email($res);
        if (!$m_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##MERCHANT_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_merchant_name($res), 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($m_email, 'order-resolution-closed-final-merchant', $tempVars, $dynamicVars);
    }

    // =========================================================================
    // GETTERS & AUDIT
    // =========================================================================

    public function get_resolution_by_ticket_number($ticket_number)
    {
        return $this->db->where('ticket_number', $ticket_number)->get('order_resolutions')->row();
    }

    public function get_messages($resolution_id)
    {
        return $this->db->where('resolution_id', $resolution_id)->order_by('created_at', 'ASC')->get('order_resolution_messages')->result();
    }

    public function get_audit_log($resolution_id)
    {
        return $this->db->where('resolution_id', $resolution_id)->order_by('created_at', 'ASC')->get('order_resolution_audit_log')->result();
    }

    public function audit_log($resolution_id, $ticket_number, $from_status, $to_status, $action, $actor_role, $actor_id, $notes = '')
    {
        return $this->db->insert('order_resolution_audit_log', [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'from_status'   => $from_status,
            'to_status'     => $to_status,
            'action'        => $action,
            'actor_role'    => $actor_role,
            'actor_id'      => $actor_id,
            'notes'         => $notes,
            'created_at'    => time(),
        ]);
    }

    private function get_shopper_email($res)
    {
        if (!empty($res->customer_id)) {
            $c = $this->db->select('email_id')->where('id', $res->customer_id)->get('customers')->row();
            if ($c && !empty($c->email_id)) return $c->email_id;
        }
        if (!empty($res->order_id)) {
            $o = $this->db->select('customer_email')->where('order_id', $res->order_id)->get('sales_order')->row();
            if ($o && !empty($o->customer_email)) return $o->customer_email;
        }
        return '';
    }

    private function get_shopper_name($res)
    {
        if (!empty($res->customer_id)) {
            $c = $this->db->select('first_name, last_name')->where('id', $res->customer_id)->get('customers')->row();
            if ($c) return trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: 'Shopper';
        }
        return 'Shopper';
    }

    private function get_merchant_name($res)
    {
        if (!empty($res->merchant_id)) {
            $m = $this->db->select('publication_name')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m && !empty($m->publication_name)) return $m->publication_name;
        }
        return 'Merchant';
    }

    private function get_merchant_email($res)
    {
        if (!empty($res->merchant_id)) {
            $m = $this->db->select('email')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m && !empty($m->email)) return $m->email;
        }
        return '';
    }

    private function get_product_name($res)
    {
        if (!empty($res->product_id)) {
            $p = $this->db->select('name')->where('id', $res->product_id)->get('products')->row();
            if ($p && !empty($p->name)) return html_entity_decode($p->name, ENT_QUOTES, 'UTF-8');
        }
        return 'N/A';
    }

    private function get_help_email()
    {
        return $this->CommonModel->get_custom_variable('contact_us_email')
            ?: ($this->CommonModel->get_custom_variable('admin_email') ?: 'help@yellowmarkets.com');
    }
}