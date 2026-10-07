<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotificationModel extends CI_Model
{
    protected $table = 'notifications';


    /**
     * Latest 3 notifications
     */
    public function get_latest_notifications($customer_id, $limit = 3)
    {
        return $this->db
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customer_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->table)
            ->result();
    }


    /**
     * All notifications
     */
    public function get_customer_notifications($customer_id)
    {
        return $this->db
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customer_id)
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }


    /**
     * Unread count
     */
    public function get_unread_count($customer_id)
    {
        return $this->db
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customer_id)
            ->where('is_read', 0)
            ->count_all_results($this->table);
    }


    /**
     * Mark notification read
     */
    public function mark_as_read($notification_id, $customer_id)
    {
        return $this->db
            ->where('id', $notification_id)
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customer_id)
            ->update($this->table, [
                'is_read' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
