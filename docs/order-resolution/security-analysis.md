# Security & Permission Analysis: Order Resolution & Ticket System
## Agent 7 — Security / Permission Agent Report

**Agent:** Agent 7 — Security & Authorization Specialist  
**System:** Yellow Markets (YM Store / PHP 8.2 CodeIgniter 3 MVC)  
**File:** `docs/order-resolution/security-analysis.md`  
**Status:** Comprehensive Analysis Complete

---

## 1. Existing Authorization & Legacy Security Posture

### 1.1 Existing Session & Authentication Mechanisms
Yellow Markets maintains segregated session contexts across its three portal environments:
- **Shopper Webshop:** Authenticated via `$_SESSION['LoginID']` (mapping to `customer_master.id`).
- **Merchant Portal:** Authenticated via `$_SESSION['LoginID']` (mapping to `merchant_master.id`), with role verification in `merchant/application/core/MY_Controller.php`.
- **Admin Portal:** Authenticated via `$_SESSION['LoginID']` (mapping to `admin_users.id`), with `$_SESSION['UserRole']` defining the administrative level (`Administrator`, `Super Admin`, `Account`, etc.).

### 1.2 Legacy Vulnerabilities & Authorization Gaps
1. **Lack of Entity Scoping:** The legacy `help_desk` queries relied heavily on simple query parameters (`$_GET['id']` or `$_POST['id']`) without systematically verifying if the authenticated user owned the associated `customer_id` or `merchant_id`.
2. **Missing State Precondition Checks:** Status updates in legacy controllers (`update_help_desk`) accepted any arbitrary status value without validating valid workflow progression or role authority.
3. **No Separation between Admin & Account:** Legacy admin controllers allowed any logged-in administrator to perform financial changes, with no dedicated role barrier restricting balance deductions to `@Account`.
4. **Client-Side Dependency:** Legacy actions relied primarily on hiding buttons in HTML/views, leaving endpoints vulnerable to direct cURL / Postman manipulation.

---

## 2. Role Restrictions: Frontend Visibility vs. Backend Authorization

Client-side button visibility is strictly a user convenience; **all security invariants are enforced server-side** in the controller and service layer.

| Action / Capability | Allowed Role(s) | Frontend Button Visibility Condition | Backend Server-Side Invariant Check |
|---|---|---|---|
| **Create Ticket** | Shopper | `[Ticket]` button beside order items in My Orders | Verifies `customer_id == $_SESSION['LoginID']` owns `order_id` and `order_item_id`. Prevents duplicate active tickets. |
| **Shopper Reply** | Shopper | Reply box in ticket view | Verifies `customer_id == $_SESSION['LoginID']` AND status in `['Open', 'Processing', 'ReOpen']`. |
| **Merchant Reply** | Merchant | Reply box in ticket view | Verifies `merchant_id == $_SESSION['LoginID']` AND status in `['Open', 'Processing', 'ReOpen']`. |
| **Refund Approved** | Merchant | Decision bar on `Open` ticket | Verifies `merchant_id == $_SESSION['LoginID']`, status == `Open`, and `merchant_action == 'none'`. |
| **Refund Denied** | Merchant | Decision bar on `Open` ticket | Verifies `merchant_id == $_SESSION['LoginID']`, status == `Open`, and non-empty denial rationale. |
| **Replacement Approved** | Merchant | Decision bar on `Open` ticket | Verifies `merchant_id == $_SESSION['LoginID']`, status == `Open`, and valid `delivery_option`. |
| **Replacement Denied** | Merchant | Decision bar on `Open` ticket | Verifies `merchant_id == $_SESSION['LoginID']`, status == `Open`, and non-empty rationale. |
| **Replacement Completed**| Merchant | Visible **only** after `Replacement Approved` + Delivery Option selected | Verifies `merchant_id == $_SESSION['LoginID']`, `merchant_action == 'replacement_approved'`, and valid delivery option. |
| **Resolution Request** | Shopper | Visible **only** when status is `Close` and `resolution_status != 'resolution_denied'` | Verifies `customer_id == $_SESSION['LoginID']`, status == `Close`, and `resolution_status != 'resolution_denied'`. |
| **Process / Assign to @Account** | @Admin | Available on `Open`, `Processing`, `ReOpen` | Verifies `UserRole` in `['Administrator', 'Super Admin']`. Updates `assigned_role = 'Account'`. |
| **Resolution Approved** | @Admin | Visible on `ReOpen` | Verifies `UserRole` in `['Administrator', 'Super Admin']` AND status == `ReOpen`. Sets `assigned_role = 'Account'`. |
| **Resolution Denied** | @Admin | Visible on `ReOpen` | Verifies `UserRole` in `['Administrator', 'Super Admin']` AND status == `ReOpen`. Sets status directly to `Close (Final)`. |
| **Refund / Payment (`Done`)** | @Account | Visible **only** to `@Account` when status is `Processing` | Verifies `UserRole` == `Account` (or Super Admin fallback) AND status == `Processing`. Executes hold-back balance deduction. |
| **Close (Normal)** | @Admin | Visible on `Open` or `Done` | Verifies `UserRole` in `['Administrator', 'Super Admin']` AND status in `['Open', 'Done']`. |
| **Close Final** | @Admin | Visible on `Done` (post-resolution) or `ReOpen` (if denied) | Verifies `UserRole` in `['Administrator', 'Super Admin']` AND status in `['Done', 'ReOpen']`. Terminal state. |

---

## 3. IDOR (Insecure Direct Object Reference) Risks & Protections

### 3.1 Ticket Access Isolation
- **Risk:** An attacker changes `ticket_id` in `/order-resolution/view/[id]` to inspect another customer's or merchant's private communications, invoices, or home addresses.
- **Protection:**
  - In `OrderResolutionController::view($ticket_id)`:
    ```php
    $ticket = $this->OrderResolutionModel->get_ticket($ticket_id);
    if (!$ticket) {
        show_404();
    }
    // Strict ownership verification
    if ($ticket->customer_id != $this->session->userdata('LoginID')) {
        show_error('Access Denied: You do not have permission to view this ticket.', 403);
    }
    ```
  - In `merchant/OrderResolutionController::view($ticket_id)`:
    ```php
    if ($ticket->merchant_id != $this->session->userdata('LoginID')) {
        show_error('Access Denied: You do not own the product associated with this ticket.', 403);
    }
    ```

### 3.2 Cross-Tenant Action Poisoning
- **Risk:** A merchant manipulates AJAX payloads to submit `merchant_action` on a ticket belonging to another vendor.
- **Protection:** All merchant action endpoints re-fetch the ticket record by ID, verifying that `ticket.merchant_id === session.LoginID`. If not matched, execution immediately halts with HTTP 403 Forbidden.

---

## 4. Evaluation of Conceptual Attack Scenarios

| Attack / Abuse Scenario | Execution Path | Expected Outcome & Defensive Countermeasure |
|---|---|---|
| **1. Shopper attempts Refund Approved** | Shopper crafts POST to `/merchant/order-resolution/action` with action `refund_approved`. | **BLOCKED (403):** Shopper session lacks merchant authentication credentials. If attempted against shopper endpoints, no such route exists. |
| **2. Merchant attempts Resolution Approved** | Merchant posts `action = 'resolution_approved'` to arbitration endpoints. | **BLOCKED (403):** Merchant session fails Admin role inspection (`MY_AdminController` check). |
| **3. Merchant accesses another merchant's ticket** | Merchant B opens `/merchant/order-resolution/view/1005` (owned by Merchant A). | **BLOCKED (403):** Controller compares `ticket->merchant_id != session->LoginID` and terminates with HTTP 403. |
| **4. Shopper accesses another shopper's ticket** | Shopper B accesses `/order-resolution/view/1005` (owned by Shopper A). | **BLOCKED (403):** Controller compares `ticket->customer_id != session->LoginID` and terminates with HTTP 403. |
| **5. @Admin performs Account refund** | General Admin attempts to execute hold-back deduction and mark `Done`. | **BLOCKED (403):** Endpoint requires explicit `@Account` role validation (`UserRole == 'Account'`). Non-finance admin is rejected. |
| **6. @Account closes ticket** | Account user crafts POST to `admin_close`. | **BLOCKED (403):** Account personnel cannot close tickets; only `@Admin` can issue normal or final closure. |
| **7. Merchant completes replacement before approval** | Merchant invokes `action = 'replacement_completed'` on an `Open` ticket without prior approval. | **BLOCKED (400):** State validation rejects action: precondition `merchant_action == 'replacement_approved'` and valid `delivery_option` failed. |
| **8. Shopper attempts Resolution Request after Close Final** | Shopper invokes `request_resolution` on a ticket in `Close (Final)`. | **BLOCKED (403):** `Close (Final)` is an immutable terminal state. Further appeals are permanently disallowed. |
| **9. Merchant acts on another merchant's product** | Multi-vendor order: Merchant A attempts to act on Merchant B's product line item. | **BLOCKED (403):** Ticket composite key binds strictly to `order_item_id` and `product_id` owned by `merchant_id`. Cross-vendor actions fail ownership check. |

---

## 5. Required Fixes & Implementation Invariants

1. **Self-Contained Authorization Layer:**
   - Implement authorization checks inside `OrderResolutionService` rather than relying solely on individual controllers.
2. **Transaction Integrity:**
   - Wrap state updates and balance deductions inside database transactions (`trans_start()` / `trans_complete()`) with row-level locks to prevent race conditions during concurrent submissions.
3. **MIME-Type & File Sanitization:**
   - Strict whitelisting for attachments: `image/jpeg`, `image/png`, `image/webp`, `application/pdf`.
   - Prevent path traversal by generating random cryptographic filenames (`bin2hex(random_bytes(16)) . '.' . $ext`).
4. **Audit Immutability:**
   - Every state transition, failed access attempt, and financial deduction writes an unmodifiable audit row to `help_desk_audit_log` including `ip_address`, `user_id`, `role`, and `timestamp`.
