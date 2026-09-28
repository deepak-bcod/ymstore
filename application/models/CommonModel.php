<?php

use PHPMailer\PHPMailer\PHPMailer;

class CommonModel extends CI_Model
{

    public function GetProducts()
    {

        $sql = $this->db->get('products');

        if ($sql->num_rows() > 0) {

            $result = $sql->result_array();

            return $result;
        } else {

            return false;
        }
    }

    public function getCitiesByState($state_id)
    {
        return $this->db
            ->select('*')
            ->from('city_master')
            ->where('state_id', $state_id)
            ->get()
            ->result();
    }


 public function getFaqData($type = 'Shopper')
{
    $this->db->select('*');
    $this->db->from('faqs');
    $this->db->where('status', 1);

    if ($type === 'Shopper') {
       
        $this->db->group_start();
            $this->db->where('faq_type', 'Shopper');
            $this->db->or_where('faq_type', NULL);
            $this->db->or_where('faq_type', '');
        $this->db->group_end();
    } else {
        
        $this->db->where('faq_type', $type);
    }

    $this->db->order_by('id', 'DESC');
    return $this->db->get()->result();
}

    // public function get_orders_by_date($date)
    // {
    //     $start = strtotime($date . ' 00:00:00');
    //     $end   = strtotime($date . ' 23:59:59');

    //     $this->db->select('b2b_orders.*, sales_order.customer_email as new_customer_email,sales_order.order_barcode');
    //     $this->db->from('b2b_orders');
    //     $this->db->join(
    //         'sales_order', 
    //         'sales_order.order_id = b2b_orders.webshop_order_id', 
    //         'left'
    //     );
    //     // $this->db->where('b2b_orders.created_at >=', $start);
    //     // $this->db->where('b2b_orders.created_at <=', $end);
    //     $this->db->where('sales_order.status', '2');
    //     $this->db->where('b2b_orders.reveiw_sent', "0");
    //     // echo $this->db->get_compiled_select(); 
    //     // die();

    //     $this->db->where('b2b_orders.review_submitted', "0");

    //     $query = $this->db->get();
    //     // $query = $this->db->get();
    //     return $query->result();


    //     // Print the SQL query without executing
    //     // echo $this->db->get_compiled_select(); 
    //     // die();


    //     // // Debug: print the query
    //     // // echo $this->db->last_query();
    //     // // die();

    //     // return $query->row(); // comment out while debugging
    // }

    public function get_orders_by_date($date)
    {
        // Start and end of the requested date
        $start = strtotime($date . ' 00:00:00');
        $end   = strtotime($date . ' 23:59:59');

        $this->db->select([
            'b2b_orders.*',
            'sales_order.customer_email AS new_customer_email',
            'sales_order.order_barcode'
        ]);

        $this->db->from('b2b_orders');

        $this->db->join(
            'sales_order',
            'sales_order.order_id = b2b_orders.webshop_order_id',
            'left'
        );

        // Only orders created on the requested date
        $this->db->where('b2b_orders.created_at >=', $start);
        $this->db->where('b2b_orders.created_at <=', $end);

        // Only completed orders
        $this->db->where('sales_order.status', 2);

        // Review email has not been sent
        $this->db->where('b2b_orders.reveiw_sent', 0);

        // Review has not been submitted
        //$this->db->where('b2b_orders.review_submitted', 0);

        $query = $this->db->get();

        // print_r($this->db->last_query());exit;
        // log_message('error', 'Review Orders SQL: ' . $this->db->last_query());

        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return [];
    }


    public function get_order_with_items($order_id)
    {
        $this->db->select('oi.*, p.name as product_name');
        $this->db->from('b2b_order_items oi');
        $this->db->join('products p', 'oi.product_id = p.id');
        $this->db->where('oi.order_id', $order_id);
        return $this->db->get()->result();
        //  $query = $this->db->get();

        // Debug: print the query
        // echo $this->db->last_query();
        // die();
    }

    public function get_customer_order_id($order_id)
    {
        $this->db->select('so.order_barcode');
        $this->db->from('b2b_orders bo');
        $this->db->join('sales_order so', 'bo.webshop_order_id = so.order_id');
        $this->db->where('bo.order_id', $order_id);

        $query = $this->db->get();
        // echo $this->db->last_query();
        // die();
        $row = $query->row();

        return $row ? $row->order_barcode : '';
    }


    public function getEmailTemplateByIdentifier($identifier)
    {
        $result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();
        return $result;
    }

    public function get_b2b_orders($order_id)
    {
        return $this->db
            ->select('*')
            ->from('b2b_orders')
            ->where('order_id', $order_id)
            ->get()
            ->row();
    }
    public function get_sale_orders($order_id)
    {
        return $this->db
            ->select('*')
            ->from('sales_order')
            ->where('order_id', $order_id)
            ->get()
            ->row();
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

    public function getOrderDataByb2bOrderId($order_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_orders');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}
    public function get_order_by_id($order_id)
    {
        return $this->db->select('order_id, increment_id, created_at, grand_total, status')
            ->from('sales_order')
            ->where('order_id', $order_id)
            ->get()
            ->row();
    }

    public function get_product_by_order($order_id, $product_id)
    {
        return $this->db->select('product_id, product_name')
            ->from('sales_order_items')
            ->where('order_id', $order_id)
            ->where('product_id', $product_id)
            ->get()
            ->row();
    }

    public function get_customer_data($email_id)
    {
        return $this->db
            ->select('*')
            ->from('customers')
            ->where('email_id', $email_id)
            ->get()
            ->row();
    }
    public function driver_edit($data)
    {
        $id = $data['id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $mobile_no = $data['mobile_no'];

        $updateTrip = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'mobile_no' => $mobile_no,
            'updated_at' => strtotime(date('Y-m-d H:i:s')),
        );
        $this->db->where('id', $id);
        return $this->db->update('driver_details', $updateTrip);
    }
    public function get_driver_details($driver_id)
    {
        return $this->db
            ->select('id,first_name,last_name,mobile_no,email,profile_photo,driver_licence_no,licence_plate_no')
            ->from('driver_details')
            ->where('id', $driver_id)
            ->get()
            ->row();
    }
    public function get_pickup_listing($driver_id, $date = null)
    {
        $this->db
            ->select('
                bopd.order_id,
                bo.order_barcode,
                m.id as merchant_id,
                m.publication_name,
                m.company_address,
                m.phone_no,
                m.latitude,
                m.longitude
            ')
            ->from('b2b_orders_pickup_details as bopd')
            ->join('b2b_orders as bo', 'bopd.order_id = bo.order_id', 'left')
            ->join('publisher as m', 'bo.publisher_id = m.id', 'left')
            ->where('bopd.pickup_status', 2)
            ->where('bopd.driver_id', $driver_id);

        // Filter by date if provided
        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(bopd.created_at)', $converted_date);
        }

        // Order by order_id descending
        $this->db->order_by('bopd.order_id', 'DESC');

        $query = $this->db->get();

        // For debugging, uncomment the next line:
        // echo $this->db->last_query(); die();

        return $query->result();
    }

    /*public function get_delivery_listing($driver_id, $date = null)
    {
        // Step 1: Get delivery records
        $this->db->select('order_id, is_parent_level, webshop_order_id');
        $this->db->from('b2b_orders_delivery_details as b2bodd');
        $this->db->where('driver_id', $driver_id);
        $this->db->where('delivery_type', '2');
        $this->db->where_in('delivery_status', [1, 3]);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(delivery_date)', $converted_date);
        }

        $delivery_query = $this->db->get();
        $deliveries = $delivery_query->result_array();

        if (empty($deliveries)) {
            return [];
        }

        $final = []; // Will hold the flattened results

        // Step 2: Get product/details for each delivery
        foreach ($deliveries as $delivery) {
            $is_parent_level = $delivery['is_parent_level'];

            if ($is_parent_level == 1) {
                $this->db->select('
                    DISTINCT b2bodd.webshop_order_id AS order_id,
                    b.order_barcode,
                    b2bodd.is_parent_level,
                    soa.first_name,
                    soa.last_name,
                    soa.mobile_no,
                    soa.address_line1,
                    soa.address_line2,
                    soa.city,
                    soa.state,
                    soa.country,
                    soa.pincode
                ', false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                    ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                    ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);
            } else {
                $this->db->select('
                    DISTINCT b2bodd.order_id AS order_id,
                    b.order_barcode,
                    b2bodd.is_parent_level,
                    soa.first_name,
                    soa.last_name,
                    soa.mobile_no,
                    soa.address_line1,
                    soa.address_line2,
                    soa.city,
                    soa.state,
                    soa.country,
                    soa.pincode
                ', false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                    ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                    ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.order_id', $delivery['order_id']);
            }

            $details = $this->db->get()->row_array(); // Get single row
            if (!empty($details)) {
                $final[] = $details; // Add directly to final array
            }
        }

        return $final;
    }*/


        // public function get_delivery_listing($driver_id, $date = null)
        // {
        //     // Step 1: Get delivery records
        //     $this->db->select('b2bodd.order_id, b2bodd.is_parent_level, b2bodd.webshop_order_id');
        //     $this->db->from('b2b_orders_delivery_details as b2bodd');
        //     $this->db->join('b2b_orders as bo', 'b2bodd.order_id = bo.order_id', 'left');

        //     $this->db->where('b2bodd.driver_id', $driver_id);
        //     $this->db->where('b2bodd.delivery_type', '2');
        //     $this->db->where_in('b2bodd.delivery_status', [1, 3]);

        //     // IMPORTANT: Exclude already delivered orders
        //     $this->db->where('bo.status !=', 8);

        //     if (!empty($date)) {
        //         $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        //         $this->db->where('DATE(b2bodd.delivery_date)', $converted_date);
        //     }

        //     $delivery_query = $this->db->get();
        //     $deliveries = $delivery_query->result_array();

        //     if (empty($deliveries)) {
        //         return [];
        //     }

        //     $final = [];

        //     // Step 2: Get product/details for each delivery
        //     foreach ($deliveries as $delivery) {

        //         $is_parent_level = $delivery['is_parent_level'];

        //         if ($is_parent_level == 1) {

        //             $this->db->select('
        //                 DISTINCT b2bodd.webshop_order_id AS order_id,
        //                 b.order_barcode,
        //                 b2bodd.is_parent_level,
        //                 soa.first_name,
        //                 soa.last_name,
        //                 soa.mobile_no,
        //                 soa.address_line1,
        //                 soa.address_line2,
        //                 soa.city,
        //                 soa.state,
        //                 soa.country,
        //                 soa.pincode
        //             ', false)
        //             ->from('b2b_orders_delivery_details as b2bodd')
        //             ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
        //             ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
        //             ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
        //             ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);

        //         } else {

        //             $this->db->select('
        //                 DISTINCT b2bodd.order_id AS order_id,
        //                 b.order_barcode,
        //                 b2bodd.is_parent_level,
        //                 soa.first_name,
        //                 soa.last_name,
        //                 soa.mobile_no,
        //                 soa.address_line1,
        //                 soa.address_line2,
        //                 soa.city,
        //                 soa.state,
        //                 soa.country,
        //                 soa.pincode
        //             ', false)
        //             ->from('b2b_orders_delivery_details as b2bodd')
        //             ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
        //             ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
        //             ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
        //             ->where('b2bodd.order_id', $delivery['order_id']);
        //         }

        //         $details = $this->db->get()->row_array();

        //         if (!empty($details)) {
        //             $final[] = $details;
        //         }
        //     }

        //     return $final;
        // }



    public function get_delivery_listing($driver_id, $date = null)
    {
        // Step 1: Get delivery records
        $this->db->select('b2bodd.order_id, b2bodd.is_parent_level, b2bodd.webshop_order_id');
        $this->db->from('b2b_orders_delivery_details as b2bodd');
        $this->db->join('b2b_orders as bo', 'b2bodd.order_id = bo.order_id', 'left');

        $this->db->where('b2bodd.driver_id', $driver_id);
        $this->db->where('b2bodd.delivery_type', '2');
        $this->db->where_in('b2bodd.delivery_status', [1, 3]);

        // Allow rows where bo is NULL (parent-level) OR bo.status != 8
        $this->db->group_start();
            $this->db->where('bo.status IS NULL', null, false);
            $this->db->or_where('bo.status !=', 8);
        $this->db->group_end();

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(b2bodd.delivery_date)', $converted_date);
        }

        $delivery_query = $this->db->get();
        $deliveries = $delivery_query->result_array();

        if (empty($deliveries)) {
            return [];
        }

        $final = [];

        // Step 2: Get product/details for each delivery
        foreach ($deliveries as $delivery) {

            // normalize is_parent_level (could be null or '1'/'0')
            $is_parent_level = (int) ($delivery['is_parent_level'] ?? 0);

            if ($is_parent_level === 1 && !empty($delivery['webshop_order_id'])) {
                // parent-level: use webshop_order_id to fetch order/address info
                $qb = $this->db->select("
                        DISTINCT b2bodd.webshop_order_id AS order_id,
                        b.order_barcode,
                        b2bodd.is_parent_level,
                        soa.first_name,
                        soa.last_name,
                        soa.mobile_no,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode
                    ", false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                    ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                    ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);
            } else {
                // child-level or fallback: use order_id
                $qb = $this->db->select("
                        DISTINCT b2bodd.order_id AS order_id,
                        b.order_barcode,
                        b2bodd.is_parent_level,
                        soa.first_name,
                        soa.last_name,
                        soa.mobile_no,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode
                    ", false)
                    ->from('b2b_orders_delivery_details as b2bodd')
                    ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                    ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                    ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
                    ->where('b2bodd.order_id', $delivery['order_id']);
            }

            // execute local query
            $details = $qb->get()->row_array();

            if (!empty($details)) {
                
                if ($is_parent_level === 1 && !empty($delivery['webshop_order_id'])) {
                    $associated_orders = $this->db
                        ->select('order_barcode, increment_id')
                        ->where('webshop_order_id', $delivery['webshop_order_id'])
                        ->order_by('order_id', 'ASC')
                        ->get('b2b_orders')
                        ->result_array();

                    $sub_barcodes = [];
                    foreach ($associated_orders as $ao) {
                        $val = !empty($ao['order_barcode']) ? trim($ao['order_barcode']) : (!empty($ao['increment_id']) ? trim($ao['increment_id']) : '');
                        if ($val !== '' && !in_array($val, $sub_barcodes)) {
                            $sub_barcodes[] = $val;
                        }
                    }

                    if (!empty($sub_barcodes)) {
                        $details['order_barcode'] = implode(', ', $sub_barcodes);
                        $details['order_numbers'] = $details['order_barcode'];
                        $details['associated_order_numbers'] = $details['order_barcode'];
                    }
                }
                $final[] = $details;
            }
        }

        // Optional: unique results by order_id (in case duplicates)
        $unique = [];
        foreach ($final as $r) {
            $unique[$r['order_id']] = $r;
        }

        return array_values($unique);
    }



    public function get_pickup_order_details($driver_id, $order_id)
    {
        $this->db->select('
            bopd.order_id AS order_id,
            bopd.image,
            bopd.thumbnail,
            bopd.remarks,
            bopd.product_details,
            b.order_barcode,
            b.grand_total,
            bo.product_id,
            bo.product_name,
            bo.qty_ordered,
            bo.price,
            bo.total_price,
            bo.is_fragile_flag,
            IFNULL(pp.base_image, cp.base_image) AS base_image
        ')
        ->from('b2b_orders_pickup_details as bopd')
        ->join('b2b_orders as b', 'bopd.order_id = b.order_id', 'left')
        ->join('b2b_order_items as bo', 'bopd.order_id = bo.order_id', 'left')
        ->join('products as cp', 'cp.id = bo.product_id', 'left')
        ->join('products as pp', 'pp.id = cp.parent_id', 'left')
        ->where('bopd.driver_id', $driver_id)
        ->where('bopd.order_id', $order_id)
        ->group_by('bo.product_id');

        $query = $this->db->get();
        $result = $query->result_array();

        //echo "<pre>";print_r($result);die;

        if (empty($result)) {
            return [];
        }

        // Order-level data
        $order = [
            'order_id' => $result[0]['order_id'],
            'order_barcode' => $result[0]['order_barcode'],
            'grand_total' => $result[0]['grand_total'],
            'image' => !empty($result[0]['image'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/' . $result[0]['image']
                : null,
            'thumbnail' => !empty($result[0]['thumbnail'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $result[0]['thumbnail']
                : null,
            'remark' => $result[0]['remarks'],
            'products' => []
        ];
        
        $productDetailsJson = json_decode($result[0]['product_details'], true) ?? [];
        // Build products array with nested product_details
        // Build products array
        foreach ($result as $row) {
            $productId = $row['product_id'];
            $details = $productDetailsJson[$productId] ?? [];

            // Prepare image URLs if present in product_details
            $pickupImage = !empty($details['pickup_image'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/' . $details['pickup_image']
                : null;

            $pickupThumb = !empty($details['pickup_thumb'])
                ? IMAGE_URL . 'admin/admin/public/images/pickup/thumbnail/' . $details['pickup_thumb']
                : null;

            
            $order['products'][] = [
                'product_id' => $productId,
                'product_name' => $row['product_name'],
                'qty_ordered' => $row['qty_ordered'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
                'is_fragile_flag' => $row['is_fragile_flag'],
                'base_image' => !empty($row['base_image'])
                    ? IMAGE_URL . 'uploads/products/thumb/' . $row['base_image']
                    : IMAGE_URL . 'uploads/products/thumb/no_image.png',
                // Only add pickup images if they exist in product_details
                'pickup_image' => $pickupImage,
                'pickup_thumb' => $pickupThumb
            ];
        }


        return (object)['order' => (object)$order];
    }


    public function get_delivery_order_details($driver_id, $order_id, $is_parent_level = null)
    {
        if ($is_parent_level == 1) {
            $orderField = 'b2bodd.webshop_order_id';
            $orderAlias = 'b2bodd.webshop_order_id AS order_id';
            $grandTotal = 'so.grand_total';

            $this->db->from('b2b_orders_delivery_details as b2bodd')
                ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                ->join('sales_order_items as oi', 'b2bodd.webshop_order_id = oi.order_id', 'left');
        } else {
            $orderField = 'b2bodd.order_id';
            $orderAlias = 'b2bodd.order_id AS order_id';
            $grandTotal = 'b.grand_total';

            $this->db->from('b2b_orders_delivery_details as b2bodd')
                ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                ->join('b2b_order_items as oi', 'b2bodd.order_id = oi.order_id', 'left');
        }

        // Product joins (common)
        $this->db->join('products as cp', 'cp.id = oi.product_id', 'left')
                ->join('products as pp', 'pp.id = cp.parent_id', 'left');

        $this->db->select("
            DISTINCT
            {$orderAlias},
            b2bodd.is_parent_level,
            b2bodd.delivery_attempt_no,
            b2bodd.delivery_status,
            b2bodd.image,
            b2bodd.thumbnail,
            b2bodd.remarks,
            b.order_barcode,
            {$grandTotal} AS grand_total,
            oi.product_id,
            oi.product_name,
            oi.qty_ordered,
            oi.price,
            oi.total_price,
            oi.is_fragile_flag,
            IFNULL(pp.base_image, cp.base_image) AS base_image
        ", false);

        $this->db->where('b2bodd.delivery_type', '2')
                ->where('b2bodd.driver_id', $driver_id)
                ->where($orderField, $order_id)
                ->group_by('oi.product_id');

        $query = $this->db->get();
        $result = $query->result_array();

        // echo $this->db->last_query(); die();

        if (empty($result)) {
            return [];
        }

        // Extract order-level info
        $order = [
            'order_id' => $result[0]['order_id'],
            'is_parent_level' => $result[0]['is_parent_level'],
            'delivery_attempt_no' => $result[0]['delivery_attempt_no'],
            'delivery_status' => $result[0]['delivery_status'],
            'grand_total' => $result[0]['grand_total'],
            'order_barcode' => $result[0]['order_barcode'],
            'image' => !empty($result[0]['image'])
                ? IMAGE_URL . 'admin/admin/public/images/delivery/' . $result[0]['image']
                : null,
            'thumbnail' => !empty($result[0]['thumbnail'])
                ? IMAGE_URL . 'admin/admin/public/images/delivery/thumbnail/' . $result[0]['thumbnail']
                : null,
            'remark' => $result[0]['remarks'],
            'products' => []
        ];

        if ($is_parent_level == 1) {
            $associated_orders = $this->db
                ->select('order_barcode, increment_id')
                ->where('webshop_order_id', $order_id)
                ->order_by('order_id', 'ASC')
                ->get('b2b_orders')
                ->result_array();

            $sub_barcodes = [];
            foreach ($associated_orders as $ao) {
                $val = !empty($ao['order_barcode']) ? trim($ao['order_barcode']) : (!empty($ao['increment_id']) ? trim($ao['increment_id']) : '');
                if ($val !== '' && !in_array($val, $sub_barcodes)) {
                    $sub_barcodes[] = $val;
                }
            }

            if (!empty($sub_barcodes)) {
                $order['order_barcode'] = implode(', ', $sub_barcodes);
                $order['order_numbers'] = $order['order_barcode'];
                $order['associated_order_numbers'] = $order['order_barcode'];
            }
        }

        // Use product_id as key to avoid duplicates
        $added = [];

        foreach ($result as $row) {
            if (isset($added[$row['product_id']])) continue; // skip duplicate

            $order['products'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'qty_ordered' => $row['qty_ordered'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
                'is_fragile_flag' => $row['is_fragile_flag'],
                'base_image' => !empty($row['base_image'])
                    ? IMAGE_URL . '/uploads/products/thumb/' . $row['base_image']
                    : IMAGE_URL . '/uploads/products/thumb/no_image.png'
            ];
            $added[$row['product_id']] = true;
        }

        return (object)['order' => (object)$order];
    }

    public function get_today_route($driver_id, $date)
    {
        // ---------------------------------
        // 1️⃣ GET PICKUP DATA
        // ---------------------------------
        $this->db
            ->select('
                bopd.order_id,
                bo.order_barcode,
                m.id as merchant_id,
                m.publication_name,
                m.company_address,
                m.location,
                m.city,
                m.state,
                m.zipcode,
                m.phone_no,
                m.latitude,
                m.longitude
            ')
            ->from('b2b_orders_pickup_details as bopd')
            ->join('b2b_orders as bo', 'bopd.order_id = bo.order_id', 'left')
            ->join('publisher as m', 'bo.publisher_id = m.id', 'left')
            ->where('bopd.pickup_status', 2)
            ->where('bopd.driver_id', $driver_id);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(bopd.created_at)', $converted_date);
        }

        $this->db->order_by('bopd.order_id', 'DESC');
        $pickup_query = $this->db->get();
        $pickup_data = $pickup_query->result_array();

        //echo "<pre>";print_r($pickup_data);die;

        $filtered_pickup = []; // ✅ new filtered array

        foreach ($pickup_data as &$pickup) {
            $pickup['full_address'] = trim(
                $pickup['company_address'] . ', ' .
                    (!empty($pickup['location']) ? $pickup['location'] . ', ' : '') .
                    $pickup['city'] . ', ' .
                    $pickup['state'] . ' - ' .
                    $pickup['zipcode']
            );

            $pickup['latitude']  = $pickup['latitude'] !== null ? (float)$pickup['latitude'] : null;
            $pickup['longitude'] = $pickup['longitude'] !== null ? (float)$pickup['longitude'] : null;

            // ✅ Only include if both lat & long exist
            if (!empty($pickup['latitude']) && !empty($pickup['longitude'])) {
                $filtered_pickup[] = $pickup;
            }
        }

        $pickup_data = $filtered_pickup; // ✅ replace with filtered array

        // ---------------------------------
        // 2️⃣ GET DELIVERY DATA
        // ---------------------------------
        $this->db->select('order_id, is_parent_level, webshop_order_id')
            ->from('b2b_orders_delivery_details as b2bodd')
            ->where('driver_id', $driver_id)
            ->where('delivery_type', '2')
            ->where_in('delivery_status', [1, 3]);

        if (!empty($date)) {
            $converted_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            $this->db->where('DATE(delivery_date)', $converted_date);
        }

        $delivery_query = $this->db->get();
        $deliveries = $delivery_query->result_array();
        // print_r($deliveries);die;
        $delivery_data = [];

        if (!empty($deliveries)) {
            foreach ($deliveries as $delivery) {
                $is_parent_level = $delivery['is_parent_level'];

                if ($is_parent_level == 1) {
                    $this->db->select('
                        DISTINCT b2bodd.webshop_order_id AS order_id,
                        b.order_barcode,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode,
                        soa.latitude,
                        soa.longitude
                    ', false)
                        ->from('b2b_orders_delivery_details as b2bodd')
                        ->join('sales_order as so', 'b2bodd.webshop_order_id = so.order_id', 'left')
                        ->join('b2b_orders as b', 'so.order_id = b.webshop_order_id', 'left')
                        ->join('sales_order_address as soa', 'b2bodd.webshop_order_id = soa.order_id', 'left')
                        // ->join('customers_address as ca', 'soa.customer_address_id = ca.id', 'left')
                        ->where('b2bodd.webshop_order_id', $delivery['webshop_order_id']);
                } else {
                    $this->db->select('
                        DISTINCT b2bodd.order_id AS order_id,
                        b.order_barcode,
                        soa.address_line1,
                        soa.address_line2,
                        soa.city,
                        soa.state,
                        soa.country,
                        soa.pincode,
                        soa.latitude,
                        soa.longitude
                    ', false)
                        ->from('b2b_orders_delivery_details as b2bodd')
                        ->join('b2b_orders as b', 'b2bodd.order_id = b.order_id', 'left')
                        ->join('b2b_order_items as bo', 'b2bodd.order_id = bo.order_id', 'left')
                        ->join('sales_order_address as soa', 'b.webshop_order_id = soa.order_id', 'left')
                        // ->join('customers_address as ca', 'soa.customer_address_id = ca.id', 'left')
                        ->where('b2bodd.order_id', $delivery['order_id']);
                }
                    // echo $this->db->get_compiled_select();
                    // exit;
                // echo "Checking record: ID=" . $delivery['id'] . 
                //     " order_id=" . $delivery['order_id'] . 
                //     " webshop_id=" . $delivery['webshop_order_id'] . 
                //     " is_parent=" . $delivery['is_parent_level'] . "<br>";
                // echo $this->db->get_compiled_select() . "<br><br>";
                $details = $this->db->get()->row_array();

                if (!empty($details)) {
                    if ($is_parent_level == 1 && !empty($delivery['webshop_order_id'])) {
                        $associated_orders = $this->db
                            ->select('order_barcode, increment_id')
                            ->where('webshop_order_id', $delivery['webshop_order_id'])
                            ->order_by('order_id', 'ASC')
                            ->get('b2b_orders')
                            ->result_array();

                        $sub_barcodes = [];
                        foreach ($associated_orders as $ao) {
                            $val = !empty($ao['order_barcode']) ? trim($ao['order_barcode']) : (!empty($ao['increment_id']) ? trim($ao['increment_id']) : '');
                            if ($val !== '' && !in_array($val, $sub_barcodes)) {
                                $sub_barcodes[] = $val;
                            }
                        }

                        if (!empty($sub_barcodes)) {
                            $details['order_barcode'] = implode(', ', $sub_barcodes);
                            $details['order_numbers'] = $details['order_barcode'];
                            $details['associated_order_numbers'] = $details['order_barcode'];
                        }
                    }

                    $details['full_address'] = trim(
                        $details['address_line1'] . ' ' .
                            $details['address_line2'] . ', ' .
                            $details['city'] . ', ' .
                            $details['state'] . ', ' .
                            $details['country'] . ' - ' .
                            $details['pincode']
                    );

                    $details['latitude']  = $details['latitude'] !== null ? round((float)$details['latitude'], 7) : null;
                    $details['longitude'] = $details['longitude'] !== null ? round((float)$details['longitude'], 7) : null;

                    // ✅ Only include if both lat & long exist
                    if (!empty($details['latitude']) && !empty($details['longitude'])) {
                        $delivery_data[] = $details;
                    }
                }
            }
        }

        // ---------------------------------
        // 3️⃣ RETURN BOTH ARRAYS
        // ---------------------------------
        return [
            'pickup'   => $pickup_data,
            'delivery' => $delivery_data
        ];
    }





   public function get_customer_orders($customer_id, $limit = 50, $offset = 0)
    {
        $this->db
            ->select('
                order.order_id,
                order.increment_id,
                order.created_at AS order_created_at,
                order.grand_total,
                order.status AS order_status,
            ')
            ->from('sales_order as order')
            ->join('invoicing as inv', 'order.invoice_id = inv.id', 'left')
            ->where('order.customer_id', $customer_id)
            ->where('order.status !=', 7)
            ->order_by('order.created_at', 'DESC')
            ->limit($limit, $offset);

        // Uncomment only for debugging
        // echo $this->db->last_query(); die();

        return $this->db->get()->result();
    }


    public function get_order_products($order_id)
    {
        $this->db->select('item_id, product_id, product_name as name, qty_ordered as qty, publisher_id');
        $this->db->from('sales_order_items');
        $this->db->where('order_id', $order_id);

        $query = $this->db->get();
        // echo $this->db->last_Query();die();
        return $query->result();
    }

    public function get_order_merchants($order_id)
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = intval($order_id);
        if ($order_id <= 0) {
            return [];
        }

        $all_merchants = [];

        // 1. Direct from sales_order if publisher_id exists on order
        $so = $this->db->select('p.id, p.publication_name')
            ->from('sales_order as so')
            ->join('publisher as p', 'p.id = so.publisher_id', 'inner')
            ->where('so.order_id', $order_id)
            ->where('p.status', 1)
            ->get();
        if ($so && $row = $so->row()) {
            $all_merchants[$row->id] = (object)[
                'id' => (string)$row->id,
                'publication_name' => trim($row->publication_name)
            ];
        }

        // 2. From sales_order_items (direct publisher_id)
        $query1 = $this->db->select('p.id, p.publication_name')
            ->from('sales_order_items as soi')
            ->join('publisher as p', 'p.id = soi.publisher_id', 'inner')
            ->where('soi.order_id', $order_id)
            ->where('p.status', 1)
            ->group_by('p.id')
            ->order_by('p.publication_name', 'ASC')
            ->get();
        if ($query1) {
            foreach ($query1->result() as $m) {
                $all_merchants[$m->id] = (object)[
                    'id' => (string)$m->id,
                    'publication_name' => trim($m->publication_name)
                ];
            }
        }

        // 3. Fallback: check products.publisher_id for items in this order
        $query3 = $this->db->select('p.id, p.publication_name')
            ->from('sales_order_items as soi')
            ->join('products as prod', 'prod.id = soi.product_id', 'inner')
            ->join('publisher as p', 'p.id = prod.publisher_id', 'inner')
            ->where('soi.order_id', $order_id)
            ->group_start()
                ->where('soi.publisher_id IS NULL', null, false)
                ->or_where('soi.publisher_id', 0)
                ->or_where('soi.publisher_id', '')
            ->group_end()
            ->where('p.status', 1)
            ->group_by('p.id')
            ->order_by('p.publication_name', 'ASC')
            ->get();
        if ($query3) {
            foreach ($query3->result() as $m) {
                $all_merchants[$m->id] = (object)[
                    'id' => (string)$m->id,
                    'publication_name' => trim($m->publication_name)
                ];
            }
        }

        // 4. Check b2b_orders strictly for this webshop order
        // NOTE: We only match bo.webshop_order_id = $order_id.
        // We do NOT use bo.order_id = $order_id because bo.order_id is the auto-increment PK of b2b_orders,
        // which matches unrelated orders belonging to different merchants.
        $query2 = $this->db->select('p.id, p.publication_name')
            ->from('b2b_orders as bo')
            ->join('publisher as p', 'p.id = bo.publisher_id', 'inner')
            ->where('bo.webshop_order_id', $order_id)
            ->where('bo.status !=', 3)
            ->where('p.status', 1)
            ->group_by('p.id')
            ->order_by('p.publication_name', 'ASC')
            ->get();
        if ($query2) {
            foreach ($query2->result() as $m) {
                $all_merchants[$m->id] = (object)[
                    'id' => (string)$m->id,
                    'publication_name' => trim($m->publication_name)
                ];
            }
        }

        // Sort merchants alphabetically by publication_name
        usort($all_merchants, function ($a, $b) {
            return strcasecmp($a->publication_name, $b->publication_name);
        });

        return array_values($all_merchants);
    }

    public function get_order_merchant_products($order_id = '', $merchant_id = '')
    {
        if (!empty($order_id) && $order_id != 0 && $order_id != '') {
            $this->db->select('
                soi.item_id, 
                soi.product_id, 
                soi.product_name as name, 
                soi.qty_ordered as qty, 
                COALESCE(NULLIF(soi.publisher_id, 0), prod.publisher_id, 0) as publisher_id
            ');
            $this->db->from('sales_order_items as soi');
            $this->db->join('products as prod', 'prod.id = soi.product_id', 'left');
            $this->db->where('soi.order_id', $order_id);

            if (!empty($merchant_id) && $merchant_id != 0 && $merchant_id != '') {
                $this->db->group_start();
                    $this->db->where('soi.publisher_id', $merchant_id);
                    $this->db->or_group_start();
                        $this->db->group_start();
                            $this->db->where('soi.publisher_id IS NULL', null, false);
                            $this->db->or_where('soi.publisher_id', 0);
                        $this->db->group_end();
                        $this->db->where('prod.publisher_id', $merchant_id);
                    $this->db->group_end();
                $this->db->group_end();
            }

            $this->db->group_by(['soi.product_id']);
            $this->db->order_by('soi.product_name', 'ASC');

            $query = $this->db->get();
            return $query ? $query->result() : [];
        } elseif (!empty($merchant_id) && $merchant_id != 0 && $merchant_id != '') {
            // When no order_id is provided, fetch active catalog products for the merchant
            $this->db->select('
                prod.id as product_id,
                prod.name,
                prod.publisher_id
            ');
            $this->db->from('products as prod');
            $this->db->where('prod.publisher_id', $merchant_id);
            $this->db->where('prod.status', 1);
            $this->db->order_by('prod.name', 'ASC');

            $query = $this->db->get();
            return $query ? $query->result() : [];
        }

        return [];
    }

    public function get_help_desk_data($customer_id) {
        return $this->db->select('hd.*, so.order_barcode as display_order_no')
            ->from('help_desk as hd')
            ->join('sales_order as so', 'so.order_id = hd.order_id', 'left')
            ->where('hd.customer_id', $customer_id)
            ->order_by('hd.id', 'DESC')
            ->get()
            ->result();
    }

   public function post_to_facebook($merchant)
    {
        $pageId = $this->config->item('fb_page_id');
        $token  = $this->config->item('fb_page_access_token');

        $message = "🎉 A New Store Just Joined Us! 🎉

        🛍 Store Name: {$merchant->publication_name}
        📍 City: {$merchant->city}, {$merchant->state}

        Support this local business today! 🚀";

        $url = "https://graph.facebook.com/$pageId/feed";

        $data = [
            'message'      => $message,
            'access_token' => $token
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            log_message('error', 'FB Posting Error: ' . curl_error($ch));
        }

        curl_close($ch);
        log_message('info', 'FB API Response: ' . $response);

        return json_decode($response);
    }


    public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $lang_code ='')
    {

        // $GlobalVar = $this->getGlobalVariableByIdentifier('fbc-admin-email');
        // if (isset($GlobalVar) && $GlobalVar->value != '') {
        $from_email = 'noreply@yellowmarkets.com';
        // }
        $emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
        // $subject = (isset($email_subject) && $email_subject != '') ? $email_subject : $emailTemplate->subject;
        // ✅ Always use template subject

        //echo lang_code;die;

        if(!empty($lang_code) && $lang_code === 'fr'){
            $subject = $emailTemplate->subject_fr;
            $title = $emailTemplate->title_fr;
            $content = $emailTemplate->content_fr;
        }else{
            $subject = $emailTemplate->subject;
            $title = $emailTemplate->title;
            $content = $emailTemplate->content;
        }

        //echo $content."<hr>";

        // ✅ Replace variables in SUBJECT
        $subject = str_replace($TempVars, $DynamicVars, $subject);
        $emailBody = str_replace($TempVars, $DynamicVars,$content);

        //echo $emailBody."<hr>";
        
        // $data['title'] = $title;
        $data['subject'] = $subject;
        $data['content'] = $emailBody;
        $email_content = $this->load->view('email_template/email_content', $data, TRUE);

        // Remove markdown fences if they exist
        $email_content = str_replace(
            array('```html', '```'),
            '',
            $email_content
        );

        //echo $email_content;die;

        if ($this->sendHTMLMailSMTP($EmailTo, $subject, $email_content, $from_email, $attachment = "")) {
            return true;
        } else {
            return false;
        }
    }

    public function get_custom_variable($identifier)
    {
        $query = $this->db
            ->select('value')
            ->where('identifier', $identifier)
            ->get('custom_variables');

        if ($query->num_rows() > 0) {
            return $query->row()->value;
        }

        return false;
    }

    public function sendMailSMTP($to, $subject, $message, $from_email, $attachment = "")
	{

		$webshop_smtp_host = $this->get_custom_variable('smtp_host');
		$webshop_smtp_port = $this->get_custom_variable('smtp_port');
		$webshop_smtp_username = $this->get_custom_variable('smtp_username');
		$webshop_smtp_password = $this->get_custom_variable('smtp_password');
		$smtp_secure = $this->get_custom_variable('smtp_secure');

        //echo $webshop_smtp_host." = ".$webshop_smtp_port." = ".$webshop_smtp_username." = ".$webshop_smtp_password." = ".$smtp_secure;die;

		// $getWebShopSiteName = $this->getFbcUsersWebShopSiteName($shopcode);
		// if(isset($getWebShopSiteName['site_name']) && $getWebShopSiteName['site_name'] !=''){
		// 	$SiteTitle = $getWebShopSiteName['site_name'];
		// }else{
		// 	$SiteTitle = SITE_TITLE;
		// }

		$mail = new PHPMailer();

		if ($webshop_smtp_host['value'] != '' && $webshop_smtp_port['value'] != '' && $webshop_smtp_username['value'] != '' && $webshop_smtp_password['value'] != '' && $smtp_secure['value'] != '') {

			$mail->IsSMTP();
			$mail->Host = $webshop_smtp_host['value']; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = $webshop_smtp_port['value']; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = $smtp_secure['value'];
			$mail->Username = $webshop_smtp_username['value']; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = $webshop_smtp_password['value']; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail
            $mail->addBCC('deepak@bcod.co.in');

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
		} else {

			$mail->IsSMTP();
			$mail->Host = SMTP_HOST; //Hostname of the mail server
			$mail->SMTPDebug = 0;
			$mail->Port = SMTP_PORT; //Port of the SMTP like to be 25, 80, 465 or 587
			$mail->SMTPAuth = true; //Whether to use SMTP authentication
			$mail->SMTPSecure = 'ssl';
			$mail->Username = SMTP_UNAME; //Username for SMTP authentication any valid email created in your domain
			$mail->Password = SMTP_PWORD; //Password for SMTP authentication
			$mail->SetFrom($from_email); //From address of the mail
			$mail->IsHTML(true);
			$mail->Subject = $subject; //Subject od your mail
            $mail->addBCC('deepak@bcod.co.in');

			if (is_string($to)) {
				$mail->AddAddress($to); //To address who will receive this email
				$mail->MsgHTML($message); //Put your body of the message you can place html code here
				// $mail->AddAttachment($attachment);
				$send = $mail->Send(); //Send the mails
			}

			if ($send) {
				return true;
			} else {
				return false;
			}
		}
	}

    public function sendHTMLMailSMTP($to, $subject, $content, $from_email = '', $attachment = "", $webshop_smtp_host = "", $webshop_smtp_port = "", $webshop_smtp_username = "", $webshop_smtp_password = "", $webshop_smtp_secure = "")

    {

       $webshop_smtp_host = $this->get_custom_variable('smtp_host');
		$webshop_smtp_port = $this->get_custom_variable('smtp_port');
		$webshop_smtp_username = $this->get_custom_variable('smtp_username');
		$webshop_smtp_password = $this->get_custom_variable('smtp_password');
		$webshop_smtp_secure = $this->get_custom_variable('smtp_secure');

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
        $this->email->bcc('deepak@bcod.co.in'); 
        $this->email->subject($subject);
        $this->email->message($content);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function get_document_by_order_id($order_id)
    {
        $this->db->select('document_file');
        $this->db->from('b2b_orders');
        $this->db->where('order_id', $order_id);
        return $this->db->get()->row();
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
					continue; // ignore cancelled
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
