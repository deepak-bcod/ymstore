<?php
// echo "<pre>";
// print_r($OrderList);
// die;
?>

<?php
function getSubOrderStatusText($status) {
    $CI =& get_instance();
    $text = $CI->lang->line('unknown');
    $class = 'green-text completed'; // default green

    if ($status == 0) {
        $text = $CI->lang->line('to_be_processed');
        $class = 'green-text completed';
    } elseif (in_array($status, [1,10,11,12,4,5,6,7])) {
        $text = $CI->lang->line('processing');
        $class = 'green-text completed';
    } elseif (in_array($status, [2,8,9])) {
        $text = $CI->lang->line('completed');
        $class = 'green-text completed';
    } elseif ($status == 3) {
        $text = $CI->lang->line('cancelled');
        $class = 'red-text cancelled';
    } elseif ($status == 13) {
        $text = $CI->lang->line('collect_from_warehouse');
        $class = 'green-text completed';
    } elseif ($status == 14) {
        $text = $CI->lang->line('return_requested');
        $class = 'red-text cancelled';
    } elseif ($status == 15) {
        $text = $CI->lang->line('replacement_requested');
        $class = 'red-text cancelled';
    } elseif ($status == 22) {
        $text = $CI->lang->line('return_approved');
        $class = 'blue-text approved';
    }
    return ['text' => $text, 'class' => $class];
}
?>
<div class="col-md-9 col-sm-9 ">
    <div class="content-page">
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo $this->lang->line('your_orders'); ?></h1>
            </div>
        </div>
        <div class="wishlist-listing my-orders ">
            <?php
            if (isset($OrderList) &&  count($OrderList) > 0) {
                $order_total_shipping = 0;
                $order_total_voucher_amount = 0;

                foreach ($OrderList as $order) {
                    $order_items = count($order->order_items);
                    $order_total_shipping = $order->shipping_amount;
                    $order_total_voucher_amount = $order->voucher_amount; 
                    //echo "<pre>";print_r($order);die;
                    //echo "<pre> oreder Status => ";print_r($order->status);
                    ?>

                    <?php
                        // Determine parent order status based on sub-orders
                        // $parent_status = $this->lang->line('unknown');
                        // $parent_status_class = 'green-text completed'; // default green
                        

                        // if (isset($order->b2b_orders) && count($order->b2b_orders) > 0) {
                        //     $sub_status_codes = array_map(fn($b2b) => $b2b->status, $order->b2b_orders);

                        //     $all_tobe = count(array_filter($sub_status_codes, fn($s) => $s == 0)) == count($sub_status_codes);
                        //     $all_complete = count(array_filter($sub_status_codes, fn($s) => in_array($s, [2,8,9]))) == count($sub_status_codes);
                        //     $any_processing = count(array_filter($sub_status_codes, fn($s) => in_array($s, [1,4,5,6,7,10,11,12,13]))) > 0;
                        //     $any_cancelled = count(array_filter($sub_status_codes, fn($s) => $s == 3)) > 0;
                        //     $return = count(array_filter($sub_status_codes, fn($s) => $s == 14)) > 0;
                        //     $replacement = count(array_filter($sub_status_codes, fn($s) => $s == 15)) > 0;


                        //     if ($all_tobe) {
                        //         $parent_status = $this->lang->line('to_be_processed');
                        //         $parent_status_class = 'green-text completed';
                        //     } elseif ($any_cancelled) {
                        //         $parent_status = $this->lang->line('cancelled');
                        //         $parent_status_class = 'red-text cancelled';
                        //     } elseif ($all_complete) {
                        //         $parent_status = $this->lang->line('completed');
                        //         $parent_status_class = 'green-text completed';
                        //     } elseif ($any_processing) {
                        //         $parent_status = $this->lang->line('processing');
                        //         $parent_status_class = 'green-text completed';
                        //     } elseif ($return) {
                        //         $parent_status = $this->lang->line('return_requested');
                        //         $parent_status_class = 'red-text cancelled';
                        //     } elseif ($replacement) {
                        //         $parent_status = $this->lang->line('replacement_requested');
                        //         $parent_status_class = 'red-text cancelled';
                        //     }
                        // }

                        // Determine parent order status from main order status
                        $parent_status = $this->lang->line('unknown');
                        $parent_status_class = 'green-text completed'; // default green

                        switch ($order->status) {
                            case 0:
                                $parent_status = $this->lang->line('to_be_processed');
                                $parent_status_class = 'green-text completed';
                                break;

                            case 1:
                            case 4:
                            case 5:
                            case 6:
                            case 7:
                            case 10:
                            case 11:
                            case 12:
                            case 13:
                            case 16:
                                $parent_status = $this->lang->line('processing');
                                $parent_status_class = 'green-text completed';
                                break;

                            case 2:
                            case 8:
                            case 9:
                            case 14:
                            case 15:
                            case 17:
                            case 18:
                            case 19:
                            case 20:
                            case 21:
                            case 22:
                            case 23:
                                $parent_status = $this->lang->line('completed');
                                $parent_status_class = 'green-text completed';
                                break;

                            case 3:
                                $parent_status = $this->lang->line('cancelled');
                                $parent_status_class = 'red-text cancelled';
                                break;
                            /*
                            case 14:
                                $parent_status = $this->lang->line('completed');
                                $parent_status_class = 'red-text cancelled';
                                break;

                            case 15:
                                $parent_status = $this->lang->line('replacement_requested');
                                $parent_status_class = 'red-text cancelled';
                                break;

                            case 16:
                                $parent_status = $this->lang->line('processing');
                                $parent_status_class = 'green-text completed';
                                break;

                            case 17:
                                $parent_status = $this->lang->line('refund_paid');
                                $parent_status_class = 'green-text completed';
                            break;
                            
                            case 18:
                                $parent_status = $this->lang->line('replacement_approved');
                                $parent_status_class = 'blue-text approved';
                            break;
                            
                            case 19:
                                $parent_status = $this->lang->line('replaced');
                                $parent_status_class = 'green-text completed';
                            break;
                            
                            case 20:
                                $parent_status = $this->lang->line('return_rejected');
                                $parent_status_class = 'red-text cancelled';
                            break;
                            
                            case 21:
                                $parent_status = $this->lang->line('replacement_rejected');
                                $parent_status_class = 'red-text cancelled';
                                break;
                            */

                            

                        }

                    ?>


                    <div class="order-info panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-7">
                                    <p class="stats"><b><?php echo $this->lang->line('order_id'); ?>: <?php echo $order->increment_id; ?></b></p>
                                    <p class="stats"><b><?php echo $this->lang->line('order_date'); ?>: <?php echo date('d F Y', $order->created_at); ?></b></p>
                                </div>
                                <div class="col-md-5">
                                   <p class="stats on-right"><b><?php echo $this->lang->line('order_status'); ?>: 
                                        <span class="return-required <?php echo $parent_status_class; ?>">
                                            <?php echo $parent_status; ?>
                                        </span>
                                    </b></p>
                                </div>
                            </div>
                        </div>
                        <?php 
                            if (isset($order->order_items)  && count($order->order_items) > 0) {
                                //echo "<pre>";print_r($order);die; 
                        ?>


                            <?php
                            $total_active_item = 0;
                            $order_total_price = 0;
                            $order_total_tax = 0;
                            $order_total_discount = 0;
                            ?>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <?php
                                        $total_active_item = 0;
                                        $delivery_status = $order->delivery_status;
                                        $delivery_status_no = $order->delivery_status_no;
                                        
                                        if (isset($order->b2b_orders) && count($order->b2b_orders) > 0) {
                                            foreach ($order->b2b_orders as $b2b) {

                                                // Determine sub-order status text & CSS class
                                                
                                                $status_map = [
                                                    0 => ['text' => $this->lang->line('to_be_processed') ?: 'To Be Processed', 'class' => 'green-text completed'],
                                                    1 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    10 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    11 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    12 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    2 => ['text' => $this->lang->line('completed') ?: 'Completed', 'class' => 'green-text completed'],
                                                    8 => ['text' => $this->lang->line('completed') ?: 'Completed', 'class' => 'green-text completed'],
                                                    9 => ['text' => $this->lang->line('collected') ?: 'Collected', 'class' => 'green-text completed'],
                                                    3 => ['text' => $this->lang->line('cancelled') ?: 'Cancelled', 'class' => 'red-text cancelled'],
                                                    4 => ['text' => $this->lang->line('shipped') ?: 'Shipped', 'class' => 'green-text completed'],
                                                    5 => ['text' => $this->lang->line('shipped') ?: 'Shipped', 'class' => 'green-text completed'],
                                                    6 => ['text' => $this->lang->line('shipped') ?: 'Shipped', 'class' => 'green-text completed'],
                                                    13 => ['text' => $this->lang->line('collect_from_warehouse') ?: 'Collect from Warehouse', 'class' => 'green-text completed'],
                                                    7 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    14 => ['text' => $this->lang->line('return_requested') ?: 'Return Requested', 'class' => 'red-text cancelled'],
                                                    15 => ['text' => $this->lang->line('replacement_requested') ?: 'Replacement Requested', 'class' => 'red-text cancelled'],
                                                    16 => ['text' => $this->lang->line('processing') ?: 'Processing', 'class' => 'green-text completed'],
                                                    17 => ['text' => $this->lang->line('refund_paid') ?: 'Refund Paid', 'class' => 'green-text completed'],
                                                    18 => ['text' => $this->lang->line('replacement_approved') ?: 'Replacement Approved', 'class' => 'blue-text approved'],
                                                    19 => ['text' => $this->lang->line('replaced') ?: 'Replaced', 'class' => 'green-text completed'],
                                                    20 => ['text' => $this->lang->line('return_rejected') ?: 'Return Rejected', 'class' => 'red-text cancelled'],
                                                    21 => ['text' => $this->lang->line('replacement_rejected') ?: 'Replacement Rejected', 'class' => 'red-text cancelled'],
                                                    22 => ['text' => $this->lang->line('return_approved') ?: 'Return Approved', 'class' => 'blue-text approved'],
                                                   
                                                    23 => ['text' => $this->lang->line('collected') ?: 'Collected', 'class' => 'blue-text completed'],
                                                    24 => ['text' => $this->lang->line('delivered') ?: 'Delivered', 'class' => 'blue-text approved'],
                                                    25 => ['text' => $this->lang->line('mark_as_delivered') ?: 'Mark As Delivered', 'class' => 'blue-text approved'],
                                                    26 => ['text' => $this->lang->line('mark_as_failed') ?: 'Mark As Failed', 'class' => 'blue-text approved'],
                                                    27 => ['text' => $this->lang->line('re_attempt') ?: 'Re Attempt', 'class' => 'blue-text approved'],
                                                    28 => ['text' => $this->lang->line('collected_from_store') ?: 'Collected From Store', 'class' => 'blue-text approved'],
                                                    29 => ['text' => $this->lang->line('assign_delivery') ?: 'Assign Delivery', 'class' => 'blue-text approved'],
                                                    30 => ['text' => $this->lang->line('not_ready') ?: 'Not Ready', 'class' => 'blue-text approved']
                                                ];
                                                
                                                // If item status is not 0 â†’ use item status
                                                // echo "<pre>";print_r($b2b->status);exit;
                                                // $b2b_status_info = isset($status_map[$b2b->status]) ? $status_map[$b2b->status] : ['text'=>$this->lang->line('unknown'),'class'=>'green-text completed'];

                                                foreach ($b2b->sub_order_items as $sub_item) {

                                                    // Determine sub-item status per product/item individually
                                                    $final_status = 0;

                                                    // Check if this specific item has a replacement or return request
                                                    $has_rep = (!empty($sub_item->replacement_order_id) || in_array((int)($sub_item->status ?? 0), [15, 18, 19, 21]));
                                                    $has_ret = (!empty($sub_item->return_order_id) || in_array((int)($sub_item->status ?? 0), [14, 16, 17, 20, 22]));

                                                    $is_replacement = false;
                                                    $is_return = false;

                                                    if ($has_rep && $has_ret) {
                                                        // Both exist -> pick the latest request by timestamp or ID
                                                        $rep_time = !empty($sub_item->replacement_created_at) ? (int)$sub_item->replacement_created_at : 0;
                                                        $ret_time = !empty($sub_item->return_created_at) ? (int)$sub_item->return_created_at : 0;

                                                        if ($rep_time > $ret_time) {
                                                            $is_replacement = true;
                                                        } elseif ($ret_time > $rep_time) {
                                                            $is_return = true;
                                                        } else {
                                                            if (in_array((int)$sub_item->status, [15, 18, 19, 21])) {
                                                                $is_replacement = true;
                                                            } else {
                                                                $is_return = true;
                                                            }
                                                        }
                                                    } elseif ($has_rep) {
                                                        $is_replacement = true;
                                                    } elseif ($has_ret) {
                                                        $is_return = true;
                                                    }

                                                    if ($is_replacement) {
                                                        $rep_order_st = isset($sub_item->replacement_order_status) ? (int)$sub_item->replacement_order_status : 0;
                                                        $rep_item_st = isset($sub_item->replacement_status) ? (int)$sub_item->replacement_status : 0;
                                                        $sub_st = isset($sub_item->status) ? (int)$sub_item->status : 0;

                                                        if ($rep_order_st == 4 || $rep_item_st == 4 || $rep_item_st == 21 || $sub_st == 21) {
                                                            $final_status = 21; // Replacement Rejected
                                                        } elseif (in_array($rep_order_st, [3, 5, 6, 19]) || in_array($rep_item_st, [3, 5, 6, 19]) || $sub_st == 19) {
                                                            $final_status = 19; // Replaced
                                                        } elseif (in_array($rep_order_st, [1, 2, 18]) || in_array($rep_item_st, [1, 2, 18]) || $sub_st == 18) {
                                                            $final_status = 18; // Replacement Approved
                                                        } else {
                                                            $final_status = 15; // Replacement Requested
                                                        }
                                                    } elseif ($is_return) {
                                                        $ret_order_st = isset($sub_item->return_order_status) ? (int)$sub_item->return_order_status : 0;
                                                        $ref_st = isset($sub_item->refund_status) ? (int)$sub_item->refund_status : -1;
                                                        $ret_item_st = isset($sub_item->return_status) ? (int)$sub_item->return_status : 0;
                                                        $sub_st = isset($sub_item->status) ? (int)$sub_item->status : 0;

                                                        if (in_array($ret_order_st, [2, 5, 20]) || $ref_st == 2 || $ret_item_st == 2 || $sub_st == 20) {
                                                            $final_status = 20; // Return Rejected
                                                        } elseif ($ref_st == 1 || $ret_order_st == 4 || $ret_item_st == 4 || $sub_st == 17) {
                                                            $final_status = 17; // Refund Paid
                                                        } elseif (in_array($ret_order_st, [1, 3]) || $ret_item_st == 1 || $sub_st == 22) {
                                                            $final_status = 22; // Return Approved
                                                        } else {
                                                            $final_status = 14; // Return Requested
                                                        }
                                                    } else {
                                                        // Item has NO return or replacement request.
                                                        // DO NOT inherit post-order/return/replacement statuses from $b2b->status!
                                                        $return_replacement_statuses = [14, 15, 16, 17, 18, 19, 20, 21, 22];

                                                        if (isset($sub_item->status) && $sub_item->status > 0 && !in_array((int)$sub_item->status, $return_replacement_statuses)) {
                                                            $final_status = (int)$sub_item->status;
                                                        } elseif (isset($b2b->status) && !in_array((int)$b2b->status, $return_replacement_statuses)) {
                                                            if ($b2b->status == 5 && isset($b2b->b2b_delivery_status) && $b2b->b2b_delivery_status == 4 && isset($b2b->b2b_delivery_attempt_no) && $b2b->b2b_delivery_attempt_no == 2) {
                                                                $final_status = 13;
                                                            } else {
                                                                $final_status = (int)$b2b->status;
                                                            }
                                                        } else {
                                                            $final_status = (isset($order->status) && !in_array((int)$order->status, $return_replacement_statuses)) ? (int)$order->status : 2;
                                                        }
                                                    }

                                                    $b2b_status_info = isset($status_map[$final_status]) ? $status_map[$final_status] : ['text'=>$this->lang->line('unknown'),'class'=>'green-text completed'];

                                                    //$b2b_status_info = isset($status_map[$delivery_status_no]) ? $status_map[$delivery_status_no] : ['text'=>$this->lang->line('unknown'),'class'=>'green-text completed'];

                                                    //echo "<pre>";print_r($b2b_status_info);die;

                                                    // Get parent item info for image & price  
                                                    // Match by product_name, tracking index to handle duplicates
                                                    $parent_item = null;
                                                    if (isset($sub_item->product_name)) {
                                                        $matches = [];
                                                        foreach ($order->order_items as $idx => $oitem) {
                                                            if ($oitem->product_name == $sub_item->product_name) {
                                                                $matches[] = $oitem;
                                                            }
                                                        }
                                                        // Use sequential matching for duplicates
                                                        if (!isset($item_match_index)) {
                                                            $item_match_index = [];
                                                        }
                                                        $key = $sub_item->product_name;
                                                        if (!isset($item_match_index[$key])) {
                                                            $item_match_index[$key] = 0;
                                                        }
                                                        if (isset($matches[$item_match_index[$key]])) {
                                                            $parent_item = $matches[$item_match_index[$key]];
                                                            $item_match_index[$key]++;
                                                        } else if (!empty($matches)) {
                                                            $parent_item = $matches[0];
                                                        }
                                                    }

                                                    $base_image = isset($parent_item->base_image) && $parent_item->base_image != '' ? PRODUCT_THUMB_IMG . $parent_item->base_image : PRODUCT_DEFAULT_IMG;
                                                    $price = isset($parent_item->price) ? $parent_item->price : 0;
                                                    ?>
                                                    <div class="row margbot20 sub-order-block">
                                                        <div class="col-sm-4 col-md-4">
                                                            <div class="shpcart-img-wrap vv">
                                                                <img src="<?php echo $base_image; ?>" alt="<?php echo get_display_product_name($sub_item, $parent_item); ?>" class="img-responsive">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-8 col-md-8">
                                                            <div class="shpcart">
                                                                <h4><?php echo $this->lang->line('sub_order_id'); ?>: <?php echo (!empty($b2b->increment_id) ? $b2b->increment_id : $b2b->order_id); ?> | 
                                                                    <span class="return-required <?php echo $b2b_status_info['class']; ?>">
                                                                        <?php echo $b2b_status_info['text']; ?>
                                                                    </span>
                                                                </h4>
                                                                <p><?php echo get_display_product_name($sub_item, $parent_item); ?></p>
                                                                <?php 
                                                                // Display product variants - prioritize sub_item variants first, then parent_item
                                                                $variants = [];
                                                                
                                                                // Try to get variants from sub_item first (most accurate)
                                                                if (isset($sub_item->product_variants) && $sub_item->product_variants != '' && $sub_item->product_type == 'conf-simple') {
                                                                    $product_variants = json_decode($sub_item->product_variants);
                                                                    if (isset($product_variants) && $product_variants != '') {
                                                                        foreach ($product_variants as $pk => $single_variant) {
                                                                            foreach ($single_variant as $key => $val) {
                                                                                $variants[] = $key . ': ' . $val;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                // Fallback to parent_item if sub_item doesn't have variants
                                                                elseif (isset($parent_item->product_variants) && $parent_item->product_variants != '' && $parent_item->product_type == 'conf-simple') {
                                                                    $product_variants = json_decode($parent_item->product_variants);
                                                                    if (isset($product_variants) && $product_variants != '') {
                                                                        foreach ($product_variants as $pk => $single_variant) {
                                                                            foreach ($single_variant as $key => $val) {
                                                                                $variants[] = $key . ': ' . $val;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                if (!empty($variants)) {
                                                                    echo '<p style="color: #777; font-size: 12px; margin: 5px 0;">' . implode(', ', $variants) . '</p>';
                                                                }
                                                                ?>
                                                                <p><?php echo $this->lang->line('quantity'); ?>: <?php echo isset($sub_item->qty_ordered) ? $sub_item->qty_ordered : 1; ?></p>
                                                                <p><?php echo $this->lang->line('price'); ?>: <?php echo CURRENCY_TYPE . ' ' . number_format($price, 2); ?></p>
                                                             </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                    $total_active_item++;
                                                }

                                            }
                                        }
                                        ?>
                                    </div>

                                    <!-- Right-side order totals remain unchanged -->
                                    <div class="col-md-4">
                                        <div class="shpcart-ship-details">
                                            <div class="shpcart-ship-details-wrap">
                                                <p><?php echo $this->lang->line('price_items'); ?> (<?php echo count($order->order_items) ?> Items)<br>(<?php echo $this->lang->line('inclusive_taxes'); ?>)</p>
                                                <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->base_subtotal, 2); ?></p>
                                            </div>
                                            <div class="shpcart-ship-details-wrap">
                                                <p><?php echo $this->lang->line('taxes'); ?></p>
                                                <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->tax_amount, 2); ?></p>
                                            </div>
                                            <div class="shpcart-ship-details-wrap">
                                                <p><?php echo $this->lang->line('shipping_charges'); ?></p>
                                                <p>+ <?php echo CURRENCY_TYPE . ' ' . number_format(($order->ym_charge > 0 ? $order->ym_charge : $order->shipping_amount), 2); ?></p>
                                            </div>
                                            <?php if ($order->voucher_amount > 0) { ?>
                                                <div class="shpcart-ship-details-wrap">
                                                    <p><?php echo $this->lang->line('gift_card_amount'); ?></p>
                                                    <p>- <?php echo CURRENCY_TYPE . ' ' . number_format($order->voucher_amount, 2); ?></p>
                                                </div>
                                            <?php } ?>
                                            <!-- <div class="shpcart-ship-details-wrap subtotal">
                                                <p><?php echo $this->lang->line('sub_total'); ?></p>
                                                <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->subtotal, 2); ?></p>
                                            </div> -->
                                            <?php if ($order->discount_amount > 0) { ?>
                                                <div class="shpcart-ship-details-wrap">
                                                    <p><?php echo $this->lang->line('discount_amount'); ?></p>
                                                    <p>- <?php echo CURRENCY_TYPE . ' ' . number_format($order->discount_amount, 2); ?></p>
                                                </div>
                                            <?php } ?>
                                        
                                            
                                            <div class="shpcart-ship-details-wrap ordtotal">
                                                <p><b><?php echo $this->lang->line('order_total'); ?></b></p>
                                                <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->grand_total, 2); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($order->b2b_orders)): ?>
                                <div class="form-group text-end py-2 merchant-invoices-wrap" style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; padding: 10px 15px;">
                                    <?php foreach ($order->b2b_orders as $b2b_sub): 
                                        $m_display_name = !empty($b2b_sub->increment_id) ? $b2b_sub->increment_id : $b2b_sub->order_id;

                                        $btn_text = (count($order->b2b_orders) > 1)
                                            ? ($this->lang->line('invoice') ?: 'Invoice') . ' (' . $m_display_name . ')'
                                            : ($this->lang->line('merchant_invoice') ?: 'Merchant Invoice');
                                    ?>
                                        <button type="button" class="btn btn-primary" onclick="downloadInvoice('<?php echo $b2b_sub->order_id; ?>')" title="Download Invoice for <?php echo htmlspecialchars($m_display_name); ?>">
                                            <i class="fa fa-file-text-o mr-1"></i> <?php echo htmlspecialchars($btn_text); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($total_active_item <= 0) { ?>
                                <input type="hidden" name="active_items" id="active_items" value="<?php echo  $total_active_item; ?>">
                                <li>
                                    <p class="mb-5 pb-5 item-re-text"><?php echo $this->lang->line('items_are_returned'); ?></p>
                                </li>
                            <?php } ?>

                            <!-- cart-table-amount-total start -->
                            <?php
                            $order_sub_total = $order_total_price  - $order_total_discount + $order_total_shipping;
                            $order_total_amount = $order_sub_total -  $order_total_voucher_amount;
                            ?>

                            
                            <div class="panel-footer">
                                <!-- ACTION BUTTONS OF THE ORDER -->
                                <?php
                                if (isset($order->flag) && $order->flag == 'able_to_cancel') { ?>
                                    <!-- <button  name="cancel_order_btn" class="btn btn-primary" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order->order_id; ?>" value="<?php echo $order->order_id; ?>" data-target="#cancel-order-modal"><?php echo $this->lang->line('cancel_order'); ?></button> -->

                                <?php   } ?>

                              

                                <?php
                                if ($order->status == 5 || $order->status == 6) {
                                ?>
                                    <!-- <button type="button" class="blue-btn-order tracking_details_btn btn btn-primary" orderid='<?php echo $order->order_id; ?>'>Track Order</button> -->
                                    <?php if (THEMENAME != 'theme_zumbawear') { ?>
                                        <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
                                    <?php }
                                }
                                if (isset($order->invoice_file) && $order->invoice_file != '' && $order->invoice_self == 1) {
                                    ?>
                                    <a class="blue-btn-order download-invoice btn " href="<?php echo INVOICE_FILE . $order->invoice_file; ?>" target="_blank"><button type="submit" class="btn btn-primary"><?php echo $this->lang->line('download_invoice'); ?></button></a>
                                <?php
                                }
                                if ($order->status != 3) {
                                    $encoded_id = base64_encode($order->order_id);
                                    $encoded_id = urlencode($encoded_id);
                                ?>
                                    <a class="blue-btn-order download-invoice print-receipt-btn " href="<?php echo BASE_URL('receipt-order/print' . '/' . $encoded_id) ?>" target="_blank"> <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('download_receipt'); ?></button></a>
                                    <?php
                                }
                                

                                

                                

                                
                                if ($order->status == 5 || $order->status == 6) {
                                    if (THEMENAME == 'theme_zumbawear') { ?>
                                        <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
                                <?php }
                                }
                                ?>
                           
                             
                                <?php
                                if ($order->status != 0 && $order->status != 1 && $order->status != 10 && $order->status != 11 && $order->status != 12 && $order->status != 3 && $order->status != 4 && $order->status != 5 && $order->status != 7 && $order->status != 16) {
                                ?>
                                    <a href="<?php echo base_url('order_resolution/create/' . $order->order_id); ?>"
                                       class="blue-btn-order"
                                       id="res-btn-<?php echo $order->order_id; ?>"
                                       style="background: #e6a817; color: #fff; margin-left: 5px;"
                                       title="Order Resolution (Refund / Replacement)">
                                        <i class="fa fa-handshake-o"></i> <?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?>
                                    </a>
                                <?php 
                                     }
                                ?>
                                 <?php if (isset($order->flag) && $order->flag == 'able_to_return'): ?>
                                    <!-- <button type="button"
                                            class="blue-btn-order "
                                            id="ret-btn-<?php echo $order->order_id; ?>"
                                            onclick="openReturnPopup('<?php echo $order->order_id; ?>','<?php echo $order->increment_id; ?>')">
                                        <?php echo $this->lang->line('return_order'); ?> / <?php echo $this->lang->line('replacement_order'); ?>
                                    </button> -->
                                  


                                <?php endif; ?>

                            </div>

                        <?php } ?>
                    </div><!-- order-info -->
            <?php }
            } else {
                echo "<div class='empty-record'>" . $this->lang->line('no_orders_found') . "</div>";
            } ?>

        </div><!-- order-listing -->
        <!--</div><!-- ROW -->
    </div><!-- content-page -->
</div>

<div class="col-md-12 col-lg-12">
    <div class="paging-main">
        <ul class="pagination myorder-pagination">
            <?php
            if (isset($links)) {
                echo $links;
            } ?></ul>
    </div>
</div>

<div id="returnPopup" class="modal fade" role="dialog">
    <div class="modal-dialog">  

        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo $this->lang->line('return_order_items'); ?></h4>
                <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="returnItemsBox">
                <!-- AJAX Item List Load Here -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="submitReturnRequest"><?php echo $this->lang->line('return'); ?></button>
                <button type="button" class="btn btn-success" id="submitReplacementRequest"><?php echo $this->lang->line('replacement'); ?></button>
            </div>
        </div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function downloadInvoice(orderId) {
    if (!orderId) {
        alert('No Order ID found.');
        return;
    }

    $.ajax({
        url: '<?php echo site_url("MyOrdersController/download_document/"); ?>' + orderId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                // Fixed syntax: directly update window.location.href to trigger download
                window.location.href = response.file_url;
            } else {
                // Shows the pop-up alert message and stays on the same page
                alert(response.message);
            }
        },
        error: function() {
            alert('An error occurred while processing your request.');
        }
    });
}
</script>

