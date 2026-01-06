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
function handleFileUpload($file, $prefix, $empId) {
    if (isset($file) && $file['error'] == 0) {
        $upload_dir = dirname(__DIR__) . '/uploads/employees/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
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
function getPostData() {
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
        'kebele' => $_POST['kebele'] ?? '', // This might be overridden by session for the 'location' context, but let's keep it if it refers to residence
        'education_level' => $_POST['education_level'] ?? '',
        'primary_school' => $_POST['primary_school'] ?? '',
        'secondary_school' => $_POST['secondary_school'] ?? '',
        'college' => $_POST['college'] ?? '',
        'university' => $_POST['university'] ?? '',
        'department' => $_POST['department'] ?? '', // Academic dept? Or hospital dept? distinct from department_assigned usually
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
        'criminal_status' => $_POST['criminal_status'] ?? '',
        'fin_id' => $_POST['fin_id'] ?? '',
        'loan_status' => $_POST['loan_status'] ?? '',
        'leave_request' => $_POST['leave_request'] ?? '', // Maybe not needed for Add
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'department_assigned' => $_POST['department_assigned'] ?? '',
        'position' => $_POST['position'] ?? '',
        'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
        'salary' => $_POST['salary'] ?? 0,
        'employment_type' => $_POST['employment_type'] ?? 'full-time',
        'status' => $_POST['status'] ?? 'active',
        'address' => $_POST['address'] ?? '',
        'emergency_contact' => $_POST['emergency_contact'] ?? '',
        'language' => $_POST['language'] ?? '',
        'other_language' => $_POST['other_language'] ?? ''
    ];
}

switch ($action) {
    case 'add':
        $d = getPostData();
        // Generate a random employee ID if not provided (or auto-increment handled by DB ID, but we need employee_id string)
        $empIdStr = 'EMP-' . strtoupper(substr(md5(time()), 0, 6));
        $session_user = $_SESSION['user_name'] ?? 'System';
        $session_kebele = $_SESSION['kebele'] ?? 'Kebele 1';

        // Handle Files
        $scan_file = handleFileUpload($_FILES['scan_file'] ?? null, 'scan', $empIdStr);
        $criminal_file = handleFileUpload($_FILES['criminal_file'] ?? null, 'criminal', $empIdStr);
        $fin_scan = handleFileUpload($_FILES['fin_scan'] ?? null, 'fin', $empIdStr);
        $loan_file = handleFileUpload($_FILES['loan_file'] ?? null, 'loan', $empIdStr);
        $leave_document = handleFileUpload($_FILES['leave_document'] ?? null, 'leave', $empIdStr);
        
        // Handle Multiple Education Files
        $edu_docs = [];
        if (isset($_FILES['education_files'])) {
            foreach ($_FILES['education_files']['name'] as $i => $name) {
                if ($_FILES['education_files']['error'][$i] == 0) {
                   $f = [
                       'name' => $name,
                       'type' => $_FILES['education_files']['type'][$i],
                       'tmp_name' => $_FILES['education_files']['tmp_name'][$i],
                       'error' => 0,
                       'size' => $_FILES['education_files']['size'][$i]
                   ];
                   $up = handleFileUpload($f, 'edu_'.$i, $empIdStr);
                   if ($up) $edu_docs[] = $up;
                }
            }
        }
        // Encode as JSON if multiple, or string if single? Better stick to JSON for consistency if multiple allowed.
        // But DB column is TEXT (soon), so JSON string is fine.
        $education_file = !empty($edu_docs) ? json_encode($edu_docs) : '';

        // Handle Multiple Employment Contracts
        $contract_docs = [];
        if (isset($_FILES['employment_agreements'])) {
             foreach ($_FILES['employment_agreements']['name'] as $i => $name) {
                if ($_FILES['employment_agreements']['error'][$i] == 0) {
                   $f = [
                       'name' => $name,
                       'type' => $_FILES['employment_agreements']['type'][$i],
                       'tmp_name' => $_FILES['employment_agreements']['tmp_name'][$i],
                       'error' => 0,
                       'size' => $_FILES['employment_agreements']['size'][$i]
                   ];
                   $up = handleFileUpload($f, 'contract_'.$i, $empIdStr);
                   if ($up) $contract_docs[] = $up;
                }
            }
        }
        $employment_agreement = !empty($contract_docs) ? json_encode($contract_docs) : '';

        $documents = [];
        // Handle multi-documents
        if (isset($_FILES['documents'])) {
            // Basic loop for multiple files
            foreach ($_FILES['documents']['name'] as $i => $name) {
                if ($_FILES['documents']['error'][$i] == 0) {
                   $f = [
                       'name' => $name,
                       'type' => $_FILES['documents']['type'][$i],
                       'tmp_name' => $_FILES['documents']['tmp_name'][$i],
                       'error' => 0,
                       'size' => $_FILES['documents']['size'][$i]
                   ];
                   $up = handleFileUpload($f, 'doc_'.$i, $empIdStr);
                   if ($up) $documents[] = $up;
                }
            }
        }
        $documents_json = json_encode($documents);

        // INSERT Query with all fields
        // INSERT Query with all fields
        $working_woreda = $_POST['working_woreda'] ?? '';
        $working_kebele = $_POST['working_kebele'] ?? '';
        
        // Auto-assign for Kebele HR if empty (and if session has it, though session structure varies)
        // Assuming session has 'woreda' and 'kebele' for Kebele HR users
        if(empty($working_woreda) && isset($_SESSION['woreda'])) $working_woreda = $_SESSION['woreda'];
        if(empty($working_kebele) && isset($_SESSION['kebele'])) $working_kebele = $_SESSION['kebele'];

        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship, region, zone, woreda, kebele, 
            education_level, primary_school, secondary_school, college, university, department, other_department, education_file,
            bank_name, other_bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status, 
            warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, 
            scan_file, criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, employment_agreement,
            email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address, emergency_contact, 
            language, other_language, documents, working_woreda, working_kebele, created_by, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             echo json_encode(['success' => false, 'message' => $conn->error]);
             exit;
        }

        $stmt->bind_param(str_repeat("s", 60), 
            $empIdStr, $d['first_name'], $d['middle_name'], $d['last_name'], $d['gender'], $d['date_of_birth'], 
            $d['religion'], $d['citizenship'], $d['other_citizenship'], $d['region'], $d['zone'], $d['woreda'], $d['kebele'],
            $d['education_level'], $d['primary_school'], $d['secondary_school'], $d['college'], $d['university'], $d['department'], $d['other_department'], $education_file,
            $d['bank_name'], $d['other_bank_name'], $d['bank_account'], $d['job_level'], $d['other_job_level'], $d['marital_status'], $d['other_marital_status'],
            $d['warranty_status'], $d['person_name'], $d['warranty_woreda'], $d['warranty_kebele'], $d['phone'], $d['warranty_type'],
            $scan_file, $d['criminal_status'], $criminal_file, $d['fin_id'], $fin_scan, $d['loan_status'], $loan_file, $d['leave_request'], $leave_document, $employment_agreement,
            $d['email'], $d['phone_number'], $d['department_assigned'], $d['position'], $d['join_date'], $d['salary'], $d['employment_type'], $d['status'], 
            $d['address'], $d['emergency_contact'], $d['language'], $d['other_language'], $documents_json, $working_woreda, $working_kebele, $session_user
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Employee added successfully!';
        } else {
            $response['message'] = $stmt->error;
        }
        $stmt->close();
        break;

    case 'edit':
        $empId = $_POST['employee_id'] ?? '';
        if (!$empId) { $response['message'] = 'ID required'; break; }
        
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
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'religion', 'citizenship', 'other_citizenship',
            'region', 'zone', 'woreda', 'kebele', 'education_level', 'primary_school', 'secondary_school', 'college', 'university',
            'department', 'other_department', 'bank_name', 'other_bank_name', 'bank_account', 'job_level', 'other_job_level',
            'marital_status', 'other_marital_status', 'warranty_status', 'person_name', 'warranty_woreda', 'warranty_kebele', 
            'phone', 'warranty_type', 'criminal_status', 'loan_status', 'email', 'phone_number', 'department_assigned', 
            'position', 'join_date', 'salary', 'employment_type', 'status', 'address', 'emergency_contact', 'language', 'other_language'
            // files handled separately below
        ];

        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $updates[] = "$f = ?";
                $types .= "s";
                $params[] = $_POST[$f];
            }
        }
        
        // Working Location
        if(isset($_POST['working_woreda'])) { $updates[] = "working_woreda=?"; $types.="s"; $params[]=$_POST['working_woreda']; }
        if(isset($_POST['working_kebele'])) { $updates[] = "working_kebele=?"; $types.="s"; $params[]=$_POST['working_kebele']; }

        // Handle File Updates (Only if new file uploaded)
        $file_fields = [
            'scan_file' => 'scan', 
            'criminal_file' => 'criminal', 
            'fin_scan' => 'fin', 
            'loan_file' => 'loan', 
            'leave_document' => 'leave'
        ];
        
        foreach($file_fields as $post_key => $prefix) {
             if (isset($_FILES[$post_key]) && $_FILES[$post_key]['error'] == 0) {
                 $new_file = handleFileUpload($_FILES[$post_key], $prefix, $empId);
                 if($new_file) {
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
                     $f = [ 'name'=>$name, 'type'=>$_FILES['education_files']['type'][$i], 'tmp_name'=>$_FILES['education_files']['tmp_name'][$i], 'error'=>0, 'size'=>$_FILES['education_files']['size'][$i] ];
                     $up = handleFileUpload($f, 'edu_'.$i, $empId);
                     if($up) $edu_docs[] = $up;
                 }
             }
             if($has_new) {
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
                     $f = [ 'name'=>$name, 'type'=>$_FILES['employment_agreements']['type'][$i], 'tmp_name'=>$_FILES['employment_agreements']['tmp_name'][$i], 'error'=>0, 'size'=>$_FILES['employment_agreements']['size'][$i] ];
                     $up = handleFileUpload($f, 'contract_'.$i, $empId);
                     if($up) $contract_docs[] = $up;
                 }
             }
             if($has_new) {
                 $updates[] = "employment_agreement = ?";
                 $types .= "s";
                 $params[] = json_encode($contract_docs);
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
        header('Content-Disposition: attachment; filename=employee_export_'.date('Ymd').'.csv');
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
