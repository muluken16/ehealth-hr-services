<?php
require_once 'db.php';
$conn = getDBConnection();

$empIdStr = 'TEST-' . time();
$education_file = 'test.pdf';
$employment_agreement = 'test2.pdf';
$working_woreda = 'TestWoreda';
$working_kebele = 'TestKebele';
$session_user = 'TestUser';
$documents_json = '[]';
$photo = '';
$scan_file = '';
$criminal_file = '';
$fin_scan = '';
$loan_file = '';
$leave_document = '';

$sql = "INSERT INTO employees (
    employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship, region, zone, woreda, kebele, 
    education_level, primary_school, secondary_school, college, university, department, other_department, education_file,
    bank_name, other_bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status, 
    warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, 
    scan_file, criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, employment_agreement,
    email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address, emergency_contact, 
    language, other_language, documents, working_woreda, working_kebele, created_by, created_at,
    photo, criminal_record_details, national_id_details, credit_status, credit_details
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$s65 = str_repeat("s", 65);
$vals = [
    $empIdStr,
    'Test',
    'Test',
    'Test',
    'male',
    '1990-01-01',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    $education_file,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    $scan_file,
    'no',
    $criminal_file,
    '',
    $fin_scan,
    'no',
    $loan_file,
    'no',
    $leave_document,
    $employment_agreement,
    '',
    '',
    '',
    '',
    date('Y-m-d'),
    0,
    'full-time',
    'active',
    '',
    '',
    '',
    '',
    $documents_json,
    $working_woreda,
    $working_kebele,
    $session_user,
    $photo,
    '',
    '',
    'good',
    ''
];

$stmt->bind_param($s65, ...$vals);

if ($stmt->execute()) {
    echo "Success!\n";
} else {
    echo "Execute failed: " . $stmt->error . "\n";
}
?>