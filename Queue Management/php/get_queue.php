<?php
// Include database connection
require_once('db_connect.php');

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Initialize response array
$response = [
    'success' => false,
    'data' => [],
    'stats' => [],
    'error' => null
];

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get optional filters from query parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

    // Prepare base SQL query
    $sql = "SELECT id, name, phone, status, 
            DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as formatted_date 
            FROM queue";

    // Add status filter if provided
    if ($status_filter && in_array($status_filter, ['waiting', 'called', 'completed'])) {
        $sql .= " WHERE status = ?";
    }

    // Always order by creation date (oldest first)
    $sql .= " ORDER BY created_at ASC";

    // Add limit if provided
    if ($limit && $limit > 0) {
        $sql .= " LIMIT ?";
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Bind parameters if needed
    if ($status_filter && $limit) {
        $stmt->bind_param("si", $status_filter, $limit);
    } elseif ($status_filter) {
        $stmt->bind_param("s", $status_filter);
    } elseif ($limit) {
        $stmt->bind_param("i", $limit);
    }

    // Execute query
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    // Get results
    $result = $stmt->get_result();
    $queue = [];

    while ($row = $result->fetch_assoc()) {
        // Format phone number for display
        $row['phone_display'] = formatPhoneNumber($row['phone']);
        $queue[] = $row;
    }

    // Get queue statistics
    $stats_sql = "SELECT 
                 SUM(status = 'waiting') as waiting,
                 SUM(status = 'called') as called,
                 SUM(status = 'completed') as completed
                 FROM queue";
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();

    // Build successful response
    $response = [
        'success' => true,
        'data' => $queue,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(500); // Internal Server Error
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Helper function to format phone numbers for display
 */
function formatPhoneNumber($phone) {
    // Remove all non-digit characters
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    
    // Format as (XXX) XXX-XXXX for US numbers
    if (strlen($cleaned) === 10) {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $cleaned);
    }
    
    // Format international numbers with + prefix
    if (strlen($cleaned) > 10) {
        return '+' . $cleaned;
    }
    
    // Return original if doesn't match expected patterns
    return $phone;
}
?>