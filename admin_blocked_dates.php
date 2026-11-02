<?php
session_start();
include('db.php');
if (!isset($_SESSION['admin'])) { header('Location: admin_login.php'); exit(); }
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_date'])) {
    $date = trim($_POST['blocked_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $date_object = DateTime::createFromFormat('Y-m-d', $date);
    if (!$date_object || $date_object->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
        $message = 'Choose a valid date today or later.'; $message_type = 'error';
    } else {
        $check = $conn->prepare("SELECT id FROM reservations WHERE event_date = ? AND status IN ('Pending','Approved') LIMIT 1");
        $check->bind_param('s', $date); $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = 'This date already has a pending or approved reservation. You cannot block it.'; $message_type = 'error';
        } else {
            $insert = $conn->prepare('INSERT INTO blocked_dates (blocked_date, reason) VALUES (?, ?)');
            $insert->bind_param('ss', $date, $reason);
            if ($insert->execute()) $message = 'Date successfully marked as unavailable.';
            else { $message = ($insert->errno == 1062) ? 'This date is already blocked.' : 'Unable to block this date.'; $message_type = 'error'; }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblock_id'])) {
    $id = (int)$_POST['unblock_id'];
    $delete = $conn->prepare('DELETE FROM blocked_dates WHERE id = ?');
    $delete->bind_param('i', $id);
    if ($delete->execute() && $delete->affected_rows > 0) $message = 'The date is available for booking again.';
    else { $message = 'Unable to remove the blocked date.'; $message_type = 'error'; }
}
include('admin_header.php');
?>
<div class="card">
  <h2>🗓️ Manage Unavailable Dates</h2>
  <p style="color:var(--color-text-muted);margin-bottom:18px;">Block dates for maintenance, private events, holidays, or other venue schedules. Customers will see these dates as unavailable on the public calendar.</p>
  <?php if ($message !== ''): ?><div style="padding:12px;border-radius:7px;margin-bottom:16px;background:<?php echo $message_type === 'success' ? '#dcfce7' : '#fee2e2'; ?>;color:<?php echo $message_type === 'success' ? '#166534' : '#991b1b'; ?>;"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;align-items:end;margin-bottom:24px;">
    <div><label for="blocked_date"><strong>Date to block</strong></label><input id="blocked_date" type="date" name="blocked_date" min="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;"></div>
    <div><label for="reason"><strong>Reason (optional)</strong></label><input id="reason" type="text" name="reason" maxlength="255" placeholder="e.g. Maintenance" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;"></div>
    <button class="btn btn-success" type="submit" name="block_date" value="1">＋ Block Date</button>
  </form>
  <h3 style="margin-bottom:12px;">Currently Blocked Dates</h3>
  <div class="table-responsive"><table><thead><tr><th>Date</th><th>Reason</th><th>Date Added</th><th>Action</th></tr></thead><tbody>
  <?php $list = $conn->query('SELECT * FROM blocked_dates ORDER BY blocked_date ASC'); if ($list && $list->num_rows): while ($row = $list->fetch_assoc()): ?>
    <tr><td><strong><?php echo htmlspecialchars(date('F d, Y', strtotime($row['blocked_date']))); ?></strong></td><td><?php echo htmlspecialchars($row['reason'] ?: '—'); ?></td><td><?php echo htmlspecialchars($row['created_at']); ?></td><td><form method="POST" onsubmit="return confirm('Make this date available for booking again?');"><input type="hidden" name="unblock_id" value="<?php echo (int)$row['id']; ?>"><button class="btn btn-danger" type="submit">Unblock</button></form></td></tr>
  <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center;">No dates are currently blocked.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>
</div></body></html>
