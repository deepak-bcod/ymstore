<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Giftcard_model extends CI_Model
{
    public function get_values($only_active = true)
    {
        $this->db->select('*')->from('gift_card_values');
        if ($only_active) $this->db->where('active', 1);
        return $this->db->order_by('amount', 'ASC')->get()->result();
    }

    public function get_value($id)
    {
        return $this->db->get_where('gift_card_values', ['id' => $id])->row();
    }

    public function create_order($data)
    {
        $this->db->insert('gift_card_orders', $data);
        return $this->db->insert_id();
    }

    public function get_order($order_id)
    {
        return $this->db->get_where('gift_card_orders', ['id' => $order_id])->row();
    }

    public function get_gift_card_by_orderid($order_id)
    {
        return $this->db->get_where('gift_cards', ['order_id' => $order_id])->row();
    }

    public function get_order_by_number($order_number)
    {
        return $this->db->get_where('gift_card_orders', ['order_number' => $order_number])->row();
    }

    public function mark_order_paid($order_id, $payment_ref)
    {
        $this->db->where('id', $order_id)->update('gift_card_orders', [
            'status' => 1,
            'payment_ref' => $payment_ref,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function mark_order_failed($order_id)
    {
        $this->db->where('id', $order_id)->update('gift_card_orders', [
            'status' => 2,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function mark_email_sent($order_id)
    {
        if ($this->db->field_exists('email_sent', 'gift_card_orders')) {
            $this->db->where('id', $order_id)->update('gift_card_orders', [
                'email_sent' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function is_email_sent($order_id)
    {
        if ($this->db->field_exists('email_sent', 'gift_card_orders')) {
            $order = $this->get_order($order_id);
            return ($order && !empty($order->email_sent));
        }
        return false;
    }


    public function issue_giftcard($order_id)
    {
        $order = $this->get_order($order_id);
        if (!$order || $order->status != 1) return false;

        // generate unique code
        $code = $this->generate_unique_code();

        $insert = [
            'code' => $code,
            'order_id' => $order->id,
            'initial_value' => $order->amount,
            'balance' => $order->amount,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('gift_cards', $insert);
        $gift_card_id = $this->db->insert_id();

        if ($gift_card_id) {
            // Automatically add transaction for the gift card credit to the receiver
            $receiver_id = $this->get_user_id($order->receiver_email);
            $this->add_transaction($receiver_id, $gift_card_id, 'credit', $order->amount, 1);
            return (object) array_merge($insert, ['id' => $gift_card_id]);
        }

        return false;
    }

    private function generate_unique_code($length = 10)
    {
        do {
            $code = 'GC' . strtoupper(substr(bin2hex(random_bytes(6)), 0, $length));
            $exists = $this->db->get_where('gift_cards', ['code' => $code])->row();
        } while ($exists);
        return $code;
    }

    // Add transaction
    public function add_transaction($user_id, $gift_card_id, $type, $amount, $status = 1)
    {
        $this->db->insert('gift_card_transactions', [
            'user_id' => $user_id,
            'gift_card_id' => $gift_card_id,
            'type' => $type, // credit or debit
            'amount' => $amount,
            'status' => $status, // 1=completed
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function get_user_email($user_id){
        $user = $this->db
            ->select('email_id')
            ->from('customers')
            ->where('id', $user_id)
            ->get()
            ->row();

        return $email = !empty($user) ? $user->email_id : '';
    }

    public function get_user_id($email){
        $user = $this->db
            ->select('id')
            ->from('customers')
            ->where('email_id', $email)
            ->get()
            ->row();

        return $user_id = !empty($user) ? $user->id : 0;
    }

    // Get all gift cards used by the user (via transactions)
    public function get_user_giftcards_by_email($email)
    {
        $this->db->select('gc.*');
        $this->db->from('gift_cards gc');
        $this->db->join('gift_card_orders gco', 'gc.order_id = gco.id');
        $this->db->where('gco.receiver_email', $email);
        $this->db->group_by('gc.id'); // avoid duplicates
        return $this->db->get()->result();
    }

    public function get_user_balance($user_id)
    {
        $this->db->select("SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance", false);
        $this->db->where('user_id', $user_id);

        if ($this->db->field_exists('status', 'gift_card_transactions')) {
            $this->db->where('status', 1); // only completed transactions
        }

        $query = $this->db->get('gift_card_transactions');

        return $query->row()->balance ?? 0;
    }

    public function get_user_transactions($user_id)
    {
        $this->db->select('gct.*, gc.code as gift_card_code, gco.order_number');
        $this->db->from('gift_card_transactions gct');
        $this->db->join('gift_cards gc', 'gct.gift_card_id = gc.id OR ((gct.gift_card_id IS NULL OR gct.gift_card_id = 0) AND gct.order_id IS NOT NULL AND gct.order_id = gc.order_id)', 'left');
        $this->db->join('gift_card_orders gco', 'gc.order_id = gco.id OR gct.order_id = gco.id', 'left');
        $this->db->where('gct.user_id', $user_id);
        if ($this->db->field_exists('status', 'gift_card_transactions')) {
            $this->db->where('gct.status', 1);
        }
        $this->db->order_by('gct.created_at', 'DESC');
        return $this->db->get()->result();
    }

    // Get all gift cards used by the user (via transactions)
    public function get_user_giftcards($user_id)
    {
        $this->db->select('gc.*');
        $this->db->from('gift_cards gc');
        $this->db->join('gift_card_transactions gct', 'gc.id = gct.gift_card_id');
        $this->db->where('gct.user_id', $user_id);
        $this->db->group_by('gc.id'); // avoid duplicates
        return $this->db->get()->result();
    }

    // ✅ Get gift card details
    public function getGiftCardDetails($code)
    {
        $query = $this->db->get_where('gift_cards', ['code' => $code, 'status' => 1]);
        return $query->row_array();
    }

    // ✅ Get cart by session ID
    public function getCartBySession($session_id)
    {
        $query = $this->db->get_where('sales_quote', ['session_id' => $session_id]);
        return $query->row_array();
    }

    // ✅ Update gift card balance
    public function updateGiftCardBalance($giftcard_id, $new_balance)
    {
        $data = [
            'balance'    => $new_balance,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $giftcard_id);
        return $this->db->update('gift_cards', $data);
    }

    // ✅ Update cart total
    public function updateCartTotalAfterGiftCard($quote_id, $new_total, $gift_code, $used_amount)
    {
        $data = [
            'grand_total'     => $new_total,
            'voucher_code'    => $gift_code,
            'voucher_amount'  => $used_amount,
            'updated_at'      => date('Y-m-d H:i:s')
        ];
        $this->db->where('quote_id', $quote_id);
        return $this->db->update('sales_quote', $data);
    }

    // ✅ Add transaction record
    public function addGiftCardTransaction($user_id, $giftcard_id, $type, $amount, $order_id = null, $note = '')
    {
        $data = [
            'user_id'       => $user_id,
            'gift_card_id'  => $giftcard_id,
            'type'          => $type, // debit or credit
            'amount'        => $amount,
            'order_id'      => $order_id,
            'note'          => $note,
            'status'        => 1,
            'created_at'    => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('gift_card_transactions', $data);
    }

    // ✅ MAIN FUNCTION — Apply gift card to cart
    /*public function applyGiftCardToCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) {
            return ['status' => 'error', 'message' => 'Invalid or inactive gift card'];
        }

        if ($gift['balance'] <= 0) {
            return ['status' => 'error', 'message' => 'Gift card has no balance left'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) {
            return ['status' => 'error', 'message' => 'Cart not found'];
        }

        $cart_total = $cart['grand_total'];

        // Calculate deduction
        if ($gift['balance'] >= $cart_total) {
            $used_amount = $cart_total;
            $remaining_balance = $gift['balance'] - $cart_total;
            $new_cart_total = 0;
        } else {
            $used_amount = $gift['balance'];
            $remaining_balance = 0;
            $new_cart_total = $cart_total - $gift['balance'];
        }

        // Update both records
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $new_cart_total, $gift_code, $used_amount);
        $this->updateGiftCardBalance($gift['id'], $remaining_balance);
        $this->addGiftCardTransaction($user_id, $gift['id'], 'debit', $used_amount, $cart['quote_id'], 'Used on cart checkout');

        return [
            'status'             => 'success',
            'message'            => 'Gift card applied successfully',
            'used_amount'        => $used_amount,
            'remaining_balance'  => $remaining_balance,
            'new_cart_total'     => $new_cart_total
        ];
    }*/

    public function applyGiftCardToCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) return ['status' => 'error', 'message' => 'Invalid or inactive gift card'];

        if ($gift['balance'] <= 0) {
            return ['status' => 'error', 'message' => 'Gift card has been fully used'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) return ['status' => 'error', 'message' => 'Cart not found'];

        // Get already applied gift cards
        $existing_codes = [];
        if (!empty($cart['voucher_code'])) {
            $existing_codes = explode(',', $cart['voucher_code']);
        }

        // Prevent duplicate gift card
        if (in_array($gift_code, $existing_codes)) {
            return ['status' => 'error', 'message' => 'Gift card already applied'];
        }

        // Calculate remaining total after already applied gift cards
        $remaining_total = $cart['grand_total'];
        if (!empty($cart['voucher_amount'])) {
            $remaining_total += $cart['voucher_amount']; // revert previously deducted amounts for recalculation
        }

        // Recalculate used amounts for all cards (old + new)
        $all_gift_codes = array_merge($existing_codes, [$gift_code]);
        $total_used = 0;

        foreach ($all_gift_codes as $code) {
            $gift_data = $this->getGiftCardDetails($code);
            if ($gift_data && $remaining_total > 0) {
                $use = min($remaining_total, $gift_data['balance']);
                $remaining_total -= $use;
                $total_used += $use;
            }
        }

        // Update DB
        $this->db->where('quote_id', $cart['quote_id'])->update('sales_quote', [
            'voucher_code'   => implode(',', $all_gift_codes),
            'voucher_amount' => $total_used,
            'grand_total'    => max(0, $remaining_total)
        ]);

        return [
            'status' => 'success',
            'message' => 'Gift card applied successfully',
            'applied_codes' => $all_gift_codes,
            'used_amount' => $total_used,
            'new_cart_total' => max(0, $remaining_total)
        ];
    }



    /*public function removeGiftCardFromCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) {
            return ['status' => 'error', 'message' => 'Invalid gift card'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) {
            return ['status' => 'error', 'message' => 'Cart not found'];
        }

        // Get the amount used on this cart
        $used_amount = $this->getGiftCardUsageOnCart($gift['id'], $cart['quote_id']);

        // Restore gift card balance
        $new_balance = $gift['balance'] + $used_amount;
        $this->updateGiftCardBalance($gift['id'], $new_balance);

        // Remove gift card deduction from cart
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $cart['grand_total'] + $used_amount, '', 0);

        // Remove gift card transaction record
        $this->removeGiftCardTransaction($user_id, $gift['id'], $cart['quote_id']);

        return [
            'status'            => 'success',
            'message'           => 'Gift card removed successfully',
            'restored_balance'  => $new_balance,
            'new_cart_total'    => $cart['grand_total'] + $used_amount
        ];
    }*/

    public function removeGiftCardFromCart($gift_code, $session_id, $user_id)
    {
        $cart = $this->getCartBySession($session_id);
        if (!$cart) return ['status' => 'error', 'message' => 'Cart not found'];

        if (empty($cart['voucher_code'])) {
            return ['status' => 'error', 'message' => 'No gift card to remove'];
        }

        $existing_codes = explode(',', $cart['voucher_code']);
        $remaining_codes = array_diff($existing_codes, [$gift_code]);

        // Recalculate totals without this card
        $remaining_total = $cart['grand_total'];
        $remaining_total += $cart['voucher_amount']; // reset before recalc
        $total_used = 0;

        foreach ($remaining_codes as $code) {
            $gift_data = $this->getGiftCardDetails($code);
            if ($gift_data && $remaining_total > 0) {
                $use = min($remaining_total, $gift_data['balance']);
                $remaining_total -= $use;
                $total_used += $use;
            }
        }

        $this->db->where('quote_id', $cart['quote_id'])->update('sales_quote', [
            'voucher_code'   => implode(',', $remaining_codes),
            'voucher_amount' => $total_used,
            'grand_total'    => max(0, $remaining_total)
        ]);

        return [
            'status' => 'success',
            'message' => 'Gift card removed successfully',
            'applied_codes' => array_values($remaining_codes),
            'new_cart_total' => max(0, $remaining_total)
        ];
    }


    public function getGiftCardUsageOnCart($giftcard_id, $quote_id)
    {
        // Get the gift card code first
        $gift_code = $this->getGiftCardCodeById($giftcard_id);
        if (!$gift_code) return 0;

        // Check how much of this gift card was applied to this cart
        $this->db->select('voucher_amount');
        $this->db->where('voucher_code', $gift_code);
        $this->db->where('quote_id', $quote_id);
        $query = $this->db->get('sales_quote');

        if ($query && $query->num_rows() > 0) {
            return (float) $query->row()->voucher_amount;
        } else {
            return 0;
        }
    }

    // ✅ Helper function to get gift card code by ID
    public function getGiftCardCodeById($giftcard_id)
    {
        $this->db->select('code');
        $this->db->where('id', $giftcard_id);
        $query = $this->db->get('gift_cards');

        if ($query && $query->num_rows() > 0) {
            return $query->row()->code;
        } else {
            return null;
        }
    }

    public function getGiftCardIdByCode($giftcard_code)
    {
        $this->db->select('id');
        $this->db->where('code', trim($giftcard_code));
        $query = $this->db->get('gift_cards');

        if ($query && $query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            return null;
        }
    }

    public function removeGiftCardTransaction($user_id, $giftcard_id, $quote_id)
    {
        // Remove the debit record for this gift card and cart
        $this->db->where('user_id', $user_id);
        $this->db->where('giftcard_id', $giftcard_id);
        $this->db->where('order_id', $quote_id);
        $this->db->where('type', 'debit'); // only debit transactions applied to cart
        return $this->db->delete('gift_card_transactions');
    }

    public function deductGiftCardOnOrder($gift_card_id, $user_id, $order_id, $used_amount)
    {
        $gift = $this->db->get_where('gift_cards', ['id' => $gift_card_id])->row_array();
        if (!$gift) return false;

        // Check balance first
        if ($gift['balance'] < $used_amount) return false; // insufficient balance

        $new_balance = $gift['balance'] - $used_amount;

        // Deduct balance safely
        $this->db->where('id', $gift_card_id)->update('gift_cards', ['balance' => $new_balance]);

        // Log transaction
        $this->addGiftCardTransaction(
            $user_id,
            $gift_card_id,
            'debit',
            $used_amount,
            $order_id,
            'Used on order checkout'
        );

        return true;
    }

    public function deductFromMultipleGiftCards($voucher_codes, $total_to_deduct, $user_id, $order_id)
    {
        $remaining = $total_to_deduct;
        $transactions = [];

        foreach ($voucher_codes as $code) {
            if ($remaining <= 0) break; // done

            $gift = $this->db->get_where('gift_cards', ['code' => $code])->row_array();
            if (!$gift) continue; // invalid code

            $balance = (float)$gift['balance'];
            if ($balance <= 0) continue; // already empty

            // Determine how much to deduct from this card
            $deduct_amount = min($balance, $remaining);
            $new_balance = $balance - $deduct_amount;

            // Update gift card balance
            $this->db->where('id', $gift['id'])->update('gift_cards', ['balance' => $new_balance]);

            // Add transaction entry
            $transactions[] = [
                'user_id'       => $user_id,
                'gift_card_id'  => $gift['id'],
                'type'          => 'debit',
                'amount'        => $deduct_amount,
                'order_id'      => $order_id,
                'note'          => 'Used on order checkout (auto split)',
                'status'        => 1,
                'created_at'    => date('Y-m-d H:i:s')
            ];

            // Reduce remaining amount
            $remaining -= $deduct_amount;
        }

        // Insert all transactions in one go
        if (!empty($transactions)) {
            $this->db->insert_batch('gift_card_transactions', $transactions);
        }

        return ($remaining <= 0); // true = fully covered, false = partial
    }
}
