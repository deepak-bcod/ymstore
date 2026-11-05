<?php

defined('BASEPATH') or exit('No direct script access allowed');



class MyOrdersController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {
            redirect(BASE_URL.'customer/login');
        }

        $this->load->library("pagination");

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }

    /*

    public function getProfileDetails(){

        $data['PageTitle']= 'My Profile - Personal Information';

        $LoginID = $_SESSION['LoginID'];



        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        $data['side_tab'] = 'account_info';



        $apiUrl = '/webshop/customer_get_personal_info'; //customer_get_personal_info

        $postArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'customer_id'=>$LoginID);

        $response= $this->restapi->post_method($apiUrl,$postArr);

        //echo '<pre>';print_r($response);//exit;

        if($response->is_success=='true'){

            $data['customerData'] = $response->customerData;

        }



        $apiUrl2 = '/webshop/get_table_data'; //get_table_data

        $table = 'country_master';

        $flag = 'own';

        $postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table,'database_flag'=>$flag);

        $response2= $this->restapi->post_method($apiUrl2,$postArr2);

        //echo '<pre>';print_r($response2);//exit;

        if($response2->is_success=='true'){

            $data['countryList'] = $response2->tableData;

        }

        $this->load->view('myprofile/personal_info', $data);

    }

    */

    public function getOrderItems()
    {
        $order_id = $this->input->post('order_id');

        if (empty($order_id)) {
            echo json_encode(['status' => 0, 'html' => '<p>Invalid Order ID.</p>']);
            return;
        }

        // Support increment_id if passed
        if (!is_numeric($order_id)) {
            $ord = $this->db->select('order_id')->where('increment_id', $order_id)->get('sales_order')->row();
            if ($ord) {
                $order_id = $ord->order_id;
            }
        }

        // STEP 1: GET ALL B2B ORDER ITEMS WITH STATUSES & PUBLISHER MAPPED BY PRODUCT
        $b2b_items = $this->db->select('
                boi.product_id AS product_id,
                boi.product_name AS product_name,
                boi.order_id AS b2b_order_id,
                boi.item_id AS b2b_item_id,
                boi.status AS b2b_item_status,
                bo.status AS b2b_order_status,
                bo.publisher_id AS publisher_id
            ')
            ->from('b2b_orders AS bo')
            ->join('b2b_order_items AS boi', 'boi.order_id = bo.order_id', 'inner')
            ->where('bo.webshop_order_id', $order_id)
            ->get()
            ->result();

        // STEP 2: FETCH RETURNABLE ITEMS FROM SALES ORDER
        $items = $this->db->select('
                soi.item_id,
                soi.product_id,
                soi.product_name,
                soi.qty_ordered,
                soi.price,
                soi.barcode,
                soi.product_type,
                soi.product_variants,
                COALESCE(p.base_image, pp.base_image) AS base_image
            ')
            ->from('sales_order_items AS soi')
            ->join('products AS p', 'p.id = soi.product_id', 'left')
            ->join('products AS pp', 'pp.id = p.parent_id', 'left')
            ->where('soi.order_id', $order_id)
            ->where('soi.can_be_returned', 1)
            ->get()
            ->result();

        if (empty($items)) {
            echo json_encode(['status' => 0, 'html' => '<p>No returnable items found.</p>']);
            return;
        }

        $html = '';

        foreach ($items as $item) {
            $b2b_order_id = 0;
            $b2b_item_id  = 0;
            $publisher_id = 0;
            $is_in_process = false;
            $process_status_label = '';

            // Find all matching B2B items for this sales order item
            $matched_b2b = [];
            if (!empty($b2b_items)) {
                foreach ($b2b_items as $bi) {
                    if ((int)$bi->product_id === (int)$item->product_id) {
                        $matched_b2b[] = $bi;
                    } elseif (!empty($item->product_name) && !empty($bi->product_name) && trim($item->product_name) === trim($bi->product_name)) {
                        $matched_b2b[] = $bi;
                    }
                }
            }

            if (!empty($matched_b2b)) {
                $b2b_order_id = (int)$matched_b2b[0]->b2b_order_id;
                $b2b_item_id  = (int)$matched_b2b[0]->b2b_item_id;
                $publisher_id = (int)$matched_b2b[0]->publisher_id;
            }

            // Candidate IDs for checking return / replacement records
            $candidate_item_ids = array_values(array_unique(array_filter([$b2b_item_id, (int)$item->item_id])));
            $candidate_order_ids = array_values(array_unique(array_filter([$b2b_order_id, (int)$order_id])));

            // 1. Check B2B item status directly
            if (!empty($matched_b2b)) {
                foreach ($matched_b2b as $bi) {
                    $item_st = (int)($bi->b2b_item_status ?? 0);

                    if ($item_st == 21) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('replacement_rejected') ?: 'Replacement Rejected';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 20) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('return_rejected') ?: 'Return Rejected';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 19) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('replaced') ?: 'Replaced';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 18) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('replacement_approved') ?: 'Replacement Approved';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 17) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('refund_paid') ?: 'Refund Paid';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 22 || $item_st == 16) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('return_approved') ?: 'Return Approved';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 15) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('replacement_requested') ?: 'Replacement Requested';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    } elseif ($item_st == 14) {
                        $is_in_process = true;
                        $process_status_label = $this->lang->line('return_requested') ?: 'Return Requested';
                        $b2b_order_id = (int)$bi->b2b_order_id;
                        $b2b_item_id = (int)$bi->b2b_item_id;
                        $publisher_id = (int)$bi->publisher_id;
                        break;
                    }
                }
            }

            // 2. Check sales_order_replacement table
            if (!$is_in_process && !empty($candidate_item_ids)) {
                $rep = $this->db
                    ->select('r.replacement_order_id, r.status as order_status, ri.status as item_status')
                    ->from('sales_order_replacement_items ri')
                    ->join('sales_order_replacement r', 'r.replacement_order_id = ri.replacement_order_id')
                    ->where_in('ri.order_item_id', $candidate_item_ids)
                    ->order_by('r.replacement_order_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();

                if (!$rep && !empty($candidate_order_ids)) {
                    $rep = $this->db
                        ->select('r.replacement_order_id, r.status as order_status, ri.status as item_status')
                        ->from('sales_order_replacement_items ri')
                        ->join('sales_order_replacement r', 'r.replacement_order_id = ri.replacement_order_id')
                        ->where_in('r.order_id', $candidate_order_ids)
                        ->where_in('ri.order_item_id', $candidate_item_ids)
                        ->order_by('r.replacement_order_id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                }

                if ($rep) {
                    $is_in_process = true;
                    $r_ord = (int)$rep->order_status;
                    $r_itm = (int)($rep->item_status ?? 0);

                    if ($r_ord == 4 || $r_itm == 4 || $r_ord == 21 || $r_itm == 21) {
                        $process_status_label = $this->lang->line('replacement_rejected') ?: 'Replacement Rejected';
                    } elseif (in_array($r_ord, [3, 5, 6, 19]) || in_array($r_itm, [3, 5, 6, 19])) {
                        $process_status_label = $this->lang->line('replaced') ?: 'Replaced';
                    } elseif (in_array($r_ord, [1, 2, 18]) || in_array($r_itm, [1, 2, 18])) {
                        $process_status_label = $this->lang->line('replacement_approved') ?: 'Replacement Approved';
                    } else {
                        $process_status_label = $this->lang->line('replacement_requested') ?: 'Replacement Requested';
                    }
                }
            }

            // 3. Check sales_order_return table
            if (!$is_in_process && !empty($candidate_item_ids)) {
                $ret = $this->db
                    ->select('r.return_order_id, r.status as order_status, r.refund_status, ri.status as item_status')
                    ->from('sales_order_return_items ri')
                    ->join('sales_order_return r', 'r.return_order_id = ri.return_order_id')
                    ->where_in('ri.order_item_id', $candidate_item_ids)
                    ->order_by('r.return_order_id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();

                if (!$ret && !empty($candidate_order_ids)) {
                    $ret = $this->db
                        ->select('r.return_order_id, r.status as order_status, r.refund_status, ri.status as item_status')
                        ->from('sales_order_return_items ri')
                        ->join('sales_order_return r', 'r.return_order_id = ri.return_order_id')
                        ->where_in('r.order_id', $candidate_order_ids)
                        ->where_in('ri.order_item_id', $candidate_item_ids)
                        ->order_by('r.return_order_id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                }

                if ($ret) {
                    $is_in_process = true;
                    $ret_ord = (int)$ret->order_status;
                    $ret_itm = (int)($ret->item_status ?? 0);
                    $ret_ref = (int)($ret->refund_status ?? -1);

                    if ($ret_ref == 1 || in_array($ret_ord, [4, 17]) || in_array($ret_itm, [4, 17])) {
                        $process_status_label = $this->lang->line('refund_paid') ?: 'Refund Paid';
                    } elseif ($ret_ref == 2 || in_array($ret_ord, [2, 5, 20]) || in_array($ret_itm, [2, 5, 20])) {
                        $process_status_label = $this->lang->line('return_rejected') ?: 'Return Rejected';
                    } elseif (in_array($ret_ord, [1, 3, 16, 22]) || in_array($ret_itm, [1, 3, 16, 22])) {
                        $process_status_label = $this->lang->line('return_approved') ?: 'Return Approved';
                    } else {
                        $process_status_label = $this->lang->line('return_requested') ?: 'Return Requested';
                    }
                }
            }

            $product_image_url = !empty($item->base_image)
                ? PRODUCT_THUMB_IMG . $item->base_image
                : base_url('assets/images/noimage.png');

            $html .= '<div class="return-item-box" style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
                        <div class="row align-items-center">

                            <div class="col-md-2 text-center">';

            if ($is_in_process) {
                // When sub-order is in Return or Replacement process: do not display checkbox
                if (!empty($process_status_label)) {
                    $html .= '<span class="badge" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; font-size:11px; padding:4px 6px; border-radius:3px; display:inline-block; line-height:1.2;">' . htmlspecialchars($process_status_label) . '</span>';
                }
            } else {
                $html .= '<input type="checkbox"
                            class="return_chk"
                            data-id="' . $item->item_id . '"
                            data-b2b="' . $b2b_order_id . '"
                            data-b2b-item="' . $b2b_item_id . '"
                            data-publisher="' . $publisher_id . '"
                            id="return_chk_' . $item->item_id . '">';
            }

            $html .= '<input type="hidden"
                        id="b2b_order_id_' . $item->item_id . '"
                        value="' . $b2b_order_id . '">
                    <input type="hidden"
                        id="publisher_id_' . $item->item_id . '"
                        value="' . $publisher_id . '">
                    </div>

                    <div class="col-md-3 text-center">
                        <img src="' . $product_image_url . '" style="max-width:100px;">
                    </div>

                    <div class="col-md-7">
                        <p><b>' . $item->product_name . '</b></p>';

            // Display variants if available
            if (isset($item->product_variants) && $item->product_variants != '' && $item->product_type == 'conf-simple') {
                $product_variants = json_decode($item->product_variants);
                $variants = [];

                if (isset($product_variants) && $product_variants != '') {
                    foreach ($product_variants as $pk => $single_variant) {
                        foreach ($single_variant as $key => $val) {
                            $variants[] = $key . ': ' . $val;
                        }
                    }
                }

                if (!empty($variants)) {
                    $html .= '<p style="color: #666; font-size: 12px; margin: 5px 0;">' . implode(', ', $variants) . '</p>';
                }
            }

            $html .= '<p>Qty Ordered: ' . $item->qty_ordered . '</p>

                        <input type="number"
                            min="1"
                            max="' . $item->qty_ordered . '"
                            id="return_qty_' . $item->item_id . '"
                            class="form-control"
                            value="1"
                            disabled>
                    </div>

                </div>
            </div>';
        }

        echo json_encode(['status' => 1, 'html' => $html]);
    }




    protected function getMerchantsForRequest($order_id, $selected_item = [], $created_ids = [], $type = 'return')
    {
        $merchants = [];
        $b2b_order_ids = [];
        $b2b_item_ids = [];
        $direct_publisher_ids = [];

        // 1. Collect IDs directly from $selected_item
        if (!empty($selected_item) && is_array($selected_item)) {
            foreach ($selected_item as $item) {
                if (!empty($item['b2b_order_id'])) {
                    $b2b_order_ids[] = (int)$item['b2b_order_id'];
                }
                if (!empty($item['item_id'])) {
                    $b2b_item_ids[] = (int)$item['item_id'];
                }
                if (!empty($item['publisher_id'])) {
                    $direct_publisher_ids[] = (int)$item['publisher_id'];
                }
            }
        }

        // 2. Collect b2b_order_ids from newly created return/replacement orders
        if (!empty($created_ids) && is_array($created_ids)) {
            if ($type === 'return') {
                $q = $this->db->select('order_id')
                    ->from('sales_order_return')
                    ->where_in('return_order_id', $created_ids)
                    ->get();
                if ($q && is_object($q)) {
                    foreach ($q->result() as $cr) {
                        if (!empty($cr->order_id)) {
                            $b2b_order_ids[] = (int)$cr->order_id;
                        }
                    }
                }
            } elseif ($type === 'replacement') {
                $q = $this->db->select('order_id')
                    ->from('sales_order_replacement')
                    ->where_in('replacement_order_id', $created_ids)
                    ->get();
                if ($q && is_object($q)) {
                    foreach ($q->result() as $cr) {
                        if (!empty($cr->order_id)) {
                            $b2b_order_ids[] = (int)$cr->order_id;
                        }
                    }
                }
            }
        }

        // 3. Resolve b2b_order_id & publisher_id from b2b_order_items (join b2b_orders since publisher_id is in b2b_orders)
        if (!empty($b2b_item_ids)) {
            $item_q = $this->db->select('boi.order_id, bo.publisher_id')
                ->from('b2b_order_items boi')
                ->join('b2b_orders bo', 'bo.order_id = boi.order_id', 'left')
                ->where_in('boi.item_id', array_unique($b2b_item_ids))
                ->get();
            if ($item_q && is_object($item_q)) {
                foreach ($item_q->result() as $ir) {
                    if (!empty($ir->order_id)) {
                        $b2b_order_ids[] = (int)$ir->order_id;
                    }
                    if (!empty($ir->publisher_id)) {
                        $direct_publisher_ids[] = (int)$ir->publisher_id;
                    }
                }
            }

            // Also check sales_order_items in case item_id came from sales_order_items
            $so_q = $this->db->select('product_id, publisher_id')
                ->from('sales_order_items')
                ->where_in('item_id', array_unique($b2b_item_ids))
                ->get();
            if ($so_q && is_object($so_q)) {
                $so_items = $so_q->result();
                $p_ids = [];
                foreach ($so_items as $so) {
                    if (!empty($so->product_id)) {
                        $p_ids[] = (int)$so->product_id;
                    }
                    if (!empty($so->publisher_id)) {
                        $direct_publisher_ids[] = (int)$so->publisher_id;
                    }
                }
                if (!empty($p_ids)) {
                    $b2b_from_p_q = $this->db->select('bo.order_id, bo.publisher_id')
                        ->from('b2b_order_items boi')
                        ->join('b2b_orders bo', 'bo.order_id = boi.order_id', 'inner')
                        ->where('bo.webshop_order_id', $order_id)
                        ->where_in('boi.product_id', $p_ids)
                        ->get();
                    if ($b2b_from_p_q && is_object($b2b_from_p_q)) {
                        foreach ($b2b_from_p_q->result() as $bp) {
                            if (!empty($bp->order_id)) {
                                $b2b_order_ids[] = (int)$bp->order_id;
                            }
                            if (!empty($bp->publisher_id)) {
                                $direct_publisher_ids[] = (int)$bp->publisher_id;
                            }
                        }
                    }
                }
            }
        }

        $b2b_order_ids = array_values(array_unique(array_filter($b2b_order_ids)));

        // 4. Query b2b_orders for resolved b2b_order_ids
        if (!empty($b2b_order_ids)) {
            $bo_q = $this->db->select('order_id, publisher_id, order_barcode, increment_id')
                ->from('b2b_orders')
                ->where_in('order_id', $b2b_order_ids)
                ->get();
            if ($bo_q && is_object($bo_q)) {
                foreach ($bo_q->result_array() as $bo) {
                    if (!empty($bo['publisher_id'])) {
                        $pub_id = (int)$bo['publisher_id'];
                        $merchants[$pub_id] = [
                            'publisher_id'  => $pub_id,
                            'b2b_order_id'  => (int)$bo['order_id'],
                            'order_barcode' => !empty($bo['order_barcode']) ? $bo['order_barcode'] : (!empty($bo['increment_id']) ? $bo['increment_id'] : ''),
                            'increment_id'  => !empty($bo['increment_id']) ? $bo['increment_id'] : (!empty($bo['order_barcode']) ? $bo['order_barcode'] : '')
                        ];
                    }
                }
            }
        }

        // 5. If any direct_publisher_ids are still not in $merchants, query them by publisher_id & webshop_order_id
        $missing_pubs = array_diff(array_unique(array_filter($direct_publisher_ids)), array_keys($merchants));
        if (!empty($missing_pubs)) {
            $bo_q = $this->db->select('order_id, publisher_id, order_barcode, increment_id')
                ->from('b2b_orders')
                ->where('webshop_order_id', $order_id)
                ->where_in('publisher_id', $missing_pubs)
                ->get();
            if ($bo_q && is_object($bo_q)) {
                foreach ($bo_q->result_array() as $bo) {
                    $pub_id = (int)$bo['publisher_id'];
                    if (!isset($merchants[$pub_id])) {
                        $merchants[$pub_id] = [
                            'publisher_id'  => $pub_id,
                            'b2b_order_id'  => (int)$bo['order_id'],
                            'order_barcode' => !empty($bo['order_barcode']) ? $bo['order_barcode'] : (!empty($bo['increment_id']) ? $bo['increment_id'] : ''),
                            'increment_id'  => !empty($bo['increment_id']) ? $bo['increment_id'] : (!empty($bo['order_barcode']) ? $bo['order_barcode'] : '')
                        ];
                    }
                }
            }
        }

        // 6. Absolute fallback: only if no merchant could be determined from items or b2b orders,
        // fallback to b2b_orders for this webshop_order_id
        if (empty($merchants) && !empty($order_id)) {
            $fb_q = $this->db->select('order_id, publisher_id, order_barcode, increment_id')
                ->from('b2b_orders')
                ->where('webshop_order_id', $order_id)
                ->limit(1)
                ->get();
            if ($fb_q && is_object($fb_q)) {
                $fallback = $fb_q->row_array();
                if (!empty($fallback) && !empty($fallback['publisher_id'])) {
                    $pub_id = (int)$fallback['publisher_id'];
                    $merchants[$pub_id] = [
                        'publisher_id'  => $pub_id,
                        'b2b_order_id'  => (int)$fallback['order_id'],
                        'order_barcode' => !empty($fallback['order_barcode']) ? $fallback['order_barcode'] : (!empty($fallback['increment_id']) ? $fallback['increment_id'] : ''),
                        'increment_id'  => !empty($fallback['increment_id']) ? $fallback['increment_id'] : (!empty($fallback['order_barcode']) ? $fallback['order_barcode'] : '')
                    ];
                }
            }
        }

        return $merchants;
    }

    public function addReplacementRequest()
{
    $LoginID  = $_SESSION['LoginID'];
    $shopcode = SHOPCODE;
    $shop_id  = SHOP_ID;

    if (!isset($_POST['order_id']) || !isset($_POST['selected_item'])) {

        echo json_encode(array(
            'flag' => 0,
            'msg'  => 'Unable to post request.'
        ));
        exit;
    }

    $order_id      = $_POST['order_id'];
    $increment_id  = isset($_POST['increment_id']) ? $_POST['increment_id'] : '';
    $selected_item = $_POST['selected_item'];
    $flag          = 'replacement';

    
    $b2b_order_id = !empty($_POST['b2b_order_id'])
        ? $_POST['b2b_order_id']
        : 0;

    $postArr = array(
        'order_id'      => $order_id,
        'increment_id'  => $increment_id,
        'selected_item' => $selected_item,
        'flag'          => $flag,
        'customer_id'   => $LoginID,
        'b2b_order_id'  => $b2b_order_id
    );

    
    $response = OrdersRepository::replacement_order_request(
        $shopcode,
        $shop_id,
        $postArr
    );

    
    if (empty($response) || $response->statusCode != '200') {

        echo json_encode(array(
            'flag' => 0,
            'msg'  => !empty($response->message)
                        ? $response->message
                        : 'Unable to submit replacement request.'
        ));

        exit;
    }

    try {
        // Determine the exact merchant(s) for the replacement items
        $created_rep_ids = [];
        if (!empty($response->replacement_order_id)) {
            $created_rep_ids = is_array($response->replacement_order_id)
                ? $response->replacement_order_id
                : [$response->replacement_order_id];
        }

        $target_merchants = $this->getMerchantsForRequest($order_id, $selected_item, $created_rep_ids, 'replacement');

        if (!empty($target_merchants)) {
            foreach ($target_merchants as $merchant_id => $m_info) {
                $actual_b2b_order_id = (int)$m_info['b2b_order_id'];
                if ($actual_b2b_order_id > 0) {
                    $this->db->where('order_id', $actual_b2b_order_id)->update('b2b_orders', ['status' => 15, 'updated_at' => time()]);
                }

                $notification_data = array(
                    'type'           => 'replacement',
                    'subtype'        => 'new_replacement',
                    'recipient_type' => 'merchant',
                    'recipient_id'   => (int)$merchant_id,
                    'title'          => 'New Replacement Request',
                    'message'        => 'Shopper has submitted a replacement request order #' . $increment_id . '.',
                    'data'           => json_encode(array(
                        'order_id'     => $order_id,
                        'b2b_order_id' => $actual_b2b_order_id,
                        'increment_id' => $increment_id
                    )),
                    'is_read'        => 0,
                    'created_at'     => date('Y-m-d H:i:s')
                );

                $this->db->insert('notifications', $notification_data);

                if ($this->db->affected_rows() <= 0) {
                    log_message(
                        'error',
                        'Replacement notification insert failed: ' .
                        json_encode($this->db->error())
                    );
                }
            }
        } else {
            log_message(
                'error',
                'Replacement notification merchant not found. ' .
                'order_id=' . $order_id .
                ', b2b_order_id=' . $b2b_order_id
            );
        }
    } catch (\Throwable $e) {
        log_message('error', 'Replacement notification error: ' . $e->getMessage());
    }


    $redirect_to = base_url() . 'customer/my-orders';

    echo json_encode(array(
        'flag'        => 1,
        'msg'         => $response->message,
        'redirect_to' => $redirect_to
    ));

    exit;
}

    public function getOrders()
    {
        $data['PageTitle']= 'My Profile - Orders';
        $data['side_tab'] = 'my_orders';

        $this->resetPrintDetails();
        $this->template->load('myprofile/my_orders', $data);
    }



    public function returnOrderDetail()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'my_orders';
        $return_order_id=$this->uri->segment(4);

        if (empty($return_order_id)) {
			redirect('customer/my-orders');
			return;
		}

		$shopDataflag = GlobalRepository::get_fbc_users_shop()?->result;
		if (!empty($shopDataflag)) {
			$data['shop_flag'] = $shopDataflag->shop_flag;
			$data['country_code'] = $shopDataflag->country_code;
		} else {
			$data['shop_flag'] = '';
			$data['country_code'] = '';
		}

		$data['online_stripe_payment_refund'] = GlobalRepository::get_custom_variable($shopcode, $shop_id, 'online_stripe_payment_refund', true) ?? 'no';

		$response = OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$OrderData = $response->OrderData;

			$data['OrderData'] = $OrderData;
		} else {
			$data['OrderData'] = array();
		}

		$this->template->load('myprofile/return_order_detail', $data);
	}


    public function download_document($order_id)
{
    $this->load->model('CommonModel');
    $order = $this->CommonModel->get_document_by_order_id($order_id);

    if ($order && !empty($order->document_file))
    {
        $file_path = FCPATH . 'merchant/uploads/order_documents/' . $order->document_file;
        
        if (file_exists($file_path))
        {
            // Return success status and file URL/name
            echo json_encode([
                'status' => true,
                'file_url' => base_url('merchant/uploads/order_documents/' . $order->document_file)
            ]);
            return;
        }
    }

    // Return error status if file or record doesn't exist
    echo json_encode([ 'status' => false, 'message' => $this->lang->line('invoice_not_uploaded') ]);
}


    public function setTempPrintDetails()
    {
		$this->session->set_userdata('reason_for_return', $_POST['reason_for_return']);
        $this->session->set_userdata('refund_payment_mode', $_POST['refund_payment_mode'] ?? '');
        $this->session->set_userdata('bank_name', $_POST['bank_name']);
        $this->session->set_userdata('bank_branch', $_POST['bank_branch']);
        $this->session->set_userdata('bic_swift', $_POST['bic_swift']);
        $this->session->set_userdata('ifsc_iban', $_POST['ifsc_iban']);
        $this->session->set_userdata('bank_acc_no', $_POST['bank_acc_no']);

        echo "success";
        exit;
    }

    public function resetPrintDetails()
    {
        $this->session->unset_userdata('reason_for_return');
        $this->session->unset_userdata('refund_payment_mode');
        $this->session->unset_userdata('bank_name');
        $this->session->unset_userdata('bank_branch');
        $this->session->unset_userdata('bic_swift');
        $this->session->unset_userdata('ifsc_iban');
        $this->session->unset_userdata('bank_acc_no');
    }

    public function printReturnOrder()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $return_order_id=$this->uri->segment(3);

        if ($return_order_id!='') {
            $postPrintArr = array('return_order_id'=>$return_order_id);
            $printresponse= OrdersRepository::return_order_print($shopcode, $shop_id, $postPrintArr);

            $response= OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);

            $return_address_field='order_return_address';
            $ReturnApiResponse = GlobalRepository::get_custom_variable($shopcode, $shop_id, $return_address_field);
            if (!empty($ReturnApiResponse) && isset($ReturnApiResponse) && $ReturnApiResponse->statusCode=='200') {
                $ReturnValue=$ReturnApiResponse->custom_variable;
                $return_address=$ReturnValue->value;
                $data['returnAddress']=$return_address;
            } else {
                $return_address='';
                $data['returnAddress']=$return_address;
            }
            /*end return address*/

            if ($response->is_success=='true') {
				$data['OrderData']= $response->OrderData;
            } else {
                $data['OrderData'] = array();
            }

            $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);

            $this->template->load('myprofile/print_return_order', $data);
        } else {
            redirect('customer/my-orders');
        }
    }



    public function addReturnRequest()
{
    $LoginID  = $_SESSION['LoginID'];
    $shopcode = SHOPCODE;
    $shop_id  = SHOP_ID;

    if (isset($_POST['order_id']) && isset($_POST['selected_item'])) {

        $order_id      = $_POST['order_id'];
        $increment_id  = $_POST['increment_id'];
        $flag          = 'return';
        $selected_item = $_POST['selected_item'];

        $postArr = array(
            'order_id'      => $order_id,
            'increment_id'  => $increment_id,
            'selected_item' => $selected_item,
            'flag'          => $flag,
            'customer_id'   => $LoginID
        );

        $response = OrdersRepository::return_order_request(
            $shopcode,
            $shop_id,
            $postArr
        );

        if (!empty($response) && $response->statusCode == '200') {

            try {
                // Determine the exact merchant(s) for the return items
                $created_ret_ids = [];
                if (!empty($response->return_order_id)) {
                    $created_ret_ids = is_array($response->return_order_id)
                        ? $response->return_order_id
                        : [$response->return_order_id];
                }

                $target_merchants = $this->getMerchantsForRequest($order_id, $selected_item, $created_ret_ids, 'return');

                if (!empty($target_merchants)) {
                    foreach ($target_merchants as $merchant_id => $m_info) {
                        $actual_b2b_order_id = (int)$m_info['b2b_order_id'];
                        if ($actual_b2b_order_id > 0) {
                            $this->db->where('order_id', $actual_b2b_order_id)->update('b2b_orders', ['status' => 14, 'updated_at' => time()]);
                        }

                        $notification_data = array(
                            'type'           => 'return',
                            'recipient_type' => 'merchant',
                            'recipient_id'   => (int)$merchant_id,
                            'title'          => 'New Return Request',
                            'message'        => 'Shopper has submitted a return request order #' . $increment_id . '.',
                            'data'           => json_encode(array(
                                'order_id'     => $order_id,
                                'b2b_order_id' => $actual_b2b_order_id,
                                'increment_id' => $increment_id
                            )),
                            'is_read'        => 0,
                            'created_at'     => date('Y-m-d H:i:s')
                        );

                        $this->db->insert('notifications', $notification_data);
                    }
                } else {
                    log_message(
                        'error',
                        'Return notification merchant not found. order_id=' . $order_id
                    );
                }
            } catch (\Throwable $e) {
                log_message('error', 'Return notification error: ' . $e->getMessage());
            }

    
            $redirect_to = base_url() . 'customer/my-orders';

            echo json_encode(array(
                'flag'        => 1,
                'msg'         => $response->message,
                'redirect_to' => $redirect_to
            ));

            exit;
        }

        echo json_encode(array(
            'flag' => 0,
            'msg'  => !empty($response->message)
                        ? $response->message
                        : 'Unable to submit return request.'
        ));

        exit;
    }

    echo json_encode(array(
        'flag' => 0,
        'msg'  => 'Unable to post request.'
    ));

    exit;
}
    public function show_tracking_details()
    {

        if (isset($_POST['order_id'])) {
            $order_id=$_POST['order_id'];
            $postArr=array('order_id'=>$order_id);
            $response = OrdersRepository::tracking_details_request($postArr);
            // print_r($response);die();

            if (!empty($response) && isset($response) && $response->statusCode=='200') {
                $data['tracking_data'] = $response->tracking_data;
                //  print_r($response);die();

                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            } else {
                $data['tracking_data'] = $response->tracking_data;
                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            }

        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
            exit;
        }

    }



    public function confirmReturn()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['reason_for_return']) && (isset($_POST['item_qty']) &&  count($_POST['item_qty'])>0)) {
            $postArr=array('customer_id'=>$LoginID);

            foreach ($_POST as $postkey=>$post_field) {
                $postArr[$postkey]=$post_field;
            }

            $response = OrdersRepository::return_order_confirm($shopcode, $shop_id, $postArr);
            if (!empty($response)) {
                if ($response->statusCode=='200') {
					$redirect_to=base_url().'customer/my-orders/return-detail/'.$response->return_order_id;
                    echo json_encode(array('flag'=>1, 'msg'=> $response->message,'redirect_to'=>$redirect_to));
                    exit;
                }

				echo json_encode(array('flag'=>0, 'msg'=> $response->message));
				exit;
			}

			echo json_encode(array('flag'=>0, 'msg'=>'Unable to post request.'));
			exit;
		}

		echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
		exit;
	}

    public function cancelOrder()
    {

		if (!isset($_POST)) {
			echo json_encode(array('flag' => 0, 'msg' => "Please two enter all mandatory / compulsory fields."));
			exit;
		}

		if (empty($_POST['cancel_reason']) && empty($_POST['order_id'])) {
			echo json_encode(array('flag' => 0, 'msg' => "Please  one enter all mandatory / compulsory fields."));
			exit;
		}
		$reason_for_cancel = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';
		$order_id = $_POST['order_id'];

        $webshopname = 'India Mags';


        $shop_logo = SITE_LOGO;
		$data['webshop_details'] = CommonRepository::get_webshop_details();
		if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
            $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);

		}


		$site_logo = '<a href="' . base_url() . '" style="color:#1E7EC8;">
					<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
		$lang_code = '';
		if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
			$lang_code = $this->session->userdata('lcode');
		}
        if(!empty($this->session->userdata('CURRENCY_CODE'))){
            $CURRENCY_CODE=$this->session->userdata('CURRENCY_CODE');
        }
        else{
            $CURRENCY_CODE = 'IN';
        }
		$postArr = array('order_id' => $order_id, 'reason_for_cancel' => $reason_for_cancel, 'site_logo' => $site_logo, 'currency_code' => $CURRENCY_CODE, 'lang_code' => $lang_code);
		
        $response = OrdersRepository::cancel_order_request($postArr);
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$redirect_to = base_url() . 'customer/my-orders/';
			echo json_encode(array('flag' => 1, 'msg' => $response->message, 'redirect_to' => $redirect_to));
			exit;
		}

		echo json_encode(array('flag' => 0, 'msg' => $response->message));
		exit;
	}
}
