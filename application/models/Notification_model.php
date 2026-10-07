<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function get_unread_count($user_id) {
        return $this->db->where('merchant_id', $user_id) // Change column name if needed (e.g., user_id or customer_id)
                        ->where('is_read', 0)
                        ->count_all_results('notifications');
    }

    public function get_latest_notifications($user_id, $limit = 3) {
        return $this->db->where('merchant_id', $user_id)
                        ->order_by('id', 'DESC')
                        ->limit($limit)
                        ->get('notifications')
                        ->result();
    }

    public function get_notifications($user_id) {
        return $this->db->where('merchant_id', $user_id)
                        ->order_by('id', 'DESC')
                        ->get('notifications')
                        ->result();
    }

    public function mark_as_read($id) {
        return $this->db->where('id', $id)
                        ->update('notifications', ['is_read' => 1]);
    }

    public function mark_all_as_read($user_id) {
        return $this->db->where('merchant_id', $user_id)
                        ->update('notifications', ['is_read' => 1]);
    }
}