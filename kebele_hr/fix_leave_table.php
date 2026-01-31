<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add carry_forward_days if it doesn't exist
$res = $conn->query("SHOW COLUMNS FROM leave_entitlements LIKE 'carry_forward_days'");
if ($res->num_rows == 0) {
    echo "Adding carry_forward_days column...\n";
    $sql = "ALTER TABLE leave_entitlements ADD COLUMN carry_forward_days INT(11) DEFAULT 0 AFTER annual_leave_days";
    if ($conn->query($sql)) {
        echo "Successfully added carry_forward_days.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} else {
    echo "Column 'carry_forward_days' already exists.\n";
}

$conn->close();
?>
