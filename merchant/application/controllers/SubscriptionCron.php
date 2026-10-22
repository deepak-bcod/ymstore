<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SubscriptionCron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Subscription_model');
    }

    /**
     * CLI / HTTP endpoint to check and send expiry reminders without requiring merchant login
     *
     * Triggers automatically without login at 3:20 PM daily or on-demand:
     * - Web URL: http://mu.yellowmarkets.com/merchant/SubscriptionCron/send_reminders
     * - Localhost: http://localhost/ymstore/merchant/SubscriptionCron/send_reminders
     * - CLI: php index.php SubscriptionCron send_reminders
     */
    public function send_reminders()
    {
        $force = $this->input->get('force'); // '1_month' or '1_week' for instant manual testing
        $result = $this->Subscription_model->check_and_send_all_expiry_reminders($force);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'    => 'success',
                'executed'  => date('Y-m-d H:i:s'),
                'force'     => $force ?: 'none (normal 30-day and 7-day schedule)',
                'result'    => $result
            ], JSON_PRETTY_PRINT));
    }

    /**
     * Check if 3:20 PM schedule is reached today, and run if not already executed today
     */
    public function check_daily_schedule()
    {
        $result = $this->Subscription_model->maybe_run_scheduled_daily_reminders();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'    => 'success',
                'timestamp' => date('Y-m-d H:i:s'),
                'executed'  => ($result !== false),
                'result'    => $result
            ]));
    }
}
