<style>
    /* Fixes content overlapping under the fixed sidebar */
    .manage-addons-wrapper {
        margin-left: 260px; /* Adjust this to match your sidebar width */
        padding: 20px;
    }
    /* Style table headers to match your theme yellow background */
    .table thead th {
        background-color: #ffc107; /* Adjust to match your exact yellow theme color */
        color: #000;
        text-align: center;
        vertical-align: middle;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
    }
    @media (max-width: 768px) {
        .manage-addons-wrapper {
            margin-left: 0;
        }
    }
</style>

<div class="manage-addons-wrapper">
    <div class="main-content">
        <div class="container-fluid">
            <h3 class="mb-4">Manage Addon</h3>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>SR NO.</th>
                                    <th>MERCHANT NAME</th>
                                    <th>ADDON SERVICE</th>
                                    <th>STATUS</th>
                                    <th>DATE</th>
                                    <th>AMOUNT</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($manage_addons)): ?>
                                    <?php $sr = 1; foreach($manage_addons as $row): ?>
                                    <tr>
                                        <td><?php echo $sr++; ?></td>
                                        <td><?php echo isset($row->merchant_name) ? $row->merchant_name : ''; ?></td>
                                        <td><?php echo isset($row->addon_service_name) ? $row->addon_service_name : ''; ?></td>
                                        <td><?php echo isset($row->status) ? $row->status : ''; ?></td>
                                        <td><?php echo isset($row->date) ? $row->date : ''; ?></td>
                                        <td><?php echo isset($row->amount) ? $row->amount : ''; ?></td>
                                        <td>
                                            <!-- Update the URL/ID dynamically based on your route configuration -->
                                            <a href="<?php echo base_url('addons/edit/' . $row->id); ?>" class="text-warning font-weight-bold mr-2">Edit</a>
                                            <a href="<?php echo base_url('addons/delete/' . $row->id); ?>" class="text-danger font-weight-bold" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>