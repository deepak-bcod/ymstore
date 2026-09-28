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

}

