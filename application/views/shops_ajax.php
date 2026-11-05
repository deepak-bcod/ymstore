<ul class="products-grid products-grid--max-5-col csmarketplace-vendors-grid">
<?php if(!empty($shops)) {
    foreach($shops as $shop){ 
        // Set image path
        $shop_image = !empty($shop->shop_image) && file_exists(FCPATH.'merchant/public/images/shop_images/'.$shop->shop_image) 
                      ? base_url('merchant/public/images/shop_images/'.$shop->shop_image) 
                      : 'http://via.placeholder.com/135x135';
    ?>
        <li class="item">
            <div class="shop_grid_list_wrap 123">
                <a href="<?php echo BASE_URL . 'shops/shop_details/' . $shop->id; ?>" class="product-image vendor-logo-image">
                    <img src="<?php echo $shop_image; ?>" alt="<?php echo $shop->vendor_name; ?>">
                </a>
                <h2 class="product-name csmarketplace-vendor-name">
                    <a href="<?php echo BASE_URL . 'shops/shop_details/' . $shop->id; ?>" title="Go to Shop"><?php echo $shop->vendor_name; ?></a>
                </h2>
                <p><?php echo $shop->publication_name; ?></p>
                <?php 
                    $shop_avg = !empty($shop->avg_rating) ? round((float)$shop->avg_rating, 1) : 0;
                    $shop_total = !empty($shop->total_reviews) ? (int)$shop->total_reviews : 0;
                    $rounded_half = round($shop_avg * 2) / 2;
                ?>
                <div class="shop-rating-stars" style="margin: 6px 0 4px; display: flex; align-items: center; justify-content: center; gap: 4px;" title="Rating: <?php echo $shop_avg; ?> / 5 (<?php echo $shop_total; ?> reviews)">
                    <div style="color: #ffbf00; font-size: 14px; letter-spacing: 2px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($rounded_half >= $i): ?>
                                <i class="fa fa-star"></i>
                            <?php elseif ($rounded_half == $i - 0.5): ?>
                                <i class="fa fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star" style="color: #ddd;"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span style="font-size: 12px; color: #666; font-weight: 500;">(<?php echo $shop_total; ?>)</span>
                </div>
            </div>
        </li>
<?php } } else { ?>
    <li><?= $this->lang->line('no_shops_found'); ?></li>
<?php } ?>
</ul>

<div class="page_limiter">
    <?php echo $pagination; ?>
</div>
<script>
$(document).ready(function(){
    $('.pagination a').each(function(){
        // convert ?page=2 to data-page="2"
        var url = $(this).attr('href');
        var page = url.split('page=')[1];
        $(this).attr('data-page', page);
        $(this).attr('href', 'javascript:void(0)');
    });
});
</script>