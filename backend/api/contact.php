<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/email_phpmailer.php';

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Handle contact form submission
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($input['name']) || empty($input['email']) || empty($input['message'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Name, email, and message are required fields.'
            ]);
            exit;
        }
        
        // Sanitize and validate email
        $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Please provide a valid email address.'
            ]);
            exit;
        }
        
        // Sanitize input data
        $name = trim($input['name']);
        $subject = isset($input['subject']) ? trim($input['subject']) : null;
        $message = trim($input['message']);
        
        // Get client information
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Basic spam detection
        $is_spam = false;
        $spam_keywords = ['casino', 'viagra', 'loan', 'crypto', 'bitcoin', 'investment', 'forex'];
        $message_lower = strtolower($message . ' ' . $subject);
        foreach ($spam_keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                $is_spam = true;
                break;
            }
        }
        
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, is_spam) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $name,
            $email,
            $subject,
            $message,
            $ip_address,
            $user_agent,
            $is_spam
        ]);
        
        if ($result) {
            // Send email notification to admin using PHPMailer
            $emailResult = sendEmailNotification($name, $email, $subject, $message);
            
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your message! I will get back to you soon.',
                'id' => $pdo->lastInsertId(),
                'email_sent' => $emailResult['success']
            ]);
        } else {
            throw new Exception('Failed to save message');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Sorry, there was an error sending your message. Please try again later.',
            'debug' => $e->getMessage()
        ]);
    }
    
} elseif ($method === 'GET') {
    // Handle admin panel requests to get messages
    try {
        // Simple authentication check (you may want to improve this)
        session_start();
        if (!isset($_SESSION['admin_logged_in'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized access'
            ]);
            exit;
        }
        
        // Get filter parameters
        $status = $_GET['status'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = (int)($_GET['offset'] ?? 0);
        
        // Build query based on filters
        $where = "WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get messages
        $stmt = $pdo->prepare("
            SELECT * FROM contact_messages 
            $where 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $messages,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to retrieve messages',
            'debug' => $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?>
