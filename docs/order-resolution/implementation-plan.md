# YellowMarkets Order Resolution - Implementation Plan

**Module:** Order Resolution (Customer Portal)  
**Status:** Planning & Analysis (Awaiting User Approval)  
**Date:** 2026-10-14  

---

## 1. Overview and Objectives

This document details the exact files, methods, database interactions, visual updates, and localization changes required to refine the YellowMarkets Order Resolution workflow according to user specifications and screenshot feedback.

### Key Objectives:
1. **Listing Sorting:** Guarantee newest resolution requests are listed first (descending order by creation timestamp and primary key).
2. **Unified Blue Button Theme:** Standardize all action buttons (e.g., `+ New Resolution Request`, `Escalate to Yellow Market`, modal submit, and reply actions) to the existing blue button styling (`btn-info` / primary blue), while keeping existing status badges intact.
3. **Escalation Renaming:**
   - Change button label from **Resolution Request** to **Escalate to Yellow Market**.
   - Change modal submit button label to **Submit Escalation**.
4. **Clean Supporting Text & Remove `@Help`:**
   - Update text to: `Merchant has not replied or resolved? Request Yellow Markets Support to intervene and assist.`
   - Apply red styling as instructed in screenshot annotations (`color: #d9534f;` / `text-danger`).
   - Remove internal `@Help` jargon from customer-facing text and modals.
5. **Hide Escalation Action After Submission:**
   - Once escalated (`resolution_status = 'resolution_requested'`), hide the escalation trigger button and form entirely.
   - Display a clean status confirmation: `The escalation has been submitted to YellowMarkets support.`
   - Enforce server-side idempotency and state validation to prevent duplicate submissions.
6. **Full Multilingual Integrity:**
   - Provide complete English and French translations for all new and updated labels, validation messages, and notifications.

---

## 2. Analysis of Existing Codebase & Affected Components

| Component | File Path | Current State & Methods | Required Modification |
| :--- | :--- | :--- | :--- |
| **Customer Controller** | `application/controllers/OrderResolutionController.php` | - `index()` queries `order_resolutions` with `order_by('r.id', 'DESC')`.<br>- `request_resolution()` handles escalation submission.<br>- `view($ticket_number)` renders detail page. | - Enforce explicit sort: `r.created_at DESC, r.id DESC`.<br>- Enhance server validation in `request_resolution()` against duplicate escalation and verify ticket eligibility. |
| **Model** | `application/models/OrderResolutionModel.php` | - `shopper_resolution_request($ticket_number, $customer_id, $message)` sets `resolution_status = 'resolution_requested'`, status = `'ReOpen'`.<br>- Validates duplicate escalation via `if ($res->resolution_status === 'resolution_requested' ...)` | - Refine return message (remove `@Help` mention from user-facing flash message: `"The escalation has been submitted to YellowMarkets support."`).<br>- Maintain audit logging and notification triggers. |
| **Listing View** | `application/views/order_resolution/list.php` | - Top right button: `<a href="..." class="btn btn-primary">` renders yellow in site theme.<br>- Table action button: `<a class="btn btn-sm btn-info">` renders blue.<br>- Status badges: Closed (`badge-status-close`), Refund Denied (`badge-action-denied`), etc. | - Change top right button class from `btn-primary` to `btn-info` (standardizing to blue theme).<br>- Preserve all status badges (`badge-status-*`, `badge-action-*`). |
| **Detail View** | `application/views/order_resolution/view.php` | - Line 148: `<button class="btn btn-warning">` with text `Resolution Request`.<br>- Line 151: Text mentions `(@Help)`.<br>- Line 216: Reply button has `btn-primary`.<br>- Line 255: Modal submit has `btn-warning` and `Confirm Resolution Request`.<br>- Escalation block currently placed inside metadata card above conversation. | - Change escalation button to blue (`btn-info`) with label `Escalate to Yellow Market`.<br>- Position escalation block (or place below conversation as requested in screenshot note).<br>- Update supporting text to red styling and remove `@Help`.<br>- Hide button & form when ticket is escalated (`resolution_status === 'resolution_requested'`); display `The escalation has been submitted to YellowMarkets support.`<br>- Update modal title, remove `@Help`, and rename submit button to `Submit Escalation` with blue theme (`btn-info`).<br>- Standardize reply button to blue theme. |
| **Create View** | `application/views/order_resolution/create.php` | - Submit button: `class="btn btn-success btn-lg"`. | - Standardize submit button to blue theme (`btn-info`). |
| **Language (EN)** | `application/language/english/content_lang.php` | - Contains existing keys around lines 2258-2367. | - Add/update keys: `escalate_to_yellow_market`, `submit_escalation`, `escalation_supporting_text`, `escalation_submitted_status`, etc. Remove `@Help` mentions. |
| **Language (FR)** | `application/language/french/content_lang.php` | - Contains existing keys around lines 1400-1476. | - Add corresponding French translations for all updated and new strings. |

---

## 3. Detailed Proposed Changes

### A. Listing Page (`application/views/order_resolution/list.php` & `OrderResolutionController.php`)
1. **Query Sorting:**
   In `OrderResolutionController::index()`:
   ```php
   $this->db->order_by('r.created_at', 'DESC');
   $this->db->order_by('r.id', 'DESC');
   ```
2. **Button Standardization:**
   Update "+ New Resolution Request":
   ```html
   <a href="<?php echo base_url('order_resolution/create'); ?>" class="btn btn-info">
       <i class="fa fa-plus"></i> <?php echo $this->lang->line('raise_resolution_request') ?: 'New Resolution Request'; ?>
   </a>
   ```
3. **Preserve Badges:**
   Ensure status labels (Closed, Refund Denied, Refund Approved, Replacement Denied, etc.) remain untouched.

---

### B. Detail Page (`application/views/order_resolution/view.php`)

#### 1. Escalation Trigger Block
- **Condition:** Displayed when ticket is in actionable state (`Close`, `Done`, `Processing`) and merchant has rejected (`refund_denied` or `replacement_denied`), AND has **not** been escalated yet (`$resolution->resolution_status !== 'resolution_requested'`).
- **Placement:** Can be rendered below the resolution conversation or within the decision panel, prominently styled.
- **Button:**
  ```html
  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#resolutionEscalateModal">
      <i class="fa fa-flag"></i> <?php echo $this->lang->line('escalate_to_yellow_market') ?: 'Escalate to Yellow Market'; ?>
  </button>
  ```
- **Supporting Text (Red):**
  ```html
  <span class="text-danger" style="margin-left: 10px; font-weight: 500; color: #d9534f;">
      <?php echo $this->lang->line('resolution_escalate_prompt') ?: 'Merchant has not replied or resolved? Request Yellow Markets Support to intervene and assist.'; ?>
  </span>
  ```

#### 2. Post-Escalation State (Hidden Button + Confirmation)
- When `$resolution->resolution_status === 'resolution_requested'`:
  - The escalation button and modal are **hidden**.
  - A clean notification box / badge is displayed:
    ```html
    <div class="alert alert-info" style="margin-top: 15px; background-color: #e8f4fd; border-color: #b8daff; color: #004085;">
        <i class="fa fa-info-circle"></i>
        <strong><?php echo $this->lang->line('escalated_badge') ?: 'Escalated'; ?>:</strong>
        <?php echo $this->lang->line('escalation_submitted_confirmation') ?: 'The escalation has been submitted to YellowMarkets support.'; ?>
    </div>
    ```

#### 3. Modal Form Updates
- **Modal ID:** `#resolutionEscalateModal`
- **Modal Title:** `Escalate to Yellow Market Support`
- **Modal Text:** Remove all mentions of `@Help`.
- **Submit Button:**
  ```html
  <button type="submit" class="btn btn-info">
      <i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_escalation') ?: 'Submit Escalation'; ?>
  </button>
  ```

---

### C. Server-Side Guard & Backend (`OrderResolutionModel.php` & `OrderResolutionController.php`)
1. **Idempotency & Duplicate Prevention:**
   ```php
   if ($res->resolution_status === 'resolution_requested' || $res->status === 'ReOpen') {
       return [
           'status' => false,
           'message' => 'This request has already been escalated to YellowMarkets support.',
           'requires_reload' => true
       ];
   }
   ```
2. **Success Feedback:**
   Updated message: `"The escalation has been submitted to YellowMarkets support."`

---

### D. Multilingual Updates

#### English (`application/language/english/content_lang.php`):
```php
$lang['escalate_to_yellow_market']           = 'Escalate to Yellow Market';
$lang['resolution_escalate_prompt']          = 'Merchant has not replied or resolved? Request Yellow Markets Support to intervene and assist.';
$lang['submit_escalation']                   = 'Submit Escalation';
$lang['escalate_modal_title']                = 'Escalate to Yellow Market Support';
$lang['escalate_modal_desc']                 = 'If you disagree with the merchant\'s decision or the issue has not been satisfactorily resolved, submit this request to escalate the ticket directly to Yellow Markets Support.';
$lang['escalation_submitted_confirmation']   = 'The escalation has been submitted to YellowMarkets support.';
$lang['escalated_badge']                     = 'Escalated';
```

#### French (`application/language/french/content_lang.php`):
```php
$lang['escalate_to_yellow_market']           = 'Escalader vers Yellow Market';
$lang['resolution_escalate_prompt']          = 'Le marchand n\'a pas répondu ou résolu ? Demandez au Support Yellow Markets d\'intervenir et de vous assister.';
$lang['submit_escalation']                   = 'Soumettre l\'escalade';
$lang['escalate_modal_title']                = 'Escalader au Support Yellow Market';
$lang['escalate_modal_desc']                 = 'Si vous êtes en désaccord avec la décision du marchand ou si le problème n\'a pas été résolu de manière satisfaisante, transmettez ce ticket directement au Support Yellow Markets.';
$lang['escalation_submitted_confirmation']   = 'L\'escalade a été soumise au support Yellow Markets.';
$lang['escalated_badge']                     = 'Escaladé';
```

---

## 4. Business Rules Preserved

1. **Order Resolution Pre-requisite:** Remains strictly gated to order items where merchant rejected Return or Replacement.
2. **Separation of Concerns:** Order status, order-item status, and resolution ticket status remain completely decoupled.
3. **Database Schema Integrity:** No schema migrations or new enum values needed; uses existing `resolution_status = 'resolution_requested'`, `status = 'ReOpen'`, and timestamps.
4. **Notifications Integrity:** Existing email dispatcher (`send_dispute_help_email`, `send_dispute_merchant_email`) and in-app notification routing preserved.
5. **Duplicate Protection:** Client-side form hiding + button double-click disabling + server-side validation.

---

## 5. Implementation Status

- **Status:** **Completed & Verified**
- **Date Completed:** 2026-10-14
- **Modified Files:**
  1. [`OrderResolutionController.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/controllers/OrderResolutionController.php): Explicit sorting by `r.created_at DESC, r.id DESC`.
  2. [`OrderResolutionModel.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/models/OrderResolutionModel.php): Updated escalation return message to remove `@Help` and return translated confirmation; enforced duplicate escalation server-side guard.
  3. [`list.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/views/order_resolution/list.php): Standardized "+ New Resolution Request" button to blue theme (`btn-info`), matching the View button.
  4. [`view.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/views/order_resolution/view.php):
     - Renamed trigger button to "Escalate to Yellow Market" and styled blue (`btn-info`).
     - Supporting text updated to: *"Merchant has not replied or resolved? Request Yellow Markets Support to intervene and assist."* styled in red (`#d9534f`).
     - Removed all customer-facing `@Help` text.
     - Renamed modal submit button to "Submit Escalation" and styled blue (`btn-info`).
     - Escalation action section moved below conversation stream as requested.
     - Post-escalation state hides button and form, displaying confirmation banner and "Escalated" badge.
     - Reply submit button standardized to blue theme (`btn-info`).
  5. [`create.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/views/order_resolution/create.php): Standardized submit button to blue theme (`btn-info`).
  6. [`application/language/english/content_lang.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/language/english/content_lang.php): English strings updated and added for escalation lifecycle.
  7. [`application/language/french/content_lang.php`](file:///d:/php-8-2-1/htdocs/ymstore/application/language/french/content_lang.php): French translations updated and added for full bilingual support.
- **Syntax Check:** All modified PHP files verified with `php -l` (0 errors).
