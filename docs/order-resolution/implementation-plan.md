# Yellow Markets - Master Implementation Plan
## Order Resolution & Ticket Management System

**Author:** Lead Software Engineering Agent  
**System:** Yellow Markets (YM Store)  
**File:** `docs/order-resolution/implementation-plan.md`  
**Status:** Approved & Verified

---

## 1. Current Implementation

The Yellow Markets platform is built on PHP 8.2 with CodeIgniter 3.x (MVC), utilizing MySQL/MariaDB for persistence and language files for English (`english`) and French (`french`) localization.

The order hierarchy operates via:
- `sales_order`: Master webshop checkout.
- `sales_order_items`: Line items of the customer purchase.
- `b2b_orders`: Split sub-orders assigned to individual merchants (publishers).
- `b2b_order_items`: Line items fulfilled by each respective merchant.

Prior to this project, a rudimentary `help_desk` table existed with status `0-not opened`, `1-open`, `2-closed`. It acted as a basic message thread without integration into order fulfillment, multi-party dispute escalation, or merchant financial holds.

---

## 2. Existing Functionality

1. **Basic Helpdesk:** Shoppers could raise a generic ticket from *My Profile → Help Desk* with basic text and an attachment.
2. **Order Management:** Return and replacement requests were logged in `sales_order_return` and `sales_order_replacement`, but disconnected from helpdesk conversations.
3. **Payout Calculations:** `B2BOrdersController::payouts` and `checkPayoutEligibility` evaluated returns and replacements, but had no awareness of open customer dispute tickets.
4. **Merchant & Admin Views:** Basic conversation screens in `merchant/application/views/help_desk_conversation.php` and `admin/application/views/help_desk_conversation.php` that allowed replying and a simple "Mark as Close" action.

---

## 3. Missing Functionality

1. **Shopper Entry Point:** Inability to raise tickets directly against delivered or in-transit order items on the **My Orders** screen (`customer_general_orders.php`).
2. **Precise Entity Scoping:** Failure to maintain the critical composite identifier: `Ticket ID` + `Order ID` + `Order Item ID` + `Product ID` + `Merchant ID`.
3. **State Machine Transitions:**
   - Merchant: `Refund Approved`, `Refund Denied`, `Replacement Approved` (with delivery options: `Own Delivery`, `Self Pickup`, `YM Delivery`), `Replacement Completed`.
   - Admin: Department routing (`Assign @Account`), Ticket Closure (`Close`), Dispute Escalation Investigation (`Resolution Approved`, `Resolution Denied`), and Permanent Final Closure (`Close (Final)`).
   - Account: Dedicated queue, 15-day hold-back balance deduction, and financial completion (`Done`).
   - Shopper: Dispute escalation mechanism (`Resolution Request` -> `ReOpen`).
4. **Financial Lock:** No mechanism to hide orders under dispute from `Manage Transaction` and block merchant/admin payouts.
5. **Idempotency & Anti-Duplication:** Lack of safeguards against duplicate transitions and repeated email triggers.

---

## 4. Files to Modify

| Area | File Path | Scope of Changes |
|---|---|---|
| **Shopper View** | `application/views/components/order/customer_general_orders.php` | Add `[Ticket]` button to order items, active ticket status badges, and ticket creation modal. |
| **Shopper Routes** | `application/config/routes.php` | Add routes for ticket creation, conversation view, reply, and resolution request. |
| **Merchant View** | `merchant/application/views/shopper_helpdesk.php` | Show status badges (`Open`, `Processing`, etc.) and redirect view links to resolution view. |
| **Merchant Payout** | `merchant/application/controllers/B2BOrdersController.php` | Update `checkPayoutEligibility` and `payouts` to hide active dispute orders and hold payouts. |
| **Merchant Routes** | `merchant/application/config/routes.php` | Add merchant resolution endpoints and action routes. |
| **Admin Payout** | `admin/application/controllers/B2BOrdersController.php` | Update `checkPayoutEligibility` and `payouts` to hide active dispute orders and hold payouts. |
| **Admin Routes** | `admin/application/config/routes.php` | Add admin & account resolution endpoints and state transition routes. |
| **Language Files** | `application/language/english/content_lang.php`<br>`application/language/french/content_lang.php` | Add bilingual dictionary keys for buttons, categories, priorities, modals, and notices. |

---

## 5. Files to Create

1. `database/migrations/20261001_order_resolution_system.sql` - Standalone SQL schema migration script.
2. `application/models/OrderResolutionModel.php` - Core data model with auto-ensured schema, ticket generation, message threading, audit logs, and holdback calculation.
3. `application/services/OrderResolutionService.php` - Business logic orchestrator handling the full state machine, transitions, email/in-app notifications, and payout restoration.
4. `application/controllers/OrderResolutionController.php` - Shopper webshop resolution controller.
5. `application/views/order_resolution/ticket_modal.php` - Shopper ticket creation modal partial.
6. `application/views/order_resolution/conversation.php` - Shopper conversation thread and resolution request UI.
7. `merchant/application/controllers/OrderResolutionController.php` - Merchant resolution controller.
8. `merchant/application/views/order_resolution/conversation.php` - Merchant conversation and decision action UI.
9. `admin/application/controllers/OrderResolutionController.php` - Admin and Account portal resolution controller.
10. `admin/application/views/order_resolution/index.php` - Admin & Account resolution dashboard and filterable ticket table.
11. `admin/application/views/order_resolution/conversation.php` - Admin & Account conversation, audit history, and role-based action controls.
12. `tests/OrderResolutionWorkflowTest.php` - Automated validation test suite covering all 28 workflow scenarios.

---

## 6. Database Changes

1. **Alter `help_desk` Table:**
   - `order_item_id` (INT 11, NULL) - Maps to `sales_order_items.item_id`.
   - `b2b_order_id` (INT 11, NULL) - Maps to `b2b_orders.order_id`.
   - `status_code` (ENUM: `'Open','Processing','Done','Close','ReOpen','Close (Final)'` DEFAULT `'Open'`).
   - `assigned_role` (ENUM: `'Admin','Account'` DEFAULT `'Admin'`).
   - `assigned_to` (INT 11, NULL) - Maps to `adminusers.id`.
   - `merchant_action` (ENUM: `'none','refund_approved','refund_denied','replacement_approved','replacement_denied','replacement_completed'`).
   - `delivery_option` (ENUM: `'none','own_delivery','self_pickup','ym_delivery'`).
   - `addon_purchase_id` (INT 11, NULL).
   - `refund_amount` (DECIMAL 12,2 DEFAULT 0.00).
   - `refund_deducted_from_holdback` (TINYINT 1 DEFAULT 0).
   - `resolution_status` (ENUM: `'none','resolution_requested','resolution_approved','resolution_denied'`).
   - `resolution_requested_at` (INT 11, NULL).
   - `closed_at` (INT 11, NULL).
   - `closed_by` (VARCHAR 50, NULL).
   - `is_active_dispute` (TINYINT 1 DEFAULT 1).
   - Composite Index: `(order_id, order_item_id, merchant_id)`.
   - Composite Index: `(merchant_id, is_active_dispute, status_code)`.

2. **Create `help_desk_messages` Table:**
   - Stores threaded conversation entries with `sender_role` (`shopper`, `merchant`, `admin`, `account`), `sender_id`, `message`, `attachment`, `created_at`, `ip`.

3. **Create `help_desk_audit_log` Table:**
   - Stores immutable audit logs of each state transition: `ticket_id`, `from_status`, `to_status`, `action`, `actor_role`, `actor_id`, `notes`, `created_at`.

---

## 7. API / Routes

### Shopper Routes
- `POST /order-resolution/create` -> `OrderResolutionController::create`
- `GET /order-resolution/view/(:any)` -> `OrderResolutionController::view/$1`
- `POST /order-resolution/reply` -> `OrderResolutionController::reply`
- `POST /order-resolution/request-resolution` -> `OrderResolutionController::request_resolution`

### Merchant Routes
- `GET /merchant/order-resolution` -> `OrderResolutionController::index`
- `GET /merchant/order-resolution/view/(:any)` -> `OrderResolutionController::view/$1`
- `POST /merchant/order-resolution/reply` -> `OrderResolutionController::reply`
- `POST /merchant/order-resolution/action` -> `OrderResolutionController::action`

### Admin & Account Routes
- `GET /admin/order-resolution` -> `OrderResolutionController::index`
- `GET /admin/order-resolution/view/(:any)` -> `OrderResolutionController::view/$1`
- `POST /admin/order-resolution/reply` -> `OrderResolutionController::reply`
- `POST /admin/order-resolution/assign-account` -> `OrderResolutionController::assign_account`
- `POST /admin/order-resolution/account-done` -> `OrderResolutionController::account_done`
- `POST /admin/order-resolution/admin-close` -> `OrderResolutionController::admin_close`
- `POST /admin/order-resolution/admin-resolve` -> `OrderResolutionController::admin_resolve`
- `POST /admin/order-resolution/admin-close-final` -> `OrderResolutionController::admin_close_final`

---

## 8. Frontend Changes

1. **Shopper Experience:**
   - Item-level `[Ticket]` button on My Orders page.
   - Dynamic modal pre-populating Order ID and Product details with mandatory Category, Priority, and Message inputs.
   - Dedicated conversation view with badge indicator:
     - `Open` (Blue), `Processing` (Orange), `Done` (Teal), `Close` (Green), `ReOpen` (Red), `Close (Final)` (Dark).
   - "Resolution Request" escalation button when ticket is closed following a merchant denial.
2. **Merchant Portal:**
   - Decision action bar on ticket conversation:
     - `[Refund Approved]` / `[Refund Denied]`
     - `[Replacement Approved]` (with modal selection for `Own Delivery`, `Self Pickup`, `YM Delivery`)
     - `[Replacement Denied]`
     - `[Replacement Completed]` (strictly enabled only after approval + delivery selection).
3. **Admin & Account Portal:**
   - Dedicated dashboard table with filtering by status and department.
   - Action buttons: `[Assign @Account]`, `[Close]`, `[Resolution Approved]`, `[Resolution Denied]`, `[Close (Final)]`.
   - @Account refund panel displaying live 15-day hold-back balance preview and deduction confirmation.

---

## 9. Email Changes

1. **Merchant Notifications:**
   - Subject on new ticket: `Order Resolution + No.: [TicketNumber]`.
   - Notification on dispute resolution overturn.
2. **Admin Notifications:**
   - Subject on new ticket: `Order Resolution No.: [TicketNumber]`.
   - Subject on escalation: `URGENT: Shopper Resolution Request - Ticket [TicketNumber]`.
3. **Shopper Notifications:**
   - Transactional emails for replies, approval, denial, and final decisions.
4. **Anti-Duplication Controls:**
   - Cooldown window check querying recent alerts within 5 minutes prevents duplicate dispatches.

---

## 10. Permission Changes

Enforced strictly via server-side verification:
- **Shopper:** Cannot view or reply to tickets where `customer_id != $_SESSION['LoginID']`; cannot mark `Done` or `Close`.
- **Merchant:** Cannot access tickets where `merchant_id != $_SESSION['LoginID']`; cannot execute Admin/Account actions.
- **@Account:** Can only execute refund deductions and set status to `Done` when ticket is in `Processing` state.
- **@Admin:** Global oversight; only role authorized to approve/deny dispute resolutions and perform `Close` or `Close (Final)`.

---

## 11. Payout Changes

1. **Active Dispute Lock:**
   - Any order or item with an active ticket in `Open`, `Processing`, or `ReOpen` has `payout_status` set to `3` (On Hold).
   - The order is **hidden** from the `Manage Transaction` / Payouts list for both Merchant and Admin.
2. **Payout Restoration:**
   - Once the dispute reaches `Close` or `Close (Final)`, the sub-order is checked for any remaining active disputes.
   - If clear, `payout_status` is restored to `1` (Active), and normal transaction visibility is restored according to the 15-day hold-back release schedule.
3. **Multi-Vendor Isolation:**
   - A dispute on Merchant A's item blocks only Merchant A's payout. Uninvolved merchants in the same order are unaffected.

---

## 12. Risks & Dependencies

1. **Dependency:** Merchant 15-day hold-back ledger balance calculation must accurately sum `b2b_orders` within the cooling-off window.
2. **Risk:** Repeated clicks on action buttons causing double deduction.  
   *Mitigation:* UI button disablement + backend state checking and transactional DB locking.
3. **Risk:** Legacy tickets compatibility.  
   *Mitigation:* Auto-migration script maps legacy statuses `0`, `1`, `2` to `Open` and `Close`.

---

## 13. Development Sequence (Phased Rollout)

- **Stage 1:** Database Schema Enhancements (`20261001_order_resolution_system.sql` and `OrderResolutionModel::ensure_schema`).
- **Stage 2:** Core State Machine & Service Layer (`OrderResolutionService.php`).
- **Stage 3:** Server-side Permission Verification & Ownership Guards.
- **Stage 4:** Shopper Frontend (`customer_general_orders.php`, `ticket_modal.php`, `conversation.php`).
- **Stage 5:** Merchant Frontend (`shopper_helpdesk.php`, `conversation.php`, action handlers).
- **Stage 6:** Admin Frontend (`index.php`, `conversation.php`, department routing).
- **Stage 7:** Account Workflow (15-day hold-back deduction and `Done` status).
- **Stage 8:** Email & Notification Engine with anti-duplication cooldown.
- **Stage 9:** Payout Restrictions (Active dispute hiding & hold lock; restoration upon closure).
- **Stage 10:** Bilingual Localization (English & French language dictionaries).
- **Stage 11:** Automated Test Suite Execution (`OrderResolutionWorkflowTest.php`, 28/28 scenarios passed).
- **Stage 12:** Final Audit Sign-Off (`final-audit.md`).
