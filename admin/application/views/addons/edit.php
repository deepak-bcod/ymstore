<?php $this->load->view('common/fbc-user/header'); ?> 

<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

<div class="main-inner">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

        <h1 class="head-name">Edit Addon Service</h1>

        <div class="float-right product-filter-div">

            <a href="<?= site_url('addons'); ?>" class="white-btn back-btn-line">Back to List</a>

        </div>

    </div>



    <div class="content-main form-dashboard">

        <div> 



            <?php if($this->session->flashdata('success')): ?>

                <p style="color:green"><?= $this->session->flashdata('success'); ?></p>

            <?php endif; ?>



            <form method="post" enctype="multipart/form-data">

                <div class="form-group">

                    <label>Category:</label>

                    <select name="category_id" class="form-control" required>

                        <option value="">-- Select Category --</option>

                        <?php foreach($categories as $c): ?>

                            <option value="<?= $c->id; ?>" <?= $addon->category_id == $c->id ? 'selected' : ''; ?>>

                                <?= $c->name; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>



                <div class="form-group">

                    <label>Title:</label>

                    <input type="text" name="title" class="form-control" value="<?= $addon->title; ?>" required>

                </div>

                <!-- New French Title Field -->
                <div class="form-group">
                    <label>Title (French):</label>
                    <input type="text" name="title_fr" class="form-control" 
                        value="<?= $addon->title_fr ?? ''; ?>">
                </div>


                <div class="form-group">

                    <label>Description:</label>

                    <textarea name="description" id="description" class="form-control"><?= set_value('description', $addon->description); ?></textarea>

                </div>

                <!-- New French Description Field -->
                <div class="form-group">
                    <label>Description (French):</label>
                    <textarea name="description_fr" id="description_fr" class="form-control">
                        <?= set_value('description_fr', $addon->description_fr ?? ''); ?>
                    </textarea>
                </div>


                <div class="form-group">

                    <label>Price (MUR):</label>

                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $addon->price; ?>" required>

                </div>

                <div class="form-group">
                    <label>YM VAT (%):</label>
                    <input type="number" step="0.01" name="vat_percent" id="vat_percent" 
                        class="form-control" value="<?= $addon->vat_percent; ?>">
                </div>

                <div class="form-group">
                    <label>Final Price (MUR):</label>
                    <input type="text" name="final_price" id="final_price" 
                        class="form-control" value="<?= $addon->final_price; ?>" readonly>
                </div>




                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" class="form-control">
                        <option value="1" <?= $addon->status == 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?= $addon->status == 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

            <div class="form-group">
                    <label>Image upload:</label>
                    <input type="file" name="addon_image" class="form-control">
                    <small class="text-muted" style="font-size: 12px; color: #6c757d;">
                        (Image size should be less than 100KB, Diamentions should be width:626 
                        height:626 size: 25.3kb)
                    </small>
                    <?php if(!empty($addon->image)): ?>
                        <div style="margin-top: 10px;">
                            <img src="<?= base_url('public/uploads/addons/'.$addon->image); ?>" alt="Current Image" style="width: 120px; border: 1px solid #ddd; padding: 5px; background: #fff;">
                        </div>
                    <?php endif; ?>
            </div>



                <div class="form-group">

                    <button type="submit" class="purple-btn small-btn bg-blue ml-0">Update</button>

                </div>

            </form>



        </div>

    </div>

</div>

</main>


<script>
    
    CKEDITOR.replace('description', {
        versionCheck: false
    });

    CKEDITOR.replace('description_fr', {
        versionCheck: false
    });
    

    function calculateWebshopPrice(price, percent) {
        price = parseFloat(price) || 0;
        percent = parseFloat(percent) || 0;

        let tax_amount = 0;
        let webshop_price = price;

        if (price > 0 && percent > 0) {
            tax_amount = (percent / 100) * price;
            webshop_price = price + tax_amount;
        }

        return { tax_amount, webshop_price };
    }

    // On VAT or Price change, recalculate
    document.getElementById('vat_percent').addEventListener('blur', function() {
        let price = document.querySelector('input[name="price"]').value;
        let vat = this.value;

        let result = calculateWebshopPrice(price, vat);

        document.getElementById('final_price').value = result.webshop_price.toFixed(2);
    });

    // Optional: recalc when price changes too
    document.querySelector('input[name="price"]').addEventListener('blur', function() {
        let vat = document.getElementById('vat_percent').value;
        let price = this.value;

        let result = calculateWebshopPrice(price, vat);

        document.getElementById('final_price').value = result.webshop_price.toFixed(2);
    });
</script>


<?php $this->load->view('common/fbc-user/footer'); ?>

