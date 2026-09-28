<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

      <ul class="nav nav-pills">

            <li><a href="<?= base_url('publishers') ?>">Merchants</a></li>

            <li class="active"><a>Edit Merchant</a></li>

      </ul>

      <div class="main-inner min-height-480">

            <form name="publisherEditForm" id="EditpublisherForm" method="POST" action="">

                  <input type="hidden" name="publisher_id" value="<?= $publisher->id ?>">
                  <input type="hidden" name="lang_flag" value="<?= $publisher->lang_flag ?>">


                  <!-- <input type="hidden" name="publisher_id" value="<?= $publisher_payment_details->id ?>"> -->



                  <input type="hidden" name="passwordCheck" value="">

                  <input type="hidden" name="type" value="1">

                  <div class="variant-common-block variant-list">

                        <h1 class="head-name pad-bottom-20">Merchants</h1>

                        <div class="form-group row pl-0">

                              <label for="variant_name" class="col-sm-2 col-form-label font-500">Email<span class="required">*</span></label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'email', 'value' => "$publisher->email", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <!-- <div class="form-group row pl-0">

					<label for="variant_name" class="col-sm-2 col-form-label font-500">Other Emails</label>

					<div class="col-sm-5">

						<?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'emails', 'value' => $publisher->cc_email ?? '']); ?>

					</div>

				</div> -->

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Edit Password</label>

                              <div class="col-sm-6">

                                    <label class="checkbox">

                                          <input type="checkbox" id="passwordCheck" name="passwordCheck" value="check"> <span class="checked"></span>

                                    </label>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500"></label>

                              <div class="col-sm-5">

                                    <?php echo form_password([
                                          'class' => 'form-control',
                                          'placeholder' => 'Enter password',
                                          'id' => "password",
                                          'name' => 'password',
                                          'value' => $publisher->password,
                                          'required' => 'true',
                                          'readonly' => 'true'
                                          ]); 
                                    ?>

                              </div>
                              <div class="col-sm-5">
                                    <p>(To Maintain the privacy the password shown here will always be in encrypted form, to change the exisiting password to new password please type the new password here and Save).</p>
                              </div>


                        </div><!-- form-group -->



                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Trade Name<span class="required">*</span></label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter Trade name', 'id' => "publication_name", 'name' => 'publication_name', 'value' => "$publisher->publication_name", 'required' => 'true', 'onkeypress' => 'return /^[a-zA-Z\s]+$/i.test(event.key)', 'maxlength' => '50']); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Merchant Name<span class="required">*</span></label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter Merchant Name', 'id' => "vendor_name", 'name' => 'vendor_name', 'value' => "$publisher->vendor_name", 'required' => 'true', 'onkeypress' => 'return /^[a-zA-Z\s]+$/i.test(event.key)', 'maxlength' => '30']); ?>

                              </div>

                        </div><!-- form-group -->



                         <div class="form-group row pl-0">



                        <label for="merchant_cat" class="col-sm-2 col-form-label font-500">Business Category</label>



                        <div class="col-sm-5">



                              <select name="merchant_cat" id="merchant_cat" class="form-control">



                                    <option value="">-- Select your Business Category --</option>



                                    <option value="Apps - Software" 



                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Apps - Software") ? 'selected' : ''; ?>>



                                    Apps - Software



                                    </option>



                                    <option value="Arts, Crafts, And Sewing" 



                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Arts, Crafts, And Sewing") ? 'selected' : ''; ?>>



                                    Arts, Crafts, And Sewing



                                    </option>



                                    <option value="Auto Accessories" 



                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto Accessories") ? 'selected' : ''; ?>>



                                    Auto Accessories



                                    </option>



                                    <option value="Auto and Parts" 



                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto and Parts") ? 'selected' : ''; ?>>



                                    Auto and Parts



                                    </option>



                                    <option value="Baby Supplies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Baby Supplies") ? 'selected' : ''; ?>>Baby Supplies</option>



                                    <option value="Beauty and Cosmetics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beauty and Cosmetics") ? 'selected' : ''; ?>>Beauty and Cosmetics</option>



                                    <option value="Beverages" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beverages") ? 'selected' : ''; ?>>Beverages</option>



                                    <option value="Books" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Books") ? 'selected' : ''; ?>>Books</option>



                                    <option value="Computers" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers") ? 'selected' : ''; ?>>Computers</option>



                                    <option value="Computers Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers Accessories") ? 'selected' : ''; ?>>Computers Accessories</option>



                                    <option value="Consumer Electronics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Consumer Electronics") ? 'selected' : ''; ?>>Consumer Electronics</option>



                                    <option value="Electronics - Audio and Video" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Audio and Video") ? 'selected' : ''; ?>>Electronics - Audio and Video</option>



                                    <option value="Electronics - Network - Wireless" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Network - Wireless") ? 'selected' : ''; ?>>Electronics - Network - Wireless</option>



                                    <option value="Electronics - Wearables" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Wearables") ? 'selected' : ''; ?>>Electronics - Wearables</option>



                                    <option value="Fashion Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Fashion Accessories") ? 'selected' : ''; ?>>Fashion Accessories</option>



                                    <option value="Food and Groceries" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Food and Groceries") ? 'selected' : ''; ?>>Food and Groceries</option>



                                    <option value="Furnitures" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Furnitures") ? 'selected' : ''; ?>>Furnitures</option>



                                    <option value="Games" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Games") ? 'selected' : ''; ?>>Games</option>



                                    <option value="Garden" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Garden") ? 'selected' : ''; ?>>Garden</option>



                                    <option value="Health" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Health") ? 'selected' : ''; ?>>Health</option>



                                    <option value="Home Appliances" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Appliances") ? 'selected' : ''; ?>>Home Appliances</option>



                                    <option value="Home Deco" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Deco") ? 'selected' : ''; ?>>Home Deco</option>



                                    <option value="Household Essentials" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Household Essentials") ? 'selected' : ''; ?>>Household Essentials</option>



                                    <option value="Jewellery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Jewellery") ? 'selected' : ''; ?>>Jewellery</option>



                                    <option value="Kids Clothing" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Clothing") ? 'selected' : ''; ?>>Kids Clothing</option>



                                    <option value="Kids Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Products") ? 'selected' : ''; ?>>Kids Products</option>



                                    <option value="Kitchen Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kitchen Equipment") ? 'selected' : ''; ?>>Kitchen Equipment</option>



                                    <option value="Luggage - Bags" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Luggage - Bags") ? 'selected' : ''; ?>>Luggage - Bags</option>



                                    <option value="Media" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Media") ? 'selected' : ''; ?>>Media</option>



                                    <option value="Medical" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Medical") ? 'selected' : ''; ?>>Medical</option>



                                    <option value="Men's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Men's Fashion") ? 'selected' : ''; ?>>Men's Fashion</option>



                                    <option value="Mobile Phones" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Mobile Phones") ? 'selected' : ''; ?>>Mobile Phones</option>



                                    <option value="Office Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Equipment") ? 'selected' : ''; ?>>Office Equipment</option>



                                    <option value="Office Stationery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Stationery") ? 'selected' : ''; ?>>Office Stationery</option>



                                    <option value="Outdoor Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Outdoor Equipment") ? 'selected' : ''; ?>>Outdoor Equipment</option>



                                    <option value="Personal Care" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Personal Care") ? 'selected' : ''; ?>>Personal Care</option>



                                    <option value="Pet Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Pet Products") ? 'selected' : ''; ?>>Pet Products</option>



                                    <option value="Shoes" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Shoes") ? 'selected' : ''; ?>>Shoes</option>



                                    <option value="Sports and Fitness" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Sports and Fitness") ? 'selected' : ''; ?>>Sports and Fitness</option>



                                    <option value="Tools and Home Improvement" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Tools and Home Improvement") ? 'selected' : ''; ?>>Tools and Home Improvement</option>



                                    <option value="Toys and Hobbies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Toys and Hobbies") ? 'selected' : ''; ?>>Toys and Hobbies</option>



                                    <option value="Travel Gear" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Travel Gear") ? 'selected' : ''; ?>>Travel Gear</option>



                                    <option value="Virtual" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Virtual") ? 'selected' : ''; ?>>Virtual</option>



                                    <option value="Women's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Women's Fashion") ? 'selected' : ''; ?>>Women's Fashion</option>



                              </select>



                        </div>



                        </div><!-- form-group -->

                              <div class="form-group row pl-0">

                              <label for="shipment_type" class="col-sm-2 col-form-label font-500">Shipment Type</label>

                              <div class="col-sm-5">

                                    <?php if (!empty($publisher->shipment_type)) : ?>

                                          <!-- Show disabled dropdown for display -->

                                          <select class="form-control" disabled>

                                          <option value="1" <?= ($publisher->shipment_type == '1') ? 'selected' : ''; ?>>Own Delivery</option>

                                          <option value="2" <?= ($publisher->shipment_type == '2') ? 'selected' : ''; ?>>YM Delivery</option>

                                          </select>

                                          <!-- Keep actual value in hidden input so it always goes to DB -->

                                          <input type="hidden" name="shipment_type" value="<?= $publisher->shipment_type ?>">

                                          <small class="text-muted">Shipment type cannot be changed once selected.</small>

                                    <?php else : ?>

                                          <!-- First time selection -->

                                          <select name="shipment_type" id="shipment_type" class="form-control">

                                          <option value="">-- Select Shipment Type --</option>

                                          <option value="1">Own Delivery</option>

                                          <option value="2">YM Delivery</option>

                                          </select>

                                    <?php endif; ?>

                              </div>

                        </div>



                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Processing Fees % <span class="required">*</span></label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'number', 'placeholder' => 'Enter commission', 'id' => "commision_percent", 'name' => 'commision_percent', 'value' => "4", 'required' => 'true', 'maxlength' => '5']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row pl-0">

                              <label for="plan_id" class="col-sm-2 col-form-label font-500">Subscription Plan <span class="required">*</span></label>

                              <div class="col-sm-5">

                                    <select name="plan_id" id="plan_id" class="form-control" required>
                                          <?php 
                                          $current_plan_id = isset($active_subscription['plan_id']) ? $active_subscription['plan_id'] : 2;
                                          if(!empty($plans)): 
                                                foreach($plans as $p): 
                                          ?>
                                                <option value="<?= $p['id'] ?>" <?= ($p['id'] == $current_plan_id) ? 'selected' : '' ?>>
                                                      <?= htmlspecialchars($p['name']) ?> (<?= number_format((float)$p['final_price'], 2) ?>)
                                                </option>
                                          <?php 
                                                endforeach; 
                                          else: 
                                          ?>
                                                <option value="2">Starter</option>
                                          <?php endif; ?>
                                    </select>
                                    <?php if(!empty($active_subscription)): ?>
                                          <small class="text-muted">Status: <?= ucfirst($active_subscription['status'] ?? 'active') ?> | Expires: <?= !empty($active_subscription['end_date']) ? date('d M Y', strtotime($active_subscription['end_date'])) : 'N/A' ?></small>
                                    <?php endif; ?>

                              </div>

                        </div><!-- form-group -->

                        <!-- <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Split Id<span></span></label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter vendor\'s ccavenue Split id', 'id' => "split_id", 'name' => 'split_id', 'value' => "$publisher->split_id", 'required' => 'true',  'maxlength' => '30']); ?>

                              </div>

                        </div> -->



                         <!-- New VAT Section -->

                        <div class="form-group row pl-0">

                        <label for="vat_status" class="col-sm-2 col-form-label font-500">VAT Status</label>

                        <div class="col-sm-5">

                              <select name="vat_status" id="vat_status" class="form-control">

                                    <option value="">-- Select VAT Status --</option>

                                    <option value="registered" <?= ($publisher->vat_status == 'registered') ? 'selected' : '' ?>>Registered</option>

                                    <option value="exempted" <?= ($publisher->vat_status == 'exempted') ? 'selected' : '' ?>>Exempted</option>

                              </select>

                        </div>

                        </div><!-- form-group -->



                        <div id="vat_details" style="display: none;">

                        <div class="form-group row pl-0">

                              <label for="vat_no" class="col-sm-2 col-form-label font-500">VAT No</label>

                              <div class="col-sm-5">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'text',

                                    'placeholder' => 'Enter VAT Number',

                                    'id' => "vat_no",

                                    'name' => 'vat_no',

                                    'value' => "$publisher->vat_no"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row pl-0">

                              <label for="vat_percent" class="col-sm-2 col-form-label font-500">VAT % </label>

                              <div class="col-sm-5">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'number',

                                    'step' => '0.01',

                                    'placeholder' => 'Enter VAT Percentage',

                                    'id' => "default_vat_percentage",

                                    'name' => 'default_vat_percentage',
                                    'value' => "$publisher->default_vat_percentage"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->

                        </div>





                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Phone No</label>

                              <div class="col-sm-5">
                                    <input 
                                          type="tel" 
                                          id="phone_no" 
                                          name="phone_no" 
                                          class="form-control" 
                                          placeholder="Enter phone number" 
                                          value="<?php echo htmlspecialchars($publisher->phone_no); ?>" 
                                          pattern="[0-9]{8}" 
                                          maxlength="8"
                                          
                                    >
                                    <input type="hidden" id="full_phone" name="full_phone">
                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Landline No</label>

                              <div class="col-sm-5">
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

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Description</label>

                              <div class="col-sm-5">

                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter description', 'name' => 'description', 'value' => "$publisher->description"]); ?>

                              </div>

                        </div><!-- form-group -->



                        

                        <div class="form-group row pl-0">



                              <label for="" class="col-sm-2 col-form-label font-500">Company Name</label>



                              <div class="col-sm-5">



                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Company Name', 'id' => "company_name", 'name' => 'company_name', 'value' => "$publisher->company_name"]); ?>



                              </div>



                        </div><!-- form-group -->



                        <div class="form-group row pl-0">



                              <label for="" class="col-sm-2 col-form-label font-500">Location</label>



                              <div class="col-sm-5">



                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Location', 'id' => "location", 'name' => 'location', 'value' => "$publisher->location"]); ?>



                              </div>



                        </div><!-- form-group -->



                        <div class="form-group row pl-0">



                              <label for="" class="col-sm-2 col-form-label font-500">Company Address</label>



                              <div class="col-sm-5">



                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter Company Address', 'name' => 'company_address', 'value' => "$publisher->company_address"]); ?>



                              </div>



                        </div><!-- form-group -->



                       

                        <!-- <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Status</label>

                              <div class="col-sm-5">

                                    <?php $status = $publisher->status; ?>

                                    <input type="radio" name="status" value="1" <?php if ($status == 1) {

                                                                                          echo "checked";

                                                                                    } ?>>

                                    <label>Active</label><br>

                                    <input type="radio" name="status" value="0" <?php if ($status == 0) {

                                                                                          echo "checked";

                                                                                    } ?>>

                                    <label>In Active</label>

                              </div>

                        </div>form-group -->



                        



                        <div class="form-group row pl-0">

                        <label for="" class="col-sm-2 col-form-label font-500">Status</label>

                        <div class="col-sm-2">

                              <?php $status = $publisher->status; ?>



                              <input type="radio" name="status" value="0" <?php if ($status == 0) echo "checked"; ?>>

                              <label>Pending</label><br>



                              <input type="radio" name="status" value="1" <?php if ($status == 1) echo "checked"; ?>>

                              <label>Approve</label><br>



                              <input type="radio" name="status" value="2" <?php if ($status == 2) echo "checked"; ?>>

                              <label>Reject</label>

                        </div>

                        </div><!-- form-group -->



                         <h1 class="head-name pad-bottom-20">Bank Details</h1>

                          <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Bank Name</label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Name', 'name' => 'bank_name',  'value' => $publisher_payment_details->bank_name ?? '']); ?>

                              </div>

                        </div>

                           <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Bank Branch No.</label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Branch No', 'name' => 'bank_branch_number', 'value' => $publisher_payment_details->bank_branch_number ?? '']); ?>

                              </div>

                        </div>

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500"> Bank Swift Code</label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Swift Code', 'name' => 'beneficiary_ifsc_code', 'value' => $publisher_payment_details->beneficiary_ifsc_code ?? '']); ?>

                              </div>

                        </div>

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Acc No.</label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Acc No', 'name' => 'beneficiary_acc_no', 'value' => $publisher_payment_details->beneficiary_acc_no ?? '']); ?>

                              </div>

                        </div>

                        <div class="form-group row pl-0">

                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Name</label>

                              <div class="col-sm-5">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Name', 'name' => 'beneficiary_name', 'value' => $publisher_payment_details->beneficiary_name ?? '']); ?>

                              </div>

                        </div>
                        <div class="form-group row pl-0">
                              <label for="" class="col-sm-2 col-form-label font-500">Bank Address</label>
                              <div class="col-sm-5">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Address', 'name' => 'bank_address','required' => 'true', 'value' => $publisher_payment_details->bank_address ?? '']); ?>
                              </div>
                        </div>
                        <div class="form-group row pl-0">
                              <label for="" class="col-sm-2 col-form-label font-500">IBan</label>
                              <div class="col-sm-5">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'enter IBan', 'name' => 'iban','required' => 'true', 'value' => $publisher_payment_details->iban ?? '']); ?>
                              </div>
                        </div>

                      



                        <div class="download-discard-small pos-ab-bottom">

                              <button class="white-btn" type="button" data-dismiss="modal" onclick="window.location.href='<?= base_url('publishers') ?>';">Discard</button>

                              <input type="submit" value="Save" id="update_publisher" name="save_publisher" class="download-btn">

                        </div><!-- download-discard-small -->

                  </div><!-- -common-block -->

            </form>

      </div>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/css/intlTelInput.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"></script>
<!-- <script src="<?php echo SKIN_JS; ?>publisher.js"></script> -->



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

            $("#passwordCheck").click(function () {

                  if ($(this).is(":checked")) {

                        $("#password").prop("readonly", false);
                        $("#password").attr("type", "text");   // show password

                  } else {

                        $("#password").prop("readonly", true);
                        $("#password").attr("type", "password"); // hide password
                        // $("#password").val('');
                  }

            });

            // ✅ Before form submit, store full international numbers in hidden inputs
            $('form').on('submit', function (e) {
                  e.preventDefault();

                  const form = $(this);
                  const formData = new FormData(this);

                  $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json', // 👈 tells jQuery to expect JSON
                        success: function (response) {
                              if (response.flag === 1) {
                                    // alert(response.msg);
                                    window.location.href = response.url; // redirect
                              } else {
                                    alert(response.msg || "Something went wrong");
                              }
                        },
                        error: function (xhr) {
                              alert("Error: " + xhr.responseText);
                        }
                  });
            });
      });

      $(document).ready(function () {

            function toggleVatFields() {
                  let status = $('#vat_status').val();

                  if (status === 'registered') {
                        $("#vat_details").show();

                        $("#vat_no").prop("required", true);
                        $("#default_vat_percentage").prop("required", true);
                  } else {
                        $("#vat_details").hide();

                        $("#vat_no").prop("required", false);
                        $("#default_vat_percentage").prop("required", false);
                  }
            }

            // run on page load
            toggleVatFields();

            // run on change
            $('#vat_status').on('change', function () {
                  toggleVatFields();
            });

      });
      $(document).ready(function () {
            $("#update_publisher").click(function (e) {
                  e.preventDefault();
                  var formData = new FormData($("#EditpublisherForm")[0]);
                  $.ajax({
                        type: "POST", 
                        url: BASE_URL + "PublisherController/submitPublisher", 
                        dataType: "json", 
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function () {
                        $("#ajax-spinner").show();
                        },
                        success: function (response) {
                        window.setTimeout(function () {
                        $("#ajax-spinner").hide();
                        }, 1500);
                       // $("#update_publisher").attr("disabled", "disabled");
                        if (response == "success") {
                        swal(
                              {
                              title: "Success",
                              text: "Merchant Update successfully!",
                              type: "success",
                              },
                              function () {
                              location.href = BASE_URL + "publishers";
                              }
                        );
                        // swal('Success','Publisher Update successfully!','success');
                        //     window.setTimeout( function() {
                        //     window.location.href="/admin/publishers";
                        //     }, 1500);
                        } else {
                        swal({
                              title: "",
                              text: response.msg,
                              button: false,
                              icon: "success",
                        });
                        return false;
                        }
                        },
                  });
            });
      });
</script>
