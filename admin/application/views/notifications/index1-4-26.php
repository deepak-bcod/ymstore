<?php $this->load->view('common/fbc-user/header');
// echo "<pre>";print_r($notifications);die;


?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<div class="main-inner">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Notifications</h1>
<!-- 
        <div class="float-right product-filter-div">
            <button class="purple-btn" id="markAllBtn">Mark All Read</button>
        </div> -->
    </div>

    <div class="content-main form-dashboard">
        <div class="table-responsive text-center">

            <?php if($this->session->flashdata('success')): ?>
                <p style="color:green"><?= $this->session->flashdata('success'); ?></p>
            <?php endif; ?>

            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Date</th>
                        <!-- <th>Status</th>
                        <th width="140">Actions</th> -->
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($notifications)): ?>
                        <?php foreach($notifications as $index =>  $n): ?>
                            <?php
                                // ensure $n is array-like
                                // $nid = isset($n['id']) ? $n['id'] : (isset($n->id) ? $n->id : '');
                                // $is_read = isset($n['is_read']) ? (int)$n['is_read'] : (isset($n->is_read) ? (int)$n->is_read : 1);
                                // $message = isset($n['message']) ? $n['message'] : (isset($n->message) ? $n->message : '');
                                // $rtype = isset($n['recipient_type']) ? $n['recipient_type'] : (isset($n->recipient_type) ? $n->recipient_type : '');
                                // $created = isset($n['created_at']) ? $n['created_at'] : (isset($n->created_at) ? $n->created_at : '');
                                // $row_class = $is_read === 0 ? 'table-warning' : '';
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
                                <td class="text-center"><?= html_escape($created) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No notifications found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script>
$(function(){

  // normalize server response to object
  function parseResponse(res) {
    if (!res) return null;
    if (typeof res === 'object') return res;
    try {
      return JSON.parse(res);
    } catch (e) {
      return null;
    }
  }

  // helper: find row by data-id or id
  function findRow(id) {
    var row = $('tr[data-id="'+id+'"]');
    if (row.length) return row;
    row = $('#notif-' + id);
    return row.length ? row : null;
  }

  // build action buttons html
  function buildActionButtons(id, isRead) {
    var html = '';
    if (isRead) {
      html += '<button class="btn btn-sm btn-outline-primary markBtn" data-id="'+id+'" data-status="0">Mark Unread</button>';
    } else {
      html += '<button class="btn btn-sm btn-primary markBtn" data-id="'+id+'" data-status="1">Mark Read</button>';
    }
    return html;
  }

  // update a single row's visual state
  function updateRowVisual(id, isRead) {
    var $row = findRow(id);
    if (!$row) return;
    if (isRead == 1) {
      $row.removeClass('table-warning');
      $row.find('.status-cell').html('<span class="badge badge-success">Read</span>');
    } else {
      $row.addClass('table-warning');
      $row.find('.status-cell').html('<span class="badge badge-danger">Unread</span>');
    }
    // update action cell
    var $action = $row.find('.action-cell');
    if ($action.length) {
      $action.html(buildActionButtons(id, isRead == 1));
    }
  }

  // optionally update unread counter elements if present
  function setUnreadCount(val) {
    var badge = $('#notification-count, #unread-count');
    if (badge.length) {
      badge.text(val);
      if (parseInt(val,10) === 0) badge.hide(); else badge.show();
    }
  }

  // DELEGATED handler for mark read/unread buttons
  $(document).on('click', '.markBtn', function(e){
    e.preventDefault();
    var $btn = $(this);
    var id = $btn.data('id');
    var status = parseInt($btn.data('status'), 10); // 1 => mark read, 0 => mark unread

    if (!id) return;

    $btn.prop('disabled', true);

    $.post("<?= site_url('notifications/mark'); ?>", { id: id, is_read: status })
      .done(function(raw){
        var res = parseResponse(raw);
        if (res && (res.status === 'ok' || res.status === 'success')) {
          // update UI
          updateRowVisual(id, status);
          // if server returned unread_count, use it
          if (typeof res.unread_count !== 'undefined') {
            setUnreadCount(parseInt(res.unread_count, 10) || 0);
          } else {
            // best-effort local adjustment
            var badge = $('#notification-count, #unread-count');
            if (badge.length) {
              var cur = parseInt(badge.text()||'0',10) || 0;
              cur = status === 1 ? Math.max(0, cur - 1) : cur + 1;
              setUnreadCount(cur);
            }
          }
        } else {
          alert('An error occurred. Please try again.');
        }
      })
      .fail(function(){
        alert('An error occurred. Please try again.');
      })
      .always(function(){
        $btn.prop('disabled', false);
      });
  });

  // MARK ALL handler
  $(document).on('click', '#markAllBtn', function(e){
    e.preventDefault();
    if (!confirm('Mark all notifications as read?')) return;

    var $btn = $(this);
    var origText = $btn.text();
    $btn.prop('disabled', true).text('Processing...');

    $.post("<?= site_url('notifications/mark_all'); ?>", {})
      .done(function(raw){
        var res = parseResponse(raw);
        if (res && (res.status === 'ok' || res.status === 'success')) {
          // Set all rows to read visually
          $('tr[data-id], tr[id^="notif-"]').each(function(){
            var $r = $(this);
            var id = $r.data('id') || ($r.attr('id') ? $r.attr('id').replace('notif-','') : null);
            $r.removeClass('table-warning');
            $r.find('.status-cell').html('<span class="badge badge-success">Read</span>');
            if (id) {
              var actionCell = $r.find('.action-cell');
              if (actionCell.length) actionCell.html(buildActionButtons(id, true));
            }
          });
          // update unread count if server provided
          if (typeof res.unread_count !== 'undefined') {
            setUnreadCount(parseInt(res.unread_count,10) || 0);
          } else {
            setUnreadCount(0);
          }
        } else {
          // helpful message if server says session missing
          if (res && res.message === 'missing_recipient_id') {
            alert('Session missing user id. Please login again or reload the page.');
          } else {
            alert('An error occurred. Please try again.');
          }
          console.warn('mark_all response:', res);
        }
      })
      .fail(function(){
        alert('An error occurred. Please try again.');
      })
      .always(function(){
        $btn.prop('disabled', false).text(origText);
      });
  });

});
</script>

<style>
.table-warning { background-color: #fff4e5 !important; } /* keep your highlight */
.status-cell .badge { font-size: 0.85rem; }
.action-cell .btn { margin-right:6px; }
</style>
