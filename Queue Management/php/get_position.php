<?php
include('db_connect.php');
header('Content-Type: application/json');

$response = ['success' => false];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get position in queue (counting waiting customers before this one)
    $sql = "SELECT COUNT(*) as position FROM queue 
            WHERE status = 'waiting' AND created_at < 
                (SELECT created_at FROM queue WHERE id = $id)";
    
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $response = [
            'success' => true,
            'position' => $row['position'] + 1 // Add 1 because position starts at 1
        ];
    }
}

echo json_encode($response);
$conn->close();
?>