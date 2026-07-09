<?php
/**
 * Email Template: Review Approved Notification
 * 
 * Variables:
 * - $reviewerName: Name of the reviewer
 * - $siteName: Website name
 * - $siteUrl: URL to the website
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .success-icon { font-size: 48px; margin: 10px 0; }
        .cta-button { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✓</div>
            <h2>Review Approved!</h2>
        </div>
        
        <div class="content">
            <h3>Hello <?php echo htmlspecialchars($reviewerName); ?>,</h3>
            
            <p>Great news! Your review has been approved and is now displayed on our website.</p>
            
            <p>Thank you for taking the time to share your experience with <?php echo htmlspecialchars($siteName ?? 'SPD Sports Therapy'); ?>. Your feedback helps us serve our clients better and helps others make informed decisions.</p>
            
            <div style="text-align: center;">
                <a href="<?php echo htmlspecialchars($siteUrl); ?>" class="cta-button">Visit Our Website</a>
            </div>
            
            <p>If you have any questions or would like to submit another review, feel free to contact us.</p>
            
            <p>Best regards,<br>
            <strong><?php echo htmlspecialchars($siteName ?? 'SPD Sports Therapy'); ?> Team</strong></p>
            
            <div class="footer">
                <p>© <?php echo date('Y'); ?> SPD Sports Therapy. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
