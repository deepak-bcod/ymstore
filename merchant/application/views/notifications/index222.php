<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <h1 class="head-name mb-4"><?= lang('notifications','Notifications') ?></h1>
        <?php $merchant_id = $this->session->userdata('merchant_id') ?: $this->session->userdata('RsUserId') ?: $this->session->userdata('user_id'); ?>
        <input type="hidden" id="merchant-id" value="<?= html_escape($merchant_id) ?>">
        <div class="text-end mb-3">
            <!-- Mark all read button -->
            <!-- <button id="mark-all" type="button" class="btn btn-primary purple-btn">
                <?= lang('mark_all_read','Mark all read') ?>
            </button> -->
        </div>

        <div class="table-responsive">
        <table class="plan-table table table-bordered table-striped toped-table" border="1" cellpadding="8" cellspacing="0">
            <thead class="text-center ym-basic-merchant-plan-2 ym-basic-plan-merchant-1 merchant-test">
                <tr>
                    <th><?= lang('sr_no','Sr No#') ?></th>
                    <th><?= lang('title','Title') ?></th>
                    <th><?= lang('message','Message') ?></th>
                    <!-- <th><?= lang('type','Type') ?></th> -->
                    <th><?= lang('date','Date') ?></th>
                   <th class="action-new"><?= lang('action','Action') ?></th> 
                </tr>
            </thead>
            <tbody class="latest-section">
                <?php if (!empty($notifications)): ?>
                    <?php foreach($notifications as $index => $n): ?>
                        <?php
                            $row_class = $n['is_read'] ? '' : 'table-warning';
                            $msg = isset($n['message']) ? $n['message'] : '';
                            $title = isset($n['title']) ? $n['title'] : '';
                            $type = isset($n['type']) ? $n['type'] . ($n['subtype'] ? ' / '.$n['subtype'] : '') : '';
                            $created = isset($n['created_at']) ? $n['created_at'] : '';
                        ?>
                        <tr data-id="<?= $n['id'] ?>" class="<?= $row_class ?>">
                            <td class="text-center"><?= ($index + 1) ?></td>
                            <td><?= html_escape($title) ?></td>
                            <td><?= nl2br(html_escape($msg)) ?></td>
                            <!-- <td class="text-center"><?= html_escape($type) ?></td> -->
                            <td class="text-center"><?= html_escape($created) ?></td>
                             <td class="test-section-new text-center">
                                <!-- <button type="button" class="btn btn-sm btn-primary view-notification" data-id="<?= $n['id'] ?>">
                                    <?= lang('view','View') ?>
                                </button> -->

                                <?php if ($n['is_read']): ?>
                                    <!-- <button type="button" class="btn btn-sm btn-outline-primary mark-unread" data-id="<?= $n['id'] ?>">
                                        <?= lang('mark_unread','Mark unread') ?>
                                    </button> -->
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary mark-read" data-id="<?= $n['id'] ?>">
                                        <?= lang('mark_read','Mark read') ?>
                                    </button>
                                <?php endif; ?>
                            </td> 
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center"><?= lang('no_notifications','No notifications') ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Config / localized labels
    var URL_MARK = '<?= site_url('notifications/mark') ?>';
    var URL_MARK_ALL = '<?= site_url('notifications/mark_all') ?>';
    var LANG_MARK_READ = '<?= lang('mark_read','Mark read') ?>';
    var LANG_MARK_UNREAD = '<?= lang('mark_unread','Mark unread') ?>';
    var LANG_ERROR = '<?= lang('error_occurred','An error occurred') ?>';
    var LANG_CONFIRM_MARK_ALL = '<?= lang('confirm_mark_all','Mark all notifications as read?') ?>';

    function post(url, data, cb) {
        var xhr = new XMLHttpRequest();
        var form = new FormData();
        
        // Add CSRF token if available
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            form.append('csrf_token', csrfToken.getAttribute('content'));
        } else {
            // Try hidden input
            var csrfInput = document.querySelector('input[name*="csrf"]');
            if (csrfInput) {
                form.append(csrfInput.name, csrfInput.value);
            }
        }
        
        for (var k in data) form.append(k, data[k]);
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            var res = {};
            try { res = JSON.parse(xhr.responseText); } catch (e) { res = { status: 'error' }; }
            if (typeof cb === 'function') cb(res);
        };
        xhr.onerror = function () {
            if (typeof cb === 'function') cb({ status: 'error' });
        };
        xhr.send(form);
    }

    // Utility: update unread counter (if present). delta can be +1 / -1
    function updateUnreadCount(delta) {
        var el = document.getElementById('notification-count');
        if (!el) return;
        var cur = parseInt(el.innerText, 10);
        if (isNaN(cur)) cur = 0;
        cur = Math.max(0, cur + delta);
        el.innerText = cur;
        if (cur === 0) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    }

    function setUnreadCount(value) {
        var el = document.getElementById('notification-count');
        if (!el) return;
        el.innerText = parseInt(value, 10) || 0;
        if (parseInt(value, 10) === 0) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    }

    // Helper: build action buttons for a row given is_read state and id
    function buildActionButtons(id, isRead) {
        if (isRead) {
            return ''; // No buttons for read notifications
        } else {
            return '<button type="button" class="btn btn-sm btn-primary mark-read" data-id="'+id+'">'+LANG_MARK_READ+'</button>';
        }
    }

    // EVENT DELEGATION: clicks anywhere in document
    document.addEventListener('click', function (e) {
        var target = e.target;

        // MARK READ
        if (target && target.classList.contains('mark-read')) {
            e.preventDefault();
            var id = target.getAttribute('data-id');
            if (!id) return;
            var tr = target.closest('tr');
            post(URL_MARK, { id: id, is_read: 1 }, function (res) {
                if (res.status === 'ok') {
                    if (tr) tr.classList.remove('table-warning');
                    // replace action buttons
                    var actionCell = tr ? tr.querySelector('.test-section-new') : null;
                    if (actionCell) actionCell.innerHTML = buildActionButtons(id, 1);
                    updateUnreadCount(-1);
                } else {
                    alert(LANG_ERROR);
                }
            });
            return;
        }

        // MARK UNREAD
        if (target && target.classList.contains('mark-unread')) {
            e.preventDefault();
            var idu = target.getAttribute('data-id');
            if (!idu) return;
            var tru = target.closest('tr');
            post(URL_MARK, { id: idu, is_read: 0 }, function (res) {
                if (res.status === 'ok') {
                    if (tru) tru.classList.add('table-warning');
                    // replace action buttons
                    var actionCellu = tru ? tru.querySelector('.test-section-new') : null;
                    if (actionCellu) actionCellu.innerHTML = buildActionButtons(idu, 0);
                    updateUnreadCount(+1);
                } else {
                    alert(LANG_ERROR);
                }
            });
            return;
        }

        // VIEW NOTIFICATION
        if (target && target.classList.contains('view-notification')) {
            e.preventDefault();
            var idv = target.getAttribute('data-id');
            var trv = target.closest('tr');
            if (!trv) return;
            var title = trv.children[1] ? trv.children[1].innerText : '';
            var message = trv.children[2] ? trv.children[2].innerText : '';
            // simple modal replacement: native alert for now (replace with modal if you want)
            alert(title + "\n\n" + message);
            return;
        }

      // MARK ALL
        if (target && target.id === 'mark-all') {
            e.preventDefault();
            if (!confirm(LANG_CONFIRM_MARK_ALL)) return;

            post(URL_MARK_ALL, {}, function (res) {
                if (res.status === 'ok') {
                    // update ALL rows visually
                    document.querySelectorAll('tr[data-id]').forEach(function (tr) {
                        tr.classList.remove('table-warning');
                        var id = tr.getAttribute('data-id');
                        var actionCell = tr.querySelector('.test-section-new');
                        if (actionCell) {
                            actionCell.innerHTML = buildActionButtons(id, 1);
                        }
                    });

                    // set unread counter from server
                    var unread = (typeof res.unread_count !== 'undefined') ? parseInt(res.unread_count, 10) : 0;
                    setUnreadCount(isNaN(unread) ? 0 : unread);
                } else {
                    alert(LANG_ERROR);
                }
            });

            return;
        }
    }, false);

    // If rows are replaced dynamically by other code, ensure the unread count is in sync:
    // Optional: on page load you can auto-set unread count from server-provided value (already done in PHP)
});
</script>
