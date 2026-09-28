<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Subscription_model extends CI_Model {



    // Plans

    public function get_plans() {

        return $this->db->get('subscription_plans')->result_array();

    }



    public function get_plan($id) {

        return $this->db->get_where('subscription_plans', ['id'=>$id])->row_array();

    }



    public function add_plan($data) {

        return $this->db->insert('subscription_plans', $data);

    }



    public function update_plan($id, $data) {

        return $this->db->update('subscription_plans', $data, ['id'=>$id]);

    }



    public function delete_plan($id) {

        return $this->db->delete('subscription_plans', ['id'=>$id]);

    }



    // Features

    public function get_features() {
        return $this->db->order_by('sort_order', 'ASC')->get('subscription_features')->result_array();
    }



    public function get_feature($id) {

        return $this->db->get_where('subscription_features', ['id'=>$id])->row_array();

    }



    public function add_feature($data) {

        return $this->db->insert('subscription_features', $data);

    }



    public function update_feature($id, $data) {

        return $this->db->update('subscription_features', $data, ['id'=>$id]);

    }



    public function delete_feature($id) {

        return $this->db->delete('subscription_features', ['id'=>$id]);

    }



    // Plan Features Mapping

    public function get_plan_features() {

        return $this->db->get('plan_features')->result_array();

    }



    public function save_plan_features($features) {

        foreach($features as $feature_id => $plans) {

            foreach($plans as $plan_id => $value) {

                $exists = $this->db->get_where('plan_features', ['plan_id'=>$plan_id, 'feature_id'=>$feature_id])->row_array();

                if($exists) {

                    $this->db->update('plan_features', ['value'=>$value], ['id'=>$exists['id']]);

                } else {

                    $this->db->insert('plan_features', ['plan_id'=>$plan_id, 'feature_id'=>$feature_id, 'value'=>$value]);

                }

            }

        }

    }

     public function get_plan_by_id($plan_id) {
        return $this->db->get_where('subscription_plans', ['id' => $plan_id])->row_array();
    }

    public function get_order_by_id($order_id)
    {
        return $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();
    }

    public function get_subscription_orders()
    {
        return $this->db
            ->select('subscription_orders.*, publisher.publication_name, subscription_plans.name as plan_name')
            ->from('subscription_orders')
            ->join('publisher', 'publisher.id = subscription_orders.publisher_id')
            ->join('subscription_plans', 'subscription_plans.id = subscription_orders.plan_id')
            ->order_by('subscription_orders.id', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_active_subscription($publisher_id)
    {
        return $this->db
            ->select('publisher_subscriptions.*, subscription_plans.name as plan_name')
            ->from('publisher_subscriptions')
            ->join('subscription_plans', 'subscription_plans.id = publisher_subscriptions.plan_id', 'left')
            ->where('publisher_subscriptions.publisher_id', $publisher_id)
            ->where('publisher_subscriptions.status', 'active')
            ->order_by('publisher_subscriptions.id', 'DESC')
            ->get()
            ->row_array();
    }

    public function assign_or_update_plan($publisher_id, $plan_id, $ip = null)
    {
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
}

