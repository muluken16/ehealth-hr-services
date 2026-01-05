<?php
session_start();
// Temporarily disable session check for testing
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
//     header('Location: ../index.html');
//     exit();
// }

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ehealth";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

function handleFileUpload($fieldName, $uploadDir, $employeeId) {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] == 0) {
        $file = $_FILES[$fieldName];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = $employeeId . '_' . $fieldName . '_' . time() . '.' . $ext;
        $path = $uploadDir . $newName;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $newName;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug: Log that we received the request
        error_log("Add employee request received");

        // Test if employees table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'employees'");
        if ($tableCheck->num_rows == 0) {
            throw new Exception("Employees table does not exist");
        }

        // Generate employee ID
        $employee_id = 'HF-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Get form data
        $first_name = trim($_POST['firstName'] ?? '');
        $middle_name = trim($_POST['middleName'] ?? '') ?: null;
        $last_name = trim($_POST['lastName'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $date_of_birth = $_POST['dateOfBirth'] ?? '';
        $religion = $_POST['religion'] ?? '';
        $citizenship = $_POST['citizenship'] ?? '';
        $other_citizenship = trim($_POST['otherCitizenship'] ?? '') ?: null;
        $region = $_POST['region'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $woreda = $_POST['woreda'] ?? '';
        $kebele = $_POST['kebele'] ?? '';
        $education_level = $_POST['educationLevel'] ?? '';
        $primary_school = trim($_POST['primarySchool'] ?? '') ?: null;
        $secondary_school = trim($_POST['secondarySchool'] ?? '') ?: null;
        $college = trim($_POST['college'] ?? '') ?: null;
        $university = trim($_POST['university'] ?? '') ?: null;
        $department = $_POST['department'] ?? '';
        $other_department = trim($_POST['otherDepartment'] ?? '') ?: null;
        $bank_name = $_POST['bankName'] ?? '';
        $bank_account = trim($_POST['bankAccount'] ?? '') ?: null;
        $job_level = $_POST['jobLevel'] ?? '';
        $other_job_level = trim($_POST['otherJobLevel'] ?? '') ?: null;
        $marital_status = $_POST['maritalStatus'] ?? '';
        $other_marital_status = trim($_POST['otherMaritalStatus'] ?? '') ?: null;
        $warranty_status = $_POST['warranty_status'] ?? '';
        $person_name = trim($_POST['person_name'] ?? '') ?: null;
        $warranty_woreda = trim($_POST['warranty_woreda'] ?? '') ?: null;
        $warranty_kebele = trim($_POST['warranty_kebele'] ?? '') ?: null;
        $phone = trim($_POST['phone'] ?? '') ?: null; // warranty_phone
        $warranty_type = $_POST['warranty_type'] ?? '';
        $criminal_status = $_POST['criminal_status'] ?? '';
        $fin_id = trim($_POST['fin_id'] ?? '') ?: null;
        $loan_status = $_POST['loan_status'] ?? '';
        $leave_request = $_POST['leaveRequest'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $phone_number = trim($_POST['phone'] ?? '') ?: null;
        $department_assigned = $_POST['department_assigned'] ?? '';
        $position = trim($_POST['position'] ?? '');
        $join_date = $_POST['joinDate'] ?? '';
        $salary = $_POST['salary'] ?? null;
        $employment_type = $_POST['employmentType'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $address = trim($_POST['address'] ?? '') ?: null;
        $emergency_contact = trim($_POST['emergencyContact'] ?? '') ?: null;
        $created_by = $_SESSION['user_id'] ?? null;

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($position)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields: first name, last name, email, position']);
            exit();
        }

        // Handle file uploads
        $upload_dir = '../uploads/employees/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $scan_file = handleFileUpload('scan_file', $upload_dir, $employee_id);
        $criminal_file = handleFileUpload('criminal_file', $upload_dir, $employee_id);
        $fin_scan = handleFileUpload('fin_scan', $upload_dir, $employee_id);
        $loan_file = handleFileUpload('loan_file', $upload_dir, $employee_id);
        $leave_document = handleFileUpload('leaveDocuments', $upload_dir, $employee_id);

        // Handle multiple documents
        $documents = [];
        if (isset($_FILES['employeeDocuments'])) {
            foreach ($_FILES['employeeDocuments']['name'] as $key => $name) {
                if ($_FILES['employeeDocuments']['error'][$key] == 0) {
                    $tmp_name = $_FILES['employeeDocuments']['tmp_name'][$key];
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_name = $employee_id . '_doc_' . $key . '_' . time() . '.' . $ext;
                    $path = $upload_dir . $new_name;
                    if (move_uploaded_file($tmp_name, $path)) {
                        $documents[] = $new_name;
                    }
                }
            }
        }
        $documents_json = json_encode($documents);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship,
            region, zone, woreda, kebele, education_level, primary_school, secondary_school, college, university,
            department, other_department, bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status,
            warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, scan_file, criminal_status, criminal_file,
            fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, email, phone_number, department_assigned,
            position, join_date, salary, employment_type, status, address, emergency_contact, documents, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssssssssssssssssssssssssssssssssssssssssssssss",
            $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $religion, $citizenship, $other_citizenship,
            $region, $zone, $woreda, $kebele, $education_level, $primary_school, $secondary_school, $college, $university,
            $department, $other_department, $bank_name, $bank_account, $job_level, $other_job_level, $marital_status, $other_marital_status,
            $warranty_status, $person_name, $warranty_woreda, $warranty_kebele, $phone, $warranty_type, $scan_file, $criminal_status, $criminal_file,
            $fin_id, $fin_scan, $loan_status, $loan_file, $leave_request, $leave_document, $email, $phone_number, $department_assigned,
            $position, $join_date, $salary, $employment_type, $status, $address, $emergency_contact, $documents_json, $created_by
        );

        if ($stmt->execute()) {
            error_log("Employee added successfully: " . $employee_id);
            echo json_encode(['success' => true, 'message' => 'Employee added successfully', 'employee_id' => $employee_id]);
        } else {
            error_log("Failed to add employee: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to add employee: ' . $stmt->error]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    $conn->close();
}
?>