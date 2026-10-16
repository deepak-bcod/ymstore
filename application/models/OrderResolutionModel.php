<?php
defined('BASEPATH') or exit('No direct script access allowed');
defined('CURRENCY_TYPE') || define('CURRENCY_TYPE', 'MUR');

class OrderResolutionModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('CommonModel');
        $this->ensure_schema();
    }

    /**
     * Ensure database columns for separate conversion workflows exist
     */
    public function ensure_schema()
    {
        try {
            if ($this->db && $this->db->table_exists('order_resolution_messages')) {
                if (!$this->db->field_exists('conversion_type', 'order_resolution_messages')) {
                    $this->db->query("ALTER TABLE `order_resolution_messages` ADD COLUMN `conversion_type` enum('shopper_merchant','shopper_admin','merchant_admin') NOT NULL DEFAULT 'shopper_merchant' AFTER `ticket_number`");
                    $this->db->query("ALTER TABLE `order_resolution_messages` ADD INDEX `idx_msg_conversion` (`ticket_number`, `conversion_type`)");
                    $this->db->query("UPDATE `order_resolution_messages` SET `conversion_type` = 'shopper_admin' WHERE `sender_role` IN ('help', 'acct')");
                }
            }
            if ($this->db && $this->db->table_exists('order_resolutions')) {
                if (!$this->db->field_exists('addon_service_id', 'order_resolutions')) {
                    $this->db->query("ALTER TABLE `order_resolutions` ADD COLUMN `addon_service_id` int(11) DEFAULT NULL AFTER `addon_purchase_id`");
                }
                if (!$this->db->field_exists('refund_amount', 'order_resolutions')) {
                    $this->db->query("ALTER TABLE `order_resolutions` ADD COLUMN `refund_amount` decimal(12,2) NOT NULL DEFAULT '0.00' AFTER `resolution_status`");
                }
            }
            if ($this->db && $this->db->table_exists('order_resolution_audit_log')) {
                if (!$this->db->field_exists('source_panel', 'order_resolution_audit_log')) {
                    $this->db->query("ALTER TABLE `order_resolution_audit_log` ADD COLUMN `source_panel` varchar(50) NOT NULL DEFAULT 'admin' AFTER `actor_id`");
                }
                if (!$this->db->field_exists('conversion_direction', 'order_resolution_audit_log')) {
                    $this->db->query("ALTER TABLE `order_resolution_audit_log` ADD COLUMN `conversion_direction` varchar(50) NOT NULL DEFAULT 'shopper_merchant' AFTER `source_panel`");
                    $this->db->query("ALTER TABLE `order_resolution_audit_log` ADD INDEX `idx_audit_conversion` (`ticket_number`, `conversion_direction`)");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Schema check in OrderResolutionModel: ' . $e->getMessage());
        }
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
     * Fetch customer orders eligible for resolution (Only completed orders - legacy)
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
     * Fetch only items where the merchant has REJECTED a Return or Replacement request.
     * Order Resolution is strictly gated to rejected return/replacement items.
     *
     * @param int $customer_id
     * @return array
     */
    public function get_customer_rejected_items($customer_id)
    {
        $customer_id = (int)$customer_id;
        if ($customer_id <= 0) {
            return [];
        }

        // 1. Get all customer's sales orders
        $orders = $this->db->select('order_id, increment_id, created_at, grand_total')
            ->from('sales_order')
            ->where('customer_id', $customer_id)
            ->order_by('order_id', 'DESC')
            ->get()
            ->result();

        if (empty($orders)) {
            return [];
        }

        $order_map = [];
        $order_ids = [];
        foreach ($orders as $o) {
            $order_map[$o->order_id] = $o;
            $order_ids[] = (int)$o->order_id;
        }

        $eligible_items = [];
        $seen_keys = [];

        // 2. Check b2b_orders and b2b_order_items for status 20 (Return Rejected) or 21 (Replacement Rejected)
        $b2b_items = $this->db->select('boi.item_id as b2b_item_id, boi.order_id as b2b_order_id, boi.product_id, boi.product_name, boi.status as item_status, bo.webshop_order_id, bo.publisher_id, pub.publication_name as merchant_name')
            ->from('b2b_order_items boi')
            ->join('b2b_orders bo', 'bo.order_id = boi.order_id', 'inner')
            ->join('publisher pub', 'pub.id = bo.publisher_id', 'left')
            ->where_in('bo.webshop_order_id', $order_ids)
            ->where_in('boi.status', [20, 21])
            ->get()
            ->result();

        if (!empty($b2b_items)) {
            foreach ($b2b_items as $bi) {
                $key = $bi->webshop_order_id . '_' . $bi->product_id;
                if (isset($seen_keys[$key])) {
                    continue;
                }

                $type = ((int)$bi->item_status === 21) ? 'replacement' : 'return';
                $category = ((int)$bi->item_status === 21) ? 'Replacement' : 'Refund';
                $reason = ((int)$bi->item_status === 21) ? 'Replacement Rejected by Merchant' : 'Return / Refund Rejected by Merchant';

                $o = $order_map[$bi->webshop_order_id] ?? null;
                $seen_keys[$key] = true;
                $eligible_items[] = (object)[
                    'order_id'             => (int)$bi->webshop_order_id,
                    'order_number'         => $o ? ($o->increment_id ?: $o->order_id) : $bi->webshop_order_id,
                    'product_id'           => (int)$bi->product_id,
                    'product_name'         => $bi->product_name ?: $this->get_product_name_by_id($bi->product_id),
                    'merchant_id'          => (int)$bi->publisher_id,
                    'merchant_name'        => $bi->merchant_name ?: 'Merchant',
                    'request_type'         => $type,
                    'category'             => $category,
                    'rejected_status_text' => $reason,
                ];
            }
        }

        // 3. Check sales_order_return for rejected returns (status in [2, 5, 20] or refund_status = 2)
        if ($this->db->table_exists('sales_order_return') && $this->db->table_exists('sales_order_return_items')) {
            $ret_orders = $this->db->select('sor.return_order_id, sor.order_id, sor.status, sor.refund_status, sori.order_item_id')
                ->from('sales_order_return sor')
                ->join('sales_order_return_items sori', 'sori.return_order_id = sor.return_order_id', 'inner')
                ->group_start()
                    ->where_in('sor.order_id', $order_ids)
                ->group_end()
                ->group_start()
                    ->where_in('sor.status', [2, 5, 20])
                    ->or_where('sor.refund_status', 2)
                    ->or_where_in('sori.status', [2, 20])
                ->group_end()
                ->get()
                ->result();

            if (!empty($ret_orders)) {
                foreach ($ret_orders as $ro) {
                    $webshop_id = (int)$ro->order_id;
                    if (!isset($order_map[$webshop_id])) {
                        $b2b = $this->db->select('webshop_order_id')->where('order_id', $webshop_id)->get('b2b_orders')->row();
                        if ($b2b && isset($order_map[$b2b->webshop_order_id])) {
                            $webshop_id = (int)$b2b->webshop_order_id;
                        } else {
                            continue;
                        }
                    }

                    $prod_id = 0;
                    if (!empty($ro->order_item_id)) {
                        $soi = $this->db->select('product_id')->where('item_id', $ro->order_item_id)->get('sales_order_items')->row();
                        if ($soi) {
                            $prod_id = (int)$soi->product_id;
                        } else {
                            $boi = $this->db->select('product_id')->where('item_id', $ro->order_item_id)->get('b2b_order_items')->row();
                            if ($boi) {
                                $prod_id = (int)$boi->product_id;
                            }
                        }
                    }

                    $key = $webshop_id . '_' . $prod_id;
                    if (isset($seen_keys[$key])) {
                        continue;
                    }

                    $o = $order_map[$webshop_id] ?? null;
                    $seen_keys[$key] = true;
                    $eligible_items[] = (object)[
                        'order_id'             => $webshop_id,
                        'order_number'         => $o ? ($o->increment_id ?: $o->order_id) : $webshop_id,
                        'product_id'           => $prod_id,
                        'product_name'         => $this->get_product_name_by_id($prod_id),
                        'merchant_id'          => $this->get_merchant_id_for_item($webshop_id, $prod_id),
                        'merchant_name'        => $this->get_merchant_name_for_item($webshop_id, $prod_id),
                        'request_type'         => 'return',
                        'category'             => 'Refund',
                        'rejected_status_text' => 'Return / Refund Rejected by Merchant',
                    ];
                }
            }
        }

        // 4. Check sales_order_replacement for rejected replacements (status == 4 or items status == 4)
        if ($this->db->table_exists('sales_order_replacement') && $this->db->table_exists('sales_order_replacement_items')) {
            $rep_orders = $this->db->select('sor.replacement_order_id, sor.order_id, sor.status, sori.order_item_id')
                ->from('sales_order_replacement sor')
                ->join('sales_order_replacement_items sori', 'sori.replacement_order_id = sor.replacement_order_id', 'inner')
                ->group_start()
                    ->where_in('sor.order_id', $order_ids)
                ->group_end()
                ->group_start()
                    ->where('sor.status', 4)
                    ->or_where('sori.status', 4)
                ->group_end()
                ->get()
                ->result();

            if (!empty($rep_orders)) {
                foreach ($rep_orders as $rpo) {
                    $webshop_id = (int)$rpo->order_id;
                    if (!isset($order_map[$webshop_id])) {
                        $b2b = $this->db->select('webshop_order_id')->where('order_id', $webshop_id)->get('b2b_orders')->row();
                        if ($b2b && isset($order_map[$b2b->webshop_order_id])) {
                            $webshop_id = (int)$b2b->webshop_order_id;
                        } else {
                            continue;
                        }
                    }

                    $prod_id = 0;
                    if (!empty($rpo->order_item_id)) {
                        $soi = $this->db->select('product_id')->where('item_id', $rpo->order_item_id)->get('sales_order_items')->row();
                        if ($soi) {
                            $prod_id = (int)$soi->product_id;
                        } else {
                            $boi = $this->db->select('product_id')->where('item_id', $rpo->order_item_id)->get('b2b_order_items')->row();
                            if ($boi) {
                                $prod_id = (int)$boi->product_id;
                            }
                        }
                    }

                    $key = $webshop_id . '_' . $prod_id;
                    if (isset($seen_keys[$key])) {
                        continue;
                    }

                    $o = $order_map[$webshop_id] ?? null;
                    $seen_keys[$key] = true;
                    $eligible_items[] = (object)[
                        'order_id'             => $webshop_id,
                        'order_number'         => $o ? ($o->increment_id ?: $o->order_id) : $webshop_id,
                        'product_id'           => $prod_id,
                        'product_name'         => $this->get_product_name_by_id($prod_id),
                        'merchant_id'          => $this->get_merchant_id_for_item($webshop_id, $prod_id),
                        'merchant_name'        => $this->get_merchant_name_for_item($webshop_id, $prod_id),
                        'request_type'         => 'replacement',
                        'category'             => 'Replacement',
                        'rejected_status_text' => 'Replacement Rejected by Merchant',
                    ];
                }
            }
        }

        // 5. Filter out items that already have an active/open Order Resolution ticket
        $final_eligible = [];
        foreach ($eligible_items as $item) {
            $active_ticket = $this->get_active_resolution_for_item($item->order_id, $item->product_id);
            if (!$active_ticket) {
                $final_eligible[] = $item;
            }
        }

        return $final_eligible;
    }

    /**
     * Check if a specific order and product is eligible for resolution (has a rejected request)
     *
     * @param int $order_id
     * @param int $product_id
     * @param int $customer_id
     * @return object|false
     */
    public function is_item_eligible_for_resolution($order_id, $product_id, $customer_id)
    {
        $rejected_items = $this->get_customer_rejected_items($customer_id);
        foreach ($rejected_items as $item) {
            if ((int)$item->order_id === (int)$order_id) {
                if (empty($product_id) || (int)$item->product_id === (int)$product_id) {
                    return $item;
                }
            }
        }
        return false;
    }

    /**
     * Get active resolution ticket for an order item (Open, Processing, ReOpen, Done)
     *
     * @param int $order_id
     * @param int $product_id
     * @return object|null
     */
    public function get_active_resolution_for_item($order_id, $product_id = 0)
    {
        $query = $this->db->select('*')
            ->from('order_resolutions')
            ->where('order_id', (int)$order_id)
            ->where_not_in('status', ['Close', 'Close (Final)']);

        if (!empty($product_id)) {
            $query->where('product_id', (int)$product_id);
        }

        return $query->order_by('id', 'DESC')->get()->row();
    }

    /**
     * Get the latest resolution ticket for an order item regardless of status
     *
     * @param int $order_id
     * @param int $product_id
     * @return object|null
     */
    public function get_latest_resolution_for_item($order_id, $product_id = 0)
    {
        $query = $this->db->select('*')
            ->from('order_resolutions')
            ->where('order_id', (int)$order_id);

        if (!empty($product_id)) {
            $query->where('product_id', (int)$product_id);
        }

        return $query->order_by('id', 'DESC')->get()->row();
    }

    /**
     * Batch retrieve escalation statuses ('in_progress' | 'complete' | null) for a list of order IDs.
     * Maps each order_id_product_id and order_id_b2b_order_id to its latest escalation state.
     *
     * @param array $order_ids
     * @return array
     */
    public function get_escalation_status_for_order_items(array $order_ids)
    {
        if (empty($order_ids)) {
            return [];
        }

        $order_ids = array_unique(array_filter(array_map('intval', $order_ids)));
        if (empty($order_ids)) {
            return [];
        }

        $rows = $this->db->select('id, order_id, product_id, b2b_order_id, status, resolution_status')
            ->from('order_resolutions')
            ->where_in('order_id', $order_ids)
            ->order_by('id', 'DESC')
            ->get()
            ->result();

        $map = [];
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $status = trim($r->status);
                $is_closed = in_array($status, ['Close', 'Close (Final)'], true);
                $state = $is_closed ? 'complete' : 'in_progress';

                // Map by order_id and product_id
                if (!empty($r->product_id)) {
                    $key_prod = $r->order_id . '_' . $r->product_id;
                    if (!isset($map[$key_prod])) {
                        $map[$key_prod] = $state;
                    }
                }

                // Map by order_id and b2b_order_id
                if (!empty($r->b2b_order_id)) {
                    $key_b2b = $r->order_id . '_sub_' . $r->b2b_order_id;
                    if (!isset($map[$key_b2b])) {
                        $map[$key_b2b] = $state;
                    }
                }

                // General order fallback
                $key_order = (string)$r->order_id;
                if (!isset($map[$key_order])) {
                    $map[$key_order] = $state;
                }
            }
        }

        return $map;
    }

    /**
     * Get escalation status for a specific item ('in_progress' | 'complete' | null)
     *
     * @param int $order_id
     * @param int $product_id
     * @param int $b2b_order_id
     * @return string|null
     */
    public function get_item_escalation_status($order_id, $product_id = 0, $b2b_order_id = 0)
    {
        $res = $this->get_latest_resolution_for_item($order_id, $product_id);
        if (!$res && !empty($b2b_order_id)) {
            $res = $this->db->select('*')
                ->from('order_resolutions')
                ->where('order_id', (int)$order_id)
                ->where('b2b_order_id', (int)$b2b_order_id)
                ->order_by('id', 'DESC')
                ->get()
                ->row();
        }

        if (!$res) {
            return null;
        }

        $status = trim($res->status);
        if (in_array($status, ['Close', 'Close (Final)'], true)) {
            return 'complete';
        }

        return 'in_progress';
    }

    /**
     * Helper to get product name by ID
     */
    public function get_product_name_by_id($product_id)
    {
        if (empty($product_id)) {
            return 'General Item';
        }
        $p = $this->db->select('name')->where('id', $product_id)->get('products')->row();
        return $p ? html_entity_decode($p->name, ENT_QUOTES, 'UTF-8') : 'Product #' . $product_id;
    }

    /**
     * Helper to get merchant ID for order item
     */
    public function get_merchant_id_for_item($order_id, $product_id)
    {
        if (!empty($product_id)) {
            $p = $this->db->select('publisher_id')->where('id', $product_id)->get('products')->row();
            if ($p && !empty($p->publisher_id)) {
                return (int)$p->publisher_id;
            }
        }
        $b2b = $this->db->select('publisher_id')->where('webshop_order_id', $order_id)->get('b2b_orders')->row();
        return $b2b ? (int)$b2b->publisher_id : 0;
    }

    /**
     * Helper to get merchant publication name for order item
     */
    public function get_merchant_name_for_item($order_id, $product_id)
    {
        $m_id = $this->get_merchant_id_for_item($order_id, $product_id);
        if ($m_id > 0) {
            $m = $this->db->select('publication_name')->where('id', $m_id)->get('publisher')->row();
            if ($m && !empty($m->publication_name)) {
                return $m->publication_name;
            }
        }
        return 'Merchant';
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

        // Calculate refund amount for resolution
        $refund_amount = 0.00;
        if (!empty($order_item_id)) {
            $soi = $this->db->select('price, total_price, qty_ordered')->where('item_id', $order_item_id)->get('sales_order_items')->row();
            if ($soi) {
                $refund_amount = (float)($soi->total_price ?: ($soi->price * (int)($soi->qty_ordered ?: 1)));
            } else {
                $boi = $this->db->select('price, total_price, qty_ordered')->where('item_id', $order_item_id)->get('b2b_order_items')->row();
                if ($boi) {
                    $refund_amount = (float)($boi->total_price ?: ($boi->price * (int)($boi->qty_ordered ?: 1)));
                }
            }
        }
        if ($refund_amount <= 0 && !empty($order_id) && !empty($product_id)) {
            $soi = $this->db->select('price, total_price, qty_ordered')->where('order_id', $order_id)->where('product_id', $product_id)->get('sales_order_items')->row();
            if ($soi) {
                $refund_amount = (float)($soi->total_price ?: ($soi->price * (int)($soi->qty_ordered ?: 1)));
            }
        }
        if ($refund_amount <= 0 && !empty($b2b_order_id) && !empty($product_id)) {
            $boi = $this->db->select('price, total_price, qty_ordered')->where('order_id', $b2b_order_id)->where('product_id', $product_id)->get('b2b_order_items')->row();
            if ($boi) {
                $refund_amount = (float)($boi->total_price ?: ($boi->price * (int)($boi->qty_ordered ?: 1)));
            }
        }
        if ($refund_amount <= 0 && !empty($product_id)) {
            $prod = $this->db->select('price, special_price')->where('id', $product_id)->get('products')->row();
            if ($prod) {
                $refund_amount = (float)(($prod->special_price > 0) ? $prod->special_price : $prod->price);
            }
        }
        if ($refund_amount <= 0 && !empty($order_id)) {
            $so = $this->db->select('subtotal, grand_total')->where('order_id', $order_id)->get('sales_order')->row();
            if ($so) {
                $refund_amount = (float)($so->subtotal ?: $so->grand_total);
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
            'refund_amount'    => $refund_amount,
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

        // Immediately sync payout status (put on hold due to active resolution)
        $this->update_payout_eligibility_for_resolution((object)$resolution_data);

        // Insert initial message into order_resolution_messages
        $shopper_name = 'Shopper';
        if (!empty($customer_id)) {
            $c_row = $this->db->select('first_name, last_name')->where('id', $customer_id)->get('customers')->row();
            if ($c_row) {
                $shopper_name = trim(($c_row->first_name ?? '') . ' ' . ($c_row->last_name ?? '')) ?: 'Shopper';
            }
        }

        $msg_data = [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'sender_role'   => 'shopper',
            'sender_id'     => $customer_id,
            'sender_name'   => $shopper_name,
            'message'       => $message,
            'attachment'    => $attachment,
            'created_at'    => $time,
            'ip'            => $ip,
        ];
        if ($this->db->field_exists('conversion_type', 'order_resolution_messages')) {
            $msg_data['conversion_type'] = 'shopper_merchant';
        }
        $this->db->insert('order_resolution_messages', $msg_data);

        // Audit Log
        $audit_data = [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'from_status'   => '',
            'to_status'     => 'Open',
            'action'        => 'Ticket Created',
            'actor_role'    => 'shopper',
            'actor_id'      => $customer_id,
            'notes'         => 'Order resolution request submitted by shopper.',
            'created_at'    => $time,
        ];
        if ($this->db->field_exists('source_panel', 'order_resolution_audit_log')) {
            $audit_data['source_panel'] = 'shopper';
        }
        if ($this->db->field_exists('conversion_direction', 'order_resolution_audit_log')) {
            $audit_data['conversion_direction'] = 'shopper_merchant';
        }
        $this->db->insert('order_resolution_audit_log', $audit_data);

        // Order Status & Item Status remain completely separate and are never altered by resolution tickets

        // Send Email Notifications
        $this->send_creation_emails($resolution_data, $shopper_name);

        // Send In-App Notifications
        $this->send_inapp_notification('admin', 1, 'new_ticket',
            'New Order Resolution Ticket',
            "New Resolution Ticket #{$ticket_number} created for Order #{$order_number} by {$shopper_name}.", $resolution_data);
        if (!empty($merchant_id)) {
            $this->send_inapp_notification('merchant', $merchant_id, 'new_ticket',
                'New Order Resolution Ticket',
                "New Resolution Ticket #{$ticket_number} raised for Order #{$order_number}.", $resolution_data);
        }

        return $this->get_resolution_by_ticket_number($ticket_number);
    }

    /**
     * Deprecated / No-op: Order status and Order Item status must remain completely separate
     * from the Order Resolution status lifecycle. This method is retained as a safe no-op
     * to prevent any accidental mutations of sales_order, b2b_orders, or b2b_order_items.
     */
    public function update_order_item_status($order_id, $product_id, $merchant_id, $target_status)
    {
        // Strict separation: Order status and Order Item status are NEVER altered by Resolution status.
        return false;
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
        $help_email = $this->get_help_email();

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
     * Supports separate conversion workflows: shopper_merchant, shopper_admin, merchant_admin
     */
    public function add_reply($ticket_number, $sender_role, $sender_id, $sender_name, $message, $attachment = '', $ip = '', $conversion_type = null)
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return false;
        }

        if ($res->status === 'Close (Final)') {
            return false;
        }

        // Determine default conversion_type if not explicitly passed
        if (empty($conversion_type)) {
            if ($sender_role === 'shopper') {
                $conversion_type = ($res->resolution_status === 'resolution_requested' || $res->status === 'ReOpen') ? 'shopper_admin' : 'shopper_merchant';
            } elseif ($sender_role === 'merchant') {
                $conversion_type = 'shopper_merchant';
            } elseif (in_array($sender_role, ['help', 'acct'], true)) {
                $conversion_type = 'shopper_admin';
            } else {
                $conversion_type = 'shopper_merchant';
            }
        }

        $time = time();
        $msg_data = [
            'resolution_id' => $res->id,
            'ticket_number' => $ticket_number,
            'sender_role'   => $sender_role,
            'sender_id'     => $sender_id,
            'sender_name'   => $sender_name,
            'message'       => $message,
            'attachment'    => $attachment,
            'created_at'    => $time,
            'ip'            => $ip ?: ($_SERVER['REMOTE_ADDR'] ?? ''),
        ];

        if ($this->db->field_exists('conversion_type', 'order_resolution_messages')) {
            $msg_data['conversion_type'] = $conversion_type;
        }

        $this->db->insert('order_resolution_messages', $msg_data);
        $this->db->where('id', $res->id)->update('order_resolutions', ['updated_at' => $time]);

        // Trigger Notifications based on conversion channel & sender
        if ($conversion_type === 'shopper_merchant') {
            if ($sender_role === 'merchant') {
                $this->send_merchant_reply_notification($res, $message);
            } elseif ($sender_role === 'shopper') {
                $this->send_shopper_reply_notification($res, $message);
            }
        } elseif ($conversion_type === 'shopper_admin') {
            if ($sender_role === 'shopper') {
                $this->send_inapp_notification('admin', 1, 'shopper_admin_reply',
                    "Order Resolution - Reply from Shopper (#{$ticket_number})",
                    "Shopper sent a message in Support dispute channel for Ticket #{$ticket_number}.", $res);
            }
        } elseif ($conversion_type === 'merchant_admin') {
            if ($sender_role === 'merchant') {
                $this->send_inapp_notification('admin', 1, 'merchant_admin_reply',
                    "Order Resolution - Message from Merchant (#{$ticket_number})",
                    "Merchant sent a message in Admin channel for Ticket #{$ticket_number}.", $res);
            }
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
        $help_email = $this->get_help_email();
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
     * Send in-app notification to notifications table
     */
    public function send_inapp_notification($recipient_type, $recipient_id, $subtype, $title, $message, $res)
    {
        if (empty($recipient_id)) {
            return false;
        }

        $ticket_number = is_object($res) ? ($res->ticket_number ?? '') : ($res['ticket_number'] ?? '');
        $order_number  = is_object($res) ? ($res->order_number ?? '') : ($res['order_number'] ?? '');
        $order_id      = is_object($res) ? ($res->order_id ?? 0) : ($res['order_id'] ?? 0);
        $product_id    = is_object($res) ? ($res->product_id ?? 0) : ($res['product_id'] ?? 0);
        $customer_id   = is_object($res) ? ($res->customer_id ?? 0) : ($res['customer_id'] ?? 0);
        $merchant_id   = is_object($res) ? ($res->merchant_id ?? 0) : ($res['merchant_id'] ?? 0);

        $time_str = date('Y-m-d H:i:s');
        $notif_data = [
            'type'           => 'order_resolution',
            'subtype'        => $subtype,
            'recipient_type' => $recipient_type, // 'customer', 'merchant', 'admin'
            'recipient_id'   => (int)$recipient_id,
            'title'          => $title,
            'message'        => $message,
            'data'           => json_encode([
                'ticket_number' => $ticket_number,
                'order_number'  => $order_number,
                'order_id'      => $order_id,
                'product_id'    => $product_id,
                'customer_id'   => $customer_id,
                'merchant_id'   => $merchant_id,
            ]),
            'is_read'        => 0,
            'created_at'     => $time_str,
            'updated_at'     => $time_str,
        ];

        return $this->db->insert('notifications', $notif_data);
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

        // Idempotency & State Guard: Cannot perform actions on closed tickets
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'This resolution ticket is permanently closed (Final).', 'requires_reload' => true];
        }

        // Idempotency Guard: Prevent duplicate actions
        if ($action === 'replacement_completed') {
            if ($res->merchant_action === 'replacement_completed') {
                return ['status' => false, 'message' => 'Replacement has already been marked as completed.', 'requires_reload' => true];
            }
            if ($res->merchant_action !== 'replacement_approved' || $res->delivery_option === 'none') {
                return ['status' => false, 'message' => 'Replacement must be approved with a delivery option first.', 'requires_reload' => true];
            }
        } else {
            if (!empty($res->merchant_action) && $res->merchant_action !== 'none') {
                return ['status' => false, 'message' => 'A decision has already been submitted for this ticket (' . ucwords(str_replace('_', ' ', $res->merchant_action)) . ').', 'requires_reload' => true];
            }
        }

        $category = strtolower(trim($res->category ?? ''));
        $is_refund_type = in_array($category, ['refund', 'return'], true);
        $is_replacement_type = ($category === 'replacement');

        if (in_array($action, ['refund_approved', 'refund_denied'], true) && !$is_refund_type) {
            return ['status' => false, 'message' => 'Refund actions are not permitted for a ' . htmlspecialchars($res->category) . ' resolution ticket.'];
        }

        if (in_array($action, ['replacement_approved', 'replacement_denied', 'replacement_completed'], true) && !$is_replacement_type) {
            return ['status' => false, 'message' => 'Replacement actions are not permitted for a ' . htmlspecialchars($res->category) . ' resolution ticket.'];
        }

        $time = time();
        $update = ['updated_at' => $time];

        switch ($action) {
            case 'refund_approved':
                $refund_amount = (float)($extra['refund_amount'] ?? 0);
                $update['merchant_action'] = 'refund_approved';
                $update['refund_amount']   = $refund_amount;
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Refund Approved', 'merchant', $merchant_id, "Approved refund of {$refund_amount}", 'merchant', 'merchant_shopper');

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

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Refund Denied', 'merchant', $merchant_id, 'Merchant denied refund.', 'merchant', 'merchant_shopper');
                
                // Notifications / Emails as per specifications:
                // Shopper receives: Order Resolution No.: [TicketNumber] - No Refund for Product
                $this->send_refund_denied_shopper_email($res);
                // @Help receives: Order Resolution No.: [TicketNumber] - No Refund for Product
                $this->send_refund_denied_help_email($res);

                // In-App Notifications
                if (!empty($res->customer_id)) {
                    $this->send_inapp_notification('customer', $res->customer_id, 'refund_denied',
                        'Order Resolution - No Refund for Product',
                        "Merchant has denied the refund request for Ticket #{$ticket_number} (Order #{$res->order_number}).", $res);
                }
                $this->send_inapp_notification('admin', 1, 'refund_denied',
                    'Order Resolution - Refund Denied',
                    "Merchant denied refund for Ticket #{$ticket_number} (Order #{$res->order_number}). Please mark ticket as Close.", $res);

                return ['status' => true, 'message' => 'Refund denied. Shopper and @Help have been notified.'];

            case 'replacement_approved':
                $delivery_option = $extra['delivery_option'] ?? '';
                if (!in_array($delivery_option, ['own_delivery', 'self_pickup', 'ym_delivery'], true)) {
                    return ['status' => false, 'message' => 'Please select a valid delivery option.'];
                }
                $update['merchant_action'] = 'replacement_approved';
                $update['delivery_option'] = $delivery_option;
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Approved', 'merchant', $merchant_id, "Selected delivery: {$delivery_option}", 'merchant', 'merchant_shopper');

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

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Denied', 'merchant', $merchant_id, 'Merchant denied replacement.', 'merchant', 'merchant_shopper');
                $this->send_merchant_action_email_to_help($res, 'Replacement Denied');
                $this->send_replacement_denied_shopper_email($res);
                $this->send_replacement_denied_help_email($res);

                // In-App Notifications
                if (!empty($res->customer_id)) {
                    $this->send_inapp_notification('customer', $res->customer_id, 'replacement_denied',
                        'Order Resolution - No Replacement for Product',
                        "Merchant has denied the replacement request for Ticket #{$ticket_number} (Order #{$res->order_number}).", $res);
                }
                $this->send_inapp_notification('admin', 1, 'replacement_denied',
                    'Order Resolution - Replacement Denied',
                    "Merchant denied replacement for Ticket #{$ticket_number} (Order #{$res->order_number}). Please mark ticket as Close.", $res);

                return ['status' => true, 'message' => 'Replacement denied. Shopper and @Help have been notified.'];

            case 'replacement_completed':
                if ($res->merchant_action !== 'replacement_approved' || $res->delivery_option === 'none') {
                    return ['status' => false, 'message' => 'Replacement must be approved with a delivery option first.'];
                }

                // Strict Server Check for YM Delivery: AddOn Service must be paid and verified
                if ($res->delivery_option === 'ym_delivery') {
                    $addon_purchase = !empty($res->addon_purchase_id) 
                        ? $this->db->get_where('merchant_addon_purchases', ['id' => (int)$res->addon_purchase_id])->row() 
                        : null;
                    if (!$addon_purchase || $addon_purchase->status !== 'paid') {
                        return [
                            'status'  => false,
                            'message' => 'Replacement cannot be completed. The YellowMarkets Delivery AddOn Service must be purchased and payment verified first.'
                        ];
                    }
                }

                $update['merchant_action'] = 'replacement_completed';
                $this->db->where('id', $res->id)->update('order_resolutions', $update);

                $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'Replacement Completed', 'merchant', $merchant_id, 'Merchant marked replacement completed.', 'merchant', 'merchant_shopper');
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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'This resolution ticket is permanently closed (Final).', 'requires_reload' => true];
        }
        if ($res->resolution_status === 'resolution_requested' || $res->status === 'ReOpen') {
            $errMsg = $this->lang->line('err_already_escalated') ?: 'This request has already been escalated to YellowMarkets support.';
            return ['status' => false, 'message' => $errMsg, 'requires_reload' => true];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'                  => 'ReOpen',
            'resolution_status'       => 'resolution_requested',
            'resolution_requested_at' => $time,
            'is_active_dispute'       => 1,
            'updated_at'              => $time,
        ]);

        $this->update_payout_eligibility_for_resolution($res);

        if (!empty($message)) {
            $this->add_reply($ticket_number, 'shopper', $customer_id, 'Shopper', $message, '', '', 'shopper_admin');
        }

        $this->audit_log($res->id, $ticket_number, $res->status, 'ReOpen', 'Escalate to Yellow Market', 'shopper', $customer_id, 'Shopper requested escalation to YellowMarkets support.', 'shopper', 'shopper_admin');

        // Emails to @Help and Merchant
        // @Help receives: Order Resolution No.: [TicketNumber] - Dispute Refund Decision
        $this->send_dispute_help_email($res, $message);
        // Merchant receives: Order Resolution No.: [TicketNumber] - Shopper Dispute Decision
        $this->send_dispute_merchant_email($res, $message);

        // In-App Notifications
        $this->send_inapp_notification('admin', 1, 'dispute_refund_decision',
            'Order Resolution - Dispute Refund Decision',
            "Shopper has disputed refund denial for Ticket #{$ticket_number} (Order #{$res->order_number}). Investigation required.", $res);
        if (!empty($res->merchant_id)) {
            $this->send_inapp_notification('merchant', $res->merchant_id, 'shopper_dispute_decision',
                'Order Resolution - Shopper Dispute Decision',
                "Shopper has disputed refund decision for Ticket #{$ticket_number} (Order #{$res->order_number}). Case is under Yellow Markets review.", $res);
        }

        $successMsg = $this->lang->line('escalation_submitted_confirmation') ?: 'The escalation has been submitted to YellowMarkets support.';
        return ['status' => true, 'message' => $successMsg];
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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'Ticket is already permanently closed (Final).', 'requires_reload' => true];
        }
        if ($res->status === 'Processing' && $res->assigned_role === 'Acct') {
            return ['status' => false, 'message' => 'Ticket is already assigned to Accounting (@Acct).', 'requires_reload' => true];
        }
        if (in_array($res->merchant_action, ['replacement_completed', 'replacement_denied'], true)) {
            return ['status' => false, 'message' => 'Accounting assignment is not applicable for this workflow.'];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'        => 'Processing',
            'assigned_role' => 'Acct',
            'assigned_to'   => $admin_id,
            'updated_at'    => $time,
        ]);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Processing', 'Assigned to @acct', 'admin', $admin_id, $notes, 'admin', 'admin_shopper');

        // 1. Email to Merchant: order-resolution-refund-initiated-merchant
        $this->send_refund_initiated_merchant_email($res);

        // 2. Email to @Acct: order-resolution-refund-request-acct (or YM delivery acct email)
        if ($res->delivery_option === 'ym_delivery') {
            $this->send_ym_delivery_acct_email($res);
        } else {
            $this->send_refund_acct_email($res);
        }

        // In-App Notification to @Acct
        $this->send_inapp_notification('admin', 1, 'refund_request_acct',
            'Order Resolution - Refund Request (@Acct)',
            "Resolution Ticket #{$ticket_number} (Order #{$res->order_number}) assigned to @Acct for refund processing.", $res);

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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'Ticket is already permanently closed (Final).', 'requires_reload' => true];
        }
        if ($res->status === 'Done') {
            return ['status' => false, 'message' => 'Ticket is already marked as Done.', 'requires_reload' => true];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'     => 'Done',
            'updated_at' => $time,
        ]);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Done', 'Marked Done', 'acct', $admin_id, $notes ?: 'Refund completed from merchant 15-day hold-back sales.', 'admin', 'admin_shopper');

        // In-App Notification to @Help indicating refund is processed and ready for Close (Final)
        $this->send_inapp_notification('admin', 1, 'refund_done',
            'Order Resolution - Refund Processed by @Acct',
            "Accounting has processed refund for Ticket #{$ticket_number} (Order #{$res->order_number}) from merchant hold-back sales. Ticket status is Done. Ready for Close (Final).", $res);

        return ['status' => true, 'message' => 'Ticket marked as Done. Refund processed from merchant 15-day hold-back sales.'];
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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'Ticket is already permanently closed (Final).', 'requires_reload' => true];
        }
        if ($res->status === 'Close') {
            return ['status' => false, 'message' => 'Ticket is already marked as Closed.', 'requires_reload' => true];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'     => 'Close',
            'closed_at'  => $time,
            'closed_by'  => 'admin',
            'updated_at' => $time,
        ]);

        $this->update_payout_eligibility_for_resolution($res);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Close', 'Ticket Closed', 'admin', $admin_id, $notes, 'admin', 'admin_shopper');

        // Merchant receives: Order Resolution No.: [TicketNumber] - Closed
        $this->send_ticket_closed_merchant_email($res);

        // In-App Notification to Merchant
        if (!empty($res->merchant_id)) {
            $this->send_inapp_notification('merchant', $res->merchant_id, 'ticket_closed',
                'Order Resolution Closed',
                "Order Resolution Ticket #{$ticket_number} has been marked as Close by Yellow Markets Support (@Help).", $res);
        }

        // If replacement was completed, notify shopper
        if ($res->merchant_action === 'replacement_completed') {
            $this->send_replacement_completed_shopper_email($res);
        }

        return ['status' => true, 'message' => 'Ticket closed successfully. Merchant notified.'];
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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'Ticket is already permanently closed (Final).', 'requires_reload' => true];
        }

        $time = time();
        $this->db->where('id', $res->id)->update('order_resolutions', [
            'status'            => 'Close (Final)',
            'is_active_dispute' => 0,
            'closed_at'         => $time,
            'closed_by'         => 'admin',
            'updated_at'        => $time,
        ]);

        $this->update_payout_eligibility_for_resolution($res);

        $this->audit_log($res->id, $ticket_number, $res->status, 'Close (Final)', 'Ticket Closed (Final)', 'admin', $admin_id, $notes, 'admin', 'admin_shopper');

        // Final Notifications as per specifications:
        // 1. Merchant receives: Order Resolution No.: [TicketNumber] - Closed (Final)
        $this->send_closed_final_merchant_email($res);

        // 2. Shopper receives: Order Resolution No.: [TicketNumber] - You Are Being Refunded
        if ($res->resolution_status === 'resolution_approved' || $res->merchant_action === 'refund_approved' || strtolower($res->category) === 'refund') {
            $this->send_refund_completed_shopper_email($res);
        } elseif ($res->delivery_option === 'ym_delivery') {
            $this->send_ym_delivery_completed_shopper_email($res);
        }

        // In-App Notifications
        if (!empty($res->merchant_id)) {
            $this->send_inapp_notification('merchant', $res->merchant_id, 'closed_final',
                'Order Resolution - Closed (Final)',
                "Order Resolution Ticket #{$ticket_number} has been closed (Final).", $res);
        }
        if (!empty($res->customer_id)) {
            $this->send_inapp_notification('customer', $res->customer_id, 'refund_completed',
                'Order Resolution - You Are Being Refunded',
                "Your refund for Ticket #{$ticket_number} (Order #{$res->order_number}) has been completed and resolution closed (Final).", $res);
        }

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

        // State & Idempotency Guard
        if ($res->status === 'Close (Final)') {
            return ['status' => false, 'message' => 'Ticket is already permanently closed (Final).', 'requires_reload' => true];
        }
        if (in_array($res->resolution_status, ['resolution_approved', 'resolution_denied'], true)) {
            return ['status' => false, 'message' => 'A resolution decision has already been recorded for this ticket.', 'requires_reload' => true];
        }

        $time = time();

        if ($decision === 'approved') {
            // Scenario 1: Decision in Favour of Shopper
            // 1. @Help clicks Resolution Approved -> status: Processing, assigned_role: Acct
            $this->db->where('id', $res->id)->update('order_resolutions', [
                'status'            => 'Processing',
                'assigned_role'     => 'Acct',
                'resolution_status' => 'resolution_approved',
                'updated_at'        => $time,
            ]);

            $this->audit_log($res->id, $ticket_number, $res->status, 'Processing', 'Resolution Approved (In Favour of Shopper)', 'admin', $admin_id, $notes, 'admin', 'admin_shopper');

            // 4. @Acct receives: Order Resolution No.: [TicketNumber] - Refund Request
            $this->send_refund_acct_email($res);

            // In-App Notification to @Acct
            $this->send_inapp_notification('admin', 1, 'refund_request_acct',
                'Order Resolution - Refund Request (@Acct)',
                "Resolution approved in favour of shopper for Ticket #{$ticket_number} (Order #{$res->order_number}). Please refund from merchant 15-day hold-back sales.", $res);

            return ['status' => true, 'message' => 'Resolution decision approved in favour of shopper and assigned to Accounting (@Acct).'];
        } else {
            // Scenario 2: Decision in Favour of Merchant
            // 1. @Help changes Resolution status to Close (Final)
            $this->db->where('id', $res->id)->update('order_resolutions', [
                'status'            => 'Close (Final)',
                'resolution_status' => 'resolution_denied',
                'is_active_dispute' => 0,
                'closed_at'         => $time,
                'closed_by'         => 'admin',
                'updated_at'        => $time,
            ]);

            $this->update_payout_eligibility_for_resolution($res);

            $this->audit_log($res->id, $ticket_number, $res->status, 'Close (Final)', 'Resolution Denied (In Favour of Merchant)', 'admin', $admin_id, $notes, 'admin', 'admin_shopper');

            // 2. Merchant receives: Order Resolution No.: [TicketNumber] - Closed (Final)
            $this->send_closed_final_merchant_email($res);

            // 3. Shopper receives: Order Resolution No.: [TicketNumber] - Refund Disapproved
            $this->send_refund_disapproved_shopper_email($res);

            // In-App Notifications
            if (!empty($res->merchant_id)) {
                $this->send_inapp_notification('merchant', $res->merchant_id, 'closed_final',
                    'Order Resolution - Closed (Final)',
                    "Dispute resolved in your favour for Ticket #{$ticket_number}. Ticket is closed (Final).", $res);
            }
            if (!empty($res->customer_id)) {
                $this->send_inapp_notification('customer', $res->customer_id, 'refund_disapproved',
                    'Order Resolution - Refund Disapproved',
                    "After investigation, resolution was decided in favour of merchant for Ticket #{$ticket_number}. Ticket is closed (Final).", $res);
            }

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
        $acct_email = $this->get_acct_email();
        $admin_url  = base_url('admin/order_resolution/view/' . $res->ticket_number);

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##REFUND_AMOUNT##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, CURRENCY_TYPE . ' ' . number_format($res->refund_amount, 2), $admin_url, 'Yellow Markets'];
        $this->CommonModel->sendCommonHTMLEmail($acct_email, 'order-resolution-refund-request-acct', $tempVars, $dynamicVars);
    }

    private function send_ym_delivery_acct_email($res)
    {
        $acct_email = $this->get_acct_email();
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

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $lang_code = ($site_lang === 'french') ? 'fr' : 'en';

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##MERCHANT_NAME##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_merchant_name($res), 'Yellow Markets'];
        $custom_subject = ($lang_code === 'fr')
            ? "Résolution de commande No. : {$res->ticket_number} - Fermé"
            : "Order Resolution No.: {$res->ticket_number} - Closed";
        $this->CommonModel->sendCommonHTMLEmail($m_email, 'order-resolution-ticket-closed-merchant', $tempVars, $dynamicVars, $lang_code, $custom_subject);
    }

    private function send_refund_denied_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $custom_subject = "Order Resolution No.{$res->ticket_number} - No Refund for Product";
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-refund-denied-shopper', $tempVars, $dynamicVars, '', $custom_subject);
    }

    private function send_refund_denied_help_email($res)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, base_url('admin/order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $custom_subject = "Order Resolution No.: {$res->ticket_number} - No Refund for Product";
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-refund-denied-help', $tempVars, $dynamicVars, '', $custom_subject);
    }

    private function send_replacement_denied_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $lang_code = ($site_lang === 'french') ? 'fr' : 'en';

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $custom_subject = ($lang_code === 'fr')
            ? "Résolution de commande No.{$res->ticket_number} - Aucun remplacement pour le produit"
            : "Order Resolution No.{$res->ticket_number} - No Replacement for Product";
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-denied-shopper', $tempVars, $dynamicVars, $lang_code, $custom_subject);
    }

    private function send_replacement_denied_help_email($res)
    {
        $help_email = $this->get_help_email();
        if (!$help_email) return;

        $site_lang = $this->session->userdata('site_lang') ?: 'english';
        $lang_code = ($site_lang === 'french') ? 'fr' : 'en';

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, base_url('admin/order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $custom_subject = ($lang_code === 'fr')
            ? "Résolution de commande No. : {$res->ticket_number} - Aucun remplacement pour le produit"
            : "Order Resolution No.: {$res->ticket_number} - No Replacement for Product";
        $this->CommonModel->sendCommonHTMLEmail($help_email, 'order-resolution-replacement-denied-help', $tempVars, $dynamicVars, $lang_code, $custom_subject);
    }

    private function send_replacement_completed_shopper_email($res)
    {
        $shopper_email = $this->get_shopper_email($res);
        if (!$shopper_email) return;

        $tempVars = ['##TICKET_NUMBER##', '##ORDER_NUMBER##', '##SHOPPER_NAME##', '##TICKET_URL##', '##WEBSHOPNAME##'];
        $dynamicVars = [$res->ticket_number, $res->order_number, $this->get_shopper_name($res), base_url('order_resolution/view/' . $res->ticket_number), 'Yellow Markets'];
        $custom_subject = "Order Resolution No.: {$res->ticket_number} - Your Product Replacement Completed.";
        $this->CommonModel->sendCommonHTMLEmail($shopper_email, 'order-resolution-replacement-completed-shopper', $tempVars, $dynamicVars, '', $custom_subject);
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
        $res = $this->db->where('ticket_number', $ticket_number)->get('order_resolutions')->row();
        if ($res) {
            $this->enrich_resolution($res);
        }
        return $res;
    }

    public function get_resolution_by_id($id)
    {
        $res = $this->db->where('id', (int)$id)->get('order_resolutions')->row();
        if ($res) {
            $this->enrich_resolution($res);
        }
        return $res;
    }

    /**
     * Compute and enrich resolution with accurate refund amount
     */
    public function enrich_resolution(&$res)
    {
        if (empty($res)) {
            return;
        }

        if (empty($res->refund_amount) || (float)$res->refund_amount <= 0) {
            $res->refund_amount = $this->get_refund_amount_for_resolution($res);
            if ($res->refund_amount > 0 && $this->db->field_exists('refund_amount', 'order_resolutions')) {
                $this->db->where('id', $res->id)->update('order_resolutions', ['refund_amount' => $res->refund_amount]);
            }
        }
    }

    /**
     * Helper to compute refund amount from items, orders or products
     */
    public function get_refund_amount_for_resolution($res)
    {
        if (empty($res)) {
            return 0.00;
        }

        $amount = 0.00;

        // 1. By order_item_id
        if (!empty($res->order_item_id)) {
            $soi = $this->db->select('price, total_price, qty_ordered')->where('item_id', $res->order_item_id)->get('sales_order_items')->row();
            if ($soi) {
                $amount = (float)($soi->total_price ?: ($soi->price * (int)($soi->qty_ordered ?: 1)));
            } else {
                $boi = $this->db->select('price, total_price, qty_ordered')->where('item_id', $res->order_item_id)->get('b2b_order_items')->row();
                if ($boi) {
                    $amount = (float)($boi->total_price ?: ($boi->price * (int)($boi->qty_ordered ?: 1)));
                }
            }
        }

        // 2. By order_id & product_id in sales_order_items
        if ($amount <= 0 && !empty($res->order_id) && !empty($res->product_id)) {
            $soi = $this->db->select('price, total_price, qty_ordered')
                ->where('order_id', $res->order_id)
                ->where('product_id', $res->product_id)
                ->get('sales_order_items')
                ->row();
            if ($soi) {
                $amount = (float)($soi->total_price ?: ($soi->price * (int)($soi->qty_ordered ?: 1)));
            }
        }

        // 3. By b2b_order_id & product_id in b2b_order_items
        if ($amount <= 0 && !empty($res->b2b_order_id) && !empty($res->product_id)) {
            $boi = $this->db->select('price, total_price, qty_ordered')
                ->where('order_id', $res->b2b_order_id)
                ->where('product_id', $res->product_id)
                ->get('b2b_order_items')
                ->row();
            if ($boi) {
                $amount = (float)($boi->total_price ?: ($boi->price * (int)($boi->qty_ordered ?: 1)));
            }
        }

        // 4. By sales_order_return table
        if ($amount <= 0 && !empty($res->order_id) && $this->db->table_exists('sales_order_return')) {
            $sor = $this->db->select('order_grandtotal_approved')->where('order_id', $res->order_id)->get('sales_order_return')->row();
            if ($sor && !empty($sor->order_grandtotal_approved)) {
                $amount = (float)$sor->order_grandtotal_approved;
            }
        }

        // 5. By products table price
        if ($amount <= 0 && !empty($res->product_id)) {
            $prod = $this->db->select('price, special_price')->where('id', $res->product_id)->get('products')->row();
            if ($prod) {
                $amount = (float)(($prod->special_price > 0) ? $prod->special_price : $prod->price);
            }
        }

        // 6. By sales_order grand total or subtotal
        if ($amount <= 0 && !empty($res->order_id)) {
            $so = $this->db->select('subtotal, grand_total')->where('order_id', $res->order_id)->get('sales_order')->row();
            if ($so) {
                $amount = (float)($so->subtotal ?: $so->grand_total);
            }
        }

        return $amount;
    }

    public function get_messages($resolution_id, $conversion_type = null)
    {
        $this->db->where('resolution_id', (int)$resolution_id);
        if ($conversion_type !== null) {
            if ($this->db->field_exists('conversion_type', 'order_resolution_messages')) {
                if (is_array($conversion_type)) {
                    $this->db->where_in('conversion_type', $conversion_type);
                } else {
                    $this->db->where('conversion_type', $conversion_type);
                }
            }
        }
        return $this->db->order_by('created_at', 'ASC')->get('order_resolution_messages')->result();
    }

    public function get_audit_log($resolution_id)
    {
        return $this->db->where('resolution_id', (int)$resolution_id)
            ->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC')
            ->get('order_resolution_audit_log')
            ->result();
    }

    public function audit_log($resolution_id, $ticket_number, $from_status, $to_status, $action, $actor_role, $actor_id, $notes = '', $source_panel = 'admin', $conversion_direction = 'shopper_merchant')
    {
        $audit_data = [
            'resolution_id' => $resolution_id,
            'ticket_number' => $ticket_number,
            'from_status'   => $from_status,
            'to_status'     => $to_status,
            'action'        => $action,
            'actor_role'    => $actor_role,
            'actor_id'      => $actor_id,
            'notes'         => $notes,
            'created_at'    => time(),
        ];

        if ($this->db->field_exists('source_panel', 'order_resolution_audit_log')) {
            $audit_data['source_panel'] = $source_panel;
        }
        if ($this->db->field_exists('conversion_direction', 'order_resolution_audit_log')) {
            $audit_data['conversion_direction'] = $conversion_direction;
        }

        return $this->db->insert('order_resolution_audit_log', $audit_data);
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
        $val = $this->CommonModel->get_custom_variable('contact_us_email');
        if (is_object($val)) {
            $val = !empty($val->value) ? $val->value : '';
        }
        if (!empty($val) && is_string($val)) {
            return $val;
        }

        $admin_val = $this->CommonModel->get_custom_variable('admin_email');
        if (is_object($admin_val)) {
            $admin_val = !empty($admin_val->value) ? $admin_val->value : '';
        }
        if (!empty($admin_val) && is_string($admin_val)) {
            return $admin_val;
        }

        return 'noreply@yellowmarkets.com';
    }

    private function get_acct_email()
    {
        $val = $this->CommonModel->get_custom_variable('account_email');
        if (is_object($val)) {
            $val = !empty($val->value) ? $val->value : '';
        }
        if (!empty($val) && is_string($val)) {
            return $val;
        }

        $admin_val = $this->CommonModel->get_custom_variable('admin_email');
        if (is_object($admin_val)) {
            $admin_val = !empty($admin_val->value) ? $admin_val->value : '';
        }
        if (!empty($admin_val) && is_string($admin_val)) {
            return $admin_val;
        }

        return 'noreply@yellowmarkets.com';
    }

    /**
     * Get or resolve delivery AddOn Service
     * Fetches dynamic Admin-configured service details (Category, Title, Final Price, Status)
     */
    public function get_delivery_addon_service($service_id = null)
    {
        // 1. If explicit service_id provided
        if (!empty($service_id)) {
            $service = $this->db->select('s.*, c.name as category_name, c.name_fr as category_name_fr')
                ->from('addon_services s')
                ->join('addon_categories c', 's.category_id = c.id', 'left')
                ->where('s.id', (int)$service_id)
                ->get()->row();
            if ($service) return $service;
        }

        // 2. Direct search for ID 61 (Admin-configured YM Delivery)
        $service = $this->db->select('s.*, c.name as category_name, c.name_fr as category_name_fr')
            ->from('addon_services s')
            ->join('addon_categories c', 's.category_id = c.id', 'left')
            ->where('s.id', 61)
            ->where('s.status', 1)
            ->get()->row();
        if ($service) return $service;

        // 3. Search for active service where title or category matches 'YM Delivery'
        $service = $this->db->select('s.*, c.name as category_name, c.name_fr as category_name_fr')
            ->from('addon_services s')
            ->join('addon_categories c', 's.category_id = c.id', 'left')
            ->group_start()
                ->like('s.title', 'YM Delivery')
                ->or_like('c.name', 'YM Delivery')
            ->group_end()
            ->where('s.status', 1)
            ->order_by('s.id', 'DESC')
            ->get()->row();
        if ($service) return $service;

        // 4. Fallback search for any active delivery service
        $service = $this->db->select('s.*, c.name as category_name, c.name_fr as category_name_fr')
            ->from('addon_services s')
            ->join('addon_categories c', 's.category_id = c.id', 'left')
            ->like('s.title', 'Delivery')
            ->where('s.status', 1)
            ->get()->row();
        if ($service) return $service;

        // 5. Fallback standard object populated with configured service 61 defaults
        return (object)[
            'id'               => 61,
            'category_id'      => 1,
            'category_name'    => 'YM Delivery Service',
            'category_name_fr' => 'Service de livraison YM',
            'title'            => 'YM Delivery',
            'title_fr'         => 'Livraison YM',
            'description'      => 'Delivery service for product replacement via YellowMarkets logistics.',
            'final_price'      => 1.00,
            'price'            => 1.00,
            'status'           => 1
        ];
    }

    /**
     * @Acct requests merchant to purchase AddOn Service for YM Delivery Replacement
     */
    public function request_addon_delivery($ticket_number, $admin_id, $notes = '', $service_id = null)
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) {
            return ['status' => false, 'message' => 'Ticket not found.'];
        }

        if ($res->delivery_option !== 'ym_delivery' || $res->merchant_action !== 'replacement_approved') {
            return ['status' => false, 'message' => 'AddOn Delivery Service request is only applicable for YM Delivery replacements.'];
        }

        $service = $this->get_delivery_addon_service($service_id);
        $service_id = $service ? (int)$service->id : 61;
        $service_title = $service ? $service->title : 'YM Delivery';
        $price = $service ? (float)$service->final_price : 1.00;

        $update_data = ['updated_at' => time()];
        if ($this->db->field_exists('addon_service_id', 'order_resolutions')) {
            $update_data['addon_service_id'] = $service_id;
        }
        $this->db->where('id', $res->id)->update('order_resolutions', $update_data);

        $audit_notes = "Requested merchant to purchase AddOn Service: {$service_title} (MUR " . number_format($price, 2) . ")." . ($notes ? ' Note: ' . $notes : '');
        $this->audit_log($res->id, $ticket_number, $res->status, $res->status, 'AddOn Service Purchase Requested', 'acct', $admin_id, trim($audit_notes), 'admin', 'admin_merchant');

        // Also post notice into Merchant <-> Admin conversion thread
        $this->add_reply($ticket_number, 'acct', $admin_id, 'Accounting Team (@Acct)', trim($audit_notes), '', '', 'merchant_admin');

        // In-app notification to merchant
        if (!empty($res->merchant_id)) {
            $this->send_inapp_notification(
                'merchant',
                $res->merchant_id,
                'addon_purchase_requested',
                'Action Required: Purchase Replacement Delivery AddOn Service',
                "Accounting (@Acct) has requested you to purchase the replacement delivery AddOn Service for Ticket #{$ticket_number} (Order #{$res->order_number}).",
                $res
            );
        }

        // Email to merchant
        $this->send_addon_purchase_request_email($res, $service);

        return ['status' => true, 'message' => 'AddOn Service purchase request sent to merchant successfully.'];
    }

    /**
     * Send email to merchant requesting AddOn Service purchase
     */
    public function send_addon_purchase_request_email($res, $service)
    {
        $merchant = !empty($res->merchant_id) ? $this->db->where('id', $res->merchant_id)->get('publisher')->row() : null;
        if (!$merchant || empty($merchant->email)) return false;

        $product = !empty($res->product_id) ? $this->db->where('id', $res->product_id)->get('products')->row() : null;
        $product_name = $product ? $product->name : 'Product';

        $subject = "Action Required: Replacement Delivery AddOn Service Purchase - Ticket #{$res->ticket_number}";
        $ticket_url = base_url('merchant/order_resolution/view/' . $res->ticket_number);

        $body = "<h3>Dear " . htmlspecialchars($merchant->publication_name ?: 'Merchant') . ",</h3>"
              . "<p>Accounting (@Acct) has reviewed your replacement approval for <strong>Ticket #{$res->ticket_number}</strong> (Order #{$res->order_number}) with YellowMarkets delivery.</p>"
              . "<p>Please purchase the replacement delivery service as an AddOn Service so logistics can be arranged:</p>"
              . "<ul>"
              . "<li><strong>Order Number:</strong> " . htmlspecialchars($res->order_number) . "</li>"
              . "<li><strong>Resolution Ticket:</strong> " . htmlspecialchars($res->ticket_number) . "</li>"
              . "<li><strong>Product:</strong> " . htmlspecialchars($product_name) . "</li>"
              . "<li><strong>Service:</strong> " . htmlspecialchars($service->title) . "</li>"
              . "<li><strong>Amount:</strong> MUR " . number_format($service->final_price, 2) . "</li>"
              . "</ul>"
              . "<p><a href=\"{$ticket_url}\" style=\"background:#1E7EC8;color:#fff;padding:10px 18px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;\">View Ticket & Purchase AddOn</a></p>"
              . "<p>Kind Regards,<br/>Yellow Markets Support & Accounting Team</p>";

        return $this->CommonModel->send_email($merchant->email, $subject, $body);
    }

    /**
     * Confirm AddOn Payment and update resolution
     */
    public function confirm_addon_payment($ticket_number, $purchase_id)
    {
        $res = $this->get_resolution_by_ticket_number($ticket_number);
        if (!$res) return false;

        $purchase = $this->db->get_where('merchant_addon_purchases', ['id' => (int)$purchase_id])->row();
        $txn = $purchase ? $this->db->where('purchase_id', $purchase->id)->order_by('id', 'DESC')->get('merchant_addon_transactions')->row() : null;
        $service = $purchase ? $this->get_delivery_addon_service($purchase->service_id) : $this->get_delivery_addon_service($res->addon_service_id ?? null);
        $service_title = $service ? $service->title : 'YM Delivery';
        $amount_val = $txn ? (float)$txn->amount : ($service ? (float)$service->final_price : 1.00);
        $amount_str = 'MUR ' . number_format($amount_val, 2);
        $txn_ref = !empty($txn->transaction_ref) ? $txn->transaction_ref : (!empty($txn->mer_trade_no) ? $txn->mer_trade_no : ('Purchase #' . $purchase_id));

        $merchant_name = 'Merchant';
        if (!empty($res->merchant_id)) {
            $m_row = $this->db->select('publication_name')->where('id', $res->merchant_id)->get('publisher')->row();
            if ($m_row && !empty($m_row->publication_name)) {
                $merchant_name = $m_row->publication_name;
            }
        }

        $this->db->where('id', $res->id)->update('order_resolutions', [
            'addon_purchase_id' => (int)$purchase_id,
            'updated_at'        => time(),
        ]);

        $audit_msg = "AddOn Service Purchased: {$service_title} ({$amount_str}) by {$merchant_name}. Ref: {$txn_ref}.";
        $this->audit_log(
            $res->id,
            $ticket_number,
            $res->status,
            $res->status,
            'AddOn Service Purchased',
            'merchant',
            $res->merchant_id,
            $audit_msg,
            'merchant',
            'merchant_admin'
        );

        // Post confirmation message into Merchant <-> Admin conversion thread
        $this->add_reply($ticket_number, 'merchant', $res->merchant_id, $merchant_name . ' (Merchant)', "Confirmed AddOn Payment: Purchased {$service_title} for {$amount_str}. Payment Ref: {$txn_ref}. Ready to proceed with replacement.", '', '', 'merchant_admin');

        // Detailed Notification to Admin (@Acct & @Help)
        $admin_title = "AddOn Service Purchased - Ticket #{$ticket_number}";
        $admin_msg = "Merchant ({$merchant_name}) has successfully purchased the AddOn Service: {$service_title} ({$amount_str}) for Ticket #{$ticket_number} (Order #{$res->order_number}). Payment Ref: {$txn_ref}.";
        $this->send_inapp_notification(
            'admin',
            1,
            'addon_payment_confirmed',
            $admin_title,
            $admin_msg,
            $res
        );

        return true;
    }

    /**
     * Re-evaluates and updates payout_status for orders associated with a resolution.
     */
    public function update_payout_eligibility_for_resolution($res)
    {
        if (empty($res)) {
            return;
        }

        if (is_numeric($res)) {
            $res = $this->get_resolution_by_id((int)$res);
            if (!$res) return;
        } elseif (is_string($res)) {
            $res = $this->get_resolution_by_ticket_number($res);
            if (!$res) return;
        }

        $order_ids = [];
        if (!empty($res->b2b_order_id)) {
            $order_ids[] = (int)$res->b2b_order_id;
        }
        if (!empty($res->order_id)) {
            $order_ids[] = (int)$res->order_id;
        }

        if (empty($order_ids)) {
            return;
        }

        // Find relevant b2b_orders
        $this->db->select('order_id, webshop_order_id, publisher_id, payout_status, status')
            ->from('b2b_orders')
            ->group_start()
                ->where_in('order_id', $order_ids)
                ->or_where_in('webshop_order_id', $order_ids)
            ->group_end();

        if (!empty($res->merchant_id)) {
            $this->db->where('publisher_id', (int)$res->merchant_id);
        }

        $b2b_orders = $this->db->get()->result_array();
        if (empty($b2b_orders)) {
            return;
        }

        foreach ($b2b_orders as $order) {
            $oid = (int)$order['order_id'];
            $payout_status = isset($order['payout_status']) ? (int)$order['payout_status'] : 1;
            if ($payout_status === 4) {
                continue; // Paid orders never revert
            }

            $pair_ids = array_unique(array_filter([$oid, (int)$order['webshop_order_id']]));
            $resolutions = $this->db->select('id, status, merchant_action, category, resolution_status, is_active_dispute')
                ->from('order_resolutions')
                ->group_start()
                    ->where_in('order_id', $pair_ids)
                    ->or_where_in('b2b_order_id', $pair_ids)
                ->group_end()
                ->order_by('id', 'DESC')
                ->get()
                ->result_array();

            $has_active = false;
            $has_approved = false;

            foreach ($resolutions as $r) {
                $r_st = trim($r['status'] ?? '');
                $r_act = trim($r['merchant_action'] ?? '');
                $r_cat = trim($r['category'] ?? '');
                $r_res = trim($r['resolution_status'] ?? '');
                $is_disp = (int)($r['is_active_dispute'] ?? 0);

                if (in_array($r_st, ['Open', 'Processing', 'ReOpen', 'Done'], true) || $is_disp === 1 || $r_res === 'resolution_requested') {
                    $has_active = true;
                    break;
                }

                $is_refund_app = ($r_st === 'Close (Final)' || $r_st === 'Close') &&
                    ($r_act === 'refund_approved' || $r_res === 'resolution_approved' || $r_cat === 'Refund');
                $is_rep_comp = ($r_st === 'Close' || $r_st === 'Close (Final)') &&
                    (in_array($r_act, ['replacement_completed', 'replacement_approved'], true) || $r_cat === 'Replacement');

                if ($is_refund_app || $is_rep_comp) {
                    $has_approved = true;
                }
            }

            if ($has_active) {
                if ($payout_status !== 3) {
                    $this->db->where('order_id', $oid)->update('b2b_orders', ['payout_status' => 3]);
                }
            } elseif ($has_approved) {
                if ($payout_status === 3) {
                    $this->db->where('order_id', $oid)->update('b2b_orders', ['payout_status' => 1]);
                }
            }
        }
    }
}