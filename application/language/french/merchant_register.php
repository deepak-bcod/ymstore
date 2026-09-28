<?php $this->load->view('common/header'); ?>

<?php 
$lang = $this->session->userdata('site_lang');
?>
<div class="section sign-page">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="content-page shadow margbot20 p-3">

					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="">
							<a href="#login" aria-controls="login" role="tab" data-toggle="tab">
								<?= $this->lang->line('login_tab'); ?>
							</a>
						</li>
						<li role="presentation" class="active">
							<a href="#register" aria-controls="register" role="tab" data-toggle="tab">
								<?= $this->lang->line('register_tab'); ?>
							</a>
						</li>
					</ul>

					<div class="tab-content">

						<!-- LOGIN TAB -->
						<div role="tabpanel" class="tab-pane fade" id="login">
							<h4><?= $this->lang->line('existing_users_login'); ?></h4>

							<form id="login-user" method="POST" action="<?= BASE_URL; ?>merchant/UserController/loginPost" style="margin-top:10px;">
								<input class="form-control" type="hidden" name="lang" value="<?= $lang; ?>">

								<div class="sigin-form login-eye">

									<div class="form-group inputEmail">
										<input class="form-control" type="email" name="inputEmail" id="inputEmail"
											placeholder="<?= $this->lang->line('email_placeholder'); ?>">
									</div>

									<div class="form-group password d-none">
										<input class="form-control" type="password" name="inputPassword"
											placeholder="<?= $this->lang->line('password_placeholder'); ?>">
									</div>

									<div class="form-group d-none otp_verification">
										<input class="form-control" type="number" name="otp_verification"
											placeholder="<?= $this->lang->line('otp_placeholder'); ?>">
										<p class="login-otp"></p>
									</div>

									<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

									<div class="signin-btn">
										<input type="button" class="btn btn-black continue-btn btn-primary"
											onclick="merchantLoginOtpEmail();"
											value="<?= $this->lang->line('continue_btn'); ?>">

										<input type="submit" class="btn btn-blue d-none btn-primary"
											value="<?= $this->lang->line('login_btn'); ?>">
									</div>

								</div>
							</form>
						</div>

						<!-- REGISTER TAB -->
						<div role="tabpanel" class="tab-pane fade active in" id="register">
							<h4><?= $this->lang->line('new_users_register'); ?></h4>
							<h4><?= $this->lang->line('fill_all_fields'); ?></h4>

							<form id="signup-user" action="<?= base_url(); ?>merchant/UserController/signUpPostData" method="POST">
								<div class="row">

									<div class="col-sm-6">
										<input type="text" name="inputFirstName" class="form-control"
											placeholder="<?= $this->lang->line('first_name'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="text" name="inputLastName" class="form-control"
											placeholder="<?= $this->lang->line('last_name'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="email" name="inputEmail" class="form-control"
											placeholder="<?= $this->lang->line('user_email'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="text" name="inputTradeName" class="form-control"
											placeholder="<?= $this->lang->line('trade_name'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="text" name="inputShopUrl" class="form-control"
											placeholder="<?= $this->lang->line('shop_url'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="text" name="inputBrnNumber" class="form-control"
											placeholder="<?= $this->lang->line('brn_number'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="password" name="inputPassword" class="form-control"
											placeholder="<?= $this->lang->line('password_placeholder'); ?>" required>
									</div>

									<div class="col-sm-6">
										<input type="password" name="inputConfirmPassword" class="form-control"
											placeholder="<?= $this->lang->line('confirm_password'); ?>" required>
									</div>

									<div class="checkbox">
										<label>
											<input type="checkbox" name="newsletter_signin">
											<?= $this->lang->line('newsletter_signup'); ?>
										</label>
									</div>

									<div class="checkbox">
										<label>
											<input type="checkbox" name="remember" required>
											<?= $this->lang->line('accept_terms'); ?>
											<a href="/page/terms-conditions" target="_blank">
												<?= $this->lang->line('terms_condition'); ?>
											</a>
										</label>
									</div>

								</div>

								<input class="btn btn-black btn-primary" type="submit"
									value="<?= $this->lang->line('signup_btn'); ?>">
							</form>

						</div>

					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?= SKIN_JS2; ?>login.js"></script>
<script type="text/javascript" src="<?= SKIN_JS2; ?>register.js"></script>




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

            success: function(response) {

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

                        //buttons: false,

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

            success: function(response) {

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

	<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<script>

    function convertToPassword() {

        $('#password').attr('type', 'password');

    }

</script> -->

	</body>

</html>