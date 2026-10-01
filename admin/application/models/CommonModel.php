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

	public function __construct()
	{
	}
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
	public function GetEmpRoleById($roleId)
	{
		$this->db->select('*');
		$this->db->where('id', $roleId);
		$sql = $this->db->get('role_master');
		if ($sql->num_rows() > 0) {
			$result = $sql->row();
			return $result;
		} else {
			return false;
		}
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

	public function get_countries()
	{
		$query = $this->db->get_where('country_master');
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function get_faqs()
	{
		// $this->db->where('status', 0);
		$query = $this->db->get_where('faqs');
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function get_faqs_details($id)
	{
		$query = $this->db
			->select('*')
			->from('faqs')
			->where('id', $id)
			->order_by('id', 'DESC')
			->get();

		return $query->row_array(); // returns associative array
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


	public function get_help_desk($type = null)
    {
		$this->db->select('*'); 
		$this->db->from('help_desk');

		if ($type === 'merchant') {
			$this->db->where('category', 3); //yellow market select 
		} elseif ($type === 'shopper') {
			$this->db->where('category', 2); //merchant select 
		}

		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();
		return $query->result_array();
    }

    public function get_help_desk_details($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('help_desk');
        return $query->row_array(); // returns associative array
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
		// $this->db->group_by('currency_code');
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
		return $result;
	}

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '')
	{
		// $GlobalVar = $this->getGlobalVariableByIdentifier('fbc-admin-email');
		// if (isset($GlobalVar) && $GlobalVar->value != '') {
			$from_email = 'noreply@yellowmarkets.com';
		// }

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);

		$subject = (isset($email_subject) && $email_subject != '')
			? $email_subject
			: $emailTemplate->subject;

		
		$subject = str_replace($TempVars, $DynamicVars, $subject);

		$title = $emailTemplate->title;

		$emailBody = str_replace(
			$TempVars,
			$DynamicVars,
			$emailTemplate->content
		);

		$data['subject'] = $subject;
		$data['content'] = $emailBody;

		$content = $this->load->view(
			'email_template/email_content',
			$data,
			TRUE
		);

		if ($this->sendHTMLMailSMTP( $EmailTo, $subject, $content, $from_email, $attachment = "")) {
			return true;
		} else {
			return false;
		}
	}




	function GetUserByUserId($id)
	{
		$result = $this->db->get_where('adminusers', array('id' => $id))->row();
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

	public function sendHTMLMailSMTP($to, $subject, $content, $from_email='', $attachment="", $webshop_smtp_host="", $webshop_smtp_port="", $webshop_smtp_username="", $webshop_smtp_password="", $webshop_smtp_secure="")

 	{

		// $email='ranjana.patel@bcod.co.in';
		$email = 'ranjana.patel@bcod.co.in';
		$webshop_smtp_host = 'smtp.gmail.com'; // $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'noreply@yellowmarkets.com'; //$this->getCustomVariableByIdentifier('smtp_username') ?? 
		$webshop_smtp_password = 'fkyvohootmnwylkg'; //$this->getCustomVariableByIdentifier('smtp_password') ?? 
		$webshop_smtp_secure = 'ssl';
        
		$from_email='info@yellowmarkets.com';

		$this->load->library('email');

		if($webshop_smtp_host != '' && $webshop_smtp_port != '' && $webshop_smtp_username != '' && $webshop_smtp_password != '' && $webshop_smtp_secure != ''){

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

		$this->email->to($to);// change it to yours

		$this->email->bcc('deepak@bcod.co.in');// change it to yours

		$this->email->subject($subject);

		$this->email->message($content);

		$this->email->set_mailtype("html");

		if($this->email->send()){

			return true;

		}else{

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
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		// echo $query;die();
		return $query->row();
	}

	public function getSingleShopDataByID($tableName, $condition, $select)
	{
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		//echo $query;die();
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

	public function getOrderStatusLabel($id)
	{
		switch ($id) {
			case 0:
				return 'To Be Processed';
			case 1:
				return 'Processing';
			case 2:
				return 'Complete';
			case 3:
				return 'Cancelled';
			case 4:
				return 'Shipped';
			case 5:
				return 'Attempt 2';
			case 6:
				return 'Attempt 3';
			case 7:
				return 'Failed';
			case 8:
				return 'Delivered';
			case 9:
				return 'Collected';
			case 10:
				return 'YM Pickup Generated';
			case 11:
				return 'Pickup';
			case 12:
				return 'Received To Warehouse';
			case 13:
				return 'Collect From Warehouse';
			case 14:
				return 'Return Requested';
			case 15:
				return 'Replacement Requested';
			case 16:
				return 'Return Approved';
			case 17:
				return 'Refund Paid';
			case 18:
				return 'Replacement Approved';
			case 19:
				return 'Replaced';
			case 20:
				return 'Return Rejected';
			case 21:
				return 'Replacement Rejected';
			case 22:
				return 'Return Approved';
			case 23:
				return 'Collected';
			case 24:
				return 'Delivered';
			case 25:
				return 'Mark as Delivered';
			case 26:
				return 'Mark as Failed';
			case 27:
				return 'Re-Attempt';
			case 28:
				return 'Collected from Store';
			case 29:
				return 'Assign Delivery';
			case 30:
				return 'Not Ready';
			case 31:
				return 'Refund Rejected';
			case 32:
				return 'Refund Approved';
			case 33:
				return 'Refund Requested';

			default:
				return 'Unknown';
		}
	}


	public function getOrderShipmentLabel($id, $default = 'Own Delivery') {
		$id_str = trim((string)$id);
		if ($id_str === '2' || strtolower($id_str) === 'ym delivery' || strtolower($id_str) === 'ym') {
			return 'YM Delivery';
		} elseif ($id_str === '1' || strtolower($id_str) === 'own delivery' || strtolower($id_str) === 'own') {
			return 'Own Delivery';
		}
		return $default;
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
			0 => '', 1 => 'One', 2 => 'Two',
			3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
			7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
			10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
			13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
			16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
			19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
			40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
			70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
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

	public function get_data_count($table_name)
	{

		$this->db->select('*');
		if ($table_name == 'publisher' || $table_name == 'products') {
			$this->db->where('remove_flag', '0');

			if ($table_name == 'products') {
				$this->db->where_not_in('product_type', 'conf-simple');
			}
		}
		$this->db->from($table_name);
		$count = $this->db->get()->num_rows();
		return $count;
	}

	public function count_pending_publishers() {
		$this->db->from('publisher');   // make sure only once
		$this->db->where('status', 0);
		$this->db->where('remove_flag', 0); // as per your table definition
		return $this->db->count_all_results();
	}

	public function count_pending_products() {
		$this->db->from('products');   // make sure only once
		$this->db->where('status', 1);
		$this->db->where('approval_status', 0);
		 $this->db->where('product_type !=', 'conf-simple');
		$this->db->where('remove_flag', 0); // as per your table definition
		return $this->db->count_all_results();
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

	function userPermission($LoginID)
	{

		$fbc_user_id = $this->GetUserByUserId($LoginID);

		if ($fbc_user_id->role_id == 0) {
			$role_name = 'Super Admin';
		}

		if ($fbc_user_id->role_id > 0) {
			$resource_access[] = '';

			$role_id = $fbc_user_id->role_id;

			$getRoleMaster = $this->GetEmpRoleById($role_id);

			$role_name = $getRoleMaster->role_name;

			if ($getRoleMaster->resource_access == 1) {
				$chekEmpPermission = $this->chekEmpPermission($role_id);
				foreach ($chekEmpPermission as $value) {
					$resource_access[] = $value['resource_code'];
				}
			} else {
				$resource_access = '';
			}
		} else {
			$resource_access = '';
		}

		$resource_access_data = array(
			'resource_access' => $resource_access,
			'role_name' => $role_name
		);
		//return $resource_access;
		return $resource_access_data;
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
	function getWebShopCommisionByShopId($publisher_id)
	{
		$this->db->select('commision_percent');
		$this->db->where(array('id' => $publisher_id));
		$query = $this->db->get('publisher');
		$result = $query->row();
		return $result->commision_percent;
	}

	public function sendEmailStatus($templateId)
	{
		// $this->db->select('database_name');
		// $this->db->where(array('shop_id'=>$shop_id));
		// $query = $this->db->get('fbc_users_shop');
		// $result =  $query->row();

		// $shop_db =  DB_PREFIX.$result->database_name; // constant variable
		$get_custom_var =  "SELECT status FROM email_template where  `email_code` = '$templateId'";
		$query  =  $this->db->query($get_custom_var);
		$result = $query->row_array();
		if ($result > 0) {
			return $result['status'];
		}
		return 0;
	}

	public function getSalesOrderItems()
	{
		// $identifier = 'rounded_webshop_prices';
		$get_custom_var =  "SELECT * FROM `sales_order_items` WHERE `sub_start_date` IS NULL OR  `sub_end_date` IS NULL";
		$query  =  $this->db->query($get_custom_var);
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

	public function get_all_subscription()
	{
		$get_custom_var =  "SELECT * FROM `sales_order_items` WHERE /*`sub_start_date` IS NOT  NULL OR  `sub_end_date` IS NOT  NULL  AND */ `sub_end_date` > UNIX_TIMESTAMP(NOW())  order by `sub_end_date` ASC";
		$query  =  $this->db->query($get_custom_var);
		$result = $query->result_array();
		if ($result > 0) {
			return $result;
		} else {
			return false;
		}
	}

	function getShopsForBTwoBOrders($order_id)
	{
		if (empty($order_id)) {
			return false;
		}

		$order_id = (int)$order_id;
		$query = "SELECT oi.publisher_id FROM sales_order_items as oi INNER JOIN products as p ON oi.product_id = p.id LEFT JOIN products_inventory as pi ON oi.product_id = pi.product_id WHERE oi.order_id = ? group by oi.publisher_id";

		$query  =  $this->db->query($query, array($order_id));
		$result = $query->result_array();
		if (!empty($result)) {
			return $result;
		} else {
			return false;
		}
	}
	public function get_webshop_details()
	{
		$query = "SELECT wsd.* FROM `webshop_details` as wsd ";

		$query  =  $this->db->query($query);
		$result = $query->row_array();
		if ($result > 0) {
			return $result;
		} else {
			return false;
		}
	}

	public function GetProductsFORlinks()
	{
		$this->db->select('url_key, updated_at');
		$this->db->where_in('product_type', ['simple', 'configurable']);
		$this->db->where('remove_flag', 0);
		$sql = $this->db->get('products');
		// echo $this->db->last_query();
		if ($sql->num_rows() > 0) {
			$result = $sql->result_array();
			return $result;
		} else {
			return false;
		}
	}
	public function get_daily_order_count()
	{
		$startOfDay = strtotime('today'); // Get the Unix timestamp for the start of today
		$endOfDay = strtotime('tomorrow') - 1; // Get the Unix timestamp for the end of today

		$query = "SELECT COUNT(order_id) AS daily_order_count FROM `sales_order` WHERE status NOT IN(3,7) AND created_at >= $startOfDay AND created_at < $endOfDay";
		// print_r($query);die;
		// Assuming $this->db->query() is the appropriate method to execute a SQL query
		$queryResult = $this->db->query($query);

		// Assuming $queryResult->row_array() returns the result as an associative array
		$result = $queryResult->row_array();

		return $result['daily_order_count'];
	}

	public function get_daily_sale_count()
	{
		// Get the Unix timestamp for the start of today
		$startOfDay = strtotime('today');

		// Get the Unix timestamp for the end of today
		// Subtracting 1 second ensures we get the end of the current day
		$endOfDay = strtotime('tomorrow') - 1;

		// Construct the SQL query
		$query = "SELECT SUM(soi.total_price) AS daily_sale_count FROM `sales_order_items` as soi left join sales_order as so on soi.order_id = so.order_id
		 WHERE so.status NOT IN(3,7) AND soi.created_at >= $startOfDay AND soi.created_at < $endOfDay";

		// Execute the query
		$queryResult = $this->db->query($query);

		// Fetch the result as an associative array
		$result = $queryResult->row_array();

		// Return the sum of total prices for the current day
		return $result['daily_sale_count'];
	}

	public function get_daily_earnings_count()
	{
		// Get the Unix timestamp for the start of today
		$startOfDay = strtotime('today');

		// Get the Unix timestamp for the end of today
		// Subtracting 1 second ensures we get the end of the current day
		$endOfDay = strtotime('tomorrow') - 1;

		// Construct the SQL query
		$query = "SELECT SUM(whuso_income) AS daily_earnings_count FROM `b2b_orders` WHERE status NOT IN(3,7) AND created_at >= $startOfDay AND created_at < $endOfDay";
		// echo $query;die;
		// Execute the query
		$queryResult = $this->db->query($query);

		// Fetch the result as an associative array
		$result = $queryResult->row_array();

		// Return the sum of total prices for the current day
		return $result['daily_earnings_count'];
	}
	public function get_weekly_sale_count()
	{
		$startOfWeek = strtotime('monday this week');
		$endOfWeek = strtotime('sunday this week') + 86399; // Adding 86399 seconds to reach the end of Sunday
		$query = "SELECT DATE_FORMAT(FROM_UNIXTIME(soi.created_at), '%d/%m/%Y') AS sale_date, SUM(soi.total_price) AS weekly_sale_count FROM `sales_order_items` as soi LEFT JOIN sales_order as so ON soi.order_id = so.order_id WHERE so.status NOT IN(3,7)  AND soi.created_at >= $startOfWeek AND soi.created_at <= $endOfWeek GROUP BY sale_date";
		// echo $query;die;
		$queryResult = $this->db->query($query);
		$result = $queryResult->result_array();
		return $result;
	}

	public function get_monthly_sale_count()
	{
		// Get the current year and month
		$currentYear = date('Y');
		$currentMonth = date('n');

		// Initialize an array to store monthly sales data
		$monthlySalesData = array();

		// Loop through each month from January to the current month
		for ($month = 1; $month <= $currentMonth; $month++) {
			// Construct the start and end date of the specified month
			$startDate = strtotime("$currentYear-$month-01");
			$endDate = strtotime(date("Y-m-t", strtotime("$currentYear-$month")));

			// Construct the SQL query to aggregate sales for the entire month and select the month name
			$query = "SELECT MONTHNAME(FROM_UNIXTIME(soi.created_at)) AS month_name, SUM(soi.total_price) AS monthly_sale_count FROM `sales_order_items` as soi LEFT JOIN sales_order as so ON soi.order_id = so.order_id WHERE so.status NOT IN(3,7) AND soi.created_at >= $startDate AND soi.created_at <= $endDate GROUP BY month_name";

		// 	$query = "SELECT
		// 	MONTHNAME(FROM_UNIXTIME(subquery.created_at)) AS month_name,
		// 	SUM(DISTINCT subquery.total_price) AS monthly_sale_count
		// FROM (
		// 	SELECT
		// 		soi.order_id,
		// 		MAX(soi.total_price) AS total_price,
		// 		MAX(soi.created_at) AS created_at
		// 	FROM
		// 		sales_order_items AS soi
		// 	WHERE
		// 		soi.created_at >= $startDate
		// 		AND soi.created_at <= $endDate
		// 	GROUP BY
		// 		soi.order_id
		// ) AS subquery
		// LEFT JOIN
		// 	sales_order AS so ON subquery.order_id = so.order_id
		// WHERE
		// 	so.status != '3'
		// GROUP BY
		// 	MONTHNAME(FROM_UNIXTIME(subquery.created_at))";
			// echo $query;
			// die;

			// Execute the query
			$queryResult = $this->db->query($query);

			// Fetch the result as an associative array
			$result = $queryResult->row_array();

			// Store the monthly sales total in the array
			if ($result !== null) {
				// Store the monthly sales total in the array
				$monthlySalesData[$result['month_name']] = $result['monthly_sale_count'];
			} else {
				// If the result is null, set the monthly sales total to 0 for the current month
				$monthlySalesData[date('F', mktime(0, 0, 0, $month, 1, $currentYear))] = 0;
			}
		}
		// echo $query;
		// die;
		// print_r($monthlySalesData) ;
		// die;
		// Return the monthly sales data
		return $monthlySalesData;
	}

	public function get_yearly_sale_count()
	{
		$startYear = 2023;
		// Initialize an array to store yearly sales data
		$yearlySalesData = array();

		// Get the current year
		$currentYear = date('Y');

		for ($year = $startYear; $year <= $currentYear + 9; $year++) {

			$startOfYear = strtotime("$year-01-01");
			$endOfYear = strtotime("$year-12-31 23:59:59");

			$query = "SELECT YEAR(FROM_UNIXTIME(soi.created_at)) AS year, SUM(soi.total_price) AS yearly_sale_count
                  FROM `sales_order_items` as soi
                  LEFT JOIN sales_order as so ON soi.order_id = so.order_id
                  WHERE so.status NOT IN(3,7)
                  AND soi.created_at >= $startOfYear
                  AND soi.created_at <= $endOfYear
                  GROUP BY year";

			$queryResult = $this->db->query($query);

			$result = $queryResult->row_array();

			if ($result !== null) {

				$yearlySalesData[$result['year']] = $result['yearly_sale_count'];
			} else {
				$yearlySalesData[$year] = 0;
			}
		}
		return $yearlySalesData;
	}

	public function getPickupDataByOrderId($order_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_orders_pickup_details');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row(); // returns first record
	}


	public function getDeliveryStatusLabel($status)
	{
		switch ($status) {
			case 1: return 'Shipped (Attempt 1)';
			case 2: return 'Attempt 1 Failed';
			case 3: return 'Shipped (Attempt 2)';
			case 4: return 'Attempt 2 Failed';
			case 5: return 'Shipped (Attempt 3)';
			case 6: return 'Attempt 3 Failed';
			case 7: return 'Pickup from Warehouse';
			case 8: return 'Delivered';
			case 9: return 'Collected';
			default: return 'Pending';
		}
	}


	public function getDeliveryDataByOrderId($order_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_orders_delivery_details');
		$this->db->where('order_id', $order_id);
		$this->db->order_by('id', 'DESC'); // latest attempt
		$query = $this->db->get();
		return $query->row();
	}

	public function getOrderDataByb2bOrderId($order_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_orders');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}

	public function getAllDeliveryPersons()
	{
		$this->db->select('id, first_name, last_name, mobile_no, email, profile_photo, driver_licence_no, licence_plate_no');
		$this->db->from('driver_details');
		$this->db->order_by('first_name', 'ASC');
		$query = $this->db->get();

		return $query->result();
	}


	public function getDriverName($driver_id)
	{
		if (!$driver_id) return '';

		$driver = $this->db->select('first_name, last_name, mobile_no')
						->from('driver_details')
						->where('id', $driver_id)
						->get()
						->row();

		if ($driver) {
			return htmlspecialchars($driver->first_name . ' ' . $driver->last_name . ' (' . $driver->mobile_no . ')');
		}
		
		return '';
	}

	// public function post_to_facebook($merchant)
    // {
	// 	if ($merchant->fb_posted == 1) {
	// 		return; // ✅ Prevent repost
	// 	}
    //     $pageId = "1397577911713632";
    //     $token  = "ecbada4e0eb0a692dcae84fedd008ac3";

    //     $message = "🎉 New Store Joined Us!\n\n".
    //            "🛍 {$merchant->publication_name}\n".
    //            "📍 {$merchant->city}, {$merchant->state}\n\n".
    //            "Support this business! 🚀";

	// 	$url = "https://graph.facebook.com/{$pageId}/feed";

	// 	$data = [
	// 		'message' => $message,
	// 		'access_token' => $token
	// 	];
		

	// 	$ch = curl_init($url);
	// 	curl_setopt($ch, CURLOPT_POST, 1);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// 	$response = curl_exec($ch);
	// 	if($response === false) {
	// 		echo "cURL Error: " . curl_error($ch);
	// 		curl_close($ch);
	// 		exit;
	// 	}
	// 	curl_close($ch);

	// 	$res = json_decode($response, true);
	// 	print_r($res);die;
	// 	if (!empty($res['id'])) {
	// 		$this->db->where('id', $merchant->id)
	// 				->update('publisher', ['fb_posted' => 1]);
	// 	}

	// 	log_message("error", "FB POST RESPONSE: ".$response);
    // }

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

	public function post_to_instagram($merchant)
	{
		if ($merchant->ig_posted == 1) {
			return;
		}

		$instagramBusinessId = "YOUR_IG_BUSINESS_ID";
		$accessToken         = "YOUR_IG_TOKEN";

		$caption = "🎉 New Merchant Joined!\n".
				"{$merchant->publication_name}\n".
				"{$merchant->city}, {$merchant->state}\n🚀";

		$imageUrl = "YOUR_IMAGE_URL"; // Must be a public URL

		// Step 1: Create IG media object
		$createUrl = "https://graph.facebook.com/v19.0/{$instagramBusinessId}/media";

		$data = [
			'image_url' => $imageUrl,
			'caption' => $caption,
			'access_token' => $accessToken
		];

		$resp = file_get_contents($createUrl . "?" . http_build_query($data));
		$media = json_decode($resp, true);

		if (empty($media['id'])) {
			log_message("error", "IG ERROR: ".$resp);
			return;
		}

		// Step 2: Publish
		$publishUrl = "https://graph.facebook.com/v19.0/{$instagramBusinessId}/media_publish";

		$resp2 = file_get_contents($publishUrl . "?".http_build_query([
			'creation_id' => $media['id'],
			'access_token' => $accessToken
		]));

		$publish = json_decode($resp2, true);

		if (!empty($publish['id'])) {
			$this->db->where('id', $merchant->id)
					->update('publisher', ['ig_posted' => 1]);
		}

		log_message("error", "IG POST RESPONSE: ".$resp2);
	}

	public function post_to_linkedin($merchant)
	{
		if ($merchant->ln_posted == 1) {
			return;
		}

		$accessToken = "YOUR_LINKEDIN_ACCESS_TOKEN";
		$organizationId = "YOUR_COMPANY_ID";

		$message = "🎉 New Merchant Joined Our Platform!\n".
				"{$merchant->publication_name}\n".
				"{$merchant->city}, {$merchant->state}\n🚀";

		$postData = [
			"author" => "urn:li:organization:$organizationId",
			"lifecycleState" => "PUBLISHED",
			"specificContent" => [
				"com.linkedin.ugc.ShareContent" => [
					"shareCommentary" => ["text" => $message],
					"shareMediaCategory" => "NONE"
				]
			],
			"visibility" => [
				"com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
			]
		];

		$ch = curl_init("https://api.linkedin.com/v2/ugcPosts");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer $accessToken",
			"Content-Type: application/json"
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($response, true);

		if (!empty($result['id'])) {
			$this->db->where('id', $merchant->id)
					->update('publisher', ['ln_posted' => 1]);
		}

		log_message("error", "LinkedIn POST RESPONSE: ".$response);
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
			$completed_and_post_statuses = [2, 8, 9, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 28, 31, 32, 33];
			$completed_statuses = [2, 8, 9, 23, 24, 28];

			$all_completed = true;
			$all_completed_or_post = true;
			$has_pending_delivery = false;

			foreach ($sub_orders as $sub) {
				if ($sub['status'] == 3) {
					continue;
				}
				if (!in_array((int)$sub['status'], $completed_statuses)) {
					$all_completed = false;
				}
				if (!in_array((int)$sub['status'], $completed_and_post_statuses)) {
					$all_completed_or_post = false;
					$has_pending_delivery = true;
				}
			}

			// Get current sales_order status
			$cur_order = $this->db->select('status')->from('sales_order')->where('order_id', $webshop_order_id)->get()->row();
			$cur_status = $cur_order ? (int)$cur_order->status : 0;

			if ($all_completed) {
				// All sub-orders are delivered/collected -> set to 2 (Complete) if not already in a specific post-completion status
				if (!in_array($cur_status, [14, 15, 16, 17, 18, 19, 20, 21, 22, 31, 32, 33])) {
					$this->db->where('order_id', $webshop_order_id);
					$this->db->set('status', 2);
					$this->db->set('updated_at', time());
					$this->db->update('sales_order');
					return 2;
				}
				return $cur_status;
			} elseif ($all_completed_or_post) {
				// All sub-orders have finished delivery and are completed or in post-completion statuses
				// Keep order complete (status 2 or its current post-completion status) - DO NOT demote to 1!
				if ($cur_status == 1 || $cur_status == 0) {
					$this->db->where('order_id', $webshop_order_id);
					$this->db->set('status', 2);
					$this->db->set('updated_at', time());
					$this->db->update('sales_order');
					return 2;
				}
				return $cur_status;
			} else {
				// There are sub-orders actually pending delivery/processing
				// Only demote to 1 if it is NOT in post-completion and actually has pending delivery
				if ($has_pending_delivery && !in_array($cur_status, [14, 15, 16, 17, 18, 19, 20, 21, 22, 31, 32, 33])) {
					$this->db->where('order_id', $webshop_order_id);
					$this->db->where('status', 2);
					$this->db->set('status', 1);
					$this->db->set('updated_at', time());
					$this->db->update('sales_order');
					return 1;
				}
				return $cur_status;
			}
		}

		return false;
	}
	function getShopCurrency($shopid = null)
	{
		return 'MUR';
	}

}
