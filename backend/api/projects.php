<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Get all active projects
    $stmt = $pdo->prepare("SELECT id, title, description, technologies, image_path, project_url, github_url, is_featured, display_order FROM projects WHERE status = 'active' ORDER BY display_order ASC");
    $stmt->execute();
    $projects = $stmt->fetchAll();
    
    // Format the response
    $response = [
        'success' => true,
        'data' => $projects,
        'count' => count($projects)
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
