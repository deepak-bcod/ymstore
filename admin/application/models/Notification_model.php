<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model
{
    protected $table = 'notifications';

    public function __construct()
    {
        parent::__construct();
    }

    // Insert a notification; $data is array matching table columns
    public function insert($data)
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Get notifications for a recipient (merchant/admin)
    // New param $only_unread (default false)
    public function get_for($recipient_type, $recipient_id, $limit = 50, $offset = 0, $only_unread = false)
{
    $this->db->from($this->table);
    
    // 1. Always filter by the specific role (Admin or Merchant)
    $this->db->where('recipient_type', $recipient_type);

    if ($recipient_type === 'admin') {
        if ($recipient_id !== null) {
            $this->db->group_start()
                     ->where('recipient_id', (int)$recipient_id)
                     ->or_where('recipient_id', 1)
                     ->or_where('recipient_id IS NULL', null, false)
                     ->group_end();
        } else {
            $this->db->group_start()
                     ->where('recipient_id', 1)
                     ->or_where('recipient_id IS NULL', null, false)
                     ->group_end();
        }
    } else {
        if ($recipient_id !== null) {
            $this->db->where('recipient_id', (int)$recipient_id);
        } else {
            return []; 
        }
    }

    if ($only_unread) {
        $this->db->where('is_read', 0);
    }

    $this->db->order_by('id', 'DESC');
    $this->db->limit($limit, $offset);
    
    $q = $this->db->get();
    $rows = $q->result_array();

    foreach ($rows as &$r) {
        $r['data'] = $r['data'] ? json_decode($r['data'], true) : null;
    }
    return $rows;
}

    // Get unread count
    public function unread_count($recipient_type, $recipient_id = null)
    {
        $this->db->from($this->table);
        $this->db->where('recipient_type', $recipient_type);
        $this->db->where('is_read', 0);

        if ($recipient_id !== null) {
            $this->db->group_start()
                    ->where('recipient_id', $recipient_id)
                    ->or_where('recipient_id IS NULL', null, false)
                    ->group_end();
        } else {
            $this->db->where('recipient_id IS NULL', null, false);
        }

        return (int) $this->db->count_all_results();
    }



    // Mark single notification read/unread (keeps previous behavior)
    public function mark_read($id, $is_read = 1)
    {
        $id = (int) $id;
        $is_read = (int) $is_read;

        if ($id <= 0) return false;

        $update = [
            'is_read' => $is_read ,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $id);
        $this->db->update($this->table, $update);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        $row = $this->db->select('is_read')->from($this->table)->where('id', $id)->get()->row_array();
        if ($row === null) {
            return false;
        }
        return ((int)$row['is_read'] === $is_read);
    }

    // Mark all unread for recipient_type + recipient_id (includes global)
    public function mark_all_read($recipient_type, $recipient_id = null)
    {
        $this->db->where('recipient_type', $recipient_type);

        if ($recipient_id !== null) {
            $this->db->group_start()
                     ->where('recipient_id', (int)$recipient_id)
                     ->or_where('recipient_id IS NULL', null, false)
                     ->group_end();
        } else {
            $this->db->where('recipient_id IS NULL', null, false);
        }

        $this->db->where('is_read', 0);

        $this->db->set('is_read', 1);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));

        $this->db->update($this->table);

        return (int) $this->db->affected_rows();
    }

    // Count unread (helper that matches mark_all_read conditions)
    public function get_unread_count($recipient_type, $recipient_id)
    {
        $this->db->where('recipient_type', $recipient_type);
        $this->db->group_start()
                 ->where('recipient_id', (int)$recipient_id)
                 ->or_where('recipient_id IS NULL', null, false)
                 ->group_end();
        $this->db->where('is_read', 0);
        return (int) $this->db->count_all_results($this->table);
    }
}
