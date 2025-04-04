<?php
include('db_connect.php');
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'stats' => []];

try {
    // Get queue data
    $result = $conn->query("SELECT id, name, phone, status, 
                          DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as formatted_date 
                          FROM queue ORDER BY created_at ASC");
    
    if ($result) {
        $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        throw new Exception('Query failed: ' . $conn->error);
    }

    // Get statistics
    $statsResult = $conn->query("SELECT 
                               SUM(status = 'waiting') as waiting,
                               SUM(status = 'called') as called,
                               SUM(status = 'completed') as completed
                               FROM queue");
    
    if ($statsResult) {
        $response['stats'] = $statsResult->fetch_assoc();
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log($e->getMessage());
}

echo json_encode($response);
$conn->close();
?>