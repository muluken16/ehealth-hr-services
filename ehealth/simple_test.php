<?php
// Very simple test
require_once 'db.php';

$conn = getDBConnection();
echo "Database connected successfully<br>";

$result = $conn->query("SHOW TABLES LIKE 'employees'");
if ($result->num_rows > 0) {
    echo "Employees table exists<br>";
} else {
    echo "Employees table does NOT exist<br>";
}

$conn->close();
?>