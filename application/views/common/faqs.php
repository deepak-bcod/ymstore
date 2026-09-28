<?php $this->load->view('common/header') ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>

<div class="main">
    <div class="container">

        <div class="row margin-bottom-40 faqs-page box-center">
            <div class="col-md-12">
                <h1><?= lang('faq_your_information') ?></h1>
            </div>

            <div class="col-md-12">
                <div class="content-page shadow faq-section-page">
                    <form method="POST" id="customer-personal-info-form"
                        action="<?php echo BASE_URL; ?>HomeController/faqs_post">

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= lang('faq_name') ?></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="<?= lang('faq_enter_name') ?>"
                                        value="<?= (isset($_SESSION) && isset($fnln)) ? $fnln : ''; ?>">
                                        <small class="text-danger error-message" id="name-error"></small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?= lang('faq_email') ?></label>
                                    <input type="text" class="form-control" id="email"
                                        placeholder="<?= lang('faq_enter_email') ?>" name="email"
                                        value="<?= (isset($_SESSION) && isset($_SESSION['EmailID'])) ? $_SESSION['EmailID'] : ''; ?>">
                                        <small class="text-danger error-message" id="email-error"></small>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?= lang('faq_enter_question') ?></label>
                                    <textarea placeholder="<?= lang('faq_question_placeholder') ?>" class="form-control"
                                        rows="5" name="question" id="question"></textarea>
                                        <small class="text-danger error-message" id="question-error"></small>

                                </div>
                            </div>

                        </div>

                        <div class="form-input">
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY_V2; ?>"></div>
                            <small class="text-danger error-message" id="captcha-error"></small>

                        </div>

                        <div class="row">
                            <div class="col-lg-12 padding-top-20">
                                <button class="btn btn-primary" type="submit"><?= lang('faq_submit') ?></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>

        <div class="row faqs-que-ans">
            <div class="col-md-12">
                <div class="table-responsive">
                    <h2><?= lang('faq_faqs_title') ?></h2>

                    <?php if (!empty($faq_list)): ?>
                        <?php
                        $site_lang = $this->session->userdata('site_lang') ?? 'english';
                        $is_french = ($site_lang == 'french' || $site_lang == 'fr');
                        ?>

                        <div class="accordion" id="accordionExample">
                            <?php $sr = 1; ?>

                            <?php foreach ($faq_list as $faq): ?>

                                <?php
                                $q_en = trim($faq->question ?? '');
                                $q_fr = trim($faq->question_fr ?? '');
                                $a_en = trim($faq->answer ?? '');
                                $a_fr = trim($faq->answer_fr ?? '');

                                // Language filtering and fallback logic
                                if ($is_french) {
                                    // French View: if no French question and no English question, skip
                                    if (empty($q_fr) && empty($q_en)) continue;

                                    $display_question = !empty($q_fr) ? $q_fr : $q_en;
                                    $display_answer   = !empty($a_fr) ? $a_fr : $a_en;
                                } else {
                                    // English View: do NOT display questions submitted exclusively in French ($q_en is empty)
                                    if (empty($q_en)) continue;

                                    $display_question = $q_en;
                                    $display_answer   = !empty($a_en) ? $a_en : $a_fr;
                                }

                                if (empty($display_question)) continue;

                                $headingId  = 'heading' . $sr;
                                $collapseId = 'collapse' . $sr;
                                $showClass  = ($sr === 1) ? 'show' : '';
                                $expanded   = ($sr === 1) ? 'true' : 'false';
                                ?>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="<?= $headingId; ?>">
                                        <button class="accordion-button <?= $showClass ? '' : 'collapsed'; ?>" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>"
                                            aria-expanded="<?= $expanded; ?>" aria-controls="<?= $collapseId; ?>">
                                            <strong><?= lang('faq_q') ?></strong>
                                            <span class="capitalize"><?= htmlspecialchars($display_question); ?></span>
                                        </button>
                                    </h2>

                                    <div id="<?= $collapseId; ?>" class="accordion-collapse collapse <?= $showClass; ?>"
                                        aria-labelledby="<?= $headingId; ?>" data-bs-parent="#accordionExample">
                                        <div class="accordion-body faq-ans">
                                            <strong><?= lang('faq_a') ?></strong>
                                            <span><?= htmlspecialchars_decode(html_entity_decode($display_answer)); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php $sr++; ?>

                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <p><?= lang('faq_no_faqs') ?></p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('common/footer') ?>

<script>
    const lang = {
        name_required:    "<?php echo $this->lang->line('err_name_required'); ?>",
        question_required:"<?php echo $this->lang->line('err_question_required'); ?>",
        captcha_required: "<?php echo $this->lang->line('err_captcha_required'); ?>",
        email_required:   "<?php echo $this->lang->line('err_email_required'); ?>",
        email_invalid:    "<?php echo $this->lang->line('err_email_invalid'); ?>",
        name_min_chars:   "<?php echo $this->lang->line('err_name_min_chars') ?: 'Name must be at least 3 characters.'; ?>",
        submitting:       "<?php echo $this->lang->line('faq_submitting') ?: 'Submitting...'; ?>",
        submit_ticket:    "<?php echo $this->lang->line('faq_submit'); ?>",
        success_title:    "<?php echo $this->lang->line('faq_success_title') ?: 'Success!'; ?>",
        error_title:      "<?php echo $this->lang->line('faq_error_title') ?: 'Error'; ?>",
        oops_title:       "<?php echo $this->lang->line('faq_oops_title') ?: 'Oops!'; ?>",
        server_error:     "<?php echo $this->lang->line('faq_server_error') ?: 'Server error. Please try again.'; ?>"
    };
    $(document).ready(function () {

    // ==========================
    // Function to show error
    // ==========================
    function showError(input, message) {
        $(input).addClass('is-invalid');

        // Remove old feedback if exists
        $(input).next('.invalid-feedback').remove();

        // Add new feedback
        $(input).after('<div class="invalid-feedback">' + message + '</div>');
    }

    // ==========================
    // Function to remove error
    // ==========================
    function removeError(input) {
        $(input).removeClass('is-invalid');
        $(input).next('.invalid-feedback').remove();
    }

    // ==========================
    // Live validation (hide error while typing)
    // ==========================

    $('#name').on('input', function () {
        if ($(this).val().trim().length >= 3) {
            removeError(this);
        }
    });

    $('#email').on('input', function () {
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailPattern.test($(this).val().trim())) {
            removeError(this);
        }
    });

    $('#question').on('input', function () {
        if ($(this).val().trim().length >= 10) {
            removeError(this);
        }
    });

    // ==========================
    // Form Submit
    // ==========================

    $('#customer-personal-info-form').on('submit', function (e) {
        e.preventDefault();

        let isValid = true;

        let name = $('#name').val().trim();
        let email = $('#email').val().trim();
        let question = $('#question').val().trim();
        let captcha = grecaptcha.getResponse();

        // Clear old errors
        removeError('#name');
        removeError('#email');
        removeError('#question');

        // ==========================
        // Name Validation
        // ==========================
        if (name === '') {
            showError('#name', lang.name_required);
            isValid = false;
        } else if (name.length < 3) {
            showError('#name', lang.name_min_chars);
            isValid = false;
        }

        // ==========================
        // Email Validation
        // ==========================
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === '') {
            showError('#email', lang.email_required);
            isValid = false;
        } else if (!emailPattern.test(email)) {
            showError('#email', lang.email_invalid);
            isValid = false;
        }

        // ==========================
        // Question Validation
        // ==========================
        if (question === '') {
            showError('#question', lang.question_required);
            isValid = false;
        }

        // ==========================
        // Captcha Validation
        // ==========================
        if (captcha.length === 0) {

            // Remove old captcha error
            $('.g-recaptcha').next('.invalid-feedback').remove();

            // Add red border
            $('.g-recaptcha').css('border', '1px solid red');
            $('.g-recaptcha').css('padding', '5px');

            // Add error message
            $('.g-recaptcha').after('<div class="invalid-feedback d-block">' + lang.captcha_required + '</div>');

            isValid = false;

        } else {

            // Remove error if captcha is completed
            $('.g-recaptcha').css('border', 'none');
            $('.g-recaptcha').next('.invalid-feedback').remove();
        }


        if (!isValid) {
            return;
        }

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',

            beforeSend: function () {
                $('#customer-personal-info-form button[type="submit"]')
                    .prop('disabled', true)
                    .text(lang.submitting);
            },

            success: function (response) {

                if (response.flag == 1) {

                    swal({
                        title: lang.success_title,
                        text: response.msg,
                        icon: "success",
                        buttons: false,
                        timer: 1500
                    }).then(() => {
                        $('#customer-personal-info-form')[0].reset();
                        grecaptcha.reset();
                        removeError('#name');
                        removeError('#email');
                        removeError('#question');
                    });

                } else {

                    swal({
                        title: lang.error_title,
                        text: response.msg,
                        icon: "error",
                        button: "OK"
                    });
                }
            },

            error: function () {

                swal({
                    title: lang.oops_title,
                    text: lang.server_error,
                    icon: "error",
                    button: "OK"
                });
            },

            complete: function () {
                $('#customer-personal-info-form button[type="submit"]')
                    .prop('disabled', false)
                    .text(lang.submit_ticket);
            }
        });

    });

});


</script>