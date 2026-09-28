<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Product_model extends CI_Model {



    public function __construct() {

        parent::__construct();

    }



    /* ==========================

       🚀 DAILY DEALS FUNCTIONS

       ========================== */

    // Auto-expire deals that have passed end time
    public function auto_expire_deals($current_time = null) {
        if (!$current_time) {
            $current_time = time();
        }
        $this->db->where('daily_deals', 1);
        $this->db->where('daily_deal_ends_at >', 0);
        $this->db->where('daily_deal_ends_at <=', $current_time);
        $this->db->update('products', ['daily_deals' => 0]);

        $this->db->where('flash_sale', 1);
        $this->db->where('flash_sale_ends_at >', 0);
        $this->db->where('flash_sale_ends_at <=', $current_time);
        $this->db->update('products', ['flash_sale' => 0]);
    }

    // ✅ Get daily deals products
    public function get_daily_deals($limit, $offset, $current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->select('p.*, 
            COALESCE(sp.special_price, var_sp.min_special_price) AS special_price, 
            sp.special_price_from, 
            sp.special_price_to,
            COALESCE(NULLIF(p.webshop_price, 0), var_sp.min_webshop_price) AS webshop_price');
        $this->db->from('products p');
        $this->db->join('products_special_prices sp', "sp.product_id = p.id AND sp.special_price_from <= $current_time AND sp.special_price_to >= $current_time", 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   MIN(vsp.special_price) AS min_special_price,
                   MIN(v.webshop_price) AS min_webshop_price
            FROM products v
            LEFT JOIN products_special_prices vsp 
                   ON vsp.product_id = v.id 
                  AND vsp.special_price_from <= $current_time 
                  AND vsp.special_price_to >= $current_time
            WHERE v.parent_id > 0
            GROUP BY v.parent_id
        ) var_sp", 'var_sp.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
        }

        // Daily deal active check - strictly active (> current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    // ✅ Count daily deals products
    public function count_daily_deals($current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->from('products p');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
        }

        // Daily deal active check - strictly active (> current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        return $this->db->count_all_results();
    }

    // ✅ Daily deal categories
    public function get_daily_deal_categories($current_time) {
        $this->auto_expire_deals($current_time);

        $this->db->distinct();
        $this->db->select('c.id, c.cat_name, c.parent_id');
        $this->db->from('category c');
        $this->db->join('products_category pc', 'pc.category_ids = c.id', 'inner');
        $this->db->join('products p', 'p.id = pc.product_id', 'inner');

        // Daily deal active check - strictly active (> current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);
        $this->db->where('c.status', 1);

        $this->db->order_by('c.cat_name', 'ASC');

        return $this->db->get()->result();
    }

    /* ==========================
       🚀 FLASH SALE FUNCTIONS
       ========================== */

    // ✅ Get flash sale products
    public function get_flash_sales($limit, $offset, $current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->select('p.*, 
            COALESCE(sp.special_price, var_sp.min_special_price) AS special_price, 
            sp.special_price_from, 
            sp.special_price_to,
            COALESCE(NULLIF(p.webshop_price, 0), var_sp.min_webshop_price) AS webshop_price');
        $this->db->from('products p');
        $this->db->join('products_special_prices sp', "sp.product_id = p.id AND sp.special_price_from <= $current_time AND sp.special_price_to >= $current_time", 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   MIN(vsp.special_price) AS min_special_price,
                   MIN(v.webshop_price) AS min_webshop_price
            FROM products v
            LEFT JOIN products_special_prices vsp 
                   ON vsp.product_id = v.id 
                  AND vsp.special_price_from <= $current_time 
                  AND vsp.special_price_to >= $current_time
            WHERE v.parent_id > 0
            GROUP BY v.parent_id
        ) var_sp", 'var_sp.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
        }

        // Flash sale active check - strictly active (> current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    // ✅ Count flash sale products (for pagination)
    public function count_flash_sales($current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->from('products p');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
        }

        // Flash sale active check - strictly active (> current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        return $this->db->count_all_results();
    }

    // ✅ Flash sale categories
    public function get_flash_sale_categories($current_time) {
        $this->auto_expire_deals($current_time);

        $this->db->distinct();
        $this->db->select('c.id, c.cat_name, c.parent_id');
        $this->db->from('category c');
        $this->db->join('products_category pc', 'pc.category_ids = c.id', 'inner');
        $this->db->join('products p', 'p.id = pc.product_id', 'inner');

        // Flash sale active check - strictly active (> current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);
        $this->db->where('c.status', 1);

        $this->db->order_by('c.cat_name', 'ASC');

        return $this->db->get()->result();
    }

    /* ==========================
        🔍 SEARCH & AUTOCOMPLETE
       ========================== */

    public function search_products_fuzzy($query, $limit = 10) {
    $this->db->select('p.id, p.product_name, p.product_sku, p.price, p.featured_image');
    $this->db->from('products p');
    
    $query = trim($query);
    $keywords = explode(' ', $query);
    
    $this->db->group_start();
    foreach ($keywords as $word) {
        if (strlen($word) >= 2) { // Changed from > 2 to >= 2 to catch short words
            // 'both' is the secret—it creates LIKE '%word%'
            $this->db->or_like('p.product_name', $word, 'both');
            $this->db->or_like('p.product_sku', $word, 'both');
        }
    }
    $this->db->group_end();

    $this->db->where('p.status', 1);
    $this->db->where('p.approval_status', 1);
    
    // This ordering ensures that "Rolls" still comes first if you type "Roll"
    $this->db->order_by("CASE 
        WHEN p.product_name LIKE '$query%' THEN 1 
        WHEN p.product_name LIKE '%$query%' THEN 2 
        ELSE 3 END", 'ASC');

    $this->db->limit($limit);   
    return $this->db->get()->result();
}




}

