
<div class="container">

    <div class="page-title">
        <h2>Notifications</h2>
    </div>

    <div class="notification-page">

        <?php if (!empty($notifications)) : ?>

            <?php foreach ($notifications as $notification) : ?>

                <div class="notification-page-item <?= ((int)$notification->is_read === 0) ? 'unread' : ''; ?>"
                     data-id="<?= (int)$notification->id; ?>">

                    <div class="notification-page-title">
                        <?= html_escape($notification->title); ?>
                    </div>

                    <div class="notification-page-message">
                        <?= nl2br(html_escape($notification->message)); ?>
                    </div>

                    <div class="notification-page-date">
                        <?= !empty($notification->created_at)
                            ? date('d M Y h:i A', strtotime($notification->created_at))
                            : ''; ?>
                    </div>

                    <?php if ((int)$notification->is_read === 0) : ?>
                        <button type="button"
                                class="mark-notification-read"
                                data-id="<?= (int)$notification->id; ?>">
                            Mark as read
                        </button>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <div class="no-notifications">
                No notifications found.
            </div>

        <?php endif; ?>

    </div>

</div>

<script>
$(document).on('click', '.mark-notification-read', function () {

    var button = $(this);
    var notificationId = button.data('id');

    button.prop('disabled', true);

    $.ajax({
        url: "<?= site_url('customer/notifications/read/'); ?>" + notificationId,
        type: "POST",
        dataType: "json",

        success: function (response) {

            if (response.status) {

                var item = button.closest('.notification-page-item');

                item.removeClass('unread');

                button.remove();

            } else {

                alert('Unable to mark notification as read.');

                button.prop('disabled', false);
            }
        },

        error: function () {

            alert('An error occurred. Please try again.');

            button.prop('disabled', false);
        }
    });

});
</script>

