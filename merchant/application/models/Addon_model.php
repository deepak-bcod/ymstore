<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Addon_model extends CI_Model {

    public function get_addons_with_services() {
        $this->db->select('c.id as category_id, c.name as category_name, c.name_fr as category_name_fr, 
                           s.id as service_id, s.title, s.title_fr, s.description, s.final_price, s.image');
        
        $this->db->from('addon_categories c');
        $this->db->join('addon_services s', 's.category_id = c.id', 'left');
        $this->db->where('c.status', 1);
        $this->db->where('s.status', 1);
        $this->db->order_by('c.id', 'ASC');
        $this->db->order_by('s.id', 'ASC');
        
        $query = $this->db->get();
        return $query->result(); // Changed from result_array() to result()
    }

  public function get_merchant_addons() {
    $this->db->select('o.order_id, o.date, o.amount, o.status, m.merchant_name, s.title as addon_service_name');
    $this->db->from('addon_orders o'); // Replace with your actual orders table
    $this->db->join('merchants m', 'm.id = o.merchant_id', 'left');
    $this->db->join('addon_services s', 's.id = o.service_id', 'left');
    
    // If you want to filter by the currently logged-in merchant, uncomment below:
    // $this->db->where('o.merchant_id', $this->session->userdata('merchant_id'));
    
    $query = $this->db->get();
    return $query->result(); // Returns objects for $row-> property syntax
}
}