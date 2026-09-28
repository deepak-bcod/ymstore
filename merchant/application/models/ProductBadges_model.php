<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductBadges_model extends CI_Model {

   public function get_product_badges_categories($lang = 'english')
{
    $lang = strtolower($lang);
    
    if ($lang == 'fr' || $lang == 'french') {
        $content_column = "CASE 
            WHEN pb.main_content_fr IS NOT NULL AND pb.main_content_fr != '' 
            THEN pb.main_content_fr 
            ELSE pb.main_content 
        END";
    } else {
        $content_column = 'pb.main_content';
    }

    return $this->db
        
        ->select("pbc.id, pbc.name, pbc.name_fr, pb.id as content_id, $content_column as main_content", FALSE)
        ->from('product_badges_categories pbc')
        ->join('product_badges_content pb', 'pb.prod_badge_cat_id = pbc.id', 'left')
        ->get()
        ->result_array();
}

    public function getProductBlockList($publisher_id)
	{
		$date = strtotime(date('d-m-Y'));
		$sub_query = '';
        $sub_query = 'status = 1 AND launch_date <= ' . $date . ' AND publisher_id =' . $publisher_id .' AND ';

		//$result =$this->db->query("SELECT id,name,product_code,product_type,launch_date,status FROM `products` WHERE status = 1 AND launch_date <= CURRENT_DATE AND product_type = 'simple' OR product_type = 'configurable'");
		$result = $this->db->query("SELECT id,name,product_code,product_type,launch_date,status FROM `products` WHERE remove_flag = 0 AND $sub_query (product_type = 'simple' OR product_type = 'configurable')");
		return $result->result();
	}
    public function getDocumentList($publisher_id)
    {
        return $this->db
            ->where('merchant_id', $publisher_id)
            ->get('mydocuments')
            ->result_array();
    }
     public function getMerchantDetails($publisher_id)
    {
        return $this->db
            ->where('id', $publisher_id)
            ->get('publisher')
            ->row_array();
    }
    public function getAppliedProductsByCategory($catId, $publisher_id)
    {
        $sql = "
            SELECT DISTINCT 
                p.id,
                p.name,
                -- if configurable, show its child SKUs
                CASE 
                    WHEN p.product_type = 'configurable' 
                    THEN (
                        SELECT GROUP_CONCAT(child.sku SEPARATOR ', ') 
                        FROM products child 
                        WHERE child.parent_id = p.id
                    )
                    ELSE p.sku
                END AS sku,
                p.description,
                p.base_image,
                pba.status
            FROM products_badge_apply pba
            JOIN products p 
                ON FIND_IN_SET(p.id, pba.assigned_products)
            WHERE pba.prod_badge_cat_id = ?
            AND p.publisher_id = ?
        ";

        $query = $this->db->query($sql, [$catId, $publisher_id]);
        return $query->result();
    }



}
