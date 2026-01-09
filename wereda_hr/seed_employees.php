<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../db.php';

$conn = getDBConnection();

// Check if we have any Woreda 1 employees
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE '%Woreda 1%'");
$row = $result->fetch_assoc();

echo "<h1>Wereda HR - Employee Data Check</h1>";
echo "<p>Current Woreda 1 employees: <strong>{$row['count']}</strong></p>";

if ($row['count'] < 5) {
    echo "<p>Adding sample employees...</p>";
    
    $sampleEmployees = [
        ['Dr. Ahmed', 'Yusuf', 'Male', 'Doctor', 'Medical', 'Kebele 01', '0911234567', '45000'],
        ['Nurse Sara', 'Mohammed', 'Female', 'Senior Nurse', 'Nursing', 'Kebele 02', '0922345678', '28000'],
        ['Dr. Fatuma', 'Hassan', 'Female', 'Pediatrician', 'Pediatrics', 'Kebele 01', '0933456789', '48000'],
        ['Technician Abdi', 'Ali', 'Male', 'Lab Technician', 'Laboratory', 'Kebele 03', '0944567890', '22000'],
        ['Pharmacist Amina', 'Ibrahim', 'Female', 'Chief Pharmacist', 'Pharmacy', 'Woreda Office', '0955678901', '32000'],
        ['Nurse Kedir', 'Abdullahi', 'Male', 'Nurse', 'Emergency', 'Kebele 02', '0966789012', '26000'],
        ['Dr. Hawa', 'Osman', 'Female', 'Surgeon', 'Surgery', 'Woreda Office', '0977890123', '52000'],
        ['Admin Yonas', 'Tesfaye', 'Male', 'HR Officer', 'Administration', 'Woreda Office', '0988901234', '24000']
    ];
    
    $stmt = $conn->prepare("INSERT INTO employees (
        employee_id, first_name, last_name, gender, position, department_assigned,
        working_kebele, working_woreda, woreda, phone, salary, status, join_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
    
    $added = 0;
    foreach ($sampleEmployees as $emp) {
        $empId = 'WRD' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $woreda = 'Woreda 1';
        
        $stmt->bind_param('ssssssssssd',
            $empId,
            $emp[0], // first_name
            $emp[1], // last_name
            $emp[2], // gender
            $emp[3], // position
            $emp[4], // department
            $emp[5], // working_kebele
            $woreda, // working_woreda
            $woreda, // woreda
            $emp[6], // phone
            $emp[7]  // salary
        );
        
        if ($stmt->execute()) {
            $added++;
            echo "<p>✅ Added: {$emp[0]} {$emp[1]} - {$emp[3]} at {$emp[5]}</p>";
        }
    }
    
    echo "<h2>Summary</h2>";
    echo "<p>Successfully added <strong>$added</strong> employees to Woreda 1!</p>";
} else {
    echo "<p>✅ Database already has sufficient test data.</p>";
}

echo "<hr>";
echo "<h3>Current Employees List:</h3>";
$employees = $conn->query("SELECT employee_id, first_name, last_name, position, working_kebele, status FROM employees WHERE woreda LIKE '%Woreda 1%' ORDER BY working_kebele, first_name LIMIT 20");

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Location</th><th>Status</th></tr>";

while ($emp = $employees->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$emp['employee_id']}</td>";
    echo "<td>{$emp['first_name']} {$emp['last_name']}</td>";
    echo "<td>{$emp['position']}</td>";
    echo "<td>{$emp['working_kebele']}</td>";
    echo "<td><span style='color: " . ($emp['status'] == 'active' ? 'green' : 'red') . "'>{$emp['status']}</span></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><a href='wereda_hr_dashboard.php'>Go to Dashboard</a> | <a href='wereda_hr_employee.php'>Go to Employee Management</a></p>";
?>
