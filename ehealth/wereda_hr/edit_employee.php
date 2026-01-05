<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Direct database connection (same as add_employee.php)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ehealth";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get employee ID
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;

    if ($employee_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
        $conn->close();
        exit();
    }

    // Build update query dynamically based on provided fields
    $update_fields = [];
    $params = [];
    $types = "";

    // Map form field names to database column names
    $field_mapping = [
        // Form field name => Database column name
        'firstName' => 'first_name',
        'middleName' => 'middle_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'phone' => 'phone_number', // Main phone number
        'gender' => 'gender',
        'dateOfBirth' => 'date_of_birth',
        'religion' => 'religion',
        'citizenship' => 'citizenship',
        'otherCitizenship' => 'other_citizenship',
        'region' => 'region',
        'zone' => 'zone',
        'woreda' => 'woreda',
        'kebele' => 'kebele',
        'educationLevel' => 'education_level',
        'primarySchool' => 'primary_school',
        'secondarySchool' => 'secondary_school',
        'college' => 'college',
        'university' => 'university',
        'department' => 'department',
        'otherDepartment' => 'other_department',
        'bankName' => 'bank_name',
        'bankAccount' => 'bank_account',
        'jobLevel' => 'job_level',
        'otherJobLevel' => 'other_job_level',
        'maritalStatus' => 'marital_status',
        'otherMaritalStatus' => 'other_marital_status',
        'warranty_status' => 'warranty_status',
        'person_name' => 'person_name',
        'warranty_woreda' => 'warranty_woreda',
        'warranty_kebele' => 'warranty_kebele',
        'warranty_phone' => 'phone', // Warranty phone stored in phone column
        'warranty_type' => 'warranty_type',
        'criminal_status' => 'criminal_status',
        'fin_id' => 'fin_id',
        'loan_status' => 'loan_status',
        'language' => 'language',
        'otherLanguage' => 'other_language',
        'leaveRequest' => 'leave_request',
        'department_assigned' => 'department_assigned',
        'position' => 'position',
        'joinDate' => 'join_date',
        'salary' => 'salary',
        'employmentType' => 'employment_type',
        'status' => 'status',
        'address' => 'address',
        'emergencyContact' => 'emergency_contact'
    ];

    // Field types for prepared statements
    $field_types = [
        'first_name' => 's', 'middle_name' => 's', 'last_name' => 's', 'email' => 's',
        'phone_number' => 's', 'gender' => 's', 'date_of_birth' => 's', 'religion' => 's',
        'citizenship' => 's', 'other_citizenship' => 's', 'region' => 's', 'zone' => 's',
        'woreda' => 's', 'kebele' => 's', 'education_level' => 's', 'primary_school' => 's',
        'secondary_school' => 's', 'college' => 's', 'university' => 's', 'department' => 's',
        'other_department' => 's', 'bank_name' => 's', 'bank_account' => 's', 'job_level' => 's',
        'other_job_level' => 's', 'marital_status' => 's', 'other_marital_status' => 's',
        'warranty_status' => 's', 'person_name' => 's', 'warranty_woreda' => 's',
        'warranty_kebele' => 's', 'phone' => 's', 'warranty_type' => 's', 'criminal_status' => 's',
        'fin_id' => 's', 'loan_status' => 's', 'language' => 's', 'other_language' => 's',
        'leave_request' => 's', 'department_assigned' => 's', 'position' => 's',
        'join_date' => 's', 'salary' => 'd', 'employment_type' => 's', 'status' => 's',
        'address' => 's', 'emergency_contact' => 's'
    ];

    // Process value mappings (e.g., religion values)
    $value_mappings = [
        'religion' => [
            'Orthodox' => 'christianity',
            'Islam' => 'islam',
            'Protestant' => 'protestant',
            'Judaism' => 'judaism',
            'Hinduism' => 'hinduism',
            'Other' => 'other'
        ]
    ];

    foreach ($field_mapping as $form_field => $db_field) {
        if (isset($_POST[$form_field]) && $_POST[$form_field] !== '') {
            $value = $_POST[$form_field];

            // Apply value mappings if needed
            if (isset($value_mappings[$db_field]) && isset($value_mappings[$db_field][$value])) {
                $value = $value_mappings[$db_field][$value];
            }

            // Special handling for empty strings - convert to NULL for optional fields
            if ($value === '' && in_array($db_field, ['middle_name', 'phone_number', 'other_citizenship', 'other_department',
                'bank_account', 'other_job_level', 'other_marital_status', 'person_name', 'warranty_woreda',
                'warranty_kebele', 'phone', 'warranty_type', 'fin_scan', 'loan_file', 'leave_document',
                'other_language', 'salary', 'employment_type', 'address', 'emergency_contact'])) {
                $value = null;
                $types .= 's'; // NULL is still a string type
            } else {
                $types .= $field_types[$db_field];
            }

            $update_fields[] = "$db_field = ?";
            $params[] = $value;
        }
    }

    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        $conn->close();
        exit();
    }

    // Handle file uploads
    $upload_dir = '../uploads/employees/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // File upload mappings: form field => database column
    $file_fields = [
        'scan_file' => 'scan_file',
        'criminal_file' => 'criminal_file',
        'fin_scan' => 'fin_scan',
        'loan_file' => 'loan_file',
        'leaveDocuments' => 'leave_document'
    ];

    foreach ($file_fields as $form_field => $db_field) {
        if (isset($_FILES[$form_field]) && $_FILES[$form_field]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$form_field];
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = $employee_id . '_' . $form_field . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $update_fields[] = "$db_field = ?";
                $params[] = $new_filename;
                $types .= "s";
            }
        }
    }

    // Handle multiple employee documents
    if (isset($_FILES['employeeDocuments'])) {
        $documents = [];
        $files = $_FILES['employeeDocuments'];

        // Handle both single and multiple files
        if (is_array($files['name'])) {
            // Multiple files
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $new_filename = $employee_id . '_doc_' . time() . '_' . $i . '.' . $file_extension;
                    $file_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                        $documents[] = $new_filename;
                    }
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $file_extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                $new_filename = $employee_id . '_doc_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;

                if (move_uploaded_file($files['tmp_name'], $file_path)) {
                    $documents[] = $new_filename;
                }
            }
        }

        if (!empty($documents)) {
            $update_fields[] = "documents = ?";
            $params[] = json_encode($documents);
            $types .= "s";
        }
    }

    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        $conn->close();
        exit();
    }

    // Add employee_id to params for WHERE clause
    $params[] = $employee_id;
    $types .= "i";

    $sql = "UPDATE employees SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Employee updated successfully',
            'employee_id' => $employee_id
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Edit employee error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>