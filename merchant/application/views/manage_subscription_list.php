<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">



    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name"><?php echo $this->lang->line('subscription_list_title'); ?></h1>
    </div>

    <div class="content-main form-dashboard">

        <!-- <div class="d-flex justify-content-end align-items-center mb-2">
            <div class="w-25" style="margin-right:30px">
                <input type="text" id="search_order" class="form-control form-control-sm mb-0 me-3" placeholder="Search...">
            </div>
        </div> -->


        <div class="table-responsive text-center" style="overflow-x: auto; white-space: nowrap;">

            <table table class="table table-bordered table-style" id="DataTables_Table_B2BOrders">
                <thead>
                    <tr>
                        <th><?php echo $this->lang->line('sr_no'); ?></th>
                        <th><?php echo $this->lang->line('merchant_name'); ?></th>
                        <th><?php echo $this->lang->line('subscription_plan'); ?></th>
                        <th><?php echo $this->lang->line('status'); ?></th>
                        <th><?php echo $this->lang->line('purchase_date'); ?></th>
                        <th><?php echo $this->lang->line('end_date'); ?></th>
                        <th><?php echo $this->lang->line('amount'); ?></th>
                        <th><?php echo $this->lang->line('action'); ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1; foreach($subscription_orders as $order): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $order['publication_name']; ?></td>
                            <td><?= $order['plan_name']; ?></td>
                            <td><?= ucfirst($order['status']); ?></td>

                            <td><?= date("d-m-Y", strtotime($order['created_at'])); ?></td>

                            <!-- End date = created_at + 1 year (example) -->
                             <?php if($order['status'] == 'paid') { 
                                $expiry_date = date('d-m-Y', strtotime($order['created_at'] . ' +1 year'));
                            } ?>
                            <td><?= isset($expiry_date) ? $expiry_date : 'N/A'; ?></td>

                            <td><?= number_format($order['amount'], 2); ?></td>
                            <td>
                                  <?php if($order['status'] == 'paid') { ?>
                                <a href="<?php echo base_url('subscription/download_invoice/' . $order['id']); ?>" target="_blank" class="btn btn-sm btn-primary yellow-button"><?php echo lang('download_invoice'); ?></a>
                            <?php } else { ?>
                                N/A
                            <?php } ?>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        var table = $('#DataTables_Table_B2BOrders').DataTable({
            lengthMenu: [10, 20, 50, 100],
            pageLength: 20,
            ordering: true,
            searching: true,
            dom: 'lrtip', // f = search box
            language: {
                emptyTable: (typeof DT_LANG !== 'undefined' && DT_LANG.emptyTable) ? DT_LANG.emptyTable : "No data available in table",
                zeroRecords: (typeof DT_LANG !== 'undefined' && DT_LANG.zeroRecords) ? DT_LANG.zeroRecords : "No matching records found",
                processing: (typeof DT_LANG !== 'undefined' && DT_LANG.processing) ? DT_LANG.processing : "Processing...",
                info: (typeof DT_LANG !== 'undefined' && DT_LANG.info) ? DT_LANG.info : "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: (typeof DT_LANG !== 'undefined' && DT_LANG.infoEmpty) ? DT_LANG.infoEmpty : "Showing 0 to 0 of 0 entries",
                infoFiltered: (typeof DT_LANG !== 'undefined' && DT_LANG.infoFiltered) ? DT_LANG.infoFiltered : "(filtered from _MAX_ total entries)",
                lengthMenu: (typeof DT_LANG !== 'undefined' && DT_LANG.lengthMenu) ? DT_LANG.lengthMenu : "Show _MENU_ entries",
                search: "",
                searchPlaceholder: (typeof DT_LANG !== 'undefined' && DT_LANG.searchPlaceholder) ? DT_LANG.searchPlaceholder : "",
                paginate: {
                    next: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.next) ? DT_LANG.paginate.next : '<i class="fas fa-angle-right"></i>',
                    previous: (typeof DT_LANG !== 'undefined' && DT_LANG.paginate && DT_LANG.paginate.previous) ? DT_LANG.paginate.previous : '<i class="fas fa-angle-left"></i>'
                }
            }
        });
        // Custom search input
		$("#search_order").on("keyup", function () {
			table.search(this.value).draw();
		});
    });

</script>