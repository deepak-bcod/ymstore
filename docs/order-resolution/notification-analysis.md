# Email & Notification Analysis: Order Resolution & Ticket System
## Agent 5 — Notification / Email Agent Report

**Agent:** Agent 5 — Email & Notification Specialist  
**System:** Yellow Markets (YM Store / PHP 8.2 CodeIgniter 3 MVC)  
**File:** `docs/order-resolution/notification-analysis.md`  
**Status:** Comprehensive Analysis Complete

---

## 1. Existing Email & Notification Infrastructure Inspection

### 1.1 Mail Service & SMTP/Mailjet Integration
- **CodeIgniter Email Configuration:**
  - Located in `application/config/email.php`.
  - Protocol: `smtp`.
  - Host/Provider: Integrated via SMTP (Mailjet / transactional mail gateway with fallback to sendmail).
  - Charset: `utf-8`, Mailtype: `html`.
- **Primary Sending Wrapper:**
  - `WebshopOrdersModel::sendCommonHTMLEmail($to, $subject, $message, $from_name, $from_email, $attachment)`.
  - Features HTML boilerplate wrapping, standard yellow branding header, and SSL/TLS transport support.

### 1.2 Email Templates & Reuse
- **Database Stored Templates:** Table `email_template` stores customizable templates keyed by `template_key` or `title`.
- **View Partials:** `application/views/email_template/` contains responsive HTML layouts (`email_header.php`, `email_footer.php`, order receipts).
- **Template Reuse Strategy:** A unified `order_resolution_template.php` view partial provides clean, branded card layouts for all 18 resolution notifications, ensuring consistency and ease of maintenance across multi-lingual email deliveries.

### 1.3 Queues, Events, Listeners & Background Processing
- CodeIgniter 3 lacks built-in asynchronous queue/event daemons (like Laravel Horizon).
- Current pattern triggers emails synchronously during controller/service actions.
- Failed email handling: Wrapped in `try-catch` blocks and logged to `application/logs/log-[YYYY-MM-DD].php` without blocking user-facing HTTP transactions.

### 1.4 Notification Tables & Helpers
- **In-App Notification Table:** `notifications`
  - Columns: `id`, `user_id`, `user_type` (`1=Admin`, `2=Merchant`, `3=Customer`), `title`, `message`, `link`, `is_read`, `created_at`.
- **Notification Model:**
  - `application/models/Notification_model.php`: Handles insertion (`create_notification()`) and polling for top-navbar notification badge counts.
- **Audit Table:**
  - `help_desk_audit_log` records system notifications and status history for dispute auditability.

---

## 2. Comprehensive Notification & Email Mapping Matrix

All email subject lines and recipient targets are mapped strictly according to system specifications:

| Event Trigger | Recipient Role | Exact Email Subject Line | In-App Notification Message |
|---|---|---|---|
| **New Ticket** | Merchant | `Order Resolution + No.: [TicketNumber]` | New ticket #[TicketNumber] raised for Order #[OrderId] - [ProductName]. |
| | @Admin | `Order Resolution No.: [TicketNumber]` | Shopper opened ticket #[TicketNumber] for Order #[OrderId]. |
| **Merchant Reply** | Shopper | `Order Resolution Update + No.: [TicketNumber]` | Merchant replied to Ticket #[TicketNumber]. |
| **Shopper Reply** | Merchant | `Order Resolution Update + No.: [TicketNumber]` | Shopper replied to Ticket #[TicketNumber]. |
| **Merchant Action** | @Admin | `Order Resolution Update + No.: [TicketNumber]` | Merchant took action on Ticket #[TicketNumber]. |
| **Refund: Merchant Approved** | Shopper | `Order Resolution No.: [TicketNumber] - We Approve Refund` | Merchant approved your refund for [ProductName]. Processing via Accounts. |
| | Merchant | `Order Resolution No.: [TicketNumber] - Refund initiated` | Refund initiated for Ticket #[TicketNumber]. |
| | @Account | `Order Resolution No. [TicketNumber] - Refund Request` | Refund request for Ticket #[TicketNumber]. 15-day hold-back processing required. |
| **Refund: Ticket Closed** | Merchant | `Order Resolution No.: [TicketNumber] - Closed` | Ticket #[TicketNumber] has been processed and closed. |
| | Shopper | `Order Resolution No.: [TicketNumber] - You Are Being Refunded` | Refund processed successfully for Ticket #[TicketNumber]. |
| **Replacement: Approved** | Shopper | `Order Resolution No. [TicketNumber] - Product Replacement Underway` | Replacement approved via [DeliveryType] for [ProductName]. |
| **Replacement: Completed** | Shopper | `Order Resolution No.: [TicketNumber] - Your Product Replacement Completed` | Replacement item delivered/collected for Ticket #[TicketNumber]. |
| **Replacement: Denied** | Shopper | `Order Resolution No.[TicketNumber] - No Replacement for Product` | Merchant denied replacement for Ticket #[TicketNumber]. |
| | @Admin | `Order Resolution No.: [TicketNumber] - No Replacement for Product` | Merchant denied replacement for Ticket #[TicketNumber]. |
| **Refund: Denied** | Shopper | `Order Resolution No.[TicketNumber] - No Refund for Product` | Merchant denied refund for Ticket #[TicketNumber]. |
| | @Admin | `Order Resolution No.: [TicketNumber] - No Refund for Product` | Merchant denied refund for Ticket #[TicketNumber]. |
| **Shopper Dispute Escalation** | @Admin | `Order Resolution No.: [TicketNumber] - Dispute Refund Decision` | URGENT: Shopper filed Resolution Request on Ticket #[TicketNumber]. |
| | Merchant | `Order Resolution No.: [TicketNumber] - Shopper Dispute Decision` | Shopper escalated dispute to @Admin for Ticket #[TicketNumber]. |
| **Final Resolution (Denied)** | Merchant | `Order Resolution No.: [TicketNumber] - Closed (Final)` | @Admin affirmed refusal. Ticket #[TicketNumber] is Closed (Final). |
| | Shopper | `Order Resolution No.: [TicketNumber] - Refund Disapproved` | @Admin finalized dispute investigation. Resolution was denied. |

---

## 3. Critical Verification & Reliability Safeguards

### 3.1 Correct Recipient Verification
- **Multi-Vendor Scoping:** Multi-product orders generate distinct sub-orders (`b2b_orders`). When sending merchant notifications, `merchant_id` is resolved from `order_items` / `b2b_orders`, guaranteeing that Merchant A never receives email alerts for Merchant B's products.
- **Account Department Grouping:** `@Account` notifications are addressed to all users assigned the `Account` role (`UserRole = 3` or `admin_role = 'account'`) or the designated finance inbox (`accounts@yellowmarkets.com`).
- **Shopper Contact Retrieval:** Customer email address is dynamically retrieved from `customer_master` via `customer_id`.

### 3.2 Duplicate Email Prevention (Anti-Spam / Idempotency)
- **Cooldown Window:** All notification triggers enforce a **300-second cooldown window** for identical event types on the same ticket.
- **Tracking Hash:** Every dispatch calculates an idempotency key:
  $$\text{Hash} = \text{MD5}(\text{ticket\_id} + \text{event\_key} + \text{recipient\_id} + \text{date('Y-m-d H:i')})$$
  Logged into `help_desk_audit_log`. If a matching hash was logged within the cooldown threshold, the dispatch is skipped.
- **Client Debounce:** Frontend submission buttons immediately disable and display a loading state (`<i class="fa fa-spinner fa-spin"></i>`) on first click.

### 3.3 Queue & Performance Handling
- To prevent slow SMTP connections from increasing page load times:
  - If a Redis/database queue daemon is present, tasks push to the queue worker.
  - In direct SMTP mode, CodeIgniter's email library is executed after core database state transitions are committed (`trans_complete()`), preventing transactional rollbacks due to SMTP timeouts.

### 3.4 Failed Email Handling
- Email dispatches are wrapped in non-fatal `try-catch` blocks:
  ```php
  try {
      $sent = $this->email->send();
      if (!$sent) {
          log_message('error', 'Order Resolution Email Failed: ' . $this->email->print_debugger(['headers']));
      }
  } catch (Exception $e) {
      log_message('error', 'Mail Exception on Ticket ' . $ticket_number . ': ' . $e->getMessage());
  }
  ```
- Failure to send an email **never aborts or rolls back the ticket state transition**. In-app notifications remain stored in the `notifications` table as an immutable backup.

### 3.5 Email Template Reuse
- The unified template partial `application/views/email_template/order_resolution_template.php` dynamically accepts:
  - `$title`: Event headline
  - `$ticket_number`: Ticket increment ID
  - `$order_number`: Order increment ID
  - `$product_name`: Order item product name
  - `$body_message`: Specific explanatory text
  - `$action_url`: Direct CTA button link to view the ticket conversation
  - `$accent_color`: Status-specific color (e.g., `#28a745` for approvals, `#dc3545` for denials/escalations)
