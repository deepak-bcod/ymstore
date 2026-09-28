<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page faq-top-page">


    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



    <form method="post" action="<?= base_url('CustomerController/update_faqs') ?>">
        <input type="hidden" name="id" value="<?= $faqs['id']; ?>">

        <div class="form-group row">
            <label class="col-sm-2 col-form-label font-500">FAQ Type</label>
            <div class="col-sm-4">
                <?php $current_type = !empty($faqs['faq_type']) ? ucfirst($faqs['faq_type']) : 'Merchant'; ?>
                <select name="faq_type" class="form-control">
                    <option value="Merchant" <?= ($current_type == 'Merchant') ? 'selected' : ''; ?>>Merchant FAQ</option>
                    <option value="Shopper" <?= ($current_type == 'Shopper') ? 'selected' : ''; ?>>Shopper FAQ</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= $faqs['name']; ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= $faqs['email']; ?>" readonly>
        </div>

        <div class="form-group question-section">
            <label>Question (English)</label>
            <textarea name="question" class="form-control" rows="3"><?= $faqs['question']; ?></textarea>
        </div>

        <div class="form-group question-section">
            <label>Question (French)</label>
            <textarea name="question_fr" class="form-control" rows="3"><?= $faqs['question_fr']; ?></textarea>
        </div>

        <div class="form-group question-section">
            <label>Answer (English)</label>
            <textarea name="answer" id="answer" class="form-control" rows="5"><?= $faqs['answer']; ?></textarea>
        </div>

        <div class="form-group question-section">
            <label>Answer (French)</label>
            <textarea name="answer_fr" id="answer_fr" class="form-control" rows="5"><?= $faqs['answer_fr']; ?></textarea>
        </div>

        <div class="form-group row">
            <label for="" class="col-sm-2 col-form-label font-500">Status</label>
            <div class="col-sm-4">
                <?php $status = $faqs['status']; ?>
                <input type="radio" name="status" value="0" <?php if ($status == 0) echo "checked"; ?>>
                <label class="mr-3">Pending</label>
                <input type="radio" name="status" value="1" <?php if ($status == 1) echo "checked"; ?>>
                <label class="mr-3">Approve / Active</label>
                <input type="radio" name="status" value="2" <?php if ($status == 2) echo "checked"; ?>>
                <label>Reject</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>


</main>
<script type="text/javascript">
    $(function () {
        CKEDITOR.replace('answer', {
            versionCheck: false, // Hides the red alert
            extraPlugins: 'justify',
            extraAllowedContent: "span(*)",
            allowedContent: true,
        });

        CKEDITOR.replace('answer_fr', {
            versionCheck: false, // Hides the red alert
            extraPlugins: 'justify',
            extraAllowedContent: "span(*)",
            allowedContent: true,
        });

        CKEDITOR.dtd.$removeEmpty.span = 0;
        CKEDITOR.dtd.$removeEmpty.i = 0;
    });
</script>



<?php $this->load->view('common/fbc-user/footer'); ?>

