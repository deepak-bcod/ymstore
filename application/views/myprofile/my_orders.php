<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li>
                    <a href="<?php echo base_url(); ?>">
                        <?= $this->lang->line('home'); ?>
                    </a>
                </li>

                <li class="active">
                    <?= $this->lang->line('my_profile'); ?>
                </li>
            </ul>
        </div>
    </div>
</div><!-- breadcrum section -->

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>
            <?php (new Orders())->list('general'); ?>
        </div><!-- row -->
    </div><!-- container -->
</div><!-- my-profile-page-full -->


<div id="cancel-order-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title">
                    <?= $this->lang->line('cancel_order'); ?>
                </h4>
            </div>

            <div class="modal-body">

                <h5>
                    <?= $this->lang->line('cancel_order_question'); ?>
                </h5>

                <form id="cancel-order-form" method="POST" action="<?php echo BASE_URL;?>MyOrdersController/cancelOrder">
                    <div class="cancel-order-form">
                        
                        <div class="form-box">
                            <textarea class="form-control" name="cancel_reason" id="cancel_reason"
                                placeholder="<?= $this->lang->line('reason_cancellation'); ?>*"
                                required="required"></textarea>

                            <input type="hidden" id="order_id" name="order_id" value="">
                        </div><!-- form-box -->

                        <div class="signin-btn">
                            <input type="submit" class="black-btn blue-btn" name="submit" id="submit_cancel_order"
                                value="<?= $this->lang->line('confirm'); ?>">

                            <input type="button" class="black-btn blue-btn" data-dismiss="modal" name="cancel" id="cancel"
                                value="<?= $this->lang->line('cancel'); ?>">
                        </div><!-- signin-btn -->

                    </div><!-- sigin-form -->
                </form>

            </div>
        </div>

    </div>
</div>



<?php $this->load->view('common/footer'); ?>

<script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>my_orders.js?v=<?php echo CSSJS_VERSION; ?>"></script>

</body>
</html>
