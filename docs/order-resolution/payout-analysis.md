# Payout & Financial Analysis: Order Resolution & Ticket System
## Agent 6 — Payout / Transaction Agent Report

**Agent:** Agent 6 — Payout / Financial Workflow Agent  
**System:** Yellow Markets (YM Store / PHP 8.2 CodeIgniter 3 MVC)  
**File:** `docs/order-resolution/payout-analysis.md`  
**Status:** Comprehensive Analysis Complete

---

## 1. Current Payout Logic

### 1.1 Architecture & Core Workflow
Yellow Markets operates a multi-vendor marketplace where customer payments are captured centrally into platform accounts via Stripe/Payment Gateways.
- When an order is placed (`sales_order`), sub-orders are generated in `b2b_orders`, partitioned strictly by `merchant_id`.
- The merchant payout mechanism is defined across:
  - `merchant/application/controllers/B2BOrdersController.php` (`payouts()`, `requestPayout()`)
  - `admin/application/controllers/B2BOrdersController.php` (`payouts()`, `processPayout()`)
  - `merchant/application/models/B2BOrdersModel.php` (`getPayoutOrders()`)
- Payout amounts are computed from item sub-totals minus marketplace commission and platform service fees.

### 1.2 Payout Status Model (`b2b_orders.payout_status`)
- `1` - **Eligible / Ready**: Past cooling-off period, ready for disbursement.
- `2` - **Draft / Unconfirmed**: Order not yet finalized.
- `3` - **On Hold**: Payout suspended due to return, replacement, cancellation, or active dispute.
- `4` - **Paid**: Funds disbursed to merchant bank account.

---

## 2. Current Transaction Filtering & Visibility

### 2.1 Manage Transaction Listing
- **Merchant Portal:** Displays `b2b_orders` where `merchant_id = $_SESSION['LoginID']`.
- **Admin Portal:** Displays all `b2b_orders` across all merchants.
- **Legacy Filtering Gap:**
  - The legacy `getPayoutOrders()` only checked dates (`created_at`) and simple flags in `sales_order_return` or `sales_order_replacement`.
  - It had **no connection** to the helpdesk/ticket table (`help_desk`).
  - As a result, an order under active customer dispute still appeared in "Manage Transaction", allowing merchants to initiate payout requests and admins to process payouts while customer issues remained unresolved.

---

## 3. Current Refund Logic

- **Direct Return Refunds:** Legacy refunds were recorded in `sales_order_return`. Admin manually reviewed banking records and updated status.
- **Helpdesk Detachment:** Prior to this system, tickets in `help_desk` had no direct relationship with financial refund records or accounting approval workflows.
- **Deduction Source:** Legacy refunds lacked automated balance reservation, risking negative balances if merchants had already withdrawn sales proceeds.

---

## 4. Merchant 15-Day Hold-back Logic

Yellow Markets enforces a standard **15-day hold-back cooling-off window** on all merchant sales before funds transition to `payout_status = 1` (Eligible):
$$\text{Eligible Date} = \text{Order Completion Date} + 15\text{ Days}$$

### 4.1 How the Hold-back Protects the Platform
1. Customer returns and resolution tickets typically occur within 7–14 days of delivery.
2. Holding merchant funds for 15 days ensures a liquid balance exists to fund shopper refunds without seeking clawbacks from merchant external bank accounts.
3. When `@Account` processes an approved refund, the deduction is executed directly against this held balance:
   ```sql
   SELECT SUM(grand_total) as holdback_balance 
   FROM b2b_orders 
   WHERE merchant_id = ? 
     AND payout_status IN (1, 3) 
     AND created_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 15 DAY));
   ```
4. If the hold-back balance is sufficient, `@Account` deducts the refund amount and marks the ticket `Done`.

---

## 5. Existing Eligibility Functions & Key Entity Attributes

### 5.1 `checkPayoutEligibility($order_id)` & `getPayoutOrders()`
- **Current Parameters Evaluated:**
  - `Order ID` (`b2b_orders.id` or `sales_order.order_id`): Evaluated.
  - `Merchant ID` (`b2b_orders.merchant_id`): Evaluated.
  - `Order Item ID` (`sales_order_item.order_item_id`): Partially evaluated via subqueries.
  - `Product ID` (`product_id`): Evaluated for commission rates.
  - `Ticket Status`: **Previously missing entirely.**
  - `Refund Status`: Checked only against `sales_order_return`.
  - `Replacement Status`: Checked only against `sales_order_replacement`.

---

## 6. Required Changes: Active Dispute Lock & Restoration Rules

To ensure strict financial integrity, the Order Resolution system implements the following rules:

### 6.1 The Active Dispute Blocking Rule
```
WHEN TICKET IS UNDER ACTIVE DISPUTE (status_code IN 'Open', 'Processing', 'ReOpen' AND is_active_dispute = 1):
  1. The sub-order MUST NOT appear in "Manage Transaction" for Merchant or Admin.
  2. Merchant cannot view or request payout for this sub-order.
  3. Admin cannot process or batch payout for this sub-order.
  4. b2b_orders.payout_status is set to 3 ('On Hold').
```

### 6.2 The Dispute Closure Restoration Rule
```
WHEN DISPUTE IS RESOLVED & CLOSED (status_code IN 'Close', 'Close (Final)' AND is_active_dispute = 0):
  1. The sub-order becomes visible again in "Manage Transaction".
  2. Normal payout eligibility is restored according to existing 15-day hold-back rules.
  3. b2b_orders.payout_status reverts from 3 ('On Hold') back to 1 ('Eligible') 
     (provided 15 days have elapsed and no other disputes or returns exist on that sub-order).
```

### 6.3 Technical Implementation in `B2BOrdersModel` & `B2BOrdersController`
In `getPayoutOrders()` and `checkPayoutEligibility()`:
```php
// Check for active dispute tickets linked to this merchant's sub-order
$active_disputes = $this->db->select('id')
    ->from('help_desk')
    ->where('order_id', $b2b_order->order_id)
    ->where('merchant_id', $b2b_order->merchant_id)
    ->where_in('status_code', ['Open', 'Processing', 'ReOpen'])
    ->where('is_active_dispute', 1)
    ->count_all_results();

if ($active_disputes > 0) {
    // Hide from listing and block payout
    return false;
}
```

---

## 7. Security Risks & Mitigations

1. **Premature Payout Release:**
   - *Risk:* Merchant rapidly requests payout while a dispute ticket is in `Open` or `ReOpen`.
   - *Mitigation:* In addition to UI hiding, `requestPayout()` enforces server-side database validation: rejects the request with HTTP 403 if `is_active_dispute = 1`.
2. **Unauthorized Balance Deduction:**
   - *Risk:* Non-financial administrative staff deducting balances or marking tickets `Done`.
   - *Mitigation:* Hold-back deductions and the `Done` transition are strictly restricted to role `@Account` (`UserRole = 3` / Super Admin).
3. **Audit Trail Deficit:**
   - *Risk:* Inability to reconcile why a payout was placed On Hold or released.
   - *Mitigation:* Every payout status toggle (`1 -> 3` or `3 -> 1`) generates an entry in `help_desk_audit_log` with the associated `ticket_id`, `actor_id`, and timestamp.

---

## 8. Multi-Merchant & Multi-Product Isolation Safeguards

### 8.1 Multi-Merchant Orders
- An order containing items from Merchant A and Merchant B produces separate records in `b2b_orders`:
  - `b2b_orders.id = 101` (`merchant_id = Merchant A`)
  - `b2b_orders.id = 102` (`merchant_id = Merchant B`)
- **Isolation Rule:** When a Shopper opens a ticket for Merchant A's item:
  - The ticket records `merchant_id = Merchant A` and `order_item_id = Item 1`.
  - The payout lock filters strictly by `order_id` AND `merchant_id = Merchant A`.
  - **Merchant B's sub-order (`id = 102`) remains 100% unaffected**, visible in Manage Transaction, and eligible for scheduled payout.

### 8.2 Multi-Product Orders from the Same Merchant
- If Merchant A has Product 1 and Product 2 in the same order:
  - The dispute record isolates `order_item_id = Product 1`.
  - The sub-order payout status is placed On Hold (`payout_status = 3`) until Product 1's dispute is resolved.
  - If a refund is approved for Product 1, `@Account` deducts only the line-item value of Product 1 from the 15-day hold-back.
  - Upon closing the dispute, the remaining balance for Product 2 is released for payout.
