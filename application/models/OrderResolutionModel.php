<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * OrderResolutionModel
 * 
 * Core Data Model for Order Resolution & Ticket Management
 * Yellow Markets Platform
 */
class OrderResolutionModel extends CI_Model
{
    protected static $schema_checked = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Auto-ensure database schema exists for zero-setup execution
     */
    public function ensure_schema()
    {
        if (self::$schema_checked) {
            return;
        }

        try {
            // Check if column status_code exists on help_desk
            $fields = $this->db->list_fields('help_desk');
            if (!in_array('status_code', $fields)) {
                $this->db->query("ALTER TABLE `help_desk`
                    ADD COLUMN `order_item_id` int(11) DEFAULT NULL COMMENT 'sales_order_items.item_id' AFTER `order_id`,
                    ADD COLUMN `b2b_order_id` int(11) DEFAULT NULL COMMENT 'b2b_orders.order_id' AFTER `order_item_id`,
                    ADD COLUMN `status_code` enum('Open','Processing','Done','Close','ReOpen','Close (Final)') NOT NULL DEFAULT 'Open' AFTER `status`,
                    ADD COLUMN `assigned_role` enum('Admin','Account') DEFAULT 'Admin' AFTER `status_code`,
                    ADD COLUMN `assigned_to` int(11) DEFAULT NULL COMMENT 'adminusers.id' AFTER `assigned_role`,
                    ADD COLUMN `merchant_action` enum('none','refund_approved','refund_denied','replacement_approved','replacement_denied','replacement_completed') DEFAULT 'none' AFTER `assigned_to`,
                    ADD COLUMN `delivery_option` enum('none','own_delivery','self_pickup','ym_delivery') DEFAULT 'none' AFTER `merchant_action`,
                    ADD COLUMN `addon_purchase_id` int(11) DEFAULT NULL COMMENT 'merchant_addon_purchases.id if ym_delivery' AFTER `delivery_option`,
                    ADD COLUMN `refund_amount` decimal(12,2) DEFAULT 0.00 AFTER `addon_purchase_id`,
                    ADD COLUMN `refund_deducted_from_holdback` tinyint(1) DEFAULT 0 COMMENT '1 if deducted from 15-day holdback' AFTER `refund_amount`,
                    ADD COLUMN `resolution_status` enum('none','resolution_requested','resolution_approved','resolution_denied') DEFAULT 'none' AFTER `refund_deducted_from_holdback`,
                    ADD COLUMN `resolution_requested_at` int(11) DEFAULT NULL AFTER `resolution_status`,
                    ADD COLUMN `closed_at` int(11) DEFAULT NULL AFTER `resolution_requested_at`,
                    ADD COLUMN `closed_by` varchar(50) DEFAULT NULL AFTER `closed_at`,
                    ADD COLUMN `is_active_dispute` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Blocks Payout, 0 = Normal Payout' AFTER `closed_by`");
            }

            if (!in_array('assigned_at', $fields)) {
                $this->db->query("ALTER TABLE `help_desk`
                    ADD COLUMN `assigned_at` int(11) DEFAULT NULL AFTER `assigned_to`,
                    ADD COLUMN `refund_reference` varchar(100) DEFAULT NULL AFTER `refund_deducted_from_holdback`,
                    ADD COLUMN `refund_completed_at` int(11) DEFAULT NULL AFTER `refund_reference`,
                    ADD COLUMN `refund_completed_by` int(11) DEFAULT NULL AFTER `refund_completed_at`");
            }

            // Create help_desk_messages if not exists
            if (!$this->db->table_exists('help_desk_messages')) {
                $this->db->query("CREATE TABLE IF NOT EXISTS `help_desk_messages` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `ticket_id` varchar(50) NOT NULL,
                    `help_desk_id` int(11) NOT NULL,
                    `sender_role` enum('shopper','merchant','admin','account') NOT NULL,
                    `sender_id` int(11) NOT NULL,
                    `sender_name` varchar(255) DEFAULT NULL,
                    `message` text NOT NULL,
                    `attachment` varchar(255) DEFAULT NULL,
                    `created_at` int(11) NOT NULL,
                    `ip` varchar(50) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_ticket_id` (`ticket_id`),
                    KEY `idx_help_desk_id` (`help_desk_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }

            // Create help_desk_audit_log if not exists
            if (!$this->db->table_exists('help_desk_audit_log')) {
                $this->db->query("CREATE TABLE IF NOT EXISTS `help_desk_audit_log` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `ticket_id` varchar(50) NOT NULL,
                    `from_status` varchar(30) NOT NULL,
                    `to_status` varchar(30) NOT NULL,
                    `action` varchar(100) NOT NULL,
                    `actor_role` varchar(30) NOT NULL,
                    `actor_id` int(11) NOT NULL,
                    `notes` text DEFAULT NULL,
                    `created_at` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_ticket_audit` (`ticket_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }

            self::$schema_checked = true;
        } catch (\Exception $e) {
            log_message('error', 'OrderResolutionModel schema check error: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique Ticket Number: RES-[INCREMENT_ID]-[TIMESTAMP]-[RAND4]
     */
    public function generate_ticket_number($order_increment_id = 'ORD')
    {
        $inc = preg_replace('/[^A-Za-z0-9]/', '', (string)$order_increment_id);
        if (empty($inc)) {
            $inc = 'ORD';
        }
        $rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return 'RES-' . $inc . '-' . date('Ymd') . '-' . $rand;
    }

    /**
     * Create a new Shopper ticket
     */
    public function create_ticket(array $data)
    {
        $this->db->trans_start();

        $ticket_id = $this->generate_ticket_number($data['order_increment_id'] ?? 'ORD');

        $ticket_data = [
            'ticket_id'                 => $ticket_id,
            'merchant_id'               => (int)$data['merchant_id'],
            'subject'                   => trim($data['subject'] ?? ('Order Resolution #' . ($data['order_increment_id'] ?? ''))),
            'category'                  => trim($data['category']), // Delivery, Refund, Replacement, Others
            'priority'                  => trim($data['priority']), // Low, Medium, High, Urgent
            'customer_id'               => (int)$data['customer_id'],
            'message'                   => trim($data['message']),
            'attachment'                => $data['attachment'] ?? null,
            'order_id'                  => (int)$data['order_id'],
            'order_item_id'             => !empty($data['order_item_id']) ? (int)$data['order_item_id'] : null,
            'b2b_order_id'              => !empty($data['b2b_order_id']) ? (int)$data['b2b_order_id'] : null,
            'products'                  => (string)($data['product_id'] ?? ''),
            'status'                    => 1, // backward compat: 1 = Open
            'status_code'               => 'Open',
            'assigned_role'             => 'Admin',
            'merchant_action'           => 'none',
            'delivery_option'           => 'none',
            'is_active_dispute'         => 1,
            'created_at'                => time(),
            'updated_at'                => time(),
            'ip'                        => $this->input->ip_address()
        ];

        $this->db->insert('help_desk', $ticket_data);
        $help_desk_id = $this->db->insert_id();

        // Also insert initial message in messages table
        $this->db->insert('help_desk_messages', [
            'ticket_id'     => $ticket_id,
            'help_desk_id'  => $help_desk_id,
            'sender_role'   => 'shopper',
            'sender_id'     => (int)$data['customer_id'],
            'sender_name'   => $data['customer_name'] ?? 'Shopper',
            'message'       => trim($data['message']),
            'attachment'    => $data['attachment'] ?? null,
            'created_at'    => time(),
            'ip'            => $this->input->ip_address()
        ]);

        // Audit Log
        $this->log_audit($ticket_id, 'None', 'Open', 'Shopper Created Ticket', 'shopper', (int)$data['customer_id']);

        // Hold payout for this sub-order if b2b_order_id exists
        if (!empty($data['b2b_order_id'])) {
            $this->db->where('order_id', (int)$data['b2b_order_id'])
                ->where('payout_status !=', 4) // Don't alter if already Paid
                ->update('b2b_orders', ['payout_status' => 3]); // 3 = On Hold
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $ticket_id;
    }

    /**
     * Add conversation message
     */
    public function add_message($ticket_id, $sender_role, $sender_id, $sender_name, $message, $attachment = null)
    {
        $ticket = $this->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return false;
        }

        $msg_data = [
            'ticket_id'     => $ticket_id,
            'help_desk_id'  => (int)$ticket->id,
            'sender_role'   => $sender_role,
            'sender_id'     => (int)$sender_id,
            'sender_name'   => $sender_name,
            'message'       => trim($message),
            'attachment'    => $attachment,
            'created_at'    => time(),
            'ip'            => $this->input->ip_address()
        ];

        $this->db->insert('help_desk_messages', $msg_data);

        // Update ticket updated_at
        $this->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'updated_at' => time()
        ]);

        return true;
    }

    /**
     * Get ticket by ticket_id string
     */
    public function get_ticket_by_number($ticket_id)
    {
        $ticket = $this->db->select('hd.*, 
            so.increment_id as order_increment_id, 
            so.status as main_order_status,
            soi.product_name,
            soi.price as item_price,
            soi.qty_ordered as item_qty,
            u.publication_name as merchant_name,
            c.first_name as customer_first_name,
            c.last_name as customer_last_name,
            c.email as customer_email,
            au.name as assigned_user_name,
            au.email as assigned_user_email')
            ->from('help_desk hd')
            ->join('sales_order so', 'so.order_id = hd.order_id', 'left')
            ->join('sales_order_items soi', 'soi.item_id = hd.order_item_id', 'left')
            ->join('users u', 'u.id = hd.merchant_id', 'left')
            ->join('users c', 'c.id = hd.customer_id', 'left')
            ->join('adminusers au', 'au.id = hd.assigned_to', 'left')
            ->where('hd.ticket_id', $ticket_id)
            ->order_by('hd.id', 'ASC')
            ->get()
            ->row();

        return $ticket;
    }

    /**
     * Get all conversation messages for a ticket
     */
    public function get_messages($ticket_id)
    {
        return $this->db->from('help_desk_messages')
            ->where('ticket_id', $ticket_id)
            ->order_by('created_at', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Audit logger for state changes
     */
    public function log_audit($ticket_id, $from_status, $to_status, $action, $actor_role, $actor_id, $notes = null)
    {
        $this->db->insert('help_desk_audit_log', [
            'ticket_id'   => $ticket_id,
            'from_status' => (string)$from_status,
            'to_status'   => (string)$to_status,
            'action'      => (string)$action,
            'actor_role'  => (string)$actor_role,
            'actor_id'    => (int)$actor_id,
            'notes'       => $notes,
            'created_at'  => time()
        ]);
    }

    /**
     * Check if an active dispute exists for an order or sub-order
     * Returns true if payout must be blocked
     */
    public function has_active_dispute($order_id, $merchant_id = null)
    {
        $this->db->select('id')->from('help_desk')
            ->where('order_id', (int)$order_id)
            ->where_in('status_code', ['Open', 'Processing', 'ReOpen'])
            ->where('is_active_dispute', 1);

        if ($merchant_id !== null) {
            $this->db->where('merchant_id', (int)$merchant_id);
        }

        return ($this->db->count_all_results() > 0);
    }

    /**
     * Check if an item already has an open ticket
     */
    public function get_active_item_ticket($order_item_id)
    {
        return $this->db->from('help_desk')
            ->where('order_item_id', (int)$order_item_id)
            ->where_in('status_code', ['Open', 'Processing', 'ReOpen'])
            ->get()
            ->row();
    }

    /**
     * Get 15-day holdback balance for a merchant
     */
    public function get_merchant_holdback_balance($merchant_id)
    {
        $fifteen_days_ago = strtotime('-15 days');
        $res = $this->db->select('SUM(grand_total) as balance')
            ->from('b2b_orders')
            ->where('merchant_id', (int)$merchant_id)
            ->where_in('payout_status', [1, 3])
            ->where('created_at >=', $fifteen_days_ago)
            ->get()
            ->row();

        return $res && $res->balance ? (float)$res->balance : 0.00;
    }
}
