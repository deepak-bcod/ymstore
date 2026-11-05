<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Addon_model extends CI_Model {

    public function get_addons_with_services()
    {
        $this->db->select('ac.id as cat_id, ac.name as cat_name, ac.name_fr as cat_name_fr, as.id as service_id, as.title as service_title, as.title_fr as service_title_fr, as.description as service_desc, as.description_fr as service_desc_fr, as.final_price, as.image');
        $this->db->from('addon_categories ac');
        $this->db->join('addon_services as', 'ac.id = as.category_id', 'left');
        $this->db->where('ac.status', 1);
        $this->db->where('as.status', 1);
        $this->db->order_by('ac.id', 'ASC');
        $this->db->order_by('as.id', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function get_service_by_id($service_id)
    {
        $this->db->select('as.*, ac.name as category_name, ac.name_fr as category_name_fr');
        $this->db->from('addon_services as');
        $this->db->join('addon_categories ac', 'ac.id = as.category_id', 'left');
        $this->db->where('as.id', $service_id);
        $this->db->where('as.status', 1);

        $query = $this->db->get();
        return $query->row();
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

        // Only show entries that have completed payment successfully
        $this->db->where_in('p.status', ['paid', 'success', 'completed']);

        // Filter to show only the currently logged-in merchant\'s purchases
        if ($merchant_id) {
            $this->db->where('p.merchant_id', $merchant_id);
        }

        $this->db->group_by('p.id');
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
            COALESCE(NULLIF(p.amount, 0), MAX(txn.amount), s.final_price) as amount,
            MAX(res.order_number) as order_number,
            MAX(res.ticket_number) as ticket_number,
            MAX(txn.transaction_ref) as transaction_ref,
            COALESCE(NULLIF(pub.publication_name, \'\'), NULLIF(pub.company_name, \'\'), \'Merchant\') as merchant_name,
            pub.email as merchant_email
        ', FALSE);
        $this->db->from('merchant_addon_purchases p');
        $this->db->join('addon_services s', 's.id = p.service_id', 'left');
        $this->db->join('addon_categories c', 'c.id = s.category_id', 'left');
        $this->db->join('publisher pub', 'pub.id = p.merchant_id', 'left');
        $this->db->join('order_resolutions res', 'res.addon_purchase_id = p.id', 'left');
        $this->db->join('merchant_addon_transactions txn', 'txn.purchase_id = p.id', 'left');
        $this->db->where('p.id', $purchase_id);

        // Only generate invoices for entries that have completed payment successfully
        $this->db->where_in('p.status', ['paid', 'success', 'completed']);
        $this->db->group_by('p.id');
        
        $query = $this->db->get();
        return $query->row();
    }

}
