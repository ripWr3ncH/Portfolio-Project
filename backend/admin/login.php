<?php
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        redirect('dashboard.php');
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portfolio Admin Panel</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(247, 147, 30, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .login-container {
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(42, 42, 42, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 3rem 2.5rem;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 
                        0 0 0 1px rgba(255, 107, 53, 0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header h1 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0.01em;
        }
        
        .form-group {
            margin-bottom: 1.75rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff6b35;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            transform: translateY(-1px);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            letter-spacing: -0.01em;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            filter: brightness(1.1);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ff4757;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .admin-info {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }
        
        .admin-info strong {
            color: #fff;
            font-weight: 600;
        }
        
        .admin-info em {
            color: rgba(255, 107, 53, 0.8);
            font-style: normal;
            font-weight: 500;
        }
        
        /* Responsive Login Page */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .login-container {
                padding: 2rem 1.5rem;
                max-width: 100%;
                margin: 2rem auto;
            }
            
            .login-header h1 {
                font-size: 1.75rem;
            }
            
            .form-group input {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem 1rem;
                margin: 1rem auto;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-header p {
                font-size: 0.9rem;
            }
            
            .admin-info {
                font-size: 0.8rem;
                padding: 1rem;
            }
        }
        
        @media (max-width: 320px) {
            .login-container {
                padding: 1rem 0.75rem;
                margin: 0.5rem auto;
            }
            
            .form-group input,
            .login-btn {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐Welcome Dewan</h1>
            <p>Portfolio Admin Panel</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Login to Admin Panel</button>
        </form>
        
        <div class="admin-info">
            <strong>Default Credentials:</strong><br>
            Username: admin<br>
            Password: admin123<br>
            <em>Please change these credentials in production!</em>
        </div>
    </div>
</body>
</html>
