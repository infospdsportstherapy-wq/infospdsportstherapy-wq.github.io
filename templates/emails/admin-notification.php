<?php
/**
 * Email Template: New Review Notification for Admin
 * 
 * Variables:
 * - $reviewerName: Name of the reviewer
 * - $reviewerEmail: Email of the reviewer
 * - $activity: Sport/activity of the reviewer
 * - $rating: Review rating (3-5 stars)
 * - $reviewText: The review content
 * - $approveLink: Link to approve the review
 * - $rejectLink: Link to reject the review
 * - $siteName: Website name
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .review-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #2563eb; }
        .rating { color: #ffc107; font-size: 18px; margin: 10px 0; }
        .action-buttons { margin: 20px 0; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🎯 New Review Submission</h2>
            <p><?php echo htmlspecialchars($siteName ?? 'SPD Sports Therapy'); ?></p>
        </div>
        
        <div class="content">
            <h3>Review from <?php echo htmlspecialchars($reviewerName); ?></h3>
            
            <p><strong>Reviewer Email:</strong> <?php echo htmlspecialchars($reviewerEmail); ?></p>
            <p><strong>Activity:</strong> <?php echo htmlspecialchars($activity); ?></p>
            
            <div class="rating">
                <?php 
                for ($i = 0; $i < $rating; $i++) {
                    echo '★';
                }
                echo ' (' . $rating . '/5)';
                ?>
            </div>
            
            <div class="review-box">
                <p><?php echo nl2br(htmlspecialchars($reviewText)); ?></p>
            </div>
            
            <h4>What would you like to do?</h4>
            <div class="action-buttons">
                <a href="<?php echo htmlspecialchars($approveLink); ?>" class="btn btn-approve">✓ Approve Review</a>
                <a href="<?php echo htmlspecialchars($rejectLink); ?>" class="btn btn-reject">✗ Reject Review</a>
            </div>
            
            <p style="color: #666; font-size: 12px;">
                <strong>Note:</strong> These links expire in 24 hours. You can also manage reviews from your admin dashboard.
            </p>
            
            <div class="footer">
                <p>© <?php echo date('Y'); ?> SPD Sports Therapy. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
