<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">



    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

        <h1 class="head-name">Manage Subscription</h1>

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
                        <th>SR No.</th>
                        <th>Merchant Name</th>
                        <th>Subscription Plan</th>
                        <th>Status</th>
                        <th>Purchase Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Action</th>
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
                            <td><?= date("d-m-Y", strtotime($order['created_at'] . " +1 year")); ?></td>

                            <td><?= number_format($order['amount'], 2); ?></td>
                              <td>
                                  <?php if($order['status'] == 'paid') { ?>
                                <a href="<?php echo base_url('subscription/download_invoice/' . $order['id']); ?>" target="_blank" class="btn btn-sm btn-primary yellow-button">Download Invoice</a>
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



<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#DataTables_Table_B2BOrders').DataTable({
            lengthMenu: [10, 20, 50, 100],
            pageLength: 20,
            ordering: true,
            searching: true,
            dom: 'lrtip', // f = search box
            language: {
                lengthMenu: "Show _MENU_ entries"
            }
        });
        // Custom search input
		$("#search_order").on("keyup", function () {
			table.search(this.value).draw();
		});
    });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>