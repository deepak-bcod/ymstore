<?php $this->load->view('common_head'); ?>
<?php
$site_lang = $this->session->userdata('site_lang') ?: 'english';
$is_french = ($site_lang === 'french');
?>
<body class="sign-in">

<div class="container-full">
	<div class="row h-100 m-rev">
		<div class="col-sm-5 d-flex align-items-center left-side text-center">
			<form class="form-signin form-style" id="login-user" action="<?php echo base_url();?>UserController/loginPost" method="POST">
				<h2 class="heading-color"><?= $is_french ? 'Se connecter' : 'Sign in' ?></h2>
				<div class="form-fields text-left">
					  <div class="mb-5">
					  <label for="inputEmail" class=""><?= $is_french ? 'Identifiant / E-mail' : 'User ID/Email' ?> <span class="required">*</span></label>
					  <input type="email" name="inputEmail" id="inputEmail" class="form-control" value="<?php if(isset($_COOKIE["login_email"])) { echo $_COOKIE["login_email"]; } ?>" required autofocus>
					  </div>
					  <div class="mb-5">
					  <label for="inputPassword" class=""><?= $is_french ? 'Mot de passe' : 'Password' ?> <span class="required">*</span></label>
					  <input type="password" name="inputPassword" id="inputPassword" class="form-control" value="<?php if(isset($_COOKIE["login_password"])) { echo $_COOKIE["login_password"]; } ?>" required>
					  <span class="eye-password toggle-password"></span>
					  </div>
					  <div class="checkbox">
						<label>
						  <input type="checkbox" value="remember-me"> 
						</label>
						<label class=""><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["login_email"])) { ?> checked <?php } ?>> <?= $is_french ? 'Se souvenir de moi' : 'Remember me' ?> <span class="checked"></span></label>
						<!-- <a href="/forgot-password" class="link-it float-right"><?= $is_french ? 'Mot de passe oublié ?' : 'Forgot Password?' ?></a> -->
					  </div>
				</div>
				<input class="btn btn-lg btn-primary btn-block" type="submit" id="sign-in-btn" name="sign-in-btn" value="<?= $is_french ? 'Se connecter' : 'Sign in' ?>">
			</form>
		</div>
		<div class="col-sm-7 d-flex align-items-center right-side bkg-img">
			<div class="right-content">
				<h1 class="heading-welcome"><span class="fw-300"><?= $is_french ? 'Bienvenue sur' : 'Welcome to' ?></span> YELLOWMARKET</h1>
				<span class="sub-heading"><?= $is_french ? 'Connectez-vous pour accéder à votre compte' : 'Sign In to Access your Account' ?></span>
			</div>
		</div>
    
	</div>
</div>
<script>
var LOGIN_LANG = {
    email_required:   "<?= $is_french ? 'L\'identifiant e-mail est requis.' : 'Email Id is required.' ?>",
    email_invalid:    "<?= $is_french ? 'Veuillez entrer une adresse e-mail valide.' : 'Please enter valid email address.' ?>",
    pass_min_length:  "<?= $is_french ? 'Veuillez saisir 8 caractères ou plus.' : 'Please enter 8 or more characters.' ?>",
    pass_invalid:     "<?= $is_french ? 'Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.' : 'Password must contain at least one alphabetic, one numeric and one special character.' ?>"
};
</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>login.js?v=<?php echo CSSJS_VERSION; ?>"></script>
</body>

</html>
