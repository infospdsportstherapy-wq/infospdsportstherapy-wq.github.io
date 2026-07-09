<?php
/**
 * Email Template: Review Rejected Notification
 * 
 * Variables:
 * - $reviewerName: Name of the reviewer
 * - $siteName: Website name
 * - $siteUrl: URL to the website
 * - $reason: Optional reason for rejection
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ef4444; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .icon { font-size: 48px; margin: 10px 0; }
        .reason-box { background: #fff3cd; padding: 15px; margin: 15px 0; border-left: 4px solid #ef4444; border-radius: 3px; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">ℹ</div>
            <h2>Review Update</h2>
        </div>
        
        <div class="content">
            <h3>Hello <?php echo htmlspecialchars($reviewerName); ?>,</h3>
            
            <p>Thank you for submitting a review to <?php echo htmlspecialchars($siteName ?? 'SPD Sports Therapy'); ?>.</p>
            
            <p>Unfortunately, your review could not be approved at this time.</p>
            
            <?php if (!empty($reason)): ?>
            <div class="reason-box">
                <strong>Reason:</strong>
                <p><?php echo nl2br(htmlspecialchars($reason)); ?></p>
            </div>
            <?php endif; ?>
            
            <p>We appreciate your feedback and welcome you to submit another review if you'd like. Please ensure your review meets our guidelines:</p>
            <ul>
                <li>Review must be at least 10 characters long</li>
                <li>Rating must be between 3 and 5 stars</li>
                <li>Reviews should be genuine and respectful</li>
                <li>No spam or promotional content</li>
            </ul>
            
            <p>If you believe this rejection is in error or have questions, please contact us at 
            <a href="mailto:info.spdsportstherapy@gmail.com">info.spdsportstherapy@gmail.com</a></p>
            
            <p>Best regards,<br>
            <strong><?php echo htmlspecialchars($siteName ?? 'SPD Sports Therapy'); ?> Team</strong></p>
            
            <div class="footer">
                <p>© <?php echo date('Y'); ?> SPD Sports Therapy. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
