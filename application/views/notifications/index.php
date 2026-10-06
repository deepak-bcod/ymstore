<div class="container">

    <div class="page-title">
        <h2>Notifications</h2>
    </div>

    <div class="notification-page">

        <?php if (!empty($notifications)) : ?>

            <?php foreach ($notifications as $notification) : ?>

                <div class="notification-page-item
                    <?= ($notification->is_read == 0) ? 'unread' : ''; ?>">

                    <div class="notification-page-title">
                        <?= htmlspecialchars($notification->title); ?>
                    </div>

                    <div class="notification-page-message">
                        <?= htmlspecialchars($notification->message); ?>
                    </div>

                    <div class="notification-page-date">
                        <?= date(
                            'd M Y h:i A',
                            strtotime($notification->created_at)
                        ); ?>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <div class="no-notifications">
                No notifications found.
            </div>

        <?php endif; ?>

    </div>

</div>
