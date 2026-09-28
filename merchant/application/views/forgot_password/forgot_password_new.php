<form class="form-signin form-style" id="reset-password">

	<div class="modal-header">
		<h4 class="head-name"><?php echo $this->lang->line('password_reset_title'); ?></h4>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">×</span>
		</button>

	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Variants</p> -->
		<div class="row">
			<div class="form-fields text-left">
				<div class="mb-5">
					<label for="password" class=""><?php echo $this->lang->line('password_label'); ?> <span class="required">*</span></label>
					<input type="password" id="inputPassword" name="inputPassword" class="form-control" required>
					<span class="eye-password toggle-password"></span>
					<div id="message" style=" display:none;">
						<p><strong><?php echo $this->lang->line('password_rule_title'); ?></strong></p>
						<p id="alphabetic" class="invalid"><?php echo $this->lang->line('password_rule_alphabetic'); ?></p>
						<p id="special" class="invalid"><?php echo $this->lang->line('password_rule_special'); ?></p>
						<p id="number" class="invalid"><?php echo $this->lang->line('password_rule_number'); ?></p>
						<p id="length" class="invalid"><?php echo $this->lang->line('password_rule_length'); ?></p>
					</div>
					<div id="error_message1">
						<span class="text-danger"><?php echo $this->lang->line('field_required'); ?></span>
					</div>
				</div>
				<div class="mb-5">
					<label for="inputPassword" class=""><?php echo $this->lang->line('confirm_password_label'); ?> <span class="required">*</span></label>
					<input type="password" id="inputConfPassword" name="inputConfPassword" class="form-control" required>
					<span class="eye-password toggle-Confpassword"></span>
					<div id="error_message2">
						<span class="text-danger"><?php echo $this->lang->line('field_required'); ?></span>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input class="purple-btn" type="button" id="reset-pass-btn" name="reset-pass-btn" value="<?php echo $this->lang->line('submit_button'); ?>" onclick="Onsubmit()">
	</div>
</form>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>reset_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>publisher.js?v=<?php echo CSSJS_VERSION; ?>"></script>
