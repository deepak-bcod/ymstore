<?php

/**
 * @property CI_Controller $ci
 */
class Orders
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function list($login_type)
    {
        $data['login_type'] = $login_type;
        $secret_return = $this->ci->input->get('secret-return') ?? '';
        if ($login_type == 'general') {
            $LoginID = $this->ci->session->userdata('LoginID');
            $page = ($this->ci->uri->segment(3) == '' || $this->ci->uri->segment(3) == null) ? 1 : $this->ci->uri->segment(3);
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $offset = ($offset == 1) ? 0 : $offset;

            $response = OrdersRepository::my_orders_listing($LoginID, $limit, $offset);
            $OrderList = [];
            $total_orders = 0;

            if (!empty($response) && isset($response->is_success) && $response->is_success == 'true' && !empty($response->OrderData)) {
                $OrderList = $response->OrderData;
                $total_orders = (int)($response->Total_Order ?? count($OrderList));
            } else {
                // FALLBACK: Query directly via database if WebAPI response fails or returns empty/error
                $fallback = $this->fetchOrdersDirectFallback($LoginID, $limit, $offset);
                $OrderList = $fallback['orders'];
                $total_orders = (int)$fallback['total'];
            }

            if (!empty($OrderList)) {
                $data['Total_Order'] = $total_orders;
                $data['Limit'] = $limit;
                $data['Page'] = $page;
                $config = array();
                $config["first_tag_open"] = "<li class='page-item'>";
                $config["first_tag_close"] = "</li>";
                $config["last_tag_open"] = "<li class='page-item'>";
                $config["last_tag_close"] = "</li>";
                $config["next_tag_open"] = "<li class='page-item'>";
                $config["next_tag_close"] = "</li>";
                $config["prev_tag_open"] = "<li class='page-item'>";
                $config["prev_tag_close"] = "</li>";
                $config["cur_tag_open"] = "<li class='page-item active'><a>";
                $config["cur_tag_close"] = "</a></li>";
                $config["num_tag_open"] = "<li class='page-item'>";
                $config["num_tag_close"] = "</li>";
                $config['use_page_numbers'] = true;
                $config["base_url"] = base_url() . "customer/my-orders";
                $config["total_rows"] = $total_orders;
                $config["per_page"] = $limit;
                $this->ci->pagination->initialize($config);
                $data["links"] = $this->ci->pagination->create_links();

                $order_ids = [];
                foreach ($OrderList as $order) {
                    $order_id = $order->order_id;
                    $order_ids[] = $order_id;
                    if (isset($secret_return) && !empty($secret_return) && $secret_return == 1 && $order->status == 6) {
                        $order->flag = 'able_to_return';
                    }
                    $order->ReturnOrders = $order->return_orders ?? [];
                }
                if (!empty($order_ids)) {
                    if (!isset($this->ci->OrderResolutionModel)) {
                        $this->ci->load->model('OrderResolutionModel');
                    }
                    $data['escalation_status_map'] = $this->ci->OrderResolutionModel->get_escalation_status_for_order_items($order_ids);
                }
                $data['OrderList'] = $OrderList;
            }
            $this->ci->template->load('components/order/customer_general_orders', $data);
        } else {
            $order_id = urldecode($this->ci->uri->segment(3));
            $order_id = base64_decode($order_id);
            $postArr = array();
            $response = OrdersRepository::my_order_detail($order_id);
            if (!empty($response) && $response->is_success == 'true') {
                $OrderData = $response->OrderData;
                $response->OrderData;
                $data['OrderData'] = $OrderData;
                $order_id = $data['OrderData']->order_id;
                if (isset($secret_return) && !empty($secret_return) && $secret_return == 1 && $OrderData->status == 6) {
                    $data['OrderData']->flag = 'able_to_return';
                } else {
                    $data['OrderData']->flag = $this->checkOperationFlag($order_id);
                }
                $data['OrderData']->ReturnOrders = $this->returnOrderListing($order_id);
            }
            $this->ci->template->load('components/order/customer_guest_orders', $data);
        }
    }

    public function checkOperationFlag($order_id)
    {
        $flag = '';
        $postArr = array('order_id' => $order_id, 'operation_name' => 'can_able_to_cancel');
        $response = OrdersRepository::order_operation_checks(SHOPCODE, SHOP_ID, $postArr);
        if (!empty($response)) {
            if ($response->is_success == 'true') {
                $flag = $response->flag;
                if ($response->flag == 'able_to_cancel') {
                    $flag = $response->flag;
                } else {
                    /*----------------------check if order is available for return-------------------------*/
                    $product_return_duration_count = 0;
                    $product_return_duration = 'product_return_duration';
                    $CVApiResponse = GlobalRepository::get_custom_variable(SHOPCODE, SHOP_ID, $product_return_duration);
                    if (!empty($CVApiResponse) && $CVApiResponse->statusCode == '200') {
                        $RowCV = $CVApiResponse->custom_variable;
                        $product_return_duration_count = $RowCV->value;
                    } else {
                        $product_return_duration_count = 0;
                    }

                    if ($product_return_duration_count > 0) {
                        $postArr = array('order_id' => $order_id, 'operation_name' => 'can_able_to_return', 'product_return_duration' => $product_return_duration_count);
                        $response2 = OrdersRepository::order_operation_checks(SHOPCODE, SHOP_ID, $postArr);
                        if (!empty($response2) && isset($response2) && $response2->is_success == 'true') {
                            $flag = $response2->flag;
                        }
                    }
                }
            }
        }

        return $flag;
    }


    public function returnOrderListing($order_id)
    {

        $ret_response = OrdersRepository::my_return_orders_listing(SHOPCODE, SHOP_ID, $order_id);
        if (!empty($ret_response) && $ret_response->is_success == 'true') {
            $ReturnOrders = $ret_response->ReturnOrderCollection;
        } else {
            $ReturnOrders = array();
        }

        return $ReturnOrders;
    }

    protected function fetchOrdersDirectFallback($LoginID, $limit, $offset)
    {
        if (empty($LoginID)) {
            return ['orders' => [], 'total' => 0];
        }

        $total = $this->ci->db->from('sales_order')
            ->where('customer_id', $LoginID)
            ->where('status !=', 7)
            ->count_all_results();

        if ($total <= 0) {
            return ['orders' => [], 'total' => 0];
        }

        $orders = $this->ci->db->select('`order`.*, `inv`.`invoice_file`')
            ->from('sales_order as `order`')
            ->join('invoicing as `inv`', '`order`.`invoice_id` = `inv`.`id`', 'left')
            ->where('`order`.`customer_id`', $LoginID)
            ->where('`order`.`status` !=', 7)
            ->order_by('`order`.`created_at`', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();

        if (empty($orders)) {
            return ['orders' => [], 'total' => $total];
        }

        $order_ids = array_column($orders, 'order_id');

        // Fetch sales_order_items
        $items_by_order = [];
        if (!empty($order_ids)) {
            $items = $this->ci->db->select('soi.*, COALESCE(p.base_image, pp.base_image) as base_image, p.url_key')
                ->from('sales_order_items as soi')
                ->join('products as p', 'p.id = soi.product_id', 'left')
                ->join('products as pp', 'pp.id = soi.parent_product_id', 'left')
                ->where_in('soi.order_id', $order_ids)
                ->order_by('soi.item_id', 'ASC')
                ->get()
                ->result();

            foreach ($items as $item) {
                $items_by_order[$item->order_id][] = $item;
            }
        }

        // Fetch b2b_orders
        $b2b_by_order = [];
        if (!empty($order_ids)) {
            $b2b_list = $this->ci->db->select('bo.*, p.name as merchant_name')
                ->from('b2b_orders as bo')
                ->join('publishers as p', 'p.id = bo.publisher_id', 'left')
                ->where_in('bo.webshop_order_id', $order_ids)
                ->get()
                ->result();

            $sub_order_ids = !empty($b2b_list) ? array_column($b2b_list, 'order_id') : [];
            $b2b_items_grouped = [];
            if (!empty($sub_order_ids)) {
                $b2b_items = $this->ci->db->select('boi.*, p.base_image')
                    ->from('b2b_order_items as boi')
                    ->join('products as p', 'p.id = boi.product_id', 'left')
                    ->where_in('boi.order_id', $sub_order_ids)
                    ->get()
                    ->result();
                foreach ($b2b_items as $bi) {
                    $b2b_items_grouped[$bi->order_id][] = $bi;
                }
            }

            foreach ($b2b_list as $b2b) {
                $b2b->sub_order_items = $b2b_items_grouped[$b2b->order_id] ?? [];
                $b2b_by_order[$b2b->webshop_order_id][] = $b2b;
            }
        }

        // Fetch return orders
        $returns_by_order = [];
        if (!empty($order_ids)) {
            $returns = $this->ci->db->select('*')
                ->from('sales_order_return')
                ->where_in('order_id', $order_ids)
                ->where_not_in('status', [0, 1])
                ->order_by('created_at', 'DESC')
                ->get()
                ->result();
            foreach ($returns as $ret) {
                $returns_by_order[$ret->order_id][] = $ret;
            }
        }

        foreach ($orders as $order) {
            $order->order_items = $items_by_order[$order->order_id] ?? [];
            $order->b2b_orders = $b2b_by_order[$order->order_id] ?? [];
            $order->return_orders = $returns_by_order[$order->order_id] ?? [];
            $order->delivery_status = '';
            $order->delivery_status_no = 0;
            $order->flag = ($order->status == 0) ? 'able_to_cancel' : false;
        }

        return ['orders' => $orders, 'total' => $total];
    }
}
