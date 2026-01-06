<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $conn = getDBConnection();

    // Generate unique employee ID
    $employee_id = 'HF-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Map form fields to DB columns
    $field_mapping = [
        'firstName' => 'first_name',
        'middleName' => 'middle_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'phone' => 'phone_number',
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
        'university' => 'university',
        'department' => 'department',
        'department_assigned' => 'department_assigned',
        'position' => 'position',
        'jobLevel' => 'job_level',
        'joinDate' => 'join_date',
        'salary' => 'salary',
        'status' => 'status',
        'address' => 'address',
        'fin_id' => 'fin_id',
        'criminal_status' => 'criminal_status',
        'warranty_status' => 'warranty_status',
        'person_name' => 'person_name',
        'warranty_phone' => 'phone',
        'maritalStatus' => 'marital_status',
        'language' => 'language',
        'emergencyContact' => 'emergency_contact',
        'primarySchool' => 'primary_school',
        'secondarySchool' => 'secondary_school',
        'college' => 'college',
        'bankName' => 'bank_name',
        'bankAccount' => 'bank_account',
        'loan_status' => 'loan_status'
    ];

    $cols = ['employee_id', 'created_by'];
    $placeholders = ['?', '?'];
    $params = [$employee_id, $_SESSION['user_id'] ?? 1];
    $types = "ss";

    foreach ($field_mapping as $form_key => $db_col) {
        if (isset($_POST[$form_key]) && $_POST[$form_key] !== '') {
            $cols[] = $db_col;
            $placeholders[] = '?';
            $params[] = $_POST[$form_key];
            $types .= (in_array($db_col, ['salary']) ? 'd' : 's');
        }
    }

    // File Uploads
    $upload_dir = '../uploads/employees/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $files = [
        'fin_scan' => 'fin_scan',
        'criminal_file' => 'criminal_file',
        'scan_file' => 'scan_file',
        'loan_file' => 'loan_file'
    ];

    foreach ($files as $form_name => $db_col) {
        if (isset($_FILES[$form_name]) && $_FILES[$form_name]['error'] == 0) {
            $ext = pathinfo($_FILES[$form_name]['name'], PATHINFO_EXTENSION);
            $filename = $employee_id . '_' . $form_name . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES[$form_name]['tmp_name'], $upload_dir . $filename)) {
                $cols[] = $db_col;
                $placeholders[] = '?';
                $params[] = $filename;
                $types .= 's';
            }
        }
    }

    $sql = "INSERT INTO employees (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee created', 'employee_id' => $employee_id]);
    } else {
        throw new Exception($stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}