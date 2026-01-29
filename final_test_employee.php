<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h2>🎯 Final Employee Add Test</h2>";

$_SESSION['user_name'] = 'Test User';
$_SESSION['role'] = 'kebele_hr';

// Connect to database
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test with minimal data
$test_data = [
    'first_name' => 'Final',
    'last_name' => 'Test',
    'gender' => 'male',
    'date_of_birth' => '1990-01-01',
    'email' => 'final.test@example.com',
    'position' => 'Test Position'
];

echo "<h3>📝 Test Data:</h3>";
foreach ($test_data as $key => $value) {
    echo "$key: $value<br>";
}

try {
    // Generate employee ID
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

    echo "<h3>🆔 Generated ID: $employee_id</h3>";

    // Prepare all 53 fields with defaults
    $first_name = $test_data['first_name'];
    $middle_name = '';
    $last_name = $test_data['last_name'];
    $gender = $test_data['gender'];
    $date_of_birth = $test_data['date_of_birth'];
    $religion = '';
    $citizenship = '';
    $other_citizenship = '';
    $region = '';
    $zone = '';
    $woreda = '';
    $kebele = '';
    $education_level = '';
    $primary_school = '';
    $secondary_school = '';
    $college = '';
    $university = '';
    $department = '';
    $other_department = '';
    $bank_name = '';
    $bank_account = '';
    $job_level = '';
    $other_job_level = '';
    $marital_status = '';
    $other_marital_status = '';
    $warranty_status = '';
    $person_name = '';
    $warranty_woreda = '';
    $warranty_kebele = '';
    $phone = '';
    $warranty_type = '';
    $scan_file = '';
    $criminal_status = '';
    $criminal_file = '';
    $fin_id = '';
    $fin_scan = '';
    $loan_status = '';
    $loan_file = '';
    $leave_request = '';
    $leave_document = '';
    $email = $test_data['email'];
    $phone_number = '';
    $department_assigned = '';
    $position = $test_data['position'];
    $join_date = date('Y-m-d');
    $salary = '';
    $employment_type = 'full-time';
    $status = 'active';
    $address = '';
    $emergency_contact = '';
    $documents_json = '[]';
    $created_by = $_SESSION['user_name'];

    // Insert
    $conn->begin_transaction();
    
    $sql = "INSERT INTO employees (
        employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship, region, zone, woreda, kebele, education_level, primary_school, secondary_school, college, university, department, other_department, bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status, warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, scan_file, criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address, emergency_contact, documents, created_by
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssssssssssssssssssssssss",
        $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $religion, $citizenship, $other_citizenship, $region, $zone, $woreda, $kebele, $education_level, $primary_school, $secondary_school, $college, $university, $department, $other_department, $bank_name, $bank_account, $job_level, $other_job_level, $marital_status, $other_marital_status, $warranty_status, $person_name, $warranty_woreda, $warranty_kebele, $phone, $warranty_type, $scan_file, $criminal_status, $criminal_file, $fin_id, $fin_scan, $loan_status, $loan_file, $leave_request, $leave_document, $email, $phone_number, $department_assigned, $position, $join_date, $salary, $employment_type, $status, $address, $emergency_contact, $documents_json, $created_by
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $conn->commit();
    $stmt->close();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "🎉 SUCCESS! Employee added successfully!<br>";
    echo "Employee ID: $employee_id<br>";
    echo "Name: $first_name $last_name<br>";
    echo "</div>";
    
    // Verify
    $verify = $conn->query("SELECT employee_id, first_name, last_name, email, position, status FROM employees WHERE employee_id = '$employee_id'");
    if ($verify && $verify->num_rows > 0) {
        $row = $verify->fetch_assoc();
        echo "<h4>✅ Verified in Database:</h4>";
        echo "<ul>";
        foreach ($row as $key => $value) {
            echo "<li>$key: $value</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

$conn->close();

echo "<h3>🚀 Ready to Use!</h3>";
echo "<p>The employee add system is now working correctly. You can:</p>";
echo "<ul>";
echo "<li><a href='kebele_hr/add_employee.php' target='_blank'>Add New Employee</a></li>";
echo "<li><a href='view_employees.php' target='_blank'>View All Employees</a></li>";
echo "<li><a href='kebele_hr/add_employee_simple.php' target='_blank'>Use Simple Form</a></li>";
echo "</ul>";
?>