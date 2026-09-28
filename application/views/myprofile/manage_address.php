<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo $this->lang->line('home'); ?></a></li>
                <!--<li><span class="icon icon-keyboard_arrow_right"></span></li>-->
                <li class="active"><?php echo $this->lang->line('my_profile'); ?></li>
            </ul>
        </div>
    </div>
</div><!-- breadcrum section -->

<div class="my-profile-page-full ">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-md-9 col-sm-9">
                <div class="content-page">
                    <div class="row">
                        <div class="col-md-12">
                            <h1><?php echo $this->lang->line('manage_addresses'); ?></h1>
                        </div>

                        <?php (new CustomerAddress())->list(); ?>

                        <div class="address-btn col-sm-12">
                            <button class="black-btn btn btn-primary" onclick="openAddressPopup('add',<?= $_SESSION['LoginID']?>)">
                                <?php echo $this->lang->line('add_new_address'); ?>
                            </button>
                        </div><!-- address-btn -->

                    </div><!--row-->
                </div><!--content-page-->
            </div><!-- col-md-9 -->

        </div><!-- row -->
    </div><!-- container -->
</div><!-- my-profile-page-full -->

<script>
var lang = {
    delete_title: "<?= lang('delete_title'); ?>",
    delete_message: "<?= lang('delete_message'); ?>",
    yes_delete: "<?= lang('yes_delete'); ?>",
    cancel: "<?= lang('cancel'); ?>"
};
</script>

<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
</body>
</html>
