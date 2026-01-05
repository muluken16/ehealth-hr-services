<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debugging Add Employee Page</h2>";

// Test 1: Basic PHP
echo "✅ PHP is working<br>";

// Test 2: Session
session_start();
echo "✅ Session started<br>";

// Test 3: Database connection
echo "<h3>Testing Database Connection:</h3>";
try {
    $conn = new mysqli("localhost", "root", "", "ehealth");
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Database connection successful<br>";
        
        // Test if employees table exists
        $result = $conn->query("SHOW TABLES LIKE 'employees'");
        if ($result->num_rows > 0) {
            echo "✅ Employees table exists<br>";
        } else {
            echo "❌ Employees table does not exist<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Include db.php
echo "<h3>Testing db.php include:</h3>";
try {
    include 'db.php';
    echo "✅ db.php included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including db.php: " . $e->getMessage() . "<br>";
}

// Test 5: File permissions
echo "<h3>Testing File Permissions:</h3>";
$upload_dir = 'uploads/employees/';
if (is_dir($upload_dir)) {
    echo "✅ Upload directory exists<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
} else {
    echo "❌ Upload directory does not exist<br>";
}

echo "<h3>🔗 Test Links:</h3>";
echo "<a href='kebele_hr/add_employee.php' target='_blank'>Test Add Employee Page</a><br>";
echo "<a href='view_employees.php' target='_blank'>Test View Employees</a><br>";
?>