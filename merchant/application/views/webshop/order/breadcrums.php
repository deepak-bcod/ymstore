<ul class="nav nav-pills">
    <li class="<?php echo (isset($current_tab) && in_array($current_tab, ['orders', 'order', 'b2b-orders', 'ES-orders'])) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b-orders/"><?= lang('b2b_orders') ?></a></li>
    <!-- <li class="<?php //echo (isset($current_tab) && ($current_tab=='split-orders' || $current_tab=='split-order'))?'active':''; ?>"><a  href="<?php //echo base_url() ?>webshop/split-orders/"><?= lang('split_orders') ?></a></li> -->
    <!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='ES-orders' || $current_tab=='ES-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b-orders/"><?= lang('b2b_orders') ?></a></li> -->
    <!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='shipped-orders' || $current_tab=='shipped-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/shipped-orders/"><?= lang('shipped_orders') ?></a></li> -->
    <!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='cancel-orders' || $current_tab=='cancel-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/cancel-orders/"><?= lang('cancel_orders') ?></a></li> -->
    <!-- <div class="filter_order_seaching dataTables_filter">
        <label><input id="global-order-search" type="search" class="form-control form-control-sm" placeholder="<?= lang('search_by_order_number') ?>"></label>
    </div> -->
</ul>
