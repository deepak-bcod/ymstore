<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Shop_model extends CI_Model {



    public function __construct() {

        parent::__construct();

    }



   public function get_count($state = null, $city = null, $zipcode = null)
    {
        $this->db->from('publisher');

        if (!empty($state)) $this->db->where('state', $state);
        if (!empty($city)) $this->db->where('city', $city);
        if (!empty($zipcode)) $this->db->where('zipcode', $zipcode);

        $this->db->where('status', '1'); // ACTIVE shops only
        $this->_apply_active_subscription_filter();
        return $this->db->count_all_results();
    }

    public function get_shops($limit, $offset, $state = null, $city = null, $zipcode = null)
    {
        $this->db->select('*')->from('publisher');

        if (!empty($state)) $this->db->where('state', $state);
        if (!empty($city)) $this->db->where('city', $city);
        if (!empty($zipcode)) $this->db->where('zipcode', $zipcode);

        $this->db->where('status', '1');
        $this->_apply_active_subscription_filter();
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }


    public function get_city()

    {

		$this->db->select('*');

		$query = $this->db->get('city_master');

		$resultArr = $query->result_array();

		return $resultArr;

    }



	public function get_states()

    {

		$this->db->select('*');

		$query = $this->db->get('country_state_master_in');

		$resultArr = $query->result_array();

		return $resultArr;

    }

    public function shop_details($id) {

        $this->db->select('*'); // Later you can join with images if needed

        $this->db->from('publisher');

        $this->db->where('id', $id);
        $this->db->where('status', '1');
        $this->_apply_active_subscription_filter();

        $query = $this->db->get();

        return $query->row(); // return single record, not array

    }

    public function get_shops_products($shop_id, $limit, $offset, $filters = []) {
        if ($this->is_shop_subscription_expired($shop_id)) {
            return [];
        }

        $this->db->select('products.*');
        
        // Select Min Webshop Price
        $this->db->select('(SELECT MIN(p2.webshop_price) FROM products p2 
                            WHERE p2.parent_id = products.id AND p2.webshop_price > 0) as calculated_min_price');
        
        // Select Min Special Price
        $this->db->select('(SELECT MIN(p2.special_price) FROM products p2 
                            WHERE p2.parent_id = products.id AND p2.special_price > 0) as calculated_min_special_price');
        
        $this->db->where('products.publisher_id', $shop_id);
        $this->db->where('products.status', 1);
        $this->db->where('products.approval_status', 1);
        $this->db->where('products.remove_flag', 0);

        $query = $this->db->limit($limit, $offset)->get('products');
        $results = $query->result();

        foreach ($results as $product) {
            //if($product->name == 'New Girl Top') echo '<pre>'; print_r($product->id);exit;
            $special_price = $this->getSpecialPrices($product->id);

            //if($product->id == 769) echo '<pre>'; print_r($special_price);exit;
            

            if ($product->product_type == 'configurable') {
                // Apply Min Price if empty
                if (empty($product->webshop_price) || $product->webshop_price == 0) {
                    $product->webshop_price = $product->calculated_min_price;
                }
                
                if (empty($product->special_price) || $product->special_price == 0) {
                    $product->special_price = $special_price;
                }
            }else{
                if (empty($product->webshop_price) || $product->webshop_price == 0) {
                    $product->webshop_price = $product->calculated_min_price;
                }
                
                if (empty($product->special_price) || $product->special_price == 0) {
                    $product->special_price = $special_price;
                }

            }
        }
        return $results;
    }

    public function getSpecialPrices($product_id)
	{ 
		$date = time();
        $sql = "SELECT special_price
        FROM products_special_prices
        WHERE product_id = ?
        AND special_price_from <= ?
        AND special_price_to >= ?";

        $param = array($product_id, $date, $date);

        //echo $sql;die;

        $query = $this->db->query($sql, $param);

        //echo '<pre>'; print_r($query);exit;

        $row = $query->row();

        //echo '<pre>'; print_r($row);exit;

        return !empty($row) ? $row->special_price : '';
	}


    public function get_count_products($shop_id) {
        if ($this->is_shop_subscription_expired($shop_id)) {
            return 0;
        }

        return $this->db->where('publisher_id', $shop_id)
                        ->where('status', 1)
                        ->where('approval_status', 1)
                        ->where('remove_flag', 0)
                        ->count_all_results('products');

    }

    public function get_avg_ratings_by_merchant($merchant_id) {
        $this->db->select('merchant_id, AVG(rating) as avg_rating');
        $this->db->from('product_reviews');
        $this->db->where('merchant_id', $merchant_id);
        $this->db->group_by('merchant_id');
        $query = $this->db->get();
        return $query->row(); // one merchant
    }

    /**
     * Check if a shop's merchant subscription is expired and not renewed
     *
     * @param int $shop_id
     * @return bool
     */
    public function is_shop_subscription_expired($shop_id)
    {
        if (empty($shop_id) || !$this->db->table_exists('publisher_subscriptions')) {
            return false;
        }

        // Check if there is an active subscription with a future end_date
        $active_sub = $this->db
            ->where('publisher_id', $shop_id)
            ->where('status', 'active')
            ->where('(end_date IS NULL OR end_date >= NOW())', NULL, FALSE)
            ->order_by('id', 'DESC')
            ->get('publisher_subscriptions')
            ->row_array();

        if ($active_sub) {
            return false; // Active and valid
        }

        // Check if the merchant has a subscription record that is expired or in the past
        $expired_sub = $this->db
            ->where('publisher_id', $shop_id)
            ->where("(status = 'expired' OR (end_date IS NOT NULL AND end_date < NOW()))", NULL, FALSE)
            ->order_by('id', 'DESC')
            ->get('publisher_subscriptions')
            ->row_array();

        if ($expired_sub) {
            return true;
        }

        return false;
    }

    /**
     * Exclude merchants whose subscription is expired and not renewed
     */
    private function _apply_active_subscription_filter()
    {
        if ($this->db->table_exists('publisher_subscriptions')) {
            $this->db->where("publisher.id NOT IN (
                SELECT ps1.publisher_id FROM publisher_subscriptions ps1
                WHERE (ps1.status = 'expired' OR (ps1.end_date IS NOT NULL AND ps1.end_date < NOW()))
                AND ps1.publisher_id NOT IN (
                    SELECT ps2.publisher_id FROM publisher_subscriptions ps2
                    WHERE ps2.status = 'active' AND (ps2.end_date IS NULL OR ps2.end_date >= NOW())
                )
            )", NULL, FALSE);
        }
    }

}

