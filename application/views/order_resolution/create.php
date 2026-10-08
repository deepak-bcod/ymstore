<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?php echo base_url(); ?>"><?php echo lang('home'); ?></a></li>
                <li><a href="<?php echo base_url('order_resolution'); ?>"><?php echo $this->lang->line('order_resolution') ?: 'Order Resolution'; ?></a></li>
                <li class="active"><?php echo $this->lang->line('raise_resolution_request') ?: 'New Resolution Request'; ?></li>
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
                    <h1><?php echo $this->lang->line('submit_order_resolution_request') ?: 'Submit Order Resolution Request'; ?></h1>
                    <p class="text-muted" style="margin-bottom: 25px;">
                        <?php echo $this->lang->line('order_resolution_desc') ?: 'Raise a resolution request directly with the merchant for delivery, refunds, replacements, or other order-related issues.'; ?>
                    </p>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <?php if (empty($orders)): ?>
                        <div class="alert alert-warning" style="margin-bottom: 20px;">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>No completed orders found.</strong> Order Resolution (Refund, Return, Replacement) is only available for orders with status "Complete". Once an order is completed, you can raise a resolution request here.
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo base_url('order_resolution/store'); ?>" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Subject Type -->
                            <div class="col-md-12 form-group">
                                <label for="category"><b><?php echo $this->lang->line('subject_type') ?: 'Subject Type'; ?> <span class="text-danger">*</span></b></label>
                                <select name="category" id="category" class="form-control" required <?php echo empty($orders) ? 'disabled' : ''; ?>>
                                    <option value="Refund" <?php echo (!isset($_GET['cat']) || $_GET['cat'] === 'Refund') ? 'selected' : ''; ?>>Refund</option>
                                    <option value="Delivery" <?php echo (isset($_GET['cat']) && $_GET['cat'] === 'Delivery') ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="Replacement" <?php echo (isset($_GET['cat']) && $_GET['cat'] === 'Replacement') ? 'selected' : ''; ?>>Replacement</option>
                                    <option value="Others" <?php echo (isset($_GET['cat']) && $_GET['cat'] === 'Others') ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-12 form-group">
                                <label for="priority"><b><?php echo $this->lang->line('priority') ?: 'Priority'; ?> <span class="text-danger">*</span></b></label>
                                <select name="priority" id="priority" class="form-control" required <?php echo empty($orders) ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo $this->lang->line('select_priority') ?: 'Select Priority'; ?></option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Order Number -->
                            <div class="col-md-6 form-group">
                                <label for="order_id"><b><?php echo $this->lang->line('order_number') ?: 'Order Number'; ?> <span class="text-danger">*</span></b></label>
                                <select name="order_id" id="order_id" class="form-control" required onchange="loadOrderProducts(this.value)">
                                    <option value=""><?php echo $this->lang->line('select_order') ?: '-- Select Order --'; ?></option>
                                    <?php if (!empty($orders)): ?>
                                        <?php foreach ($orders as $o): ?>
                                            <option value="<?php echo $o->order_id; ?>" <?php echo ($selected_order_id == $o->order_id) ? 'selected' : ''; ?>>
                                                Order #<?php echo htmlspecialchars($o->increment_id ?: $o->order_id); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Product -->
                            <div class="col-md-6 form-group">
                                <label for="product_id"><b><?php echo $this->lang->line('product_related') ?: 'Product (Related to Order)'; ?></b></label>
                                <select name="product_id" id="product_id" class="form-control">
                                    <option value="0"><?php echo $this->lang->line('all_items_general') ?: '-- All Items / General Issue --'; ?></option>
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?php echo $p->product_id; ?>" <?php echo ($selected_product_id == $p->product_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="form-group">
                            <label for="message"><b><?php echo $this->lang->line('message') ?: 'Message / Description'; ?> <span class="text-danger">*</span></b></label>
                            <textarea name="message" id="message" rows="5" class="form-control" placeholder="<?php echo $this->lang->line('enter_resolution_details') ?: 'Describe the issue or reason for this request in detail...'; ?>" required></textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="form-group">
                            <label for="attachment"><b><?php echo $this->lang->line('upload_image_optional') ?: 'Upload Image / Document (Optional)'; ?></b></label>
                            <input type="file" name="attachment" id="attachment" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Allowed formats: JPG, PNG, WEBP, PDF. Max size: 5MB.</small>
                        </div>

                        <div class="form-group text-right" style="margin-top: 30px;">
                            <a href="<?php echo base_url('order_resolution'); ?>" class="btn btn-default" style="margin-right: 10px;">
                                <?php echo $this->lang->line('cancel') ?: 'Cancel'; ?>
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_resolution_request') ?: 'Submit Resolution Request'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadOrderProducts(orderId) {
    var productSelect = document.getElementById('product_id');
    productSelect.innerHTML = '<option value="0">-- All Items / General Issue --</option>';

    if (!orderId) return;

    fetch('<?php echo base_url("order_resolution/get_order_products_ajax/"); ?>' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.products) {
                data.products.forEach(function(item) {
                    var opt = document.createElement('option');
                    opt.value = item.product_id;
                    opt.textContent = item.name;
                    productSelect.appendChild(opt);
                });
            }
        })
        .catch(err => console.error('Error fetching order products:', err));
}
</script>

<?php $this->load->view('common/footer'); ?>