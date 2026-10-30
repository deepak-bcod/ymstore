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

        // 1. Get logged-in customer ID
        $LoginID = (int) $this->ci->session->userdata('LoginID');

        $limit = 20;

        // 2. Validate customer session
        if ($LoginID <= 0) {

            log_message('error', 'MY ORDERS: Customer LoginID is missing.');

            $data['OrderList'] = [];
            $data['Total_Order'] = 0;
            $data['Limit'] = $limit;
            $data['Page'] = 1;
            $data['links'] = '';
            $data['escalation_status_map'] = [];

            $this->ci->template->load(
                'components/order/customer_general_orders',
                $data
            );

            return;
        }

        // 3. Pagination
        $page = (int) $this->ci->uri->segment(3);

        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        // 4. Call API
        $response = OrdersRepository::my_orders_listing(
            $LoginID,
            $limit,
            $offset
        );

        // 5. Normalize API response
        if (is_string($response)) {

            $response = json_decode($response);

        } elseif (is_array($response)) {

            $response = json_decode(json_encode($response));
        }

        // Debug logs
        log_message('error', 'MY ORDERS CUSTOMER ID: ' . $LoginID);
        log_message('error', 'MY ORDERS PAGE: ' . $page);
        log_message('error', 'MY ORDERS API OFFSET: ' . $offset);
        log_message('error', 'MY ORDERS RESPONSE TYPE: ' . gettype($response));
        log_message('error', 'MY ORDERS API RESPONSE: ' . print_r($response, true));

        // 6. Initialize order data
        $OrderList = [];
        $total_orders = 0;

        // 7. Check API response
        if (
            is_object($response) &&
            isset($response->is_success) &&
            in_array($response->is_success, ['true', true, 1, '1'], true)
        ) {

            $OrderData = $response->OrderData ?? [];

            // Convert order data into an array of objects
            if (is_array($OrderData)) {

                $OrderList = json_decode(json_encode($OrderData));

            } elseif (is_object($OrderData)) {

                $OrderList = array_values((array) $OrderData);
            }

            if (!is_array($OrderList)) {
                $OrderList = [];
            }

            $total_orders = (int) (
                $response->Total_Order ?? count($OrderList)
            );

        } else {

            log_message(
                'error',
                'MY ORDERS API ERROR: ' . print_r($response, true)
            );
        }

        log_message(
            'error',
            'MY ORDERS FINAL COUNT: ' . count($OrderList)
        );

        // 8. Prepare view data
        $data['OrderList'] = $OrderList;
        $data['Total_Order'] = $total_orders;
        $data['Limit'] = $limit;
        $data['Page'] = $page;
        $data['links'] = '';
        $data['escalation_status_map'] = [];

        // 9. Pagination links
        if ($total_orders > $limit) {

            $config = [];

            $config['base_url'] = base_url() . 'customer/my-orders';
            $config['total_rows'] = $total_orders;
            $config['per_page'] = $limit;
            $config['use_page_numbers'] = true;
            $config['uri_segment'] = 3;
            $config['num_links'] = 5;

            $config['first_tag_open'] = "<li class='page-item'>";
            $config['first_tag_close'] = "</li>";

            $config['last_tag_open'] = "<li class='page-item'>";
            $config['last_tag_close'] = "</li>";

            $config['next_tag_open'] = "<li class='page-item'>";
            $config['next_tag_close'] = "</li>";

            $config['prev_tag_open'] = "<li class='page-item'>";
            $config['prev_tag_close'] = "</li>";

            $config['cur_tag_open'] = "<li class='page-item active'><a>";
            $config['cur_tag_close'] = "</a></li>";

            $config['num_tag_open'] = "<li class='page-item'>";
            $config['num_tag_close'] = "</li>";

            $this->ci->pagination->initialize($config);

            $data['links'] = $this->ci->pagination->create_links();
        }

        // 10. Process order information
        if (!empty($OrderList)) {

            $order_ids = [];

            foreach ($OrderList as $order) {

                if (isset($order->order_id)) {

                    $order_ids[] = (int) $order->order_id;

                    // Return permission
                    if (
                        $secret_return == 1 &&
                        isset($order->status) &&
                        $order->status == 6
                    ) {
                        $order->flag = 'able_to_return';
                    }

                    // Return orders
                    $order->ReturnOrders = $order->return_orders ?? [];
                }
            }

            // 11. Get escalation status
            if (!empty($order_ids)) {

                try {

                    if (
                        !empty($this->ci->db) &&
                        !empty($this->ci->db->conn_id)
                    ) {

                        if (!isset($this->ci->OrderResolutionModel)) {
                            $this->ci->load->model('OrderResolutionModel');
                        }

                        if (isset($this->ci->OrderResolutionModel)) {

                            $data['escalation_status_map'] =
                                $this->ci->OrderResolutionModel
                                    ->get_escalation_status_for_order_items(
                                        $order_ids
                                    );
                        }
                    }

                } catch (\Throwable $e) {

                    log_message(
                        'error',
                        'MY ORDERS ESCALATION ERROR: ' . $e->getMessage()
                    );
                }
            }
        }

        // 12. Load customer orders view
        $this->ci->template->load(
            'components/order/customer_general_orders',
            $data
        );

    } else {

        // Guest order detail - existing functionality
        $order_id = urldecode($this->ci->uri->segment(3));
        $order_id = base64_decode($order_id);

        $response = OrdersRepository::my_order_detail($order_id);

        if (
            !empty($response) &&
            isset($response->is_success) &&
            $response->is_success == 'true'
        ) {

            $OrderData = $response->OrderData;

            $data['OrderData'] = $OrderData;

            $order_id = $OrderData->order_id;

            if (
                $secret_return == 1 &&
                $OrderData->status == 6
            ) {

                $data['OrderData']->flag = 'able_to_return';

            } else {

                $data['OrderData']->flag =
                    $this->checkOperationFlag($order_id);
            }

            $data['OrderData']->ReturnOrders =
                $this->returnOrderListing($order_id);
        }

        $this->ci->template->load(
            'components/order/customer_guest_orders',
            $data
        );
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
}
