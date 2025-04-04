<?php
require_once __DIR__ . '/vendor/autoload.php';
include('db_connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get customer details
    $sql = "SELECT * FROM queue WHERE id = $id";
    $result = $conn->query($sql);
    $customer = $result->fetch_assoc();

    if ($customer) {
        // Sending SMS using Twilio (or another SMS API)
        $sid = 'your_twilio_sid';
        $token = 'your_twilio_token';
        $from = 'your_twilio_phone_number';
        $to = $customer['phone'];

        // Twilio API request to send SMS
        $client = new \Twilio\Rest\Client($sid, $token);
        $message = $client->messages->create(
            $to,
            [
                'from' => $from,
                'body' => 'It\'s your turn in the queue. Please proceed to the counter.'
            ]
        );

        // Now delete the customer from the queue
        $deleteSql = "DELETE FROM queue WHERE id = $id";
        if ($conn->query($deleteSql) === TRUE) {
            echo json_encode(["success" => true, "name" => $customer['name']]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting customer"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Customer not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

$conn->close();

