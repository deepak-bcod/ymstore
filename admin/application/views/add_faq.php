<?php $this->load->view('common/fbc-user/header'); ?> 

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page faq-top-page">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New FAQ</h2>
        <a href="<?= base_url('faqs'); ?>" class="btn btn-secondary">Back to FAQs</a>
    </div>

    <form method="post" action="<?= base_url('CustomerController/faq_save') ?>">
        
        <div class="form-group row">
            <label class="col-sm-2 col-form-label font-500">FAQ Type <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <select name="faq_type" class="form-control" required>
                    <option value="Merchant" selected>Merchant FAQ</option>
                    <option value="Shopper">Shopper FAQ</option>
                </select>
            </div>
        </div>

        <div class="form-group question-section">
            <label class="font-500">Question (English) <span class="text-danger">*</span></label>
            <textarea name="question" class="form-control" rows="3" required placeholder="Enter question in English"></textarea>
        </div>

        <div class="form-group question-section">
            <label class="font-500">Question (French)</label>
            <textarea name="question_fr" class="form-control" rows="3" placeholder="Enter question in French"></textarea>
        </div>

        <div class="form-group question-section">
            <label class="font-500">Answer (English) <span class="text-danger">*</span></label>
            <textarea name="answer" id="answer" class="form-control" rows="5" placeholder="Enter answer in English"></textarea>
        </div>

        <div class="form-group question-section">
            <label class="font-500">Answer (French)</label>
            <textarea name="answer_fr" id="answer_fr" class="form-control" rows="5" placeholder="Enter answer in French"></textarea>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label font-500">Status</label>
            <div class="col-sm-4">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_active" value="1" checked>
                    <label class="form-check-label" for="status_active">Approve / Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_pending" value="0">
                    <label class="form-check-label" for="status_pending">Pending</label>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-5">
            <button type="submit" class="btn btn-primary">Save FAQ</button>
            <a href="<?= base_url('faqs'); ?>" class="btn btn-secondary ml-2">Cancel</a>
        </div>

    </form>

</main>

<script type="text/javascript">
    $(function () {
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('answer', {
                versionCheck: false,
                extraPlugins: 'justify',
                extraAllowedContent: "span(*)",
                allowedContent: true,
            });

            CKEDITOR.replace('answer_fr', {
                versionCheck: false,
                extraPlugins: 'justify',
                extraAllowedContent: "span(*)",
                allowedContent: true,
            });

            CKEDITOR.dtd.$removeEmpty.span = 0;
            CKEDITOR.dtd.$removeEmpty.i = 0;
        }
    });
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
