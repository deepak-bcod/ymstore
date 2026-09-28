<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_model extends CI_Model {

    public function get_plans() {
        return $this->db->get_where('subscription_plans', ['status' => 1])->result_array();
    }


    public function get_features() {
        return $this->db->order_by('sort_order', 'ASC')->get('subscription_features')->result_array();
    }

    public function get_plan_features() {
        $this->db->select('plan_features.plan_id, plan_features.feature_id, plan_features.value');
        $this->db->from('plan_features');
        return $this->db->get()->result_array();
    }

    public function get_default_starter_plan() {
        $plan = $this->db->get_where('subscription_plans', ['LOWER(name)' => 'starter', 'status' => 1])->row_array();
        if (!$plan) {
            $plan = $this->db->order_by('id', 'ASC')->get_where('subscription_plans', ['status' => 1])->row_array();
        }
        return $plan;
    }

    public function get_active_plan($publisher_id) {
        $sub = $this->db
            ->select('publisher_subscriptions.*, subscription_plans.name as plan_name, subscription_plans.name_fr as plan_name_fr')
            ->from('publisher_subscriptions')
            ->join('subscription_plans', 'subscription_plans.id = publisher_subscriptions.plan_id', 'left')
            ->where('publisher_subscriptions.publisher_id', $publisher_id)
            ->where('publisher_subscriptions.status', 'active')
            ->order_by('publisher_subscriptions.id', 'DESC')
            ->get()
            ->row_array();

        if ($sub) {
            return $sub;
        }

        // If no active record, check if there is an existing subscription record
        $latest = $this->db
            ->select('publisher_subscriptions.*, subscription_plans.name as plan_name, subscription_plans.name_fr as plan_name_fr')
            ->from('publisher_subscriptions')
            ->join('subscription_plans', 'subscription_plans.id = publisher_subscriptions.plan_id', 'left')
            ->where('publisher_subscriptions.publisher_id', $publisher_id)
            ->order_by('publisher_subscriptions.id', 'DESC')
            ->get()
            ->row_array();

        return $latest ?: null;
    }

    public function get_merchant_plan($publisher_id) {
        $active_sub = $this->get_active_plan($publisher_id);
        if ($active_sub && !empty($active_sub['plan_id'])) {
            $plan = $this->get_plan_by_id($active_sub['plan_id']);
            if ($plan) {
                $plan['subscription'] = $active_sub;
                return $plan;
            }
        }

        // Fallback to default Starter plan
        $starter = $this->get_default_starter_plan();
        if ($starter) {
            $starter['subscription'] = null;
        }
        return $starter;
    }

    public function can_use_flash_sales($publisher_id) {
        $plan = $this->get_merchant_plan($publisher_id);
        if (!$plan) return false;

        $plan_name = strtolower($plan['name'] ?? '');
        // Starter is strictly blocked from Flash Sales. Growth and Cyber have access.
        if ($plan_name === 'starter') {
            return false;
        }
        if (in_array($plan_name, ['growth', 'cyber'])) {
            return true;
        }

        // Also check plan_features mapping if custom feature is defined
        $this->db->select('pf.value');
        $this->db->from('plan_features pf');
        $this->db->join('subscription_features sf', 'sf.id = pf.feature_id');
        $this->db->where('pf.plan_id', $plan['id']);
        $this->db->like('LOWER(sf.feature_name)', 'flash sale');
        $row = $this->db->get()->row_array();

        if ($row) {
            $val = trim(strtolower($row['value']));
            if ($val === '-' || $val === 'no' || $val === '0' || empty($val)) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function can_use_daily_deals($publisher_id) {
        // Daily Deals is allowed on Starter, Growth, and Cyber
        return true;
    }

    public function get_base_flash_sale_limit($publisher_id) {
        if (!$this->can_use_flash_sales($publisher_id)) {
            return 0;
        }
        // Base allowance for Growth & Cyber: 3 products for seven days
        return 3;
    }

    public function get_base_daily_deals_limit($publisher_id) {
        // Base allowance for Starter, Growth & Cyber: 7 products (1 per day)
        return 7;
    }

    public function subscribe_plan($publisher_id, $plan_id, $ip = null) {
        $plan = $this->get_plan_by_id($plan_id);
        if (!$plan) return false;

        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($start_date)));
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $existing = $this->db->get_where('publisher_subscriptions', [
            'publisher_id' => $publisher_id,
            'status' => 'active'
        ])->row_array();

        if ($existing) {
            // Update existing active subscription to new plan
            $this->db->where('id', $existing['id'])->update('publisher_subscriptions', [
                'plan_id'    => $plan_id,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'ip'         => $ip,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $data = [
                'publisher_id' => $publisher_id,
                'plan_id'      => $plan_id,
                'status'       => 'active',
                'start_date'   => $start_date,
                'end_date'     => $end_date,
                'ip'           => $ip,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => NULL
            ];
            $this->db->insert('publisher_subscriptions', $data);
        }

        return true;
    }

    public function get_plan_by_id($plan_id) {
        return $this->db->get_where('subscription_plans', ['id' => $plan_id])->row_array();
    }

    // New helper: get order by id and publisher
    public function get_order_by_id($order_id, $publisher_id)
    {
        return $this->db->get_where('subscription_orders', ['id' => $order_id, 'publisher_id' => $publisher_id])->row_array();
    }

    // New helper: update order status
    public function update_order_status($order_id, $status) {
        return $this->db->where('id', $order_id)->update('subscription_orders', ['status' => $status]);
    }

    public function get_subscription_orders($seller_id)
    {
        return $this->db
            ->select('subscription_orders.*, publisher.publication_name, subscription_plans.name as plan_name')
            ->from('subscription_orders')
            ->join('publisher', 'publisher.id = subscription_orders.publisher_id')
            ->join('subscription_plans', 'subscription_plans.id = subscription_orders.plan_id')
            ->order_by('subscription_orders.id', 'DESC')
            ->where('subscription_orders.publisher_id', $seller_id)
            ->get()
            ->result_array();
    }
}

