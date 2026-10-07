<?php $this->load->view('common/header'); ?>

<style>
.badge-status-open { background-color: #007bff; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-processing { background-color: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-done { background-color: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close { background-color: #6c757d; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-reopen { background-color: #fd7e14; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-status-close-final { background-color: #343a40; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

.badge-action { font-size: 11px; padding: 3px 6px; border-radius: 3px; font-weight: 600; display: inline-block; margin-top: 2px; }
.badge-action-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-action-denied { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.badge-action-completed { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
</style>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li class="active"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php 
            $data['side_tab'] = 'order_resolution';
            $this->load->view('common/profile_sidebar', $data); 
            ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-sm-8 col-md-8">
                            <h1 style="margin: 0;"><?php echo $this->lang->line('order_resolution_requests') ?: 'Order Resolution Requests'; ?></h1>
                        </div>
                        <div class="col-sm-4 col-md-4 text-right">
                            <a href="<?php echo base_url('order_resolution/create'); ?>" class="btn btn-primary">
                                <i class="fa fa-plus"></i> <?php echo $this->lang->line('raise_resolution_request') ?: 'New Resolution Request'; ?>
                            </a>
                        </div>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <div class="table-responsive" style="margin-top: 15px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr style="background-color: #f9f9f9;">
                                    <th><?php echo $this->lang->line('ticket_no') ?: 'Ticket #'; ?></th>
                                    <th><?php echo $this->lang->line('order_number') ?: 'Order #'; ?></th>
                                    <th><?php echo $this->lang->line('product') ?: 'Product'; ?></th>
                                    <th><?php echo $this->lang->line('category') ?: 'Category'; ?></th>
                                    <th><?php echo $this->lang->line('priority') ?: 'Priority'; ?></th>
                                    <th><?php echo $this->lang->line('status') ?: 'Status'; ?></th>
                                    <th><?php echo $this->lang->line('date') ?: 'Date'; ?></th>
                                    <th style="width: 80px; text-align: center;"><?php echo $this->lang->line('action') ?: 'Action'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($resolutions)): ?>
                                    <?php foreach ($resolutions as $res): 
                                        $status_slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $res->status));
                                        $badge_class = 'badge-status-' . $status_slug;
                                    ?>
                                        <tr>
                                            <td><b><?php echo htmlspecialchars($res->ticket_number); ?></b></td>
                                            <td><?php echo htmlspecialchars($res->order_number); ?></td>
                                            <td><?php echo htmlspecialchars($res->product_name ?: 'All Items'); ?></td>
                                            <td><span class="label label-default"><?php echo htmlspecialchars($res->category); ?></span></td>
                                            <td><?php echo htmlspecialchars($res->priority); ?></td>
                                            <td>
                                                <span class="<?php echo $badge_class; ?>">
                                                    <?php echo htmlspecialchars($res->status); ?>
                                                </span>
                                                <?php if ($res->merchant_action !== 'none'): ?>
                                                    <br>
                                                    <span class="badge-action <?php echo strpos($res->merchant_action, 'approved') !== false ? 'badge-action-approved' : (strpos($res->merchant_action, 'denied') !== false ? 'badge-action-denied' : 'badge-action-completed'); ?>">
                                                        <?php echo ucwords(str_replace('_', ' ', $res->merchant_action)); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', $res->created_at); ?></td>
                                            <td style="text-align: center;">
                                                <a href="<?php echo base_url('order_resolution/view/' . $res->ticket_number); ?>" class="btn btn-sm btn-info" title="View Ticket">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center" style="padding: 30px;">
                                            <p><?php echo $this->lang->line('no_order_resolution_tickets') ?: 'No Order Resolution requests found.'; ?></p>
                                            <a href="<?php echo base_url('customer/my-orders'); ?>" class="btn btn-default">
                                                <?php echo $this->lang->line('view_my_orders') ?: 'Go to My Orders'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
