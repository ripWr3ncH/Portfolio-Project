<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    try {
        // Log the raw input for debugging
        $raw_input = file_get_contents('php://input');
        error_log("Raw input: " . $raw_input);
        
        $input = json_decode($raw_input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid JSON data: ' . json_last_error_msg()
            ]);
            exit;
        }
        
        error_log("Parsed input: " . print_r($input, true));
        
        // Validate required fields
        if (empty($input['name']) || empty($input['email']) || empty($input['message'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Name, email, and message are required fields.',
                'received_data' => $input
            ]);
            exit;
        }
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            trim($input['name']),
            trim($input['email']),
            isset($input['phone']) ? trim($input['phone']) : null,
            isset($input['subject']) ? trim($input['subject']) : null,
            trim($input['message']),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Message sent successfully!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save message to database.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Contact API Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Only POST method is allowed'
    ]);
}
?>
