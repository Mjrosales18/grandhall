<?php
include('db.php');
include('header.php');

$code = isset($_GET['code']) ? $_GET['code'] : '';
$reservation = null;

if ($code) {
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $reservation = $stmt->get_result()->fetch_assoc();
}
?>

<div class="card">
    <h2>🔍 Track Reservation Status</h2>
    <form action="status.php" method="GET" style="margin-top: 15px;">
        <input type="text" name="code" placeholder="Enter Reservation Code (e.g. RES-XXXXXX)" value="<?php echo htmlspecialchars($code); ?>" required style="padding: 8px; width: 300px;">
        <button type="submit" class="btn">Search</button>
    </form>

    <?php if ($reservation): ?>
        <hr style="margin: 20px 0;">
        <h3>Reservation Code: <?php echo $reservation['reservation_code']; ?></h3>
        <p><strong>Status:</strong> 
            <span class="badge badge-<?php echo strtolower($reservation['status']); ?>">
                <?php echo $reservation['status']; ?>
            </span>
        </p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($reservation['full_name']); ?></p>
        <p><strong>Event Date:</strong> <?php echo $reservation['event_date']; ?></p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($reservation['total_amount'], 2); ?></p>
        <p><strong>Downpayment Paid:</strong> ₱<?php echo number_format($reservation['downpayment_amount'], 2); ?></p>

        <?php if ($reservation['status'] == 'Approved'): ?>
            <br>
            <a href="print_invoice.php?code=<?php echo $reservation['reservation_code']; ?>" target="_blank" class="btn btn-success">🖨️ Print Approved Reservation Form</a>
        <?php elseif ($reservation['status'] == 'Rejected'): ?>
            <p style="color: red; margin-top: 10px;">Reason/Notes: <?php echo htmlspecialchars($reservation['admin_notes']); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</div>
</body>
</html>