<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Subscription_model extends CI_Model
{

    public function get_plans()
    {
        return $this->db->get_where('subscription_plans', ['status' => 1])->result_array();
    }


    public function get_features()
    {
        return $this->db->order_by('sort_order', 'ASC')->get('subscription_features')->result_array();
    }

    public function get_plan_features()
    {
        $this->db->select('plan_features.plan_id, plan_features.feature_id, plan_features.value');
        $this->db->from('plan_features');
        return $this->db->get()->result_array();
    }

    public function get_default_starter_plan()
    {
        $plan = $this->db->get_where('subscription_plans', ['LOWER(name)' => 'starter', 'status' => 1])->row_array();
        if (!$plan) {
            $plan = $this->db->order_by('id', 'ASC')->get_where('subscription_plans', ['status' => 1])->row_array();
        }
        return $plan;
    }

    public function get_active_plan($publisher_id)
    {
        $sub = $this->db
            ->select('publisher_subscriptions.*, subscription_plans.name as plan_name, subscription_plans.name_fr as plan_name_fr')
            ->from('publisher_subscriptions')
            ->join('subscription_plans', 'subscription_plans.id = publisher_subscriptions.plan_id', 'left')
            ->where('publisher_subscriptions.publisher_id', $publisher_id)
            ->where('publisher_subscriptions.status', 'active')
            ->order_by('publisher_subscriptions.id', 'DESC')
            ->get()
            ->row_array();

        if ($sub) {
            return $sub;
        }

        // If no active record, check if there is an existing subscription record
        $latest = $this->db
            ->select('publisher_subscriptions.*, subscription_plans.name as plan_name, subscription_plans.name_fr as plan_name_fr')
            ->from('publisher_subscriptions')
            ->join('subscription_plans', 'subscription_plans.id = publisher_subscriptions.plan_id', 'left')
            ->where('publisher_subscriptions.publisher_id', $publisher_id)
            ->order_by('publisher_subscriptions.id', 'DESC')
            ->get()
            ->row_array();

        return $latest ?: null;
    }

    /**
     * Check if the merchant's subscription is expired and not renewed
     *
     * @param int $publisher_id
     * @return bool
     */
    public function is_subscription_expired($publisher_id)
    {
        if (empty($publisher_id) || !$this->db->table_exists('publisher_subscriptions')) {
            return false;
        }

        // Check if there is an active subscription with a future end_date
        $active_sub = $this->db
            ->where('publisher_id', $publisher_id)
            ->where('status', 'active')
            ->where('(end_date IS NULL OR end_date >= NOW())', NULL, FALSE)
            ->order_by('id', 'DESC')
            ->get('publisher_subscriptions')
            ->row_array();

        if ($active_sub) {
            return false; // Active and not expired
        }

        // Check if the merchant has a subscription record that is expired or in the past
        $expired_sub = $this->db
            ->where('publisher_id', $publisher_id)
            ->where("(status = 'expired' OR (end_date IS NOT NULL AND end_date < NOW()))", NULL, FALSE)
            ->order_by('id', 'DESC')
            ->get('publisher_subscriptions')
            ->row_array();

        if ($expired_sub) {
            return true;
        }

        return false;
    }

    public function get_merchant_plan($publisher_id)
    {
        $active_sub = $this->get_active_plan($publisher_id);
        if ($active_sub && !empty($active_sub['plan_id'])) {
            $plan = $this->get_plan_by_id($active_sub['plan_id']);
            if ($plan) {
                $plan['subscription'] = $active_sub;
                return $plan;
            }
        }

        // Fallback to default Starter plan
        $starter = $this->get_default_starter_plan();
        if ($starter) {
            $starter['subscription'] = null;
        }
        return $starter;
    }

    public function can_use_flash_sales($publisher_id)
    {
        $plan = $this->get_merchant_plan($publisher_id);
        if (!$plan) return false;

        $plan_name = strtolower($plan['name'] ?? '');
        // Starter is strictly blocked from Flash Sales. Growth and Cyber have access.
        if ($plan_name === 'starter') {
            return false;
        }
        if (in_array($plan_name, ['growth', 'cyber'])) {
            return true;
        }

        // Also check plan_features mapping if custom feature is defined
        $this->db->select('pf.value');
        $this->db->from('plan_features pf');
        $this->db->join('subscription_features sf', 'sf.id = pf.feature_id');
        $this->db->where('pf.plan_id', $plan['id']);
        $this->db->like('LOWER(sf.feature_name)', 'flash sale');
        $row = $this->db->get()->row_array();

        if ($row) {
            $val = trim(strtolower($row['value']));
            if ($val === '-' || $val === 'no' || $val === '0' || empty($val)) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function can_use_daily_deals($publisher_id)
    {
        // Daily Deals is allowed on Starter, Growth, and Cyber
        return true;
    }

    public function get_base_flash_sale_limit($publisher_id)
    {
        if (!$this->can_use_flash_sales($publisher_id)) {
            return 0;
        }
        // Base allowance for Growth & Cyber: 3 products for seven days
        return 3;
    }

    public function get_base_daily_deals_limit($publisher_id)
    {
        // Base allowance for Starter, Growth & Cyber: 7 products (1 per day)
        return 7;
    }

    public function subscribe_plan($publisher_id, $plan_id, $ip = null)
    {
        $plan = $this->get_plan_by_id($plan_id);
        if (!$plan) return false;

        $start_date = date('Y-m-d H:i:s');
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $existing = $this->db->order_by('id', 'DESC')->get_where('publisher_subscriptions', [
            'publisher_id' => $publisher_id
        ])->row_array();

        // If renewing the same active plan before it expires, extend from current end_date
        if ($existing && $existing['status'] === 'active' && !empty($existing['end_date']) && strtotime($existing['end_date']) > strtotime($start_date) && $existing['plan_id'] == $plan_id) {
            $end_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($existing['end_date'])));
        } else {
            $end_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($start_date)));
        }

        if ($existing) {
            // Update existing subscription to active and reset reminder tracking
            $updateData = [
                'plan_id'    => $plan_id,
                'status'     => 'active',
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'ip'         => $ip,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if ($this->db->field_exists('reminder_month_sent', 'publisher_subscriptions')) {
                $updateData['reminder_month_sent']    = 0;
                $updateData['reminder_month_sent_at'] = NULL;
                $updateData['reminder_week_sent']     = 0;
                $updateData['reminder_week_sent_at']  = NULL;
            }
            $this->db->where('id', $existing['id'])->update('publisher_subscriptions', $updateData);
        } else {
            $data = [
                'publisher_id' => $publisher_id,
                'plan_id'      => $plan_id,
                'status'       => 'active',
                'start_date'   => $start_date,
                'end_date'     => $end_date,
                'ip'           => $ip,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => NULL
            ];
            if ($this->db->field_exists('reminder_month_sent', 'publisher_subscriptions')) {
                $data['reminder_month_sent']    = 0;
                $data['reminder_month_sent_at'] = NULL;
                $data['reminder_week_sent']     = 0;
                $data['reminder_week_sent_at']  = NULL;
            }
        }

        // Synchronize end_date to latest subscription_orders record for easy display in subscription list
        $this->ensure_subscription_invoice_schema();
        if ($this->db->table_exists('subscription_orders') && $this->db->field_exists('end_date', 'subscription_orders')) {
            $latest_order = $this->db
                ->where('publisher_id', $publisher_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('subscription_orders')
                ->row_array();
            if ($latest_order) {
                $this->db->where('id', $latest_order['id'])->update('subscription_orders', ['end_date' => $end_date]);
            }
        }

        return true;
    }

    public function get_plan_by_id($plan_id)
    {
        return $this->db->get_where('subscription_plans', ['id' => $plan_id])->row_array();
    }

    // New helper: get order by id and publisher
    public function get_order_by_id($order_id, $publisher_id)
    {
        return $this->db->get_where('subscription_orders', ['id' => $order_id, 'publisher_id' => $publisher_id])->row_array();
    }

    // New helper: update order status
    public function update_order_status($order_id, $status)
    {
        return $this->db->where('id', $order_id)->update('subscription_orders', ['status' => $status]);
    }

    public function get_subscription_orders($seller_id)
    {
        $this->ensure_subscription_invoice_schema();
        return $this->db
            ->select('subscription_orders.*, publisher.publication_name, subscription_plans.name as plan_name')
            ->from('subscription_orders')
            ->join('publisher', 'publisher.id = subscription_orders.publisher_id')
            ->join('subscription_plans', 'subscription_plans.id = subscription_orders.plan_id')
            ->order_by('subscription_orders.id', 'DESC')
            ->where('subscription_orders.publisher_id', $seller_id)
            ->get()
            ->result_array();
    }

    /**
     * Ensure database columns for reminder tracking exist in publisher_subscriptions
     */
    public function ensure_subscription_reminder_schema()
    {
        if (!$this->db->table_exists('publisher_subscriptions')) {
            return;
        }

        $fields = [];
        if (!$this->db->field_exists('reminder_month_sent', 'publisher_subscriptions')) {
            $fields[] = "ADD COLUMN `reminder_month_sent` TINYINT(1) NOT NULL DEFAULT 0";
        }
        if (!$this->db->field_exists('reminder_month_sent_at', 'publisher_subscriptions')) {
            $fields[] = "ADD COLUMN `reminder_month_sent_at` DATETIME NULL DEFAULT NULL";
        }
        if (!$this->db->field_exists('reminder_week_sent', 'publisher_subscriptions')) {
            $fields[] = "ADD COLUMN `reminder_week_sent` TINYINT(1) NOT NULL DEFAULT 0";
        }
        if (!$this->db->field_exists('reminder_week_sent_at', 'publisher_subscriptions')) {
            $fields[] = "ADD COLUMN `reminder_week_sent_at` DATETIME NULL DEFAULT NULL";
        }

        if (!empty($fields)) {
            $this->db->query("ALTER TABLE `publisher_subscriptions` " . implode(', ', $fields));
        }
    }

    /**
     * Ensure reminder audit log table exists
     */
    public function ensure_reminder_logs_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `subscription_reminder_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `subscription_id` INT(11) NOT NULL,
            `publisher_id` INT(11) NOT NULL,
            `reminder_type` VARCHAR(20) NOT NULL,
            `email_to` VARCHAR(255) NOT NULL,
            `end_date` DATETIME NULL,
            `sent_at` DATETIME NOT NULL,
            `status` VARCHAR(50) DEFAULT 'sent',
            PRIMARY KEY (`id`),
            KEY `idx_pub_type` (`publisher_id`, `reminder_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
    }

    /**
     * Ensure default email templates exist in email_template table
     */
    public function ensure_subscription_email_templates()
    {
        if (!$this->db->table_exists('email_template')) {
            return;
        }

        $has_fr = $this->db->field_exists('subject_fr', 'email_template');

        // 1. Template: 1 Month Before Expiry
        $data_1m = [
            'email_code' => 'merchant-subscription-renewal-1month',
            'title'      => 'Merchant Subscription Expiry Reminder (1 Month Before)',
            'subject'    => 'Action Required: Your Yellow Markets Subscription expires on ##EXPIRY_DATE##',
            'content'    => '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                '<p>Dear <strong>##MERCHANT_NAME##</strong>,</p>' .
                '<p>This is a friendly reminder that your merchant subscription for plan <strong>##PLAN_NAME##</strong> on Yellow Markets will expire in <strong>1 month</strong>, on <strong>##EXPIRY_DATE##</strong>.</p>' .
                '<p>To ensure uninterrupted access to your shop features, product listings, daily deals, flash sales, and seller tools, please renew your subscription in advance.</p>' .
                '<div style="text-align: left; margin: 25px 0;">' .
                '<a href="##RENEWAL_URL##" style="background-color: #f7a800; color: #000; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Renew Subscription Now</a>' .
                '</div>' .
                '<p>If you have already renewed your subscription, please disregard this email.</p>' .
                '<p>Thank you for partnering with <strong>Yellow Markets</strong>.</p>' .
                '<p>Best regards,<br>Yellow Markets Support Team</p>' .
                '</div>',
            'status'     => 1,
            'created_by' => 1,
            'created_at' => time()
        ];
        if ($has_fr) {
            $data_1m['title_fr']   = "Rappel d'expiration de l'abonnement marchand (1 mois avant)";
            $data_1m['subject_fr'] = "Action requise : Votre abonnement Yellow Markets expire le ##EXPIRY_DATE##";
            $data_1m['content_fr'] = '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                "<p>Bonjour <strong>##MERCHANT_NAME##</strong>,</p>" .
                "<p>Nous vous rappelons que votre abonnement marchand pour le forfait <strong>##PLAN_NAME##</strong> sur Yellow Markets expirera dans <strong>1 mois</strong>, le <strong>##EXPIRY_DATE##</strong>.</p>" .
                "<p>Afin d'assurer la continuité de vos annonces de boutique, promotions et outils vendeurs, nous vous invitons à renouveler votre forfait à l'avance.</p>" .
                "<div style=\"text-align: left; margin: 25px 0;\">" .
                "<a href=\"##RENEWAL_URL##\" style=\"background-color: #f7a800; color: #000; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;\">Renouveler mon abonnement</a>" .
                "</div>" .
                "<p>Si vous avez déjà procédé au renouvellement, veuillez ignorer ce message.</p>" .
                "<p>Merci pour votre confiance sur <strong>Yellow Markets</strong>.</p>" .
                "<p>Cordialement,<br>L'équipe support de Yellow Markets</p>" .
                '</div>';
        }

        $tpl_1m = $this->db->get_where('email_template', ['email_code' => 'merchant-subscription-renewal-1month'])->row_array();
        if (!$tpl_1m) {
            $this->db->insert('email_template', $data_1m);
        } else {
            $up1m = [
                'subject' => $data_1m['subject'],
                'content' => $data_1m['content']
            ];
            if ($has_fr && isset($data_1m['subject_fr'])) {
                $up1m['subject_fr'] = $data_1m['subject_fr'];
                $up1m['content_fr'] = $data_1m['content_fr'];
            }
            $this->db->where('email_code', 'merchant-subscription-renewal-1month')->update('email_template', $up1m);
        }

        // 2. Template: 1 Week Before Expiry
        $data_1w = [
            'email_code' => 'merchant-subscription-renewal-1week',
            'title'      => 'Merchant Subscription Expiry Reminder (1 Week Before)',
            'subject'    => 'Urgent: Your Yellow Markets Subscription expires in 1 week on ##EXPIRY_DATE##',
            'content'    => '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                '<p>Dear <strong>##MERCHANT_NAME##</strong>,</p>' .
                '<p><span style="color: #d9534f; font-weight: bold;">Important Notice:</span> Your merchant subscription for plan <strong>##PLAN_NAME##</strong> will expire in <strong>1 week</strong>, on <strong>##EXPIRY_DATE##</strong>.</p>' .
                '<p>Our records show that your subscription has not been renewed yet. Once expired, your shop listings and seller privileges may be restricted.</p>' .
                '<p>Please renew your subscription today to avoid any disruption to your business on Yellow Markets:</p>' .
                '<div style="text-align: left; margin: 25px 0;">' .
                '<a href="##RENEWAL_URL##" style="background-color: #d9534f; color: #fff; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">Renew Subscription Immediately</a>' .
                '</div>' .
                '<p>If you have already renewed, please disregard this email.</p>' .
                '<p>Best regards,<br>Yellow Markets Support Team</p>' .
                '</div>',
            'status'     => 1,
            'created_by' => 1,
            'created_at' => time()
        ];
        if ($has_fr) {
            $data_1w['title_fr']   = "Rappel d'expiration de l'abonnement marchand (1 semaine avant)";
            $data_1w['subject_fr'] = "Urgent : Votre abonnement Yellow Markets expire dans 1 semaine le ##EXPIRY_DATE##";
            $data_1w['content_fr'] = '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                "<p>Bonjour <strong>##MERCHANT_NAME##</strong>,</p>" .
                "<p><span style=\"color: #d9534f; font-weight: bold;\">Avis important :</span> Votre abonnement marchand pour le forfait <strong>##PLAN_NAME##</strong> expirera dans <strong>1 semaine</strong>, le <strong>##EXPIRY_DATE##</strong>.</p>" .
                "<p>Nos dossiers indiquent que votre abonnement n'a pas encore été renouvelé. Après expiration, vos annonces et privilèges vendeurs pourraient être suspendus.</p>" .
                "<p>Veuillez renouveler votre abonnement aujourd'hui pour assurer la continuité de votre activité sur Yellow Markets :</p>" .
                "<div style=\"text-align: center; margin: 30px 0;\">" .
                "<div style=\"text-align: left; margin: 25px 0;\">" .
                "<a href=\"##RENEWAL_URL##\" style=\"background-color: #d9534f; color: #fff; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;\">Renouveler immédiatement mon abonnement</a>" .
                "</div>" .
                "<p>Si vous avez déjà procédé au renouvellement, veuillez ignorer ce message.</p>" .
                "<p>Cordialement,<br>L'équipe support de Yellow Markets</p>" .
                '</div>';
        }

        $tpl_1w = $this->db->get_where('email_template', ['email_code' => 'merchant-subscription-renewal-1week'])->row_array();
        if (!$tpl_1w) {
            $this->db->insert('email_template', $data_1w);
        } else {
            $up1w = [
                'subject' => $data_1w['subject'],
                'content' => $data_1w['content']
            ];
            if ($has_fr && isset($data_1w['subject_fr'])) {
                $up1w['subject_fr'] = $data_1w['subject_fr'];
                $up1w['content_fr'] = $data_1w['content_fr'];
            }
            $this->db->where('email_code', 'merchant-subscription-renewal-1week')->update('email_template', $up1w);
        }

        // 3. Template: Subscription Payment Confirmation & Invoice
        $data_conf = [
            'email_code' => 'merchant-subscription-payment-confirmation',
            'title'      => 'Merchant Subscription Payment Confirmation & Invoice',
            'subject'    => 'Subscription Renewal : Your Subscription is renewed to ##PLAN_NAME## - Invoice ###INVOICE_ID##',
            'content'    => '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                '<p>Dear <strong>##MERCHANT_NAME##</strong>,</p>' .
                '<p>Thank you for your payment! We are pleased to confirm that your merchant subscription on <strong>Yellow Markets</strong> has been successfully renewed to <strong>##PLAN_NAME##</strong>.</p>' .
                '<div style="background-color: #f8f9fa; border-left: 4px solid #f7a800; padding: 15px 20px; margin: 20px 0; text-align: left;">' .
                '<p style="margin: 0 0 8px 0;"><strong>Invoice ID:</strong> ###INVOICE_ID##</p>' .
                '<p style="margin: 0 0 8px 0;"><strong>Subscription Plan:</strong> ##PLAN_NAME##</p>' .
                '<p style="margin: 0 0 8px 0;"><strong>Amount Paid:</strong> MUR ##AMOUNT##</p>' .
                '<p style="margin: 0 0 8px 0;"><strong>Payment Method:</strong> ##PAYMENT_METHOD##</p>' .
                '<p style="margin: 0 0 8px 0;"><strong>Start Date:</strong> ##START_DATE##</p>' .
                '<p style="margin: 0;"><strong>Expiry Date:</strong> ##EXPIRY_DATE##</p>' .
                '</div>' .
                '<p>Your official subscription invoice has been attached to this email as a PDF document for your records.</p>' .
                '<p>You can also view and download your invoice at any time from your merchant dashboard:</p>' .
                '<div style="text-align: left; margin: 25px 0;">' .
                '<a href="##SUBSCRIPTION_URL##" style="background-color: #f7a800; color: #000; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;">View Subscription & Invoices</a>' .
                '</div>' .
                '<p>Thank you for partnering with <strong>Yellow Markets</strong>.</p>' .
                '<p>Best regards,<br>Yellow Markets Support Team</p>' .
                '</div>',
            'status'     => 1,
            'created_by' => 1,
            'created_at' => time()
        ];
        if ($has_fr) {
            if ($this->db->field_exists('title_fr', 'email_template')) {
                $data_conf['title_fr'] = "Confirmation de paiement et facture de l'abonnement marchand";
            }
            $data_conf['subject_fr'] = "Renouvellement d'abonnement : Votre abonnement est renouvelé pour ##PLAN_NAME## - Facture ###INVOICE_ID##";
            $data_conf['content_fr'] = '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                "<p>Bonjour <strong>##MERCHANT_NAME##</strong>,</p>" .
                "<p>Merci pour votre paiement ! Nous vous confirmons que votre abonnement marchand sur <strong>Yellow Markets</strong> a bien été renouvelé pour le forfait <strong>##PLAN_NAME##</strong>.</p>" .
                "<div style=\"background-color: #f8f9fa; border-left: 4px solid #f7a800; padding: 15px 20px; margin: 20px 0; text-align: left;\">" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>N° de Facture :</strong> ###INVOICE_ID##</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Forfait d'abonnement :</strong> ##PLAN_NAME##</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Montant payé :</strong> MUR ##AMOUNT##</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Mode de paiement :</strong> ##PAYMENT_METHOD##</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Date de début :</strong> ##START_DATE##</p>" .
                "<p style=\"margin: 0;\"><strong>Date d'expiration :</strong> ##EXPIRY_DATE##</p>" .
                "</div>" .
                "<p>Votre facture officielle d'abonnement est jointe à cet email au format PDF pour vos archives.</p>" .
                "<p>Vous pouvez également consulter et télécharger vos factures à tout moment depuis votre espace marchand :</p>" .
                "<div style=\"text-align: left; margin: 25px 0;\">" .
                "<a href=\"##SUBSCRIPTION_URL##\" style=\"background-color: #f7a800; color: #000; padding: 12px 28px; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block;\">Gérer mon abonnement</a>" .
                "</div>" .
                "<p>Merci pour votre confiance sur <strong>Yellow Markets</strong>.</p>" .
                "<p>Cordialement,<br>L'équipe support de Yellow Markets</p>" .
                '</div>';
        }

        $tpl_conf = $this->db->get_where('email_template', ['email_code' => 'merchant-subscription-payment-confirmation'])->row_array();
        if (!$tpl_conf) {
            $this->db->insert('email_template', $data_conf);
        } else {
            $upconf = [
                'subject' => $data_conf['subject'],
                'content' => $data_conf['content']
            ];
            if ($has_fr && isset($data_conf['subject_fr'])) {
                $upconf['subject_fr'] = $data_conf['subject_fr'];
                $upconf['content_fr'] = $data_conf['content_fr'];
            }
            $this->db->where('email_code', 'merchant-subscription-payment-confirmation')->update('email_template', $upconf);
        }
    }

    public $last_expiry_error = null;

    /**
     * Check if the merchant has already renewed their subscription
     */
    public function has_merchant_renewed($publisher_id, $current_sub_id, $current_end_date)
    {
        $now = time();
        $current_end_ts = strtotime($current_end_date);

        // 1. If current end_date is more than 30 days away, it has already been extended / renewed
        if ($current_end_ts && ($current_end_ts - $now) > (30 * 86400)) {
            $this->last_expiry_error = "Renewal check: end_date is more than 30 days away (" . round(($current_end_ts - $now)/86400, 1) . " days left)";
            return true;
        }

        // 2. Check if a newer active subscription exists with a later end_date
        $newer_sub = $this->db
            ->where('publisher_id', $publisher_id)
            ->where('id !=', $current_sub_id)
            ->where('status', 'active')
            ->where('end_date >', $current_end_date)
            ->get('publisher_subscriptions')
            ->row_array();
        if ($newer_sub) {
            $this->last_expiry_error = "Renewal check: newer active subscription exists (ID: {$newer_sub['id']}, end_date: {$newer_sub['end_date']})";
            return true;
        }

        return false;
    }

    /**
     * Check active subscription expiry and send reminder email upon merchant login
     *
     * Rules:
     * - Send 1 month before expiry (between 8 and 30 days remaining)
     * - Send 1 week before expiry (between 1 and 7 days remaining)
     * - Send only if merchant has not renewed subscription
     * - If merchant renewed, do not send email
     * - Uses email_template table templates
     */
    public function check_and_send_expiry_reminders($publisher_id)
    {
        if (empty($publisher_id)) {
            return false;
        }

        // Ensure database tables and templates are ready
        $this->ensure_subscription_reminder_schema();
        $this->ensure_reminder_logs_table();
        $this->ensure_subscription_email_templates();

        // Get the active plan for this merchant
        $active_sub = $this->get_active_plan($publisher_id);
        if (!$active_sub || empty($active_sub['end_date'])) {
            $this->last_expiry_error = 'No active subscription or empty end_date';
            return false;
        }

        $end_timestamp = strtotime($active_sub['end_date']);
        if (!$end_timestamp) {
            $this->last_expiry_error = 'Invalid end_date format';
            return false;
        }

        $today_date = date('Y-m-d');
        $expiry_date_str = date('Y-m-d', $end_timestamp);
        $days_left = (int)round((strtotime($expiry_date_str) - strtotime($today_date)) / 86400);

        // If already expired, do not send "before expiry" reminders
        if ($days_left < 0) {
            $this->last_expiry_error = 'Subscription end_date is in the past (already expired)';
            return false;
        }

        // If more than 30 days away, not in reminder window
        if ($days_left > 30) {
            $this->last_expiry_error = "Subscription expires in {$days_left} days (more than 30 days away)";
            return false;
        }

        // Check if merchant already renewed
        if ($this->has_merchant_renewed($publisher_id, $active_sub['id'], $active_sub['end_date'])) {
            // $this->last_expiry_error is set in has_merchant_renewed()
            return false;
        }

        // Fetch fresh subscription record to check reminder flags
        $sub_record = $this->db->get_where('publisher_subscriptions', ['id' => $active_sub['id']])->row_array();
        if (!$sub_record) {
            $this->last_expiry_error = 'Subscription record not found in publisher_subscriptions';
            return false;
        }

        $reminder_type = null;
        $template_code = null;

        // Window 1: 1 week before expiry (0 to 7 days left)
        if ($days_left <= 7 && $days_left >= 0) {
            if (empty($sub_record['reminder_week_sent'])) {
                $reminder_type = '1_week';
                $template_code = 'merchant-subscription-renewal-1week';
            }
        }
        // Window 2: 1 month before expiry (8 to 30 days left)
        else if ($days_left <= 30 && $days_left > 7) {
            if (empty($sub_record['reminder_month_sent'])) {
                $reminder_type = '1_month';
                $template_code = 'merchant-subscription-renewal-1month';
            }
        }

        // If no reminder is due or already sent
        if (!$reminder_type || !$template_code) {
            $this->last_expiry_error = "Reminder not sent: reminder_month_sent = " . ($sub_record['reminder_month_sent'] ?? '0') . ", reminder_week_sent = " . ($sub_record['reminder_week_sent'] ?? '0') . ", days_left = {$days_left}";
            return false;
        }

        // Fetch merchant details from publisher table
        $merchant = $this->db
            ->where('id', $publisher_id)
            ->where('(remove_flag = 0 OR remove_flag IS NULL)', null, false)
            ->get('publisher')
            ->row_array();
        if (!$merchant) {
            $merchant = $this->db->get_where('publisher', ['id' => $publisher_id])->row_array();
        }
        if (!$merchant) {
            $this->last_expiry_error = "Merchant ID {$publisher_id} not found in publisher table";
            log_message('error', "Subscription reminder failed: Merchant ID {$publisher_id} not found in publisher table.");
            return false;
        }
        if (empty($merchant['email'])) {
            $this->last_expiry_error = "Merchant ID {$publisher_id} has empty email in publisher table";
            log_message('error', "Subscription reminder failed: Merchant ID {$publisher_id} has no email.");
            return false;
        }

        $to_email = trim($merchant['email']);
        $merchant_name = !empty($merchant['vendor_name']) ? $merchant['vendor_name'] : (!empty($merchant['publication_name']) ? $merchant['publication_name'] : 'Valued Merchant');
        $shop_name = !empty($merchant['publication_name']) ? $merchant['publication_name'] : $merchant_name;

        // Determine language preference
        $CI = &get_instance();
        $site_lang = $CI->session->userdata('site_lang') ?: 'english';
        $lang_code = ($site_lang === 'french') ? 'fr' : 'en';

        $plan_name = !empty($active_sub['plan_name']) ? $active_sub['plan_name'] : 'Merchant Plan';
        if ($lang_code === 'fr' && !empty($active_sub['plan_name_fr'])) {
            $plan_name = $active_sub['plan_name_fr'];
        }

        $expiry_date_formatted = date('d M Y', $end_timestamp);
        $renewal_url = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : base_url()) . '/subscription';

        $tempVars = [
            '##MERCHANT_NAME##',
            '{merchant_name}',
            '##SHOP_NAME##',
            '{shop_name}',
            '##PLAN_NAME##',
            '{plan_name}',
            '##EXPIRY_DATE##',
            '{expiry_date}',
            '##DAYS_LEFT##',
            '{days_left}',
            '##RENEWAL_URL##',
            '{renewal_url}',
            '##YEAR##',
            '{year}'
        ];

        $dynamicVars = [
            $merchant_name,
            $merchant_name,
            $shop_name,
            $shop_name,
            $plan_name,
            $plan_name,
            $expiry_date_formatted,
            $expiry_date_formatted,
            $days_left . ' ' . (($days_left == 1) ? 'day' : 'days'),
            $days_left . ' ' . (($days_left == 1) ? 'day' : 'days'),
            $renewal_url,
            $renewal_url,
            date('Y'),
            date('Y')
        ];

        // Send email using CommonModel
        $CI->load->model('CommonModel');
        $mailSent = $CI->CommonModel->sendCommonHTMLEmail(
            $to_email,
            $template_code,
            $tempVars,
            $dynamicVars,
            $lang_code
        );

        if ($mailSent) {
            $this->last_expiry_error = "Success: {$reminder_type} reminder email sent to {$to_email}";
            $updateData = [];
            if ($reminder_type === '1_week') {
                $updateData['reminder_week_sent']    = 1;
                $updateData['reminder_week_sent_at'] = date('Y-m-d H:i:s');
                $updateData['reminder_month_sent']   = 1; // Also mark 1-month as sent
            } else if ($reminder_type === '1_month') {
                $updateData['reminder_month_sent']    = 1;
                $updateData['reminder_month_sent_at'] = date('Y-m-d H:i:s');
            }

            $this->db->where('id', $active_sub['id'])->update('publisher_subscriptions', $updateData);

            // Record into audit log
            $this->db->insert('subscription_reminder_logs', [
                'subscription_id' => $active_sub['id'],
                'publisher_id'    => $publisher_id,
                'reminder_type'   => $reminder_type,
                'email_to'        => $to_email,
                'end_date'        => $active_sub['end_date'],
                'sent_at'         => date('Y-m-d H:i:s'),
                'status'          => 'sent'
            ]);

            log_message('info', "Subscription {$reminder_type} renewal reminder sent to merchant {$publisher_id} ({$to_email}), expires {$expiry_date_formatted}");
            return true;
        } else {
            $smtp_diag = !empty($CI->CommonModel->last_smtp_error) ? ' [SMTP Error: ' . trim($CI->CommonModel->last_smtp_error) . ']' : ' (check SMTP configuration)';
            $this->last_expiry_error = "Failed: CommonModel->sendCommonHTMLEmail returned false when sending to {$to_email}{$smtp_diag}";
            log_message('error', "Failed sending subscription {$reminder_type} renewal reminder to merchant {$publisher_id} ({$to_email})");
            return false;
        }
    }

    /**
     * Batch check and send expiry reminders for all active merchant subscriptions (cron / batch helper)
     */
    public function check_and_send_all_expiry_reminders()
    {
        $active_subs = $this->db
            ->where('status', 'active')
            ->where('end_date IS NOT NULL', null, false)
            ->get('publisher_subscriptions')
            ->result_array();

        $sent_count = 0;
        foreach ($active_subs as $sub) {
            if (!empty($sub['publisher_id'])) {
                if ($this->check_and_send_expiry_reminders($sub['publisher_id'])) {
                    $sent_count++;
                }
            }
        }

        return [
            'status'     => 'success',
            'checked'    => count($active_subs),
            'sent_count' => $sent_count
        ];
    }

    /**
     * Check if 3:20 PM (15:20) daily expiry reminder check is due, and execute without requiring login
     */
    public function maybe_run_scheduled_daily_reminders()
    {
        // Only run at or after 15:20 (3:20 PM)
        $current_time = (int)date('Hi');
        if ($current_time < 1520) {
            return false;
        }

        $today = date('Y-m-d');
        $this->ensure_reminder_logs_table();

        // Check if daily batch has already run today
        $already_run = $this->db
            ->where('reminder_type', 'daily_cron_batch')
            ->where('DATE(sent_at)', $today)
            ->get('subscription_reminder_logs')
            ->row_array();

        if ($already_run) {
            return false;
        }

        // Lock for today
        $this->db->insert('subscription_reminder_logs', [
            'subscription_id' => 0,
            'publisher_id'    => 0,
            'reminder_type'   => 'daily_cron_batch',
            'email_to'        => 'system',
            'end_date'        => null,
            'sent_at'         => date('Y-m-d H:i:s'),
            'status'          => 'running'
        ]);
        $batch_log_id = $this->db->insert_id();

        $res = $this->check_and_send_all_expiry_reminders();

        $this->db->where('id', $batch_log_id)->update('subscription_reminder_logs', [
            'status' => 'completed: ' . json_encode($res)
        ]);

        return $res;
    }

    /**
     * Ensure invoice columns in subscription_orders exist
     */
    public function ensure_subscription_invoice_schema()
    {
        if (!$this->db->table_exists('subscription_orders')) {
            return;
        }

        if (!$this->db->field_exists('invoice_file', 'subscription_orders')) {
            $this->db->query("ALTER TABLE `subscription_orders` ADD COLUMN `invoice_file` VARCHAR(255) NULL DEFAULT NULL AFTER `payment_timestamp`");
        }
        if (!$this->db->field_exists('end_date', 'subscription_orders')) {
            $this->db->query("ALTER TABLE `subscription_orders` ADD COLUMN `end_date` DATETIME NULL DEFAULT NULL AFTER `payment_timestamp`");
        }
    }

    /**
     * Generate Subscription Invoice PDF using Dompdf and save to uploads/subscription_invoices/
     */
    public function generate_subscription_invoice_pdf($order_id)
    {
        $order = $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();
        if (!$order) {
            log_message('error', "generate_subscription_invoice_pdf: Order not found for ID {$order_id}");
            return false;
        }

        $order['plan'] = $this->get_plan_by_id($order['plan_id']);
        $order['publisher'] = $this->db->get_where('publisher', ['id' => $order['publisher_id']])->row_array();

        $active_sub = $this->get_active_plan($order['publisher_id']);
        if ($active_sub && !empty($active_sub['end_date'])) {
            $order['expiry_date'] = date('d-M-Y', strtotime($active_sub['end_date']));
        }

        $data['order'] = $order;

        // Ensure Dompdf autoloader is loaded
        if (!class_exists('\\Dompdf\\Dompdf')) {
            $autoload_path = APPPATH . '../vendor/autoload.php';
            if (file_exists($autoload_path)) {
                require_once $autoload_path;
            }
        }

        $CI = &get_instance();
        $htmldata = $CI->load->view('invoice/subscription_invoice', $data, true);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', TRUE);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($htmldata);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_output = $dompdf->output();

        $dir = rtrim(FCPATH, '/\\') . '/uploads/subscription_invoices/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $filename = 'subscription_invoice_' . $order['id'] . '.pdf';
        $file_path = $dir . $filename;
        file_put_contents($file_path, $pdf_output);

        $this->ensure_subscription_invoice_schema();
        if ($this->db->field_exists('invoice_file', 'subscription_orders')) {
            $this->db->where('id', $order['id'])->update('subscription_orders', [
                'invoice_file' => $filename
            ]);
        }

        return [
            'file_path' => $file_path,
            'filename'  => $filename,
            'order'     => $order
        ];
    }

    /**
     * Generate invoice and send confirmation email to merchant, with admin CC
     */
    public function generate_and_send_subscription_invoice($order_id)
    {
        $this->ensure_subscription_email_templates();
        $this->ensure_subscription_invoice_schema();

        // 1. Generate Invoice PDF
        $pdf_info = $this->generate_subscription_invoice_pdf($order_id);
        if (!$pdf_info) {
            log_message('error', "Failed generating subscription invoice PDF for Order ID: {$order_id}");
            return false;
        }

        $order = $pdf_info['order'];
        $attachment_path = $pdf_info['file_path'];

        // 2. Fetch Merchant Information
        $publisher = $order['publisher'];
        if (!$publisher || empty($publisher['email'])) {
            log_message('error', "No valid merchant email for Order ID {$order_id} (Publisher ID: {$order['publisher_id']})");
            return false;
        }

        $to_email = trim($publisher['email']);
        $merchant_name = !empty($publisher['publication_name'])
            ? $publisher['publication_name']
            : (!empty($publisher['name']) ? $publisher['name'] : 'Merchant');

        $lang_pref = strtolower($publisher['language'] ?? 'english');
        $lang_code = ($lang_pref === 'fr' || $lang_pref === 'french') ? 'fr' : 'en';

        // 3. Fetch Admin Email for CC
        $CI = &get_instance();
        $CI->load->model('CommonModel');

        $admin_email = $CI->CommonModel->get_custom_variable('admin_email');
        if (empty($admin_email)) {
            $admin_email = $CI->CommonModel->get_custom_variable('contact_us_email');
        }
        if (empty($admin_email)) {
            $admin_email = 'info@yellowmarkets.com';
        }
        $admin_email = trim($admin_email);

        // 4. Subscription & Plan details
        $plan_name = $order['plan']['name'] ?? 'Merchant Subscription';
        if ($lang_code === 'fr' && !empty($order['plan']['name_fr'])) {
            $plan_name = $order['plan']['name_fr'];
        }

        $active_sub = $this->get_active_plan($order['publisher_id']);
        $expiry_date_formatted = (!empty($active_sub['end_date']))
            ? date('d-M-Y', strtotime($active_sub['end_date']))
            : date('d-M-Y', strtotime($order['created_at'] . ' +1 year'));

        $start_date_formatted = (!empty($order['created_at']))
            ? date('d-M-Y', strtotime($order['created_at']))
            : date('d-M-Y');

        $amount_formatted = number_format((float)$order['amount'], 2);
        $subscription_url = base_url('subscription');

        // 5. Template Placeholders
        $tempVars = [
            '##MERCHANT_NAME##',
            '{merchant_name}',
            '##INVOICE_ID##',
            '{invoice_id}',
            '##PLAN_NAME##',
            '{plan_name}',
            '##AMOUNT##',
            '{amount}',
            '##PAYMENT_METHOD##',
            '{payment_method}',
            '##START_DATE##',
            '{start_date}',
            '##EXPIRY_DATE##',
            '{expiry_date}',
            '##SUBSCRIPTION_URL##',
            '{subscription_url}',
            '##YEAR##',
            '{year}'
        ];

        $dynamicVars = [
            $merchant_name,
            $merchant_name,
            $order['id'],
            $order['id'],
            $plan_name,
            $plan_name,
            $amount_formatted,
            $amount_formatted,
            'My.T Money',
            'My.T Money',
            $start_date_formatted,
            $start_date_formatted,
            $expiry_date_formatted,
            $expiry_date_formatted,
            $subscription_url,
            $subscription_url,
            date('Y'),
            date('Y')
        ];

        $custom_subject = ($lang_code === 'fr')
            ? "Renouvellement d'abonnement : Votre abonnement est renouvelé pour {$plan_name} - Facture #{$order['id']}"
            : "Subscription Renewal : Your Subscription is renewed to {$plan_name} - Invoice #{$order['id']}";

        // Send confirmation email + invoice PDF to Merchant
        $mailSent = $CI->CommonModel->sendCommonHTMLEmail(
            $to_email,
            'merchant-subscription-payment-confirmation',
            $tempVars,
            $dynamicVars,
            $lang_code,
            $custom_subject,
            '', // custom_content fallback
            $attachment_path
        );

        // 6. Notifications for Admin and Merchant (In-App and Email)
        try {
            $CI->load->model('Notification_model');

            // In-app Notification to Admin
            $admin_notif_title = 'Subscription Renewal';
            $admin_notif_msg   = "Merchant {$merchant_name} has renewed his subscription plan to {$plan_name}";
            $CI->Notification_model->insert([
                'recipient_type' => 'admin',
                'recipient_id'   => null,
                'title'          => $admin_notif_title,
                'message'        => $admin_notif_msg,
                'data'           => [
                    'order_id'       => $order['id'],
                    'publisher_id'   => $order['publisher_id'],
                    'merchant_name'  => $merchant_name,
                    'plan_name'      => $plan_name,
                    'type'           => 'subscription_renewal'
                ],
                'is_read'        => 0
            ]);

            // In-app Notification to Merchant
            $merchant_notif_title = ($lang_code === 'fr') ? "Renouvellement d'abonnement" : "Subscription Renewal";
            $merchant_notif_msg   = ($lang_code === 'fr') 
                ? "Votre abonnement est renouvelé pour {$plan_name}" 
                : "Your Subscription is renewed to {$plan_name}";

            $CI->Notification_model->insert([
                'recipient_type' => 'merchant',
                'recipient_id'   => (int)$order['publisher_id'],
                'title'          => $merchant_notif_title,
                'message'        => $merchant_notif_msg,
                'data'           => [
                    'order_id'       => $order['id'],
                    'publisher_id'   => $order['publisher_id'],
                    'plan_name'      => $plan_name,
                    'type'           => 'subscription_renewal'
                ],
                'is_read'        => 0
            ]);

            // Email Notification to Admin
            $admin_email_subject = "Subscription Renewal : Merchant {$merchant_name} has renewed his subscription plan to {$plan_name}";
            $admin_email_content = '<div style="text-align: left; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">' .
                '<p>Dear Admin,</p>' .
                "<p>Merchant <strong>{$merchant_name}</strong> has renewed his subscription plan to <strong>{$plan_name}</strong>.</p>" .
                "<div style=\"background-color: #f8f9fa; border-left: 4px solid #f7a800; padding: 15px 20px; margin: 20px 0; text-align: left;\">" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Merchant:</strong> {$merchant_name}</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Order ID:</strong> #{$order['id']}</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>New Plan:</strong> {$plan_name}</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Amount Paid:</strong> MUR {$amount_formatted}</p>" .
                "<p style=\"margin: 0 0 8px 0;\"><strong>Renewal Date:</strong> {$start_date_formatted}</p>" .
                "<p style=\"margin: 0;\"><strong>New Expiry Date:</strong> {$expiry_date_formatted}</p>" .
                "</div>" .
                '<p>Yellow Markets System Notification</p>' .
                '</div>';

            $CI->CommonModel->sendCommonHTMLEmail(
                $admin_email,
                'admin-subscription-renewal-notification',
                [],
                [],
                'en',
                $admin_email_subject,
                $admin_email_content,
                $attachment_path
            );
        } catch (\Throwable $e) {
            log_message('error', 'Error creating/sending subscription renewal notifications: ' . $e->getMessage());
        }

        if ($mailSent) {
            log_message('info', "Subscription payment confirmation email and invoice sent for Order ID: {$order['id']} | To: {$to_email} | CC: {$admin_email}");
            return true;
        } else {
            log_message('error', "Failed to send subscription payment confirmation email for Order ID: {$order['id']} | To: {$to_email} | CC: {$admin_email}");
            return false;
        }
    }
}
