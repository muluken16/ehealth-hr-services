<?php
// Script to add working_kebele column to employees table
require_once '../db.php';
$conn = getDBConnection();

$sql = "ALTER TABLE employees ADD COLUMN IF NOT EXISTS working_kebele VARCHAR(100) DEFAULT NULL AFTER kebele";

if ($conn->query($sql) === TRUE) {
    echo "✅ Column 'working_kebele' added successfully to employees table.<br>";

    // Update existing records to set working_kebele = kebele where working_kebele is null
    $update_sql = "UPDATE employees SET working_kebele = kebele WHERE working_kebele IS NULL OR working_kebele = ''";
    if ($conn->query($update_sql)) {
        $affected = $conn->affected_rows;
        echo "✅ Updated $affected records to set working_kebele from kebele.<br>";
    }
} else {
    echo "❌ Error adding column: " . $conn->error . "<br>";
}

$conn->close();
?>