<?php $this->load->view('common/fbc-user/header'); ?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

.badge-action { font-size: 11px; padding: 3px 6px; border-radius: 3px; font-weight: 600; display: inline-block; margin-top: 3px; }
.badge-action-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-action-denied { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.badge-action-completed { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
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
                <table class="table table-bordered table-hover text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Shopper</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($resolutions)): ?>
                            <?php foreach ($resolutions as $res): 
                                $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                $shopper_name = trim(($res->first_name ?? '') . ' ' . ($res->last_name ?? '')) ?: ($res->customer_email ?: 'Shopper');
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($res->ticket_number); ?></strong></td>
                                    <td>#<?= htmlspecialchars($res->order_number); ?></td>
                                    <td><?= htmlspecialchars($res->product_name ?: 'All Items'); ?></td>
                                    <td><?= htmlspecialchars($shopper_name); ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($res->category); ?></span></td>
                                    <td><?= htmlspecialchars($res->priority); ?></td>
                                    <td>
                                        <span class="badge-status-<?= $status_slug; ?>">
                                            <?= htmlspecialchars($res->status); ?>
                                        </span>
                                        <?php if ($res->merchant_action !== 'none'): ?>
                                            <br>
                                            <span class="badge-action <?= strpos($res->merchant_action, 'approved') !== false ? 'badge-action-approved' : (strpos($res->merchant_action, 'denied') !== false ? 'badge-action-denied' : 'badge-action-completed'); ?>">
                                                <?= ucwords(str_replace('_', ' ', $res->merchant_action)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', $res->created_at); ?></td>
                                    <td>
                                        <a href="<?= base_url('order_resolution/view/' . $res->ticket_number); ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No Order Resolution requests found.
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
