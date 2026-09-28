<?php
  $current_lang = $this->session->userdata('site_lang') ?? 'english';

?>
<?php if($current_lang == "french") { ?>
<marquee>
  <p>
    <span><em><strong><?= $this->lang->line('marquee_message'); ?></strong></em></span>
  </p>
</marquee>
<?php } else { ?>

<marquee>
  <p>
    <span><em><strong><?= $this->lang->line('marquee_message'); ?></strong></em></span>
  </p>
</marquee>

<?php } ?>