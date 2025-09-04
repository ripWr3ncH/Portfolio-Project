<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Get all active education records
    $stmt = $pdo->prepare("SELECT id, title, institution, start_date, end_date, description, highlights, display_order FROM education WHERE status = 'active' ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $education = $stmt->fetchAll();
    
    // Format the response
    $response = [
        'success' => true,
        'data' => $education,
        'count' => count($education)
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error occurred',
        'count' => 0,
        'data' => []
    ];
    
    echo json_encode($response);
}
?>
