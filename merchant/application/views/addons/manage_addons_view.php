<div class="main-content">
    <div class="container-fluid">
        <h3>Manage Addon</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SR NO.</th>
                                <th>IMAGE</th>
                                <th>CATEGORY NAME</th>
                                <th>Addon Service</th>
                                <th>STATUS</th>
                                <th>SERVICE ID</th>
                                <th>PRICE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($manage_addons)): ?>
                                <?php $sr = 1; foreach($manage_addons as $row): ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td>
                                        <?php if(!empty($row->image)): ?>
                                            <img src="<?php echo base_url('uploads/addons/' . $row->image); ?>" alt="Service Image" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row->category_name; ?></td>
                                    <td><?php echo $row->title; ?></td>
                                    <td>Active</td>
                                    <td><?php echo $row->service_id; ?></td>
                                    <td><?php echo $row->final_price; ?></td>
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