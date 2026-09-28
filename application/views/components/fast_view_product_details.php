<?php $lang = $this->session->userdata('site_lang'); ?>
    <div class="container-fluid">
        <div class="row">
            <!-- BEGIN CONTENT -->
            <div class="col-md-12">
                <div class="product-page">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="product-name">
                                <?php echo (($lang == "french" && isset($ProductData->lang_title) && $ProductData->lang_title != '') ? $ProductData->lang_title : $ProductData->name); ?>
                            </h1>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="product-image-section" id="product-image-section">
                                <?php $this->load->view('product/media_gallery') ?>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-8">
                            <ul class="list-group">
                                <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name) && $ProductData->publication_name !== 'Anu Test Developer Account') { ?>
                                    <li class="list-group-item"><b><?= lang('merchant') ?>:
                                        </b><?php echo $ProductData->publication_name; ?>
                                    </li>
                                <?php } ?>
                                <?php if($lang == "french") { ?>
                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) {
                                        if (isset($attr->attr_value_lang) && !empty($attr->attr_value_lang)) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name_lang ?>:
                                                </b><?php echo $attr->attr_value_lang ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php } else { ?>
                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) {
                                        if (isset($attr->attr_value) && !empty($attr->attr_value)) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name ?>:
                                                </b><?php echo $attr->attr_value ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php  } ?>
                                            
                                
                            </ul>
                            <form name="product-frm" id="quick-product-frm">
                                <input type="hidden" name="media_variant_id" id="media_variant_id" value="<?php echo  $ProductData->media_variant_id; ?>">

                                <input type="hidden" name="product_type" id="product_type" value="<?php echo  $ProductData->product_type; ?>">

                                <?php if ($ProductData->product_type == 'simple') { ?>
                                    <div class="table-responsive product-price">
                                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                                            <thead>
                                                <tr class="active">
                                                    <th><?= lang('price') ?> <?= lang('mur') ?></th>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <th><?= lang('offer_price') ?> <?= lang('mur') ?></th>
                                                    <?php } ?>
                                                    <!-- <th>Issues</th>
                                                    <th>Gift</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo number_format((float)$ProductData->webshop_price, 2); ?></td>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <td>
    <?php echo number_format((float)$ProductData->special_price, 2); ?>
    <?php echo $ProductData->off_percent_price; ?><?= lang('percent_off') ?>
</td>
                                                    <?php } ?>
                                                    <!-- <td><?php //echo $ProductData->sub_issues; ?> issues</td>
                                                    <td><?php //echo $ProductData->gift_master_name; ?> </td> -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                                <?php (new ProductDetails())->productStockVariant($ProductData, $CategoryIds); ?>

                                <?php
                                if (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == 1) { ?>
                                    <!-- To use when items OUT of stock (no variants - STARTS) -->
                                    <div class="notifywrap">
                                        <div class="notifyhd">
                                            <i class="fa fa-exclamation-circle fa-2x"></i> <span><?= lang('out_of_stock_label') ?></span>
                                        </div>
                                        <div class="notifyfrm">
                                            <p><?= lang('out_of_stock_message') ?></p>
                                            <div class="form-inline" id="keepnotified">
                                                <div class="form-group">
                                                    <label class="sr-only" for="notified-email"><?= lang('email_address_label') ?></label>
                                                    <input type="email" class="form-control" id="notified-email" placeholder="<?= lang('email_placeholder') ?>" value="<?php echo $_SESSION["EmailID"] ?? ""; ?>" onkeyup="ValidateEmail();">

                                                </div>

                                                <button type="button" id="notified-keep-inform" class="btn btn-primary" onclick=openNotifiedPopup(<?php echo $ProductData->id; ?>)><?= lang('keep_me_informed_btn') ?></button>
                                            </div>
                                            <span id="lblError" class="notified-error"></span>
                                        </div>
                                    </div>
                                    <!-- To use when items OUT of stock (no variants - ENDS) -->

                                <?php } ?>

                                <div class="subscribe-deli">
                                    <?php if (isset($ProductData->stock_status) && ($ProductData->stock_status == 'Instock')) { ?>
                                        <?php if ($restricted_access == "yes" && $customer_id == 0) { ?>
                                            <button type="button" class="btn btn-primary subscr-now pull-left as" <?php echo isset($ProductData->stock_status) &&
                                                                                                                        ($ProductData->stock_status == "Instock" &&
                                                                                                                            $ProductData->product_type == "simple")
                                                                                                                        ? ""
                                                                                                                        : "disabled"; ?> onclick="openRestrictedAccessPopup()" class="add-to-cart-btn">
                                                <?= lang('add_to_cart_btn') ?>
                                            </button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent"> <?= lang('more_details') ?></a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b><?= lang('expected_delivery') ?>: <?php echo $ProductData->estimate_delivery_time; ?> <?= lang('days') ?>.</b></p>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="button" id="add_to_cart" onclick="Quickaddtocart()" class="btn btn-primary subscr-now pull-left as"><?= lang('add_to_cart_btn') ?></button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent"><?= lang('more_details') ?></a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b><?= lang('expected_delivery') ?>: <?php echo $ProductData->estimate_delivery_time; ?> <?= lang('days') ?>.</b></p>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-primary subscr-now pull-left" id="add_to_cart" type="submit" disabled><?= lang('out_of_stock') ?></button>
                                    <?php } ?>
                                    <div id="addtocart-message" class="addtocart-message"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
    </div>