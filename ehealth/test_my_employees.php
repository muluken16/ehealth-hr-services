<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>🧪 Test My Employees Filter</h2>";

// Set the same user as in add_employee.php
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

echo "<h3>👤 Current User: " . $_SESSION['user_name'] . "</h3>";

// Connect to database
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check all employees in database
echo "<h3>📊 Database Statistics:</h3>";
$total_result = $conn->query("SELECT COUNT(*) as total FROM employees");
$total_row = $total_result->fetch_assoc();
echo "Total employees in database: " . $total_row['total'] . "<br>";

// Check employees by creator
$creator_result = $conn->query("SELECT created_by, COUNT(*) as count FROM employees GROUP BY created_by");
echo "<h4>Employees by Creator:</h4>";
echo "<ul>";
while ($row = $creator_result->fetch_assoc()) {
    $creator = $row['created_by'] ?? 'NULL';
    $count = $row['count'];
    $isCurrent = ($creator === $_SESSION['user_name']) ? ' <strong>(YOU)</strong>' : '';
    echo "<li>$creator: $count employees$isCurrent</li>";
}
echo "</ul>";

// Test the API endpoint
echo "<h3>🔗 Testing API Endpoint:</h3>";
$api_url = "http://localhost/ehealth/kebele_hr/get_kebele_hr_employees.php";
echo "<p>API URL: <a href='$api_url' target='_blank'>$api_url</a></p>";

// Test with curl or file_get_contents
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . session_name() . '=' . session_id()
    ]
]);

$api_response = file_get_contents($api_url, false, $context);
if ($api_response) {
    $data = json_decode($api_response, true);
    if ($data) {
        echo "<h4>✅ API Response:</h4>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        
        $employees = $data['employees'] ?? $data;
        if (is_array($employees)) {
            echo "<h4>📋 Your Employees (" . count($employees) . "):</h4>";
            if (count($employees) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Position</th><th>Created By</th><th>Created At</th></tr>";
                foreach ($employees as $emp) {
                    echo "<tr>";
                    echo "<td>" . $emp['employee_id'] . "</td>";
                    echo "<td>" . $emp['first_name'] . " " . $emp['last_name'] . "</td>";
                    echo "<td>" . ($emp['email'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($emp['position'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($emp['created_by'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($emp['created_at'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No employees found for your account.</p>";
            }
        }
    } else {
        echo "<p>❌ Failed to decode API response</p>";
        echo "<pre>Raw response: $api_response</pre>";
    }
} else {
    echo "<p>❌ Failed to fetch API response</p>";
}

$conn->close();

echo "<h3>🔗 Navigation:</h3>";
echo "<ul>";
echo "<li><a href='kebele_hr/hr-employees.html' target='_blank'>View My Employees Page</a></li>";
echo "<li><a href='kebele_hr/add_employee.php' target='_blank'>Add New Employee</a></li>";
echo "<li><a href='view_employees.php' target='_blank'>View All Employees (Alternative)</a></li>";
echo "</ul>";
?>