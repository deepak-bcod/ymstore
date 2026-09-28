<?php $this->load->view('common/fbc-user/header'); ?> 
<script src="https://www.google.com/recaptcha/api.js" async defer></script>


<div class="container-fluid">
    <div class="row">

        <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h2 class="h3 mb-0 font-weight-bold text-dark" style="font-size: 24px;">
                        <?php echo $this->lang->line('faq_title'); ?>
                    </h2>
                </div>
                <div>
                     <a href="javascript:history.back()" class="back-link" style="float:right">
                        <?php echo $this->lang->line('back_to_list'); ?></a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12">
                    <div class="card p-4 p-md-5 border-0 merchant-faq-card">
                        
                        <h3 class="h4 font-weight-bold text-dark mb-4 text-center" style="font-size: 24px; letter-spacing: 0.5px; font-family: sans-serif;">
                            <?php echo $this->lang->line('faq_your_information'); ?>
                        </h3>
                            
                            <form method="POST" id="merchant-faq-submit-form" action="<?php echo base_url('merchantfaqs/save'); ?>">
    
                                <div class="form-row mb-4">
                                    <div class="form-group col-md-6 text-left">
                                        <label class="merchant-faq-label text-uppercase mb-2">
                                            <?php echo $this->lang->line('faq_name'); ?>
                                        </label>
                                        <input type="text" name="name" class="form-control font-weight-bold text-dark merchant-faq-input" 
                                            value="<?php echo htmlspecialchars($merchant_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            readonly>
                                    </div>
                                    <div class="form-group col-md-6 text-left">
                                        <label class="merchant-faq-label text-uppercase mb-2">
                                            <?php echo $this->lang->line('faq_email'); ?>
                                        </label>
                                        <input type="text" name="email" class="form-control font-weight-bold text-dark merchant-faq-input" 
                                           value="<?php echo htmlspecialchars($merchant_email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group mb-4 text-left">
                                    <label class="merchant-faq-label font-weight-bold text-uppercase mb-2">
                                        <?php echo $this->lang->line('faq_enter_question_en'); ?>
                                    </label>
                                    <textarea placeholder="<?php echo $this->lang->line('faq_placeholder_question'); ?>" class="form-control p-3 merchant-faq-textarea" rows="5" name="question" id="question"></textarea>
                                    <small class="text-danger error-message mt-1 d-block" id="question-error"></small>
                                </div>

                                <div class="form-group mb-4 text-left">
                                    <label class="merchant-faq-label font-weight-bold text-uppercase mb-2">
                                        <?php echo $this->lang->line('faq_enter_question_fr'); ?>
                                    </label>
                                    
                                    <textarea placeholder="<?php echo $this->lang->line('faq_placeholder_question'); ?>" class="form-control p-3 merchant-faq-textarea" rows="5" name="question_fr" id="question_fr"></textarea>
                                    <small class="text-danger error-message mt-1 d-block" id="question-error"></small>
                                </div>
<?php
$recaptcha_lang = ($this->session->userdata('site_lang') == 'french') ? 'fr' : 'en';
?>
<script src="https://www.google.com/recaptcha/api.js?hl=<?= $recaptcha_lang; ?>" async defer></script>

                                <div class="form-group mb-4 text-left">
                                    <div class="g-recaptcha" data-sitekey="6LedjPEsAAAAAPOJ5Q3mQCrQ_LQGVymdA_aFE3zt"></div>
                                    <small class="text-danger error-message mt-1 d-block" id="captcha-error"></small>
                                </div>

                                <div class="text-left pt-2">
                                    <button class="btn font-weight-bold text-uppercase merchant-faq-submit-btn shadow-sm" type="submit">
                                        <?php echo $this->lang->line('faq_submit'); ?>
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            
        </main>

    </div>
</div>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {

    const currentLang = "<?= $this->session->userdata('site_lang') ?? 'english'; ?>";
    const isFrench = currentLang === "french";

    const messages = {
        validationTitle: isFrench ? "Avis de validation" : "Validation Notice",
        validationText: isFrench
            ? "Veuillez saisir votre question avant de continuer."
            : "Please input your question content text field before proceeding.",

        captchaTitle: isFrench ? "Vérification CAPTCHA" : "Captcha Verification",
        captchaText: isFrench
            ? "Veuillez cocher la case de vérification reCAPTCHA avant de continuer."
            : "Please clear the reCAPTCHA verification checkbox to authorize entry.",

        successTitle: isFrench ? "Soumission réussie !" : "Submitted Successfully!",
        successText: isFrench
            ? "Votre FAQ a été soumise à l'administrateur pour examen."
            : "Your FAQ is submitted to the Admin for the Review.",

        errorTitle: isFrench ? "Erreur de traitement" : "Transaction Error",
        errorText: isFrench
            ? "Une erreur est survenue lors du traitement."
            : "Processing loop exception triggered.",

        systemTitle: isFrench ? "Erreur système" : "System Dispatch Fault",
        systemText: isFrench
            ? "Impossible de se connecter au serveur."
            : "Unable to connect to service endpoints."
    };

    const formElement = document.getElementById("merchant-faq-submit-form");
    if (!formElement) return;

    formElement.addEventListener("submit", function(event) {
        event.preventDefault();

        const questionField = document.getElementById("question");
        const questionValue = questionField.value ? questionField.value.trim() : "";
        
        /*
        if (questionValue === "") {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: messages.validationTitle,
                    text: messages.validationText,
                    confirmButtonColor: '#FFD400'
                });
            } else {
                alert(messages.validationText);
            }
            return;
        }
        */

        let captchaResponse = "";
        if (typeof grecaptcha !== 'undefined') {
            captchaResponse = grecaptcha.getResponse();

            if (captchaResponse.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: messages.captchaTitle,
                        text: messages.captchaText,
                        confirmButtonColor: '#FFD400'
                    });
                } else {
                    alert(messages.captchaText);
                }
                return;
            }
        }

        const payloadData = new FormData(formElement);

        fetch(formElement.getAttribute("action"), {
            method: "POST",
            body: payloadData
        })
        .then(response => {
            if (!response.ok) throw new Error("HTTP connection fault.");
            return response.json();
        })
        .then(data => {
            if (data.status === "success") {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: messages.successTitle,
                        text: messages.successText,
                        confirmButtonColor: '#FFD400',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "<?= base_url('merchantfaqs'); ?>";
                    });
                } else {
                    alert(messages.successText);
                    window.location.href = "<?= base_url('merchantfaqs'); ?>";
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: messages.errorTitle,
                        text: data.message || messages.errorText,
                        confirmButtonColor: '#000000'
                    });
                } else {
                    alert(data.message || messages.errorText);
                }
            }
        })
        .catch(err => {
            console.error("Runtime stream abort:", err);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: messages.systemTitle,
                    text: messages.systemText,
                    confirmButtonColor: '#000000'
                });
            } else {
                alert(messages.systemText);
            }
        });
    });

});
</script>