<?php $this->load->view('common/fbc-user/header'); ?>
<style>
    .plan-table thead th {
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: 600;
        font-size: 16px;
    }
</style>

<link href="<?php echo SKIN_CSS; ?>dashboard1.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

<!-- Flash messages (auto-dismissed) -->
  <?php if($this->input->get('payment') === 'success' || $this->session->flashdata('payment') === 'success' || $this->session->flashdata('payment_status') === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3 alert-auto-dismiss" role="alert">
        <?php echo lang('payment_success'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Error Message (auto-dismissed) -->
<?php if($this->input->get('payment') === 'error' || $this->session->flashdata('payment') === 'error' || $this->session->flashdata('payment_status') === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3 alert-auto-dismiss" role="alert">
        <?php echo lang('payment_failed'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php 
$days_remaining = null;
if (!empty($active_plan['end_date'])) {
    $today_ts = strtotime(date('Y-m-d'));
    $end_ts   = strtotime(date('Y-m-d', strtotime($active_plan['end_date'])));
    $days_remaining = (int)round(($end_ts - $today_ts) / 86400);
}
$is_fr = ($this->session->userdata('site_lang') == 'french');
?>

<!-- Static Alerts: Expired or Remaining Expiry Warnings -->
<?php if(!empty($is_expired)): ?>
    <div class="alert alert-danger fade show mt-3 alert-static" role="alert">
        <i class="fa fa-exclamation-triangle mr-2"></i>
        <?= $is_fr 
            ? "Votre abonnement a expiré et votre boutique est actuellement désactivée sur le site. Veuillez renouveler votre abonnement ci-dessous afin de réactiver votre boutique et de rétablir un accès complet." 
            : "Your subscription has expired and your shop is currently disabled on the website. Please renew your subscription below to reactivate your shop and restore full access."; ?>
    </div>
<?php elseif($days_remaining !== null && $days_remaining <= 7 && $days_remaining >= 0): ?>
    <div class="alert alert-danger fade show mt-3 alert-static" role="alert">
        <i class="fa fa-exclamation-triangle mr-2"></i>
        <?= $is_fr 
            ? "Urgent : Votre abonnement Yellow Markets expire dans 1 semaine (" . ($days_remaining == 1 ? "1 jour" : $days_remaining . " jours") . ") le " . date('d M Y', strtotime($active_plan['end_date'])) . ". Veuillez renouveler votre abonnement ci-dessous afin d'assurer la continuité de vos services."
            : "Urgent: Your Yellow Markets Subscription expires in 1 week (" . ($days_remaining == 1 ? "1 day" : $days_remaining . " days") . ") on " . date('d M Y', strtotime($active_plan['end_date'])) . ". Please renew your subscription below to ensure uninterrupted service."; ?>
    </div>
<?php elseif($days_remaining !== null && $days_remaining <= 30 && $days_remaining > 7): ?>
    <div class="alert alert-warning fade show mt-3 alert-static" role="alert">
        <i class="fa fa-info-circle mr-2"></i>
        <?= $is_fr 
            ? "Action requise : Votre abonnement Yellow Markets expire dans " . $days_remaining . " jours le " . date('d M Y', strtotime($active_plan['end_date'])) . ". Veuillez renouveler votre abonnement ci-dessous afin d'assurer la continuité de vos services."
            : "Action Required: Your Yellow Markets Subscription expires in " . $days_remaining . " days on " . date('d M Y', strtotime($active_plan['end_date'])) . ". Please renew your subscription below to ensure uninterrupted service."; ?>
    </div>
<?php endif; ?>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-4 mb-3">
        <h4 class="mb-0"><?php echo lang('choose_subscription_plan'); ?></h4> 
        <?php if(!empty($is_expired)): ?>
            <div class="text-center flex-grow-1">
                <span class="font-weight-bold" style="color: red !important; font-size: 16px; text-decoration: underline;">
                    <?= $is_fr 
                        ? 'Veuillez renouveler votre compte pour accéder au portail.' 
                        : 'Please Renew your account to access the Portal.'; ?>
                </span>
            </div>
        <?php elseif($days_remaining !== null && $days_remaining <= 7 && $days_remaining >= 0): ?>
            <div class="text-center flex-grow-1">
                <span class="font-weight-bold" style="color: #dc3545 !important; font-size: 16px; text-decoration: underline;">
                    <?= $is_fr 
                        ? "Urgent : Votre abonnement Yellow Markets expire dans 1 semaine le " . date('d M Y', strtotime($active_plan['end_date'])) . ". Veuillez renouveler votre compte."
                        : "Urgent: Your Yellow Markets Subscription expires in 1 week on " . date('d M Y', strtotime($active_plan['end_date'])) . ". Please renew your account."; ?>
                </span>
            </div>
        <?php elseif($days_remaining !== null && $days_remaining <= 30 && $days_remaining > 7): ?>
            <div class="text-center flex-grow-1">
                <span class="font-weight-bold" style="color: #e67e22 !important; font-size: 16px; text-decoration: underline;">
                    <?= $is_fr 
                        ? "Action requise : Votre abonnement Yellow Markets expire le " . date('d M Y', strtotime($active_plan['end_date'])) . ". Veuillez renouveler votre compte."
                        : "Action Required: Your Yellow Markets Subscription expires on " . date('d M Y', strtotime($active_plan['end_date'])) . ". Please renew your account."; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= base_url('subscription/subscribe') ?>">

        <table class="plan-table table table-bordered table-striped text-center toped-table">
            <thead class="ym-basic-merchant-plan-2 ym-basic-plan-merchant-1">
                <tr>
                    <th><?php echo lang('features_plans'); ?></th>
                    <?php foreach($plans as $plan): ?>
                        <th><?= htmlspecialchars(($this->session->userdata('site_lang')=='french' && !empty($plan['name_fr'])) ? $plan['name_fr'] : $plan['name']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <!-- Yearly Subscription Price Row -->
                <tr>
                    <td><strong><?php echo lang('yearly_subscription'); ?></strong></td>
                    <?php foreach($plans as $plan): ?>
                        <td><?= number_format((float)$plan['final_price'], 2); ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Features Rows -->
                <?php foreach($features as $feature): ?>
                    <tr>
                        <td class="<?= in_array($feature['id'], [1, 10, 15]) ? 'main_feature_name font-weight-bold' : '' ?>">
                            <?= htmlspecialchars(($this->session->userdata('site_lang')=='french' && !empty($feature['feature_name_fr'])) ? $feature['feature_name_fr'] : $feature['feature_name']); ?>
                        </td>
                        <?php foreach($plans as $plan): ?>
                            <td class="<?= in_array($feature['id'], [1, 10, 15]) ? 'main_feature_name' : '' ?>">
                                <?= isset($plan_features[$feature['id']][$plan['id']]) 
                                    ? $plan_features[$feature['id']][$plan['id']] 
                                    : '-'; ?>   
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>

                
                <!-- Select Plan Row -->
                <tr class="bg-light">
                    <td><strong><?php echo lang('select_plan'); ?></strong></td>
                    <?php 
                    foreach($plans as $plan): 
                        $is_active = (isset($active_plan['plan_id']) && $active_plan['plan_id'] == $plan['id']);
                        
                        // Identify the Starter plan specifically
                        $plan_name_clean = strtolower(trim(($plan['name'] ?? '') . ' ' . ($plan['name_fr'] ?? '')));
                        $is_starter = ($plan_name_clean === 'starter' || strpos($plan_name_clean, 'starter') !== false);
                        
                        // Check if user has an active higher plan (e.g., Growth is active, and current loop is Starter)
                        $has_active_plan = !empty($active_plan['plan_id']);

                        // Check if subscription is expired
                        $is_plan_expired = (!empty($is_expired) || (!empty($active_plan['end_date']) && strtotime($active_plan['end_date']) < time()));

                        // Renew option should only show 30 days prior before expiry date (or if expired)
                        // Do not show renew/select radio for Starter plan when expired or active
                        $show_renew = false;
                        if ($is_active && !$is_starter) {
                            if ($is_plan_expired) {
                                $show_renew = true;
                            } elseif (!empty($active_plan['end_date'])) {
                                $end_ts = strtotime($active_plan['end_date']);
                                $plan_days_remaining = ($end_ts - time()) / 86400;
                                // Show only if 30 days or fewer remaining before expiry
                                if ($plan_days_remaining <= 30) {
                                    $show_renew = true;
                                }
                            }
                        }
                    ?>
                        <td>
                            <?php if($is_active): ?>
                                <?php if($is_plan_expired): ?>
                                    <span class="badge badge-danger"><?php echo lang('expired') ?: 'Expired'; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo lang('active'); ?></span>
                                <?php endif; ?>
                                <?php if(!empty($active_plan['end_date'])): ?>
                                    <br><small><?php echo lang('expires'); ?>: <?= date('d M Y', strtotime($active_plan['end_date'])); ?></small>
                                <?php endif; ?>
                                <?php if($show_renew && !$is_starter): ?>
                                    <div class="mt-2">
                                        <input type="radio" name="plan_id" value="<?= $plan['id']; ?>" required>
                                        <label><?php echo lang('select'); ?></label>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($has_active_plan && $is_starter): ?>
                                <!-- Hide select option only for the Starter plan when any plan is already active -->
                                <span class="text-muted">-</span>
                            <?php elseif($is_starter && $is_plan_expired): ?>
                                <!-- Hide select option for Starter plan when subscription is expired -->
                                <span class="text-muted">-</span>
                            <?php else: ?>
                                <input type="radio" name="plan_id" value="<?= $plan['id']; ?>" required>
                                <label><?php echo lang('select'); ?></label>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>

        <!-- Proceed Button -->
        <div class="actions-toolbar mt-4 mb-5 text-right">
            <button type="submit" id="subscription-payment" class="btn btn-primary btn-lg px-5" title="Subscribe">
                <i class="fa fa-credit-card"></i> <?php echo lang('proceed_to_payment'); ?>
            </button>
        </div>
    </form>

</main>

<script>
function renewSubscription(planId) {
    if (!planId) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("subscription/subscribe") ?>';

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'plan_id';
    input.value = planId;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
}

// Clean query string from browser address bar to show clean https://mu.yellowmarkets.com/merchant/subscription
if (window.location.search.includes('payment=')) {
    window.history.replaceState({}, document.title, window.location.pathname);
}

setTimeout(() => {
  document.querySelectorAll('.alert-auto-dismiss').forEach(a => {
    a.classList.remove('show');
  });
}, 4000);
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
