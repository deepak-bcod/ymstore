<?php 

$this->load->view('common/fbc-user/header'); ?>

<style>
    body{background-color: #ffffff;}
</style>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    

    <!-- Breadcrumbs if needed -->

    <?php //$this->load->view('seller/products/breadcrums'); ?>



    <div class="container py-4">

       <div class="float-right product-filter-div">
    <a href="<?= site_url('addons'); ?>" class="white-btn back-btn-line">
        <?= lang('back_to_list'); ?>
    </a>
</div>
        <?php if (!empty($service)) : ?>

            

            <h2 class="page-title"><?= lang(strtolower(str_replace(' ', '_', trim($service['category_name']))), $service['category_name']) ?></h2>

            

            <div class="row">

                <!-- Left: Image -->

               <div class="col-md-5 mb-4">
    <div class="service-card text-center p-3">
        <img  src="<?= !empty($service['image']) 
            ? BASE_URL2.'admin/public/uploads/addons/' . $service['image'] 
            : base_url('public/images/placeholder.png') ?>" 
            class="img-fluid mb-3" 
            alt="<?= htmlspecialchars($service['title']) ?>">
    </div>
</div>



                <!-- Right: Details -->

                <div class="col-md-7">

                    <?php
                        $site_lang = $this->session->userdata('site_lang');

                        $title = ($site_lang == 'french' && !empty($service['title_fr']))
                                ? $service['title_fr']
                                : $service['title'];

                        $description = ($site_lang == 'french' && !empty($service['description_fr']))
                                    ? $service['description_fr']
                                    : $service['description'];
                        ?>

                        <h4 class="mb-3"><?= $title; ?></h4>
                        <p><?= $description; ?></p>



                    <?php if(!empty($service['conditions'])): ?>

                        <h5 class="mt-4"><?= lang('conditions','Conditions:') ?></h5>

                        <ul>

                            <?php foreach($service['conditions'] as $cond): ?>

                                <li><?= $cond ?></li>

                            <?php endforeach; ?>

                        </ul>

                    <?php endif; ?>



                    <?php if(!empty($service['additional_info'])): ?>

                        <h5 class="mt-3"><?= lang('what_is_more','What is more:') ?></h5>

                        <p><?= $service['additional_info'] ?></p>

                    <?php endif; ?>



                    <div class="mt-4 mb-3">

                        <strong class="d-block mb-2">

                            <?= lang('price','Price') ?>: MUR <span id="price_display"><?= number_format($service['final_price'], 0) ?></span>

                        </strong>

                        

                        <form method="post" action="<?= site_url('addons/buy/'.$service['id']); ?>">

                            <div class="form-group">

                                <label for="qty"><?= lang('qty','Qty:') ?></label>

                                <input type="number" class="form-control form-section-control" id="qty" name="qty" value="1" min="1" required>

                            </div>



                            <!-- Hidden base price for JS calculation -->

                            <input type="hidden" id="base_price" value="<?= $service['final_price'] ?>">



                            <button type="submit" class="btn btn-primary btn-lg mt-2 addors-section"><?= lang('buy_now','Buy Now') ?></button>

                        </form>

                    </div>

                </div>

            </div>



        <?php else: ?>

            <div class="alert alert-info"><?= lang('service_not_found','Service details not found.') ?></div>

        <?php endif; ?>

    </div>



</main>

<?php $this->load->view('common/fbc-user/footer'); ?>


<script>

function buyService(serviceId) {

    var qty = document.getElementById('qty').value;

    window.location.href = "<?= site_url('addons/buy/'); ?>" + serviceId + "?qty=" + qty;

}

</script>



<script>

document.addEventListener("DOMContentLoaded", function() {

    let qtyInput = document.getElementById("qty");

    let basePrice = parseFloat(document.getElementById("base_price").value);

    let priceDisplay = document.getElementById("price_display");



    qtyInput.addEventListener("input", function() {

        let qty = parseInt(qtyInput.value) || 1;

        let total = basePrice * qty;

        priceDisplay.textContent = total.toLocaleString(); // adds commas like 10,000

    });

});

</script>

