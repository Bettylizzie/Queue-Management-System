<?php
include 'db_connect.php';

// Fetch all customers in the queue
$result = $conn->query("SELECT * FROM queue ORDER BY created_at ASC");

$queue = [];
while ($row = $result->fetch_assoc()) {
    $queue[] = $row;
}

// Return JSON response
echo json_encode($queue);

$conn->close();
?>
