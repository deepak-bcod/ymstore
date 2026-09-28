<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

      <ul class="nav nav-pills">

            <!-- <li><a href="<?= base_url('merchants') ?>">Merchants</a></li> -->

           <li class="active"><a><?php echo lang('edit_merchant'); ?></a></li>


      </ul>

      <div class="main-inner min-height-480">

            <form id="publisherForm" method="POST" action="<?php echo base_url('PublisherController/submitMerchant'); ?>" enctype="multipart/form-data">

                  <input type="hidden" name="publisher_id" value="<?= $publisher->id ?>">

                  <input type="hidden" name="passwordCheck" value="">

                  <input type="hidden" name="type" value="1">
                  <div class="variant-common-block variant-list">

                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

					<h1 class="head-name pad-bottom-20"><?php echo lang('merchants'); ?></h1>


					<div class="float-right">

						<?php if ($_SESSION["LoginID"]) { ?> <!-- <button class="purple-btn" data-toggle="modal" data-target="#change_pass_modal" type="button">Change Password</button> -->

							<button class="purple-btn" type="button" onclick="OpenPasswordChangePopup();"><?php echo lang('change_password'); ?></button>


						<?php } ?>

					</div>



				</div>

                        <div class="form-group row">

                              <label for="variant_name" class="col-sm-2 col-form-label font-500"><?php echo lang('email_label'); ?></label>


                              <div class="col-sm-3">

                                    <?php echo form_input([
                                    'class' => 'form-control', 
                                    'placeholder' => lang('enter_email_placeholder'), 
                                    'id' => "email", 
                                    'name' => 'email', 
                                    'value' => "$publisher->email", 
                                    'required' => 'true', 
                                    'readonly' => 'true'
                                    ]); ?>


                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <!-- <label for="" class="col-sm-2 col-form-label font-500">Edit Password</label>

                        <div class="col-sm-6">

                        <label class="checkbox">

						<input type="checkbox" id="passwordCheck"  name="passwordCheck" value="check" >  <span class="checked"></span>

						</label>

                        </div> -->

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <!-- <label for="" class="col-sm-2 col-form-label font-500"></label>

                        <div class="col-sm-3">

                       <?php echo form_password([
                              'class' => 'form-control',
                              'placeholder' => lang('enter_password_placeholder'),
                              'id' => "password",
                              'name' => 'password',
                              'value' => "$publisher->password",
                              'required' => 'true',
                              'disabled' => 'true'
                        ]); ?>


                        </div> -->

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('merchant_name'); ?>*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => lang('enter_publication_name'), 'id' => "publication_name", 'name' => 'publication_name', 'value' => "$publisher->publication_name", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('vendor_name'); ?>*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => lang('enter_vendor_name'), 'id' => "vendor_name", 'name' => 'vendor_name', 'value' => "$publisher->vendor_name", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        

                        <!-- New Shop Image Field -->

                       <div class="form-group row">
    <label for="shop_image" class="col-sm-2 col-form-label font-500"><?php echo lang('shop_image'); ?></label>
    
    <div class="col-sm-6"> <div class="d-flex align-items-center">
            <input type="file" name="shop_image" id="shop_image" class="form-control" style="width: auto;">
            
            <span class="text-muted ml-3" style="font-size: 13px; white-space: nowrap;">
                  <?php echo lang('logo_upload_instruction'); ?>
            </span>
        </div>

        <?php if(!empty($publisher->shop_image)) { ?>
            <div class="mt-2">
                <img src="<?= base_url('public/images/shop_images/'.$publisher->shop_image) ?>" 
                     alt="<?php echo lang('shop_image'); ?>" 
                     style="max-width:150px; border-radius: 4px;">
            </div>
        <?php } ?>
    </div>
</div>


                        <div class="form-group row">

                              <label for="merchant_cat" class="col-sm-2 col-form-label font-500"><?php echo lang('business_category'); ?></label>

                              <div class="col-sm-3">

                                    <select name="merchant_cat" id="merchant_cat" class="form-control" required>

                                          <option value=""><?php echo lang('select_business_category'); ?></option>

                                          <option value="Apps - Software" 

                                          <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Apps - Software") ? 'selected' : ''; ?>>

                                          <?php echo lang('apps_software'); ?>

                                          </option>

                                          <option value="Arts, Crafts, And Sewing" 

                                          <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Arts, Crafts, And Sewing") ? 'selected' : ''; ?>>

                                          <?php echo lang('arts_crafts_sewing'); ?>

                                          </option>

                                          <option value="Auto Accessories" 

                                          <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto Accessories") ? 'selected' : ''; ?>>

                                          <?php echo lang('auto_accessories'); ?>

                                          </option>

                                          <option value="Auto and Parts" 

                                          <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto and Parts") ? 'selected' : ''; ?>>

                                          <?php echo lang('auto_and_parts'); ?>

                                          </option>

                                          <option value="Baby Supplies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Baby Supplies") ? 'selected' : ''; ?>><?php echo lang('baby_supplies'); ?></option>

                                          <option value="Beauty and Cosmetics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beauty and Cosmetics") ? 'selected' : ''; ?>><?php echo lang('beauty_cosmetics'); ?></option>

                                          <option value="Beverages" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beverages") ? 'selected' : ''; ?>><?php echo lang('beverages'); ?></option>

                                          <option value="Books" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Books") ? 'selected' : ''; ?>><?php echo lang('books'); ?></option>

                                          <option value="Computers" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers") ? 'selected' : ''; ?>><?php echo lang('computers'); ?></option>

                                          <option value="Computers Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers Accessories") ? 'selected' : ''; ?>><?php echo lang('computers_accessories'); ?></option>

                                          <option value="Consumer Electronics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Consumer Electronics") ? 'selected' : ''; ?>><?php echo lang('consumer_electronics'); ?></option>

                                          <option value="Electronics - Audio and Video" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Audio and Video") ? 'selected' : ''; ?>><?php echo lang('electronics_audio_video'); ?></option>

                                          <option value="Electronics - Network - Wireless" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Network - Wireless") ? 'selected' : ''; ?>><?php echo lang('electronics_network_wireless'); ?></option>

                                          <option value="Electronics - Wearables" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Wearables") ? 'selected' : ''; ?>><?php echo lang('electronics_wearables'); ?></option>

                                          <option value="Fashion Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Fashion Accessories") ? 'selected' : ''; ?>><?php echo lang('fashion_accessories'); ?></option>

                                          <option value="Food and Groceries" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Food and Groceries") ? 'selected' : ''; ?>><?php echo lang('food_groceries'); ?></option>

                                          <option value="Furnitures" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Furnitures") ? 'selected' : ''; ?>><?php echo lang('furnitures'); ?></option>

                                          <option value="Games" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Games") ? 'selected' : ''; ?>><?php echo lang('games'); ?></option>

                                          <option value="Garden" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Garden") ? 'selected' : ''; ?>><?php echo lang('garden'); ?></option>

                                          <option value="Health" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Health") ? 'selected' : ''; ?>><?php echo lang('health'); ?></option>

                                          <option value="Home Appliances" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Appliances") ? 'selected' : ''; ?>><?php echo lang('home_appliances'); ?></option>

                                          <option value="Home Deco" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Deco") ? 'selected' : ''; ?>><?php echo lang('home_deco'); ?></option>

                                          <option value="Household Essentials" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Household Essentials") ? 'selected' : ''; ?>><?php echo lang('household_essentials'); ?></option>

                                          <option value="Jewellery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Jewellery") ? 'selected' : ''; ?>><?php echo lang('jewellery'); ?></option>

                                          <option value="Kids Clothing" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Clothing") ? 'selected' : ''; ?>><?php echo lang('kids_clothing'); ?></option>

                                          <option value="Kids Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Products") ? 'selected' : ''; ?>><?php echo lang('kids_products'); ?></option>

                                          <option value="Kitchen Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kitchen Equipment") ? 'selected' : ''; ?>><?php echo lang('kitchen_equipment'); ?></option>

                                          <option value="Luggage - Bags" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Luggage - Bags") ? 'selected' : ''; ?>><?php echo lang('luggage_bags'); ?></option>

                                          <option value="Media" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Media") ? 'selected' : ''; ?>><?php echo lang('media'); ?></option>

                                          <option value="Medical" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Medical") ? 'selected' : ''; ?>><?php echo lang('medical'); ?></option>

                                          <option value="Men's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Men's Fashion") ? 'selected' : ''; ?>><?php echo lang('mens_fashion'); ?></option>

                                          <option value="Mobile Phones" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Mobile Phones") ? 'selected' : ''; ?>><?php echo lang('mobile_phones'); ?></option>

                                          <option value="Office Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Equipment") ? 'selected' : ''; ?>><?php echo lang('office_equipment'); ?></option>

                                          <option value="Office Stationery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Stationery") ? 'selected' : ''; ?>><?php echo lang('office_stationery'); ?></option>

                                          <option value="Outdoor Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Outdoor Equipment") ? 'selected' : ''; ?>><?php echo lang('outdoor_equipment'); ?></option>

                                          <option value="Personal Care" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Personal Care") ? 'selected' : ''; ?>><?php echo lang('personal_care'); ?></option>

                                          <option value="Pet Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Pet Products") ? 'selected' : ''; ?>><?php echo lang('pet_products'); ?></option>

                                          <option value="Shoes" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Shoes") ? 'selected' : ''; ?>><?php echo lang('shoes'); ?></option>

                                          <option value="Sports and Fitness" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Sports and Fitness") ? 'selected' : ''; ?>><?php echo lang('sports_fitness'); ?></option>

                                          <option value="Tools and Home Improvement" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Tools and Home Improvement") ? 'selected' : ''; ?>><?php echo lang('tools_home_improvement'); ?></option>

                                          <option value="Toys and Hobbies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Toys and Hobbies") ? 'selected' : ''; ?>><?php echo lang('toys_hobbies'); ?></option>

                                          <option value="Travel Gear" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Travel Gear") ? 'selected' : ''; ?>><?php echo lang('travel_gear'); ?></option>

                                          <option value="Virtual" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Virtual") ? 'selected' : ''; ?>><?php echo lang('virtual'); ?></option>

                                          <option value="Women's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Women's Fashion") ? 'selected' : ''; ?>><?php echo lang('womens_fashion'); ?></option>

                                    </select>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                        <label for="shipment_type" class="col-sm-2 col-form-label font-500"><?php echo lang('shipment_type'); ?></label>
                        <div class="col-sm-3">
                              <?php if (!empty($publisher->shipment_type)) : ?>
                                    <!-- Show readonly style like normal input -->
                                    <input type="text" class="form-control" value="<?= ($publisher->shipment_type == '1') ? lang('own_delivery') : lang('ym_delivery'); ?>" readonly>
                                    <input type="hidden" name="shipment_type" value="<?= $publisher->shipment_type; ?>">
                                    <small class="text-muted"><?php echo lang('shipment_type_notice'); ?></small>
                              <?php else : ?>
                                    <!-- First time selection -->
                                    <select name="shipment_type" id="shipment_type" class="form-control" required>
                                    <option value=""><?php echo lang('select_shipment_type'); ?></option>
                                    <option value="1"><?php echo lang('own_delivery'); ?></option>
                                    <option value="2"><?php echo lang('ym_delivery'); ?></option>
                                    </select>
                              <?php endif; ?>
                        </div>
                        </div><!-- form-group -->

                        <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('commission_percent'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input([
                                    'class' => 'form-control', 
                                    'type' => 'number', 
                                    'placeholder' => lang('enter_commission'), 
                                    'id' => "commision_percent", 
                                    'name' => 'commision_percent', 
                                    'value' => "4", 
                                    'required' => 'true', 
                                    'readonly' => 'true'
                              ]); ?>
                        </div>
                        </div><!-- form-group -->

                        <!-- New VAT Section -->

                        <div class="form-group row">

                        <label for="vat_status" class="col-sm-2 col-form-label font-500"><?php echo lang('vat_status'); ?></label>
                        <div class="col-sm-3">
                              <select name="vat_status" id="vat_status" class="form-control" required>
                                    <option value=""><?php echo lang('select_vat_status'); ?></option>
                                    <option value="registered" <?= ($publisher->vat_status == 'registered') ? 'selected' : '' ?>><?php echo lang('registered'); ?></option>
                                    <option value="exempted" <?= ($publisher->vat_status == 'exempted') ? 'selected' : '' ?>><?php echo lang('exempted'); ?></option>
                              </select>
                        </div>

                        </div><!-- form-group -->


                  <div id="vat_details" style="display: none;">

                        <div class="form-group row">

                              <label for="vat_no" class="col-sm-2 col-form-label font-500"><?php echo lang('vat_no'); ?></label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'text',

                                    'placeholder' => lang('enter_vat_number'),

                                    'id' => "vat_no",

                                    'name' => 'vat_no',

                                    'value' => "$publisher->vat_no"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="vat_percent" class="col-sm-2 col-form-label font-500"><?php echo lang('vat_percent'); ?></label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'number',

                                    'step' => '0.01',

                                    'placeholder' => lang('enter_vat_percentage'),

                                    'id' => "default_vat_percentage",

                                    'name' => 'default_vat_percentage',

                                    'value' => "$publisher->default_vat_percentage"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->

                  </div>



                      <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('brn_number'); ?></label>

                        <div class="col-sm-3">

                              <?php echo form_input(['class' => 'form-control', 'placeholder' => lang('enter_brn_number'), 'id' => "brn_no", 'name' => 'brn_no', 'value' => "$publisher->brn_no", 'required' => 'true']); ?>

                        </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('phone_no'); ?></label>

                              <div class="col-sm-3">
                                    <input 
                                          type="tel" 
                                          id="phone_no" 
                                          name="phone_no" 
                                          class="form-control" 
                                          placeholder="Enter phone number" 
                                          value="<?php echo htmlspecialchars($publisher->phone_no); ?>" 
                                          pattern="[0-9]{8}" 
                                          maxlength="8"
                                          required
                                    >
                                    <input type="hidden" id="full_phone" name="full_phone">
                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('landline_no'); ?></label>

                              <div class="col-sm-3">
                                    <input 
                                          type="tel" 
                                          id="landline_no" 
                                          name="landline_no" 
                                          class="form-control" 
                                          placeholder="Enter Landline Number" 
                                          value="<?php echo htmlspecialchars($publisher->landline_no); ?>"
                                          pattern="[0-9]{7}" 
                                          maxlength="7"
                                    >
                                    <input type="hidden" id="full_landline_no" name="full_landline_no">
                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('description'); ?>*</label>

                        <div class="col-sm-3">

                              <?php echo form_textarea(['class' => 'form-control', 'placeholder' => lang('enter_description'), 'name' => 'description', 'value' => "$publisher->description", 'required' => 'true']); ?>

                        </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('company_name'); ?>*</label>

                        <div class="col-sm-3">

                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_company_name'), 'id' => "company_name", 'name' => 'company_name', 'value' => "$publisher->company_name", 'required' => 'true']); ?>

                        </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('location'); ?>*</label>

                        <div class="col-sm-3">

                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_location'), 'id' => "location", 'name' => 'location', 'value' => "$publisher->location", 'required' => 'true']); ?>

                        </div>

                        </div><!-- form-group -->


                        <div class="form-group row">

                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('company_address'); ?></label>

                        <div class="col-sm-3">

                              <?php echo form_textarea(['class' => 'form-control production-section', 'placeholder' => lang('enter_company_address'), 'name' => 'company_address', 'value' => "$publisher->company_address", 'required' => 'true']); ?>

                        </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                        <label for="state" class="col-sm-2 col-form-label font-500"><?php echo lang('state'); ?></label>

                        <div class="col-sm-3">

                              <select name="state" id="state" class="form-control" required>

                                    <option value=""><?php echo lang('select_state'); ?></option>

                                    <?php foreach ($state as $s): ?>

                                    <option value="<?php echo $s['id']; ?>" <?php echo (!empty($publisher->state) && $publisher->state == $s['id']) ? 'selected' : ''; ?>>

                                          <?php echo $s['state_name']; ?>

                                    </option>

                                    <?php endforeach; ?>

                              </select>

                        </div>

                        </div><!-- form-group -->

                        <div class="form-group row">
                              <label for="city" class="col-sm-2 col-form-label font-500">
                                    <?php echo lang('city'); ?>
                              </label>

                              <div class="col-sm-3">
                                    <input type="text" id="city" name="city" class="form-control" placeholder="<?php echo lang('enter_city'); ?>" value="<?php echo !empty($publisher->city) ? htmlspecialchars($publisher->city) : ''; ?>" required>
                              </div>
                        </div>


                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('zipcode'); ?></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'maxlength' => "12", 'placeholder' => lang('enter_zipcode'), 'id' => "zipcode", 'name' => 'zipcode', 'value' => "$publisher->zipcode", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                        <div class="col-sm-3">

                              <?php  //$status = $publisher->status; ?>

                              <input type="hidden" name="status" value="1">

                        </div>

                        </div><!-- form-group -->

                        <div class="download-discard-small pos-ab-bottom">

                        <button class="white-btn" type="button" data-dismiss="modal" onclick="window.location.href='<?= base_url('DashboardController/index') ?>';"><?php echo lang('discard'); ?></button>

                        <button type="submit" class="download-btn"><?php echo lang('update'); ?></button>

                        </div><!-- download-discard-small -->


                  </div><!-- -common-block -->

                  <h1 class="head-name pad-bottom-20"><?php echo lang('bank_details'); ?></h1>
                  <div class="form-group row bank-text">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('bank_name'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_bank_name'), 'name' => 'bank_name', 'required' => 'true', 'value' => $publisher_payment_details->bank_name ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('bank_branch_no'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_bank_branch_no'), 'name' => 'bank_branch_number','required' => 'true', 'value' => $publisher_payment_details->bank_branch_number ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('bank_swift_code'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_bank_swift_code'), 'name' => 'beneficiary_ifsc_code','required' => 'true', 'value' => $publisher_payment_details->beneficiary_ifsc_code ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('beneficiary_acc_no'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_beneficiary_acc_no'), 'name' => 'beneficiary_acc_no','required' => 'true', 'value' => $publisher_payment_details->beneficiary_acc_no ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('beneficiary_name'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_beneficiary_name'), 'name' => 'beneficiary_name','required' => 'true', 'value' => $publisher_payment_details->beneficiary_name ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('bank_address'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_bank_address'), 'name' => 'bank_address','required' => 'true', 'value' => $publisher_payment_details->bank_address ?? '']); ?>
                        </div>
                  </div>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500"><?php echo lang('iban'); ?></label>
                        <div class="col-sm-3">
                              <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => lang('enter_iban'), 'name' => 'iban','required' => 'true', 'value' => $publisher_payment_details->iban ?? '']); ?>
                        </div>
                  </div>

                      

                  <div class="div">
                        <h1 class="head-name pad-bottom-20"><?php echo lang('my_documents'); ?></h1>

                        <div class="form-group form-setion-new">
                              <label for=""><?php echo lang('delivery_policy'); ?></label>
                              <input type="file" id="delivery_policy" name="delivery_policy" class="form-control">

                              <?php if (!empty($publisher->delivery_policy)) : ?>
                                    <?php 
                                    $ext = strtolower(pathinfo($publisher->delivery_policy, PATHINFO_EXTENSION));
                                    $fileUrl = rtrim(BASE_URL2, '/') . '/uploads/delivery_policy/' . rawurlencode($publisher->delivery_policy);
                                    $img_ext = ['jpg','jpeg','png','gif'];
                                    ?>
                                    <?php if (in_array($ext, $img_ext)) : ?>
                                    <img src="<?= $fileUrl ?>" alt="<?php echo lang('delivery_policy'); ?>" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                    <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw"></i> <?= htmlspecialchars($publisher->delivery_policy) ?>
                                    </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                              <label for=""><?php echo lang('return_policy'); ?></label>
                              <input type="file" id="return_policy" name="return_policy" class="form-control">

                              <?php if (!empty($publisher->return_policy)) : ?>
                                    <?php 
                                    $ext = strtolower(pathinfo($publisher->return_policy, PATHINFO_EXTENSION));
                                    $fileUrl = rtrim(BASE_URL2, '/') . '/uploads/return_policy/' . rawurlencode($publisher->return_policy);
                                    $img_ext = ['jpg','jpeg','png','gif'];
                                    ?>
                                    <?php if (in_array($ext, $img_ext)) : ?>
                                    <img src="<?= $fileUrl ?>" alt="<?php echo lang('return_policy'); ?>" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                    <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw"></i> <?= htmlspecialchars($publisher->return_policy) ?>
                                    </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                              <label for=""><?php echo lang('refund_policy'); ?></label>
                              <input type="file" id="refund_policy" name="refund_policy" class="form-control">

                              <?php if (!empty($publisher->refund_policy)) : ?>
                                    <?php 
                                    $ext = strtolower(pathinfo($publisher->refund_policy, PATHINFO_EXTENSION));
                                    $fileUrl = rtrim(BASE_URL2, '/') . '/uploads/refund_policy/' . rawurlencode($publisher->refund_policy);
                                    $img_ext = ['jpg','jpeg','png','gif'];
                                    ?>
                                    <?php if (in_array($ext, $img_ext)) : ?>
                                    <img src="<?= $fileUrl ?>" alt="<?php echo lang('refund_policy'); ?>" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                    <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw"></i> <?= htmlspecialchars($publisher->refund_policy) ?>
                                    </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                            <label for="banner_img">
                                <?php echo lang('banner_image'); ?> <?php if (empty($publisher->banner_img)) : ?><span class="text-danger">*</span><?php endif; ?>
                            </label>

                            <input type="file" id="banner_img" name="banner_img" class="form-control" <?php echo empty($publisher->banner_img) ? 'required' : ''; ?>>

                            <small class="form-text text-muted" style="margin-top: 5px;">
                                <?php echo lang('banner_upload_instruction'); ?>
                            </small>

                            <?php if (!empty($publisher->banner_img)) : ?>
                                <?php 
                                    $fileUrl = rtrim(BASE_URL2, '/') . '/uploads/banner_img/' . rawurlencode($publisher->banner_img);
                                ?>
                                <img src="<?= $fileUrl ?>" alt="<?php echo lang('banner_image'); ?>" style="margin-top:10px; max-width:150px; display: block;">
                            <?php endif; ?>
                        </div>

                  </div>


            </div><!-- add new tab -->

      </div>

      </div>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/css/intlTelInput.min.css">
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- jQuery Validate Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"></script>
<script src="<?php echo SKIN_JS; ?>publisher.js"></script>

<script>

      document.addEventListener("DOMContentLoaded", function() {

            const vatStatus = document.getElementById("vat_status");

            const vatDetails = document.getElementById("vat_details");



            function toggleVatFields() {

                  if (vatStatus.value === "registered") {

                        vatDetails.style.display = "block";

                  } else {

                        vatDetails.style.display = "none";

                  }

            }



            vatStatus.addEventListener("change", toggleVatFields);



            // Trigger on page load if already selected

            toggleVatFields();

      });

</script>
<script>
      $(document).ready(function() {
            // ✅ Initialize with Mauritius as the default country
            const phoneInput = document.querySelector("#phone_no");
            const itiPhone = window.intlTelInput(phoneInput, {
                  initialCountry: "mu", // Mauritius 🇲🇺
                  nationalMode: false,
                  formatOnDisplay: true,
                  separateDialCode: true,
                  utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"
            });

            const landlineInput = document.querySelector("#landline_no");
            const itiLandline = window.intlTelInput(landlineInput, {
                  initialCountry: "mu", // Mauritius 🇲🇺
                  nationalMode: false,
                  formatOnDisplay: true,
                  separateDialCode: true,
                  utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"
            });

            // ✅ Before form submit, store full international numbers in hidden inputs
            // $('form').on('submit', function (e) {
            //       e.preventDefault();

            //       const form = $(this);
            //       const formData = new FormData(this);

            //       $.ajax({
            //             url: form.attr('action'),
            //             type: form.attr('method'),
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             dataType: 'json', // 👈 tells jQuery to expect JSON
            //             success: function (response) {
            //                   if (response.flag === 1) {
            //                         // alert(response.msg);
            //                         window.location.href = response.url; // redirect
            //                   } else {
            //                         alert(response.msg || "Something went wrong");
            //                   }
            //             },
            //             error: function (xhr) {
            //                   alert("Error: " + xhr.responseText);
            //             }
            //       });
            // });
      });
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const input = document.querySelector('.production-section');

  if (!input) return;

  input.addEventListener('input', function () {
    let cursorPos = this.selectionStart;

    let words = this.value.toLowerCase().split(' ');

    words = words.map(word => {
      if (word.length === 0) return '';
      return word.charAt(0).toUpperCase() + word.slice(1);
    });

    this.value = words.join(' ');

    this.setSelectionRange(cursorPos, cursorPos); // cursor fix
  });

});
</script>