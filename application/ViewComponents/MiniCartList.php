<?php
/**
 * @property CI_Controller $ci
 */
class MiniCartList {
    private $ci;
    private $mini_cart_list;
    private $cart_count = 0;

    public function __construct(){
        $this->ci =& get_instance();

        $this->mini_cart_list = CartRepository::cart_listing($this->get_cc_post_arr());
        $this->cart_count = $this->mini_cart_list->cartData->cartCount ?? 0;
    }

    public function render(){
        $this->ci->template->load('components/mini_cart_list', ['cart_response' => $this->mini_cart_list, 'cart_count' => $this->cart_count]);
    }

    private function get_cc_post_arr(){
        $cc_post_arr = [];
        
        $cc_post_arr['session_id'] = $this->ci->session->userdata('sis_session_id') ?: $this->ci->session->userdata('LoginToken');
        // $cc_post_arr['lang_code'] = $this->ci->session->userdata('lcode') ?? '' ;

        if ($this->ci->session->userdata('LoginID')) {
            $cc_post_arr['customer_id'] = $this->ci->session->userdata('LoginID');
        }
        if ($this->ci->session->userdata('QuoteId')) {
            $cc_post_arr['quote_id'] = $this->ci->session->userdata('QuoteId');   
        }

        if (!empty($cc_post_arr['customer_id'])) {
            $has_items = false;
            if (!empty($cc_post_arr['quote_id'])) {
                $item_count = $this->ci->db->from('sales_quote_items')
                    ->where('quote_id', $cc_post_arr['quote_id'])
                    ->count_all_results();
                $has_items = ($item_count > 0);
            }
            if (!$has_items) {
                $active_quote = $this->ci->db->select('SQ.quote_id, SQ.session_id')
                    ->from('sales_quote SQ')
                    ->join('sales_quote_items SQI', 'SQI.quote_id = SQ.quote_id')
                    ->where('SQ.customer_id', $cc_post_arr['customer_id'])
                    ->order_by('SQ.quote_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
                if (!$active_quote) {
                    $active_quote = $this->ci->db->select('quote_id, session_id')
                        ->from('sales_quote')
                        ->where('customer_id', $cc_post_arr['customer_id'])
                        ->order_by('quote_id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                }
                if ($active_quote) {
                    $cc_post_arr['quote_id'] = $active_quote->quote_id;
                    $this->ci->session->set_userdata('QuoteId', $active_quote->quote_id);
                    if (!empty($active_quote->session_id)) {
                        $cc_post_arr['session_id'] = $active_quote->session_id;
                        $this->ci->session->set_userdata('sis_session_id', $active_quote->session_id);
                        $this->ci->session->set_userdata('LoginToken', $active_quote->session_id);
                    }
                }
            }
        }

        if (empty($cc_post_arr['session_id'])) {
            $token = function_exists('generateToken') ? generateToken('50') : md5(uniqid((string)mt_rand(), true));
            $cc_post_arr['session_id'] = $token;
            $this->ci->session->set_userdata('sis_session_id', $token);
        }

        return $cc_post_arr;
    }
}
