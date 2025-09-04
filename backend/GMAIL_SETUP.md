# 📧 Gmail Setup for PHPMailer

## ⚡ Quick Setup Steps:

### 1. Enable Gmail App Password
1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on "Security" in the left sidebar
3. Under "Signing in to Google", click "2-Step Verification" (enable if not already)
4. After 2-Step is enabled, click "App passwords"
5. Select "Mail" and "Windows Computer" (or Custom name: "Portfolio Contact")
6. Copy the 16-character password (something like: `abcd efgh ijkl mnop`)

### 2. Update Email Configuration
Edit this file: `backend/config/email_phpmailer.php`

Find this line:
```php
define('SMTP_PASSWORD', 'your_gmail_app_password_here');
```

Replace with your app password:
```php
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // Your actual app password
```

### 3. Test Email
Create a test file to verify it's working:

```php
<?php
require_once 'config/email_phpmailer.php';

$result = testEmailConfig();
if ($result['success']) {
    echo "✅ Email setup is working!";
} else {
    echo "❌ Error: " . $result['message'];
}
?>
```

## 🎯 Important Notes:
- **App Password ≠ Gmail Password**: Use the 16-character app password, not your regular Gmail password
- **2-Step Verification Required**: Gmail requires 2-step verification to generate app passwords
- **Security**: Keep your app password private - don't commit it to version control
- **Backup**: Your contact form will work even if email fails - messages save to database

## 🚀 You're Done!
Once you add the app password, your contact form will send Gmail notifications automatically!
