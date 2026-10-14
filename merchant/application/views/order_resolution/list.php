<?php $this->load->view('common/fbc-user/header'); ?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; vertical-align: middle; }

.badge-action { font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; display: inline-block; vertical-align: middle; margin: 0; }
.badge-action-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-action-denied { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.badge-action-completed { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }

.table-resolution-list > tbody > tr > td { vertical-align: middle !important; }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <div class="content-main form-dashboard helf-section">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution Requests'; ?></h1>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center table-resolution-list">
                    <thead class="thead-light">
                        <tr>
                            <th><?= $this->lang->line('ticket_no') ?: 'Ticket #'; ?></th>
                            <th><?= $this->lang->line('order_number') ?: 'Order #'; ?></th>
                            <th><?= $this->lang->line('product') ?: 'Product'; ?></th>
                            <th><?= $this->lang->line('shopper') ?: 'Shopper'; ?></th>
                            <th><?= $this->lang->line('subject_type') ?: 'Subject Type'; ?></th>
                            <th><?= $this->lang->line('priority') ?: 'Priority'; ?></th>
                            <th><?= $this->lang->line('status') ?: 'Status'; ?></th>
                            <th><?= $this->lang->line('merchant_status') ?: 'Merchant Status'; ?></th>
                            <th><?= $this->lang->line('date') ?: 'Date'; ?></th>
                            <th><?= $this->lang->line('action') ?: 'Action'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($resolutions)): ?>
                            <?php foreach ($resolutions as $res): 
                                $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                $status_key  = 'status_' . strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $res->status));
                                $cat_key     = 'category_' . strtolower(trim($res->category ?? ''));
                                $pri_key     = 'priority_' . strtolower(trim($res->priority ?? ''));
                                $shopper_name = trim(($res->first_name ?? '') . ' ' . ($res->last_name ?? '')) ?: ($res->customer_email ?: ($this->lang->line('shopper') ?: 'Shopper'));
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($res->ticket_number); ?></strong></td>
                                    <td>#<?= htmlspecialchars($res->order_number); ?></td>
                                    <td><?= htmlspecialchars($res->product_name ?: ($this->lang->line('all_items') ?: 'All Items')); ?></td>
                                    <td><?= htmlspecialchars($shopper_name); ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($this->lang->line($cat_key) ?: $res->category); ?></span></td>
                                    <td>
                                        <?php
                                        $p_badge = 'badge-secondary';
                                        if ($res->priority === 'High') {
                                            $p_badge = 'badge-danger';
                                        } elseif ($res->priority === 'Medium') {
                                            $p_badge = 'badge-warning';
                                        } elseif ($res->priority === 'Low') {
                                            $p_badge = 'badge-info';
                                        }
                                        ?>
                                        <span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($this->lang->line($pri_key) ?: ($res->priority ?: 'Medium')); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status-<?= $status_slug; ?>">
                                            <?= htmlspecialchars($this->lang->line($status_key) ?: $res->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($res->merchant_action) && $res->merchant_action !== 'none'): ?>
                                            <span class="badge-action <?= strpos($res->merchant_action, 'approved') !== false ? 'badge-action-approved' : (strpos($res->merchant_action, 'denied') !== false ? 'badge-action-denied' : 'badge-action-completed'); ?>">
                                                <?= htmlspecialchars($this->lang->line($res->merchant_action) ?: ucwords(str_replace('_', ' ', $res->merchant_action))); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', $res->created_at); ?></td>
                                    <td>
                                        <a href="<?= base_url('order_resolution/view/' . $res->ticket_number); ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> <?= $this->lang->line('view') ?: 'View'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <?= $this->lang->line('no_order_resolution_tickets') ?: 'No Order Resolution requests found.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
