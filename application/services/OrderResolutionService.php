<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * OrderResolutionService
 * 
 * Orchestrator for the Yellow Markets Order Resolution State Machine,
 * Payout Locks, Role Permissions, and Notifications.
 */
class OrderResolutionService
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('OrderResolutionModel');
        $this->ci->load->model('Notification_model');
        $this->ci->load->library('email');
    }

    /**
     * Merchant Action Handler
     */
    public function handle_merchant_action($ticket_id, $merchant_id, $action, array $params = [])
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        if ((int)$ticket->merchant_id !== (int)$merchant_id) {
            return ['status' => false, 'message' => 'Unauthorized merchant action'];
        }

        if (!in_array($ticket->status_code, ['Open', 'ReOpen'])) {
            return ['status' => false, 'message' => 'Cannot perform actions on ticket in current state: ' . $ticket->status_code];
        }

        $now = time();

        switch ($action) {
            case 'refund_approved':
                $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                    'merchant_action'   => 'refund_approved',
                    'updated_at'        => $now
                ]);

                $order_num = $ticket->order_increment_id ?: $ticket->order_id;
                $product_name = $ticket->product_name ?: 'Product Item';
                $shopper_name = trim(($ticket->customer_first_name ?? '') . ' ' . ($ticket->customer_last_name ?? '')) ?: ($ticket->customer_name ?: 'Shopper');
                $merchant_name = $ticket->merchant_name ?: 'Merchant';
                $datetime = date('d-M-Y H:i', $now);

                $audit_notes = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nProduct: {$product_name}\nShopper: {$shopper_name}\nMerchant: {$merchant_name}\nRefund Action: Approved\nUser: Merchant #{$merchant_id}\nDate/Time: {$datetime}";
                $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, $ticket->status_code, 'Merchant approved refund.', 'merchant', $merchant_id, $audit_notes);
                
                // Add system message to thread
                $this->ci->OrderResolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $merchant_name, 'Merchant approved refund. Forwarded to Admin & Accounts.');

                // 1. Send Shopper Email: We Approve Refund
                $shopper_subject = "Order Resolution No.: {$ticket_id} - We Approve Refund";
                $shopper_body = "Dear {$shopper_name},\n\nThe Merchant ({$merchant_name}) has approved your refund request for Order #{$order_num} ({$product_name}).\n\nThe refund process will continue through Yellow Markets Admin and Accounts/Finance team.\n\nTicket Number: {$ticket_id}\nPlease access the Ticket section to view and track your refund progress.";
                $this->dispatch_email_and_notification($ticket, 'refund_approved', $shopper_subject, $shopper_body, 'shopper');

                // 2. Send Admin Notification Email
                $admin_subject = "Order Resolution Update + No.: {$ticket_id}";
                $admin_body = "Ticket Number: {$ticket_id}\nCategory: {$ticket->category}\nOrder Number: {$order_num}\nProduct Name: {$product_name}\nMerchant Name: {$merchant_name}\nShopper Name: {$shopper_name}\nPriority: {$ticket->priority}\nAction: Refund Approved\nDate/Time: {$datetime}\nLink: " . base_url('admin/order-resolution/view/' . $ticket_id);
                $this->dispatch_email_and_notification($ticket, 'admin_action_required', $admin_subject, $admin_body, 'admin');

                return ['status' => true, 'message' => 'Refund approved successfully. Admin has been notified.'];

            case 'refund_denied':
                $reason = trim($params['reason'] ?? 'Refund declined by merchant.');
                $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                    'merchant_action'   => 'refund_denied',
                    'status'            => 2, // Closed
                    'status_code'       => 'Close',
                    'closed_at'         => $now,
                    'closed_by'         => 'merchant_denial',
                    'is_active_dispute' => 0,
                    'updated_at'        => $now
                ]);
                $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Close', 'Merchant Refund Denied', 'merchant', $merchant_id, $reason);

                // Add message
                $this->ci->OrderResolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $ticket->merchant_name ?: 'Merchant', 'Refund Denied by Merchant. Reason: ' . $reason);

                // Restore payout if no other disputes
                $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

                // Notify Shopper & Admin
                $this->send_resolution_notification($ticket, 'refund_denied', 'Refund Declined', 'Merchant declined refund for Ticket #' . $ticket_id . '. You may submit a Resolution Request if you wish to dispute this.');
                $this->send_merchant_action_notification($ticket, 'Deny Refund');

                return ['status' => true, 'message' => 'Refund request denied. Ticket closed.'];

            case 'replacement_approved':
                $delivery_option = trim($params['delivery_option'] ?? '');
                if (!in_array($delivery_option, ['own_delivery', 'self_pickup', 'ym_delivery'])) {
                    return ['status' => false, 'message' => 'Please select a valid delivery option (Own Delivery, Self Pickup, or YM Delivery).'];
                }

                $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                    'merchant_action'   => 'replacement_approved',
                    'delivery_option'   => $delivery_option,
                    'updated_at'        => $now
                ]);
                $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, $ticket->status_code, 'Merchant Replacement Approved (' . $delivery_option . ')', 'merchant', $merchant_id);

                // Add message
                $opt_labels = ['own_delivery' => 'Own Delivery', 'self_pickup' => 'Self Pickup', 'ym_delivery' => 'YM Delivery'];
                $msg_text = 'Replacement Approved by Merchant via ' . ($opt_labels[$delivery_option] ?? $delivery_option) . '.';
                $this->ci->OrderResolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $ticket->merchant_name ?: 'Merchant', $msg_text);

                // Notify Shopper & Admin
                $this->send_resolution_notification($ticket, 'replacement_approved', 'Replacement Approved', 'Merchant approved replacement via ' . ($opt_labels[$delivery_option] ?? $delivery_option));
                $this->send_merchant_action_notification($ticket, 'Approve Replacement');

                return ['status' => true, 'message' => 'Replacement approved via ' . ($opt_labels[$delivery_option] ?? $delivery_option)];

            case 'replacement_completed':
                if ($ticket->merchant_action !== 'replacement_approved' || empty($ticket->delivery_option) || $ticket->delivery_option === 'none') {
                    return ['status' => false, 'message' => 'Replacement Completed is only available after Replacement is Approved and Delivery Option is selected.'];
                }

                $dispatch_notes = trim($params['dispatch_notes'] ?? 'Replacement dispatched/ready.');

                $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                    'merchant_action'   => 'replacement_completed',
                    'status'            => 2, // Closed
                    'status_code'       => 'Close',
                    'closed_at'         => $now,
                    'closed_by'         => 'merchant_completed',
                    'is_active_dispute' => 0,
                    'updated_at'        => $now
                ]);
                $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Close', 'Merchant Replacement Completed', 'merchant', $merchant_id, $dispatch_notes);

                // Add message
                $this->ci->OrderResolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $ticket->merchant_name ?: 'Merchant', 'Replacement Completed by Merchant: ' . $dispatch_notes);

                // Restore payout if no other disputes
                $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

                // Notify Shopper & Admin
                $this->send_resolution_notification($ticket, 'replacement_completed', 'Replacement Completed', 'Merchant has completed your item replacement.');
                $this->send_merchant_action_notification($ticket, 'Replacement Completed');

                return ['status' => true, 'message' => 'Replacement completed. Ticket resolved.'];

            case 'replacement_denied':
                $reason = trim($params['reason'] ?? 'Replacement denied by merchant.');
                $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                    'merchant_action'   => 'replacement_denied',
                    'status'            => 2, // Closed
                    'status_code'       => 'Close',
                    'closed_at'         => $now,
                    'closed_by'         => 'merchant_denial',
                    'is_active_dispute' => 0,
                    'updated_at'        => $now
                ]);
                $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Close', 'Merchant Replacement Denied', 'merchant', $merchant_id, $reason);

                // Add message
                $this->ci->OrderResolutionModel->add_message($ticket_id, 'merchant', $merchant_id, $ticket->merchant_name ?: 'Merchant', 'Replacement Denied by Merchant. Reason: ' . $reason);

                // Restore payout if no other disputes
                $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

                // Notify Shopper & Admin
                $this->send_resolution_notification($ticket, 'replacement_denied', 'Replacement Declined', 'Merchant declined replacement for Ticket #' . $ticket_id . '. You may submit a Resolution Request if you wish to dispute this.');
                $this->send_merchant_action_notification($ticket, 'Deny Replacement');

                return ['status' => true, 'message' => 'Replacement denied. Ticket closed.'];

            default:
                return ['status' => false, 'message' => 'Unknown merchant action'];
        }
    }

    /**
     * Admin Assigns Ticket to @Account
     */
    public function admin_assign_account($ticket_id, $admin_id, $account_user_id = null)
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        if (!in_array($ticket->status_code, ['Open', 'ReOpen', 'Processing'])) {
            return ['status' => false, 'message' => 'Ticket cannot be assigned in current status: ' . $ticket->status_code];
        }

        $now = time();
        $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'status_code'       => 'Processing',
            'assigned_role'     => 'Account',
            'assigned_to'       => !empty($account_user_id) ? (int)$account_user_id : null,
            'assigned_at'       => $now,
            'updated_at'        => $now
        ]);

        $order_num = $ticket->order_increment_id ?: $ticket->order_id;
        $product_name = $ticket->product_name ?: 'Product Item';
        $shopper_name = trim(($ticket->customer_first_name ?? '') . ' ' . ($ticket->customer_last_name ?? '')) ?: ($ticket->customer_name ?: 'Shopper');
        $merchant_name = $ticket->merchant_name ?: 'Merchant';
        $refund_amount = (float)($ticket->refund_amount > 0 ? $ticket->refund_amount : ($ticket->item_price ?? 0.00));
        $datetime = date('d-M-Y H:i', $now);

        $account_user_name = 'Accounts Team';
        if (!empty($account_user_id)) {
            $u = $this->ci->db->select('name')->where('id', (int)$account_user_id)->get('adminusers')->row();
            if ($u && !empty($u->name)) {
                $account_user_name = $u->name;
            }
        }

        $audit_notes = "Assigned To: {$account_user_name} (ID #{$account_user_id})\nDate/Time: {$datetime}";
        $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Processing', 'Ticket assigned to Account.', 'admin', $admin_id, $audit_notes);

        $this->ci->OrderResolutionModel->add_message($ticket_id, 'admin', $admin_id, 'Admin', "Ticket assigned to Account ({$account_user_name}). Status changed to Processing.");

        // 1. Send Merchant Notification: Refund initiated
        $merchant_subject = "Order Resolution No.: {$ticket_id} - Refund initiated";
        $merchant_body = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nProduct: {$product_name}\nRefund amount: MUR " . number_format($refund_amount, 2) . "\nStatus: Processing\n\nThe refund has been initiated and is being processed by Account/Finance.";
        $this->dispatch_email_and_notification($ticket, 'refund_initiated', $merchant_subject, $merchant_body, 'merchant');

        // 2. Send Account Notification: Refund Request
        $account_subject = "Order Resolution No. {$ticket_id} - Refund Request";
        $holdback_balance = $this->ci->OrderResolutionModel->get_merchant_holdback_balance($ticket->merchant_id);
        $account_body = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nProduct Name: {$product_name}\nMerchant Name: {$merchant_name}\nShopper Name: {$shopper_name}\nRefund Amount: MUR " . number_format($refund_amount, 2) . "\nOriginal refund reason/message:\n{$ticket->message}\n\nMerchant refund approval: Approved\nLink: " . base_url('admin/order-resolution/view/' . $ticket_id) . "\n\nRelevant Transaction Info:\nSub-order #{$ticket->b2b_order_id}\nEligible 15-day Hold-back Balance: MUR " . number_format($holdback_balance, 2);
        $this->dispatch_email_and_notification($ticket, 'account_refund_request', $account_subject, $account_body, 'admin');

        return ['status' => true, 'message' => 'Ticket assigned to @Account. Status updated to Processing.'];
    }

    /**
     * @Account Executes Refund from Merchant 15-Day Hold-back & Marks Done
     */
    public function account_mark_done($ticket_id, $account_user_id, $params = [])
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        if ($ticket->status_code !== 'Processing') {
            return ['status' => false, 'message' => 'Only tickets in Processing state can be marked Done by Accounts.'];
        }

        if ((int)$ticket->refund_deducted_from_holdback === 1) {
            return ['status' => false, 'message' => 'Refund has already been deducted and processed for this ticket.'];
        }

        if (is_string($params)) {
            $params = ['notes' => $params];
        }

        $now = time();
        $holdback_balance = $this->ci->OrderResolutionModel->get_merchant_holdback_balance($ticket->merchant_id);
        $refund_amount = !empty($params['refund_amount']) ? (float)$params['refund_amount'] : (float)($ticket->refund_amount > 0 ? $ticket->refund_amount : ($ticket->item_price ?? 0.00));

        if ($refund_amount <= 0) {
            $refund_amount = (float)($ticket->item_price ?? 0.00);
        }

        // Validate holdback balance
        if ($holdback_balance > 0 && $refund_amount > $holdback_balance) {
            return [
                'status' => false,
                'message' => 'Refund amount (MUR ' . number_format($refund_amount, 2) . ') exceeds available 15-day hold-back sales balance (MUR ' . number_format($holdback_balance, 2) . ').'
            ];
        }

        $refund_reference = trim($params['refund_reference'] ?? ('REF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6))));
        $notes = trim($params['notes'] ?? '');

        $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'status_code'                   => 'Done',
            'refund_amount'                 => $refund_amount,
            'refund_deducted_from_holdback' => 1,
            'refund_reference'              => $refund_reference,
            'refund_completed_at'           => $now,
            'refund_completed_by'           => (int)$account_user_id,
            'updated_at'                    => $now
        ]);

        $order_num = $ticket->order_increment_id ?: $ticket->order_id;
        $datetime = date('d-M-Y H:i', $now);

        $audit_notes = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nProduct ID: {$ticket->products}\nMerchant ID: {$ticket->merchant_id}\nShopper ID: {$ticket->customer_id}\nRefund Amount: MUR " . number_format($refund_amount, 2) . "\nSource: Merchant 15-day hold-back\nProcessed By: Account user #{$account_user_id}\nProcessed Date/Time: {$datetime}\nRefund Reference: {$refund_reference}\nPrevious ticket status: Processing\nNew ticket status: Done\nNotes: {$notes}";
        $this->ci->OrderResolutionModel->log_audit($ticket_id, 'Processing', 'Done', 'Refund completed by Account.', 'account', $account_user_id, $audit_notes);

        $this->ci->OrderResolutionModel->add_message($ticket_id, 'account', $account_user_id, 'Accounts Team', 'Refund of MUR ' . number_format($refund_amount, 2) . ' processed from Merchant 15-day hold-back sales. Ref: ' . $refund_reference . '. Status set to Done. ' . $notes);

        // Notify Admin
        $admin_subject = "Order Resolution Update + No.: {$ticket_id}";
        $admin_body = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nAction: Refund Completed by Account\nRefund Amount: MUR " . number_format($refund_amount, 2) . "\nRefund Reference: {$refund_reference}\nStatus: Done\n\nTicket is ready for Admin final closure.";
        $this->dispatch_email_and_notification($ticket, 'account_done', $admin_subject, $admin_body, 'admin');

        return ['status' => true, 'message' => 'Refund completed by Account. Status updated to Done. Ready for Admin final closure.'];
    }

    /**
     * @Admin Closes Ticket (Normal Flow)
     */
    public function admin_close_ticket($ticket_id, $admin_id, $closure_notes = '')
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        // Prevent Admin from closing ticket if it was a refund and refund has not been completed
        if ($ticket->merchant_action === 'refund_approved' || $ticket->category === 'Refund') {
            if ($ticket->status_code !== 'Done' || (int)$ticket->refund_deducted_from_holdback !== 1) {
                return [
                    'status' => false, 
                    'message' => 'Cannot close ticket. The refund must be completed by @Account and marked Done first.'
                ];
            }
        }

        if (!in_array($ticket->status_code, ['Open', 'Done'])) {
            return ['status' => false, 'message' => 'Ticket cannot be closed in current state: ' . $ticket->status_code];
        }

        $now = time();
        $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'status'            => 2, // Closed
            'status_code'       => 'Close',
            'closed_at'         => $now,
            'closed_by'         => 'admin_' . $admin_id,
            'is_active_dispute' => 0,
            'updated_at'        => $now
        ]);

        $order_num = $ticket->order_increment_id ?: $ticket->order_id;
        $product_name = $ticket->product_name ?: 'Product Item';
        $shopper_name = trim(($ticket->customer_first_name ?? '') . ' ' . ($ticket->customer_last_name ?? '')) ?: ($ticket->customer_name ?: 'Shopper');
        $refund_amount = (float)($ticket->refund_amount > 0 ? $ticket->refund_amount : ($ticket->item_price ?? 0.00));
        $refund_ref = $ticket->refund_reference ?: ('REF-' . date('Ymd'));
        $datetime = date('d-M-Y H:i', $now);

        $audit_notes = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nClosed By: Admin #{$admin_id}\nDate/Time: {$datetime}\nNotes: {$closure_notes}";
        $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Close', 'Resolution ticket closed by Admin.', 'admin', $admin_id, $audit_notes);

        $this->ci->OrderResolutionModel->add_message($ticket_id, 'admin', $admin_id, 'Admin', 'Resolution ticket closed by Admin. ' . $closure_notes);

        // Restore normal transaction visibility and payout
        $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

        // 1. Send Merchant Closed Email
        $merchant_subject = "Order Resolution No.: {$ticket_id} - Closed";
        $merchant_body = "Ticket Number: {$ticket_id}\nOrder Number: {$order_num}\nProduct: {$product_name}\nRefund amount: MUR " . number_format($refund_amount, 2) . "\nRefund completed: Yes\nResolution ticket closed: Yes";
        $this->dispatch_email_and_notification($ticket, 'ticket_closed', $merchant_subject, $merchant_body, 'merchant');

        // 2. Send Shopper Refund Email
        $shopper_subject = "Order Resolution No.: {$ticket_id} - You Are Being Refunded";
        $shopper_body = "Dear {$shopper_name},\n\nYour refund has been processed/initiated for Order #{$order_num} ({$product_name}).\n\nRefund Amount: MUR " . number_format($refund_amount, 2) . "\nOrder Number: {$order_num}\nProduct: {$product_name}\nTicket Number: {$ticket_id}\nRefund Reference: {$refund_ref}\n\nPlease note: depending on your payment method / card issuer, funds will appear in your account according to standard banking processing timelines.";
        $this->dispatch_email_and_notification($ticket, 'ticket_closed', $shopper_subject, $shopper_body, 'shopper');

        return ['status' => true, 'message' => 'Resolution ticket closed by Admin. Payout status restored.'];
    }

    /**
     * Shopper Escalates via Resolution Request (ReOpen)
     */
    public function shopper_resolution_request($ticket_id, $customer_id, $dispute_reason)
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        if ((int)$ticket->customer_id !== (int)$customer_id) {
            return ['status' => false, 'message' => 'Unauthorized'];
        }

        if ($ticket->status_code !== 'Close') {
            return ['status' => false, 'message' => 'Resolution Request can only be raised on closed tickets.'];
        }

        if ($ticket->resolution_status === 'resolution_denied') {
            return ['status' => false, 'message' => 'This dispute has already received a final decision and cannot be reopened.'];
        }

        $now = time();
        $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'status'                    => 1, // Open again
            'status_code'               => 'ReOpen',
            'resolution_status'         => 'resolution_requested',
            'resolution_requested_at'   => $now,
            'is_active_dispute'         => 1, // Re-lock payout!
            'updated_at'                => $now
        ]);

        $this->ci->OrderResolutionModel->log_audit($ticket_id, 'Close', 'ReOpen', 'Shopper Resolution Request', 'shopper', $customer_id, $dispute_reason);

        $this->ci->OrderResolutionModel->add_message($ticket_id, 'shopper', $customer_id, 'Shopper', 'Shopper Escalation: Resolution Request submitted to Admin. Reason: ' . $dispute_reason);

        // Re-hold payout for this sub-order
        if (!empty($ticket->b2b_order_id)) {
            $this->ci->db->where('order_id', (int)$ticket->b2b_order_id)
                ->where('payout_status !=', 4)
                ->update('b2b_orders', ['payout_status' => 3]);
        }

        // Notify Admin urgently
        $this->send_resolution_notification($ticket, 'resolution_requested', 'URGENT: Shopper Resolution Request - Ticket ' . $ticket_id, 'Shopper has escalated Ticket #' . $ticket_id . ' to Admin for dispute investigation.', 'admin');

        return ['status' => true, 'message' => 'Resolution Request submitted. Ticket is now under Admin investigation.'];
    }

    /**
     * @Admin Investigates Dispute & Decides (Approved or Denied)
     */
    public function admin_resolution_decision($ticket_id, $admin_id, $decision, $decision_notes = '')
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        if ($ticket->status_code !== 'ReOpen') {
            return ['status' => false, 'message' => 'Resolution decisions can only be made on ReOpen tickets.'];
        }

        $now = time();

        if ($decision === 'approved') {
            // Resolution Approved -> Assign @Account -> Processing
            $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                'status_code'       => 'Processing',
                'resolution_status' => 'resolution_approved',
                'assigned_role'     => 'Account',
                'updated_at'        => $now
            ]);

            $this->ci->OrderResolutionModel->log_audit($ticket_id, 'ReOpen', 'Processing', 'Admin Approved Dispute Resolution', 'admin', $admin_id, $decision_notes);

            $this->ci->OrderResolutionModel->add_message($ticket_id, 'admin', $admin_id, 'Admin', 'Admin Investigation Decision: Resolution Approved. Forwarded to @Account for Shopper refund. ' . $decision_notes);

            // Notify Account, Merchant, and Shopper
            $this->send_resolution_notification($ticket, 'resolution_approved', 'Dispute Resolution Approved', 'Admin approved dispute for Ticket #' . $ticket_id . '. Processing refund.');
            $this->send_resolution_notification($ticket, 'resolution_approved_merchant', 'Dispute Resolution Overruled by Admin', 'Admin approved Shopper dispute for Ticket #' . $ticket_id . '. Refund will be deducted from hold-back.', 'merchant');
            $this->send_resolution_notification($ticket, 'assigned_to_account', 'Action Required: Resolution Refund Ticket ' . $ticket_id, 'Process resolution refund for Ticket #' . $ticket_id, 'admin');

            return ['status' => true, 'message' => 'Resolution approved. Assigned to @Account for refund.'];
        } else {
            // Resolution Denied -> Close (Final)
            $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
                'status'            => 2, // Closed
                'status_code'       => 'Close (Final)',
                'resolution_status' => 'resolution_denied',
                'closed_at'         => $now,
                'closed_by'         => 'admin_final_denial',
                'is_active_dispute' => 0,
                'updated_at'        => $now
            ]);

            $this->ci->OrderResolutionModel->log_audit($ticket_id, 'ReOpen', 'Close (Final)', 'Admin Denied Dispute Resolution', 'admin', $admin_id, $decision_notes);

            $this->ci->OrderResolutionModel->add_message($ticket_id, 'admin', $admin_id, 'Admin', 'Admin Investigation Decision: Resolution Denied. Ticket closed permanently. ' . $decision_notes);

            // Restore normal payout
            $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

            // Notify Merchant & Shopper
            $this->send_resolution_notification($ticket, 'resolution_denied', 'Dispute Resolution Decision: Denied', 'Admin has completed dispute investigation for Ticket #' . $ticket_id . '. Resolution was denied. Case is permanently closed.');
            $this->send_resolution_notification($ticket, 'resolution_denied_merchant', 'Dispute Resolution Denied', 'Admin upheld denial on Ticket #' . $ticket_id . '. Payout will be restored.', 'merchant');

            return ['status' => true, 'message' => 'Resolution denied. Ticket permanently closed as Close (Final).'];
        }
    }

    /**
     * @Admin Closes (Final) post-resolution refund
     */
    public function admin_close_final($ticket_id, $admin_id, $closure_notes = '')
    {
        $ticket = $this->ci->OrderResolutionModel->get_ticket_by_number($ticket_id);
        if (!$ticket) {
            return ['status' => false, 'message' => 'Ticket not found'];
        }

        $now = time();
        $this->ci->db->where('ticket_id', $ticket_id)->update('help_desk', [
            'status'            => 2, // Closed
            'status_code'       => 'Close (Final)',
            'closed_at'         => $now,
            'closed_by'         => 'admin_final',
            'is_active_dispute' => 0,
            'updated_at'        => $now
        ]);

        $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, 'Close (Final)', 'Admin Marked Final Closure', 'admin', $admin_id, $closure_notes);

        $this->ci->OrderResolutionModel->add_message($ticket_id, 'admin', $admin_id, 'Admin', 'Ticket permanently closed by Admin (Close Final). ' . $closure_notes);

        // Restore normal transaction visibility and payout
        $this->evaluate_and_restore_payout($ticket->b2b_order_id, $ticket->merchant_id);

        // Notify Merchant & Shopper
        $this->send_resolution_notification($ticket, 'ticket_closed_final', 'Final Resolution Closure - Ticket ' . $ticket_id, 'Dispute resolution has been finalized and closed permanently.');
        $this->send_resolution_notification($ticket, 'ticket_closed_final', 'Dispute Case Finalized: ' . $ticket_id, 'Ticket #' . $ticket_id . ' permanently closed by Admin.', 'merchant');

        return ['status' => true, 'message' => 'Ticket permanently closed as Close (Final).'];
    }

    /**
     * Helper to restore payout eligibility if no other active disputes exist for this sub-order
     */
    public function evaluate_and_restore_payout($b2b_order_id, $merchant_id)
    {
        if (empty($b2b_order_id)) {
            return;
        }

        // Check if ANY other active dispute exists on this sub-order
        $other_disputes = $this->ci->db->select('id')
            ->from('help_desk')
            ->where('b2b_order_id', (int)$b2b_order_id)
            ->where_in('status_code', ['Open', 'Processing', 'ReOpen'])
            ->where('is_active_dispute', 1)
            ->count_all_results();

        if ($other_disputes === 0) {
            // Restore payout status to 1 (Active) if currently 3 (Hold)
            $this->ci->db->where('order_id', (int)$b2b_order_id)
                ->where('payout_status', 3)
                ->update('b2b_orders', ['payout_status' => 1]);
        }
    }

    /**
     * Dispatch New Ticket Notifications to Merchant and @Admin
     */
    public function send_new_ticket_notifications($ticket, $shopper_message)
    {
        $ticket_id = $ticket->ticket_id;
        $order_num = $ticket->order_increment_id ?: $ticket->order_id;
        $product_name = $ticket->product_name ?: 'Product Item';
        $merchant_name = $ticket->merchant_name ?: 'Merchant';
        $shopper_name = trim(($ticket->customer_first_name ?? '') . ' ' . ($ticket->customer_last_name ?? '')) ?: ($ticket->customer_name ?: 'Shopper');

        // 1. Merchant Notification
        $merchant_subject = 'Order Resolution + No.: ' . $ticket_id;
        $merchant_body = "Category: {$ticket->category}\n\nOrder Number: {$order_num}\n\nProduct Name: {$product_name}\n\nPriority: {$ticket->priority}\n\nMessage:\n{$shopper_message}\n\nPlease access the Ticket section to view and respond to this ticket.";
        $this->dispatch_email_and_notification($ticket, 'new_ticket', $merchant_subject, $merchant_body, 'merchant');

        // 2. Admin Notification
        $admin_subject = 'Order Resolution No.: ' . $ticket_id;
        $admin_body = "Category: {$ticket->category}\n\nMerchant Name: {$merchant_name}\n\nShopper Name: {$shopper_name}\n\nOrder Number: {$order_num}\n\nProduct Name: {$product_name}\n\nPriority: {$ticket->priority}\n\nOriginal Message:\n{$shopper_message}";
        $this->dispatch_email_and_notification($ticket, 'new_ticket', $admin_subject, $admin_body, 'admin');
    }

    /**
     * Merchant Reply -> Shopper Notification
     */
    public function send_merchant_reply_notification($ticket)
    {
        $subject = 'Order Resolution Update + No.: ' . $ticket->ticket_id;
        $body = "Merchant has responded to your ticket.\n\nPlease access the Ticket section to view the response and reply if required.";
        $this->dispatch_email_and_notification($ticket, 'merchant_reply', $subject, $body, 'shopper');
    }

    /**
     * Shopper Reply -> Merchant Notification
     */
    public function send_shopper_reply_notification($ticket)
    {
        $subject = 'Order Resolution Update + No.: ' . $ticket->ticket_id;
        $body = "Shopper has responded to ticket {$ticket->ticket_id}.\n\nPlease access the Ticket section to view the response and take the necessary action.";
        $this->dispatch_email_and_notification($ticket, 'shopper_reply', $subject, $body, 'merchant');
    }

    /**
     * Merchant Button Action -> Help/Admin Notification
     */
    public function send_merchant_action_notification($ticket, $action_name)
    {
        $ticket_id = $ticket->ticket_id;
        $order_num = $ticket->order_increment_id ?: $ticket->order_id;
        $product_name = $ticket->product_name ?: 'Product Item';
        $merchant_name = $ticket->merchant_name ?: 'Merchant';
        $datetime = date('d-M-Y H:i');

        $subject = 'Order Resolution Update + No.: ' . $ticket_id;
        $body = "Merchant has clicked on a button in ticket {$ticket_id}.\n\nAction: {$action_name}\n\nOrder Number: {$order_num}\n\nProduct Name: {$product_name}\n\nPlease access the Ticket section to review the action.";

        // Dispatch notification to Admin
        $this->dispatch_email_and_notification($ticket, 'merchant_action', $subject, $body, 'admin');

        // Record structured action in audit log
        $log_text = "Ticket #{$ticket_id}\nMerchant: {$merchant_name}\nAction: {$action_name}\nDate/Time: {$datetime}";
        $this->ci->OrderResolutionModel->log_audit($ticket_id, $ticket->status_code, $ticket->status_code, 'Merchant Button Action: ' . $action_name, 'merchant', $ticket->merchant_id, $log_text);
    }

    /**
     * Internal Dispatcher: Email + In-App Notification with Idempotency
     */
    public function dispatch_email_and_notification($ticket, $subtype, $subject, $message, $recipient_type = 'shopper')
    {
        $ticket_id = $ticket->ticket_id;
        $recipient_id = null;

        if ($recipient_type === 'shopper') {
            $recipient_id = (int)$ticket->customer_id;
        } elseif ($recipient_type === 'merchant') {
            $recipient_id = (int)$ticket->merchant_id;
        } elseif ($recipient_type === 'admin') {
            $recipient_id = 1; // Default Admin
        }

        // Check duplicate within last 5 minutes
        $five_min_ago = date('Y-m-d H:i:s', time() - 300);
        $exists = $this->ci->db->from('notifications')
            ->where('recipient_type', $recipient_type)
            ->where('recipient_id', $recipient_id)
            ->where('subtype', $subtype)
            ->like('message', $ticket_id)
            ->where('created_at >=', $five_min_ago)
            ->count_all_results();

        if ($exists > 0) {
            return; // Duplicate prevented!
        }

        // Insert in-app notification
        $this->ci->Notification_model->insert([
            'recipient_type' => $recipient_type,
            'recipient_id'   => $recipient_id,
            'type'           => 'helpdesk',
            'subtype'        => $subtype,
            'title'          => $subject,
            'message'        => nl2br(htmlspecialchars($message)),
            'data'           => ['ticket_id' => $ticket_id, 'order_id' => $ticket->order_id],
            'is_read'        => 0
        ]);

        // Attempt transactional email delivery
        try {
            $to_email = null;
            if ($recipient_type === 'shopper') {
                $to_email = $ticket->customer_email;
            } elseif ($recipient_type === 'merchant') {
                $merchant_user = $this->ci->db->select('email')->where('id', (int)$ticket->merchant_id)->get('users')->row();
                $to_email = $merchant_user ? $merchant_user->email : null;
            } elseif ($recipient_type === 'admin') {
                $admin_user = $this->ci->db->select('email')->where('id', 1)->get('adminusers')->row();
                $to_email = $admin_user ? $admin_user->email : 'support@yellowmarkets.mu';
            }

            if (!empty($to_email) && filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                $this->ci->email->from('no-reply@yellowmarkets.mu', 'Yellow Markets Resolution');
                $this->ci->email->to($to_email);
                $this->ci->email->subject($subject);
                $this->ci->email->message("<div style='font-family:sans-serif;line-height:1.6;color:#333;'>" . nl2br(htmlspecialchars($message)) . "</div>");
                @$this->ci->email->send();
            }
        } catch (\Exception $e) {
            log_message('error', 'Resolution email dispatch failed: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch notification & email with anti-duplication idempotency check (Backward Compatible)
     */
    public function send_resolution_notification($ticket, $subtype, $title, $message, $recipient_type = 'shopper')
    {
        $this->dispatch_email_and_notification($ticket, $subtype, $title, $message, $recipient_type);
    }
}
