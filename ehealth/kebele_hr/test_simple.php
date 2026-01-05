<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>✅ PHP is working in kebele_hr directory!</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database
try {
    $conn = new mysqli("localhost", "root", "", "ehealth");
    if ($conn->connect_error) {
        echo "<p>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p>✅ Database connection successful</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='add_employee.php'>Test Original Add Employee</a></p>";
echo "<p><a href='add_employee_simple.php'>Test Simple Add Employee</a></p>";
?>