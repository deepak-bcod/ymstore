<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductBadges_model extends CI_Model {

    public function get_product_badges_categories()
    {
        return $this->db
            ->select('pbc.id, pbc.name, pb.id as content_id, pb.main_content') // choose what columns you need
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
            ->where('merchant_id', $publisher_id)
            ->get('publisher')
            ->result_array();
    }

 public function getAppliedProductsByCategory($catId)
{
    $sql = "SELECT p.id, p.name, p.sku, p.url_key, pba.status, 
            FROM_UNIXTIME(pba.created_at, '%d-%m-%Y') as applied_on,
            pub.publication_name as merchant_name,
            GROUP_CONCAT(DISTINCT doc.document_file) as all_documents
            FROM products_badge_apply pba
            JOIN products p ON FIND_IN_SET(p.id, pba.assigned_products) > 0
            LEFT JOIN publisher pub ON p.publisher_id = pub.id
            /* FIX: Join using the primary key 'id' of the documents table */
            LEFT JOIN mydocuments doc ON FIND_IN_SET(doc.id, pba.documents) > 0
            WHERE pba.prod_badge_cat_id = ?
            GROUP BY p.id, pba.status, pba.created_at, pub.publication_name
            ORDER BY pba.id DESC";

    $query = $this->db->query($sql, [$catId]);
    return $query->result();
}
    public function get_productBadgesListing()
    {
        return $this->db
            ->select('products_badge_apply.*, product_badges_categories.name AS category_name, publisher.publication_name AS merchant_name')
            ->from('products_badge_apply')
            ->join('product_badges_categories', 'product_badges_categories.id = products_badge_apply.prod_badge_cat_id', 'left')
            ->join('publisher', 'publisher.id = products_badge_apply.merchant_id', 'left')
            ->order_by('products_badge_apply.id', 'DESC')
            ->get()
            ->result_array();
    }




}
