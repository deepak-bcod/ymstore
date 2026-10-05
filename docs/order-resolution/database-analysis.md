# Database & Data Model Analysis: Order Resolution & Ticket System
## Agent 2 — Database Agent Report

**Agent:** Agent 2 — Database / Data Model Agent  
**System:** Yellow Markets (YM Store / MariaDB 10.3 / MySQL)  
**File:** `docs/order-resolution/database-analysis.md`  
**Status:** Comprehensive Analysis Complete

---

## 1. Existing Tables

A thorough audit of the active `ysm` database schema reveals the following relevant entity tables:

| Domain | Table Name | Purpose in Yellow Markets |
|---|---|---|
| **Helpdesk / Tickets** | `help_desk` | Legacy support ticket registry; stores basic customer message and replies. |
| **Orders** | `sales_order` | Master webshop order placed by customer. |
| | `b2b_orders` | Split sub-orders apportioned to individual merchants (publishers). |
| **Order Items** | `sales_order_items` | Individual product items purchased in master sales order. |
| | `b2b_order_items` | Individual product items assigned to merchant sub-order. |
| **Products** | `products` | Master product catalog, SKU, stock inventory, crate size, pricing. |
| | `products_description`| Localized product names and descriptions (EN/FR). |
| **Merchants / Users** | `users` | Unified store user registry for Shoppers (Customers) and Merchants (Publishers). |
| | `publisher_payment_details` | Merchant bank accounts, IBAN/SWIFT, IFSC, beneficiary name. |
| | `merchant_payout` | Historic payout disbursement ledger. |
| **Refunds** | `sales_order_return` | Master return records initiated by shoppers/admin. |
| | `sales_order_return_items` | Item-level return quantities, return approval, and restock flags. |
| | `refund_payment` | Bank payment transfers for customer refunds. |
| | `sales_order_payment_refunds` | Payment gateway refund transaction responses. |
| **Replacements** | `sales_order_replacement` | Master replacement requests. |
| | `sales_order_replacement_items` | Item-level replacement quantities and status. |
| **Payouts & Transactions** | `b2b_orders.payout_status` | Status flags: `1-Active`, `2-Draft`, `3-On Hold`, `4-Paid`. |
| | `merchant_addon_transactions` | Merchant Add-on payment transactions (e.g. YM delivery add-on). |
| | `order_mytmoney_transactions`| Shopper payment gateway transaction records. |
| **Users & Roles** | `adminusers` | Administrative staff, supervisors, and account department users. |
| | `role_master` | Role definitions: `Administrator`, `Super Admin`, `Manager`, `Account`. |
| | `role_resource` | Granular ACL resource permissions assigned to roles. |
| **Notifications** | `notifications` | In-app real-time and polled notification queue. |
| **Add-ons** | `addon_services` | Catalog of merchant add-ons (including delivery tiers). |
| | `merchant_addon_purchases` | Subscriptions and services purchased by merchants. |

---

## 2. Existing Relationships

```
                                  +-------------------+
                                  |    users (Shopper)|
                                  +---------+---------+
                                            | 1
                                            |
                                            | N
+--------------------+ 1         N +--------v----------+ 1         N +--------------------+
|  users (Merchant)  +<------------+    sales_order    +------------>+  sales_order_items  |
+---------+----------+             +--------+----------+             +---------+----------+
          | 1                               | 1                                | 1
          |                                 |                                  |
          | N                               | N                                | 1
+---------v----------+ 1         N          |                                  |
|     b2b_orders     +<---------------------+                                  |
+---------+----------+                                                         |
          | 1                                                                  |
          |                                                                    |
          | N                                                                  |
+---------v----------+                                                         |
|  b2b_order_items   +<--------------------------------------------------------+
+---------+----------+
          |
          |
          v
+-----------------------------------------------------------------------------------------+
|                                        help_desk                                        |
|  Ticket ID + Order ID + Order Item ID + Product ID + Merchant ID + Customer (Shopper) ID|
+-----------------------------------------------------------------------------------------+
```

### Relationship Mechanics
1. **Master to Sub-order:** A customer places 1 `sales_order` containing items from Merchant A and Merchant B. The checkout splits this into:
   - `b2b_orders` (Order 1, `merchant_id = A`, `webshop_order_id = sales_order.order_id`)
   - `b2b_orders` (Order 2, `merchant_id = B`, `webshop_order_id = sales_order.order_id`)
2. **Item Mapping:** Every `sales_order_items` row corresponds 1:1 to a `b2b_order_items` row linked through `product_id` and sub-order.
3. **Existing Helpdesk Disconnection:** The legacy `help_desk` table only held `order_id` and an ambiguous `products` string. It did not store foreign keys to `order_item_id` or `b2b_order_id`.

---

## 3. Existing Relevant Fields

### Table `help_desk`
- `id` (int 11, PK, Auto Increment)
- `ticket_id` (varchar 50): Stores formatted numeric string e.g. `0001`.
- `merchant_id` (int 11): Merchant user ID.
- `customer_id` (int 11): Shopper user ID.
- `order_id` (int 50): Sales order ID.
- `products` (varchar 255): Product name or ID.
- `category` (varchar 255): Unvalidated text.
- `priority` (varchar 255): Unvalidated text.
- `subject` (varchar 255): Subject line.
- `message` (text): Initial message.
- `attachment` (varchar 255): Single file upload name.
- `status` (tinyint 1): `0-not opened`, `1-open`, `2-closed`.
- `created_at` / `updated_at` (int 11 UNIX timestamp).

### Table `b2b_orders`
- `order_id` (int 11, PK): Sub-order ID.
- `webshop_order_id` (int 11): Parent `sales_order.order_id`.
- `merchant_id` (int 11): Seller ID.
- `grand_total` (decimal 12,2): Total payable to merchant.
- `payout_status` (int 11): `1-Active`, `2-Draft`, `3-On Hold`, `4-Paid`.
- `status` (int 11): Sub-order fulfillment status.

---

## 4. Missing Fields in Existing Schema

To support the complete business workflow and strict composite scoping, the following fields were missing:

| Missing Field | Target Table | Type / Specification | Workflow Purpose |
|---|---|---|---|
| `order_item_id` | `help_desk` | `int(11) NULL` | Foreign key to `sales_order_items.item_id`. Prevents cross-product ambiguity. |
| `b2b_order_id` | `help_desk` | `int(11) NULL` | Foreign key to `b2b_orders.order_id`. Directly isolates merchant sub-order. |
| `status_code` | `help_desk` | `enum('Open','Processing','Done','Close','ReOpen','Close (Final)')` | Complete state machine tracking beyond legacy 0/1/2. |
| `assigned_role` | `help_desk` | `enum('Admin','Account')` | Department routing for @Admin and @Account assignment. |
| `assigned_to` | `help_desk` | `int(11) NULL` | Specific user ID in `adminusers` handling the ticket. |
| `merchant_action` | `help_desk` | `enum('none','refund_approved','refund_denied','replacement_approved','replacement_denied','replacement_completed')` | Merchant decision tracking. |
| `delivery_option` | `help_desk` | `enum('none','own_delivery','self_pickup','ym_delivery')` | Mandatory selection for replacement approval. |
| `addon_purchase_id`| `help_desk` | `int(11) NULL` | Tracks delivery Add-on purchase when YM Delivery is chosen. |
| `refund_amount` | `help_desk` | `decimal(12,2) DEFAULT 0.00` | Exact refund amount calculated for the disputed item. |
| `refund_deducted_from_holdback` | `help_desk` | `tinyint(1) DEFAULT 0` | Audit flag confirming deduction from 15-day holdback. |
| `resolution_status`| `help_desk` | `enum('none','resolution_requested','resolution_approved','resolution_denied')` | Tracks Shopper dispute escalation and Admin determination. |
| `resolution_requested_at` | `help_desk` | `int(11) NULL` | Timestamp of shopper dispute escalation. |
| `closed_at` | `help_desk` | `int(11) NULL` | Timestamp of final or normal closure. |
| `closed_by` | `help_desk` | `varchar(50) NULL` | Actor identifier (`admin`, `merchant_denial`, `admin_final`). |
| `is_active_dispute`| `help_desk` | `tinyint(1) NOT NULL DEFAULT 1` | `1` = Blocks Payout & Hides from Manage Transaction; `0` = Payout Restored. |
| Thread Table | `help_desk_messages` | New Table | Multi-party conversation entries with role attribution. |
| Audit Table | `help_desk_audit_log` | New Table | State transition audit trail and accountability log. |

---

## 5. Required Migrations

### Migration 1: Schema Expansion on `help_desk`
```sql
ALTER TABLE `help_desk`
  ADD COLUMN `order_item_id` int(11) DEFAULT NULL COMMENT 'sales_order_items.item_id' AFTER `order_id`,
  ADD COLUMN `b2b_order_id` int(11) DEFAULT NULL COMMENT 'b2b_orders.order_id' AFTER `order_item_id`,
  ADD COLUMN `status_code` enum('Open','Processing','Done','Close','ReOpen','Close (Final)') NOT NULL DEFAULT 'Open' AFTER `status`,
  ADD COLUMN `assigned_role` enum('Admin','Account') DEFAULT 'Admin' AFTER `status_code`,
  ADD COLUMN `assigned_to` int(11) DEFAULT NULL COMMENT 'adminusers.id' AFTER `assigned_role`,
  ADD COLUMN `merchant_action` enum('none','refund_approved','refund_denied','replacement_approved','replacement_denied','replacement_completed') DEFAULT 'none' AFTER `assigned_to`,
  ADD COLUMN `delivery_option` enum('none','own_delivery','self_pickup','ym_delivery') DEFAULT 'none' AFTER `merchant_action`,
  ADD COLUMN `addon_purchase_id` int(11) DEFAULT NULL COMMENT 'merchant_addon_purchases.id if ym_delivery' AFTER `delivery_option`,
  ADD COLUMN `refund_amount` decimal(12,2) DEFAULT 0.00 AFTER `addon_purchase_id`,
  ADD COLUMN `refund_deducted_from_holdback` tinyint(1) DEFAULT 0 COMMENT '1 if deducted from 15-day holdback' AFTER `refund_amount`,
  ADD COLUMN `resolution_status` enum('none','resolution_requested','resolution_approved','resolution_denied') DEFAULT 'none' AFTER `refund_deducted_from_holdback`,
  ADD COLUMN `resolution_requested_at` int(11) DEFAULT NULL AFTER `resolution_status`,
  ADD COLUMN `closed_at` int(11) DEFAULT NULL AFTER `resolution_requested_at`,
  ADD COLUMN `closed_by` varchar(50) DEFAULT NULL AFTER `closed_at`,
  ADD COLUMN `is_active_dispute` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Blocks Payout, 0 = Normal Payout' AFTER `closed_by`;
```

### Migration 2: Conversation Messages Table (`help_desk_messages`)
```sql
CREATE TABLE IF NOT EXISTS `help_desk_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `help_desk_id` int(11) NOT NULL,
  `sender_role` enum('shopper','merchant','admin','account') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `ip` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_help_desk_id` (`help_desk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Migration 3: State Machine Audit Trail Table (`help_desk_audit_log`)
```sql
CREATE TABLE IF NOT EXISTS `help_desk_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `from_status` varchar(30) NOT NULL,
  `to_status` varchar(30) NOT NULL,
  `action` varchar(100) NOT NULL,
  `actor_role` varchar(30) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_audit` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 6. Required Indexes

To ensure sub-second response times on order lookups, payout checks, and dispute verification:

1. **Composite Identification Index:**
   ```sql
   ALTER TABLE `help_desk` ADD INDEX `idx_order_item_merchant` (`order_id`, `order_item_id`, `merchant_id`);
   ```
2. **Payout Locking & Active Dispute Check Index:**
   ```sql
   ALTER TABLE `help_desk` ADD INDEX `idx_dispute_payout_lock` (`merchant_id`, `is_active_dispute`, `status_code`);
   ```
3. **Unique Ticket Number Index:**
   ```sql
   ALTER TABLE `help_desk` ADD UNIQUE INDEX `idx_ticket_unique` (`ticket_id`);
   ```

---

## 7. Foreign-Key Considerations

1. **`help_desk.order_id` → `sales_order.order_id`:** Main purchase constraint.
2. **`help_desk.order_item_id` → `sales_order_items.item_id`:** Product item constraint. Must use `ON DELETE SET NULL` to preserve resolution audit trails even if order items are archived.
3. **`help_desk.b2b_order_id` → `b2b_orders.order_id`:** Sub-order constraint. Ensures direct link to payout calculations.
4. **`help_desk.merchant_id` → `users.id`:** Merchant identity constraint.
5. **`help_desk.customer_id` → `users.id`:** Shopper identity constraint.
6. **`help_desk.assigned_to` → `adminusers.id`:** Staff assignment constraint.

---

## 8. Data Integrity Risks & Mitigations

| Identified Risk | Impact | Database Architectural Mitigation |
|---|---|---|
| **Ambiguous Ticket Identity** | In a multi-product order, dispute applies to wrong item. | **Critical Composite Rule:** Every ticket requires `Ticket ID` + `Order ID` + `Order Item ID` + `Product ID` + `Merchant ID` + `Shopper ID`. |
| **Cross-Merchant Payout Contamination** | Disputing Merchant A locks Merchant B's payout. | Sub-order scoping via `b2b_order_id` and `merchant_id`. Payout check filters exclusively by `merchant_id`. |
| **Double Refund Deduction** | Merchant balance deducted twice on concurrent clicks. | Idempotency guard: `help_desk.status_code` must be `Processing` and `refund_deducted_from_holdback = 0` within a DB transaction. |
| **Legacy Ticket Incompatibility** | Existing helpdesk entries failing to render. | Default value fallback: legacy records with `status = 1` map to `status_code = 'Open'`, `status = 2` to `Close`. |

---

## 9. Recommended Schema Changes

1. **Execute Migration:** Deploy `database/migrations/20261001_order_resolution_system.sql`.
2. **Model Self-Healing Check:** Keep the `OrderResolutionModel::ensure_schema()` routine active so that new environments or staging databases auto-configure required columns upon first invocation without requiring manual CLI intervention.
3. **Hold-back Calculation Verification Query:**
   ```sql
   SELECT SUM(grand_total) as holdback_balance 
   FROM b2b_orders 
   WHERE merchant_id = ? 
     AND payout_status IN (1, 3) 
     AND created_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 15 DAY));
   ```
   This query guarantees that refunds processed by `@Account` are strictly deducted from the merchant's 15-day sales hold-back pool.
