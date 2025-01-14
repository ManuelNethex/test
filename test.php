<?php

function insertRecord($conn, $data) {
    // Fields to store directly in the table
    $standardFields = ['name', 'email'];

    // Separate standard fields and additional data
    $standardData = [];
    $otherData = [];

    foreach ($data as $key => $value) {
        if (in_array($key, $standardFields)) {
            $standardData[$key] = $value;
        } else {
            $otherData[$key] = $value;
        }
    }

    // Encode additional fields as JSON
    $otherDataJson = json_encode($otherData);

    // Prepare SQL statement
    $sql = "INSERT INTO test_table (name, email, other_data) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param('sss', $standardData['name'], $standardData['email'], $otherDataJson);

    // Execute and check for errors
    if ($stmt->execute()) {
        echo "Record inserted successfully.\n";
    } else {
        echo "Error inserting record: " . $stmt->error . "\n";
    }

    $stmt->close();
}

function fetchRecord($conn, $id) {
    // Prepare SQL statement to fetch the record
    $sql = "SELECT name, email, other_data FROM test_table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param('i', $id);

    // Execute the query
    $stmt->execute();
    $stmt->bind_result($name, $email, $otherDataJson);
    $stmt->fetch();

    // Decode the JSON data
    $otherData = json_decode($otherDataJson, true);

    // Merge standard fields with additional data
    $record = [
        'name' => $name,
        'email' => $email,
    ] + $otherData;

    $stmt->close();

    return $record;
}

// Example usage
$conn = new mysqli('localhost', 'root', 'root', 'jsontest');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example data to insert
$data = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'type_subscription' => 'Premium',
    'date_subscription' => '2024-12-27',
    'cost_subscription' => 100.00,
];

// Insert the record
insertRecord($conn, $data);

// Fetch the record by ID
$id = 1; // Assuming the record has ID 1
$record = fetchRecord($conn, $id);
print_r($record);

$conn->close();
