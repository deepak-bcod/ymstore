<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Product_model extends CI_Model {



    public function __construct() {

        parent::__construct();

        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}



    /* ==========================

       ­ƒÜÇ DAILY DEALS FUNCTIONS

       ========================== */

    // Auto-expire deals that have passed end time and restore default special price
    public function auto_expire_deals($current_time = null) {
        if (!$current_time) {
            $current_time = time();
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // Ensure default_special_price column exists safely
        if (!$this->db->field_exists('default_special_price', 'products')) {
            @$this->db->query("ALTER TABLE products ADD COLUMN default_special_price DECIMAL(12,2) NULL DEFAULT NULL AFTER special_price");
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // 1. Expire daily deals (by time or out of stock)
        $exp_dd = $this->db->select('p.id, p.special_price, p.default_special_price')
            ->from('products p')
            ->join('products_inventory inv', 'inv.product_id = p.id', 'left')
            ->join("(
                SELECT v.parent_id, 
                       SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
                FROM products v
                JOIN products_inventory vi ON vi.product_id = v.id
                WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
                GROUP BY v.parent_id
            ) var_inv", 'var_inv.parent_id = p.id', 'left')
            ->where('p.daily_deals', 1)
            ->group_start()
                ->group_start()
                    ->where('p.daily_deal_ends_at >', 0)
                    ->where('p.daily_deal_ends_at <=', $current_time)
                ->group_end()
                ->or_where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) <= 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) <= 0))", null, false)
            ->group_end()
            ->get()->result();

        foreach ($exp_dd as $p) {
            if (!empty($p->default_special_price) && (float)$p->default_special_price > 0) {
                $this->db->where('id', $p->id)->update('products', [
                    'daily_deals' => 0,
                    'daily_deal_ends_at' => 0,
                    'special_price' => (float)$p->default_special_price
                ]);
                $this->db->where('product_id', $p->id)->update('products_special_prices', [
                    'special_price' => (float)$p->default_special_price,
                    'offer_type' => 'general'
                ]);
                /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

} else {
                $this->db->where('id', $p->id)->update('products', [
                    'daily_deals' => 0,
                    'daily_deal_ends_at' => 0,
                    'special_price' => null
                ]);
                $this->db->where('product_id', $p->id)->delete('products_special_prices');
                /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // 2. Expire flash sales (by time or out of stock)
        $exp_fs = $this->db->select('p.id, p.special_price, p.default_special_price')
            ->from('products p')
            ->join('products_inventory inv', 'inv.product_id = p.id', 'left')
            ->join("(
                SELECT v.parent_id, 
                       SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
                FROM products v
                JOIN products_inventory vi ON vi.product_id = v.id
                WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
                GROUP BY v.parent_id
            ) var_inv", 'var_inv.parent_id = p.id', 'left')
            ->where('p.flash_sale', 1)
            ->group_start()
                ->group_start()
                    ->where('p.flash_sale_ends_at >', 0)
                    ->where('p.flash_sale_ends_at <=', $current_time)
                ->group_end()
                ->or_where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) <= 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) <= 0))", null, false)
            ->group_end()
            ->get()->result();

        foreach ($exp_fs as $p) {
            if (!empty($p->default_special_price) && (float)$p->default_special_price > 0) {
                $this->db->where('id', $p->id)->update('products', [
                    'flash_sale' => 0,
                    'flash_sale_ends_at' => 0,
                    'special_price' => (float)$p->default_special_price
                ]);
                $this->db->where('product_id', $p->id)->update('products_special_prices', [
                    'special_price' => (float)$p->default_special_price,
                    'offer_type' => 'general'
                ]);
                /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

} else {
                $this->db->where('id', $p->id)->update('products', [
                    'flash_sale' => 0,
                    'flash_sale_ends_at' => 0,
                    'special_price' => null
                ]);
                $this->db->where('product_id', $p->id)->delete('products_special_prices');
                /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // 3. Clean up orphaned deal rows in products_special_prices
        if ($this->db->table_exists('products_special_prices')) {
            $orphans = $this->db->select('p.id, p.special_price, p.default_special_price, p.special_price_to, sp.id as sp_id, sp.special_price as sp_special_price, sp.offer_type, sp.special_price_to as sp_to')
                ->from('products p')
                ->join('products_special_prices sp', 'sp.product_id = p.id', 'inner')
                ->where('p.daily_deals', 0)
                ->where('p.flash_sale', 0)
                ->get()->result();

            foreach ($orphans as $row) {
                if (in_array($row->offer_type, ['daily_deal', 'flash_sale']) || empty($row->special_price) || (float)$row->special_price <= 0) {
                    $has_default = (!empty($row->default_special_price) && (float)$row->default_special_price > 0);
                    $to_valid = true;
                    if (!empty($row->special_price_to)) {
                        $to_ts = is_numeric($row->special_price_to) ? (int)$row->special_price_to : strtotime($row->special_price_to);
                        if ($to_ts > 0 && $to_ts < $current_time) {
                            $to_valid = false;
                            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
                        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

                    if ($has_default && $to_valid) {
                        $base_price = (float)$row->default_special_price;
                        $this->db->where('id', $row->id)->update('products', [
                            'special_price' => $base_price
                        ]);
                        $this->db->where('id', $row->sp_id)->update('products_special_prices', [
                            'special_price' => $base_price,
                            'offer_type'    => 'general',
                            'updated_at'    => $current_time
                        ]);
                        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

} else {
                        $this->db->where('id', $row->id)->update('products', [
                            'special_price'         => null,
                            'default_special_price' => null,
                            'special_price_from'    => null,
                            'special_price_to'      => null
                        ]);
                        $this->db->where('id', $row->sp_id)->delete('products_special_prices');
                        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
                    /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

} elseif (!empty($row->sp_to)) {
                    $sp_to_ts = is_numeric($row->sp_to) ? (int)$row->sp_to : strtotime($row->sp_to);
                    if ($sp_to_ts > 0 && $sp_to_ts < $current_time) {
                        $this->db->where('id', $row->id)->update('products', [
                            'special_price'         => null,
                            'default_special_price' => null,
                            'special_price_from'    => null,
                            'special_price_to'      => null
                        ]);
                        $this->db->where('id', $row->sp_id)->delete('products_special_prices');
                        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
                    /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
                /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    // Ô£à Get daily deals products (strictly stock > 0)
    public function get_daily_deals($limit, $offset, $current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->select('p.*, 
            COALESCE(sp.special_price, var_sp.min_special_price) AS special_price, 
            sp.special_price_from, 
            sp.special_price_to,
            COALESCE(NULLIF(p.webshop_price, 0), var_sp.min_webshop_price) AS webshop_price,
            COALESCE(var_inv.total_available_qty, inv.available_qty, inv.qty, 0) AS calculated_stock');
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
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // Daily deal active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->group_start();
            $this->db->where('p.daily_deal_starts_at IS NULL', null, false);
            $this->db->or_where('p.daily_deal_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from daily deals section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    // Ô£à Count daily deals products (strictly stock > 0)
    public function count_daily_deals($current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->from('products p');
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // Daily deal active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->group_start();
            $this->db->where('p.daily_deal_starts_at IS NULL', null, false);
            $this->db->or_where('p.daily_deal_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from daily deals section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        return $this->db->count_all_results();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    // Ô£à Daily deal categories (strictly categories with stock > 0)
    public function get_daily_deal_categories($current_time) {
        $this->auto_expire_deals($current_time);

        $this->db->distinct();
        $this->db->select('c.id, c.cat_name, c.parent_id');
        $this->db->from('category c');
        $this->db->join('products_category pc', 'pc.category_ids = c.id', 'inner');
        $this->db->join('products p', 'p.id = pc.product_id', 'inner');
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        // Daily deal active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.daily_deals', 1);
        $this->db->group_start();
            $this->db->where('p.daily_deal_starts_at IS NULL', null, false);
            $this->db->or_where('p.daily_deal_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.daily_deal_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);
        $this->db->where('c.status', 1);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from daily deals section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        $this->db->order_by('c.cat_name', 'ASC');

        return $this->db->get()->result();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    /* ==========================
       ­ƒÜÇ FLASH SALE FUNCTIONS
       ========================== */

    // Ô£à Get flash sale products (strictly stock > 0)
    public function get_flash_sales($limit, $offset, $current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->select('p.*, 
            COALESCE(sp.special_price, var_sp.min_special_price) AS special_price, 
            sp.special_price_from, 
            sp.special_price_to,
            COALESCE(NULLIF(p.webshop_price, 0), var_sp.min_webshop_price) AS webshop_price,
            COALESCE(var_inv.total_available_qty, inv.available_qty, inv.qty, 0) AS calculated_stock');
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
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // Flash sale active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->group_start();
            $this->db->where('p.flash_sale_starts_at IS NULL', null, false);
            $this->db->or_where('p.flash_sale_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from flash sales section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    // Ô£à Count flash sale products (strictly stock > 0)
    public function count_flash_sales($current_time, $category_id = null) {
        $this->auto_expire_deals($current_time);

        $this->db->from('products p');
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        if ($category_id) {
            $this->db->join('products_category pc', 'pc.product_id = p.id', 'inner');
            $this->db->where('pc.category_ids', $category_id);
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

        // Flash sale active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->group_start();
            $this->db->where('p.flash_sale_starts_at IS NULL', null, false);
            $this->db->or_where('p.flash_sale_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from flash sales section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        return $this->db->count_all_results();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    // Ô£à Flash sale categories (strictly categories with stock > 0)
    public function get_flash_sale_categories($current_time) {
        $this->auto_expire_deals($current_time);

        $this->db->distinct();
        $this->db->select('c.id, c.cat_name, c.parent_id');
        $this->db->from('category c');
        $this->db->join('products_category pc', 'pc.category_ids = c.id', 'inner');
        $this->db->join('products p', 'p.id = pc.product_id', 'inner');
        $this->db->join('products_inventory inv', 'inv.product_id = p.id', 'left');
        $this->db->join("(
            SELECT v.parent_id, 
                   SUM(COALESCE(vi.available_qty, vi.qty, 0)) AS total_available_qty
            FROM products v
            JOIN products_inventory vi ON vi.product_id = v.id
            WHERE v.parent_id > 0 AND v.status = 1 AND v.remove_flag = 0
            GROUP BY v.parent_id
        ) var_inv", 'var_inv.parent_id = p.id', 'left');

        // Flash sale active check - strictly active (starts_at <= current_time AND ends_at > current_time)
        $this->db->where('p.flash_sale', 1);
        $this->db->group_start();
            $this->db->where('p.flash_sale_starts_at IS NULL', null, false);
            $this->db->or_where('p.flash_sale_starts_at <=', $current_time);
        $this->db->group_end();
        $this->db->where('p.flash_sale_ends_at >', $current_time);
        $this->db->where('p.status', 1);
        $this->db->where('p.approval_status', 1);
        $this->db->where('p.remove_flag', 0);
        $this->db->where('c.status', 1);

        // Stock check: Must be in stock (Stock > 0). If Stock = 0, remove product from flash sales section
        $this->db->where("((p.product_type = 'configurable' AND COALESCE(var_inv.total_available_qty, 0) > 0) OR (p.product_type != 'configurable' AND COALESCE(inv.available_qty, inv.qty, 0) > 0))", null, false);

        $this->db->order_by('c.cat_name', 'ASC');

        return $this->db->get()->result();
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}

    /* ==========================
        ­ƒöì SEARCH & AUTOCOMPLETE
       ========================== */

    public function search_products_fuzzy($query, $limit = 10) {
    $this->db->select('p.id, p.product_name, p.product_sku, p.price, p.featured_image');
    $this->db->from('products p');
    
    $query = trim($query);
    $keywords = explode(' ', $query);
    
    $this->db->group_start();
    foreach ($keywords as $word) {
        if (strlen($word) >= 2) { // Changed from > 2 to >= 2 to catch short words
            // 'both' is the secretÔÇöit creates LIKE '%word%'
            $this->db->or_like('p.product_name', $word, 'both');
            $this->db->or_like('p.product_sku', $word, 'both');
            /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
        /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
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
    /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}





    /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }
    /* ==========================
        ⭐ PRODUCT REVIEW METHODS (STRICTLY PUBLISHED)
       ========================== */

    /**
     * Get review summary (avg_rating, total_reviews, featured_testimony) for a product
     * Only calculates and includes reviews where status = 'published'
     */
    public function get_product_review_summary($product_id) {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return (object)[
                'avg_rating'      => 0,
                'total_reviews'   => 0,
                'featured_review' => null
            ];
        }

        $p_id = (int)$product_id;

        // Calculate average rating and total review count
        $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
        $this->db->from('ym_product_reviews');
        $this->db->where('product_id', $p_id);
        $this->db->where('status', 'published');
        $row = $this->db->get()->row();

        $avg = (!empty($row) && $row->avg_rating !== null) ? round((float)$row->avg_rating, 1) : 0;
        $total = (!empty($row) && $row->total_reviews !== null) ? (int)$row->total_reviews : 0;

        // If parent product has 0 direct reviews, check if variant children have published reviews
        if ($total === 0) {
            $this->db->select('AVG(rating) as avg_rating, COUNT(id) as total_reviews');
            $this->db->from('ym_product_reviews');
            $this->db->where("product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
            $this->db->where('status', 'published');
            $v_row = $this->db->get()->row();
            if (!empty($v_row) && (int)$v_row->total_reviews > 0) {
                $avg = round((float)$v_row->avg_rating, 1);
                $total = (int)$v_row->total_reviews;
            }
        }

        // Fetch top featured testimony (highest rating with comment)
        $this->db->select("
            pr.id,
            pr.rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');
        $this->db->where('pr.comments IS NOT NULL', NULL, FALSE);
        $this->db->where("TRIM(pr.comments) != ''", NULL, FALSE);
        $this->db->order_by('pr.rating', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $this->db->limit(1);
        $featured = $this->db->get()->row();

        return (object)[
            'avg_rating'      => $avg,
            'total_reviews'   => $total,
            'featured_review' => $featured
        ];
    }

    /**
     * Get published review records for a product
     */
    public function get_product_published_reviews($product_id, $limit = 50, $offset = 0, $sort = 'newest') {
        if (empty($product_id) || !$this->db->table_exists('ym_product_reviews')) {
            return [];
        }

        $p_id = (int)$product_id;

        $this->db->select("
            pr.id,
            pr.product_id,
            pr.product_name,
            pr.rating AS item_rating,
            COALESCE(mr.rating, pr.rating) AS service_rating,
            pr.comments,
            pr.created_at,
            COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
            c.email_id AS customer_email
        ");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('ym_merchant_reviews mr', 'mr.id = pr.review_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->group_start();
            $this->db->where('pr.product_id', $p_id);
            $this->db->or_where("pr.product_id IN (SELECT id FROM products WHERE parent_id = {$p_id})", NULL, FALSE);
        $this->db->group_end();
        $this->db->where('pr.status', 'published');

        if ($sort === 'highest') {
            $this->db->order_by('pr.rating', 'DESC');
            $this->db->order_by('pr.id', 'DESC');
        } elseif ($sort === 'lowest') {
            $this->db->order_by('pr.rating', 'ASC');
            $this->db->order_by('pr.id', 'DESC');
        } else {
            $this->db->order_by('pr.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

}
