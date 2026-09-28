<?php $this->load->view('common/fbc-user/header'); ?>

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <?php $this->load->view('webshop/order/breadcrums'); ?>


    	<?php if ($current_tab == 'deliveries') { ?>
            <h1 class="head-name">Delivery Orders</h1>
        <?php } else { ?>
            <h1 class="head-name">Complete Orders</h1>
         <?php } ?> 


    <div class="accordion" id="b2cOrdersAccordion">
        <?php if (!empty($orders_data)): ?>
            <?php
            
            /*foreach ($orders_data as $index => $order_block):
                $parent = $order_block['parent'];
                $sub_orders = $order_block['sub_orders'];*/

                foreach ($orders_data as $index => $order_block):
                //$parent = $order_block['parent'];
                //$sub_orders = $order_block['sub_orders'];

                $parent = $order_block['parent'];
                $sub_orders = $order_block['sub_orders'];

                /* ================================
                TAB BASED FILTERING LOGIC
                ================================ */

                // Completed & post-completion sub-order statuses
                $completedAndPostStatuses = [2, 8, 9, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 28, 31, 32, 33];
                $pendingDeliveryStatuses = [0, 1, 4, 5, 6, 7, 10, 11, 12, 13, 25, 26, 27, 29, 30];

                // Check status of all sub-orders
                $allCompletedOrPost = !empty($sub_orders);
                $hasActiveSubOrder = false;
                $hasCompletedOrPostSubOrder = false;
                $hasPendingDelivery = false;

                foreach ($sub_orders as $b2b) {
                    if ($b2b->status == 3) {
                        continue; // ignore cancelled
                    }
                    $hasActiveSubOrder = true;
                    if (in_array((int)$b2b->status, $completedAndPostStatuses)) {
                        $hasCompletedOrPostSubOrder = true;
                    } else {
                        $allCompletedOrPost = false;
                    }

                    if (in_array((int)$b2b->status, $pendingDeliveryStatuses)) {
                        $hasPendingDelivery = true;
                    }
                }

                // Parent order check: if parent order status itself is completed or post-completed
                $isParentCompletedOrPost = in_array((int)$parent->status, $completedAndPostStatuses);

                // An order is complete (finished delivery lifecycle) if:
                // 1) All active sub-orders are completed/post-completed and none are pending delivery, OR
                // 2) The parent order is completed/post-completed and no sub-order is pending delivery
                $isOrderFinished = ($hasActiveSubOrder && $allCompletedOrPost && !$hasPendingDelivery) || 
                                   ($isParentCompletedOrPost && !$hasPendingDelivery);

                if ($current_tab === 'deliveries') {

                    // ✅ If order is finished (completed / rest of statuses) or has no active sub-orders → hide from Delivery tab (belongs in Complete tab)
                    if ($isOrderFinished || !$hasActiveSubOrder) {
                        continue;
                    }

                    // ✅ If ANY merchant sub-order is still pending delivery, keep order visible in Delivery tab!
                    // ✅ Show all merchant sub-orders in the accordion table so admin can see full progress

                } else { // ✅ COMPLETED TAB

                    // ✅ Once order complete for rest all the order status keep the order in the completed tab it should not go back to Delivery tab
                    if (!$isOrderFinished) {
                        continue;
                    }

                    // ✅ Show all merchant sub-orders in Complete tab
                }


                // ✅ ONLY SHOW THESE B2B STATUSES
                /*$allowedStatuses = [4, 5, 6, 7, 12, 13];

                // ✅ FILTER ONLY REQUIRED SUB-ORDERS
                $sub_orders = array_filter($sub_orders, function ($b2b) use ($allowedStatuses) {
                    return in_array((int)$b2b->status, $allowedStatuses);
                });

                // ✅ IF NO VALID SUB-ORDERS, SKIP THIS PARENT COMPLETELY
                if (empty($sub_orders)) {
                    continue;
                }*/

                $all_ready = !empty($sub_orders) && array_reduce($sub_orders, function ($carry, $b2b) {
                    return $carry && ($b2b->status == 12);
                }, true);

                // Parent-level delivery
                $parentDelivery = $this->db
                    ->where('webshop_order_id', $parent->order_id)
                    ->where('is_parent_level', 1)
                    ->order_by('id', 'DESC')
                    ->get('b2b_orders_delivery_details')
                    ->row();
                ?>
                <div class="card mb-2 shadow-sm">
                    <div class="card-header collapsed" data-toggle="collapse" data-target="#collapse-<?php echo $index; ?>">
                        <h3 class="card-title card-new mb-0">
                            <div>
                                Order #<?= $parent->increment_id; ?> -
                                <?= $parent->customer_firstname . ' ' . $parent->customer_lastname; ?>
                                <small class="text-muted">(<?= date('d-M-Y H:i', $parent->created_at); ?>)</small>
                            </div>
                        </h3>
                    </div>

                    <div id="collapse-<?php echo $index; ?>" class="collapse" data-parent="#b2cOrdersAccordion">
                        <div class="card-body">
                            <?php if (!empty($sub_orders)): ?>
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Merchant</th>
                                            <th>Delivery Status</th>
                                            <th>Delivery Details</th>
                                            <th>Images</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sub_orders as $b2b):
                                            $deliveryAttempts = $this->db
                                                ->where('order_id', $b2b->order_id)
                                                ->group_start()
                                                ->where('is_parent_level', 0)
                                                ->or_where('is_parent_level IS NULL', null, false)
                                                ->group_end()
                                                ->order_by('delivery_attempt_no', 'ASC')
                                                ->get('b2b_orders_delivery_details')
                                                ->result();

                                            $lastAttempt = !empty($deliveryAttempts) ? end($deliveryAttempts) : null;

                                            /*echo "<pre>";
                                                 print_r($lastAttempt);
                                                 exit;*/
                                            /*$deliveryAttempts = $this->db
                                            ->where('order_id', $b2b->order_id)
                                            ->order_by('delivery_attempt_no', 'ASC')
                                            ->get('b2b_orders_delivery_details')
                                            ->result();
                                        $lastAttempt = !empty($deliveryAttempts) ? end($deliveryAttempts) : null;*/


                                            $statusLabel = isset($lastAttempt) ? $this->CommonModel->getDeliveryStatusLabel($lastAttempt->delivery_status) : 'Not Assigned';
                                            if (isset($lastAttempt) && $lastAttempt->is_parent_level == 1) {
                                                $statusLabel .= " (Group Delivery)";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $b2b->increment_id; ?></td>
                                                <td><?= $b2b->customer_firstname . ' ' . $b2b->customer_lastname; ?></td>
                                                <td><?= $this->CommonModel->getWebShopNameByShopId($b2b->publisher_id); ?></td>
                                                <td>
                                                    <?php
                                                    if ($parentDelivery) {
                                                        // Show parent delivery status
                                                        echo $this->CommonModel->getDeliveryStatusLabel($parentDelivery->delivery_status) . " (Group Delivery)";
                                                    } else {
                                                        // Show individual delivery status
                                                        echo isset($lastAttempt) ? $this->CommonModel->getDeliveryStatusLabel($lastAttempt->delivery_status) : 'Not Assigned';
                                                    }
                                                    ?>
                                                </td>

                                                <td>
                                                    <?php
                                                    if ($parentDelivery) {
                                                        echo "Group Delivery";
                                                    } else {
                                                        if (!empty($deliveryAttempts)): ?>
                                                            <button class="btn btn-sm btn-info"
                                                                onclick="openDeliveryDetailsPopup('<?= $b2b->order_id ?>')">
                                                                View Delivery
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">No Attempts</span>
                                                        <?php endif;
                                                    } ?>
                                                </td>

                                                <td>
                                                    <a href="javascript:void(0);" class="link-orange" style="color:#f39c12;font-weight:600;" onclick="openOrderImagesModal('<?= !empty($parentDelivery) ? $parent->order_id : $b2b->order_id ?>', 'delivery')">View Image</a>
                                                </td>

                                                <td>
                                                    <?php
                                                    $attempt_no = $lastAttempt->delivery_attempt_no ?? 0;
                                                    $delivery_status = $lastAttempt->delivery_status ?? null;

                                                    $isDelivered = in_array($delivery_status, [8]) || in_array($b2b->status, [8, 24]);
                                                    $isCollected = in_array($delivery_status, [9]) || in_array($b2b->status, [9, 23, 28]);
                                                    $isPostCompleted = in_array((int)$b2b->status, [14, 15, 16, 17, 18, 19, 20, 21, 22, 31, 32, 33]);
                                                    $isComplete = ($b2b->status == 2);
                                                    $isFailed = in_array($delivery_status, [2, 4]);

                                                    if ($parentDelivery) {
                                                        $parentStatus = $parentDelivery->delivery_status ?? null;
                                                        if ($parentStatus == 8 || in_array($b2b->status, [8, 24])) {
                                                            echo '<span class="text-success font-weight-bold">Delivered</span>';
                                                        } elseif ($parentStatus == 9 || in_array($b2b->status, [9, 23, 28])) {
                                                            echo '<span class="text-success font-weight-bold">Collected</span>';
                                                        } elseif ($isPostCompleted) {
                                                            echo '<span class="text-info font-weight-bold">' . $this->CommonModel->getOrderStatusLabel($b2b->status) . '</span>';
                                                        } elseif ($isComplete) {
                                                            echo '<span class="text-success font-weight-bold">Complete</span>';
                                                        } else {
                                                            echo '<span class="text-muted">Group Delivery Assigned</span>';
                                                        }
                                                    } elseif ($isPostCompleted) {
                                                        echo '<span class="text-info font-weight-bold">' . $this->CommonModel->getOrderStatusLabel($b2b->status) . '</span>';
                                                    } elseif ($isComplete) {
                                                        echo '<span class="text-success font-weight-bold">Complete</span>';
                                                    } elseif ($isDelivered) {
                                                        echo '<span class="text-success font-weight-bold">Delivered</span>';
                                                    } elseif ($isCollected) {
                                                        echo '<span class="text-success font-weight-bold">Collected</span>';
                                                    } elseif ($delivery_status == 1 || $delivery_status == 3) {
                                                        // Ongoing delivery attempt
                                                        echo '<button class="btn btn-success btn-sm mr-1 mb-1" onclick="markAsDelivered(' . $b2b->order_id . ')">Mark as Delivered</button>';
                                                        echo '<button class="btn btn-danger btn-sm" onclick="MarkAsFailedPopup(' . $b2b->order_id . ',' . $attempt_no . ')">Mark as Failed</button>';
                                                    } elseif ($isFailed) {
                                                        // Failed attempts handling
                                                        if ($attempt_no < 2) {
                                                            $nextAttempt = $attempt_no + 1;
                                                            echo '<button class="btn btn-warning btn-sm" onclick="openAssignDeliveryPopup(' . $b2b->order_id . ',' . $nextAttempt . ')">Re-Attempt</button>';
                                                        } else {
                                                            // Both attempts failed — allow collect or mark as collected
                                                            if ($b2b->status == 13) {
                                                                echo '<button class="btn btn-success btn-sm" onclick="markAsCollected(' . $b2b->order_id . ')">Mark as Collected</button>';
                                                            } else {
                                                                echo '<button class="btn btn-info btn-sm" onclick="markAsCollected(' . $b2b->order_id . ')">Collect from Store</button>';
                                                            }
                                                        }
                                                    } else {
                                                        // Default handling — ready for first attempt
                                                        if ($b2b->status == 13) {
                                                            echo '<button class="btn btn-success btn-sm" onclick="markAsCollected(' . $b2b->order_id . ')">Mark as Collected</button>';
                                                        } elseif ($b2b->status == 12) {
                                                            echo '<button class="btn btn-primary btn-sm bg-blue" onclick="openAssignDeliveryPopup(' . $b2b->order_id . ',1)">Assign Delivery</button>';
                                                        } else {
                                                            echo '<span class="text-muted">Not Ready</span>';
                                                        }
                                                    }
                                                    ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php
                                $attempt_no = $parentDelivery->delivery_attempt_no ?? 0;
                                $lastStatus = $parentDelivery->delivery_status ?? null;
                                $isDelivered = ($lastStatus == 8);
                                $isCollected = ($lastStatus == 9); // 5 = Collected
                    
                                $individualProcessed = $this->db
                                    ->where('webshop_order_id', $parent->order_id)
                                    ->where('(is_parent_level IS NULL OR is_parent_level = 0)', null, false)
                                    ->where('webshop_order_id IS NULL', null, false)
                                    // ->where_in('delivery_status', [1, 2, 3, 4, 8]) // statuses that indicate an attempt or delivery
                                    ->count_all_results('b2b_orders_delivery_details') > 0;



                                ?>
                                <?php if(empty($deliveryAttempts)){ ?>
                                <div class="mt-3 p-3 border-top">
                                    <?php  if ($parentDelivery) { ?>
                                    <h6>Group Order Delivery Action</h6>
                                    <?php } ?>
                                    <?php if (!$parentDelivery): ?>
                                        <!-- No delivery assigned yet -->
                                         
                                    <?php if ($current_tab == 'deliveries' ) { ?>
                                        <button class="btn btn-success btn-sm bg-blue"
                                            onclick="openAssignParentDeliveryPopup('<?= $parent->order_id ?>', 1)">
                                            Assign Delivery to All Sub-Orders
                                        </button>
                                    <?php } ?>
                                    <?php elseif ($isCollected): ?>
                                        <span class="text-success">Collected from Warehouse</span>
                                        <button class="btn btn-info btn-sm ml-3"
                                            onclick="openParentDeliveryDetailsPopup('<?= $parent->order_id ?>')">View Group
                                            Delivery</button>
                                        <button class="btn btn-warning btn-sm ml-1"
                                            onclick="openOrderImagesModal('<?= $parent->order_id ?>', 'delivery')">View Image</button>

                                    <?php elseif ($isDelivered): ?>
                                        <span class="text-success">Delivered</span>
                                        <button class="btn btn-info btn-sm ml-3"
                                            onclick="openParentDeliveryDetailsPopup('<?= $parent->order_id ?>')">View Group
                                            Delivery</button>
                                        <button class="btn btn-warning btn-sm ml-1"
                                            onclick="openOrderImagesModal('<?= $parent->order_id ?>', 'delivery')">View Image</button>

                                    <?php else: ?>
                                        <?php
                                        // Switch based on last delivery status
                                        switch ($lastStatus) {
                                            case 1:
                                            case 3: // Shipped / Attempt 1
                                                ?>
                                                <button class="btn btn-success btn-sm mr-1"
                                                    onclick="markParentAsDelivered('<?= $parent->order_id ?>')">Mark as Delivered</button>
                                                <button class="btn btn-danger btn-sm mr-1"
                                                    onclick="openMarkFailedPopup('<?= $parent->order_id ?>', <?= $attempt_no ?>)">Mark as
                                                    Failed</button>
                                                <?php
                                                break;

                                            case 2:
                                            case 4: // Failed attempt 1/2
                                                if ($attempt_no < 2):
                                                    ?>
                                                    <button class="btn btn-warning btn-sm mr-1"
                                                        onclick="openAssignParentDeliveryPopup('<?= $parent->order_id ?>', <?= $attempt_no + 1 ?>)">Re-Attempt
                                                        Delivery</button>
                                                    <?php
                                                else:
                                                    ?>
                                                    <button class="btn btn-primary bg-blue btn-sm mr-1"
                                                        onclick="collectParentFromWarehouse('<?= $parent->order_id ?>')">Collect from
                                                        Store</button>
                                                    <?php
                                                endif;
                                                break;

                                            default:
                                                if ($attempt_no < 2):
                                                    ?>
                                                    <button class="btn btn-primary btn-sm bg-blue mr-1"
                                                        onclick="openAssignParentDeliveryPopup('<?= $parent->order_id ?>', <?= $attempt_no + 1 ?>)">Assign
                                                        Delivery</button>
                                                    <?php
                                                else:
                                                    ?>
                                                    <button class="btn btn-danger btn-sm mr-1"
                                                        onclick="collectParentFromWarehouse('<?= $parent->order_id ?>')">Collect from
                                                        Warehouse</button>
                                                    <?php
                                                endif;
                                                break;
                                        }
                                        ?>
                                        <button class="btn btn-info btn-sm"
                                            onclick="openParentDeliveryDetailsPopup('<?= $parent->order_id ?>')">View Group
                                            Delivery</button>
                                        <button class="btn btn-warning btn-sm ml-1"
                                            onclick="openOrderImagesModal('<?= $parent->order_id ?>', 'delivery')">View Image</button>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>

                            <?php else: ?>
                                <p>No B2B orders found for this parent order.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No Delivery Orders Found.</p>
        <?php endif; ?>
    </div>
</main>

<!-- Delivery Details Modal -->
<div class="modal fade" id="deliveryPopupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="deliveryPopupContent"></div>
    </div>
</div>

<!-- Assign Delivery Modal -->
<div class="modal fade" id="assignDeliveryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="assignDeliveryContent"></div>
    </div>
</div>

<!-- Mark Parent Failed Modal -->
<div class="modal fade" id="parentFailedModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content" id="parentFailedContent"></div>
    </div>
</div>

<!-- Order Images Modal (Pickup & Delivery) -->
<div class="modal fade" id="orderImagesModal" tabindex="-1" role="dialog" aria-labelledby="orderImagesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#fff; border-bottom:1px solid #e9ecef;">
        <h5 class="modal-title font-weight-bold" id="orderImagesModalLabel" style="font-size:18px; color:#333;">Order Images</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="orderImagesModalContent" style="min-height: 200px; padding: 20px;">
        <p class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading images...</p>
      </div>
      <div class="modal-footer" style="background:#fff; border-top:1px solid #e9ecef;">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
    function openDeliveryDetailsPopup(order_id) {
        $('#deliveryPopupModal').modal('show');
        $('#deliveryPopupContent').html('<p class="text-center">Loading...</p>');
        $.post(BASE_URL + "B2BOrdersController/getDeliveryAttemptsPopup", { order_id }, res => $('#deliveryPopupContent').html(res));
    }

    function openAssignDeliveryPopup(order_id, attempt_no) {
        $('#assignDeliveryModal').modal('show');
        $('#assignDeliveryContent').html('<p class="text-center">Loading...</p>');
        $.post(BASE_URL + "B2BOrdersController/AssignNewDeliveryPopup", { order_id, attempt_no }, res => $('#assignDeliveryContent').html(res));
    }

    // Parent-level delivery popup
    function openAssignParentDeliveryPopup(webshop_order_id, attempt_no) {
        $('#assignDeliveryModal').modal('show');
        $('#assignDeliveryContent').html('<p class="text-center">Loading...</p>');
        $.post(BASE_URL + "B2BOrdersController/AssignParentDeliveryPopup", { webshop_order_id, attempt_no }, function (res) {
            $('#assignDeliveryContent').html(res);

            // Form submit for assigning delivery (no confirm)
            /*$('#parentDeliveryForm').on('submit', function(e){
                e.preventDefault();
                const form = $(this);
                $.post(BASE_URL + 'B2BOrdersController/saveParentDeliveryAssignment', form.serialize(), function(res){
                    let data;
                    try { data = typeof res === "object" ? res : JSON.parse(res); } 
                    catch(err){ alert("Unexpected error. Try again."); return; }
    
                    alert(data.msg);
                    if(data.status == 200) location.reload();
                });
            });*/

            /*$('#parentDeliveryForm').off('submit').on('submit', function(e){
         e.preventDefault();
         const form = $(this);
         const submitBtn = form.find('button[type="submit"]');
     
         if (submitBtn.prop('disabled')) return; // 🛑 Already submitted once
     
         // Disable immediately
         submitBtn.prop('disabled', true);
     
         $.post(BASE_URL + 'B2BOrdersController/saveParentDeliveryAssignment', form.serialize(), function(res){
             let data;
             try { data = typeof res === "object" ? res : JSON.parse(res); } 
             catch(err){ alert("Unexpected error. Try again."); return; }
     
             if(data.status == 200) location.reload();
             else submitBtn.prop('disabled', false);
         });
     });*/

        });
    }
    function openParentDeliveryDetailsPopup(webshop_order_id) {
        $('#deliveryPopupModal').modal('show');
        $('#deliveryPopupContent').html('<p class="text-center">Loading...</p>');
        $.post(BASE_URL + "B2BOrdersController/getParentDeliveryDetailsPopup", { webshop_order_id }, res => $('#deliveryPopupContent').html(res));
    }

    /*function collectParentFromWarehouse(webshop_order_id) {
        swal({
            title: "Are you sure?",
            text: "Do you really want to collect this parent order from warehouse?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }, function(willCollect) {
            if (willCollect) {
                $.post(BASE_URL + "B2BOrdersController/collectParentFromWarehouse", { webshop_order_id }, function(response) {
                    var res = jQuery.parseJSON(response);
                    swal({title: res.status==200?"Success":"Error", icon: res.status==200?"success":"error", text: res.message, buttons: true}, function(){if(res.status==200) location.reload();});
                });
            }
        });
    }*/

    function collectParentFromWarehouse(webshop_order_id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to collect this parent order from warehouse?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, collect it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(BASE_URL + "B2BOrdersController/collectParentFromWarehouse", { webshop_order_id }, function (res) {
                    let data;
                    try { data = typeof res === "object" ? res : JSON.parse(res); }
                    catch (err) { Swal.fire("Error", "Unexpected error occurred. Try again.", "error"); return; }

                    Swal.fire({
                        title: data.status == 200 ? 'Success' : 'Error',
                        text: data.message,
                        icon: data.status == 200 ? 'success' : 'error'
                    }).then(() => { if (data.status == 200) location.reload(); });
                });
            }
        });
    }


    /*function markParentAsDelivered(webshop_order_id) {
        swal({
            title: "Are you sure?",
            text: "Do you really want to mark this parent order as delivered?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }, function(willDeliver) {
            if (willDeliver) {
                $.post(BASE_URL + "B2BOrdersController/markParentDelivered", { webshop_order_id }, function(response) {
                    var res = jQuery.parseJSON(response);
                    swal({title: res.status==200?"Success":"Error", icon: res.status==200?"success":"error", text: res.message, buttons: true}, function(){if(res.status==200) location.reload();});
                });
            }
        });
    }*/

    /*function markParentAsDelivered(webshop_order_id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to mark this Group order as delivered?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deliver it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(result.isConfirmed){
                $.post(BASE_URL + "B2BOrdersController/markParentDelivered", { webshop_order_id }, function(res){
                    let data;
                    try { data = typeof res === "object" ? res : JSON.parse(res); } 
                    catch(err){ Swal.fire("Error", "Unexpected error occurred. Try again.", "error"); return; }
    
                    Swal.fire({
                        title: data.status == 200 ? 'Success' : 'Error',
                        text: data.message,
                        icon: data.status == 200 ? 'success' : 'error'
                    }).then(() => { if(data.status == 200) location.reload(); });
                });
            }
        });
    }*/

    function markParentAsDelivered(webshop_order_id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to mark this Group order as delivered?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deliver it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + "B2BOrdersController/markParentDelivered",
                    type: "POST",
                    data: { webshop_order_id },
                    dataType: "json", // <- ensures jQuery parses JSON automatically
                    success: function (data) {
                        Swal.fire({
                            title: data.status == 200 ? 'Success' : 'Error',
                            text: data.message,
                            icon: data.status == 200 ? 'success' : 'error'
                        }).then(() => {
                            if (data.status == 200) location.reload();
                        });
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    }


    function openMarkFailedPopup(webshop_order_id, attempt_no) {
        $('#parentFailedModal').modal('show');
        $('#parentFailedContent').html('<p class="text-center">Loading...</p>');
        $.post(BASE_URL + "B2BOrdersController/markParentFailedPopup", { webshop_order_id, attempt_no }, function (response) {
            $('#parentFailedContent').html(response);
        });
    }

</script>

<?php $this->load->view('common/fbc-user/footer'); ?>