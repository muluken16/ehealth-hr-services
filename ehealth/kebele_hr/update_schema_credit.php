<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add credit_details column if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM employees LIKE 'credit_details'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE employees ADD COLUMN credit_details TEXT AFTER credit_status");
    echo "Added credit_details column.\n";
} else {
    echo "credit_details column already exists.\n";
}

$conn->close();
?>
