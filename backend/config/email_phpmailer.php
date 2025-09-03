<?php
// PHPMailer Email Configuration
require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';
require_once __DIR__ . '/../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email Configuration
define('ADMIN_EMAIL', 'fortestingonly.0ki@gmail.com');
define('SITE_NAME', 'My Portfolio');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'fortestingonly.0ki@gmail.com');
// Note: You'll need to set up Gmail App Password and add it here
define('SMTP_PASSWORD', 'elpc kfks tlpw obim'); // Replace with actual app password

/**
 * Send email notification using PHPMailer
 */
function sendEmailNotification($name, $email, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_USERNAME, SITE_NAME);
        $mail->addAddress(ADMIN_EMAIL);
        $mail->addReplyTo($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Message: ' . $subject;
        
        // HTML Email Template
        $htmlBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #495057; }
                .value { background: white; padding: 10px; border-radius: 3px; border: 1px solid #ced4da; margin-top: 5px; }
                .footer { background: #6c757d; color: white; padding: 15px; text-align: center; border-radius: 0 0 5px 5px; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📧 New Contact Form Message</h2>
                    <p>You have received a new message from your portfolio website</p>
                </div>
                <div class='content'>
                    <div class='field'>
                        <div class='label'>👤 Name:</div>
                        <div class='value'>" . htmlspecialchars($name) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>📧 Email:</div>
                        <div class='value'>" . htmlspecialchars($email) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>📝 Subject:</div>
                        <div class='value'>" . htmlspecialchars($subject) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>💬 Message:</div>
                        <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                    </div>
                </div>
                <div class='footer'>
                    <p>Sent from " . SITE_NAME . " Contact Form | " . date('Y-m-d H:i:s') . "</p>
                    <p>Reply directly to this email to respond to " . htmlspecialchars($name) . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $htmlBody;
        
        // Alternative plain text version
        $mail->AltBody = "New Contact Form Message\n\n" .
                        "Name: $name\n" .
                        "Email: $email\n" .
                        "Subject: $subject\n" .
                        "Message: $message\n\n" .
                        "Sent from " . SITE_NAME . " Contact Form";
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Test email configuration
 */
function testEmailConfig() {
    return sendEmailNotification(
        'Test User',
        'test@example.com',
        'PHPMailer Test',
        'This is a test email to verify PHPMailer configuration is working correctly.'
    );
}
?>
