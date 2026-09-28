<footer class="page-footer">
  <div class="footer-container footer-style-1">
    <div class="footer-wrapper">
      <div class="footer-middle">
        <div class="container">
          <div class="middle-block">
            <div class="row">
              <!-- CONDITIONS -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('conditions') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/terms-conditions" target="_blank" rel="noopener"><?= lang('terms_conditions') ?></a></li>
                      <li><a href="/page/privacy-policy" target="_blank" rel="noopener"><?= lang('privacy_policy') ?></a></li>
                      <li><a href="/page/delivery-policy" target="_blank" rel="noopener"><?= lang('delivery_policy') ?></a></li>
                      <li><a href="/page/return-policy" target="_blank" rel="noopener"><?= lang('return_policy') ?></a></li>
                      <li><a href="/page/refund-and-replacements" target="_blank" rel="noopener"><?= lang('refund_guide') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- HELP YOU -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('help_you') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/support" target="_blank"><?= lang('support') ?></a></li>
                      <li><a href="/customer/account/"><?= lang('my_account') ?></a></li>
                      <li><a href="/customer/my-orders"><?= lang('my_orders') ?></a></li>
                      <li><a href="/merchants/register" target="_blank"><?= lang('join_marketplace') ?></a></li>
                      <li><a href="<?= BASE_URL; ?>faqs" target="_blank"><?= lang('faq') ?></a></li>
                      <li><a href="https://yellowmarkets.com/contact-us.html" target="_blank"><?= lang('contact_us') ?></a></li>

                    </ul>
                  </div>
                </div>
              </div>

              <!-- INFORMATION -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('information') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/about-us"><?= lang('about_us') ?></a></li>
                      <li><a href="/page/career" target="_blank"><?= lang('careers') ?></a></li>
                      <li><a href="<?= BASE_URL; ?>blogs" target="_blank"><?= lang('blogs') ?></a></li>
                      <li><a href="/page/competitions" target="_blank"><?= lang('competitions') ?></a></li>
                      <li><a href="/page/delivery-information" target="_blank"><?= lang('delivery_info') ?></a></li>
                      <li><a href="/page/press-and-news" target="_blank"><?= lang('news_press') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- MERCHANTS -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('our_merchants') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/daily-deals/"><?= lang('daily_deals') ?></a></li>
                      <li><a href="/flash-sale"><?= lang('flash_sale') ?></a></li>
                      <li><a href="/trending-products"><?= lang('trending_products') ?></a></li>
                      <li><a href="/giftcards/"><?= lang('gift_cards') ?></a></li>
                      <li><a href="<?= BASE_URL; ?>shops"><?= lang('merchants_directory') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- NEWSLETTER -->
              <div class="col-xl-4 col-lg-12 col-md-12">
                <div class="block-footer">
                  <?php
                  $shopcode = SHOPCODE;
                  $website_texts = HomeDetailsRepository::get_website_texts(SHOPCODE);
                  ?>
                  
                  <h2>
                    <?php
                       echo lang('newsletter');
                   /* if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) {
                      echo (($website_texts->FbcWebsiteTexts)->newsletter_title) ?: lang('newsletter');
                    } else {
                      echo lang('newsletter');
                    }*/
                    ?>
                  </h2>

                  <?php
                  if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200 && (($website_texts->FbcWebsiteTexts)->newsletter_message) != '') {
                    echo (($website_texts->FbcWebsiteTexts)->newsletter_message);
                  }
                  ?>

                  <div class="block-footer-content">
                    <p class="newsletter-description">
                      <?= lang('newsletter_description') ?? "Enter your email address for our mailing list to keep yourself updated" ?>
                    </p>
                    <div class="block-subscribe-footer">
                      <form id="newsletter-subscribe-form" action="<?= BASE_URL; ?>newsletter" method="POST" novalidate>
                        <div class="input-group">
                          <input type="email" placeholder="<?= lang('newsletter_placeholder') ?>" class="form-control" id="newsletter-loader" name="email_subscribe">
                          <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit" name="email-subscribe-btn" value="Send">
                              <?= lang('newsletter_subscribe') ?>
                            </button>

                          </span>
                        </div>
                        <!-- <div class="email-subscribe-error"></div> -->
                      </form>
                      <div class="subscribe_result" id="subscribe_result" style="color: #198754;font-weight: bold;"></div>
                    </div>
                  </div>

                  <br><br>
                  <div class="block-footer-title dummy-text-online"><?= lang('become_merchant') ?></div>
                  <!-- <a href="/merchants/register/">
                    <img src="<?php // TEMP_SKIN_IMG.'/new/online-merchant-signup-517x70.jpg'; ?>" alt="merchant signup">
                  </a> -->
                  <?php 
                  // echo "<pre>"; print_r($navCatData); die;
                  $lang = $this->session->userdata('site_lang'); // or whatever key you use for language
                  // print_r($lang);die;
                  ?>
                  <div class="fot-section m-none">
                    <?php if ($lang == 'french') { ?>
                      <iframe id='a206ed74' name='a206ed74' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2536&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='409' height='55' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a0aaa6ef&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2536&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=a0aaa6ef' border='0' alt='' /></a></iframe>
                    <?php } else { ?>
                      <iframe id='a4769b24' name='a4769b24' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2296&amp;cb=INSERT_RANDOM_NUMBER_H… frameborder='0' scrolling='no' width='409' height='55' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a2a00fda&amp;cb=INSERT_RANDOM_NUMBER_HER… target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2296&amp;cb=INSERT_RANDOM_NUMBER_H… border='0' alt='' /></a></iframe>
                    <?php } ?>
                    
                  </div>
                  <div class="fot-section d-none">
                    <?php if ($lang == 'french') { ?>
                      <iframe id='abc39254' name='abc39254' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2537&amp;cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=aa5c893e&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2537&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=aa5c893e' border='0' alt='' /></a></iframe>
                    <?php } else { ?>
                      <iframe id='a86323c9' name='a86323c9' src='https://mauritiusadvertising.com/www/delivery/afr.php?zoneid=2523&cb=INSERT_RANDOM_NUMBER_HERE' frameborder='0' scrolling='no' width='320' height='50' allow='autoplay'><a href='https://mauritiusadvertising.com/www/delivery/ck.php?n=a1f75fc4&cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='https://mauritiusadvertising.com/www/delivery/avw.php?zoneid=2523&cb=INSERT_RANDOM_NUMBER_HERE&n=a1f75fc4' border='0' alt='' /></a></iframe>
                    <?php } ?>
                   
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FOOTER BOTTOM -->
        <div class="footer-bottom">
          <div class="container">
            <div class="row">
              <div class="col-lg-4">
                <div class="footer-payment">
                  <p><img src="<?= TEMP_SKIN_IMG.'/new/payment-footer.jpg'; ?>" loading="lazy" alt="Payment"></p>
                </div>
              </div>
              <div class="col-lg-8">
                <div class="copyright-footer">
                 <address><?= $this->lang->line('footer_text'); ?> © <?= date('Y'); ?> Yellow Markets. <?= $this->lang->line('all_rights_reserved'); ?></address>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</footer>

<!-- MODALS -->
<div id="WebShopCommonModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?= lang('modal_heading') ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <center><div class="spinner-border text-primary" role="status"></div></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal"><?= lang('close') ?></button>
        <button type="button" class="btn btn-primary"><?= lang('save_changes') ?></button>
      </div>
    </div>
  </div>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" id="quick-modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">X</button>

            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>


<div id="WebShopSecondaryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modal-content-second">
      <div class="modal-header">
        <h4 class="modal-title"><?= lang('modal_heading') ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <center><div class="spinner-border text-primary" role="status"></div></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal"><?= lang('close') ?></button>
        <button type="button" class="btn btn-primary"><?= lang('save_changes') ?></button>
      </div>
    </div>
  </div>
</div>

<!-- Spinner -->
<div class="ajax-spinner" id="ajax-spinner">
  <div class="ajax-spinner-inner"></div>
</div>
<!-- 1. jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. jQuery Validate -->
<script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
  if (typeof $.validator !== 'undefined' && typeof $.validator.messages !== 'undefined') {
    $.extend($.validator.messages, {
      required: "<?= addslashes(lang('required_field')); ?>"
    });
  }
</script>

<script>
$(function () {

    var form = $("#newsletter-subscribe-form");

    // Get current language
    var siteLang = "<?= $this->session->userdata('site_lang'); ?>";

    var validationMessages;

    if (siteLang === "french") {
        validationMessages = {
            required: "L’adresse e-mail est obligatoire.",
            email: "Veuillez saisir une adresse e-mail valide."
        };
    } else {
        validationMessages = {
            required: "Email is required.",
            email: "Please enter a valid email address."
        };
    }

    if (form.data("validator")) {

        form.validate().settings.messages.email_subscribe = validationMessages;

    } else {

        form.validate({
            rules: {
                email_subscribe: {
                    required: true,
                    email: true
                }
            },

            messages: {
                email_subscribe: validationMessages
            },

            errorElement: "span",
            errorClass: "text-danger"
        });
    }

});
</script>

<?php $this->template->load('common_for_all/header_script'); ?>

</body>
</html>
