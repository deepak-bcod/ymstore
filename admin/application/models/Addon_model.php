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
            COALESCE(NULLIF(p.amount, 0), MAX(txn.amount), s.final_price) as amount,
            MAX(res.order_number) as order_number,
            MAX(res.ticket_number) as ticket_number,
            MAX(txn.transaction_ref) as transaction_ref,
            pub.company_name as merchant_name
        ', FALSE);
        $this->db->from('merchant_addon_purchases p');
        $this->db->join('addon_services s', 's.id = p.service_id', 'left');
        $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
        $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
        $this->db->join('order_resolutions res', 'res.addon_purchase_id = p.id', 'left');
        $this->db->join('merchant_addon_transactions txn', 'txn.purchase_id = p.id', 'left');
        
        // Only show paid purchases in manage view
        $this->db->where_in('p.status', ['paid', 'success', 'completed']);
        $this->db->group_by('p.id');
        $this->db->order_by('p.id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    
public function get_purchase_by_id($purchase_id, $merchant_id = null)
{
    $this->db->select('
        p.id AS purchase_id,
        p.merchant_id,
        DATE_FORMAT(p.created_at, "%d %M %Y") AS date,
        p.status,
        c.name AS addon_service_name,
        s.title AS addon_name,
        s.price,
        s.final_price,
        s.vat_percent,
        COALESCE(NULLIF(p.amount, 0), MAX(txn.amount), s.final_price) AS amount,
        MAX(res.order_number) AS order_number,
        MAX(res.ticket_number) AS ticket_number,
        MAX(txn.transaction_ref) AS transaction_ref,
        COALESCE(NULLIF(pub.publication_name, \'\'), NULLIF(pub.company_name, \'\'), \'Merchant\') AS merchant_name,
        pub.email AS merchant_email,
        pub.brn_no AS merchant_brn,
        pub.company_address AS merchant_address,
        pub.phone_no AS merchant_phone,
        pub.landline_no AS merchant_landline
    ', FALSE);

    $this->db->from('merchant_addon_purchases p');

    $this->db->join('addon_services s', 's.id = p.service_id', 'left');
    $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
    $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
    $this->db->join('order_resolutions res', 'res.addon_purchase_id = p.id', 'left');
    $this->db->join('merchant_addon_transactions txn', 'txn.purchase_id = p.id', 'left');

    $this->db->where('p.id', $purchase_id);

    // Merchant ownership verification
    if ($merchant_id !== null) {
        $this->db->where('p.merchant_id', $merchant_id);
    }

    // Only paid purchases
    $this->db->where_in('p.status', ['paid', 'success', 'completed']);

    $this->db->group_by('p.id');

    $query = $this->db->get();

    return $query->row();
}


}
