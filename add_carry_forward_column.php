<?php
require_once 'db.php';
$conn = getDBConnection();

$sql = "ALTER TABLE leave_entitlements ADD COLUMN carry_forward_days INT DEFAULT 0 AFTER annual_leave_days";

if ($conn->query($sql)) {
    echo "Successfully added carry_forward_days column.";
} else {
    echo "Error or column already exists: " . $conn->error;
}

$conn->close();
?>