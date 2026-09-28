<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>


    <?php 
$site_lang = $this->session->userdata('site_lang') ?: 'english';
$months_fr = [
    'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
    'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
    'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'
];
$format_date = function($ts) use ($site_lang, $months_fr) {
    if (empty($ts)) return '';
    $d = date('d M Y, H:i', $ts);
    return ($site_lang == 'french') ? strtr($d, $months_fr) : $d;
};

if(!empty($help_desk_data)): ?>
        <?php 
        $first_ticket = $help_desk_data[0]; 
        //echo "<pre>";print_r($first_ticket);
        $product = ($first_ticket->products != '') ? $first_ticket->products : 0;
        $back_url = (!empty($first_ticket->category) && $first_ticket->category > 2) ? base_url('help_desk/merchant') : base_url('help_desk/shopper');
        ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
    <strong><?= $this->lang->line('ticket_label'); ?> <?= $first_ticket->ticket_id; ?></strong>
    
    <a href="<?= $back_url; ?>" 
       style="text-decoration: none; color: #000000; font-weight: bold; border-bottom: 2px solid #555; padding-bottom: 2px;">
        <?= lang('back_list'); ?>
    </a>
</div>
            <input type="hidden" value="<?= $first_ticket->merchant_id; ?>" id="merchant_id" name="merchant_id">

            <div class="card-body">
                
                <p>
                    <strong><?= $this->lang->line('order_label'); ?>:</strong> <?= !empty($order) ? $order->increment_id : (!empty($first_ticket->order_id) ? $first_ticket->order_id : 'N/A'); ?> 
                    | <strong><?= $this->lang->line('product_label'); ?>:</strong> <?= !empty($product) ? $product->product_name : (!empty($first_ticket->products) ? $first_ticket->products : 'N/A'); ?>
                </p>

                <br/>
                
                <?php if(!empty($first_ticket->attachment)): ?>
                <p>
                    <a href="<?= BASE_URL2 . 'uploads/help_desk_attachment/' . $first_ticket->attachment; ?>" target="_blank"><?= $this->lang->line('attachment_label'); ?></a>
                </p>
                <?php endif; ?>   

                <hr>
                <h5><?= $this->lang->line('conversation_label'); ?> :</h5>
                <ul class="list-unstyled">
                    <?php foreach($help_desk_data as $msg): ?>
                        <?php if(!empty($msg->message)): ?>
                            <li class="mb-3">
                                <div class="bg-light p-2 rounded">
                                    <?php if (!empty($msg->category > 2)) : ?>
                                        <strong><?= $this->lang->line('merchant_label'); ?></strong>
                                    <?php else : ?>
                                        <strong><?= $this->lang->line('shopper_label'); ?></strong>
                                    <?php endif; ?>
                                   <small class="text-muted">(<?= $format_date($msg->created_at); ?>)</small>
                                    <p><?= nl2br($msg->message); ?></p>
                                </div>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($msg->admin_reply)): ?>
                            <li class="mb-3">
                                <div class="bg-primary text-white p-2 rounded">
                                    <?php if (!empty($msg->category > 2)) : ?>
                                        <strong><?= $this->lang->line('admin_label'); ?></strong>
                                    <?php else : ?>
                                        <strong><?= $this->lang->line('merchant_label'); ?></strong>
                                    <?php endif; ?>
                                    <small class="">(<?= $format_date($msg->updated_at); ?>)</small>
                                    <p><?= nl2br($msg->admin_reply); ?></p>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </ul>

                <!-- Admin Reply Form -->
                <form method="POST" action="<?= base_url('UserController/update_help_desk'); ?>" class="mt-3">
                    <input type="hidden" name="id" value="<?= $first_ticket->id; ?>">
                    <input type="hidden" value="<?= $first_ticket->merchant_id; ?>" id="merchant_id" name="merchant_id">
                    <input type="hidden" value="<?= $first_ticket->ticket_id; ?>" id="ticket_id" name="ticket_id">
                    <input type="hidden" value="<?= $first_ticket->category; ?>" id="category" name="category">

                    <div class="form-group">
                        <label><?= $this->lang->line('reply_label'); ?></label>
                        <textarea name="admin_reply" class="form-control" rows="3" required oninvalid="this.setCustomValidity('<?= lang('please_fill_out_this_field') ?>')" oninput="this.setCustomValidity('')"></textarea>
                    </div>
                    <div class="d-flex">
    <button type="submit" class="btn btn-primary btn-sm mr-2">
        <?= $this->lang->line('send_reply_label'); ?>
    </button>

    <?php if ($first_ticket->status != 2 && $first_ticket->category <= 2): ?>
        <a href="<?= base_url('UserController/close_ticket/' . $first_ticket->order_id . '/' . $product . '/' . $first_ticket->ticket_id); ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('<?= lang('are_you_sure_close_ticket'); ?>')">
           <?= lang('mark_as_close'); ?> 
        </a>
    <?php endif; ?>
</div>

                </form>

            </div>
        </div>
    <?php else: ?>
        <p><?= $this->lang->line('no_conversation_label'); ?></p>
    <?php endif; ?>



</main>



<?php $this->load->view('common/fbc-user/footer'); ?>
