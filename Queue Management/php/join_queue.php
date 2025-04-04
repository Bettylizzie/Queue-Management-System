<?php
// Include the database connection file
include('db_connect.php');

// Set response header to JSON
header('Content-Type: application/json');

// Check if the form data is set and not empty
if (!empty($_POST['name']) && !empty($_POST['phone'])) {
    // Sanitize the inputs to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Insert the data into the database
    $query = "INSERT INTO queue (name, phone, status) VALUES ('$name', '$phone', 'waiting')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'You have successfully joined the queue!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Name and Phone are required']);
}

// Close the database connection
mysqli_close($conn);
?>
