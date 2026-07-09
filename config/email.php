<?php
/**
 * =============================================================================
 * SPD Sports Therapy - Email Service Configuration
 * =============================================================================
 * 
 * Email sending configuration and helper functions using PHPMailer
 * Handles SMTP setup, HTML template rendering, and error handling
 * 
 * Usage: 
 *   $result = sendEmail($to, $subject, $htmlBody);
 *   $html = renderEmailTemplate('approval-email', ['name' => 'John', 'link' => $link]);
 * =============================================================================
 */

// Load config if not already loaded
if (!function_exists('getConfig')) {
    require_once __DIR__ . '/config.php';
}

// Use PHPMailer if available
$usePhpMailer = file_exists(__DIR__ . '/../vendor/autoload.php');
if ($usePhpMailer) {
    require_once __DIR__ . '/../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
}

/**
 * Initialize PHPMailer instance
 * 
 * @return PHPMailer|false PHPMailer instance or false if not available
 */
function initializeMailer() {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    $emailConfig = getEmailConfig();
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $emailConfig['host'];
        $mail->Port = $emailConfig['port'];
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = $emailConfig['username'];
        $mail->Password = $emailConfig['password'];
        
        // Set from address
        $mail->setFrom(
            $emailConfig['from_email'],
            $emailConfig['from_name']
        );
        
        // Debug setting
        if (getConfig('MAIL_DEBUG')) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        
        return $mail;
    } catch (Exception $e) {
        error_log('PHPMailer initialization failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHPMailer
 * 
 * @param string|array $to Recipient email(s)
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string|null $textBody Plain text version (optional)
 * @param array $attachments File paths to attach (optional)
 * 
 * @return array ['success' => bool, 'message' => string, 'error' => string|null]
 */
function sendEmail($to, $subject, $htmlBody, $textBody = null, $attachments = []) {
    // If PHPMailer is not available, use fallback
    if (!function_exists('initializeMailer')) {
        return sendEmailFallback($to, $subject, $htmlBody, $textBody);
    }
    
    try {
        $mail = initializeMailer();
        
        if ($mail === false) {
            return sendEmailFallback($to, $subject, $htmlBody, $textBody);
        }
        
        // Set recipients
        if (is_array($to)) {
            foreach ($to as $address) {
                $mail->addAddress(trim($address));
            }
        } else {
            $mail->addAddress(trim($to));
        }
        
        // Set subject and body
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $htmlBody;
        
        if ($textBody) {
            $mail->AltBody = $textBody;
        }
        
        // Add attachments if provided
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $mail->addAttachment($file);
                }
            }
        }
        
        // Send email
        if ($mail->send()) {
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'error' => null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $mail->ErrorInfo
            ];
        }
        
    } catch (Exception $e) {
        error_log('Email sending exception: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Email sending error',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Fallback email sending using PHP mail() function
 * Used when PHPMailer is not available
 * 
 * @param string|array $to Recipient email(s)
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string|null $textBody Plain text version
 * 
 * @return array ['success' => bool, 'message' => string, 'error' => string|null]
 */
function sendEmailFallback($to, $subject, $htmlBody, $textBody = null) {
    $emailConfig = getEmailConfig();
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $emailConfig['from_name'] . " <" . $emailConfig['from_email'] . ">\r\n";
    $headers .= "Reply-To: " . $emailConfig['from_email'] . "\r\n";
    
    $to_addr = is_array($to) ? implode(', ', $to) : $to;
    
    $result = mail($to_addr, $subject, $htmlBody, $headers);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Email sent successfully (via PHP mail)',
            'error' => null
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to send email via PHP mail',
            'error' => 'mail() function returned false'
        ];
    }
}

/**
 * Render email template with variables
 * 
 * @param string $template Template name (e.g., 'approval-email')
 * @param array $variables Variables to inject into template
 * 
 * @return string HTML template rendered with variables
 */
function renderEmailTemplate($template, $variables = []) {
    $templatePath = __DIR__ . '/../templates/emails/' . $template . '.php';
    
    if (!file_exists($templatePath)) {
        return '<p>Template not found: ' . htmlspecialchars($template) . '</p>';
    }
    
    // Extract variables into current scope
    extract($variables);
    
    // Render template
    ob_start();
    include $templatePath;
    return ob_get_clean();
}

/**
 * Generate email token for one-time actions
 * 
 * @param int $reviewId Review ID
 * @param string $action Action type ('approve' or 'reject')
 * @param int $expirationHours Token expiration time in hours
 * 
 * @return string|false Generated token or false on error
 */
function generateEmailToken($reviewId, $action, $expirationHours = 24) {
    try {
        $pdo = getDbConnection();
        $token = bin2hex(random_bytes(32));
        
        $query = "
            INSERT INTO email_tokens (review_id, token, action, created_at, expires_at)
            VALUES (:review_id, :token, :action, NOW(), DATE_ADD(NOW(), INTERVAL :hours HOUR))
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':review_id' => $reviewId,
            ':token' => $token,
            ':action' => $action,
            ':hours' => $expirationHours
        ]);
        
        return $token;
    } catch (Exception $e) {
        error_log('Token generation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Verify and use email token
 * 
 * @param string $token Token to verify
 * @param string $expectedAction Expected action type
 * 
 * @return array|false Token data if valid, false if invalid or expired
 */
function verifyEmailToken($token, $expectedAction = null) {
    try {
        $pdo = getDbConnection();
        
        $query = "
            SELECT token_id, review_id, action, expires_at, is_used
            FROM email_tokens
            WHERE token = :token AND is_used = FALSE AND expires_at > NOW()
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return false;
        }
        
        if ($expectedAction && $result['action'] !== $expectedAction) {
            return false;
        }
        
        // Mark token as used
        $updateQuery = "
            UPDATE email_tokens
            SET is_used = TRUE, used_at = NOW()
            WHERE token_id = :token_id
        ";
        
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([':token_id' => $result['token_id']]);
        
        return $result;
    } catch (Exception $e) {
        error_log('Token verification failed: ' . $e->getMessage());
        return false;
    }
}

?>
