<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1 font-weight-bold">
                    <i class="fa fa-life-ring text-warning mr-2"></i> Order Resolution & Ticket Management
                </h3>
                <p class="text-muted small mb-0">Shopper & Merchant Disputes, Refund Holds, and Resolution Lifecycle</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card mb-4 shadow-sm border-0 bg-light">
            <div class="card-body p-3">
                <form method="GET" action="<?= base_url('admin/order-resolution'); ?>" class="form-inline d-flex flex-wrap" style="gap: 15px;">
                    <div class="form-group">
                        <label class="mr-2 font-weight-bold">Status:</label>
                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- All Statuses --</option>
                            <option value="Open" <?= ($current_status === 'Open') ? 'selected' : ''; ?>>Open</option>
                            <option value="Processing" <?= ($current_status === 'Processing') ? 'selected' : ''; ?>>Processing (@Account)</option>
                            <option value="Done" <?= ($current_status === 'Done') ? 'selected' : ''; ?>>Done</option>
                            <option value="Close" <?= ($current_status === 'Close') ? 'selected' : ''; ?>>Close</option>
                            <option value="ReOpen" <?= ($current_status === 'ReOpen') ? 'selected' : ''; ?>>ReOpen (Escalated Dispute)</option>
                            <option value="Close (Final)" <?= ($current_status === 'Close (Final)') ? 'selected' : ''; ?>>Close (Final)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="mr-2 font-weight-bold">Department:</label>
                        <select name="assigned_role" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- All Departments --</option>
                            <option value="Admin" <?= ($current_role === 'Admin') ? 'selected' : ''; ?>>@Admin</option>
                            <option value="Account" <?= ($current_role === 'Account') ? 'selected' : ''; ?>>@Account</option>
                        </select>
                    </div>

                    <?php if (!empty($current_status) || !empty($current_role)): ?>
                        <a href="<?= base_url('admin/order-resolution'); ?>" class="btn btn-outline-secondary btn-sm">Reset Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Ticket Listing Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-center" style="font-size: 13px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Ticket ID</th>
                                <th>Order #</th>
                                <th>Product</th>
                                <th>Shopper</th>
                                <th>Merchant</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Dispute Lock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets)): ?>
                                <?php foreach ($tickets as $t): ?>
                                    <?php
                                    $badge_map = [
                                        'Open'          => 'badge-primary',
                                        'Processing'    => 'badge-warning text-dark',
                                        'Done'          => 'badge-info',
                                        'Close'         => 'badge-success',
                                        'ReOpen'        => 'badge-danger',
                                        'Close (Final)' => 'badge-dark'
                                    ];
                                    $b_cls = $badge_map[$t->status_code] ?? 'badge-secondary';
                                    ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= htmlspecialchars($t->ticket_id); ?></td>
                                        <td><span class="text-primary font-weight-bold">#<?= htmlspecialchars($t->order_increment_id ?: $t->order_id); ?></span></td>
                                        <td><?= htmlspecialchars($t->product_name ?: ('Product #' . $t->products)); ?></td>
                                        <td><?= htmlspecialchars($t->first_name . ' ' . $t->last_name); ?></td>
                                        <td><?= htmlspecialchars($t->merchant_name ?: 'Merchant'); ?></td>
                                        <td><?= htmlspecialchars($t->category); ?></td>
                                        <td>
                                            <span class="badge <?= $b_cls; ?> p-2 font-weight-bold">
                                                <?= htmlspecialchars($t->status_code); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">@<?= htmlspecialchars($t->assigned_role ?: 'Admin'); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($t->is_active_dispute): ?>
                                                <span class="badge badge-danger"><i class="fa fa-lock mr-1"></i> Payout Held</span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><i class="fa fa-check mr-1"></i> Released</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/order-resolution/view/' . $t->ticket_id); ?>" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye mr-1"></i> View / Act
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No order resolution tickets found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
