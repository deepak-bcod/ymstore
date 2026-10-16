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

    <!-- Flash messages -->
  <?php if($this->input->get('payment') === 'success' || $this->session->flashdata('payment') === 'success' || $this->session->flashdata('payment_status') === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <?php echo lang('payment_success'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Error Message -->
<?php if($this->input->get('payment') === 'error' || $this->session->flashdata('payment') === 'error' || $this->session->flashdata('payment_status') === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <?php echo lang('payment_failed'); ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if(!empty($is_expired)): ?>
    <div class="alert alert-danger fade show mt-3" role="alert">
        <i class="fa fa-exclamation-triangle mr-2"></i>
        <?= ($this->session->userdata('site_lang') == 'french') 
            ? "Votre abonnement a expiré et votre boutique a été temporairement désactivée sur la place de marché. Veuillez renouveler votre abonnement ci-dessous pour réactiver votre boutique et restaurer l'accès complet." 
            : "Your subscription has expired and your shop is currently disabled on the storefront. Please renew your subscription below to reactivate your shop and restore full access."; ?>
    </div>
<?php endif; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-4 mb-3">
        <h4 class="mb-0"><?php echo lang('choose_subscription_plan'); ?></h4>
        <?php if(!empty($is_expired)): ?>
            <div class="text-center flex-grow-1">
                <span class="font-weight-bold" style="color: red !important; font-size: 16px; text-decoration: underline;">
                    <?= ($this->session->userdata('site_lang') == 'french') 
                        ? 'Veuillez renouveler votre compte pour accéder au portail.' 
                        : 'Please Renew your account to access the Portal.'; ?>
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
                        
                        // Identify the Starter plan specifically (adjust 'Starter' if your database name/slug is different)
                        $is_starter = (strtolower($plan['name']) === 'starter');
                        
                        // Check if user has an active higher plan (e.g., Growth is active, and current loop is Starter)
                        $has_active_plan = !empty($active_plan['plan_id']);

                        // Renew button should only show 30 days prior before expiry date (or if expired)
                        $show_renew = false;
                        if ($is_active) {
                            if (!empty($is_expired)) {
                                $show_renew = true;
                            } elseif (!empty($active_plan['end_date'])) {
                                $end_ts = strtotime($active_plan['end_date']);
                                $days_remaining = ($end_ts - time()) / 86400;
                                // Show only if 30 days or fewer remaining before expiry
                                if ($days_remaining <= 30) {
                                    $show_renew = true;
                                }
                            }
                        }
                    ?>
                        <td>
                            <?php if($is_active): ?>
                                <?php if(!empty($is_expired)): ?>
                                    <span class="badge badge-danger"><?php echo lang('expired') ?: 'Expired'; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo lang('active'); ?></span>
                                <?php endif; ?>
                                <?php if(!empty($active_plan['end_date'])): ?>
                                    <br><small><?php echo lang('expires'); ?>: <?= date('d M Y', strtotime($active_plan['end_date'])); ?></small>
                                <?php endif; ?>
                                <?php if($show_renew): ?>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-warning btn-sm font-weight-bold px-3 text-dark btn-renew" onclick="renewSubscription(<?= $plan['id']; ?>)">
                                            <i class="fa fa-refresh"></i> <?php echo lang('renew') ?: 'Renew'; ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($has_active_plan && $is_starter): ?>
                                <!-- Hide select option only for the Starter plan when any plan is already active -->
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
  document.querySelectorAll('.alert').forEach(a => {
    a.classList.remove('show');
  });
}, 4000);
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
