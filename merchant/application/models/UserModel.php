<?php
class UserModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();

	}
	
	public function checkUserIdentifierExist($identifier)
	{
		$result = $this->db->get_where('fbc_users', array('identifier' => $identifier))->result();
		//echo $this->db->last_query();
		return $result;
	}
	
	public function checkIdentifierExistDynamic($tableName, $identifier)
	{
		$result = $this->db->get_where($tableName, array('identifier' => $identifier))->result();
		return $result;
	}
	
	function updateUserIdentifierByUserId($fbc_user_id, $identifier) {
		
		$data	=  array('identifier' => $identifier);
		$this->db->where('fbc_user_id',$fbc_user_id);
		$this->db->update('fbc_users',$data);
		return true;
	}
	
	function updateRememberToken($id, $remember_token) {
		
		$data	=  array('remember_token' => $remember_token);
		$this->db->where('id',$id);
		$this->db->update('adminusers',$data);
		return true;
	}
	
	function updatePasswordResetToken($fbc_user_id, $password_reset_token) {
		
		$data	=  array('password_reset_token' => $password_reset_token);
		$this->db->where('fbc_user_id',$fbc_user_id);
		$this->db->update('fbc_users',$data);
		return true;
	}
	
	function updatePassword($LogindID, $inputPassword,$password) {

		// print_r($confpassword);die;
		$data	=  array('password' => $password, 'updated_at' => strtotime(date('Y-m-d H:i:s')));
		$this->db->where('id',$LogindID);
		$this->db->update('publisher',$data);
		// echo $this->db->last_query();die;
		// if($this->db->affected_rows() > 0){
		// 	return true;
		// }else{
		// 	return false;
		// }

		// $data	=  array('remember_token' => $password);
		// $this->db->where('id',$LogindID);
		// $this->db->update('adminusers',$data);

		return true;
	}
	
	public function insertIntoLoginSession($accessToken, $login_id)
	{
		$insertdata = array(
			'sessionid'		=> $accessToken,
			'login_id'		=> $login_id,
			'login_time'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('adminsession', $insertdata);
	}

	public function getUserByEmail($email)
	{
		$result = $this->db->get_where('publisher', array('email' => $email, 'remove_flag' => 0))->row();
		return $result;
	}
	
	public function getUserdataByEmail($email)
	{
		   $this->db->select('p.*, pp.*');  // select all columns from both tables
			$this->db->from('publisher p');
			$this->db->join('publisher_payment_details pp', 'pp.publisher_id = p.id', 'left'); // left join in case no payment details
			$this->db->where('p.email', $email);
			$this->db->where('p.remove_flag', 0);
			$query = $this->db->get();
			return $query->row(); 
		
	}
	
	public function getUserByMerchantId($ID)
	{
	
		$this->db->select('p.*, pp.*');  // select all columns from both tables
		$this->db->from('publisher p');
		$this->db->join('publisher_payment_details pp', 'pp.publisher_id = p.id', 'left'); // left join in case no payment details
		$this->db->where('p.id', $ID);
		$query = $this->db->get();
		return $query->row(); 
	}

	public function getShopDetailsByShopId($shop_id)
	{
		if (!$this->db->table_exists('fbc_users_shop')) {
			return null;
		}
		$result = $this->db->get_where('fbc_users_shop', array('shop_id' => $shop_id))->row();
		return $result;
	}
	
	public function getShopDetailsByfbcuserid($fbc_user_id)
	{
		if (!$this->db->table_exists('fbc_users_shop')) {
			return null;
		}
		$result = $this->db->get_where('fbc_users_shop', array('fbc_user_id' => $fbc_user_id))->row();
		return $result;
	}
	
	public function checkUserExistByEmail($email)
	{
		if (!$this->db->table_exists('fbc_users')) {
			return 0;
		}
		$result = $this->db->get_where('fbc_users', array('email' => $email))->num_rows();
		return $result;
	}
	
	public function getUserByUserId($fbc_user_id)
	{
		if (!$this->db->table_exists('fbc_users')) {
			return null;
		}
		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $fbc_user_id))->row();
		return $result;
	}
	
	public function getUserDetails($fbc_user_id, $email)
	{
		if (!$this->db->table_exists('fbc_users')) {
			return null;
		}
		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $fbc_user_id, 'email' => $email))->row();
		//echo $this->db->last_query();
		return $result;
	}
	
	public function getActiveUsersWithoutDB()
    {   
		if (!$this->db->table_exists('fbc_users') || !$this->db->table_exists('fbc_users_shop')) {
			return array();
		}
		$this->db->select('FU.fbc_user_id, FUS.shop_id');
		$this->db->from('fbc_users FU');
		$this->db->join('fbc_users_shop FUS', 'FU.fbc_user_id = FUS.fbc_user_id');
		//$this->db->where(array('FU.status' => 1, 'FU.email_verification_status' => 1, 'FUS.database_name' => null));
		$this->db->where(array('FU.status' => 1, 'FU.email_verification_status' => 1));
		$this->db->where('FUS.database_name',null);
		$this->db->or_where('FUS.database_name','');
		$this->db->order_by('FU.fbc_user_id','ASC');	
		$query = $this->db->get();
		$result = $query->result();
		
		return $result;
    }
	
	function updateDBName($shop_id, $db_name) {
		if (!$this->db->table_exists('fbc_users_shop')) {
			return false;
		}
		$data = array('database_name' => $db_name);
		$this->db->where('shop_id',$shop_id);
		$this->db->update('fbc_users_shop',$data);
		
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function ip_visitor_country()
	{
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		$country  = "Unknown";
		if(filter_var($client, FILTER_VALIDATE_IP)){
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP)){
			$ip = $forward;
		}
		else{
			$ip = $remote;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=".$ip);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$ip_data_in = curl_exec($ch); // string
		curl_close($ch);

		$ip_data = json_decode($ip_data_in,true);
		$ip_data = str_replace('&quot;', '"', $ip_data);
		$cCode = "";
		if($ip_data && $ip_data['geoplugin_countryName'] != null ) {
			$country = $ip_data['geoplugin_countryName'];
			$cCode = $ip_data['geoplugin_countryCode'];
			
		}
		return $cCode;
	}

	public function update_fbc_users($update_array)
	{
		if (!$this->db->table_exists('fbc_users')) {
			return false;
		}
		$this->db->where('fbc_user_id', $_SESSION['LoginID']);
		$query = $this->db->update('fbc_users',$update_array); 
		
		return $query;
	} 

	public function update_fbc_users_shop($update_array)
	{
		if (!$this->db->table_exists('fbc_users_shop')) {
			return false;
		}
		$this->db->where('fbc_user_id', $_SESSION['LoginID']);
		$query = $this->db->update('fbc_users_shop',$update_array); 
		
		return $query;
	}

	public function insert_employee($insert_array_fbc_users,$identifier)
	{
		$insert_query = $this->db->insert('fbc_users',$insert_array_fbc_users);
		if($insert_query)
		{
			$fbc_user_id= $this->db->insert_id();
			$Identifier = $identifier."-".$fbc_user_id;
			$update_query = $this->UserModel->updateUserIdentifierByUserId($fbc_user_id, $Identifier);
			return $update_query;
		}
		
	}
	
	public function insert_employee_details($fbc_users_emp_details)
	{
		$insert_query = $this->db->insert('fbc_users_emp_details',$fbc_users_emp_details);
		return $insert_query;
	}
	
	public function update_employee($update_array,$user_id)
	{
		$this->db->where('fbc_user_id', $user_id);
		$query = $this->db->update('fbc_users',$update_array); 
		// echo $this->db->last_query();
		return $query;
	}
	
	public function update_employee_details($update_array,$user_id)
	{
		$this->db->where('fbc_user_id', $user_id);
		$query = $this->db->update('fbc_users_emp_details',$update_array); 
		// echo $this->db->last_query();
		return $query;
	}
	
	public function update_password($update_date,$fbc_user_id)
	{
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users',$update_date); 
		// echo $this->db->last_query();
		return $query;
	}

	public function update_email($update_data,$fbc_user_id)
	{
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users',$update_data); 
		// echo $this->db->last_query();
		return $query;
	}
	
	// public function getShopEmployeesDetails($fbc_user_id)
	// {

		
	// 	$fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID

	// 	$shop_id		=	$this->session->userdata('ShopID');

		

	// 	$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');

	// 	if(isset($FBCData) && $FBCData->database_name!='')

	// 	{

	// 		$fbc_user_database=$FBCData->database_name;

			

	// 		$this->load->database();

	// 		$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		

	// 		$this->seller_db = $this->load->database($config_app,TRUE);

	// 		if($this->seller_db->conn_id) {

	// 			//do something

	// 		} else {

	// 			redirect(base_url());

	// 		}

	// 	}else{

	// 		redirect(base_url());

	// 	}
		
	// 	$main_db_name=$this->seller_db->database;
	// 	$this->seller_db->select();
	// 	// $this->db->select($main_db_name.'.employee_role_master.*');
	// 	$this->db->where('parent_id', $fbc_user_id);	
	// 	$this->db->from('fbc_users fu');
	// 	$this->db->join('fbc_users_emp_details fued', 'fu.fbc_user_id = fued.fbc_user_id');
	// 	$this->db->join($main_db_name.'.employee_role_master emp', 'emp.id = fued.role_in_company');
		
	// 	$query = $this->db->get(); 
	// 	// echo $this->db->last_query();die();
	// 	if ($query->num_rows() > 0)
	// 	   {
	// 			$result = $query->result_array();
	// 			return $result;
	// 	   }
	// 	   else{
	// 		   return false;
	// 	   }
	// }
	
	public function change_employee_status($status,$fbc_user_id)
	{
		$this->db->set('status',$status);
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users');
		return $query;
	}
	
	public function email_exists($new_email)
	{
		$this->db->where('email',$new_email);
		$query = $this->db->get('fbc_users');
		if($query->num_rows() > 0){
			return true;
		}else{
			return false;
		}
	}

	public function getSaleQuote($flag,$date){
		$this->db->select('quote_id');
		$this->db->from('sales_quote');
		if($flag > 0){
			$this->db->where('customer_id >', 0);
			$this->db->where('updated_at <' ,$date);	
		}
		else{	
			$this->db->where('customer_id', 0);
			$this->db->where('updated_at <' ,$date);
		}
		$query = $this->db->get();
		//echo $this->db->last_query();
		return $query->result();
	}

	public function deleteData($quote_ids){

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_address');

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_items');

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_payment');
	
		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote');

	}

	public function deleteLoginSession($login_time,$logout_time){

		$this->db->where('login_time <', $login_time);
		$this->db->or_where('logout_time <',$logout_time AND 'logout_time'> 0);
		$this->db->delete('login_session');
		if ( $this->db->affected_rows() > 0 ) {
			return true;
		}
		else {
			return false;
		}
		
	}

	public function get_merchant_cycle_start($merchant_id)
	{
		$this->load->model('Subscription_model');
		$plan = $this->Subscription_model->get_merchant_plan($merchant_id);
		if (!empty($plan['subscription']['start_date'])) {
			return $plan['subscription']['start_date'];
		}
		// Fallback for Starter plan or merchants without explicit subscription start date:
		// Use beginning of current month
		return date('Y-m-01 00:00:00');
	}

	public function ensure_deal_usage_schema()
	{
		if (!$this->db->field_exists('daily_deal_starts_at', 'products')) {
			@$this->db->query("ALTER TABLE products ADD COLUMN daily_deal_starts_at INT(11) NULL DEFAULT NULL AFTER daily_deals");
		}
		if (!$this->db->field_exists('flash_sale_starts_at', 'products')) {
			@$this->db->query("ALTER TABLE products ADD COLUMN flash_sale_starts_at INT(11) NULL DEFAULT NULL AFTER flash_sale");
		}

		if (!$this->db->table_exists('merchant_deal_usage')) {
			$sql = "CREATE TABLE IF NOT EXISTS `merchant_deal_usage` (
				`id` INT AUTO_INCREMENT PRIMARY KEY,
				`merchant_id` INT NOT NULL,
				`product_id` INT NOT NULL,
				`deal_type` ENUM('daily_deal', 'flash_sale') NOT NULL,
				`starts_at` INT NULL,
				`ends_at` INT NULL,
				`created_at` DATETIME NOT NULL,
				INDEX `idx_m_deal_type` (`merchant_id`, `deal_type`, `created_at`),
				INDEX `idx_m_prod` (`merchant_id`, `product_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			@$this->db->query($sql);

			$this->backfill_deal_usage();
		}
	}

	public function backfill_deal_usage()
	{
		if (!$this->db->table_exists('merchant_deal_usage')) {
			return;
		}

		// 1. Backfill daily deals from products table
		$dd_prods = $this->db->select('id, publisher_id, daily_deals, daily_deal_starts_at, daily_deal_ends_at, updated_at')
			->from('products')
			->where('(daily_deals = 1 OR daily_deal_ends_at > 0)', NULL, FALSE)
			->where('publisher_id >', 0)
			->get()->result();

		foreach ($dd_prods as $p) {
			$exists = $this->db->where([
				'merchant_id' => $p->publisher_id,
				'product_id'  => $p->id,
				'deal_type'   => 'daily_deal'
			])->count_all_results('merchant_deal_usage');

			if ($exists == 0) {
				$start_ts = !empty($p->daily_deal_starts_at) ? (int)$p->daily_deal_starts_at : (!empty($p->updated_at) ? (int)$p->updated_at : time());
				$this->db->insert('merchant_deal_usage', [
					'merchant_id' => $p->publisher_id,
					'product_id'  => $p->id,
					'deal_type'   => 'daily_deal',
					'starts_at'   => $p->daily_deal_starts_at ?: $start_ts,
					'ends_at'     => $p->daily_deal_ends_at ?: 0,
					'created_at'  => date('Y-m-d H:i:s', $start_ts)
				]);
			}
		}

		// 2. Backfill flash sales from products table
		$fs_prods = $this->db->select('id, publisher_id, flash_sale, flash_sale_starts_at, flash_sale_ends_at, updated_at')
			->from('products')
			->where('(flash_sale = 1 OR flash_sale_ends_at > 0)', NULL, FALSE)
			->where('publisher_id >', 0)
			->get()->result();

		foreach ($fs_prods as $p) {
			$exists = $this->db->where([
				'merchant_id' => $p->publisher_id,
				'product_id'  => $p->id,
				'deal_type'   => 'flash_sale'
			])->count_all_results('merchant_deal_usage');

			if ($exists == 0) {
				$start_ts = !empty($p->flash_sale_starts_at) ? (int)$p->flash_sale_starts_at : (!empty($p->updated_at) ? (int)$p->updated_at : time());
				$this->db->insert('merchant_deal_usage', [
					'merchant_id' => $p->publisher_id,
					'product_id'  => $p->id,
					'deal_type'   => 'flash_sale',
					'starts_at'   => $p->flash_sale_starts_at ?: $start_ts,
					'ends_at'     => $p->flash_sale_ends_at ?: 0,
					'created_at'  => date('Y-m-d H:i:s', $start_ts)
				]);
			}
		}
	}

	public function record_deal_usage($merchant_id, $product_id, $deal_type = 'daily_deal', $starts_at = null, $ends_at = null)
	{
		$this->ensure_deal_usage_schema();
		$now = time();
		if (!$starts_at) $starts_at = $now;
		if (!$ends_at) $ends_at = ($deal_type === 'daily_deal') ? ($starts_at + 86400) : ($starts_at + (7 * 86400));

		// Check if there is already an active/ongoing usage record for this product
		$existing = $this->db->where([
			'merchant_id' => $merchant_id,
			'product_id'  => $product_id,
			'deal_type'   => $deal_type
		])->where('ends_at >', $now)->order_by('id', 'DESC')->limit(1)->get('merchant_deal_usage')->row();

		if ($existing) {
			$this->db->where('id', $existing->id)->update('merchant_deal_usage', [
				'starts_at' => $starts_at,
				'ends_at'   => $ends_at
			]);
			return $existing->id;
		}

		// New deal activation: consumes a slot permanently
		$this->db->insert('merchant_deal_usage', [
			'merchant_id' => $merchant_id,
			'product_id'  => $product_id,
			'deal_type'   => $deal_type,
			'starts_at'   => $starts_at,
			'ends_at'     => $ends_at,
			'created_at'  => date('Y-m-d H:i:s')
		]);
		return $this->db->insert_id();
	}

	public function get_used_daily_deals_count($merchant_id, $exclude_active_product_id = null)
	{
		$this->ensure_deal_usage_schema();
		$this->expire_deals(time());

		$cycle_start = $this->get_merchant_cycle_start($merchant_id);

		$this->db->from('merchant_deal_usage');
		$this->db->where('merchant_id', $merchant_id);
		$this->db->where('deal_type', 'daily_deal');
		$this->db->where('created_at >=', $cycle_start);

		if (!empty($exclude_active_product_id)) {
			$now = time();
			$this->db->group_start();
			$this->db->where('product_id !=', $exclude_active_product_id);
			$this->db->or_where('ends_at <=', $now);
			$this->db->group_end();
		}

		$used_count = $this->db->count_all_results();

		// Check for any product in products table marked daily_deals = 1 not yet in merchant_deal_usage
		$this->db->from('products');
		$this->db->where('publisher_id', $merchant_id);
		$this->db->where('daily_deals', 1);
		if (!empty($exclude_active_product_id)) {
			$this->db->where('id !=', $exclude_active_product_id);
		}
		$active_prod_count = $this->db->count_all_results();

		return (int)max($used_count, $active_prod_count);
	}

	public function get_used_flash_sale_count($merchant_id, $exclude_active_product_id = null)
	{
		$this->ensure_deal_usage_schema();
		$this->expire_deals(time());

		$cycle_start = $this->get_merchant_cycle_start($merchant_id);

		$this->db->from('merchant_deal_usage');
		$this->db->where('merchant_id', $merchant_id);
		$this->db->where('deal_type', 'flash_sale');
		$this->db->where('created_at >=', $cycle_start);

		if (!empty($exclude_active_product_id)) {
			$now = time();
			$this->db->group_start();
			$this->db->where('product_id !=', $exclude_active_product_id);
			$this->db->or_where('ends_at <=', $now);
			$this->db->group_end();
		}

		$used_count = $this->db->count_all_results();

		$this->db->from('products');
		$this->db->where('publisher_id', $merchant_id);
		$this->db->where('flash_sale', 1);
		if (!empty($exclude_active_product_id)) {
			$this->db->where('id !=', $exclude_active_product_id);
		}
		$active_prod_count = $this->db->count_all_results();

		return (int)max($used_count, $active_prod_count);
	}

	public function get_daily_deals_limit($merchant_id)
	{
		$this->load->model('Subscription_model');
		$base_limit = $this->Subscription_model->get_base_daily_deals_limit($merchant_id);
		$cycle_start = $this->get_merchant_cycle_start($merchant_id);

		$this->db->select('m.service_id, m.qty');
		$this->db->from('merchant_addon_purchases m');
		$this->db->join('addon_services s', 's.id = m.service_id', 'inner');
		$this->db->where('m.merchant_id', $merchant_id);
		$this->db->where('m.status', 'paid');
		$this->db->where('s.category_id', 1);
		$this->db->where('m.created_at >=', $cycle_start);
		$purchases = $this->db->get()->result();

		$addon_limit = 0;
		$service_limits = [2 => 1, 3 => 3, 4 => 7];

		foreach ($purchases as $purchase) {
			$limit_per_unit = isset($service_limits[$purchase->service_id]) ? $service_limits[$purchase->service_id] : 0;
			$addon_limit += $limit_per_unit * $purchase->qty;
		}

		return (int)($base_limit + $addon_limit);
	}

	public function get_active_daily_deals_count($merchant_id)
    {
        return $this->get_used_daily_deals_count($merchant_id);
    }

    public function expire_deals($now = null, $product_id = null)
    {
        if (!$now) $now = time();

        if (!$this->db->field_exists('default_special_price', 'products')) {
            @$this->db->query("ALTER TABLE products ADD COLUMN default_special_price DECIMAL(12,2) NULL DEFAULT NULL AFTER special_price");
        }

        // 1. Expire daily deals whose time has passed
        $this->db->select('id, special_price, default_special_price, special_price_from, special_price_to, daily_deals, daily_deal_ends_at');
        $this->db->from('products');
        if ($product_id) {
            $this->db->where('id', $product_id);
        }
        $this->db->where('daily_deals', 1);
        $this->db->where('daily_deal_ends_at >', 0);
        $this->db->where('daily_deal_ends_at <=', $now);
        $exp_dd = $this->db->get()->result();

        foreach ($exp_dd as $p) {
            $this->revert_product_deal($p, 'daily_deal', $now);
        }

        // 2. Expire flash sales whose time has passed
        $this->db->select('id, special_price, default_special_price, special_price_from, special_price_to, flash_sale, flash_sale_ends_at');
        $this->db->from('products');
        if ($product_id) {
            $this->db->where('id', $product_id);
        }
        $this->db->where('flash_sale', 1);
        $this->db->where('flash_sale_ends_at >', 0);
        $this->db->where('flash_sale_ends_at <=', $now);
        $exp_fs = $this->db->get()->result();

        foreach ($exp_fs as $p) {
            $this->revert_product_deal($p, 'flash_sale', $now);
        }

        // 3. Clean up orphaned deal rows in products_special_prices
        $this->cleanup_orphaned_special_prices($product_id, $now);
    }

    public function revert_product_deal($p, $type = 'daily_deal', $now = null)
    {
        if (!$now) $now = time();
        $update = [];
        if ($type === 'daily_deal') {
            $update['daily_deals'] = 0;
            $update['daily_deal_ends_at'] = 0;
        } else {
            $update['flash_sale'] = 0;
            $update['flash_sale_ends_at'] = 0;
        }

        // Check if regular special price was preserved in default_special_price
        $has_default_sp = (!empty($p->default_special_price) && (float)$p->default_special_price > 0);
        
        // Check if regular special_price_to is still active
        $sp_to_valid = true;
        if (!empty($p->special_price_to)) {
            $sp_to_ts = is_numeric($p->special_price_to) ? (int)$p->special_price_to : strtotime($p->special_price_to);
            if ($sp_to_ts > 0 && $sp_to_ts < $now) {
                $sp_to_valid = false;
            }
        }

        if ($has_default_sp && $sp_to_valid) {
            // Restore regular special price
            $restored_price = (float)$p->default_special_price;
            $update['special_price'] = $restored_price;
            $this->db->where('id', $p->id)->update('products', $update);

            // Synchronize products_special_prices
            $sp_row = $this->db->get_where('products_special_prices', ['product_id' => $p->id])->row();
            $sp_payload = [
                'product_id'    => $p->id,
                'special_price' => $restored_price,
                'offer_type'    => 'general',
                'updated_at'    => $now
            ];
            if (!empty($p->special_price_from)) {
                $sp_payload['special_price_from'] = $p->special_price_from;
            }
            if (!empty($p->special_price_to)) {
                $sp_payload['special_price_to'] = $p->special_price_to;
            }

            if (!empty($sp_row)) {
                $this->db->where('id', $sp_row->id)->update('products_special_prices', $sp_payload);
            } else {
                $sp_payload['created_at'] = $now;
                $this->db->insert('products_special_prices', $sp_payload);
            }
        } else {
            // No default special price, or regular special price has expired
            $update['special_price'] = null;
            $update['default_special_price'] = null;
            $update['special_price_from'] = null;
            $update['special_price_to'] = null;
            $this->db->where('id', $p->id)->update('products', $update);

            $this->db->where('product_id', $p->id)->delete('products_special_prices');
        }
    }

    public function cleanup_orphaned_special_prices($product_id = null, $now = null)
    {
        if (!$now) $now = time();

        if (!$this->db->table_exists('products_special_prices')) {
            return;
        }

        $this->db->select('p.id, p.special_price, p.default_special_price, p.special_price_from, p.special_price_to, sp.id as sp_id, sp.special_price as sp_special_price, sp.offer_type, sp.special_price_to as sp_to');
        $this->db->from('products p');
        $this->db->join('products_special_prices sp', 'sp.product_id = p.id', 'inner');
        $this->db->where('p.daily_deals', 0);
        $this->db->where('p.flash_sale', 0);
        if ($product_id) {
            $this->db->where('p.id', $product_id);
        }
        $orphans = $this->db->get()->result();

        foreach ($orphans as $row) {
            // If offer_type was daily_deal/flash_sale or products table has NULL/0 special price
            if (in_array($row->offer_type, ['daily_deal', 'flash_sale']) || empty($row->special_price) || (float)$row->special_price <= 0) {
                $has_default = (!empty($row->default_special_price) && (float)$row->default_special_price > 0);
                $to_valid = true;
                if (!empty($row->special_price_to)) {
                    $to_ts = is_numeric($row->special_price_to) ? (int)$row->special_price_to : strtotime($row->special_price_to);
                    if ($to_ts > 0 && $to_ts < $now) {
                        $to_valid = false;
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
                        'updated_at'    => $now
                    ]);
                } else {
                    $this->db->where('id', $row->id)->update('products', [
                        'special_price'         => null,
                        'default_special_price' => null,
                        'special_price_from'    => null,
                        'special_price_to'      => null
                    ]);
                    $this->db->where('id', $row->sp_id)->delete('products_special_prices');
                }
            } elseif (!empty($row->sp_to)) {
                $sp_to_ts = is_numeric($row->sp_to) ? (int)$row->sp_to : strtotime($row->sp_to);
                if ($sp_to_ts > 0 && $sp_to_ts < $now) {
                    $this->db->where('id', $row->id)->update('products', [
                        'special_price'         => null,
                        'default_special_price' => null,
                        'special_price_from'    => null,
                        'special_price_to'      => null
                    ]);
                    $this->db->where('id', $row->sp_id)->delete('products_special_prices');
                }
            }
        }
    }

	// application/models/UserModel.php
	public function get_flash_sale_limit($merchant_id)
	{
		$this->load->model('Subscription_model');
		if (!$this->Subscription_model->can_use_flash_sales($merchant_id)) {
			return 0;
		}

		$base_limit = $this->Subscription_model->get_base_flash_sale_limit($merchant_id);
		$cycle_start = $this->get_merchant_cycle_start($merchant_id);

		$this->db->select('m.service_id, m.qty');
		$this->db->from('merchant_addon_purchases m');
		$this->db->join('addon_services s', 's.id = m.service_id', 'inner');
		$this->db->where('m.merchant_id', $merchant_id);
		$this->db->where('m.status', 'paid'); // or 'active'
		$this->db->where('s.category_id', 2);    // flash sale category
		$this->db->where('m.created_at >=', $cycle_start);
		$purchases = $this->db->get()->result();

		$addon_limit = 0;
		$flash_service_limits = [
			5 => 1,
			6 => 3,
			7 => 7
		];

		foreach ($purchases as $purchase) {
			$limit_per_unit = isset($flash_service_limits[$purchase->service_id]) ? $flash_service_limits[$purchase->service_id] : 0;
			$addon_limit += $limit_per_unit * $purchase->qty;
		}

		return (int)($base_limit + $addon_limit);
	}

	public function get_active_flash_sale_count($merchant_id)
	{
		return $this->get_used_flash_sale_count($merchant_id);
	}


}
