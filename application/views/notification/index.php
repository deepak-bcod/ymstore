<div class="container" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Notifications</h2>

        <a href="<?= base_url('notification/mark_all_read'); ?>"
           class="btn btn-dark"
           style="background: #222; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px;">
            Mark All Read
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered"
               style="background: #fff; border-collapse: collapse; width: 100%;">

            <thead>
                <tr style="background: #ffeb3b; color: #000;">
                    <th style="padding: 12px; border: 1px solid #ddd;">Title</th>
                    <th style="padding: 12px; border: 1px solid #ddd;">Message</th>
                    <th style="padding: 12px; border: 1px solid #ddd;">Date</th>
                    <th style="padding: 12px; border: 1px solid #ddd;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($notifications)): ?>

                    <?php foreach ($notifications as $row): ?>

                        <tr style="<?= ($row->is_read == 0)
                            ? 'background-color: #fffdf0; font-weight: bold;'
                            : ''; ?>">

                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <?= htmlspecialchars($row->title); ?>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <?= htmlspecialchars($row->message); ?>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd;">
                               <?= date('d-m-Y h:i A', strtotime($row->created_at)); ?>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">

                                <?php if ($row->is_read == 0): ?>

                                    <a href="<?= base_url('notification/mark_read/' . $row->id); ?>"
                                       class="btn btn-sm btn-primary"
                                       style="background: #007bff; color: #fff; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px;">
                                        Mark Read
                                    </a>

                                <?php else: ?>

                                    <span style="color: #28a745; font-size: 12px;">
                                        Read
                                    </span>

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4"
                            style="text-align: center; padding: 20px; color: #777;">
                            No notifications found.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>