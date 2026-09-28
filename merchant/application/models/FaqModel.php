<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FaqModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        // Connect to your ysm database configuration
        $this->load->database();
    }

    
    public function get_visible_faqs($type = NULL) {
        $this->db->select('*');
        $this->db->from('faqs');
        
        // CRITICAL FIXES: 
        $this->db->where('status', 1);
        $this->db->group_start()
            ->group_start()
                ->where('answer IS NOT NULL', null, false)
                ->where('answer !=', '')
            ->group_end()
            ->or_group_start()
                ->where('answer_fr IS NOT NULL', null, false)
                ->where('answer_fr !=', '')
            ->group_end()
            ->group_end();          

        
        if ($type !== NULL) {
            $this->db->where('faq_type', $type);
        }

        $this->db->order_by('id', 'DESC'); // Show newest answered questions first
        
        $query = $this->db->get();
        
        
        return $query->result_array();
    }

    public function insert_faq($data) {
        return $this->db->insert('faqs', $data);
    }
    public function get_merchant_own_faqs($email, $type = 'Merchant') {
        $this->db->select('*');
        $this->db->from('faqs');
        $this->db->where('faq_type', 'Merchant');
        $this->db->where('status', 1);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
    
}