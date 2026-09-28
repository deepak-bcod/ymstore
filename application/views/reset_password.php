<?php $this->load->view('common/header'); ?>
    <div class="signin-full">
      <div class="container"  style="margin-bottom: 20px;">
        <div class="col-md-12">
          <div class="row">
			
			<div class="grey-bg-user signin-section forgot-password-section">
				<div class="sign-in-inner new-toped">
					<h3><?= lang('reset_title'); ?></h3>
					<h5><?= lang('reset_subtitle'); ?></h5>

					<form id="reset-password-form" method="POST" action="<?= BASE_URL; ?>customer/reset-password/<?= $urlData; ?>">

						<div class="forgotpassword-form">

							<div class="form-box" style="margin-bottom:10px;">
								<input class="form-control"
									type="password"
									name="password"
									id="password"
									placeholder="<?= lang('password_placeholder'); ?>">
							</div>

							<div class="form-box" style="margin-bottom:10px;">
								<input class="form-control"
									type="password"
									id="conf_password"
									name="conf_password"
									placeholder="<?= lang('confirm_password_placeholder'); ?>">
							</div>

							<div class="signin-btn">
								<input type="submit"
									class="black-btn blue-btn"
									name="reset-password-btn"
									id="reset-password-btn"
									value="<?= lang('submit_btn'); ?>">
							</div>

						</div>

					</form>

				</div><!-- sign-in-inner -->
			</div><!-- grey-bg-user -->
			
		  </div><!-- row-->
		 </div><!-- col-md-12-->
		</div>
	</div><!-- signin-full -->
	<script>
	var lang = {
		password_required        : "<?= lang('password_required'); ?>",
		password_minlength       : "<?= lang('password_minlength'); ?>",
		password_strength        : "<?= lang('password_strength'); ?>",
		confirm_password_required: "<?= lang('confirm_password_required'); ?>",
		confirm_password_match   : "<?= lang('password_mismatch'); ?>",
		email_invalid            : "<?= lang('email_invalid'); ?>"
	};
	</script>
   
    <?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>reset_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>