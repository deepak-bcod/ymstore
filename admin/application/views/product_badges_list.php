<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">



    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

        <h1 class="head-name">Product Badge</h1>

    </div>

    <div class="content-main form-dashboard">

        <div class="d-flex justify-content-end align-items-center mb-2">
            <div class="w-25" style="margin-right:30px">
                <input type="text" id="search_order" class="form-control form-control-sm mb-0 me-3" placeholder="Search...">
            </div>
        </div>

        <div class="table-responsive text-center" style="overflow-x: auto; white-space: nowrap;">

            <table class="table table-bordered table-style" id="DataTables_Table_Badges">
                <thead>
                    <tr>
                        <th>SR No.</th>
                        <th>Merchant Name</th>
                        <th>Badge Category</th>
                        <th>Company Name</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1; foreach($product_badges as $pb): ?>
                        <tr>
                            <td><?= $i++; ?></td>

                            <td><?= $pb['merchant_name'] ?? 'N/A'; ?></td>

                            <td><?= $pb['category_name'] ?? 'N/A'; ?></td>

                            <td><?= $pb['company_name']; ?></td>

                            <td><?= $pb['mobile']; ?></td>

                            <td>
                                <?php if ($pb['status'] == 'approve'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif ($pb['status'] == 'reject'): ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>

                            <td><?= date("d-m-Y", $pb['created_at']); ?></td>
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
        $('#DataTables_Table_Badges').DataTable({
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