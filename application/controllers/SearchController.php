<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SearchController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('pagination');
        $this->load->library('Ajax_pagination');
        // $this->perPage = 3;
        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }
    }

    public function searchResultPage()
    {
        $LoginID = $this->session->userdata('LoginID');
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

        $search_val = isset($_GET['s']) ? $_GET['s'] : (isset($_GET['q']) ? $_GET['q'] : '');
        $data['PageTitle'] = 'Search';
        $data['PageMetaTitle'] = $search_val . ' - indiamags';
        $data['PageMetaDesc'] = $search_val;
        $data['PageMetaKey'] = $search_val;
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $page = 0;

        $identifier = 'browse_by_gender_enabled';
        $data['customVariable'] = $customVariable =  GlobalRepository::get_custom_variable($identifier);
        $identity = 'product_listing_get_show_records_list';

        $customVariable =  GlobalRepository::get_custom_variable($identity);

        $data['show_limit'] = $show_limit = 0;
        if (isset($customVariable->statusCode) && $customVariable->statusCode == '200') {
            $variable = $customVariable->custom_variable;
            $data['show_limit'] = explode("::", $variable->value);
            $show_limit_drp = $data['show_limit'][0];
        }

        $data['search_term'] = $search_term = urlencode($search_val);
        $data['sort_val'] = $sort_val = !empty($_GET['sort']) && $_GET['sort'] !== 'undefined' ? $_GET['sort'] : 'newest';
        $data['show_limit_selected'] = $show_limit = !empty($_GET['limit']) && is_numeric($_GET['limit']) ? $_GET['limit'] : $show_limit_drp;
        $page = (isset($_GET['page']) ? $_GET['page'] : 0);

        if (!str_contains($search_term, '+%2B')) {
            $search_term = urldecode($search_term);
        }

        // echo $show_limit;
        // if(str_contains)
        $gender = (isset($_GET['gender']) ? explode(",", $_GET['gender']) : array());
        $price_range = (isset($_GET['price_range']) ? $_POST['price_range'] : array());
        $variantId = (isset($_GET['variantId']) ? explode(",", $_GET['variantId']) : array());
        $variantVal = (isset($_GET['variantVal']) ? explode(",", $_GET['variantVal']) : array());
        $attributeArr = (isset($_GET['attribute']) ? explode(",", $_GET['attribute']) : array());

        $productArrCat = array('search_term' => $search_term);
        $categorySearch =  ProductRepository::geSearchtCategoryIds($productArrCat);
        $categoryIdsarr =  array();
        if (!empty($categorySearch) && (isset($categorySearch->statusCode) && $categorySearch->statusCode == '200')) {
            foreach ($categorySearch->categoryIds as $catKey => $catval) {
                array_push($categoryIdsarr, $catval->id);
            }
        }

        $lang_code = '';
        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
            $lang_code = $this->session->userdata('lcode');
        }

        if (isset($LoginID)) {
            $productArr1 = array('search_term' => $search_term, 'options' => $sort_val, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => $LoginID, 'lang_code' => $lang_code, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'categoryIdsarr' => $categoryIdsarr);
        } else {
            $productArr1 = array('search_term' => $search_term, 'options' => $sort_val, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => 0, 'lang_code' => $lang_code, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'categoryIdsarr' => $categoryIdsarr);
        }
        // print_r($productArr1);
        $productCount = 0;
        $main_product_list = ProductRepository::product_listing($productArr1);
        // echo "<pre>" ;print_r($main_product_list);
        // die();
        // print_r($main_product_list);
        if (!empty($main_product_list) && (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200')) {
            $productCount = $main_product_list->ProductListCount;
        }

       
    if ($productCount == 0) {
    $foundProducts = [];
    $search_terms = array_filter(explode(' ', strtolower(trim($search_term)))); 
    
    foreach ($search_terms as $term) {
        if (strlen($term) < 2) continue; 
        
        $productArr1['search_term'] = $term;
        $fallback_results = ProductRepository::product_listing($productArr1);

        if (!empty($fallback_results) && isset($fallback_results->statusCode) && $fallback_results->statusCode == '200') {
            foreach($fallback_results->ProductList as $prod) {
                $foundProducts[$prod->id] = $prod; 
            }
        }
    }

    if (!empty($foundProducts)) {
        // CRITICAL FIX: Ensure $main_product_list is an object
        if (!is_object($main_product_list)) {
            $main_product_list = new stdClass();
        }
        
        $main_product_list->ProductList = array_values($foundProducts);
        $productCount = count($foundProducts);
        $main_product_list->ProductListCount = $productCount;
        $main_product_list->statusCode = '200';
    }
}

        $data['product_list'] = $main_product_list;
        
        //echo "<pre>" ;print_r($data['product_list']);die();

        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');

        if ($page > 0) {
            $cur_page = $page * $show_limit - $show_limit;
        } else {
            $cur_page = 1;
        }
        // echo  count($main_product_list->ProductList;
        // die();
        //pagination configuration
        $config['target']      = '#product-list-section';
        $config['base_url']    = BASE_URL . 'ProductsController/sort_by';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';
        $config['search_terms']   = $search_term;
        $this->ajax_pagination->initialize($config);

        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        if ($productCount > 0) {
            $searchTermArr = array('search_term' => $search_term);
            $saveSearch = SearchRepository::save_search_term($shopcode, $shop_id, $searchTermArr);
        }

        $identifier = 'restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($identifier);
        if ($ApiResponse->statusCode == '200') {
            $RowCV = $ApiResponse->custom_variable;
            $restricted_access = $RowCV->value;
        } else {
            $restricted_access = 'no';
        }
        $data['restricted_access'] = $restricted_access;
      
        // $webshop_name_shop = GlobalRepository::get_fbc_users_shop();
        // $data['shop_flag_shop']=$webshop_name_shop->result->shop_flag ?? '';

        $this->template->load('search/search_result_page', $data);
    }
    
public function getSearchSuggestion() {
    if (!empty($_POST)) {
        $search_term = trim($_POST['search_key']);
        if (empty($search_term)) {
            echo 'No products Found';
            exit;
        }

        $site_lang = $this->session->userdata('site_lang');
        $lcode = $this->session->userdata('lcode');
        $is_french = ($site_lang == 'french' || $lcode == 'fr');
        
        // 1. Try standard search API
        $get_search_terms = SearchRepository::get_search_terms_post($search_term);
        
        if (isset($get_search_terms->statusCode) && $get_search_terms->statusCode == '200' && !empty($get_search_terms->search_result)) {
            echo $this->_generateSuggestionHtml($get_search_terms->search_result, $search_term);
            exit;
        }

        // 2. FALLBACK: Try a partial match if method exists
        if (method_exists('SearchRepository', 'get_partial_match_terms')) {
            $partial_match = SearchRepository::get_partial_match_terms($search_term);
            if (isset($partial_match->statusCode) && $partial_match->statusCode == '200' && !empty($partial_match->search_result)) {
                echo $this->_generateSuggestionHtml($partial_match->search_result, $search_term);
                exit;
            }
        }

        // 3. FALLBACK: Query products table directly for name or lang_title (French title)
        $this->db->select('name, lang_title');
        $this->db->from('products');
        $this->db->where('status', 1);
        $this->db->where('approval_status', 1);
        $this->db->where('remove_flag', 0);
        $this->db->group_start();
        $this->db->like('name', $search_term);
        $this->db->or_like('lang_title', $search_term);
        $this->db->or_like('highlights', $search_term);
        $this->db->or_like('lang_highlights', $search_term);
        $this->db->or_like('search_keywords', $search_term);
        $this->db->group_end();
        $this->db->limit(5);

        $prod_results = $this->db->get()->result();

        if (!empty($prod_results)) {
            $suggestions = [];
            foreach ($prod_results as $prod) {
                $term = ($is_french && !empty($prod->lang_title)) ? $prod->lang_title : (!empty($prod->lang_title) && str_contains(strtolower($prod->lang_title), strtolower($search_term)) ? $prod->lang_title : $prod->name);
                $suggestions[] = (object) ['search_term' => $term];
            }
            echo $this->_generateSuggestionHtml($suggestions, $search_term);
            exit;
        }

        echo 'No products Found';
        exit;
    }
}

// Helper function to keep code clean
private function _generateSuggestionHtml($search_result, $original_term) {
    $count = 0;
    $html = '<ul>';
    $site_lang = $this->session->userdata('site_lang');
    $lcode = $this->session->userdata('lcode');
    $is_french = ($site_lang == 'french' || $lcode == 'fr');

    foreach ($search_result as $value) {
        $term = '';
        if (isset($value->search_term)) {
            $term = $value->search_term;
        } elseif ($is_french && !empty($value->lang_title)) {
            $term = $value->lang_title;
        } else {
            $term = !empty($value->name) ? $value->name : '';
        }

        if ($is_french && !empty($term)) {
            $french_name = get_display_product_name((object)['name' => $term]);
            if (!empty($french_name)) {
                $term = $french_name;
            }
        }

        if (!empty($term) && $count < 3) {
            $html .= '<li><a href="' . BASE_URL . 'searchresult/?s=' . urlencode($term) . '">' . htmlspecialchars($term) . '</a></li>';
            $count++;
        }
    }
    if ($count > 3 || (is_array($search_result) && count($search_result) > 3)) {
        $html .= '<li><a href="' . BASE_URL . 'searchresult/?s=' . urlencode($original_term) . '" style="color: #E02222;">See More</a></li>';
    }
    $html .= '</ul>';
    return $html;
}
	
}
