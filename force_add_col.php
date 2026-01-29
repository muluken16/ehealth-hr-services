<?php
require_once 'db.php';
$conn = getDBConnection();
$sql = "ALTER TABLE employees ADD COLUMN education_file TEXT AFTER other_department";
if ($conn->query($sql)) {
    echo "Successfully added education_file column!\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>