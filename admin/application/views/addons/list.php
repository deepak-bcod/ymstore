<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

<div class="main-inner">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3">
        
        <h1 class="head-name" style="margin-bottom: 0;">Addon Services</h1>

        <div class="product-filter-div d-flex align-items-center" style="gap: 15px;">
            <div class="search-container">
                <input type="text" id="addonSearch" placeholder="Search" 
                       style="padding: 0 20px 0 35px; border: 1px solid #ccc; border-radius: 20px;   box-sizing: border-box; height: 30px; font-size: 12px; font-weight: 500; width: 300px !important;background: url(../images/search-icon.png) no-repeat left 12px top 8px; outline: 0;">
            </div>
            <button class="purple-btn" onclick="window.location.href='<?= site_url('addons/create') ?>';">
                + Add New
            </button>
        </div>

    </div>
    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">



            <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 20px;">
         <?= $this->session->flashdata('success'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>


            <table class="table table-bordered table-style">

                <thead class="">

                    <tr>

                        <th>ID</th>

                        <th>Category</th>

                        <th>Title</th>

                       <!-- <th>Description</th>-->

                        <th>Final Price</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if(!empty($addons)): ?>

                        <?php foreach($addons as $a): ?>

                            <tr class="addon-row">

                                <td><?= $a->id; ?></td>

                                <td><?= $a->category_name; ?></td>

                                <td><?= $a->title; ?></td>

                               

                                <td><?= number_format($a->final_price, 2); ?></td>

                                <td><?= $a->status ? 'Active' : 'Inactive'; ?></td>

                                <td>

                                    <a href="<?= site_url('addons/edit/'.$a->id); ?>" class="btn btn-sm btn-primary">Edit</a>

                                    <a href="<?= site_url('addons/delete/'.$a->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this addon?')">Delete</a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7" class="text-center">No addons found</td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>



        </div>

    </div>

                    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>
<script>
document.getElementById('addonSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('.addon-row'); 

    items.forEach(item => {
        let text = item.textContent.toLowerCase();
        if (text.includes(filter)) {
            item.style.display = ""; // Show
        } else {
            item.style.display = "none"; // Hide
        }
    });
});
</script>

