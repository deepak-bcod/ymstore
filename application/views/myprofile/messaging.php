<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?= lang('home') ?></a></li>
                <li class="active"><?= lang('messaging.title') ?></li>
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
                            <h1><?= lang('messaging.header') ?></h1>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
								<div class="message-list-table">
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th><?= lang('messaging.sr_no') ?></th>
                                            <th><?= lang('messaging.name') ?></th>
                                            <th><?= lang('messaging.category') ?></th>
                                            <th><?= lang('messaging.message') ?></th>
                                            <th><?= lang('messaging.status') ?></th>
                                            <th><?= lang('messaging.actions') ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $sr = 1; ?>
                                        <?php foreach ($messaging_data as $msg): ?>
                                            <tr>
                                                <td><?= $sr++; ?></td>
                                                <td><?= $msg->name; ?></td>
                                                <td><?= $msg->category; ?></td>
                                                <td><?= $msg->message; ?></td>
                                                
<td>           
    <?php 
        $status = strtolower($msg->status);
        // Check if the specific row has a reply OR if the thread has replies
        $has_reply = (!empty($msg->merchant_reply) || (isset($msg->reply_count) && $msg->reply_count > 0));
        $is_read = ($msg->is_read == 1);

        if ($status == 'closed') {
            echo '<span style="color: #28a745;">Closed</span>';
        } 
        elseif ($has_reply || $is_read || $status == 'answered') {
            echo '<span style="color: #007bff;">Answered</span>';
        } 
        else {
            echo '<span style="color: #dc3545;">Not Answered</span>';
        }
    ?>
</td>
                                                <td>
                                                    <a href="<?= base_url('MyProfileController/viewMessage/' . $msg->product_id); ?>">
                                                        <?= lang('messaging.view_messages') ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
								</div>

                            </div>
                        </div>

                    </div><!-- .content-page ends -->

                </div>
            </div><!-- row -->

        </div><!-- container -->
    </div><!-- my-profile-page-full -->

    <?php $this->load->view('common/footer'); ?>
</body>
</html>
