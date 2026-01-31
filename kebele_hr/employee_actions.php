<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method !== 'POST' || empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$response = ['success' => false];

// Helper to handle file uploads
function handleFileUpload($file, $prefix, $empId)
{
    if (isset($file) && $file['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/employees/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        if (in_array($ext, $allowed)) {
            $fileName = $empId . '_' . $prefix . '_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $fileName)) {
                return $fileName;
            }
        }
    }
    return null;
}

// Common fields retrieval
function getPostData()
{
    return [
        'first_name' => $_POST['first_name'] ?? '',
        'middle_name' => $_POST['middle_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'religion' => $_POST['religion'] ?? '',
        'citizenship' => $_POST['citizenship'] ?? '',
        'other_citizenship' => $_POST['other_citizenship'] ?? '',
        'region' => $_POST['region'] ?? '',
        'zone' => $_POST['zone'] ?? '',
        'woreda' => $_POST['woreda'] ?? '',
        'kebele' => $_POST['kebele'] ?? '',
        'education_level' => $_POST['education_level'] ?? '',
        'primary_school' => $_POST['primary_school'] ?? '',
        'secondary_school' => $_POST['secondary_school'] ?? '',
        'college' => $_POST['college'] ?? '',
        'university' => $_POST['university'] ?? '',
        'department' => $_POST['department'] ?? '',
        'other_department' => $_POST['other_department'] ?? '',
        'bank_name' => $_POST['bank_name'] ?? '',
        'other_bank_name' => $_POST['other_bank_name'] ?? '',
        'bank_account' => $_POST['bank_account'] ?? '',
        'job_level' => $_POST['job_level'] ?? '',
        'other_job_level' => $_POST['other_job_level'] ?? '',
        'marital_status' => $_POST['marital_status'] ?? '',
        'other_marital_status' => $_POST['other_marital_status'] ?? '',
        'warranty_status' => $_POST['warranty_status'] ?? '',
        'person_name' => $_POST['person_name'] ?? '',
        'warranty_woreda' => $_POST['warranty_woreda'] ?? '',
        'warranty_kebele' => $_POST['warranty_kebele'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'warranty_type' => $_POST['warranty_type'] ?? '',
        'criminal_status' => (($_POST['criminal_status'] ?? 'no') === 'yes') ? 'yes' : 'no',
        'criminal_record_details' => $_POST['criminal_record_details'] ?? '',
        'fin_id' => $_POST['fin_id'] ?? '',
        'national_id_details' => $_POST['national_id_details'] ?? '',
        'credit_status' => $_POST['credit_status'] ?? 'good',
        'credit_details' => $_POST['credit_details'] ?? '',
        'loan_status' => (($_POST['credit_status'] ?? '') === 'active') ? 'yes' : 'no',
        'leave_request' => 'no', // Default for new employees
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'department_assigned' => (($_POST['department_assigned'] ?? '') === 'other') ? ($_POST['other_department_assigned'] ?? 'Other') : ($_POST['department_assigned'] ?? ''),
        'position' => $_POST['position'] ?? '',
        'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
        'salary' => $_POST['salary'] ? floatval($_POST['salary']) : 0,
        'employment_type' => (($_POST['employment_type'] ?? '') === 'other') ? ($_POST['other_employment_type'] ?? 'Other') : ($_POST['employment_type'] ?? 'full-time'),
        'status' => $_POST['status'] ?? 'active',
        'address' => $_POST['address'] ?? '',
        'emergency_contact' => $_POST['emergency_contact'] ?? '',
        'language' => $_POST['language'] ?? '',
        'other_language' => $_POST['other_language'] ?? '',
        'loan_lender' => $_POST['loan_lender'] ?? '',
        'loan_type' => $_POST['loan_type'] ?? '',
        'loan_amount' => $_POST['loan_amount'] ?? 0,
        'remaining_balance' => $_POST['remaining_balance'] ?? 0,
        'monthly_payment' => $_POST['monthly_payment'] ?? 0,
        'loan_end_date' => $_POST['loan_end_date'] ?? null,
        'loan_purpose' => $_POST['loan_purpose'] ?? '',
        'person_relationship' => $_POST['person_relationship'] ?? '',
        'warranty_amount' => $_POST['warranty_amount'] ?? 0,
        'warranty_start_date' => $_POST['warranty_start_date'] ?? null,
        'warranty_end_date' => $_POST['warranty_end_date'] ?? null,
        'warranty_court_status' => $_POST['warranty_court_status'] ?? '',
        'criminal_type' => $_POST['criminal_type'] ?? '',
        'criminal_date' => $_POST['criminal_date'] ?? null,
        'criminal_court' => $_POST['criminal_court'] ?? '',
        'criminal_sentence' => $_POST['criminal_sentence'] ?? '',
        'criminal_status_current' => $_POST['criminal_status_current'] ?? '',
    ];
}

switch ($action) {
    case 'add':
        $d = getPostData();
        $empIdStr = 'EMP-' . strtoupper(substr(md5(time()), 0, 6));
        $session_user = $_SESSION['user_name'] ?? 'System';

        // ... (File upload logic) ...
        function handleMultiUpload($fileField, $prefix, $empId)
        {
            $docs = [];
            if (isset($_FILES[$fileField])) {
                if (is_array($_FILES[$fileField]['name'])) {
                    foreach ($_FILES[$fileField]['name'] as $i => $name) {
                        if ($_FILES[$fileField]['error'][$i] == 0) {
                            $f = ['name' => $name, 'type' => $_FILES[$fileField]['type'][$i], 'tmp_name' => $_FILES[$fileField]['tmp_name'][$i], 'error' => 0, 'size' => $_FILES[$fileField]['size'][$i]];
                            $up = handleFileUpload($f, $prefix . '_' . $i, $empId);
                            if ($up) $docs[] = $up;
                        }
                    }
                } else if ($_FILES[$fileField]['error'] == 0) {
                    $up = handleFileUpload($_FILES[$fileField], $prefix, $empId);
                    if ($up) $docs[] = $up;
                }
            }
            return !empty($docs) ? json_encode($docs) : '';
        }

        $photo = handleFileUpload($_FILES['photo'] ?? null, 'photo', $empIdStr);
        $scan_file = handleMultiUpload('scan_file', 'scan', $empIdStr);
        $criminal_file = handleMultiUpload('criminal_file', 'criminal', $empIdStr);
        $fin_scan = handleMultiUpload('fin_scan', 'fin', $empIdStr);
        $loan_file = handleMultiUpload('loan_file', 'loan', $empIdStr);
        $leave_document = handleMultiUpload('leave_document', 'leave', $empIdStr);
        $education_file = handleMultiUpload('education_files', 'edu', $empIdStr);
        $employment_agreement = handleMultiUpload('employment_agreements', 'contract', $empIdStr);
        $documents_json = handleMultiUpload('documents', 'doc', $empIdStr);

        $working_woreda = $_POST['working_woreda'] ?? '';
        $working_kebele = $_POST['working_kebele'] ?? '';
        if (empty($working_woreda) && isset($_SESSION['woreda'])) $working_woreda = $_SESSION['woreda'];
        if (empty($working_kebele) && isset($_SESSION['kebele'])) $working_kebele = $_SESSION['kebele'];

        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship, region, zone, woreda, kebele, 
            education_level, primary_school, secondary_school, college, university, department, other_department, education_file,
            bank_name, other_bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status, 
            warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, 
            scan_file, criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, employment_agreement,
            email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address, emergency_contact, 
            language, other_language, documents, working_woreda, working_kebele, created_by, created_at,
            photo, criminal_record_details, national_id_details, credit_status, credit_details,
            loan_lender, loan_type, loan_amount, remaining_balance, monthly_payment, loan_end_date, loan_purpose,
            person_relationship, warranty_amount, warranty_start_date, warranty_end_date, warranty_court_status,
            criminal_type, criminal_date, criminal_court, criminal_sentence, criminal_status_current
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), ?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?,?,?, ?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => $conn->error]);
            exit;
        }

        // Count of types: 65 original + 17 new = 82
        $stmt->bind_param(
            str_repeat("s", 82),
            $empIdStr, $d['first_name'], $d['middle_name'], $d['last_name'], $d['gender'], $d['date_of_birth'], $d['religion'], $d['citizenship'], $d['other_citizenship'], $d['region'], $d['zone'], $d['woreda'], $d['kebele'], 
            $d['education_level'], $d['primary_school'], $d['secondary_school'], $d['college'], $d['university'], $d['department'], $d['other_department'], $education_file,
            $d['bank_name'], $d['other_bank_name'], $d['bank_account'], $d['job_level'], $d['other_job_level'], $d['marital_status'], $d['other_marital_status'], 
            $d['warranty_status'], $d['person_name'], $d['warranty_woreda'], $d['warranty_kebele'], $d['phone'], $d['warranty_type'], 
            $scan_file, $d['criminal_status'], $criminal_file, $d['fin_id'], $fin_scan, $d['loan_status'], $loan_file, $d['leave_request'], $leave_document, $employment_agreement,
            $d['email'], $d['phone_number'], $d['department_assigned'], $d['position'], $d['join_date'], $d['salary'], $d['employment_type'], $d['status'], $d['address'], $d['emergency_contact'], 
            $d['language'], $d['other_language'], $documents_json, $working_woreda, $working_kebele, $session_user,
            $photo, $d['criminal_record_details'], $d['national_id_details'], $d['credit_status'], $d['credit_details'],
            $d['loan_lender'], $d['loan_type'], $d['loan_amount'], $d['remaining_balance'], $d['monthly_payment'], $d['loan_end_date'], $d['loan_purpose'],
            $d['person_relationship'], $d['warranty_amount'], $d['warranty_start_date'], $d['warranty_end_date'], $d['warranty_court_status'],
            $d['criminal_type'], $d['criminal_date'], $d['criminal_court'], $d['criminal_sentence'], $d['criminal_status_current']
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Employee added successfully!';
        } else {
            $response['message'] = $stmt->error;
        }
        break;$stmt->close();
        break;

    case 'edit':
        $empId = $_POST['employee_id'] ?? '';
        if (!$empId) {
            $response['message'] = 'ID required';
            break;
        }

        $d = getPostData();
        // Filter out empty fields if you want to keep existing data? 
        // For a full edit form, empty fields usually mean clearing the data. 
        // But for partial updates, we might want to check. 
        // Let's assume the form sends all current values.

        // However, files are tricky. If no file sent, don't overwrite with empty string? 
        // Or check if a hidden "current_file" input exists?
        // For simplicity, we only update files if new ones are uploaded.

        $updates = [];
        $types = "";
        $params = [];

        // Define fields to update (exclude IDs, created_at, etc)
        $fields = [
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'date_of_birth',
            'religion',
            'citizenship',
            'other_citizenship',
            'region',
            'zone',
            'woreda',
            'kebele',
            'education_level',
            'primary_school',
            'secondary_school',
            'college',
            'university',
            'department',
            'other_department',
            'bank_name',
            'other_bank_name',
            'bank_account',
            'job_level',
            'other_job_level',
            'marital_status',
            'other_marital_status',
            'warranty_status',
            'person_name',
            'warranty_woreda',
            'warranty_kebele',
            'phone',
            'warranty_type',
            'criminal_status',
            'loan_status',
            'email',
            'phone_number',
            'department_assigned',
            'position',
            'join_date',
            'salary',
            'employment_type',
            'status',
            'address',
            'emergency_contact',
            'language',
            'other_language',
            'credit_status',
            'credit_details',
            'criminal_record_details',
            'national_id_details',
            'loan_lender',
            'loan_type',
            'loan_amount',
            'remaining_balance',
            'monthly_payment',
            'loan_end_date',
            'loan_purpose',
            'person_relationship',
            'warranty_amount',
            'warranty_start_date',
            'warranty_end_date',
            'warranty_court_status',
            'criminal_type',
            'criminal_date',
            'criminal_court',
            'criminal_sentence',
            'criminal_status_current'
        ];

        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $updates[] = "$f = ?";
                $types .= "s";
                $params[] = $_POST[$f];
            }
        }

        // Working Location
        if (isset($_POST['working_woreda'])) {
            $updates[] = "working_woreda=?";
            $types .= "s";
            $params[] = $_POST['working_woreda'];
        }
        if (isset($_POST['working_kebele'])) {
            $updates[] = "working_kebele=?";
            $types .= "s";
            $params[] = $_POST['working_kebele'];
        }

        // Handle File Updates (Only if new file uploaded)
        $file_fields = [
            'scan_file' => 'scan',
            'criminal_file' => 'criminal',
            'fin_scan' => 'fin',
            'loan_file' => 'loan',
            'leave_document' => 'leave',
            'photo' => 'photo'
        ];

        foreach ($file_fields as $post_key => $prefix) {
            if (isset($_FILES[$post_key]) && $_FILES[$post_key]['error'] == 0) {
                $new_file = handleFileUpload($_FILES[$post_key], $prefix, $empId);
                if ($new_file) {
                    $updates[] = "$post_key = ?";
                    $types .= "s";
                    $params[] = $new_file;
                }
            }
        }

        // Handle Multi Files (Education)
        if (isset($_FILES['education_files'])) {
            // Logic to append or replace? Usually replace in edit.
            // Simplified: if new files, replace.
            $edu_docs = [];
            $has_new = false;
            foreach ($_FILES['education_files']['name'] as $i => $name) {
                if ($_FILES['education_files']['error'][$i] == 0) {
                    $has_new = true;
                    $f = ['name' => $name, 'type' => $_FILES['education_files']['type'][$i], 'tmp_name' => $_FILES['education_files']['tmp_name'][$i], 'error' => 0, 'size' => $_FILES['education_files']['size'][$i]];
                    $up = handleFileUpload($f, 'edu_' . $i, $empId);
                    if ($up)
                        $edu_docs[] = $up;
                }
            }
            if ($has_new) {
                $updates[] = "education_file = ?";
                $types .= "s";
                $params[] = json_encode($edu_docs);
            }
        }

        // Handle Multi Files (Contract)
        if (isset($_FILES['employment_agreements'])) {
            $contract_docs = [];
            $has_new = false;
            foreach ($_FILES['employment_agreements']['name'] as $i => $name) {
                if ($_FILES['employment_agreements']['error'][$i] == 0) {
                    $has_new = true;
                    $f = ['name' => $name, 'type' => $_FILES['employment_agreements']['type'][$i], 'tmp_name' => $_FILES['employment_agreements']['tmp_name'][$i], 'error' => 0, 'size' => $_FILES['employment_agreements']['size'][$i]];
                    $up = handleFileUpload($f, 'contract_' . $i, $empId);
                    if ($up)
                        $contract_docs[] = $up;
                }
            }
            if ($has_new) {
                $updates[] = "employment_agreement = ?";
                $types .= "s";
                $params[] = json_encode($contract_docs);
            }
        }

        // Handle Multi Files (Additional Documents)
        if (isset($_FILES['documents'])) {
            $other_docs = [];
            $has_new = false;
            foreach ($_FILES['documents']['name'] as $i => $name) {
                if ($_FILES['documents']['error'][$i] == 0) {
                    $has_new = true;
                    $f = ['name' => $name, 'type' => $_FILES['documents']['type'][$i], 'tmp_name' => $_FILES['documents']['tmp_name'][$i], 'error' => 0, 'size' => $_FILES['documents']['size'][$i]];
                    $up = handleFileUpload($f, 'doc_' . $i, $empId);
                    if ($up)
                        $other_docs[] = $up;
                }
            }
            if ($has_new) {
                $updates[] = "documents = ?";
                $types .= "s";
                $params[] = json_encode($other_docs);
            }
        }

        // Execute Update
        if (!empty($updates)) {
            $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE employee_id = ?";
            $types .= "s";
            $params[] = $empId;

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Employee updated successfully';
            } else {
                $response['message'] = "DB Error: " . $stmt->error;
            }
        } else {
            $response['success'] = true; // No changes
            $response['message'] = 'No changes to update.';
        }
        break;

    case 'delete':
        $employee_id = $_POST['employee_id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id=?");
        $stmt->bind_param('s', $employee_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Employee deleted';
        } else {
            $response['message'] = $stmt->error;
        }
        $stmt->close();
        break;
    case 'export':
        // No JSON response for export, it's a file download
        $user_kebele = $_SESSION['kebele'] ?? 'Kebele 1';
        $search = $_GET['search'] ?? '';
        $dept = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? '';

        $sql = "SELECT employee_id, first_name, last_name, gender, department_assigned, position, salary, status, join_date, phone_number, email 
                FROM employees WHERE working_kebele = ?";
        $params = [$user_kebele];
        $types = "s";

        if (!empty($search)) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ?)";
            $search_param = "%$search%";
            array_push($params, $search_param, $search_param, $search_param);
            $types .= "sss";
        }
        if (!empty($dept)) {
            $sql .= " AND department_assigned = ?";
            $params[] = $dept;
            $types .= "s";
        }
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        // CSV Setup
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=employee_export_' . date('Ymd') . '.csv');
        $output = fopen('php://output', 'w');

        // Header Row
        fputcsv($output, ['Employee ID', 'First Name', 'Last Name', 'Gender', 'Department', 'Position', 'Salary', 'Status', 'Join Date', 'Phone', 'Email']);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
}

$conn->close();
echo json_encode($response);
?>