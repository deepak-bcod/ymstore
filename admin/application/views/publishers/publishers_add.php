<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

      <ul class="nav nav-pills">

            <li><a href="<?= base_url('publishers') ?>">Merchants</a></li>

            <li class="active"><a>Add New</a></li>

      </ul>

      <div class="main-inner min-height-480">

            <form id="publisherForm" method="POST" action="<?php echo base_url('PublisherController/submitPublisher'); ?>">

                  <input type="hidden" name="publisher_id" value="">

                  <input type="hidden" name="type" value="1">

                  <div class="variant-common-block variant-list">

                        <h1 class="head-name pad-bottom-20">Merchants</h1>

                        <div class="form-group row">

                              <label for="variant_name" class="col-sm-2 col-form-label font-500">Email <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['type' => 'email', 'class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'email', 'value' => set_value('email'), 'required' => 'true', 'validateEmail' => true]); ?>

                              </div>

                        </div><!-- form-group -->

                        <!-- <div class="form-group row">

					<label for="variant_name" class="col-sm-2 col-form-label font-500">Other Emails</label>

					<div class="col-sm-3">

						<?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'emails', 'value' => set_value('emails')]); ?>

					</div>

				</div> -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Password <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <?php echo form_password(['class' => 'form-control', 'placeholder' => 'Enter password', 'id' => "password", 'name' => 'password', 'value' => set_value('password'), 'required' => 'true', 'minlength' => 8]); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Trade Name <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter Trade Name', 'id' => "publication_name", 'name' => 'publication_name', 'value' => set_value('publication_name'), 'required' => 'true', 'onkeypress' => 'return /^[a-zA-Z\s]+$/i.test(event.key)', 'maxlength' => '50']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Merchant Name <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter Merchant Name', 'id' => "vendor_name", 'name' => 'vendor_name', 'value' => set_value('vendor_name'), 'required' => 'true', 'onkeypress' => 'return /^[a-zA-Z\s]+$/i.test(event.key)', 'maxlength' => '30']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Commission % <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'number', 'placeholder' => 'Enter Commission', 'id' => "commision_percent", 'name' => 'commision_percent', 'value' => set_value('commision_percent'), 'required' => 'true', 'maxlength' => '5']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="plan_id" class="col-sm-2 col-form-label font-500">Subscription Plan <span class="required">*</span></label>

                              <div class="col-sm-3">

                                    <select name="plan_id" id="plan_id" class="form-control" required>
                                          <?php if(!empty($plans)): ?>
                                                <?php foreach($plans as $p): ?>
                                                      <option value="<?= $p['id'] ?>" <?= (strtolower($p['name']) === 'starter') ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['name']) ?> (<?= number_format((float)$p['final_price'], 2) ?>)
                                                      </option>
                                                <?php endforeach; ?>
                                          <?php else: ?>
                                                <option value="2">Starter</option>
                                          <?php endif; ?>
                                    </select>

                              </div>

                        </div><!-- form-group -->

                        <!-- <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Split Id<span></span></label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter vendor\'s ccavenue Split id', 'id' => "split_id", 'name' => 'split_id', 'value' => "",   'maxlength' => '30']); ?>

                              </div>

                        </div> -->



                        <!-- New VAT Section -->

                        <div class="form-group row">

                        <label for="vat_status" class="col-sm-2 col-form-label font-500">VAT Status *</label>

                        <div class="col-sm-3">

                              <select name="vat_status" id="vat_status" class="form-control" required>

                                    <option value="">-- Select VAT Status --</option>

                                    <option value="registered" <?= set_value('vat_status') == 'registered' ? 'selected' : '' ?>>Registered</option>

                                    <option value="exempted" <?= set_value('vat_status') == 'exempted' ? 'selected' : '' ?>>Exempted</option>

                              </select>

                        </div>

                        </div><!-- form-group -->



                        <div id="vat_details" style="display: none;">

                        <div class="form-group row">

                              <label for="vat_no" class="col-sm-2 col-form-label font-500">VAT No *</label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'text',

                                    'placeholder' => 'Enter VAT Number',

                                    'id' => "vat_no",

                                    'name' => 'vat_no',

                                    'value' => set_value('vat_no')

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="vat_percent" class="col-sm-2 col-form-label font-500">VAT % *</label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'number',

                                    'step' => '0.01',

                                    'placeholder' => 'Enter VAT Percentage',

                                    'id' => "default_vat_percentage",

                                    'name' => 'default_vat_percentage',

                                    'value' => set_value('default_vat_percentage')

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->

                        </div>



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Phone No <span class="required">*</span></label>

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

                              <label for="" class="col-sm-2 col-form-label font-500">Landline No</label>

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

                              <label for="" class="col-sm-2 col-form-label font-500">Description</label>

                              <div class="col-sm-3">

                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter description', 'name' => 'description', 'value' => set_value('description')]); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Acc No.</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Acc No', 'name' => 'beneficiary_acc_no', 'value' => set_value('beneficiary_acc_no')]); ?>

                              </div>

                        </div> 

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Name</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Name', 'name' => 'beneficiary_name', 'value' => set_value('beneficiary_name')]); ?>

                              </div>

                        </div> 

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary IFSC Code</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary IFSC Code', 'name' => 'beneficiary_ifsc_code', 'value' => set_value('beneficiary_ifsc_code')]); ?>

                              </div>

                        </div> 

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Status</label>

                              <div class="col-sm-3">

                                    <div class="radio col-sm-12">

                                          <label>

                                                <input type="radio" name="status" value="1" checked="">Active <span class="checkmark"></span>

                                          </label>

                                    </div><!-- radio -->

                                    <div class="radio col-sm-12">

                                          <label>

                                                <input type="radio" name="status" value="0">In Active <span class="checkmark"></span>

                                          </label>

                                    </div><!-- radio -->

                              </div>

                        </div><!-- form-group -->

                        <div class="download-discard-small pos-ab-bottom">

                              <button class="white-btn" type="button" data-dismiss="modal" onclick="window.location.href='<?= base_url('publishers') ?>';">Discard</button>

                              <button type="submit" id="save_publisher" class="download-btn">Save</button>

                        </div><!-- download-discard-small -->

                  </div><!-- -common-block -->

      </div><!-- add new tab -->

      </div>

      </div>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/css/intlTelInput.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
</script>