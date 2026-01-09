<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

if ($_SESSION['role'] != 'wereda_hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../db.php';
$conn = getDBConnection();

$action = $_GET['action'] ?? '';

// ADD EMPLOYEE
if ($action == 'add') {
    try {
        // Required fields
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $employee_id = $_POST['employee_id'] ?? 'EMP' . time();
        
        // Optional fields with defaults
        $middle_name = $_POST['middle_name'] ?? '';
        $gender = $_POST['gender'] ?? 'Male';
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $marital_status = $_POST['marital_status'] ?? 'Single';
        $language = $_POST['language'] ?? '';
        $religion = $_POST['religion'] ?? '';
        $citizenship = $_POST['citizenship'] ?? 'Ethiopian';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $region = $_POST['region'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $woreda = $_SESSION['woreda'] ?? 'Woreda 1';
        $kebele = $_POST['kebele'] ?? '';
        $working_woreda = $_SESSION['woreda'] ?? 'Woreda 1';
        $working_kebele = $_POST['working_kebele'] ?? '';
        $position = $_POST['position'] ?? 'Employee';
        $department_assigned = $_POST['department_assigned'] ?? '';
        $join_date = $_POST['join_date'] ?? date('Y-m-d');
        $salary = $_POST['salary'] ?? 0;
        $status = $_POST['status'] ?? 'active';
        $education_level = $_POST['education_level'] ?? '';
        $university = $_POST['university'] ?? '';
        $department = $_POST['department'] ?? '';
        $bank_name = $_POST['bank_name'] ?? '';
        $bank_account = $_POST['bank_account'] ?? '';
        $loan_status = $_POST['loan_status'] ?? 'no';
        $person_name = $_POST['person_name'] ?? '';
        $criminal_status = $_POST['criminal_status'] ?? 'no';
        
        $query = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth, marital_status,
            language, religion, citizenship, phone, email, address, region, zone, woreda, kebele,
            working_woreda, working_kebele, position, department_assigned, join_date, salary, status,
            education_level, university, department, bank_name, bank_account, loan_status, 
            person_name, criminal_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssssssssssssssssdssssssssss",
            $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $marital_status,
            $language, $religion, $citizenship, $phone, $email, $address, $region, $zone, $woreda, $kebele,
            $working_woreda, $working_kebele, $position, $department_assigned, $join_date, $salary, $status,
            $education_level, $university, $department, $bank_name, $bank_account, $loan_status,
            $person_name, $criminal_status
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee added successfully!', 'employee_id' => $employee_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// EDIT EMPLOYEE
else if ($action == 'edit') {
    try {
        $id = $_POST['id'] ?? 0;
        $employee_id = $_POST['employee_id'] ?? '';
        
        $updates = [];
        $types = '';
        $values = [];
        
        $fields = [
            'first_name' => 's', 'middle_name' => 's', 'last_name' => 's', 'gender' => 's',
            'date_of_birth' => 's', 'marital_status' => 's', 'language' => 's', 'religion' => 's',
            'citizenship' => 's', 'phone' => 's', 'email' => 's', 'address' => 's',
            'region' => 's', 'zone' => 's', 'woreda' => 's', 'kebele' => 's',
            'working_woreda' => 's', 'working_kebele' => 's', 'position' => 's',
            'department_assigned' => 's', 'join_date' => 's', 'salary' => 'd', 'status' => 's',
            'education_level' => 's', 'university' => 's', 'department' => 's',
            'bank_name' => 's', 'bank_account' => 's', 'loan_status' => 's',
            'person_name' => 's', 'criminal_status' => 's'
        ];
        
        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $updates[] = "$field = ?";
                $types .= $type;
                $values[] = $_POST[$field];
            }
        }
        
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit();
        }
        
        $query = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ? OR employee_id = ?";
        $types .= 'is';
        $values[] = $id;
        $values[] = $employee_id;
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// DELETE EMPLOYEE
else if ($action == 'delete') {
    try {
        $employee_id = $_POST['employee_id'] ?? '';
        
        if (empty($employee_id)) {
            echo json_encode(['success' => false, 'message' => 'Employee ID required']);
            exit();
        }
        
        // Verify employee belongs to this woreda
        $woreda_wildcard = "%" . ($_SESSION['woreda'] ?? 'Woreda 1') . "%";
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ? AND woreda LIKE ?");
        $stmt->bind_param('ss', $employee_id, $woreda_wildcard);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee not found or unauthorized']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
