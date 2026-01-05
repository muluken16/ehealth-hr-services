<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Employee Add System Test</h2>";

// Test database connection
include 'db.php';
$conn = new mysqli("localhost", "root", "", "ehealth");

if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    exit();
}
echo "✅ Database connection successful<br>";

// Test employees table structure
$result = $conn->query("DESCRIBE employees");
if ($result) {
    echo "✅ Employees table exists<br>";
    echo "<h3>📋 Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Employees table not found: " . $conn->error . "<br>";
}

// Test uploads directory
$upload_dir = 'uploads/employees/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "✅ Created uploads directory: $upload_dir<br>";
    } else {
        echo "❌ Failed to create uploads directory: $upload_dir<br>";
    }
} else {
    echo "✅ Uploads directory exists: $upload_dir<br>";
}

// Test file permissions
if (is_writable($upload_dir)) {
    echo "✅ Uploads directory is writable<br>";
} else {
    echo "❌ Uploads directory is not writable<br>";
}

// Count existing employees
$result = $conn->query("SELECT COUNT(*) as count FROM employees");
if ($result) {
    $row = $result->fetch_assoc();
    echo "📊 Current employees in database: " . $row['count'] . "<br>";
} else {
    echo "❌ Error counting employees: " . $conn->error . "<br>";
}

// Test employee ID generation
function generateEmployeeId($conn) {
    do {
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "HF-{$year}-{$random}";
        
        $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
        $check_stmt->bind_param("s", $employee_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $check_stmt->close();
    } while ($result->num_rows > 0);
    
    return $employee_id;
}

$test_id = generateEmployeeId($conn);
echo "🆔 Generated test employee ID: $test_id<br>";

echo "<br><h3>🔗 Quick Links:</h3>";
echo "<a href='kebele_hr/add_employee.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>➕ Add Employee</a>";
echo "<a href='kebele_hr/hr-employees.html' style='display: inline-block; padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>👥 View Employees</a>";

$conn->close();
?>