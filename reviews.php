<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['customer_name'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Save review with default status as 'Pending'
    $stmt = $conn->prepare("INSERT INTO reviews (customer_name, rating, comment, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("sis", $name, $rating, $comment);
    $stmt->execute();
    
    // Redirect with a success notice
    header("Location: reviews.php?submitted=true");
    exit();
}

include('header.php');
?>

<div class="card" id="reviews-top">
    <h2>💬 Customer Feedback & Reviews</h2>
    <br>

    <?php if (isset($_GET['submitted'])): ?>
        <div style="background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">
            ✅ Thank you! Your review has been submitted and is currently pending admin approval.
        </div>
    <?php endif; ?>
    
    <h3>Submit Your Review</h3>
    <form action="reviews.php" method="POST">
        <label>Name:</label>
        <input type="text" name="customer_name" required placeholder="John Doe" 
               oninput="this.value = this.value.replace(/\b\w/g, function(char) { return char.toUpperCase(); });" 
               style="width: 100%; padding: 8px; margin: 8px 0; text-transform: capitalize;">

        <label>Rating:</label>
        <select name="rating" required style="width: 100%; padding: 8px; margin: 8px 0;">
            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
            <option value="4">⭐⭐⭐⭐ (4/5)</option>
            <option value="3">⭐⭐⭐ (3/5)</option>
            <option value="2">⭐⭐ (2/5)</option>
            <option value="1">⭐ (1/5)</option>
        </select>

        <label>Feedback:</label>
        <textarea name="comment" required style="width: 100%; padding: 8px; margin: 8px 0; height: 80px;"></textarea>

        <button type="submit" class="btn">Submit Review</button>
    </form>

    <hr style="margin: 30px 0;">

    <h3>What Our Clients Say</h3>
    <?php
    // Fetch ONLY Approved reviews for public viewing
    $result = $conn->query("SELECT * FROM reviews WHERE status = 'Approved' ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0):
        while($row = $result->fetch_assoc()):
    ?>
        <div style="background: #f7fafc; padding: 15px; border-radius: 6px; margin-top: 15px; border-left: 4px solid #D4AF37;">
            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong> 
            <span style="color: #d69e2e;"><?php echo str_repeat('⭐', $row['rating']); ?></span>
            <p style="margin-top: 5px;"><?php echo htmlspecialchars($row['comment']); ?></p>
            <small style="color: #718096;"><?php echo $row['created_at']; ?></small>

            <?php if (!empty($row['admin_reply'])): ?>
                <div style="margin-top: 10px; padding: 10px; background: #ebf8ff; border-left: 3px solid #3182ce; border-radius: 4px;">
                    <strong style="color: #2b6cb0;">🏛️ Response from Grand Horizon Admin:</strong>
                    <p style="margin-top: 3px; font-size: 0.95rem;"><?php echo htmlspecialchars($row['admin_reply']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php 
        endwhile; 
    else:
    ?>
        <p style="color: #718096; font-style: italic; margin-top: 15px;">No approved reviews yet. Be the first to share your experience!</p>
    <?php endif; ?>

    <!-- Back to Top Button at the Lower Part -->
    <div style="text-align: right; margin-top: 30px; border-top: 1px dashed #cbd5e0; padding-top: 20px;">
        <button onclick="scrollToTop()" class="btn" style="background: #1F232B; color: #D4AF37; border: 1px solid #D4AF37; padding: 8px 18px; font-weight: 700; cursor: pointer; border-radius: 6px;">
            Back to Top
        </button>
    </div>
</div>

<!-- Smooth Scroll JavaScript -->
<script>
function scrollToTop() {
    document.getElementById('reviews-top').scrollIntoView({ 
        behavior: 'smooth' 
    });
}
</script>

</div>
</body>
</html>