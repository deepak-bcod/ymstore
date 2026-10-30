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

    /*
    |--------------------------------------------------------------------------
    | GENERAL CUSTOMER ORDERS
    |--------------------------------------------------------------------------
    */

    if ($login_type == 'general') {

        $LoginID = (int) $this->ci->session->userdata('LoginID');

        $page = (int) $this->ci->uri->segment(3);
        $page = max(1, $page);

        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $OrderList = [];
        $total_orders = 0;

        $data['OrderList'] = [];
        $data['Total_Order'] = 0;
        $data['Limit'] = $limit;
        $data['Page'] = $page;
        $data['links'] = '';
        $data['escalation_status_map'] = [];

        try {

            // Validate customer LoginID
            if ($LoginID <= 0) {

                log_message(
                    'error',
                    'MY ORDERS ERROR: Customer LoginID is empty or invalid.'
                );

            } else {

                // Get customer orders from API
                $response = OrdersRepository::my_orders_listing(
                    $LoginID,
                    $limit,
                    $offset
                );

                log_message(
                    'error',
                    'MY ORDERS LIST RESPONSE: ' . print_r($response, true)
                );

                // Check API response
                if (!empty($response)) {

                    $is_success = $response->is_success ?? false;

                    $api_orders = $response->OrderData ?? [];

                    if (
                        $is_success === true ||
                        $is_success === 'true' ||
                        $is_success == 1
                    ) {

                        if (
                            is_array($api_orders) ||
                            $api_orders instanceof \Traversable
                        ) {

                            $OrderList = is_array($api_orders)
                                ? $api_orders
                                : iterator_to_array($api_orders);

                            $total_orders = (int) (
                                $response->Total_Order ?? count($OrderList)
                            );
                        }

                    } else {

                        log_message(
                            'error',
                            'MY ORDERS API SUCCESS FLAG: ' .
                            print_r($is_success, true)
                        );
                    }

                } else {

                    log_message(
                        'error',
                        'MY ORDERS ERROR: API returned empty response.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | PAGINATION
                |--------------------------------------------------------------------------
                */

                if (!empty($OrderList)) {

                    $this->ci->load->library('pagination');

                    $config = [];

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

                    $config['use_page_numbers'] = true;
                    $config['base_url'] = base_url() . 'customer/my-orders';
                    $config['total_rows'] = $total_orders;
                    $config['per_page'] = $limit;

                    $this->ci->pagination->initialize($config);

                    $data['links'] = $this->ci->pagination->create_links();

                    /*
                    |--------------------------------------------------------------------------
                    | PREPARE ORDER DATA
                    |--------------------------------------------------------------------------
                    */

                    $order_ids = [];

                    foreach ($OrderList as $order) {

                        if (!empty($order->order_id)) {
                            $order_ids[] = $order->order_id;
                        }

                        if (
                            !empty($secret_return) &&
                            $secret_return == 1 &&
                            isset($order->status) &&
                            $order->status == 6
                        ) {

                            $order->flag = 'able_to_return';
                        }

                        $order->ReturnOrders = $order->return_orders ?? [];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER ESCALATION STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($order_ids)) {

                        try {

                            if (!isset($this->ci->OrderResolutionModel)) {

                                $this->ci->load->model(
                                    'OrderResolutionModel'
                                );
                            }

                            if (isset($this->ci->OrderResolutionModel)) {

                                $data['escalation_status_map'] =
                                    $this->ci->OrderResolutionModel
                                        ->get_escalation_status_for_order_items(
                                            $order_ids
                                        );
                            }

                        } catch (\Throwable $e) {

                            log_message(
                                'error',
                                'MY ORDERS ESCALATION ERROR: ' .
                                $e->getMessage() .
                                ' FILE: ' . $e->getFile() .
                                ' LINE: ' . $e->getLine()
                            );
                        }
                    }
                }
            }

        } catch (\Throwable $e) {

            log_message(
                'error',
                'MY ORDERS MAIN ERROR: ' .
                $e->getMessage() .
                ' FILE: ' . $e->getFile() .
                ' LINE: ' . $e->getLine()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD CUSTOMER ORDERS VIEW
        |--------------------------------------------------------------------------
        */

        $data['OrderList'] = $OrderList;
        $data['Total_Order'] = $total_orders;
        $data['Limit'] = $limit;
        $data['Page'] = $page;

        $this->ci->template->load(
            'components/order/customer_general_orders',
            $data
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | GUEST ORDER DETAILS
        |--------------------------------------------------------------------------
        */

        $order_id = urldecode($this->ci->uri->segment(3));

        $order_id = base64_decode($order_id);

        $postArr = [];

        $data['OrderData'] = null;

        try {

            $response = OrdersRepository::my_order_detail($order_id);

            if (
                !empty($response) &&
                isset($response->is_success) &&
                $response->is_success == 'true' &&
                !empty($response->OrderData)
            ) {

                $OrderData = $response->OrderData;

                $data['OrderData'] = $OrderData;

                $order_id = $OrderData->order_id;

                if (
                    !empty($secret_return) &&
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

        } catch (\Throwable $e) {

            log_message(
                'error',
                'GUEST ORDER ERROR: ' .
                $e->getMessage() .
                ' FILE: ' . $e->getFile() .
                ' LINE: ' . $e->getLine()
            );
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
