<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard faq-top-section">
        
        <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show text-left" role="alert" style="margin-bottom: 20px;">
                <strong>Success!</strong> <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show text-left" role="alert" style="margin-bottom: 20px;">
                <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>FAQs</h2>
            <a href="<?= base_url('faqs/add'); ?>" class="btn btn-primary">+ Add New FAQ</a>
        </div>

        <div class="table-responsive text-center">

            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>SR No</th>
                        <th>Name</th>
                        <th>Question (EN)</th>
                        <th>Question (FR)</th>
                        <th>FAQ Type</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                
                    <?php $sr = count($faqs); ?>
                    <?php foreach($faqs as $faq): ?>
                        <tr>
                            <td><?= $sr--; ?></td>
                            <td><?= $faq['name']; ?></td>
                            <td><?= $faq['question']; ?></td>
                            <td><?= $faq['question_fr']; ?></td>
                            <td>
                                <?php 
                                   
                                    if (!empty($faq['faq_type'])) {
                                        echo ucfirst($faq['faq_type']); 
                                    } 
                                    
                                    else {
                                        if (strtolower($faq['name']) == 'snehal' || strpos($faq['email'], 'snehal') !== false) {
                                            echo 'Merchant';
                                        } else {
                                            echo 'Shopper';
                                        }
                                    }
                                ?>
                            </td>
                            <td><?= $faq['email']; ?></td>
                            <td>
                                <?php
                                    if ($faq['status'] == 0) {
                                        echo 'Pending';
                                    } elseif ($faq['status'] == 1) {
                                        echo 'Approved';
                                    } elseif ($faq['status'] == 2) {
                                        echo 'Rejected';
                                    } else {
                                        echo 'Unknown';
                                    }
                                ?>
                            </td>

                            <td>
                                <a href="<?= base_url('faqs/edit/'.$faq['id']); ?>" class="btn btn-sm btn-primary">Edit / Reply</a>
                                <a href="<?= base_url('faqs/delete/'.$faq['id']); ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Are you sure you want to delete this FAQ?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<?php if($this->session->flashdata('success')): ?>
    <script type="text/javascript">
        alert("<?= $this->session->flashdata('success') ?>");
    </script>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <script type="text/javascript">
        alert("<?= $this->session->flashdata('error') ?>");
    </script>
<?php endif; ?>
<?php $this->load->view('common/fbc-user/footer'); ?>