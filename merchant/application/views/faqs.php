<?php $this->load->view('common/fbc-user/header'); ?> 


<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h2 class="h3 mb-0 font-weight-bold text-dark" style="font-size: 24px;">
                    <?php echo $this->lang->line('faq_title'); ?>
                </h2>
            </div>
            <div>
                <a href="<?php echo base_url('merchantfaqs/add'); ?>" class="btn btn-dark font-weight-bold px-4 py-2" style="border-radius: 4px; background-color: #000; color: #fff; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                    <?php echo $this->lang->line('btn_faq_form'); ?>
                </a>
            </div>
        </div>

        <div id="customMerchantFaqAccordion">
            <?php if(!empty($faq_list)): ?>
                <?php foreach($faq_list as $faq): ?>
                    
                    <div class="faq-card">
                        <div class="faq-card-header">
                            <button class="faq-btn" type="button">
                                <?php 
                                    $is_french = ($this->session->userdata('site_lang') == "french");
                                    $q_text = $is_french ? ($faq['question_fr'] ?: $faq['question']) : ($faq['question'] ?: $faq['question_fr']);
                                ?>
                                <span>Q. <?= htmlspecialchars($q_text); ?></span>
                                <i class="fas fa-chevron-down faq-toggle-icon"></i>
                            </button>
                        </div>

                        <div class="faq-collapse">
                            <div class="faq-body">
                                <?php 
                                    $answer_text = $is_french ? ($faq['answer_fr'] ?: $faq['answer']) : ($faq['answer'] ?: $faq['answer_fr']);
                                    $answer_text = nl2br(html_entity_decode($answer_text, ENT_QUOTES, 'UTF-8'));
                                ?>
                                <div class="d-flex align-items-start">
                                    <strong class="text-dark me-2">A.</strong>
                                    <span><?= $answer_text; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center py-3" style="border-radius: 4px;">
                    No approved FAQ answers found matching database queries.
                </div>
            <?php endif; ?>
        </div>

    
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const accordion = document.getElementById("customMerchantFaqAccordion");
        if (!accordion) return;

        const cards = accordion.querySelectorAll(".faq-card");

        cards.forEach(card => {
            const button = card.querySelector(".faq-btn");
            const collapsePanel = card.querySelector(".faq-collapse");

            button.addEventListener("click", function(e) {
                e.preventDefault();
                
                const isCurrentlyActive = card.classList.contains("active");

                // Auto-collapses matching elements when a non-active row is toggled
                cards.forEach(otherCard => {
                    if (otherCard !== card) {
                        otherCard.classList.remove("active");
                        otherCard.querySelector(".faq-collapse").style.maxHeight = null;
                    }
                });

                if (isCurrentlyActive) {
                    card.classList.remove("active");
                    collapsePanel.style.maxHeight = null;
                } else {
                    card.classList.add("active");
                    collapsePanel.style.maxHeight = collapsePanel.scrollHeight + "px";
                }
            });
        });
    });
</script>