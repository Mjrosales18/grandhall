<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Approve / Reject / Delete Review actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'approve' || $action == 'reject') {
        $status = ($action == 'approve') ? 'Approved' : 'Rejected';
        $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    header("Location: admin_reviews.php");
    exit();
}

// Handle Admin Reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_id'])) {
    $review_id = intval($_POST['review_id']);
    $reply = $_POST['admin_reply'];

    $stmt = $conn->prepare("UPDATE reviews SET admin_reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply, $review_id);
    $stmt->execute();
    header("Location: admin_reviews.php");
    exit();
}

include('admin_header.php');
?>

<style>
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-block;
    }
    .badge-approved { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde047; }
    .badge-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
</style>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h2>💬 Customer Feedback & Reviews Management</h2>
        <a href="admin_dashboard.php" class="btn" style="background: #4a5568; color: #fff; text-decoration: none; padding: 6px 14px; border-radius: 6px; font-size: 0.9rem;">⬅️ Back to Reservations</a>
    </div>
    <hr style="margin-bottom: 20px; border-color: #e2e8f0;">

    <?php
    $res = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
    if ($res && $res->num_rows > 0):
        while ($row = $res->fetch_assoc()):
            $status = $row['status'] ?? 'Pending';
    ?>
        <div style="border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px; margin-bottom: 18px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="font-size: 1.05rem; color: #0E0F12;"><?php echo htmlspecialchars($row['customer_name']); ?></strong> 
                    <span style="color: #d69e2e; margin-left: 8px;"><?php echo str_repeat('⭐', $row['rating']); ?></span>
                    <small style="color: #718096; margin-left: 10px;"><?php echo $row['created_at']; ?></small>
                </div>
                
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <!-- Status Badge -->
                    <?php 
                    if ($status === 'Approved') {
                        echo '<span class="badge badge-approved">Approved</span>';
                    } elseif ($status === 'Rejected') {
                        echo '<span class="badge badge-rejected">Rejected</span>';
                    } else {
                        echo '<span class="badge badge-pending">Pending</span>';
                    }
                    ?>

                    <!-- Approve / Reject Actions -->
                    <?php if ($status !== 'Approved'): ?>
                        <a href="admin_reviews.php?action=approve&id=<?php echo $row['id']; ?>" class="btn" style="background: #166534; color: #fff; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; text-decoration: none; font-weight: 600;">Approve</a>
                    <?php endif; ?>

                    <?php if ($status !== 'Rejected'): ?>
                        <a href="admin_reviews.php?action=reject&id=<?php echo $row['id']; ?>" class="btn" style="background: #c53030; color: #fff; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; text-decoration: none; font-weight: 600;">Reject</a>
                    <?php endif; ?>

                    <!-- Delete Button -->
                    <a href="admin_reviews.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this feedback?');">🗑️ Delete</a>
                </div>
            </div>

            <p style="margin: 12px 0; color: #2d3748; font-size: 0.95rem; line-height: 1.5;"><?php echo htmlspecialchars($row['comment']); ?></p>

            <!-- Reply Section -->
            <form action="admin_reviews.php" method="POST" style="margin-top: 12px; background: #f7fafc; padding: 12px; border-radius: 6px; border: 1px solid #edf2f7;">
                <input type="hidden" name="review_id" value="<?php echo $row['id']; ?>">
                <label style="font-size: 0.85rem; color: #4a5568;"><strong>Admin Reply:</strong></label>
                <textarea name="admin_reply" style="width: 100%; padding: 8px; margin: 6px 0 10px; border: 1px solid #cbd5e0; border-radius: 4px; height: 60px; font-size: 0.9rem;" placeholder="Write a response to this customer..."><?php echo htmlspecialchars($row['admin_reply'] ?? ''); ?></textarea>
                <button type="submit" class="btn btn-success" style="padding: 6px 14px; font-size: 0.85rem;">Save / Update Reply</button>
            </form>
        </div>
    <?php 
        endwhile;
    else:
    ?>
        <p style="text-align: center; color: #718096; padding: 20px;">No customer reviews found.</p>
    <?php endif; ?>
</div>

</div>
</body>
</html>