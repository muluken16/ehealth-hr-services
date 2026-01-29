<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Employee API Endpoints</h2>";

// Test 1: Get all employees
echo "<h3>📋 Test 1: Get All Employees</h3>";
$employees_url = "http://localhost/ehealth/kebele_hr/get_kebele_hr_employees.php";

$context = stream_context_create([
    'http' => [
        'timeout' => 10
    ]
]);

$employees_response = file_get_contents($employees_url, false, $context);

if ($employees_response === false) {
    echo "❌ Failed to fetch employees<br>";
} else {
    $employees = json_decode($employees_response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Successfully fetched " . count($employees) . " employees<br>";
        
        if (count($employees) > 0) {
            echo "<h4>📊 Sample Employee Data:</h4>";
            $sample = $employees[0];
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($sample as $key => $value) {
                echo "<tr><td>$key</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
            
            // Test 2: Get employee detail
            echo "<h3>👤 Test 2: Get Employee Detail</h3>";
            $employee_id = $sample['employee_id'];
            $detail_url = "http://localhost/ehealth/kebele_hr/get_kebele_hr_employee_detail.php?id=" . urlencode($employee_id);
            
            $detail_response = file_get_contents($detail_url, false, $context);
            
            if ($detail_response === false) {
                echo "❌ Failed to fetch employee detail<br>";
            } else {
                $detail = json_decode($detail_response, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "✅ Successfully fetched detail for employee: $employee_id<br>";
                    echo "Detail contains " . count($detail) . " fields<br>";
                } else {
                    echo "❌ Invalid JSON in detail response: " . json_last_error_msg() . "<br>";
                }
            }
        } else {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
            echo "⚠️ No employees found in database. Add some employees first!";
            echo "</div>";
        }
    } else {
        echo "❌ Invalid JSON response: " . json_last_error_msg() . "<br>";
        echo "Raw response: " . htmlspecialchars($employees_response) . "<br>";
    }
}

echo "<h3>🔗 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='kebele_hr/hr-employees.html' target='_blank'>HR Employees Page</a></li>";
echo "<li><a href='kebele_hr/add_employee.php' target='_blank'>Add New Employee</a></li>";
echo "<li><a href='view_employees.php' target='_blank'>Simple Employee List</a></li>";
echo "</ul>";

echo "<h3>📝 API Endpoints:</h3>";
echo "<ul>";
echo "<li><strong>Get All Employees:</strong> <code>kebele_hr/get_kebele_hr_employees.php</code></li>";
echo "<li><strong>Get Employee Detail:</strong> <code>kebele_hr/get_kebele_hr_employee_detail.php?id=EMPLOYEE_ID</code></li>";
echo "</ul>";
?>