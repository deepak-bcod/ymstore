<?php

###+------------------------------------------------------------------------------------------------

###| BCOD WEB SOLUTIONS PVT. LTD., MUMBAI [ www.bcod.co.in ]

###+------------------------------------------------------------------------------------------------

###| Code By - Alanka (alanka@bcod.co.in)

###+------------------------------------------------------------------------------------------------

###| Date - Dec 2020

###+------------------------------------------------------------------------------------------------

use App\Services\Trackers\ShipmentStatusEnum;

class CommonModel extends CI_Model
{



	public function __construct() {}

	public function GetEmpRole()

	{

		$sql = $this->db->get('employee_role_master');

		if ($sql->num_rows() > 0) {

			$result = $sql->result_array();

			return $result;
		} else {

			return false;
		}
	}





	function getWebShopNameByShopId($publisher_id)

	{

		$this->db->select('publication_name');

		$this->db->where(array('id' => $publisher_id));

		$query = $this->db->get('publisher');

		$result = $query->row();

		$name = $result->publication_name ?? '';

		return $name;
	}



	function getShopsForBTwoBOrders($order_id)

	{











		$param = array($order_id);







		/*



		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";



		*/







		$query = "SELECT oi.publisher_id FROM sales_order_items as oi  INNER JOIN products as p ON oi.product_id = p.id LEFT JOIN products_inventory as pi ON oi.product_id = pi.product_id WHERE/* p.product_inv_type IN ('dropship') AND */ oi.order_id = $order_id  group by oi.publisher_id";







		// echo $query;

		// print_R($param);



		// exit;



		$query  =  $this->db->query($query);

		$result = $query->result_array();

		if ($result > 0) {

			return $result;
		} else {

			return false;
		}
	}



	function getWebShopCommisionByShopId($publisher_id)

	{

		$this->db->select('commision_percent');

		$this->db->where(array('id' => $publisher_id));

		$query = $this->db->get('publisher');

		$result = $query->row();

		return $result->commision_percent;
	}



	public function getShopOwnerData($shop_id)

	{

		$this->db->select("fs.*");

		$this->db->from('publisher as fs');

		//   $this->seller_db->join('fbc_users as u','u.shop_id = fs.shop_id AND u.created_by=0','LEFT');

		$this->db->where('fs.id', $shop_id);

		$query = $this->db->get();

		//echo $this->seller_db->last_query();exit;

		return $query->row();
	}

	public function getSalesOrderItems($LoginID = '')

	{

		// $identifier = 'rounded_webshop_prices';

		$get_custom_var =  "SELECT * FROM `sales_order_items` WHERE publisher_id='$LoginID' and `sub_end_date` > UNIX_TIMESTAMP(NOW()) /* and `sub_start_date` IS NULL OR  `sub_end_date` IS NULL*/ ";

		// $LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';

		// print_r($LogindID );die;

		// if($LogindID != '' && $LogindID != '0' ){

		// 	$get_custom_var =  "SELECT * FROM `sales_order_items` WHERE publisher_id='$LogindID' and `sub_start_date` IS NULL OR  `sub_end_date` IS NULL";



		// }

		$query  =  $this->db->query($get_custom_var);

		// echo $this->db->last_query();

		$result = $query->result_array();

		if ($result > 0) {

			return $result;
		} else {

			return false;
		}
	}

	public function get_sub_period($product_id)

	{

		// $identifier = 'rounded_webshop_prices';

		$get_custom_var =  "select * from `products_variants` as pv

		  LEFT JOIN `subscription_time` as st ON pv.attr_value=st.eav_option_id

		  where pv.product_id =$product_id";

		$query  =  $this->db->query($get_custom_var);

		$result = $query->row_array();

		if ($result > 0) {

			return $result;
		} else {

			return false;
		}
	}

	public function get_all_subscription($LoginID = '')

	{

		$get_custom_var =  "SELECT * FROM `sales_order_items` where publisher_id='$LoginID' and  /*`sub_start_date` IS NOT  NULL OR  `sub_end_date` IS NOT  NULL  AND */ `sub_end_date` > UNIX_TIMESTAMP(NOW()) order by `sub_end_date` ASC";



		// $get_custom_var =  "SELECT * FROM `sales_order_items` where /*`sub_start_date` IS NOT  NULL OR  `sub_end_date` IS NOT  NULL  AND */ `sub_end_date` > UNIX_TIMESTAMP(NOW()) order by `sub_end_date` ASC";

		$query  =  $this->db->query($get_custom_var);

		// echo $this->db->last_query();die;



		$result = $query->result_array();

		if ($result > 0) {

			return $result;
		} else {

			return false;
		}
	}

	public function update_sub_start($item_id, $sub_start_time)

	{

		$update_array = array(

			"sub_start_date" => $sub_start_time

		);

		$this->db->where('item_id', $item_id);

		$this->db->update('sales_order_items', $update_array);

		if ($this->db->affected_rows() > 0) {

			return true;
		} else {

			return false;
		}

		$this->db->reset_query();
	}



	public function update_sub_end($item_id, $sub_end_time)

	{

		$update_array = array(

			"sub_end_date" => $sub_end_time

		);

		$this->db->where('item_id', $item_id);

		$this->db->update('sales_order_items', $update_array);

		// echo $this->db->last_query();die();

		if ($this->db->affected_rows() > 0) {

			return true;
		} else {

			return false;
		}

		$this->db->reset_query();
	}

	//   public function getShopOwnerData($shop_id)

	// {

	// 	$this->db->select("u.*,fs.*");		

	// 	$this->db->from('fbc_users_shop as fs');

	// 	$this->seller_db->join('fbc_users as u','u.shop_id = fs.shop_id AND u.created_by=0','LEFT');

	// 	$this->db->where('u.shop_id',$shop_id);

	// 	$query = $this->seller_db->get();

	// 	//echo $this->seller_db->last_query();exit;

	// 	return $query->row();

	// }



	public function GetEmpRoleById($role_id, $email, $flag)

	{

		$this->db->select('*');

		$this->db->where('id', $role_id);

		$this->db->where('email', $email);

		$this->db->where('remove_flag', $flag);

		$sql = $this->db->get('publisher');



		if ($sql->num_rows() > 0) {

			$result = $sql->row();

			return $result;
		} else {

			return false;
		}
	}

	public function post_to_facebook($page_id, $page_access_token= null, $message, $link = null, $schedule_time = null)
	{
		$url = "https://graph.facebook.com/v25.0/{$page_id}/feed";

		$data = [
			'message' => $message,
			'access_token' => $page_access_token,
		];

		// Optional link
		if (!empty($link)) {
			$data['link'] = $link;
		}

		// Optional scheduling
		if (!empty($schedule_time)) {
			$data['published'] = 'false';
			$data['scheduled_publish_time'] = $schedule_time; // UNIX timestamp
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		// echo "<pre>";print_r($response);die;

		curl_close($ch);

		return json_decode($response, true);
	}


	public function chekEmpPermission($roleId)

	{

		$this->db->select('t1.*,t2.*');

		$this->db->where('t2.role_id', $roleId);

		$this->db->join('role_resource as t2', 't2.resource_id = t1.id', 'LEFT');

		$query = $this->db->get('resource_master as t1');

		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function get_customer_orders($customer_id, $limit = 50, $offset = 0)
    {
        $this->db
            ->select('`order`.`order_id`, `order`.`increment_id`, `order`.`created_at`, `order`.`grand_total`, `order`.`status`')
            ->from('sales_order as `order`')
            ->join('invoicing as `inv`', '`order`.`invoice_id` = `inv`.`id`', 'left')
            ->where('`order`.`customer_id`', $customer_id)
            ->where('`order`.`status !=', 7)
            ->order_by('`order`.`created_at`', 'DESC')
            ->limit($limit, $offset);

        return $this->db->get()->result();
    }

	public function get_merchant_orders($merchant_id, $limit = 50, $offset = 0)
    {
        $this->db->distinct()->select('`order`.`order_id`, `order`.`increment_id`, `order`.`created_at`, `order`.`grand_total`, `order`.`status`')
		->from('sales_order AS `order`')
		->join('sales_order_items AS soi', '`order`.`order_id` = soi.`order_id`', 'left')
		->where('soi.publisher_id', $merchant_id)
		->where('`order`.`status` !=', 7)
		->order_by('`order`.`created_at`', 'DESC')
		->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_order_products($order_id) {
        $this->db->select('item_id, product_id, product_name as name, qty_ordered as qty');
        $this->db->from('sales_order_items');
        $this->db->where('order_id', $order_id);
        
        $query = $this->db->get();
		// echo $this->db->last_Query();die();
        return $query->result();
    }

		// Get a single order by order_id
	public function get_order_by_id($order_id) {
		return $this->db
			->select('order_id, increment_id, created_at, grand_total, status')
			->from('sales_order')
			->where('order_id', $order_id)
			->get()
			->row();
	}

	// Get a single product by order_id and product_id
	public function get_product_by_order($order_id, $product_id) {
		return $this->db
			->select('product_id, product_name')
			->from('sales_order_items')
			->where('order_id', $order_id)
			->where('product_id', $product_id)
			->get()
			->row();
	}


	public function get_help_desk($LogindID)
	{
		$this->db->where('merchant_id', $LogindID); 
		$query = $this->db->get('help_desk'); 
		return $query->result_array();
	}

	public function get_help_desk_details($id) 
	{
		$this->db->where('id', $id);
		$query = $this->db->get('help_desk');
		return $query->row_array(); 
	}


	public function get_shopper_help_desk($LogindID) {
		$this->db->where('merchant_id', $LogindID); 
		$this->db->where('category', 1);            
		$this->db->order_by('id', 'DESC');
		return $this->db->get('help_desk')->result_array();
	}


	public function get_merchant_help_desk($LogindID) {
		$this->db->where('merchant_id', $LogindID); // Ensure user isolation
		$this->db->where('category', 3);            // Force category 2
		$this->db->order_by('id', 'DESC');
		return $this->db->get('help_desk')->result_array();
	}

	public function get_countries()
	{
		$query = $this->db->get_where('country_master');
		$resultArr = $query->result_array();
		return $resultArr;
	}



	public function get_states_in()
	{

		$query = $this->db->get_where('country_state_master_in');

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_shop_country_master()

	{

		$query = $this->db->get_where('country_master');

		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function get_currency()

	{

		$this->db->select('currency_symbol as currency, currency_code, currency_name');

		$this->db->group_by('currency_code');

		$query = $this->db->get_where('country_master');

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function getCurrencySymbolByCountryCode($country_code)

	{

		$this->db->select('currency_symbol as currency, currency_code , currency_name');

		$this->db->where(array('country_code' => $country_code));

		$query = $this->db->get('country_master');

		$result = $query->row();

		return $result;
	}



	public function get_category()

	{

		$this->db->order_by('cat_name', 'ASC');

		$query = $this->db->get_where('category', array('status' => '1'));

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_category_for_seller()

	{

		$this->db->select('*');

		$this->db->where('status', 1);

		$this->db->where('cat_level', 0);

		$this->db->order_by('cat_name', 'asc');

		$query = $this->db->get('category');

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_child_category($parent_id)

	{

		$this->db->order_by('cat_name', 'ASC');

		$query = $this->db->get_where('category', array('parent_id' => $parent_id, 'status' => '1'));

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_child_category_for_seller($seller_id, $parent_cat_id)

	{

		$this->db->select('*');

		$this->db->where('status', 1);

		$this->db->where('parent_id', $parent_cat_id);

		$this->db->where("(`created_by` = $seller_id OR `created_by_type` = 0)");

		$this->db->order_by('cat_name', 'asc');

		$query = $this->db->get('category');

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_all_attributes()

	{

		$this->db->order_by('school', 'ASC');

		$query = $this->db->get_where('eav_attributes', array('status' => '1'));

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_all_active_suppliers()

	{

		$this->db->select('*');

		$this->db->order_by('supplier', 'ASC');

		$query = $this->db->get_where('suppliers', array('status' => '1'));

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function get_custom_variable($name)

	{

		$result = $this->db->get_where('custom_variables_master', array('name' => $name))->row();

		return $result;
	}



	public function get_custom_variable_by_id($id)

	{

		$result = $this->db->get_where('custom_variables_master', array('id' => $id))->row();

		return $result;
	}



	public function getEmailTemplateById($TemplateId)

	{

		$result = $this->db->get_where('email_template', array('id' => $TemplateId))->row();

		return $result;
	}



	public function sendCommonEmail($EmailTo, $templateId, $TempVars, $DynamicVars)
	{

		$emailTemplate = $this->getEmailTemplateById($templateId);

		$subject = $emailTemplate->subject;

		$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

		if ($this->sendMailSMTP($EmailTo, $subject, $emailBody, $attachment = "")) {

			return true;
		} else {

			return false;
		}
	}





	public function getGlobalVariableByIdentifier($identifier)
	{

		$result = $this->db->get_where('global_custom_variables', array('identifier' => $identifier))->row();

		return $result;
	}





	public function getEmailTemplateByIdentifier($identifier)
	{
		$result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();
		if (!$result && in_array($identifier, [
			'order-resolution-merchant',
			'order-resolution-help',
			'order-resolution-shopper-reply-merchant',
			'order-resolution-merchant-reply-shopper'
		])) {
			$result = $this->getDefaultEmailTemplate($identifier);
		}
		return $result;
	}

	public function getDefaultEmailTemplate($identifier)
	{
		$templates = [
			'order-resolution-merchant' => [
				'email_code' => 'order-resolution-merchant',
				'title'      => 'Order Resolution - Merchant Notification',
				'title_fr'   => 'Résolution de commande - Notification marchand',
				'subject'    => 'New Order Resolution Ticket ###TICKET_NUMBER## - Order ###ORDER_NUMBER##',
				'subject_fr' => 'Nouveau ticket de résolution de commande ###TICKET_NUMBER## - Commande ###ORDER_NUMBER##',
				'content'    => '<h3>Dear ##MERCHANT_NAME##,</h3><p>A new order resolution ticket has been submitted by a shopper for your product.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Ticket Number:</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Category:</td><td style="border:1px solid #e9ecef;">##CATEGORY##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Order Number:</td><td style="border:1px solid #e9ecef;">###ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Product Name:</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Priority:</td><td style="border:1px solid #e9ecef;">##PRIORITY##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Shopper Message:</td><td style="border:1px solid #e9ecef;">##SHOPPER_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">View Ticket</a></p><p>Kind Regards,<br/>##WEBSHOPNAME##</p>',
				'content_fr' => '<h3>Bonjour ##MERCHANT_NAME##,</h3><p>Un nouveau ticket de résolution de commande a été soumis par un client pour votre produit.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Numéro de ticket :</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Catégorie :</td><td style="border:1px solid #e9ecef;">##CATEGORY##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Numéro de commande :</td><td style="border:1px solid #e9ecef;">###ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du produit :</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Priorité :</td><td style="border:1px solid #e9ecef;">##PRIORITY##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Message du client :</td><td style="border:1px solid #e9ecef;">##SHOPPER_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">Voir le ticket</a></p><p>Cordialement,<br/>##WEBSHOPNAME##</p>',
				'status'     => 1,
				'created_by' => 1,
				'created_at' => time(),
				'updated_at' => time(),
				'ip'         => '::1',
			],
			'order-resolution-help' => [
				'email_code' => 'order-resolution-help',
				'title'      => 'Order Resolution - Admin/Help Notification',
				'title_fr'   => 'Résolution de commande - Notification Admin/Assistance',
				'subject'    => 'New Order Resolution Ticket ###TICKET_NUMBER## - [##PRIORITY##]',
				'subject_fr' => 'Nouveau ticket de résolution de commande ###TICKET_NUMBER## - [##PRIORITY##]',
				'content'    => '<h3>Dear Support Team,</h3><p>A new order resolution ticket has been submitted on ##WEBSHOPNAME##.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Ticket Number:</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Category:</td><td style="border:1px solid #e9ecef;">##CATEGORY##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Merchant Name:</td><td style="border:1px solid #e9ecef;">##MERCHANT_NAME##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Shopper Name:</td><td style="border:1px solid #e9ecef;">##SHOPPER_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Order Number:</td><td style="border:1px solid #e9ecef;">###ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Product Name:</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Priority:</td><td style="border:1px solid #e9ecef;">##PRIORITY##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Original Shopper Message:</td><td style="border:1px solid #e9ecef;">##SHOPPER_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">View Ticket</a></p><p>Kind Regards,<br/>##WEBSHOPNAME##</p>',
				'content_fr' => '<h3>Bonjour l\'équipe d\'assistance,</h3><p>Un nouveau ticket de résolution de commande a été soumis sur ##WEBSHOPNAME##.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Numéro de ticket :</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Catégorie :</td><td style="border:1px solid #e9ecef;">##CATEGORY##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du marchand :</td><td style="border:1px solid #e9ecef;">##MERCHANT_NAME##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du client :</td><td style="border:1px solid #e9ecef;">##SHOPPER_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Numéro de commande :</td><td style="border:1px solid #e9ecef;">###ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du produit :</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Priorité :</td><td style="border:1px solid #e9ecef;">##PRIORITY##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Message original du client :</td><td style="border:1px solid #e9ecef;">##SHOPPER_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">Voir le ticket</a></p><p>Cordialement,<br/>##WEBSHOPNAME##</p>',
				'status'     => 1,
				'created_by' => 1,
				'created_at' => time(),
				'updated_at' => time(),
				'ip'         => '::1',
			],
			'order-resolution-shopper-reply-merchant' => [
				'email_code' => 'order-resolution-shopper-reply-merchant',
				'title'      => 'Order Resolution - Shopper Reply to Merchant',
				'title_fr'   => 'Résolution de commande - Réponse du client au marchand',
				'subject'    => 'Order Resolution Update No.: [##TICKET_NUMBER##]',
				'subject_fr' => 'Mise à jour de la résolution de commande n° : [##TICKET_NUMBER##]',
				'content'    => '<h3>Dear ##MERCHANT_NAME##,</h3><p>The shopper has posted a new reply on Order Resolution Ticket <strong>###TICKET_NUMBER##</strong>.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Ticket Number:</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Shopper Name:</td><td style="border:1px solid #e9ecef;">##SHOPPER_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Order Number:</td><td style="border:1px solid #e9ecef;">##ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Product Name:</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Shopper\'s Reply:</td><td style="border:1px solid #e9ecef;">##REPLY_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">View Ticket &amp; Reply</a></p><p>Kind Regards,<br/>##WEBSHOPNAME##</p>',
				'content_fr' => '<h3>Bonjour ##MERCHANT_NAME##,</h3><p>Le client a publié une nouvelle réponse sur le ticket de résolution de commande <strong>###TICKET_NUMBER##</strong>.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Numéro de ticket :</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du client :</td><td style="border:1px solid #e9ecef;">##SHOPPER_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Numéro de commande :</td><td style="border:1px solid #e9ecef;">##ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du produit :</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Réponse du client :</td><td style="border:1px solid #e9ecef;">##REPLY_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">Voir le ticket et répondre</a></p><p>Cordialement,<br/>##WEBSHOPNAME##</p>',
				'status'     => 1,
				'created_by' => 1,
				'created_at' => time(),
				'updated_at' => time(),
				'ip'         => '::1',
			],
			'order-resolution-merchant-reply-shopper' => [
				'email_code' => 'order-resolution-merchant-reply-shopper',
				'title'      => 'Order Resolution - Merchant Reply to Shopper',
				'title_fr'   => 'Résolution de commande - Réponse du marchand au client',
				'subject'    => 'Order Resolution Update No.: [##TICKET_NUMBER##]',
				'subject_fr' => 'Mise à jour de la résolution de commande n° : [##TICKET_NUMBER##]',
				'content'    => '<h3>Dear ##SHOPPER_NAME##,</h3><p>The merchant has replied to your Order Resolution Ticket <strong>###TICKET_NUMBER##</strong>.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Ticket Number:</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Merchant:</td><td style="border:1px solid #e9ecef;">##MERCHANT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Order Number:</td><td style="border:1px solid #e9ecef;">##ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Product Name:</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Merchant\'s Reply:</td><td style="border:1px solid #e9ecef;">##REPLY_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">View Conversation</a></p><p>Kind Regards,<br/>##WEBSHOPNAME##</p>',
				'content_fr' => '<h3>Bonjour ##SHOPPER_NAME##,</h3><p>Le marchand a répondu à votre ticket de résolution de commande <strong>###TICKET_NUMBER##</strong>.</p><table border="0" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:15px 0;"><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold; width:30%;">Numéro de ticket :</td><td style="border:1px solid #e9ecef;">###TICKET_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Marchand :</td><td style="border:1px solid #e9ecef;">##MERCHANT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Numéro de commande :</td><td style="border:1px solid #e9ecef;">##ORDER_NUMBER##</td></tr><tr><td style="border:1px solid #e9ecef; font-weight:bold;">Nom du produit :</td><td style="border:1px solid #e9ecef;">##PRODUCT_NAME##</td></tr><tr style="background:#f8f9fa;"><td style="border:1px solid #e9ecef; font-weight:bold;">Réponse du marchand :</td><td style="border:1px solid #e9ecef;">##REPLY_MESSAGE##</td></tr></table><p style="margin:20px 0;"><a href="##TICKET_URL##" style="background-color:#1E7EC8; color:#ffffff; padding:10px 20px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;">Consulter la conversation</a></p><p>Cordialement,<br/>##WEBSHOPNAME##</p>',
				'status'     => 1,
				'created_by' => 1,
				'created_at' => time(),
				'updated_at' => time(),
				'ip'         => '::1',
			],
		];

		if (!isset($templates[$identifier])) {
			return null;
		}

		$tplData = $templates[$identifier];
		try {
			$this->db->insert('email_template', $tplData);
			$inserted = $this->db->get_where('email_template', ['email_code' => $identifier])->row();
			if ($inserted) {
				return $inserted;
			}
		} catch (\Throwable $e) {
			// Fallback to in-memory object
		}

		return (object)$tplData;
	}

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $lang_code = '')
	{
		$from_email = 'noreply@yellowmarkets.com';

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		if (!$emailTemplate) {
			log_message('error', 'sendCommonHTMLEmail: email template not found for: ' . $identifier);
			return false;
		}

		if (!empty($lang_code) && ($lang_code === 'fr' || $lang_code === 'french')) {
			$subject = !empty($emailTemplate->subject_fr) ? $emailTemplate->subject_fr : $emailTemplate->subject;
			$content = !empty($emailTemplate->content_fr) ? $emailTemplate->content_fr : $emailTemplate->content;
		} else {
			$subject = $emailTemplate->subject;
			$content = $emailTemplate->content;
		}

		$email_code = !empty($emailTemplate->email_code) ? $emailTemplate->email_code : $identifier;

		// FIXED: Correct comparison
		$ccEmail = "";
		if ($email_code == "admin-notification-new-merchant-register-fr" ||
			$email_code == "admin-notification-new-merchant-register") 
		{
			$ccEmail = "info@yellowmarkets.com";
		}

		// Replace placeholders in subject and content
		$subject = str_replace($TempVars, $DynamicVars, $subject);
		$emailBody = str_replace($TempVars, $DynamicVars, $content);

		$data['subject'] = $subject;
		$data['content'] = $emailBody;

		$contentView = $this->load->view('email_template/email_content', $data, TRUE);
		$contentView = str_replace(['```html', '```'], '', $contentView);

		// FIXED: Pass actual variable
		if ($this->sendHTMLMailSMTP($EmailTo, $subject, $contentView, $from_email, $attachment = "", $ccEmail)) {
			return true;
		} else {
			return false;
		}
	}






	function GetUserByUserId($id)

	{

		$result = $this->db->get_where('publisher', array('id' => $id))->row();

		return $result;
	}



	function GetEmpByUserId($fbc_user_id)

	{

		$result = $this->db->get_where('fbc_users_emp_details', array('fbc_user_id' => $fbc_user_id))->row();

		return $result;
	}



	function getUserFullNameById($fbc_user_id)

	{

		$this->db->reset_query();

		$full_name = '';

		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $fbc_user_id))->row();

		if (isset($result) && ($result->owner_name != '')) {

			$full_name = $result->owner_name;
		} else {

			$full_name = 'Unknown';
		}

		return $full_name;
	}



	public function GetDropDownOptions($attr_id)

	{

		$this->db->reset_query();

		$this->db->order_by('position', 'ASC');

		$query = $this->db->get_where('eav_attributes_options', array('attr_id' => $attr_id));

		$resultArr = $query->result_array();

		return $resultArr;
	}



	public function sendMailSMTP($to, $subject, $message, $attachment = "")

	{

		$mail = new PHPMailer; // call the class

		$mail->IsSMTP();

		$mail->Host = SMTP_HOST; //Hostname of the mail server

		$mail->SMTPDebug = 1;

		$mail->Port = SMTP_PORT; //Port of the SMTP like to be 25, 80, 465 or 587

		$mail->SMTPAuth = true; //Whether to use SMTP authentication

		$mail->Username = SMTP_UNAME; //Username for SMTP authentication any valid email created in your domain

		$mail->Password = SMTP_PWORD; //Password for SMTP authentication

		$mail->AddReplyTo("alanka@bcod.co.in", "Reply name"); //reply-to address

		$mail->SetFrom("alanka@bcod.co.in", SITE_TITLE); //From address of the mail

		$mail->IsHTML(true);

		$mail->Subject = $subject; //Subject od your mail

		if (is_string($to)) {

			$mail->AddAddress($to); //To address who will receive this email

			$mail->MsgHTML($message); //Put your body of the message you can place html code here

			$send = $mail->Send(); //Send the mails

		}

		if ($send) {

			return true;
		} else {

			return false;
		}
	}



	public function sendHTMLMailSMTP($to, $subject, $content, $from_email = '', $attachment = "",$ccEmail = "", $webshop_smtp_host = "", $webshop_smtp_port = "", $webshop_smtp_username = "", $webshop_smtp_password = "", $webshop_smtp_secure = "")

	{

		$email = 'ranjana.patel@bcod.co.in';
		$webshop_smtp_host = 'smtp.gmail.com'; // $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'noreply@yellowmarkets.com'; //$this->getCustomVariableByIdentifier('smtp_username') ?? 
		$webshop_smtp_password = 'fkyvohootmnwylkg'; //$this->getCustomVariableByIdentifier('smtp_password') ?? 
		$webshop_smtp_secure = 'ssl';
		$this->load->library('email');
		// print_r($webshop_smtp_host);die;
		if ($webshop_smtp_host != '' && $webshop_smtp_port != '' && $webshop_smtp_username != '' && $webshop_smtp_password != '' && $webshop_smtp_secure != '') {

			$config = array(

				'protocol'  => 'smtp',

				'smtp_host' => $webshop_smtp_host,

				'smtp_port' => $webshop_smtp_port,

				'smtp_user' => $webshop_smtp_username,

				'smtp_pass' => $webshop_smtp_password,

				'mailtype'  => 'html',

				'charset'   => 'utf-8',

				'smtp_crypto' => $webshop_smtp_secure,

			);

			$this->email->initialize($config);
		}

		$this->email->set_newline("\r\n");

		$this->email->from($from_email); // change it to yours

		$this->email->to($to); // change it to yours
		if (!empty($ccEmail)) {
			$this->email->cc($ccEmail);
		}

		$this->email->subject($subject);

		$this->email->message($content);

		$this->email->set_mailtype("html");

		if ($this->email->send()) {

			return true;
		} else {

			return false;
		}
	}







	public function sendHTMLMailSMTPOld($to_email, $subject, $content, $from_email = '', $attachment = "")

	{

		$this->load->library('email');

		$message = $content;

		$this->email->set_newline("\r\n");

		$this->email->set_mailtype("html");

		$this->email->from("$from_email"); // change it to yours

		$this->email->reply_to("$from_email"); // change it to yours

		$this->email->bcc('usha@bcod.co.in');

		$this->email->cc('alanka@bcod.co.in');

		$this->email->to($to_email); // change it to yours

		$this->email->subject($subject);

		$this->email->message($message);

		$start_time = time();

		if ($this->email->send()) {

			$endtime = time();

			$diff = $endtime - $start_time;

			$apierros = " Subject :  $subject <br>To:  $to_email<br> Total Time: {$diff} Sec ";

			TAB_Log::write_email_log('info', $apierros);
		} else {

			show_error($this->email->print_debugger());

			$apierros = $this->email->print_debugger();

			TAB_Log::write_email_log('error', $apierros);
		}

		$this->email->clear(TRUE);
	}



	public function sendTestEmailBySMTP($to, $subject, $message, $attachment = "")

	{

		$this->sendMailSMTP($to, $subject, $message, $attachment);
	}



	function getNotifications($user_id, $limit = '')

	{

		$this->db->select('*');

		$this->db->where(array('user_id' => $user_id));

		$this->db->order_by('created_at', 'desc');

		if ($limit != '') {

			$this->db->limit($limit);
		}

		$query = $this->db->get('notifications');

		return $query->result();
	}



	function getUnreadNotificationsCount($user_id)

	{

		$this->db->select('*');

		$this->db->where(array('user_id' => $user_id, 'status' => 0));

		$this->db->order_by('created_at', 'desc');

		$query = $this->db->get('notifications');

		return $query->num_rows();
	}



	public function getRoundedPriceFlag()

	{

		$identifier = 'rounded_webshop_prices';

		$get_custom_var =  "SELECT value FROM custom_variables where  `identifier` = '$identifier'";

		$query  =  $this->db->query($get_custom_var);

		$result = $query->row_array();

		if ($result > 0) {

			return $result['value'];
		} else {

			return 0;
		}
	}



	public function getAllPublishers()

	{

		$get_pub =  "SELECT id,publication_name FROM publisher ORDER BY id DESC";

		$query  =  $this->db->query($get_pub);

		$result = $query->result();

		if ($result > 0) {

			return $result;
		} else {

			return 0;
		}
	}

	public function getPublishersDetail($LogindID)

	{

		return $this->db
			->where('id', $LogindID)
			->get('publisher')
			->row_array();
	}

	public function custom_filter_input($data)
	{

		$data = trim($data);

		$data = stripslashes($data);

		$data = htmlspecialchars($data);



		return $data;
	}



	public	function generate_new_purchase_order_id($po_id)

	{

		$h = $po_id;

		$tr_id = str_pad($h, 5, "0", STR_PAD_LEFT);

		$new_po_id = 'TAB2' . $tr_id;

		return $new_po_id;
	}



	public function getLastPOId()

	{

		$this->db->select('*');

		$this->db->order_by('id', 'desc');

		$this->db->limit(1);

		$query = $this->db->get('inventory_purchase_order');

		return $query->row();
	}



	public function getSingleDataByID($tableName, $condition, $select)

	{
		if ($tableName == "fbc_users_shop") {
			return "";
		}
		if (!empty($select)) {

			$this->db->select($select);
		}

		$this->db->where($condition);

		$query = $this->db->get($tableName);

		return $query->row();
	}



	public function getSingleShopDataByID($tableName, $condition, $select)

	{

		if ($tableName == "fbc_users_shop") {
			return "";
		}
		if (!empty($select)) {

			$this->db->select($select);
		}

		$this->db->where($condition);

		$query = $this->db->get($tableName);

		return $query->row();
	}



	public function update_custom_variable($tableName, $condition, $updateData)

	{

		$this->db->where($condition);

		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;
		} else {

			return false;
		}

		$this->db->reset_query();
	}



	public function GetUserByEmail($email)

	{

		$this->db->where('email', $email);

		$query = $this->db->get('fbc_users');

		if ($query->num_rows() > 0) {

			$result = $query->row_array();

			return $result;
		} else {

			return false;
		}
	}





	public function getMultiDataById($tableName, $condition, $select, $order_by_column = '', $order_by_type = '')

	{

		if (!empty($select)) {

			$this->db->select($select);
		}

		$this->db->where($condition);



		if (isset($order_by_column) &&  $order_by_column != '') {

			$this->db->order_by($order_by_column, $order_by_type);
		}

		$query = $this->db->get($tableName);

		return $query->result();
	}



	function deleteDataById($tablename, $where)
	{

		$this->db->delete($tablename, $where);

		$this->db->reset_query();
	}



	public function updateData($tableName, $condition, $updateData)

	{

		$this->db->where($condition);

		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;
		} else {

			return false;
		}

		$this->db->reset_query();
	}





	//insertData

	public function insertData($table, $data)

	{

		$this->db->reset_query();

		$this->db->insert($table, $data);

		if ($this->db->affected_rows() > 0) {

			$last_insert_id = $this->db->insert_id();

			return $last_insert_id;
		} else {

			return false;
		}
	}



	public function getNotificationCount($to_shop_id, $to_fbc_user_id, $limit = '')
	{

		$this->db->select('*');

		$this->db->from('notifications');

		$this->db->where(array('to_shop_id' => $to_shop_id, 'to_fbc_user_id' => $to_fbc_user_id, 'visited_flag' => 0));

		$this->db->order_by('id', 'desc');

		$query = $this->db->get();

		$result = $query->num_rows();

		return $result;
	}



	public function getUserNotifications($to_shop_id, $to_fbc_user_id, $notificationId = '', $limit = '')
	{

		$this->db->select('*');

		$this->db->from('notifications');

		$this->db->where(array('to_shop_id' => $to_shop_id, 'to_fbc_user_id' => $to_fbc_user_id));

		if ($notificationId != '') {

			$this->db->where('id < ', $notificationId);
		}

		$this->db->order_by('id', 'desc');

		if ($limit != '') {

			$this->db->limit($limit);
		}

		$query = $this->db->get();



		$result = $query->result();

		return $result;
	}



	public function getUserNotificationsById($to_shop_id, $to_fbc_user_id, $notificationId)
	{

		$this->db->select('*');

		$this->db->from('notifications');

		$this->db->where(array('id' => $notificationId, 'to_shop_id' => $to_shop_id, 'to_fbc_user_id' => $to_fbc_user_id));

		$query = $this->db->get();

		$row = $query->row();

		return $row;
	}



	public function updateNotificationData($tableName, $condition, $updateData)
	{

		$this->db->where($condition);

		$this->db->update($tableName, $updateData);

		if ($this->db->affected_rows() > 0) {

			return true;
		} else {

			return false;
		}

		$this->db->reset_query();
	}



	public function get_country_name_by_code($country_code)

	{

		$this->db->select('country_name');

		$this->db->where('country_code', $country_code);

		$query = $this->db->get('country_master');

		if ($query->num_rows() > 0) {

			$result = $query->row_array();

			return $result['country_name'];
		} else {

			return false;
		}
	}


	function getShopCurrency($shopid = null)
	{
		return 'MUR';
	}

	function getTime($date)
	{

		$date2 = strtotime(date('Y-m-d H:i:s'));

		$diff = abs($date2 - $date);

		$years = floor($diff / (365 * 60 * 60 * 24));

		$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

		$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

		$hours = floor(($diff - $years * 365 * 60 * 60 * 24  - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

		$minutes = floor(($diff - $years * 365 * 60 * 60 * 24  - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24  - $hours * 60 * 60) / 60);

		$seconds = floor(($diff - $years * 365 * 60 * 60 * 24  - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

		if ($years != 0 && $months != 0 && $days != 0) {

			printf("%d years, %d months, %d days ago\n", $years, $months, $days);
		} elseif ($months != 0 && $days != 0) {

			printf("%d months, %d days ago\n", $months, $days);
		} elseif ($days != 0) {

			printf("%d days ago\n", $days);
		} elseif ($hours != 0 && $minutes != 0) {

			if ($hours == 1) {

				printf("%d hour, %d minutes ago\n", $hours, $minutes);
			} else {

				printf("%d hours, %d minutes ago\n", $hours, $minutes);
			}
		} elseif ($minutes != 0) {

			printf("%d minutes ago\n", $minutes);
		}
	}



	/*public function getOrderStatusLabel($id)
	{
		$label = '';

		switch ($id) {
			case 0:
				$label = 'To Be Processed';
				break;
			case 1:
				$label = 'Processing';
				break;
			case 2:
				$label = 'Complete';
				break;
			case 3:
				$label = 'Cancelled';
				break;
			case 4:
				$label = 'Shipped';
				break;
			case 5:
				$label = 'Attempt 2';
				break;
			case 6:
				$label = 'Attempt 3';
				break;
			case 7:
				$label = 'Collect From Store';
				break;
			case 8:
				$label = 'Delivered';
				break;
			case 9:
				$label = 'Collected';
				break;
			case 10:
				$label = 'YM Pickup Generated';
				break;
			default:
				$label = 'Unknown';
				break;
		}

		return $label;
	}*/

public function getOrderStatusLabel($id)
{
    switch ($id) {
        case 0:  return $this->lang->line('model_status_to_be_processed');
        case 1:  return $this->lang->line('model_status_processing');
        case 2:  return $this->lang->line('model_status_complete');
        case 3:  return $this->lang->line('model_status_cancelled');
        case 4:  return $this->lang->line('model_status_shipped');
        case 5:  return $this->lang->line('model_status_attempt_2');
        case 6:  return $this->lang->line('model_status_attempt_3');
        case 7:  return $this->lang->line('model_status_collect_from_store');
        case 8:  return $this->lang->line('model_status_delivered');
        case 9:  return $this->lang->line('model_status_collected');
        case 10: return $this->lang->line('model_status_ym_pickup_generated');
		case 11: return $this->lang->line('model_status_pickup');
		case 12: return $this->lang->line('model_status_received_to_warehouse');
		case 13: return $this->lang->line('model_status_collect_from_warehouse');
		case 14: return $this->lang->line('model_status_return_requested');
		case 15: return $this->lang->line('model_status_replacement_requested');
		case 16: return $this->lang->line('model_status_return_approved');
		case 17: return $this->lang->line('model_status_refund_paid');
		case 18: return $this->lang->line('model_status_replacement_approved');
		case 19: return $this->lang->line('model_status_replaced');
		case 20: return $this->lang->line('model_status_return_rejected');
		case 21: return $this->lang->line('model_status_replacement_rejected');
		case 22: return $this->lang->line('model_status_return_approved'); // Return Approved (legacy code 22 normalized)
		case 23: return $this->lang->line('delivery_delivered');
		case 24: return $this->lang->line('delivery_mark_as_delivered');
		case 25: return $this->lang->line('delivery_mark_as_failed');
		case 26: return $this->lang->line('delivery_re_attempt');
		case 27: return $this->lang->line('delivery_collected_from_store');
		case 28: return $this->lang->line('delivery_assign_delivery');
		case 29: return $this->lang->line('delivery_not_ready');
		case 30: return $this->lang->line('model_status_attempt_1_fail');
		case 31: return $this->lang->line('model_status_attempt_2_fail');
		case 32: return $this->lang->line('model_status_attempt_3_fail');
		default: return $this->lang->line('model_status_unknown');
    }
}

	/**
	 * Resolves and synchronizes the exact Return or Replacement status for a B2B order.
	 *
	 * Return Flow:
	 *   14: Return Requested
	 *   16: Return Approved
	 *   20: Return Rejected
	 *   17: Refund Paid
	 *
	 * Replacement Flow:
	 *   15: Replacement Requested
	 *   18: Replacement Approved
	 *   21: Replacement Rejected
	 *   19: Replaced
	 *
	 * Prevents cross-contamination between workflows and updates b2b_orders if out of sync.
	 */
	public function resolveReturnReplacementStatus($order_id, $webshop_order_id = 0, $current_status = 0)
	{
		$order_id = (int)$order_id;
		$webshop_order_id = (int)$webshop_order_id;
		$current_status = (int)$current_status;
		
		// No valid return or replacement request found.
		// Keep the original B2B order status.
		if (empty($rep) && empty($ret)) {

			// Normalize legacy status 22 only
			if ($current_status === 22) {
				return 16;
			}

			return $current_status;
		}

		$is_explicit_return = in_array($current_status, [14, 16, 17, 20], true);
		$is_explicit_replacement = in_array($current_status, [15, 18, 19, 21], true);

		// 1. Fetch latest replacement request
		$rep = null;
		if (!$is_explicit_return) {
			$rep = $this->db->select('sor.replacement_order_id, sor.status as rep_status, sori.status as item_status, sor.created_at, sor.updated_at')
				->from('sales_order_replacement sor')
				->join('sales_order_replacement_items sori', 'sori.replacement_order_id = sor.replacement_order_id', 'left')
				->join('b2b_order_items boi', 'boi.item_id = sori.order_item_id', 'inner')
				->where('boi.order_id', $order_id)
				->order_by('sor.replacement_order_id', 'DESC')
				->limit(1)
				->get()->row();

			if (empty($rep)) {
				$rep_query = $this->db->select('sor.replacement_order_id, sor.status as rep_status, sori.status as item_status, sor.created_at, sor.updated_at')
					->from('sales_order_replacement sor')
					->join('sales_order_replacement_items sori', 'sori.replacement_order_id = sor.replacement_order_id', 'left')
					->group_start()
						->where('sor.order_id', $order_id);
				if (!empty($webshop_order_id)) {
					$rep_query->or_where('sor.order_id', $webshop_order_id);
				}
				$rep = $rep_query->group_end()
					->order_by('sor.replacement_order_id', 'DESC')
					->limit(1)
					->get()->row();
			}
		}

		// 2. Fetch latest return request
		$ret = null;
		if (!$is_explicit_replacement) {
			$ret = $this->db->select('sor.return_order_id, sor.status as ret_status, sor.refund_status, sori.status as item_status, sor.created_at, sor.updated_at')
				->from('sales_order_return sor')
				->join('sales_order_return_items sori', 'sori.return_order_id = sor.return_order_id', 'left')
				->join('b2b_order_items boi', 'boi.item_id = sori.order_item_id', 'inner')
				->where('boi.order_id', $order_id)
				->order_by('sor.return_order_id', 'DESC')
				->limit(1)
				->get()->row();

			if (empty($ret)) {
				$ret_query = $this->db->select('sor.return_order_id, sor.status as ret_status, sor.refund_status, sori.status as item_status, sor.created_at, sor.updated_at')
					->from('sales_order_return sor')
					->join('sales_order_return_items sori', 'sori.return_order_id = sor.return_order_id', 'left')
					->group_start()
						->where('sor.order_id', $order_id);
				if (!empty($webshop_order_id)) {
					$ret_query->or_where('sor.order_id', $webshop_order_id);
				}
				$ret = $ret_query->group_end()
					->order_by('sor.return_order_id', 'DESC')
					->limit(1)
					->get()->row();
			}
		}

		// Determine which flow applies
		$chosen_flow = null;
		if (!empty($rep) && !empty($ret)) {
			if ($is_explicit_replacement) {
				$chosen_flow = 'replacement';
			} elseif ($is_explicit_return) {
				$chosen_flow = 'return';
			} else {
				$rep_time = !empty($rep->updated_at) ? $rep->updated_at : (!empty($rep->created_at) ? $rep->created_at : $rep->replacement_order_id);
				$ret_time = !empty($ret->updated_at) ? $ret->updated_at : (!empty($ret->created_at) ? $ret->created_at : $ret->return_order_id);
				$chosen_flow = ($rep_time >= $ret_time) ? 'replacement' : 'return';
			}
		} elseif (!empty($rep)) {
			$chosen_flow = 'replacement';
		} elseif (!empty($ret)) {
			$chosen_flow = 'return';
		}

		$resolved_status = $current_status;

		if ($chosen_flow === 'replacement') {
			$rep_st  = (int)($rep->rep_status ?? 0);
			$item_st = (int)($rep->item_status ?? 0);
			// Stage 4: Replaced (19)
			if (in_array($rep_st, [3, 5, 6, 19], true) || in_array($item_st, [3, 5, 6, 19], true)) {
				$resolved_status = 19;
			}
			// Stage 3: Replacement Rejected (21)
			elseif ($rep_st === 4 || $item_st === 4 || $item_st === 21) {
				$resolved_status = 21;
			}
			// Stage 2: Replacement Approved (18)
			elseif (in_array($rep_st, [1, 2, 18], true) || in_array($item_st, [1, 2, 18], true)) {
				$resolved_status = 18;
			}
			// Stage 1: Replacement Requested (15)
			else {
				$resolved_status = 15;
			}
		} elseif ($chosen_flow === 'return') {
			$ret_st  = (int)($ret->ret_status ?? 0);
			$ref_st  = isset($ret->refund_status) ? (int)$ret->refund_status : -1;
			$item_st = (int)($ret->item_status ?? 0);
			// Stage 4: Refund Paid (17)
			if ($ref_st === 1 || $ret_st === 4 || $item_st === 4 || $item_st === 17) {
				$resolved_status = 17;
			}
			// Stage 3: Return Rejected (20)
			elseif (in_array($ret_st, [2, 5, 20], true) || $ref_st === 2 || $item_st === 2 || $item_st === 20) {
				$resolved_status = 20;
			}
			// Stage 2: Return Approved (16)
			elseif (in_array($ret_st, [1, 3, 16, 22], true) || in_array($item_st, [1, 3, 16, 22], true)) {
				$resolved_status = 16;
			}
			// Stage 1: Return Requested (14)
			else {
				$resolved_status = 14;
			}
		} else {
			// Normalize legacy Return Approved (22) to 16
			if ($current_status === 22) {
				$resolved_status = 16;
			} else {
				// Fallback to b2b_order_items status
				$item_stat = $this->db->select('status')
					->from('b2b_order_items')
					->where('order_id', $order_id)
					->where_in('status', [14, 15, 16, 17, 18, 19, 20, 21, 22])
					->order_by('item_id', 'DESC')
					->limit(1)
					->get()->row();
				if (!empty($item_stat) && !empty($item_stat->status)) {
					$st = (int)$item_stat->status;
					$resolved_status = ($st === 22) ? 16 : $st;
				}
			}
		}

		// Keep database in sync if status was adjusted
		if ($current_status !== $resolved_status) {
			$this->db->where('order_id', $order_id)->update('b2b_orders', [
				'status' => $resolved_status,
				'updated_at' => time()
			]);
		}

		return $resolved_status;
	}


	public function getOrderShipmentLabel($id)
	{

		$label = '';

		if ($id == '2') {

			$label = 'YM Delivery';
		} else {

			$label = 'Own Delivery';;
		}

		return $label;
	}



	function calculate_percent_data($amount, $percent = '')

	{

		$Response = array();

		$percent_amount = 0;

		$net_pay_amount = 0;

		$net_pay_amount = $amount;

		if ($amount > 0 && $percent > 0) {

			$percent_amount = ($percent / 100) * $amount;

			$net_pay_amount = $percent_amount + $amount;
		}

		$Response['percent_amount'] = $percent_amount;

		$Response['net_pay_amount'] = $net_pay_amount;

		return $Response;
	}



	public function getProducyDataByID($tableName, $condition, $select)
	{

		if (!empty($select)) {

			$this->db->select($select);
		}

		$this->db->where($condition);

		$query = $this->db->get($tableName);

		return $query->result();
	}



	public function getReturnOrderStatusLabel($id)
	{

		$label = '';

		if ($id == '0') {

			$label = 'Not Confirmed';
		} else if ($id == '1') {

			$label = 'Print';
		} else if ($id == '2') {

			$label = 'Pending';
		} else if ($id == '3') {

			$label = '<span class="tracking-complete">Approved</span>';
		} else if ($id == '4') {

			$label = '<span class="tracking-missing">Partially Approved</span>';
		} else if ($id == '5') {

			$label = '<span class="tracking-incomplete">Rejected</span>';
		}

		return $label;
	}



	public function get_custom_variables()

	{

		$query = $this->db->get('custom_variables');

		return $query->result_array();
	}

	public function get_customer_types()

	{

		$query = $this->db->get('customers_type_master');

		return $query->result_array();
	}

	public function get_account_manager()

	{

		$query = $this->db->get('acc_managers_master');

		return $query->result_array();
	}



	public function update_custom_variable_master($update_array)

	{

		$LoginID = $this->session->userdata('LoginID');

		$time = time();

		$ip = $_SERVER['REMOTE_ADDR'];

		foreach ($update_array as $key => $val) {

			if ($key != 'shipment_countries') {

				$this->db->where('identifier', $key);

				$this->db->set('value', $val);

				$this->db->set('updated_by', $LoginID);

				$this->db->set('updated_at', $time);

				$this->db->set('ip', $ip);

				$this->db->update('custom_variables');
			}
		}
	}



	public function get_cms_pages()

	{

		$query = $this->db->get('cms_pages');

		return $query->result_array();
	}



	public function get_customers_info()
	{

		$this->db->select('id,email_id, first_name, last_name');

		$query = $this->db->get('customers');

		return $query->result_array();
	}



	public function getRefundOrderStatusLabel($id)
	{

		$label = '';

		if ($id == '0') {

			$label = 'Pending';
		} else if ($id == '1') {

			$label = '<span class="tracking-complete">Completed</span>';
		} else if ($id == '2') {

			$label = '<span class="tracking-incomplete">Rejected</span>';
		}

		return $label;
	}



	public function getPaymentTypeLabel($id)
	{

		$label = '-';

		if ($id == '1') {

			$label = 'Direct Payment';
		} else if ($id == '2') {

			$label = 'Split Payment';
		} else if ($id == '3') {

			$label = 'Voucher Payment';
		}

		return $label;
	}



	public function get_webshop_texts()

	{

		$this->db->select('*');

		$query = $this->db->get('website_texts');

		return $query->result_array();
	}



	public function get_states_id($name)

	{

		$this->db->select('state_code');

		$this->db->where(array('state_name' => $name));

		$query = $this->db->get('country_state_master_in');

		$result = $query->row();

		return $result;
	}



	public function get_states($name)

	{

		$this->db->select('state_code');

		$this->db->where(array('state_name' => $name));

		$query = $this->db->get('country_state_master_in');

		$result = $query->row();

		return $result;
	}



	// invoice amount to text

	function getIndianCurrencytoText(float $number)

	{

		$no = floor($number);

		$decimal = round($number - $no, 2) * 100;

		$decimal_part = $decimal;

		$hundred = null;

		$hundreds = null;

		$digits_length = strlen($no);

		$decimal_length = strlen($decimal);

		$i = 0;

		$str = array();

		$str2 = array();

		$words = array(
			0 => '',
			1 => 'One',
			2 => 'Two',

			3 => 'Three',
			4 => 'Four',
			5 => 'Five',
			6 => 'Six',

			7 => 'Seven',
			8 => 'Eight',
			9 => 'Nine',

			10 => 'Ten',
			11 => 'Eleven',
			12 => 'Twelve',

			13 => 'Thirteen',
			14 => 'Fourteen',
			15 => 'Fifteen',

			16 => 'Sixteen',
			17 => 'Seventeen',
			18 => 'Eighteen',

			19 => 'Nineteen',
			20 => 'Twenty',
			30 => 'Thirty',

			40 => 'Forty',
			50 => 'Fifty',
			60 => 'Sixty',

			70 => 'Seventy',
			80 => 'Eighty',
			90 => 'Ninety'
		);

		$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');



		while ($i < $digits_length) {

			$divider = ($i == 2) ? 10 : 100;

			$number = floor($no % $divider);

			$no = floor($no / $divider);

			$i += $divider == 10 ? 1 : 2;

			if ($number) {

				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;

				$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;

				$str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
			} else $str[] = null;
		}

		$d = 0;

		while ($d < $decimal_length) {

			$divider = ($d == 2) ? 10 : 100;

			$decimal_number = floor($decimal % $divider);

			$decimal = floor($decimal / $divider);

			$d += $divider == 10 ? 1 : 2;

			if ($decimal_number) {

				$plurals = (($counter = count($str2)) && $decimal_number > 9) ? 's' : null;

				$hundreds = ($counter == 1 && $str2[0]) ? ' and ' : null;

				@$str2[] = ($decimal_number < 21) ? $words[$decimal_number] . ' ' . $digits[$decimal_number] . $plural . ' ' . $hundred : $words[floor($decimal_number / 10) * 10] . ' ' . $words[$decimal_number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
			} else $str2[] = null;
		}



		$Rupees = implode('', array_reverse($str));

		$paise = implode('', array_reverse($str2));

		$paise = ($decimal_part > 0) ? $paise . ' Paise' : '';

		return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
	}



	// send email with attchment

	public function sendHTMLMailSMTPAttchment($to, $subject, $content, $from_email = '', $attachment = "", $webshop_smtp_host = "", $webshop_smtp_port = "", $webshop_smtp_username = "", $webshop_smtp_password = "", $webshop_smtp_secure = "")

	{

		$email = 'ranjana.patel@bcod.co.in';

		$this->load->library('email');

		if ($webshop_smtp_host != '' && $webshop_smtp_port != '' && $webshop_smtp_username != '' && $webshop_smtp_password != '' && $webshop_smtp_secure != '') {

			$config = array(

				'protocol'  => 'smtp',

				'smtp_host' => $webshop_smtp_host,

				'smtp_port' => $webshop_smtp_port,

				'smtp_user' => $webshop_smtp_username,

				'smtp_pass' => $webshop_smtp_password,

				'mailtype'  => 'html',

				'charset'   => 'utf-8',

				'smtp_crypto' => $webshop_smtp_secure,

			);

			$this->email->initialize($config);
		}

		$this->email->set_newline("\r\n");

		$this->email->from($from_email); // change it to yours

		$this->email->to($to); // change it to yours

		$this->email->subject($subject);

		$this->email->message($content);

		$this->email->set_mailtype("html");

		if ($attachment != '') {

			$this->email->attach($attachment);
		}

		if ($this->email->send()) {

			return true;
		} else {

			return false;
		}
	}



	// convert

	public function convert_number_to_words($number)

	{

		$hyphen = ' ';

		$conjunction = ' and ';

		$separator = ' ';

		$negative = 'negative ';

		$decimal = ' and Cents ';

		$dictionary = array(

			0 => 'Zero',

			1 => 'One',

			2 => 'Two',

			3 => 'Three',

			4 => 'Four',

			5 => 'Five',

			6 => 'Six',

			7 => 'Seven',

			8 => 'Eight',

			9 => 'Nine',

			10 => 'Ten',

			11 => 'Eleven',

			12 => 'Twelve',

			13 => 'Thirteen',

			14 => 'Fourteen',

			15 => 'Fifteen',

			16 => 'Sixteen',

			17 => 'Seventeen',

			18 => 'Eighteen',

			19 => 'Nineteen',

			20 => 'Twenty',

			30 => 'Thirty',

			40 => 'Fourty',

			50 => 'Fifty',

			60 => 'Sixty',

			70 => 'Seventy',

			80 => 'Eighty',

			90 => 'Ninety',

			100 => 'Hundred',

			1000 => 'Thousand',

			1000000 => 'Million',

		);

		if (!is_numeric($number)) {

			return false;
		}

		if ($number < 0) {

			return $negative . $this->convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {

			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {

			case $number < 21:

				$string = $dictionary[$number];

				break;

			case $number < 100:

				$tens = ((int)($number / 10)) * 10;

				$units = $number % 10;

				$string = $dictionary[$tens];

				if ($units) {

					$string .= $hyphen . $dictionary[$units];
				}

				break;

			case $number < 1000:

				$hundreds = $number / 100;

				$remainder = $number % 100;

				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];

				if ($remainder) {

					$string .= $conjunction . $this->convert_number_to_words($remainder);
				}

				break;

			default:

				$baseUnit = pow(1000, floor(log($number, 1000)));

				$numBaseUnits = (int)($number / $baseUnit);

				$remainder = $number % $baseUnit;

				$string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];

				if ($remainder) {

					$string .= $remainder < 100 ? $conjunction : $separator;

					$string .= $this->convert_number_to_words($remainder);
				}

				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {

			$string .= $decimal;

			$words = array();

			foreach (str_split((string)$fraction) as $number) {

				$words[] = $dictionary[$number];
			}

			$string .= implode(' ', $words);
		}

		return $string;
	}





	// product category

	function getProductsMaintCategoryNames($product_id)
	{

		$catgory_name = '-';

		$main_db_name = $this->db->database;

		$sql = "SELECT GROUP_CONCAT(c.cat_name separator ',') as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id  and level = 0";

		$query = $this->db->query($sql);

		$Row = $query->row();

		if (isset($Row) && $Row->cat_name != '') {

			$catgory_name = $Row->cat_name;
		}

		return $catgory_name;
	}



	public function get_exceptional_taxes_set()

	{

		$result = $this->db->query("SELECT * FROM `exceptional_taxes_set`");

		return $result->row();
	}



	public function get_exceptional_CatMenus($id)

	{

		$result = $this->db->get_where('exceptional_taxes_set_details', array('exc_taxes_id' => $id,))->result();

		return $result;
	}



	/*cancel order*/

	function incrementAvailableQty($product_id, $qty_ordered)
	{

		$params = array($qty_ordered, $product_id);

		$update_row = $this->db->query("UPDATE products_inventory SET available_qty = available_qty + ?, is_in_stock = 1  WHERE product_id = ?  ", $params);
	}



	function incrementAvailableQtyByShopCode($shopcode, $product_id, $qty_ordered)
	{

		$params = array($qty_ordered, $product_id);

		$shop_db =  DB_NAME_PREFIX . $shopcode;

		$sql = "SELECT * FROM $shop_db.products_inventory where product_id=$product_id";

		$update_row = $this->db->query("UPDATE $shop_db.products_inventory SET available_qty = available_qty + ?, is_in_stock = 1  WHERE product_id = ?  ", $params);
	}

	/*end cancel order*/



	// api

	/*india time set*/

	function indiaTimeSet()
	{

		$startTime = date('Y-m-d H:i:s');

		$data['date'] = date('Y-m-d', strtotime('+5 hour +30 minutes', strtotime($startTime)));

		$data['time'] = date('H:i:s', strtotime('+5 hour +30 minutes', strtotime($startTime)));

		return $data;
	}

	/*end india time set*/

	/*special charatcter*/

	function specialCharatcterRemove($string)
	{

		$string = str_replace(array('#', '&', '%', ';', 'amp;', '\\', '/'), '', $string);

		$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', ' ', $string);

		return trim($string, '-');
	}

	/*end special charatcter*/

	public function getWarehouse_status_name($warehouse_status)
	{

		if ($warehouse_status == "") {

			$warehouse_status_name = '-';
		} elseif ($warehouse_status == "0") {

			$warehouse_status_name = 'Locked for sending';
		} elseif ($warehouse_status == "1") {

			$warehouse_status_name = 'Sent';
		} elseif ($warehouse_status == "2") {

			$warehouse_status_name = 'Acknowledged';
		} elseif ($warehouse_status == "3") {

			$warehouse_status_name = 'Shipped';
		} elseif ($warehouse_status == "4") {

			$warehouse_status_name = 'Received';
		} elseif ($warehouse_status == "5") {

			$warehouse_status_name = 'Partial Shipped';
		} elseif ($warehouse_status == "6") {

			$warehouse_status_name = 'Zero Shipped';
		} elseif ($warehouse_status == "7") {

			$warehouse_status_name = 'Cancelled';
		} elseif ($warehouse_status == "8") {

			$warehouse_status_name = 'Delivered';
		} elseif ($warehouse_status == "9") {

			$warehouse_status_name = 'Partially Received';
		}

		return $warehouse_status_name;
	}





	function array_group_data($input_array, $column_name): array

	{

		$output_array = [];

		foreach ($input_array as $array_element) {

			$output_array[$array_element[$column_name]][] = $array_element;
		}

		return $output_array;
	}



	function array_group($input_array, $column_name): array

	{

		$output_array = [];

		foreach ($input_array as $array_element) {

			$output_array[$array_element->$column_name][] = $array_element;
		}

		return $output_array;
	}



	public function getVariantByID($id)
	{

		$result = $this->db->get_where('eav_attributes', array('id' => $id))->row();

		return $result;
	}



	public function CheckShipmentStatus($status_id)
	{

		$returnStatus = '';

		if (isset($status_id)) {

			$returnStatus = (new ShipmentStatusEnum())->label($status_id);
		}

		return $returnStatus;
	}



	public function get_data_count($table_name, $LoginID = '')
	{



		$this->db->select('*');

		if ($table_name == 'publisher' || $table_name == 'products') {

			$this->db->where('remove_flag', '0');

			if ($table_name == 'products') {

				$this->db->where('publisher_id', $LoginID);

				$this->db->where_not_in('product_type', 'conf-simple');
			}
		}

		$this->db->from($table_name);

		$count = $this->db->get()->num_rows();

		return $count;
	}





	public function getAttributesOptions($AttOptionArray)
	{

		$returnData = '';

		if (isset($AttOptionArray) && !empty($AttOptionArray)) {

			$AOdata = json_decode($AttOptionArray);

			$combine = '';

			foreach ($AOdata as $bdkey => $bdval) {

				$Akey = $bdkey;

				$BValue = $bdval;

				$AttrDataresult = $this->db->get_where('eav_attributes', array('id' => $Akey))->row();

				if ($AttrDataresult == false) {

					$attr_name = '';
				} else {

					$attr_name = $AttrDataresult->attr_name;
				}



				$this->db->select('group_concat(attr_options_name) as optionName');

				$this->db->from('eav_attributes_options');

				$this->db->where("id IN (" . $BValue . ")");

				$query = $this->db->get();

				$resultArr = $query->row();

				$combine .= '(' . $attr_name . ' : ' . $resultArr->optionName . '),';
			}

			$returnData = substr($combine, 0, -1);
		}

		return $returnData;
	}



	function userPermission($LoginID, $email)
	{



		$fbc_user_id = $this->GetUserByUserId($LoginID);





		// if ($fbc_user_id->role_id == 0) {

		// 	$role_name = 'Super Admin';

		// }



		if (isset($fbc_user_id)) {

			$resource_access[] = '';



			$role_id = $fbc_user_id->id;

			$email =  $fbc_user_id->email;

			$flag = $fbc_user_id->remove_flag;



			$getRoleMaster = $this->GetEmpRoleById($role_id, $email, $flag);



			if ($email == $getRoleMaster->email && $getRoleMaster->remove_flag == 0) {

				$resource_access = true;
			} else {



				$resource_access = '';
			}
		} else {

			$resource_access = '';
		}



		// $resource_access_data = array(

		// 	'resource_access' => $resource_access,

		// 	'role_name' => $role_name

		// );

		return $resource_access;
	}

	public function getOrderDataByb2bOrderId($order_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_orders');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_messaging($publisher_id)
{
    $this->db->select('product_questions.*, products.name as product_name');
    $this->db->from('product_questions');
    $this->db->join('products', 'products.id = product_questions.product_id', 'left');
    $this->db->where('merchant_id', $publisher_id);
    $this->db->order_by('created_at', 'DESC'); // Get newest messages first

    $query = $this->db->get();
    $all_messages = $query->result_array();

    $grouped = [];

    foreach ($all_messages as $msg) {
        $customer_identifier = ($msg['customer_id'] > 0) ? $msg['customer_id'] : $msg['email'];
        $key = $msg['product_id'] . '_' . $customer_identifier;

        if (!isset($grouped[$key])) {
            // This is the first time we see this conversation (the newest message)
            $grouped[$key] = $msg;
        } else {
            // We have seen this conversation before. 
            // If the current row we are looking at is "answered", 
            // update the grouped row to show "answered" too.
            if ($msg['status'] == 'answered' || !empty($msg['merchant_reply'])) {
                $grouped[$key]['status'] = 'answered';
                $grouped[$key]['is_replied'] = 1;
            }
        }
    }

    return array_values($grouped);
}

	public function checkAndUpdateMainOrderStatus($webshop_order_id)
	{
		if (empty($webshop_order_id)) {
			return false;
		}

		$this->db->select('order_id, status');
		$this->db->from('b2b_orders');
		$this->db->where('webshop_order_id', $webshop_order_id);
		$query = $this->db->get();
		$sub_orders = $query->result_array();

		if (!empty($sub_orders)) {
			$all_completed = true;
			$completed_statuses = [2, 8, 9, 23, 24];

			foreach ($sub_orders as $sub) {
				if ($sub['status'] == 3) {
					continue;
				}
				if (!in_array($sub['status'], $completed_statuses)) {
					$all_completed = false;
					break;
				}
			}

			if ($all_completed) {
				$this->db->where('order_id', $webshop_order_id);
				$this->db->set('status', 2);
				$this->db->set('updated_at', time());
				return $this->db->update('sales_order');
			} else {
				// If not all sub-orders are completed, ensure sales_order is not marked as complete (status 2)
				$this->db->where('order_id', $webshop_order_id);
				$this->db->where('status', 2);
				$this->db->set('status', 1);
				$this->db->set('updated_at', time());
				$this->db->update('sales_order');
			}
		}

		return false;
	}


}
