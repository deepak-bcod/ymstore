
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h3><?= ($flag == 'add') ? lang('add_address') : lang('edit_address') ?></h3>
</div>
<div class="modal-body">
<div class="row ">
	<div class="content-page shadow">
	<form id="address-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/addEditAddress">
		<input type="hidden" id="flag" name="flag" value="<?= $flag?>">
		<input type="hidden" id="address_id" name="address_id" value="<?= $address_id?>">
		<?php //echo '<pre>';print_r($addressData);?>
		  <div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="first_name" class=""><?= lang('first_name') ?></label>
						<input class="form-control" type="text" name="first_name" id="first_name" placeholder="First Name*" onkeypress="return isCharKey(event);" value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->first_name != '')?$addressData[0]->first_name:''?>">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('last_name') ?></label>
						<input class="form-control" type="text" name="last_name" id="last_name" placeholder="Last Name*" onkeypress="return isCharKey(event);" value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->last_name != '')?$addressData[0]->last_name:''?>">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('mobile_number') ?></label>
						<input class="form-control" type="tel" id="mobile_no" name="mobile_no" onkeypress="return isNumberKey(event)" placeholder="Mobile Number"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->mobile_no != '')?$addressData[0]->mobile_no:''?>">
					</div>
				</div><!-- form-box -->
			<!--</div><!-- first block-->

			<!--<div class="row">-->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('building_wing') ?></label>
						<input class="form-control" type="text" id="address_line1" name="address_line1" placeholder="Address Line 1*" value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->address_line1 != '')?$addressData[0]->address_line1:''?>" maxlength="35">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('address_line_2') ?></label>
						<input class="form-control" type="text" id="address_line2" name="address_line2" placeholder="Address Line2" value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->address_line2 != '')?$addressData[0]->address_line2:''?>" maxlength="35">
					</div>
				</div><!-- form-box -->			

				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('country') ?></label>
							<select name="country" id="country" class="form-control">
								<!-- <option value="" selected>Select Country</option> -->
								<?php if (isset($countryList) && count($countryList) > 0) {
									foreach ($countryList as $data) { 
									 	if ($data->country_code == 'MU') {
										?>
											<option value="<?php echo $data->country_code; ?>"  <?php echo ($flag != 'add' && isset($addressData) && $data->country_code == $addressData[0]->country)? "selected='selected'" : ''; ?>><?php echo $data->country_name; ?></option>
									<?php }
								 	}	
								} ?>
							</select>	
					</div>
					
				</div>
				<div class="col-md-6 state_div">
					<!-- onkeypress="return isCharKey(event);" -->
					<div class="form-group">
						<label for="new_password" class=""><?= lang('state') ?></label>
							<input class="form-control validate-char" type="text" id="state" name="state" placeholder="State"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->state != '')?$addressData[0]->state:''?>">
					</div>
					
				</div><!-- form-box -->
				<div class="col-sm-6 dp_state_div">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('state') ?></label>
						<select name="state_dp" id="state_dp" class="form-control">
							<option value="" selected><?= lang('select_state'); ?></option>

							<?php
							if (isset($stateList) && count($stateList) > 0) {
								foreach ($stateList as $data) {

									// Hide Baie Malgache
									if (trim($data->state_name) == 'Baie Malgache') {
										continue;
									}
							?>
									<option value="<?php echo $data->id; ?>"
										<?php echo ($flag != 'add' && isset($addressData) && $data->state_name == $addressData[0]->state)
											? "selected='selected'"
											: ''; ?>>
										<?php echo $data->state_name; ?>
									</option>
							<?php
								}
							}
							?>
						</select>
						</select>
					</div>
				</div><!-- form-box -->
				<div class="col-md-6 city_div">
					<div class="form-group">
						<label for="city" class=""><?= lang('city') ?></label>
						<input class="form-control validate-char" type="text" id="city" name="city"
							placeholder="City" 
							value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->city != '') ? $addressData[0]->city : '' ?>">
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class=""><?= lang('postal_code') ?></label>
						<input class="form-control" type="text" id="pincode" name="pincode" placeholder="Postal Code*" onkeypress="return isNumberKey(event)" maxlength="50" value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->pincode != '')?$addressData[0]->pincode:''?>">
					</div>
				</div><!-- form-box -->

			</div><!-- second block-->
			<div class="row tw-w-full">

				<input type="hidden" id="session_vat_flag" value="<?=$this->session->session_vat_flag ?? '0'?>">
				<?php if($this->session->session_vat_flag === 1): ?>
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="company_name" name="company_name" placeholder="<?=lang('companyname')?>"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->company_name != '')?$addressData[0]->company_name:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control"  <?php echo  ($flag != 'add' && isset($addressData) && $addressData[0]->vat_no != '')?'readonly':'';?>  type="text" id="vat_no" name="vat_no" placeholder="<?=lang('vatno')?>"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->vat_no != '')?$addressData[0]->vat_no:''?>" onkeyup="this.value = this.value.toUpperCase();">
					<div class="loaderDiv" style="display: none"><span ><?=lang('please_wait')?><div class="loader"></div></span></div>
						<input type="hidden" name="vat_flag" id="vatFlag" value="0">
				</div><!-- form-box -->
			</div><!-- third block-->
			<div class="form-box col-sm-6">
			<input class="form-control" type="hidden" id="consulation_no" name="consulation_no" placeholder="Consulation No"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->consulation_no != '')?$addressData[0]->consulation_no:''?>">
			</div><!-- form-box -->

			<div class="form-box col-sm-6">
			<input class="form-control" type="hidden" id="res_company_name" name="res_company_name" placeholder="Res Company Name"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->res_company_name != '')?$addressData[0]->res_company_name:''?>">
			</div><!-- form-box -->

			<div class="form-box col-sm-6">
			<input class="form-control" type="hidden" id="res_company_address" name="res_company_address" placeholder="Res Company Address"  value="<?= ($flag != 'add' && isset($addressData) && $addressData[0]->res_company_address != '')?$addressData[0]->res_company_address:''?>">
			</div><!-- form-box -->

			<?php endif; ?>
          <div class="form-group">

            <div class="col-md-12">

			<input type="submit" class="black-btn btn btn-primary" name="address-btn" id="address-btn" value="<?= lang('submit') ?>">

             <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('close') ?></button> -->

            </div>

          </div>

        </form>

      </div>
      
</div>
</div>

<script>
var lang = {
    required: "<?= lang('required_field'); ?>",
    address_150: "<?= lang('address_150'); ?>",
    address_35: "<?= lang('address_35'); ?>",
    invalid_pincode: "<?= lang('invalid_pincode'); ?>",
    phone_invalid: "<?= lang('phone_invalid'); ?>",
    vat_valid: "<?= lang('vat_valid'); ?>",
    vat_invalid: "<?= lang('vat_invalid'); ?>",
    vat_charge: "<?= lang('vat_charge'); ?>"
};
</script>

<script src="<?php echo SKIN_JS ?>add_edit_address.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
	$(document).ready(function() {
		// 1. Update the 'state' text field whenever the dropdown changes
		$("#state_dp").on("change", function () {
			var selectedStateName = $("#state_dp option:selected").text();
			
			// If "Select State" is picked, clear the value, otherwise set the name
			if($(this).val() == "") {
				$("#state").val("");
			} else {	
				$("#state").val(selectedStateName);
			}
		});

		// 2. (Optional but recommended) Hide the text input div so users only see the dropdown
		$(".state_div").hide(); 
	});
</script>

