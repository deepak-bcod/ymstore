
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

$(document).ready(function () {

    /*
     * Notification bell click
     */
    $('#notificationBell').on('click', function (e) {

        e.preventDefault();
        e.stopPropagation();

        $('#notificationDropdown').toggleClass('show');

        if ($('#notificationDropdown').hasClass('show')) {
            loadNotifications();
        }

    });


    /*
     * Don't close dropdown when clicking inside it
     */
    $('#notificationDropdown').on('click', function (e) {
        e.stopPropagation();
    });


    /*
     * Close dropdown when clicking outside
     */
    $(document).on('click', function () {
        $('#notificationDropdown').removeClass('show');
    });


    /*
     * Load latest notifications
     */
    function loadNotifications() {

        $.ajax({

            url: "<?= site_url('notifications/latest'); ?>",

            type: "GET",

            dataType: "json",

            success: function (response) {

                if (!response.status) {
                    return;
                }

                /*
                 * Update notification count
                 */
                var unreadCount = parseInt(response.unread_count);

                if (unreadCount > 0) {

                    $('#notificationCount')
                        .text(unreadCount)
                        .show();

                } else {

                    $('#notificationCount').hide();

                }


                /*
                 * Build notification list
                 */
                var html = '';

                if (response.notifications.length > 0) {

                    $.each(response.notifications, function (index, notification) {

                        var unreadClass =
                            parseInt(notification.is_read) === 0
                                ? 'unread'
                                : '';

                        html += `
                            <div class="notification-dropdown-item ${unreadClass}"
                                 data-id="${notification.id}">

                                <div class="notification-dropdown-title">
                                    ${escapeHtml(notification.title)}
                                </div>

                                <div class="notification-dropdown-message">
                                    ${escapeHtml(notification.message)}
                                </div>

                                <div class="notification-dropdown-date">
                                    ${notification.created_at}
                                </div>

                            </div>
                        `;

                    });

                } else {

                    html = `
                        <div class="no-notifications">
                            No notifications found.
                        </div>
                    `;

                }

                $('#notificationList').html(html);

            },

            error: function () {

                $('#notificationList').html(`
                    <div class="no-notifications">
                        Unable to load notifications.
                    </div>
                `);

            }

        });

    }


    /*
     * Mark notification as read
     */
    $(document).on(
        'click',
        '.notification-dropdown-item.unread',
        function () {

            var item = $(this);

            var notificationId = item.data('id');

            $.ajax({

                url: "<?= site_url('notifications/read/'); ?>" + notificationId,

                type: "POST",

                dataType: "json",

                success: function (response) {

                    if (response.status) {

                        item.removeClass('unread');

                        /*
                         * Reload count
                         */
                        loadNotifications();

                    }

                }

            });

        }
    );


    /*
     * Simple HTML escaping
     */
    function escapeHtml(text) {

        if (!text) {
            return '';
        }

        return $('<div>').text(text).html();

    }

});

</script>

<script>
$(document).on('click', '.mark-notification-read', function () {

    var button = $(this);
    var notificationId = button.data('id');

    button.prop('disabled', true);

    $.ajax({
        url: "<?= site_url('notifications/read/'); ?>" + notificationId,

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

