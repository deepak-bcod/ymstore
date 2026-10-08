<style>
    /* Limits the message cell to a maximum of 2 lines cleanly inside the box */
    .message-cell {
        max-width: 450px; /* Adjust width as needed */
    }
    .message-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal !important;
    }
</style>

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
                    <th style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;">Date</th>
                    <th style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($notifications)): ?>

                    <?php foreach ($notifications as $row): ?>

                        <tr style="<?= ($row->is_read == 0)
                            ? 'background-color: #fffdf0; font-weight: bold;'
                            : ''; ?>">

                            <td style="padding: 12px; border: 1px solid #ddd; white-space: nowrap;">
                                <?= htmlspecialchars($row->title); ?>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd;" class="message-cell">
                                <div class="message-text">
                                    <?= htmlspecialchars($row->message); ?>
                                </div>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd; white-space: nowrap; text-align: center;">
                                <?= date('d-m-Y', strtotime($row->created_at)); ?><br>
                                <span style="font-size: 12px; color: #555;"><?= date('H:i:s', strtotime($row->created_at)); ?></span>
                            </td>

                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center; white-space: nowrap;">

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