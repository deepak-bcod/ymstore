<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?= lang('home'); ?></a></li>
                <li class="active"><?= lang('my_profile'); ?></li>
            </ul>
        </div>
    </div>
</div><!-- breadcrum section -->


<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">
                    <div class="row">
                        <div class="col-sm-4 col-md-6">
                            <h1><?= lang('personal_information'); ?></h1>
                        </div>
                        <div class="col-sm-8 col-md-6 on-right">
                            <button class="btn btn-primary" onclick="openChangePasswordPopup(<?= $customerData->id ?>)">
                                <?= lang('change_password'); ?>
                            </button>
                            <button class="btn btn-primary" onclick="openChangeEmailPopup(<?= $customerData->id ?>)">
                                <?= lang('change_email'); ?>
                            </button>
                        </div>
                        <div class="col-sm-12">
                            <div class="profile-complete" style="margin-top:15px;">
                                <div><?= lang('profile_complete'); ?></div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="<?= $profilePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $profilePercentage ?>%;">
                                        <?= $profilePercentage ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <form class="default-form" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL; ?>MyProfileController/updateCustomerInfo">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('firstname'); ?></label>
                                            <input type="text" class="form-control" value="<?= $_SESSION['FirstName'] ?>" id="first_name" name="first_name">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('lastname'); ?></label>
                                            <input type="text" class="form-control" value="<?= $_SESSION['LastName'] ?>" id="last_name" name="last_name">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('email'); ?></label>
                                            <input type="email" class="form-control" value="<?= $_SESSION['EmailID'] ?>" id="email" disabled="">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('mobile_number'); ?></label>
                                            <input
                                            type="text"
                                            class="form-control"
                                            value="<?= isset($customerData->mobile_no) ? $customerData->mobile_no : '' ?>"
                                            id="mobile_no"
                                            disabled>

                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('country'); ?></label>
                                            <select class="form-control" name="country" id="country">
                                                <option value="" selected><?= lang('selectcountry') ?></option>
                                                <?php if (!empty($countryList)) {
                                                    foreach ($countryList as $data) { ?>
                                                       <option value="<?= $data->country_code; ?>" <?=  $data->country_code == 'MU' ? "selected" : ""; ?>>
                                                        <?= $data->country_name; ?>
                                                        </option>
                                                <?php }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?= lang('dob'); ?></label>
                                            <input type="hidden" class="form-control" id="dob"
                                                value="<?= isset($customerData->dob) ? date("Y-m-d", strtotime($customerData->dob)) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <?php if (isset($restricted_access) && $restricted_access == 'yes') { ?>

                                            <div class="col-sm-6">
                                                <label><?= lang('companyname') ?></label>
                                                <input type="text" class="form-control"
                                                    value="<?= $customerData->company_name ?? '' ?>"
                                                    <?= $restricted_access == 'yes' ? 'disabled' : '' ?>
                                                    id="company_name" name="company_name">
                                            </div>

                                            <div class="col-sm-6">
                                                <label>
                                                    <?= $shop_flag == 1 ? lang('gst_number') : lang('vat_number'); ?>
                                                </label>
                                                <input type="text" class="form-control"
                                                    value="<?= $customerData->gst_no ?? '' ?>"
                                                    <?= $restricted_access == 'yes' ? 'disabled' : '' ?>
                                                    id="gst_no" name="gst_no">
                                            </div>
                                        <?php } ?>

                                        <div class="male-female-section">
                                            <label><?= lang('gender'); ?></label>
                                            <div class="male-female-inner">
                                                <label class="radio-label-checkout">
                                                    <input class="radio-checkout" type="radio" value="male" name="gender"
                                                        <?= ($customerData->gender == 'male') ? 'checked' : ''; ?>>
                                                    <?= lang('male'); ?> <span class="radio-check"></span>
                                                </label>

                                                <label class="radio-label-checkout">
                                                    <input class="radio-checkout" type="radio" value="female" name="gender"
                                                        <?= ($customerData->gender == 'female') ? 'checked' : ''; ?>>
                                                    <?= lang('female'); ?> <span class="radio-check"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-primary"><?= lang('submit'); ?></button>
                                        <a href="<?php echo base_url(); ?>" class="btn btn-secondary"><?= lang('continue_shopping'); ?></a>
                                    </div>

                                </div><!-- .row ends -->
                            </form>
                        </div>
                    </div>
                </div><!-- .content-page ends -->
            </div>
        </div><!-- row -->
    </div><!-- container -->
</div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
<script>
  const input = document.querySelector("#mobile_no");
  const iti = window.intlTelInput(input, {
    initialCountry: "mu",
    separateDialCode: true,
    placeholderNumberType: "MOBILE",
    autoPlaceholder: "polite",
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  });
</script>
<script type="text/javascript">
    // create a custom phone number rule called 'intlTelNumber'
  jQuery.validator.addMethod("intlTelNumber", function(value, element) {
    return this.optional(element) || $(element).intlTelInput("isValidNumber");
  }, "Please enter a valid International Phone Number");
</script>
<script src="<?php echo SKIN_JS ?>myprofile.js"></script>
</body>
</html>
