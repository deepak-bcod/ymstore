<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Sitemap extends CI_Controller
{
    public function index()
    {
        // If accessed as /sitemap without .xml, redirect to /sitemap.xml so .xml is shown in browser
        if ($this->uri->uri_string() === 'sitemap') {
            redirect(base_url('sitemap.xml'), 'location', 301);
            return;
        }

        //static pages
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'cms_pages';
        $flag = 'own';
        $where = 'status = ? AND  remove_flag = ? ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0);
        $postArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        // $response= $this->restapi->post_method($apiUrl, $postArr);
        if ($response->is_success == 'true') {
            $data['cms_pages'] = $response->tableData;
        } else {
            $data['cms_pages'] = array();
        }
        // print_r($data['cms_pages']);die();

        //main category
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'category';
        $flag = 'main';
        $where = 'status = ? AND parent_id= ? ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0);
        $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        // $response = $this->restapi->post_method($apiUrl, $postArr);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        if ($response->is_success == 'true') {
            $data['categorymenu'] = $response->tableData;
        } else {
            $data['categorymenu'] = array();
        }
        // echo"<pre>"; print_r($data['categorymenu']);die();

        //main category
        $date = strtotime(date('d-m-Y'));
        $apiUrl = '/webshop/get_table_data'; //get_table_data
        $table = 'products';
        $flag = 'own';
        $where = 'status = ? AND  remove_flag = ? AND launch_date <= ?  AND product_type IN (?,?) ';
        $order_by = 'ORDER BY id DESC';
        $params = array(1, 0, $date, 'simple', 'configurable');
        $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
        // $response = $this->restapi->post_method($apiUrl, $postArr);
        $response =   CommonRepository::get_table_data($postArr, 3600);
        if ($response->is_success == 'true') {
            $data['products'] = $response->tableData;
        } else {
            $data['products'] = array();
        }

        // echo"<pre>"; print_r($data['products']);die();
        // Load view as string to eliminate any stray whitespace or BOM
        $xml = $this->load->view('sitemap', $data, true);

        // Discard any previous buffer (whitespaces from included config/model files)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Ensure <?xml starts at line 1, column 1 with no leading spaces or BOM
        $xml = ltrim($xml);
        $xml = preg_replace('/^[\xEF\xBB\xBF\s]+/', '', $xml);

        header("Content-Type: text/xml; charset=utf-8");
        echo $xml;
        exit;
    }
}
