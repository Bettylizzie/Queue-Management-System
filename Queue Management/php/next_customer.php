<?php
require_once('db_connect.php');

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'customer' => null
];

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method. Use GET.');
    }

    // Validate customer ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid customer ID');
    }

    $customer_id = (int)$_GET['id'];

    // Start transaction
    $conn->begin_transaction();

    // Get and lock customer record
    $stmt = $conn->prepare("SELECT * FROM queue WHERE id = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        throw new Exception('Customer not found in queue');
    }

    // Update status to 'called' first (instead of immediate deletion)
    $update_stmt = $conn->prepare("UPDATE queue SET status = 'called', called_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $customer_id);
    $update_stmt->execute();
    
    if ($update_stmt->affected_rows !== 1) {
        throw new Exception('Failed to update customer status');
    }
    $update_stmt->close();

    // Commit transaction
    $conn->commit();

    // Prepare success response
    $response = [
        'success' => true,
        'message' => 'Customer successfully called',
        'customer' => [
            'id' => $customer['id'],
            'name' => htmlspecialchars($customer['name']),
            'phone' => htmlspecialchars($customer['phone']),
            'status' => 'called'
        ]
    ];

    // Optional: Send SMS notification
    if (isset($_GET['send_sms']) && $_GET['send_sms'] === 'true') {
        $sms_sent = sendSmsNotification($customer['phone'], $customer['name']);
        $response['sms_sent'] = $sms_sent;
    }

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->begin_transaction()) {
        $conn->rollback();
    }
    
    $response['message'] = $e->getMessage();
    http_response_code(400); // Bad request for client errors
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}

// Return JSON response
echo json_encode($response);

/**
 * Helper function to send SMS notification
 */
function sendSmsNotification($phone, $name) {
    // Implement your SMS provider integration here
    // This is a placeholder implementation
    
    try {
        /*
        // Example with Twilio:
        $sid = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $from = getenv('TWILIO_FROM');
        
        $client = new \Twilio\Rest\Client($sid, $token);
        $message = $client->messages->create(
            $phone,
            [
                'from' => $from,
                'body' => "Hello $name, it's your turn! Please proceed to counter."
            ]
        );
        return true;
        */
        
        // For now just log that we would send SMS
        error_log("SMS would be sent to $phone for customer $name");
        return true;
        
    } catch (Exception $e) {
        error_log("SMS sending failed: " . $e->getMessage());
        return false;
    }
}
?>