<?php

/**

 * @property CI_Controller $ci

 */

class CartList
{

    private $ci;
    private $cart_list;
    private $qty_limit;
    private $cart_data;
    private $ship_address;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function render()
    {

        // echo "<pre>";
        // print_r($_SESSION);
        // die;
        $quote_id = $this->ci->session->userdata('QuoteId') ?? '';
        $lang_code = $this->ci->session->userdata('lcode') ?? '';
        $customer_id = $this->ci->session->userdata('LoginID') ?? '';
        $session_id = $this->ci->session->userdata('sis_session_id') ?: ($this->ci->session->userdata('LoginToken') ?? '');

        if (!empty($customer_id)) {
            $has_items = false;
            if (!empty($quote_id)) {
                $item_count = $this->ci->db->from('sales_quote_items')
                    ->where('quote_id', $quote_id)
                    ->count_all_results();
                $has_items = ($item_count > 0);
            }
            if (!$has_items) {
                $active_quote = $this->ci->db->select('SQ.quote_id, SQ.session_id')
                    ->from('sales_quote SQ')
                    ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                    ->where('SQ.customer_id', $customer_id)
                    ->order_by('SQ.quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
                if (!$active_quote) {
                    $active_quote = $this->ci->db->select('quote_id, session_id')
                        ->from('sales_quote')
                        ->where('customer_id', $customer_id)
                        ->order_by('quote_id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                }
                if ($active_quote) {
                    $quote_id = $active_quote->quote_id;
                    $this->ci->session->set_userdata('QuoteId', $quote_id);
                    if (!empty($active_quote->session_id)) {
                        $session_id = $active_quote->session_id;
                        $this->ci->session->set_userdata('sis_session_id', $session_id);
                        $this->ci->session->set_userdata('LoginToken', $session_id);
                    }
                }
            }
        }

        if (empty($session_id)) {
            $session_id = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
            $this->ci->session->set_userdata('sis_session_id', $session_id);
        }

        $cc_post_arr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id, 'lang_code' => $lang_code);
        $this->cart_list = CartRepository::cart_listing($cc_post_arr);

        if (!empty($this->cart_list) && isset($this->cart_list) && $this->cart_list->is_success == 'true') {

            $qty_identifier = 'product_detail_page_max_qty';
            $QtyApiResponse = GlobalRepository::get_custom_variable($qty_identifier);
            if (!empty($QtyApiResponse) && isset($QtyApiResponse) && $QtyApiResponse->statusCode == '200') {
                $RowCV = $QtyApiResponse->custom_variable;
                $qty_limit = $RowCV->value;
            } else {
                $qty_limit = 50;
            }
            $this->qty_limit = $qty_limit;
            $this->cart_data = $this->cart_list->cartData;
        }

        $this->ci->template->load('components/cart_list', ['CartData' => $this->cart_data, 'qty_limit' => $this->qty_limit]);
    }

    public function cartPriceDetails($CartData, $type)
    {
        $this->type = $type;
        $this->ship_address = $this->cartShipAddress() ?? '';
        $this->list_array = array('CartData' => $CartData, 'cartType' => $this->type, 'ShipAddress' => $this->ship_address);
        if (empty($this->list_array)) {
            return;
        }
        $this->ci->template->load('components/cart_total', $this->list_array);
    }


    public function cartShipAddress()
    {
        $quote_id = $this->ci->session->userdata('QuoteId') ?? '';
        $table = 'sales_quote_address';
        $flag = 'own';
        $where = 'quote_id = ? AND  address_type = ? ';
        $order_by = 'ORDER BY address_id DESC';
        $params = array($quote_id, 2);
        $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        $response = CommonRepository::get_table_data($postArr);
        if (!empty($response) && isset($response) && $response->is_success == 'true') {
            $this->ship_address = $response->tableData[0];
        } else {
            $this->ship_address = array();
        }

        return $this->ship_address;
    }
}
