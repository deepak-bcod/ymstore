# YellowMarkets Order Resolution - Comprehensive Implementation Plan

**Module:** Order Resolution (Customer, Merchant, Admin Portals)  
**Status:** Planning & Analysis Only (Awaiting User Approval — No Code Changes Authorized)  
**Date:** 2026-10-14  

---

## 1. Executive Summary & Scope

This implementation plan incorporates the complete set of new requirements (A through E) into the YellowMarkets Order Resolution architecture. It covers the technical specifications, affected files, database mapping, security controls, and step-by-step implementation tasks across Customer, Merchant, and Admin modules.

### Scope of Requirements:
1. **Requirement A: Popup close and page refresh**
   - Automatically and reliably refresh the parent page after a modal/popup completes its operation and closes, for both Merchant and Admin portals.
   - Prevent duplicate form submissions and infinite refresh loops.
   - Retain current URL parameters (language, ticket context, active tab/role query).
2. **Requirement B: Refund amount visibility**
   - Consistently display the refund amount on both Merchant and Admin detail pages.
   - Use the unified refund calculation and storage from `order_resolutions.refund_amount` without discrepancies.
   - Cleanly format and display statuses: Pending, Approved (with currency and amount), Denied (or 0.00), and N/A (where not applicable).
3. **Requirement C: Audit Trail and State Transitions Ordering**
   - Display newest events and transitions first across all interfaces.
   - Sort primarily by `created_at DESC` and secondarily by `id DESC`.
   - Apply identically across Admin, Merchant, and Customer audit views.
4. **Requirement D: Admin role-specific actions and posting identities**
   - **Support Team (@Help):** Restrict access strictly to Help-related actions (`assign_to_acct`, `close_ticket`, `close_final`, dispute approvals/denials). Display posting identity as: `Posting As: Support Team (@Help)`.
   - **Accounting Team (@Acct):** Restrict access strictly to Accounting-related actions (`mark_done`, request AddOn delivery). Display posting identity as: `Posting As: Accounting Team (@Acct)`.
   - **Interface Cleanup:** Completely remove `@Help View` and `@Acct View` toggle buttons from the controls header. Remove "Posting As" radio buttons from the reply form.
   - **Server-Side Enforcement:** Strictly validate permissions on all controller endpoints; prevent role spoofing or execution of unauthorized actions via direct requests.
5. **Requirement E: YM Delivery Replacement — AddOn Service Required**
   - **Workflow:** When Merchant selects `ym_delivery` for replacement, Accounting (@Acct) reviews the ticket and issues a formal purchase request for the replacement delivery AddOn Service.
   - **Merchant Purchase Interface:** Display a dedicated AddOn service details table with Order #, Ticket #, Product, Delivery service, AddOn service name, Amount (price + taxes), Payment status, Action button/link to purchase, and next step instructions.
   - **Payment & Confirmation Flow:** Merchant checks out via the existing YellowMarkets AddOn payment flow (`merchant/application/controllers/Addons.php`). Upon payment confirmation (callback/notify), link `addon_purchase_id` to `order_resolutions`, log confirmation in the audit trail, and notify @Acct to continue replacement dispatch.

---

## 2. Complete Affected Files & Architecture Map

| Area | File Path | Component Type | Summary of Changes |
| :--- | :--- | :--- | :--- |
| **Admin Controller** | `admin/application/controllers/OrderResolutionController.php` | Controller | • Remove toggle query spoofing; enforce server session role authentication.<br>• Add server-side RBAC guards to `assign_to_acct`, `close_ticket`, `close_final`, `resolution_decision`, `mark_done`.<br>• Add `request_addon_delivery()` endpoint for `@Acct`.<br>• Remove radio role selection; force server-resolved role in `reply()`. |
| **Merchant Controller** | `merchant/application/controllers/OrderResolutionController.php` | Controller | • Pass complete AddOn delivery purchase details (service model, purchase record, transaction status, checkout URL) to `view()` data.<br>• Unify refund amount presentation logic. |
| **Customer Controller** | `application/controllers/OrderResolutionController.php` | Controller | • Maintain descending audit ordering and consistent ticket context. |
| **Shared Model** | `application/models/OrderResolutionModel.php` | Model | • In `get_audit_log($resolution_id)`: order by `created_at DESC, id DESC`.<br>• Add `request_addon_delivery()` method (state update, audit log, email/in-app notification to merchant).<br>• Add `confirm_addon_payment()` method (state update, audit log, notification to @Acct).<br>• Standardize refund calculation helper `get_formatted_refund_amount()`. |
| **Merchant AddOn Controller** | `merchant/application/controllers/Addons.php` | Controller | • Add `buy_resolution_delivery($ticket_number)` or link ticket reference to `merchant_addon_purchases`.<br>• In `_handleMytAddonResponse()`, trigger resolution audit log and notifications upon successful payment. |
| **Admin Detail View** | `admin/application/views/order_resolution/view.php` | View / JS | • Remove `@Help View` and `@Acct View` toggle button group.<br>• Replace "Posting As" radio buttons with static label `Posting As: Support Team (@Help)` or `Posting As: Accounting Team (@Acct)` and hidden input.<br>• Add `@Acct` action button: "Request AddOn Service Purchase" with modal confirmation.<br>• Implement clean modal close handler (`hidden.bs.modal`) to refresh page only after successful operation completion while preserving query params. |
| **Merchant Detail View** | `merchant/application/views/order_resolution/view.php` | View / JS | • Add AddOn Service Required table with complete 10-field specification.<br>• Unify Refund Amount block in status banner to show across all states (Pending, Approved, Denied, N/A).<br>• Implement clean modal close handler (`hidden.bs.modal`) to refresh page upon operation completion. |
| **Language (EN)** | `admin/application/language/english/content_lang.php`<br>`merchant/application/language/english/content_lang.php`<br>`application/language/english/content_lang.php` | Language | • Add English translation keys for role posting headers, AddOn request actions, table column headers, and status messages. |
| **Language (FR)** | `admin/application/language/french/content_lang.php`<br>`merchant/application/language/french/content_lang.php`<br>`application/language/french/content_lang.php` | Language | • Add complete French translations for all new and updated labels, validation text, and email notices. |
| **Email Templates** | `database/migrations/` | Migration / DB | • Add email template `order-resolution-addon-purchase-request-merchant` for AddOn purchase instructions. |

---

## 3. Deep-Dive Design Specifications

### A. Popup Close & Page Refresh (Requirement A)

#### Current Behavior:
Modals hide via `$modal.modal('hide')` inside AJAX `success` handlers, followed by an arbitrary `setTimeout` before `window.location.reload()`. If the user closes an unsubmitted modal or an error occurs, behaviors vary.

#### Proposed Architecture:
1. **Operation State Flag:** In both `merchant/application/views/order_resolution/view.php` and `admin/application/views/order_resolution/view.php`, maintain a scoped state flag `var operationCompleted = false;` per modal interaction.
2. **AJAX Success Handler:** On successful JSON response (`res.status === true`), set `operationCompleted = true`, trigger modal close via `$modal.modal('hide')`.
3. **Bootstrap `hidden.bs.modal` Event Listener:**
   ```javascript
   $('.modal').on('hidden.bs.modal', function() {
       if (operationCompleted) {
           operationCompleted = false;
           // Retain current path, language, and ticket context
           window.location.href = window.location.href;
       }
   });
   ```
4. **Idempotency & Loop Prevention:** If the user opens and cancels a modal without completing an operation, `operationCompleted` remains `false` and no refresh occurs.

---

### B. Refund Amount Visibility (Requirement B)

#### Business Logic:
The refund amount is stored in `order_resolutions.refund_amount` (decimal 12,2).
- When a refund is requested/pending: Display calculated order/item refund value or `Pending`.
- When refund is approved (`merchant_action = 'refund_approved'` or `resolution_status = 'resolution_approved'`): Display `MUR XX.XX` in green bold text.
- When refund is denied (`merchant_action = 'refund_denied'` or `resolution_status = 'resolution_denied'`): Display `Denied` or `MUR 0.00`.
- When ticket is Replacement without refund: Display `N/A`.

#### Implementation:
Both Merchant (`merchant/application/views/order_resolution/view.php`) and Admin (`admin/application/views/order_resolution/view.php`) will render the identical component block:
```html
<div class="col-md-3">
    <strong>Refund Amount:</strong>
    <span class="font-weight-bold <?= ($resolution->refund_amount > 0) ? 'text-success' : 'text-muted'; ?>">
        <?php 
        if ($resolution->merchant_action === 'refund_approved' || $resolution->resolution_status === 'resolution_approved') {
            echo $currency . ' ' . number_format($resolution->refund_amount, 2);
        } elseif ($resolution->merchant_action === 'refund_denied' || $resolution->resolution_status === 'resolution_denied') {
            echo 'Denied';
        } elseif ($resolution->category === 'Refund' && $resolution->refund_amount > 0) {
            echo $currency . ' ' . number_format($resolution->refund_amount, 2) . ' (Pending)';
        } else {
            echo 'N/A';
        }
        ?>
    </span>
</div>
```

---

### C. Audit Trail & State Transitions Chronological Order (Requirement C)

#### Current Code (`application/models/OrderResolutionModel.php` line 1676):
```php
public function get_audit_log($resolution_id)
{
    return $this->db->where('resolution_id', $resolution_id)->order_by('created_at', 'ASC')->get('order_resolution_audit_log')->result();
}
```

#### Proposed Code:
```php
public function get_audit_log($resolution_id)
{
    return $this->db->where('resolution_id', (int)$resolution_id)
        ->order_by('created_at', 'DESC')
        ->order_by('id', 'DESC')
        ->get('order_resolution_audit_log')
        ->result();
}
```
Because Customer, Merchant, and Admin controllers all retrieve audit records exclusively via `OrderResolutionModel::get_audit_log()`, this single update immediately and uniformly sorts all audit records descending across all three portals.

---

### D. Admin Role-Specific Actions & Posting Identities (Requirement D)

#### 1. UI Updates in `admin/application/views/order_resolution/view.php`:
- **Remove Toggle Buttons:** Delete lines 151–160 (the button group containing `@Help View` and `@Acct View`).
- **Remove Posting As Radio Selection:** Delete lines 306–318 (radio inputs `roleHelp` and `roleAcct`).
- **Render Static Role Header:**
  - If `$current_role === 'help'`:
    ```html
    <div class="form-group mb-2">
        <label class="font-weight-bold">Posting As:</label>
        <span class="badge badge-primary ml-2 py-1 px-2">Support Team (@Help)</span>
        <input type="hidden" name="role" value="help">
    </div>
    ```
  - If `$current_role === 'acct'`:
    ```html
    <div class="form-group mb-2">
        <label class="font-weight-bold">Posting As:</label>
        <span class="badge badge-warning ml-2 py-1 px-2">Accounting Team (@Acct)</span>
        <input type="hidden" name="role" value="acct">
    </div>
    ```

#### 2. Server-Side RBAC Enforcement in `admin/application/controllers/OrderResolutionController.php`:
- Helper check in controller:
  ```php
  protected function require_admin_role($required_role)
  {
      $session_role = $this->get_authenticated_admin_role(); // 'help' or 'acct'
      if ($session_role !== $required_role && !$this->is_super_admin()) {
          $this->json_response(false, 'Unauthorized. This action is restricted to ' . ucfirst($required_role) . ' team.');
          return false;
      }
      return true;
  }
  ```
- **Guarded Endpoints:**
  - `assign_to_acct()`: Requires `help`.
  - `resolution_decision()`: Requires `help`.
  - `close_ticket()`: Requires `help`.
  - `close_final()`: Requires `help`.
  - `mark_done()`: Requires `acct`.
  - `request_addon_delivery()`: Requires `acct`.
  - `reply()`: Ignores client-supplied `role` parameter; overrides strictly with server-authenticated role (`help` or `acct`).

---

### E. YM Delivery Replacement — AddOn Service Integration (Requirement E)

#### 1. Workflow Sequence:
```
[Merchant: Replacement Approved (YM Delivery)]
                     │
                     ▼
[@Acct Admin: Reviews ticket, clicks "Request AddOn Service Purchase"]
                     │
                     ▼ (Generates purchase request, audit log, email & in-app notification)
[Merchant Portal: Dedicated AddOn Service Card with Full Breakdown]
                     │
                     ▼ (Merchant clicks "Purchase Delivery AddOn Service")
[Addon Payment Gateway (MyT Money / Checkout)]
                     │
                     ▼ (Payment Confirmed via mytNotify / mytCallback)
[System: merchant_addon_purchases status = 'paid']
[System: order_resolutions.addon_purchase_id linked]
[System: Audit log 'AddOn Purchase Confirmed' + Notify @Acct]
                     │
                     ▼
[@Acct & @Help: Can proceed to delivery completion and closure]
```

#### 2. Required AddOn Service Detail Card in Merchant View:
When `delivery_option === 'ym_delivery'` and `merchant_action === 'replacement_approved'`:
Display a dedicated, styled card containing the exact 10 required fields:

| Field | Value Source | Description |
| :--- | :--- | :--- |
| **Order number** | `$resolution->order_number` | Original order increment ID |
| **Resolution ticket** | `$resolution->ticket_number` | Active ticket reference |
| **Product** | `$product->name` | Product item to replace |
| **Delivery service** | YellowMarkets Delivery | Selected delivery channel |
| **AddOn Service** | `$addon_service->title` (e.g., Replacement Delivery Service) | Catalog service title |
| **Amount** | `$addon_service->final_price` (+ applicable VAT/tax) | Actual catalog price formatted with currency |
| **Payment status** | `$addon_purchase->status ?: 'Pending Purchase'` | Badge: `Pending` (warning), `Paid` (success), `Failed` (danger) |
| **Purchase action** | Button linking to `addons/buy_resolution_delivery/{ticket_number}` | Active button if pending/failed; disabled if paid |
| **Instructions** | Clear guidance text | "Please complete payment for the replacement delivery service so our logistics team can dispatch the item." |
| **Confirmation** | `$addon_transaction->transaction_ref ?: 'Awaiting confirmation'` | Official transaction / receipt reference |

#### 3. Payment Confirmation Hook in `merchant/application/controllers/Addons.php`:
In `_handleMytAddonResponse()`:
When payment is marked `success` and `merchant_addon_purchases` is set to `paid`:
```php
// Check if purchase is associated with an order resolution
$res = $this->db->get_where('order_resolutions', ['addon_purchase_id' => $purchase_id])->row();
if ($res) {
    $this->OrderResolutionModel->audit_log(
        $res->id,
        $res->ticket_number,
        $res->status,
        $res->status,
        'AddOn Delivery Purchased',
        'merchant',
        $res->merchant_id,
        "Merchant completed AddOn purchase #{$purchase_id} for replacement delivery."
    );
    // Notify Accounting and Help
    $this->OrderResolutionModel->send_inapp_notification('admin', 1, 'addon_purchased',
        'Order Resolution - AddOn Delivery Purchased',
        "Merchant has paid for YM Delivery AddOn Service for Ticket #{$res->ticket_number}.", $res);
}
```

---

## 4. Acceptance Criteria & Validation Strategy

1. **Ordering Check:** Inspect audit log tables in Customer, Merchant, and Admin views to verify `created_at DESC, id DESC` ordering.
2. **Modal Close & Refresh:** Open action modals in Merchant and Admin; submit valid action -> confirm modal closes, page refreshes seamlessly, retaining language and ticket context without double submission. Open modal and click "Cancel" -> confirm no unnecessary refresh.
3. **Refund Amount Consistency:** Compare Merchant and Admin views across pending, approved, denied, and replacement tickets; confirm identical figures, labels, and formatting.
4. **RBAC Isolation:** 
   - Verify `@Help View` and `@Acct View` toggle buttons are gone.
   - Verify reply form renders static `Posting As:` text without radio buttons.
   - Attempt direct POST request from `@Acct` to `close_ticket` -> confirm server responds with 403 / unauthorized error.
5. **AddOn Workflow:**
   - On ticket with YM Delivery replacement, verify `@Acct` sees "Request AddOn Service Purchase" control.
   - Verify Merchant portal renders the AddOn details card with all 10 required fields.
   - Verify payment flow links `addon_purchase_id` and records audit entry upon completion.
