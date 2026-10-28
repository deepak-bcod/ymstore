<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Addon_model extends CI_Model {

    public function get_addons_with_services()
    {
        $this->db->select('c.id as category_id, c.name as category_name, c.name_fr as category_name_fr, 
                           s.id as service_id, s.title, s.title_fr, s.description, s.final_price, s.image');
        
        $this->db->from('addon_categories c');
        $this->db->join('addon_services s', 's.category_id = c.id', 'left');
        $this->db->where('c.status', 1);
        $this->db->where('s.status', 1);
        $this->db->order_by('c.id', 'ASC');
        $this->db->order_by('s.id', 'ASC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_service_by_id($service_id)
    {
        $this->db->select('s.*, c.name as category_name');
        $this->db->from('addon_services s');
        $this->db->join('addon_categories c', 's.category_id = c.id', 'left');
        $this->db->where('s.id', $service_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_merchant_addons() {
        // Get the currently logged-in merchant ID from the session
        $merchant_id = $this->session->userdata('LoginID');
        $site_lang = $this->session->userdata('site_lang');
        $is_french = ($site_lang === 'french');

        $this->db->select('
            p.id as purchase_id,
            p.merchant_id,
            DATE_FORMAT(p.created_at, "%d-%m-%Y %H:%i:%s") as date,
            p.status,
            ' . ($is_french ? 'COALESCE(NULLIF(c.name_fr, \'\'), c.name)' : 'c.name') . ' as addon_service_name,
            ' . ($is_french ? 'COALESCE(NULLIF(s.title_fr, \'\'), s.title)' : 's.title') . ' as addon_name,
            s.final_price as amount,
            pub.company_name as merchant_name
        ', FALSE);
        $this->db->from('merchant_addon_purchases p');
        $this->db->join('addon_services s', 's.id = p.service_id', 'left');
        $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
        $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');

        // Only show entries that have completed payment successfully
        $this->db->where_in('p.status', ['paid', 'success', 'completed']);

        // Filter to show only the currently logged-in merchant\'s purchases
        if ($merchant_id) {
            $this->db->where('p.merchant_id', $merchant_id);
        }

        $this->db->order_by('p.id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_purchase_by_id($purchase_id) 
    {
        $site_lang = $this->session->userdata('site_lang');
        $is_french = ($site_lang === 'french');

        $this->db->select('
            p.id as purchase_id,
            p.merchant_id,
            DATE_FORMAT(p.created_at, "%d-%m-%Y %H:%i:%s") as date,
            p.status,
            ' . ($is_french ? 'COALESCE(NULLIF(c.name_fr, \'\'), c.name)' : 'c.name') . ' as addon_service_name,
            ' . ($is_french ? 'COALESCE(NULLIF(s.title_fr, \'\'), s.title)' : 's.title') . ' as addon_name,
            s.price,
            s.vat_percent,
            s.final_price as amount,
            COALESCE(NULLIF(pub.publication_name, \'\'), NULLIF(pub.company_name, \'\'), \'Merchant\') as merchant_name,
            pub.email as merchant_email
        ', FALSE);
        $this->db->from('merchant_addon_purchases p');
        $this->db->join('addon_services s', 's.id = p.service_id', 'left');
        $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
        $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
        $this->db->where('p.id', $purchase_id);

        // Only generate invoices for entries that have completed payment successfully
        $this->db->where_in('p.status', ['paid', 'success', 'completed']);
        
        $query = $this->db->get();
        return $query->row();
    }

}