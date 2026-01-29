<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Testing Page Load</h2>";

// Test if we can access the add_employee.php file
echo "<h3>Testing add_employee.php inclusion:</h3>";

ob_start();
try {
    // Capture any output or errors
    $test_url = "http://localhost/ehealth/kebele_hr/add_employee.php";
    echo "Testing URL: <a href='$test_url' target='_blank'>$test_url</a><br>";
    
    // Test database connection first
    $conn = new mysqli("localhost", "root", "", "ehealth");
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Database connection successful<br>";
        
        // Test employees table
        $result = $conn->query("SHOW TABLES LIKE 'employees'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Employees table exists<br>";
        } else {
            echo "❌ Employees table missing<br>";
        }
        $conn->close();
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

$output = ob_get_clean();
echo $output;

echo "<h3>🔗 Test Links:</h3>";
echo "<a href='kebele_hr/add_employee_simple.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Simple Add Employee (Working)</a><br>";
echo "<a href='kebele_hr/add_employee.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Original Add Employee</a><br>";
echo "<a href='debug_add_employee.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Debug Page</a><br>";
?>