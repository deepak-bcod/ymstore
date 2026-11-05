<?php
$mr = $details['merchant_review'] ?? array();
$prs = $details['product_reviews'] ?? array();
$merchant_name = !empty($mr['publication_name']) ? $mr['publication_name'] : (!empty($mr['trade_name']) ? $mr['trade_name'] : 'Merchant');
$mr_status = strtolower($mr['status'] ?? 'pending');
?>

<div class="review-order-modal-content">
    <!-- Order & Customer Header -->
    <div class="card mb-3 bg-light" style="border: 1px solid #d1d5db; border-radius: 6px;">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <small class="text-muted d-block">Sub-Order Barcode</small>
                    <strong class="text-primary" style="font-size: 15px;"><?= htmlspecialchars($mr['B2B_order_id'] ?? ('#' . ($mr['order_id'] ?? ''))) ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Shopper</small>
                    <strong><?= htmlspecialchars($mr['customer_name'] ?? 'Guest') ?></strong>
                    <div class="text-muted small"><?= htmlspecialchars($mr['customer_email'] ?? '') ?></div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Submitted On</small>
                    <span class="text-dark"><?= !empty($mr['created_at']) ? date("d M Y, h:i A", strtotime($mr['created_at'])) : '-' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Merchant Service Review Card -->
    <div class="card mb-4" style="border: 1px solid #b8daff; border-radius: 6px;">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background: #e8f4fd;">
            <div>
                <span class="badge badge-info mr-2" style="background:#17a2b8; color:#fff; font-size:11px;">Merchant Service</span>
                <strong class="text-dark font-weight-bold" style="font-size: 14px;"><?= htmlspecialchars($merchant_name) ?></strong>
            </div>
            <div>
                <span class="mr-2">Status:</span>
                <?php if ($mr_status === 'published'): ?>
                    <span class="badge badge-success" style="background:#28a745; font-size:12px; padding:5px 9px;">Published</span>
                <?php elseif ($mr_status === 'rejected'): ?>
                    <span class="badge badge-danger" style="background:#dc3545; font-size:12px; padding:5px 9px;">Rejected</span>
                <?php else: ?>
                    <span class="badge badge-warning" style="background:#ffc107; color:#212529; font-size:12px; padding:5px 9px;">Pending</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body py-3">
            <div class="d-flex align-items-center mb-2">
                <span class="text-muted mr-2" style="width: 140px;">Customer Service:</span>
                <span style="color:#f59e0b; font-size:16px;">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <?= ($s <= (int)($mr['rating'] ?? 0)) ? '★' : '<span style="color:#d1d5db;">★</span>' ?>
                    <?php endfor; ?>
                </span>
                <strong class="ml-2 text-dark">(<?= (int)($mr['rating'] ?? 0) ?>/5)</strong>
            </div>

            <div class="mb-3">
                <span class="text-muted d-block mb-1">Feedback / Comments:</span>
                <div class="p-2 bg-light rounded" style="border: 1px solid #e5e7eb; font-size: 13.5px; color: #1f2937; min-height: 40px;">
                    <?= !empty($mr['comments']) ? nl2br(htmlspecialchars($mr['comments'])) : '<em class="text-muted">No comments provided.</em>' ?>
                </div>
            </div>

            <!-- Merchant Moderation Controls -->
            <div class="d-flex align-items-center justify-content-end" style="gap: 8px;">
                <span class="small font-weight-bold text-muted mr-2">Moderate Merchant Review:</span>
                <button type="button" class="btn btn-sm btn-success <?= ($mr_status === 'published') ? 'disabled' : '' ?>" onclick="changeReviewStatus('merchant', <?= (int)$mr['id'] ?>, 'published', true)">
                    <i class="fa fa-check"></i> Publish
                </button>
                <button type="button" class="btn btn-sm btn-danger <?= ($mr_status === 'rejected') ? 'disabled' : '' ?>" onclick="changeReviewStatus('merchant', <?= (int)$mr['id'] ?>, 'rejected', true)">
                    <i class="fa fa-times"></i> Reject
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark <?= ($mr_status === 'pending') ? 'disabled' : '' ?>" onclick="changeReviewStatus('merchant', <?= (int)$mr['id'] ?>, 'pending', true)">
                    <i class="fa fa-clock"></i> Set Pending
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Individual Product Reviews List -->
    <h6 class="font-weight-bold text-dark mb-3">
        <i class="fa fa-box-open mr-1 text-primary"></i> Purchased Product Reviews (<?= count($prs) ?>)
    </h6>

    <?php if (!empty($prs)): ?>
        <?php foreach ($prs as $idx => $pr): ?>
            <?php
            $p_name = !empty($pr['product_name']) ? $pr['product_name'] : (!empty($pr['catalog_product_name']) ? $pr['catalog_product_name'] : 'Product #' . ($idx + 1));
            $pr_status = strtolower($pr['status'] ?? 'pending');
            ?>
            <div class="card mb-3" style="border: 1px solid #e5e7eb; border-radius: 6px;">
                <div class="card-header d-flex justify-content-between align-items-center py-2 bg-white">
                    <div>
                        <span class="badge mr-2" style="background:#6f42c1; color:#fff; font-size:11px;">Product #<?= $idx + 1 ?></span>
                        <strong class="text-dark font-weight-bold" style="font-size: 13.5px;"><?= htmlspecialchars($p_name) ?></strong>
                    </div>
                    <div>
                        <span class="mr-2">Status:</span>
                        <?php if ($pr_status === 'published'): ?>
                            <span class="badge badge-success" style="background:#28a745; font-size:12px; padding:5px 9px;">Published</span>
                        <?php elseif ($pr_status === 'rejected'): ?>
                            <span class="badge badge-danger" style="background:#dc3545; font-size:12px; padding:5px 9px;">Rejected</span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="background:#ffc107; color:#212529; font-size:12px; padding:5px 9px;">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body py-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted mr-2" style="width: 140px;">Product Rating:</span>
                        <span style="color:#f59e0b; font-size:16px;">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <?= ($s <= (int)($pr['rating'] ?? 0)) ? '★' : '<span style="color:#d1d5db;">★</span>' ?>
                            <?php endfor; ?>
                        </span>
                        <strong class="ml-2 text-dark">(<?= (int)($pr['rating'] ?? 0) ?>/5)</strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted d-block mb-1">Product Feedback:</span>
                        <div class="p-2 bg-light rounded" style="border: 1px solid #e5e7eb; font-size: 13.5px; color: #1f2937; min-height: 40px;">
                            <?= !empty($pr['comments']) ? nl2br(htmlspecialchars($pr['comments'])) : '<em class="text-muted">No comments provided.</em>' ?>
                        </div>
                    </div>

                    <!-- Independent Product Moderation Controls -->
                    <div class="d-flex align-items-center justify-content-end" style="gap: 8px;">
                        <span class="small font-weight-bold text-muted mr-2">Moderate Product Review:</span>
                        <button type="button" class="btn btn-sm btn-success <?= ($pr_status === 'published') ? 'disabled' : '' ?>" onclick="changeReviewStatus('product', <?= (int)$pr['id'] ?>, 'published', true)">
                            <i class="fa fa-check"></i> Publish
                        </button>
                        <button type="button" class="btn btn-sm btn-danger <?= ($pr_status === 'rejected') ? 'disabled' : '' ?>" onclick="changeReviewStatus('product', <?= (int)$pr['id'] ?>, 'rejected', true)">
                            <i class="fa fa-times"></i> Reject
                        </button>
                        <button type="button" class="btn btn-sm btn-warning text-dark <?= ($pr_status === 'pending') ? 'disabled' : '' ?>" onclick="changeReviewStatus('product', <?= (int)$pr['id'] ?>, 'pending', true)">
                            <i class="fa fa-clock"></i> Set Pending
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No product reviews found for this order.</p>
    <?php endif; ?>
</div>
