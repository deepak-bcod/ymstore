<?php
class ProductReviewModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_ensure_schema_compatibility();
    }

    /**
     * Auto-migrate missing columns or tables safely
     */
    private function _ensure_schema_compatibility()
    {
        if ($this->db->table_exists('ym_product_reviews')) {
            if (!$this->db->field_exists('status', 'ym_product_reviews')) {
                @$this->db->query("ALTER TABLE `ym_product_reviews` ADD COLUMN `status` ENUM('pending', 'published', 'rejected') NOT NULL DEFAULT 'pending' AFTER `comments`");
                @$this->db->query("ALTER TABLE `ym_product_reviews` ADD INDEX `idx_product_status` (`product_id`, `status`)");
            }
        }
        if ($this->db->table_exists('publisher')) {
            if (!$this->db->field_exists('is_badge_enabled', 'publisher')) {
                @$this->db->query("ALTER TABLE `publisher` ADD COLUMN `is_badge_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`");
            }
        }
    }

    /**
     * Build base SQL subquery for unified reviews
     */
    private function _build_base_query($search_param = array(), $status_filter = '', $type_filter = '')
    {
        $merchant_sql = "
            SELECT 
                'merchant' AS review_type,
                mr.id AS review_id,
                mr.id AS parent_review_id,
                mr.order_id,
                mr.B2B_order_id,
                mr.merchant_id,
                COALESCE(pub.publication_name, pub.company_name, pub.trade_name, 'Merchant') AS target_name,
                pub.id AS target_id,
                mr.customer_id,
                COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
                COALESCE(c.email_id, bo.customer_email, '') AS customer_email,
                mr.rating,
                mr.comments,
                mr.status,
                mr.created_at
            FROM ym_merchant_reviews mr
            LEFT JOIN publisher pub ON pub.id = mr.merchant_id
            LEFT JOIN customers c ON c.id = mr.customer_id
            LEFT JOIN b2b_orders bo ON bo.order_id = mr.order_id
        ";

        $has_pr_status = $this->db->field_exists('status', 'ym_product_reviews');
        $pr_status_expr = $has_pr_status ? "COALESCE(pr.status, mr.status, 'pending') AS status" : "COALESCE(mr.status, 'pending') AS status";

        $product_sql = "
            SELECT 
                'product' AS review_type,
                pr.id AS review_id,
                COALESCE(pr.review_id, 0) AS parent_review_id,
                COALESCE(mr.order_id, 0) AS order_id,
                COALESCE(mr.B2B_order_id, '') AS B2B_order_id,
                COALESCE(mr.merchant_id, 0) AS merchant_id,
                COALESCE(pr.product_name, p.name, 'Product') AS target_name,
                pr.product_id AS target_id,
                COALESCE(mr.customer_id, 0) AS customer_id,
                COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
                COALESCE(c.email_id, bo.customer_email, '') AS customer_email,
                pr.rating,
                pr.comments,
                " . $pr_status_expr . ",
                pr.created_at
            FROM ym_product_reviews pr
            LEFT JOIN ym_merchant_reviews mr ON mr.id = pr.review_id
            LEFT JOIN products p ON p.id = pr.product_id
            LEFT JOIN publisher pub ON pub.id = mr.merchant_id
            LEFT JOIN customers c ON c.id = mr.customer_id
            LEFT JOIN b2b_orders bo ON bo.order_id = mr.order_id
        ";

        if ($type_filter === 'merchant') {
            $inner = $merchant_sql;
        } elseif ($type_filter === 'product') {
            $inner = $product_sql;
        } else {
            $inner = $merchant_sql . " UNION ALL " . $product_sql;
        }

        $sql = "SELECT * FROM (" . $inner . ") AS all_reviews WHERE 1=1";

        if (!empty($status_filter) && $status_filter !== 'all') {
            $sql .= " AND status = " . $this->db->escape($status_filter);
        }

        if (!empty($search_param['keyword'])) {
            $kw = $this->db->escape_like_str($search_param['keyword']);
            $sql .= " AND (
                target_name LIKE '%" . $kw . "%' 
                OR customer_name LIKE '%" . $kw . "%' 
                OR customer_email LIKE '%" . $kw . "%' 
                OR comments LIKE '%" . $kw . "%' 
                OR B2B_order_id LIKE '%" . $kw . "%'
            )";
        }

        return $sql;
    }

    public function getReviewsList($page = 0, $limit = 50, $search_param = array(), $status_filter = '', $type_filter = '')
    {
        $sql = $this->_build_base_query($search_param, $status_filter, $type_filter);
        $sql .= " ORDER BY created_at DESC";

        if (!empty($limit) && (int)$limit > 0) {
            $sql .= " LIMIT " . (int)$page . ", " . (int)$limit;
        }

        $query = $this->db->query($sql);
        return $query ? $query->result_array() : array();
    }

    public function getReviewsCount($search_param = array(), $status_filter = '', $type_filter = '')
    {
        $base_sql = $this->_build_base_query($search_param, $status_filter, $type_filter);
        $count_sql = "SELECT COUNT(*) as total FROM (" . $base_sql . ") AS counted_reviews";
        $query = $this->db->query($count_sql);
        if ($query && $row = $query->row()) {
            return (int)$row->total;
        }
        return 0;
    }

    public function getStatusCounts()
    {
        $counts = array(
            'all'                => 0,
            'pending'            => 0,
            'published'          => 0,
            'rejected'           => 0,
            'merchant'           => 0,
            'product'            => 0,
            'merchant_all'       => 0,
            'merchant_pending'   => 0,
            'merchant_published' => 0,
            'merchant_rejected'  => 0,
            'product_all'        => 0,
            'product_pending'    => 0,
            'product_published'  => 0,
            'product_rejected'   => 0,
        );

        // Counts from ym_merchant_reviews
        $q1 = $this->db->query("SELECT status, COUNT(*) as cnt FROM ym_merchant_reviews GROUP BY status");
        if ($q1) {
            foreach ($q1->result_array() as $r) {
                $st = strtolower($r['status']);
                if (isset($counts[$st])) {
                    $counts[$st] += (int)$r['cnt'];
                }
                if (isset($counts['merchant_' . $st])) {
                    $counts['merchant_' . $st] += (int)$r['cnt'];
                }
                $counts['all'] += (int)$r['cnt'];
                $counts['merchant'] += (int)$r['cnt'];
                $counts['merchant_all'] += (int)$r['cnt'];
            }
        }

        // Counts from ym_product_reviews
        if ($this->db->field_exists('status', 'ym_product_reviews')) {
            $q2 = $this->db->query("SELECT status, COUNT(*) as cnt FROM ym_product_reviews GROUP BY status");
            if ($q2) {
                foreach ($q2->result_array() as $r) {
                    $st = strtolower($r['status']);
                    if (isset($counts[$st])) {
                        $counts[$st] += (int)$r['cnt'];
                    }
                    if (isset($counts['product_' . $st])) {
                        $counts['product_' . $st] += (int)$r['cnt'];
                    }
                    $counts['all'] += (int)$r['cnt'];
                    $counts['product'] += (int)$r['cnt'];
                    $counts['product_all'] += (int)$r['cnt'];
                }
            }
        } else {
            // Safe fallback if status column is not present yet
            $q2 = $this->db->query("SELECT COUNT(*) as cnt FROM ym_product_reviews");
            if ($q2 && $r = $q2->row()) {
                $counts['all'] += (int)$r->cnt;
                $counts['product'] += (int)$r->cnt;
                $counts['product_all'] += (int)$r->cnt;
                $counts['pending'] += (int)$r->cnt;
                $counts['product_pending'] += (int)$r->cnt;
            }
        }

        return $counts;
    }

    public function updateReviewStatus($type, $id, $status)
    {
        $allowed = array('pending', 'published', 'rejected');
        if (!in_array($status, $allowed)) {
            return false;
        }

        if ($type === 'merchant') {
            $this->db->where('id', (int)$id);
            return $this->db->update('ym_merchant_reviews', array(
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ));
        } elseif ($type === 'product') {
            if (!$this->db->field_exists('status', 'ym_product_reviews')) {
                @$this->db->query("ALTER TABLE `ym_product_reviews` ADD COLUMN `status` ENUM('pending', 'published', 'rejected') NOT NULL DEFAULT 'pending' AFTER `comments`");
            }
            if ($this->db->field_exists('status', 'ym_product_reviews')) {
                $this->db->where('id', (int)$id);
                return $this->db->update('ym_product_reviews', array(
                    'status'     => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ));
            }
            return false;
        }
        return false;
    }

    public function getOrderReviewDetails($parent_review_id)
    {
        // 1. Fetch merchant review
        $this->db->select("mr.*, pub.publication_name, pub.company_name, pub.trade_name, pub.shop_image,
                           COALESCE(CONCAT(c.first_name, ' ', c.last_name), CONCAT(bo.customer_firstname, ' ', bo.customer_lastname), 'Shopper') AS customer_name,
                           COALESCE(c.email_id, bo.customer_email, '') AS customer_email");
        $this->db->from('ym_merchant_reviews mr');
        $this->db->join('publisher pub', 'pub.id = mr.merchant_id', 'left');
        $this->db->join('customers c', 'c.id = mr.customer_id', 'left');
        $this->db->join('b2b_orders bo', 'bo.order_id = mr.order_id', 'left');
        $this->db->where('mr.id', (int)$parent_review_id);
        $merchant_review = $this->db->get()->row_array();

        if (empty($merchant_review)) {
            return null;
        }

        // 2. Fetch associated product reviews
        $this->db->select("pr.*, p.name as catalog_product_name");
        $this->db->from('ym_product_reviews pr');
        $this->db->join('products p', 'p.id = pr.product_id', 'left');
        $this->db->where('pr.review_id', (int)$parent_review_id);
        $this->db->order_by('pr.id', 'ASC');
        $product_reviews = $this->db->get()->result_array();

        return array(
            'merchant_review' => $merchant_review,
            'product_reviews' => $product_reviews
        );
    }

    public function deleteReview($type, $id)
    {
        if ($type === 'merchant') {
            $this->db->where('id', (int)$id);
            return $this->db->delete('ym_merchant_reviews');
        } elseif ($type === 'product') {
            $this->db->where('id', (int)$id);
            return $this->db->delete('ym_product_reviews');
        }
        return false;
    }

    /**
     * Get paginated merchants list with ratings and badge status
     */
    public function getMerchantBadgeList($start = 0, $limit = 50, $search_param = array())
    {
        $this->_ensure_schema_compatibility();
        $has_col = $this->db->field_exists('is_badge_enabled', 'publisher');
        $badge_col = $has_col ? "COALESCE(pub.is_badge_enabled, 1) AS is_badge_enabled" : "1 AS is_badge_enabled";

        $sql = "
            SELECT 
                pub.id AS merchant_id,
                pub.publication_name,
                pub.vendor_name,
                pub.email,
                pub.phone_no,
                pub.status AS merchant_status,
                {$badge_col},
                COUNT(mr.id) AS total_published_reviews,
                COALESCE(AVG(mr.rating), 0) AS avg_rating
            FROM publisher pub
            LEFT JOIN ym_merchant_reviews mr 
                ON mr.merchant_id = pub.id 
                AND LOWER(mr.status) = 'published'
            WHERE (pub.remove_flag = 0 OR pub.remove_flag IS NULL)
        ";

        if (!empty($search_param['keyword'])) {
            $kw = $this->db->escape_like_str($search_param['keyword']);
            $sql .= " AND (
                pub.publication_name LIKE '%{$kw}%' 
                OR pub.vendor_name LIKE '%{$kw}%' 
                OR pub.email LIKE '%{$kw}%' 
                OR pub.phone_no LIKE '%{$kw}%' 
                OR pub.id = '{$kw}'
            )";
        }

        $sql .= " GROUP BY pub.id ORDER BY pub.id DESC";

        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$start . ", " . (int)$limit;
        }

        $query = $this->db->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Get total count of merchants matching search
     */
    public function getMerchantBadgeCount($search_param = array())
    {
        $sql = "
            SELECT COUNT(*) AS total 
            FROM publisher pub 
            WHERE (pub.remove_flag = 0 OR pub.remove_flag IS NULL)
        ";

        if (!empty($search_param['keyword'])) {
            $kw = $this->db->escape_like_str($search_param['keyword']);
            $sql .= " AND (
                pub.publication_name LIKE '%{$kw}%' 
                OR pub.vendor_name LIKE '%{$kw}%' 
                OR pub.email LIKE '%{$kw}%' 
                OR pub.phone_no LIKE '%{$kw}%' 
                OR pub.id = '{$kw}'
            )";
        }

        $query = $this->db->query($sql);
        if ($query && $row = $query->row()) {
            return (int)$row->total;
        }
        return 0;
    }

    /**
     * Toggle front-end trust badge status for a merchant
     */
    public function toggleMerchantBadge($merchant_id, $status)
    {
        if (empty($merchant_id)) {
            return false;
        }
        $this->_ensure_schema_compatibility();
        $this->db->where('id', (int)$merchant_id);
        return $this->db->update('publisher', array(
            'is_badge_enabled' => (int)$status
        ));
    }
}
