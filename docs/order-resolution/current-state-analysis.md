# Yellow Markets - Current State Analysis
## Order Resolution / Ticket System Workflow

**Author:** Lead Software Engineering Agent  
**Date:** October 2026  
**System:** Yellow Markets (YM Store / Yellow Online Marketplace)  
**Document:** `docs/order-resolution/current-state-analysis.md`

---

## 1. Current Architecture

The Yellow Markets platform is an e-commerce multi-vendor platform built primarily on:
- **Framework & Version:** PHP 8.2 with CodeIgniter 3.x (MVC structure).
- **Core Applications:**
  - `application/`: Webshop frontend for Shoppers (Customers).
  - `merchant/`: Merchant Portal for Sellers / Publishers.
  - `admin/`: Super Admin & Staff Portal (including Management, Operations, and Accounts).
  - `webapi/`: Slim 4 microservice for REST APIs (cart listing, checkout, rate calculation, mobile/external integrations).
- **Database:** MariaDB 10.3 / MySQL (`ysm`), accessed via CodeIgniter Active Record and PDO.
- **Localization:** Bilingual system supporting English (`english`) and French (`french`) via CodeIgniter language files (`content_lang.php`, `notifications_lang.php`).

---

## 2. Existing Ticket Flow

Currently, a basic "Help Desk" module exists, but it operates as a generic message thread rather than an end-to-end Order Resolution engine:
1. **Shopper Entry:**
   - Shoppers can only raise a ticket from **My Profile → Help Desk** (`MyProfileController::helpDesk`, view: `application/views/myprofile/help_desk.php`).
   - The ticket form accepts Subject, Category, Priority, Order ID (free text/dropdown), Product, Message, and Attachment.
   - It is **not** integrated directly into the **My Orders** screen (`MyProfileController::getOrders`, `application/views/components/order/customer_general_orders.php`).
2. **Ticket Generation:**
   - In `MyProfileController::helpDeskPost`, ticket numbers are generated as simple 4-digit numbers padded with zeroes (`str_pad($next_number, 4, '0', STR_PAD_LEFT)` e.g. `0001`, `0002`).
   - Every reply creates a duplicate row in the `help_desk` table with the same `ticket_id`, rather than appending to a dedicated thread messages table.
3. **Merchant View:**
   - Accessible via `help_desk/shopper` (`UserController::shopper_helpdesk`) and `help_desk/merchant` (`UserController::merchant_helpdesk`).
   - Tickets are grouped in memory by `ticket_id` or `order_id . '_' . products`.
   - The merchant can only type a reply or click "Mark as Close" (`UserController::close_ticket`).
   - There are **no action controls** for approving/denying refunds, selecting replacement delivery options (`Own Delivery`, `Self Pickup`, `YM Delivery`), or marking replacement completed.
4. **Admin View:**
   - Accessible via `CustomerController::help_desk`, `CustomerController::shopper_help_desk`, and `CustomerController::merchant_help_desk`.
   - Admin can send a reply or "Mark as Close".
   - Admin cannot route or assign tickets to the `@Account` department.
5. **Shopper Dispute / Reopen:**
   - Once a ticket is closed (`status = 2`), the Shopper has no formal "Resolution Request" button to escalate to Admin or reopen the dispute.

---

## 3. Existing Status Values

### Current `help_desk.status` column
The existing `help_desk` table only stores a `tinyint(1)` status:
- `0`: Not Opened (Pending)
- `1`: Open
- `2`: Closed

### Required State Machine (Per Business Workflow)
The new workflow requires a robust multi-party state machine:
- `Open`: Initial status upon Shopper creation from My Orders.
- `Processing`: Under Admin investigation or Account processing (refund calculation, hold-back deduction, or YM delivery add-on requirement).
- `Done`: Set exclusively by `@Account` after successful refund or replacement financial execution.
- `Close`: Set exclusively by `@Admin` for normal ticket closure (e.g. after merchant replacement completion, refund done, or denied without dispute).
- `ReOpen`: Set exclusively by Shopper via "Resolution Request" after a merchant denial or initial closure.
- `Close (Final)`: Set exclusively by `@Admin` after investigating a Shopper Resolution Request (either Approved and processed via `@Account`, or Denied). No further dispute can be opened.

---

## 4. Existing Refund Flow

1. **Current Return Module:**
   - Managed in `sales_order_return` and `sales_order_return_items`.
   - Tracked in `admin/application/controllers/ReturnOrderController.php` and `merchant/application/controllers/ReturnOrderController.php`.
   - Statuses in `sales_order_return`: `0-pending`, `1-approved`, `2-rejected`, `3-approved`, `4-refund paid`.
2. **Current Payment Refund Actions:**
   - Uses `sales_order_payment_refunds` and `refund_payment` tables.
   - Handled via `App\Actions\Orders\ProcessPaymentRefund.php`.
3. **Disconnection from Help Desk:**
   - Currently, if a merchant or shopper discusses a refund in `help_desk`, it is completely disconnected from the actual refund tables and the 15-day hold-back ledger.
   - There is no mechanism in `help_desk` for a Merchant to click "Refund Approved" or "Refund Denied".
   - No workflow exists for Admin to assign the refund task to `@Account` to execute deduction from the Merchant's 15-day hold-back sales balance.

---

## 5. Existing Replacement Flow

1. **Current Replacement Module:**
   - Stored in `sales_order_replacement` and `sales_order_replacement_items`.
   - Statuses in `sales_order_replacement`: `0 = Pending`, `1 = Own Replacement`, `2 = YM Replacement`, `3 = Replaced`, `4 = Rejected`, `5 = Own Replacement Done`, `6 = YM Replacement Done`.
2. **Disconnection from Ticket System:**
   - The ticket system does not trigger or track replacement states.
   - When a Shopper asks for replacement in a ticket, Merchant has no buttons to approve/deny with delivery method selection (`Own Delivery`, `Self Pickup`, `YM Delivery`).
   - For `YM Delivery`, there is no integration with `@Account` requesting the Merchant to purchase a replacement delivery Add-on (`merchant_addon_purchases`, `merchant_addon_transactions`).
   - No button exists for "Replacement Completed" that verifies delivery selection.

---

## 6. Existing Payout Flow

1. **Current Payout Implementation:**
   - Managed in `merchant/application/controllers/B2BOrdersController.php` (`payouts()`, `checkPayoutEligibility()`) and `admin/application/controllers/B2BOrdersController.php`.
   - Lists eligible orders from `b2b_orders` joined with `sales_order`.
   - `checkPayoutEligibility($order_id)` checks `sales_order_return` and `sales_order_replacement`.
2. **Critical Flaw / Gap:**
   - Payout eligibility completely ignores active tickets and disputes!
   - If an order or product has an active dispute / ticket in `help_desk`, it **still appears** in `Manage Transaction` and can be paid out.
   - **Required Rule:** Active dispute (`Open`, `Processing`, `ReOpen`) must **hide the order from Manage Transaction** and **block Merchant & Admin payout**.
   - Upon `Close` or `Close (Final)`, normal transaction visibility and payout eligibility must be immediately restored.

---

## 7. Existing Notification Flow

1. **In-App Notifications:**
   - Table: `notifications` (`recipient_type`, `recipient_id`, `type`, `subtype`, `title`, `message`, `data`, `is_read`, `created_at`).
   - Implemented via `Notification_model::insert()`.
   - Currently inserts notifications for `type = helpdesk`, but lacks specific event subtypes and anti-duplication guards.
2. **Email System:**
   - Managed via `WebshopOrdersModel::sendCommonHTMLEmail` and `EmailModel` / `email_template`.
   - Template variables are substituted (e.g. `##TICKET_NO##`, `##ORDER_ID##`, `##PRODUCT_NAME##`).
   - Currently no automated email dispatch when tickets transition through:
     - New ticket raised (Merchant & Admin notified)
     - Reply from Merchant (Shopper notified)
     - Reply from Shopper (Merchant notified)
     - Refund/Replacement Approved or Denied (Shopper & Admin notified)
     - Assigned to `@Account` (Account team notified)
     - Resolution Request raised (Admin notified)
     - Final Close / Done (Merchant & Shopper notified)

---

## 8. Existing Database Structure

| Table Name | Key Columns | Current Limitations |
|---|---|---|
| `help_desk` | `id`, `ticket_id`, `merchant_id`, `subject`, `category`, `priority`, `customer_id`, `message`, `attachment`, `order_id`, `products`, `admin_reply`, `status`, `created_at`, `updated_at`, `ip` | 1. No `order_item_id`.<br>2. Status is only 0, 1, 2.<br>3. Flattens replies into new ticket rows.<br>4. Lacks dispute flags, assigned role (`assigned_to`), resolution choice, replacement delivery mode, hold-back refund tracking. |
| `sales_order` | `order_id`, `increment_id`, `customer_id`, `grand_total`, `status` | Master order table. Needs linking to active dispute count. |
| `sales_order_items` | `item_id`, `order_id`, `product_id`, `qty_ordered`, `price` | Main order items. Needs direct binding to tickets. |
| `b2b_orders` | `order_id`, `webshop_order_id`, `merchant_id`, `payout_status`, `status` | Sub-orders per merchant. `payout_status`: 1=Active, 3=Hold, 4=Paid. Currently unaware of `help_desk` tickets. |
| `b2b_order_items` | `item_id`, `order_id`, `product_id`, `status` | Merchant item status. |
| `notifications` | `id`, `recipient_type`, `recipient_id`, `type`, `title`, `message`, `data`, `is_read` | Supports in-app notifications. |
| `adminusers` | `id`, `name`, `email`, `user_type` | `user_type` joins with `role_master.id`. |
| `role_master` | `id`, `role_name` | Super Admin, Administrator, Manager, Account. |

---

## 9. Existing Files Involved

### Shopper (Customer Application)
- `application/controllers/MyOrdersController.php` (Order listing & actions)
- `application/views/components/order/customer_general_orders.php` (Order item view & action buttons)
- `application/controllers/MyProfileController.php` (Existing `helpDesk`, `helpDeskPost`, `viewTicket`)
- `application/views/myprofile/help_desk.php`
- `application/views/myprofile/help_desk_conversation.php`
- `application/language/english/content_lang.php` & `application/language/french/content_lang.php`

### Merchant Portal
- `merchant/application/controllers/UserController.php` (`shopper_helpdesk`, `merchant_helpdesk`, `view`, `update_help_desk`, `close_ticket`)
- `merchant/application/controllers/B2BOrdersController.php` (`payouts`, `checkPayoutEligibility`)
- `merchant/application/views/shopper_helpdesk.php`
- `merchant/application/views/help_desk_list.php`
- `merchant/application/views/help_desk_conversation.php`
- `merchant/application/views/b2b/order/payoutsorderlist.php`
- `merchant/application/views/common/fbc-user/sidebar.php`

### Admin Portal
- `admin/application/controllers/CustomerController.php` (`help_desk`, `shopper_help_desk`, `merchant_help_desk`, `view`, `update_help_desk`, `close_ticket`)
- `admin/application/controllers/B2BOrdersController.php` (`payouts`, `checkPayoutEligibility`)
- `admin/application/views/shopper_help_desk_list.php`
- `admin/application/views/merchant_help_desk_list.php`
- `admin/application/views/help_desk_conversation.php`
- `admin/application/views/b2b/order/payoutsorderlist.php`

---

## 10. Missing Functionality

1. **Ticket Trigger from My Orders:** Shopper cannot raise a ticket directly against an order item from "My Orders".
2. **Precise Data Identification:** Tickets do not strictly store `Ticket ID + Order ID + Order Item ID + Product ID + Merchant ID`.
3. **State Machine Transitions:**
   - Merchant cannot click `Refund Approved` or `Refund Denied`.
   - Merchant cannot click `Replacement Approved` (with `Own Delivery`, `Self Pickup`, `YM Delivery`).
   - Merchant cannot click `Replacement Completed` (only available after approval & delivery option).
   - Merchant cannot click `Replacement Denied`.
   - Shopper cannot submit `Resolution Request` (which sets status to `ReOpen`).
   - Admin cannot approve/deny resolution (`Resolution Approved` / `Resolution Denied`).
   - Admin cannot assign to `@Account`.
   - `@Account` cannot execute refund from 15-day hold-back sales and mark `Done`.
   - `@Admin` cannot perform `Close (Final)`.
4. **Payout Locking:**
   - Active tickets do not block orders from `Manage Transaction`.
   - Payouts are not placed on hold when a ticket is opened.
   - Payout restoration does not run when tickets are closed.
5. **Role-Based Visibility & Authorization:**
   - Actions and buttons are not strictly segregated by server-side authorization for `Shopper`, `Merchant`, `@Admin`, `@Account`.
6. **Notification Automation:**
   - Lack of dedicated email & in-app alerts mapped to each step with idempotency (duplicate prevention).

---

## 11. Potential Conflicts & Regressions

1. **Multiple Merchants in Single Order:** A single customer order often contains items from multiple merchants. If payout or dispute is applied to the entire order without scoping to `order_item_id` / `b2b_order_id` / `merchant_id`, uninvolved merchants would have their payouts unjustly blocked.
2. **Multiple Products from Same Merchant:** A shopper may dispute 1 product out of 3 from the same merchant. Scoping must be accurate to avoid blocking or refunding undamaged items.
3. **Existing Help Desk Compatibility:** Existing records in `help_desk` (status 0, 1, 2) must remain readable without breaking existing view templates.
4. **B2B Payout Hold-back Logic:** The 15-day hold-back calculation must accurately deduct approved refund amounts from the merchant's eligible sales balance without putting the account balance into an inconsistent state.
