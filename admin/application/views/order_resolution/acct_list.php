<?php 
$currency = defined('CURRENCY_TYPE') ? CURRENCY_TYPE : 'MUR';
$this->load->view('common/fbc-user/header'); 
?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="content-main form-dashboard">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fa fa-calculator text-warning"></i> Assigned Order Resolution (@Acct)</h1>
            <div>
                <a href="<?= base_url('order_resolution'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-list"></i> View All Tickets (@Help)
                </a>
            </div>
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
                        <th>Shopper</th>
                        <th>Merchant</th>
                        <th>Product</th>
                        <th>Priority</th>
                        <th>Action Required</th>
                        <th>Refund / Cost</th>
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
                                <td><?= htmlspecialchars($shopper_name); ?></td>
                                <td><?= htmlspecialchars($res->merchant_name ?: 'N/A'); ?></td>
                                <td><?= htmlspecialchars($res->product_name ?: 'All Items'); ?></td>
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
                                    <span class="badge <?= $p_badge; ?>"><?= htmlspecialchars($res->priority ?: 'Medium'); ?></span>
                                </td>
                                <td>
                                    <?php if ($res->delivery_option === 'ym_delivery'): ?>
                                        <span class="badge badge-info p-1">YM Delivery Service</span>
                                    <?php else: ?>
                                        <span class="badge badge-success p-1">Refund Processing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= ($res->refund_amount > 0) ? $currency . ' ' . number_format($res->refund_amount, 2) : 'N/A'; ?></strong>
                                </td>
                                <td>
                                    <span class="badge-status-<?= $status_slug; ?>">
                                        <?= htmlspecialchars($res->status); ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', $res->created_at); ?></td>
                                <td>
                                    <a href="<?= base_url('order_resolution/view/' . $res->ticket_number); ?>" class="btn btn-sm btn-warning">
                                        <i class="fa fa-pencil"></i> Process
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                No tickets currently assigned to Accounting (@Acct).
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
