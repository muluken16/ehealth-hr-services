<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Fixed Employee Insert</h2>";

// Simulate form submission
$_POST = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'middle_name' => 'Michael',
    'gender' => 'male',
    'date_of_birth' => '1990-05-15',
    'email' => 'john.doe@example.com',
    'position' => 'Software Developer',
    'religion' => 'Christianity',
    'citizenship' => 'Ethiopia',
    'region' => 'Amhara',
    'zone' => 'North Gondar',
    'woreda' => 'Gondar',
    'kebele' => 'Kebele 01',
    'education_level' => 'bachelor',
    'primary_school' => 'ABC Primary School',
    'secondary_school' => 'XYZ Secondary School',
    'college' => 'Tech College',
    'university' => 'University of Technology',
    'department_assigned' => 'information_technology',
    'bank_name' => 'commercial_bank',
    'bank_account' => '1234567890',
    'job_level' => 'mid',
    'marital_status' => 'single',
    'warranty_status' => 'no',
    'criminal_status' => 'no',
    'fin_id' => 'FIN123456',
    'loan_status' => 'no',
    'leave_request' => 'no',
    'salary' => '50000',
    'employment_type' => 'full-time',
    'status' => 'active',
    'address' => '123 Main Street, Gondar',
    'emergency_contact' => 'Jane Doe - 0911234567'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h3>📝 Test Data:</h3>";
echo "<pre>";
foreach ($_POST as $key => $value) {
    echo "$key: $value\n";
}
echo "</pre>";

echo "<h3>🔄 Processing...</h3>";

// Start session
session_start();
$_SESSION['user_name'] = 'Test User';
$_SESSION['role'] = 'kebele_hr';

// Connect to database
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test the insertion logic
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

    echo "✅ Generated Employee ID: $employee_id<br>";

    // Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $citizenship = $_POST['citizenship'] ?? '';
    $other_citizenship = trim($_POST['other_citizenship'] ?? '');
    $region = $_POST['region'] ?? '';
    $zone = $_POST['zone'] ?? '';
    $woreda = $_POST['woreda'] ?? '';
    $kebele = $_POST['kebele'] ?? '';
    $education_level = $_POST['education_level'] ?? '';
    $primary_school = $_POST['primary_school'] ?? '';
    $secondary_school = $_POST['secondary_school'] ?? '';
    $college = $_POST['college'] ?? '';
    $university = $_POST['university'] ?? '';
    $department = $_POST['department'] ?? '';
    $other_department = $_POST['other_department'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $bank_account = $_POST['bank_account'] ?? '';
    $job_level = $_POST['job_level'] ?? '';
    $other_job_level = $_POST['other_job_level'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $other_marital_status = $_POST['other_marital_status'] ?? '';
    $warranty_status = $_POST['warranty_status'] ?? '';
    $person_name = $_POST['person_name'] ?? '';
    $warranty_woreda = $_POST['warranty_woreda'] ?? '';
    $warranty_kebele = $_POST['warranty_kebele'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $warranty_type = $_POST['warranty_type'] ?? '';
    $scan_file = '';
    $criminal_status = $_POST['criminal_status'] ?? '';
    $criminal_file = '';
    $fin_id = $_POST['fin_id'] ?? '';
    $fin_scan = '';
    $loan_status = $_POST['loan_status'] ?? '';
    $loan_file = '';
    $leave_request = $_POST['leave_request'] ?? '';
    $leave_document = '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $department_assigned = $_POST['department_assigned'] ?? '';
    $position = $_POST['position'] ?? '';
    $join_date = $_POST['join_date'] ?? date('Y-m-d');
    $salary = $_POST['salary'] ?? '';
    $employment_type = $_POST['employment_type'] ?? 'full-time';
    $status = $_POST['status'] ?? 'active';
    $address = $_POST['address'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $documents = [];

    echo "✅ Form data processed<br>";

    // Insert into database
    $conn->begin_transaction();
    
    $sql = "INSERT INTO employees (
        employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion,
        citizenship, other_citizenship, region, zone, woreda, kebele, education_level,
        primary_school, secondary_school, college, university, department, other_department,
        bank_name, bank_account, job_level, other_job_level,
        marital_status, other_marital_status, warranty_status, person_name,
        warranty_woreda, warranty_kebele, phone, warranty_type, scan_file,
        criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file,
        leave_request, leave_document, email, phone_number, department_assigned,
        position, join_date, salary, employment_type, status, address,
        emergency_contact, documents, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $documents_json = json_encode($documents);
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssssssssssssssssssss",
        $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $religion,
        $citizenship, $other_citizenship, $region, $zone, $woreda, $kebele, $education_level,
        $primary_school, $secondary_school, $college, $university, $department, $other_department,
        $bank_name, $bank_account, $job_level, $other_job_level,
        $marital_status, $other_marital_status, $warranty_status, $person_name,
        $warranty_woreda, $warranty_kebele, $phone, $warranty_type, $scan_file,
        $criminal_status, $criminal_file, $fin_id, $fin_scan, $loan_status, $loan_file,
        $leave_request, $leave_document, $email, $phone_number, $department_assigned,
        $position, $join_date, $salary, $employment_type, $status, $address,
        $emergency_contact, $documents_json, $_SESSION['user_name']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $conn->commit();
    $stmt->close();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "✅ Employee added successfully!<br>";
    echo "Employee ID: $employee_id<br>";
    echo "Name: $first_name $last_name<br>";
    echo "</div>";
    
    // Verify the record
    $verify = $conn->query("SELECT * FROM employees WHERE employee_id = '$employee_id'");
    if ($verify && $verify->num_rows > 0) {
        echo "✅ Record verified in database<br>";
        $row = $verify->fetch_assoc();
        echo "<h4>📋 Inserted Record:</h4>";
        echo "<pre>";
        foreach ($row as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

$conn->close();

echo "<h3>🔗 Navigation</h3>";
echo "<a href='kebele_hr/add_employee.php'>Try Real Form</a> | ";
echo "<a href='view_employees.php'>View All Employees</a>";
?>