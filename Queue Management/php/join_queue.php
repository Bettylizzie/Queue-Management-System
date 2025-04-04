<?php
// Include the database connection file
require_once('db_connect.php');

// Set response header for JSON output
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false];

try {
    // Check if request method is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Invalid request method. Use POST.');
    }

    // Check if required fields are provided
    if (empty($_POST['name']) || empty($_POST['phone'])) {
        throw new Exception('Both name and phone are required.');
    }

    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    // Validate name (allow letters, spaces, and basic punctuation)
    if (!preg_match('/^[\p{L}\s\'-]{2,50}$/u', $name)) {
        throw new Exception('Invalid name format. Only letters, spaces, hyphens, and apostrophes are allowed.');
    }

    // Validate phone number (international format)
    $cleanedPhone = preg_replace('/[^0-9+]/', '', $phone);
    if (!preg_match('/^\+?[0-9]{8,15}$/', $cleanedPhone)) {
        throw new Exception('Invalid phone number format. Please enter a valid international number.');
    }

    // Prepare SQL statement with prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO queue (name, phone, status, created_at) VALUES (?, ?, 'waiting', NOW())");
    if (!$stmt) {
        throw new Exception('Database preparation error: ' . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("ss", $name, $cleanedPhone);
    if (!$stmt->execute()) {
        throw new Exception('Database execution error: ' . $stmt->error);
    }

    // Check if row was inserted
    if ($stmt->affected_rows === 1) {
        $response = [
            'success' => true,
            'message' => 'Successfully joined the queue!',
            'queue_id' => $stmt->insert_id
        ];
    } else {
        throw new Exception('Failed to join the queue. Please try again.');
    }

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(400); // Bad request for client errors
}

// Close the database connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>