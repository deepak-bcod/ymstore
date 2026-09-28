<?php $this->load->view('common/header'); ?>
<?php 
$lang = $this->session->userdata('site_lang');
// print_r($lang);die;
?>
<div class="section sign-page">

	<div class="container">

		<div class="row">

			<div class="col-md-6 col-md-offset-3">

				<div class="content-page shadow margbot20 p-3">

					<ul class="nav nav-tabs" role="tablist">

						<li role="presentation" class="active">

							<a href="#login" aria-controls="login" role="tab" data-toggle="tab" aria-expanded="true">

								<?= $this->lang->line('login_tab'); ?>

							</a>

						</li>

						<!-- <li role="presentation" class="">

							<a href="#register" aria-controls="register" role="tab" data-toggle="tab" aria-expanded="false">

								<?= $this->lang->line('register_tab'); ?>

							</a>

						</li> -->

					</ul>

					<div class="tab-content">

						<div role="tabpanel" class="tab-pane fade active in" id="login">

							<h4><?= $this->lang->line('existing_users_login'); ?></h4>

							<form id="login-user" method="POST"

								action="<?php echo BASE_URL; ?>merchant/UserController/loginPost"

								style="margin-top:10px;">
								<input class="form-control" type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">

								<div class="sigin-form login-eye">

									<div

										class="form-group form-group <?php echo ($display_page == 'checkout') ? 'first' : '' ?> inputEmail">

										<input class="form-control" type="email" name="inputEmail" id="inputEmail"

											placeholder="<?= $this->lang->line('email_placeholder'); ?>">

									</div><!-- form-box -->



									<div

										class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> password d-none">

										<input class="form-control" type="password" id="inputPassword"

											name="inputPassword" placeholder="<?= $this->lang->line('password_placeholder'); ?>">

									</div>



									<div

										class="form-group <?php echo ($display_page == 'checkout') ? 'second' : '' ?> d-none otp_verification">

										<input class="form-control" type="number"

											id="<?php echo ($display_page == 'checkout') ? 'reg_password' : 'conf_password' ?>"

											name="otp_verification" id="otp_verification" placeholder="<?= $this->lang->line('otp_placeholder'); ?>">

										<p class="login-otp"></p>

									</div><!-- form-box -->
									<p class="forgot-password">

										<a href="<?php echo BASE_URL ?>merchants/forgot-password"><?= $this->lang->line('forgot_password'); ?></a>

									</p>



									<input type="hidden" name="g-recaptcha-response"

										id="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response-login' : 'g-recaptcha-response' ?>"

										class="<?php echo ($display_page == 'checkout') ? 'g-recaptcha-response' : '' ?>">



									<?php if (isset($GLOBALS['captcha_check_flag_g']) && $GLOBALS['captcha_check_flag_g'] == 'no') { ?>

										<div class="col-sm-12">

											<input type="text" name="nickname" id="nickname" value=""

												style="display: none;"></label>

										</div>

									<?php } ?>



									<div class="signin-btn">

										<input type="button" class="btn btn-black continue-btn btn btn-primary"

											onclick="merchantLoginOtpEmail();" name="signin-btn"

											id="signin-continue-btn" value="<?= $this->lang->line('continue_btn'); ?>">

										<input type="submit" class="btn btn-blue d-none btn btn-primary"

											name="signin-btn" id="signin-btn" value="<?= $this->lang->line('login_btn'); ?>">

									</div><!-- signin-btn -->

								</div><!-- sigin-form -->

							</form>

						</div>



						<div role="tabpanel" class="tab-pane fade" id="register">

							<h4><?= $this->lang->line('new_users_register'); ?></h4>

							<h4><?= $this->lang->line('fill_all_fields'); ?></h4>

							<form class="form-signin form-style" id="signup-user"

								action="<?php echo base_url(); ?>merchant/UserController/signUpPostData" method="POST">
								<input class="form-control" type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">
								<input class="form-control" type="hidden" name="frontend_register" id="frontend_register" value="frontend_register">
								<div class="row">

									<div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputFirstName" id="inputFirstName"

												class="form-control" placeholder="<?= $this->lang->line('first_name'); ?>" value="" required>

											<span class="pmd-textfield-focused"></span>

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputLastName" id="inputLastName"

												class="form-control" placeholder="<?= $this->lang->line('last_name'); ?>" value="" required>

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="email" name="inputEmail" id="inputEmail" class="form-control"

												placeholder="<?= $this->lang->line('user_email'); ?>"

												value="<?php if (isset($_COOKIE["login_email"])) { echo $_COOKIE["login_email"]; } ?>"

												required autofocus>

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputTradeName" id="inputTradeName"

												placeholder="<?= $this->lang->line('trade_name'); ?>" class="form-control" value="" required>

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputShopUrl" id="inputShopUrl"

												placeholder="<?= $this->lang->line('shop_url'); ?>" class="form-control" value="">

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputBrnNumber" id="inputBrnNumber"

												placeholder="<?= $this->lang->line('brn_number'); ?>" class="form-control" value="" required>

										</div>

									</div>

									<!-- <div class="col-sm-6">

										<div class="form-group">

											<input type="text" name="inputVatNumber" id="inputVatNumber"

												placeholder="<?= $this->lang->line('vat_number'); ?>" class="form-control" value="" required>

										</div>

									</div> -->

									<div class="col-sm-6">

										<div class="form-group">

											<input type="password" name="inputPassword" id="inputPassword"

												placeholder="<?= $this->lang->line('password_placeholder'); ?>" class="form-control"

												value="<?php if (isset($_COOKIE["login_password"])) { echo $_COOKIE["login_password"]; } ?>"

												required>

											<span class="eye-text eye-password toggle-password"></span>

										</div>

									</div>

									<div class="col-sm-6">

										<div class="form-group">

											<input type="password" name="inputConfirmPassword" id="inputConfirmPassword"

												placeholder="<?= $this->lang->line('confirm_password'); ?>" class="form-control" value="" required>

											<span class="eye-text eye-password toggle-Confpassword"></span>

										</div>

									</div>

									<div class="checkbox">

										<label class="">

											<input type="checkbox" name="newsletter_signin" id="newsletter_signin">

											<?= $this->lang->line('newsletter_signup'); ?> <span class="checked"></span>

										</label>

									</div>

									<div class="checkbox">

										<label class="">

											<input type="checkbox" name="remember" id="remember" <?php if (isset($_COOKIE["login_email"])) { ?> checked <?php } ?>>

											<?= $this->lang->line('accept_terms'); ?>

											<span class="checked"></span>

											<span class="required"><?= $this->lang->line('terms_condition'); ?></span>

										</label>

									</div>

								</div>

								<input class="btn btn-black btn btn-primary" type="submit" id="sign-up-btn"

									name="sign-up-btn" value="<?= $this->lang->line('signup_btn'); ?>">

							</form>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>

<?php $this->load->view('common/footer'); ?>
<?php $is_french_ml = ($lang === 'french'); ?>
<script>
var LOGIN_LANG = {
    email_required:  "<?= $is_french_ml ? 'L\'identifiant e-mail est requis.' : 'Email Id is required.' ?>",
    email_invalid:   "<?= $is_french_ml ? 'Veuillez entrer une adresse e-mail valide.' : 'Please enter valid email address.' ?>",
    pass_min_length: "<?= $is_french_ml ? 'Veuillez saisir 8 caractères ou plus.' : 'Please enter 8 or more characters.' ?>",
    pass_invalid:    "<?= $is_french_ml ? 'Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.' : 'Password must contain at least one alphabetic, one numeric and one special character.' ?>"
};
</script>

<script type="text/javascript" src="<?php echo SKIN_JS2; ?>login.js"></script>

<script type="text/javascript" src="<?php echo SKIN_JS2; ?>register.js"></script>

<script type="text/javascript">

	// Checkout Page

	function merchantLoginOtpEmailCheckout() {

		var inputEmail = $('#inputEmail').val();

		$.ajax({

			url: BASE_URL + "CheckoutController/merchantLoginOtpEmail",

			type: "POST",

			data: {

				inputEmail: inputEmail

			},

			success: function (response) {

				var obj = JSON.parse(response);

				if (obj.flag == 1) {

					$('.inputEmail').addClass('d-none');

					$('.password').removeClass('d-none');

					$('.continue-btn').addClass('d-none');

					$('.btn-blue').removeClass('d-none');

					$('.login-otp').html('Login OTP is :' + obj.data);

				} else if (obj.flag == 2) {

					$('.inputEmail').addClass('d-none');

					$('.otp_verification').removeClass('d-none');

					$('.continue-btn').addClass('d-none');

					$('.btn-blue').removeClass('d-none');

					$('.login-otp').html('OTP is : ' + obj.data);

				} else {

					swal({

						title: "",

						icon: "error",

						text: obj.msg,

						showCancelButton: true,

						cancelButtonText: "CANCEL",

					});

				}

			}

		});

	}



	function merchantLoginOtpEmail() {

		var inputEmail = $('#inputEmail').val();

		$.ajax({

			url: BASE_URL + "CustomerController/merchantLoginOtpEmail",

			type: "POST",

			data: {

				inputEmail: inputEmail

			},

			success: function (response) {

				var obj = JSON.parse(response);

				if (obj.flag == 1) {

					$('.inputEmail').addClass('d-none');

					$('.password').removeClass('d-none');

					$('.continue-btn').addClass('d-none');

					$('.btn-blue').removeClass('d-none');

					$('.forgot-password').removeClass('d-none');

				} else if (obj.flag == 2) {

					$('.inputEmail').addClass('d-none');

					$('.otp_verification').removeClass('d-none');

					$('.continue-btn').addClass('d-none');

					$('.btn-blue').removeClass('d-none');

					$('.login-otp').html('OTP is : ' + obj.data);

				} else {

					swal({

						title: "",

						icon: "error",

						text: obj.msg,

						buttons: false,

					});

				}

			}

		});

	}

</script>

</body>

</html>

