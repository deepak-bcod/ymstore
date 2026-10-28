<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Addon_model extends CI_Model {



    private $table = 'addon_services';



    // Get all addons with category name

    public function get_all() {

        $this->db->select('addon_services.*, addon_categories.name as category_name');

        $this->db->from($this->table);

        $this->db->join('addon_categories', 'addon_categories.id = addon_services.category_id', 'left'); // ✅ use category_id

        $this->db->order_by('addon_services.id', 'DESC');

        return $this->db->get()->result();

    }



    public function get($id) {

        return $this->db->where('id', $id)->get($this->table)->row();

    }



    public function insert($data) {
        $insert_data = [
            'category_id'    => $data['category_id'],
            'title'          => $data['title'],
            'description'    => $data['description'],
            'title_fr'       => $data['title_fr'],
            'description_fr' => $data['description_fr'],
            'price'          => $data['price'],
            'vat_percent'    => $data['vat_percent'],
            'final_price'    => $data['final_price'],
            'status'         => $data['status'],
            'ip'             => $this->input->ip_address(),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        // ✅ FIX: Save image if it exists in the data array
        if (!empty($data['image'])) {
            $insert_data['image'] = $data['image'];
        } else {
            $insert_data['image'] = ''; 
        }

        return $this->db->insert($this->table, $insert_data);
    }

    public function update($id, $data) {
        $update_data = [
            'category_id'    => $data['category_id'],
            'title'          => $data['title'],
            'description'    => $data['description'],
            'title_fr'       => $data['title_fr'],
            'description_fr' => $data['description_fr'],
            'price'          => $data['price'],
            'vat_percent'    => $data['vat_percent'],
            'final_price'    => $data['final_price'],
            'status'         => $data['status'],
            'ip'             => $this->input->ip_address(),
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        // ✅ FIX: Only update image if a new one was uploaded
        if (isset($data['image']) && !empty($data['image'])) {
            $update_data['image'] = $data['image'];
        }

        return $this->db->where('id', $id)->update($this->table, $update_data);
    }



    public function delete($id) {

        return $this->db->where('id', $id)->delete($this->table);

    }
    public function get_all_merchant_purchases() 
{
    $this->db->select('
        p.id as purchase_id,
        DATE_FORMAT(p.created_at, "%d-%m-%Y %H:%i:%s") as date,
        p.status,
        c.name as addon_service_name,
        s.title as addon_name,
        s.final_price as amount,
        pub.company_name as merchant_name
    ');
    $this->db->from('merchant_addon_purchases p');
    $this->db->join('addon_services s', 's.id = p.service_id', 'left');
    $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
    $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
    
  
    $this->db->order_by('p.id', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

    public function get_purchase_by_id($purchase_id) 
    {
        $this->db->select('
            p.id as purchase_id,
            DATE_FORMAT(p.created_at, "%d-%m-%Y %H:%i:%s") as date,
            p.status,
            c.name as addon_service_name,
            s.title as addon_name,
            s.price,
            s.vat_percent,
            s.final_price as amount,
            COALESCE(NULLIF(pub.publication_name, \'\'), NULLIF(pub.company_name, \'\'), \'Merchant\') as merchant_name,
            pub.email as merchant_email
        ');
        $this->db->from('merchant_addon_purchases p');
        $this->db->join('addon_services s', 's.id = p.service_id', 'left');
        $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
        $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
        $this->db->where('p.id', $purchase_id);
        
        $query = $this->db->get();
        return $query->row();
    }

}

