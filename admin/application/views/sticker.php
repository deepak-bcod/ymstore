<?php $this->load->view('common/fbc-user/header'); ?> 
<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page faq-top-page">
    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>
    <form method="post" action="<?= base_url('sticker/update/1') ?>">
        
        <div class="form-group question-setion">
            <label>Sticker Text English</label>
            <textarea name="text" class="form-control" rows="4"><?= $sticker->text; ?></textarea>
        </div>

        <div class="form-group question-setion">
            <label>Sticker Text French</label>
            <textarea name="text_fr" class="form-control" rows="4"><?= $sticker->text_fr; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>

