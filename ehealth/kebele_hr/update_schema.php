<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$result = $conn->query("SHOW COLUMNS FROM employees");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

$required_columns = [
    'middle_name' => 'VARCHAR(100)',
    'gender' => 'ENUM("male", "female", "other")',
    'date_of_birth' => 'DATE',
    'religion' => 'VARCHAR(50)',
    'citizenship' => 'VARCHAR(50)',
    'other_citizenship' => 'VARCHAR(100)',
    'region' => 'VARCHAR(50)',
    'zone' => 'VARCHAR(50)',
    'kebele' => 'VARCHAR(50)', // Residence kebele
    'education_level' => 'VARCHAR(50)',
    'primary_school' => 'VARCHAR(255)',
    'secondary_school' => 'VARCHAR(255)',
    'college' => 'VARCHAR(255)',
    'university' => 'VARCHAR(255)',
    'department' => 'VARCHAR(100)', // Academic dept
    'other_department' => 'TEXT',
    'bank_name' => 'VARCHAR(50)',
    'other_bank_name' => 'VARCHAR(100)',
    'bank_account' => 'VARCHAR(50)',
    'job_level' => 'VARCHAR(50)',
    'other_job_level' => 'TEXT',
    'marital_status' => 'VARCHAR(20)',
    'other_marital_status' => 'TEXT',
    'warranty_status' => 'ENUM("yes", "no")',
    'person_name' => 'VARCHAR(100)',
    'warranty_woreda' => 'VARCHAR(100)',
    'warranty_kebele' => 'VARCHAR(100)',
    'phone' => 'VARCHAR(20)',
    'warranty_type' => 'VARCHAR(50)',
    'scan_file' => 'VARCHAR(255)',
    'criminal_status' => 'ENUM("yes", "no")',
    'criminal_file' => 'VARCHAR(255)',
    'fin_id' => 'VARCHAR(50)',
    'fin_scan' => 'VARCHAR(255)',
    'loan_status' => 'ENUM("yes", "no")',
    'loan_file' => 'VARCHAR(255)',
    'education_file' => 'VARCHAR(255)',
    'leave_request' => 'ENUM("yes", "no")',
    'leave_document' => 'VARCHAR(255)',
    'employment_agreement' => 'VARCHAR(255)',
    'address' => 'TEXT',
    'emergency_contact' => 'VARCHAR(100)',
    'language' => 'VARCHAR(50)',
    'other_language' => 'VARCHAR(50)',
    'documents' => 'JSON',
    'working_woreda' => 'VARCHAR(50)',
    'working_kebele' => 'VARCHAR(50)',
    'created_by' => 'VARCHAR(100)'
];

$missing = [];
foreach ($required_columns as $col => $def) {
    if (!in_array($col, $columns)) {
        $missing[$col] = $def;
    }
}

if (!empty($missing)) {
    echo "Adding missing columns...\n";
    foreach ($missing as $col => $def) {
        $sql = "ALTER TABLE employees ADD COLUMN $col $def";
        if ($conn->query($sql) === TRUE) {
            echo "Added $col\n";
        } else {
            echo "Error adding $col: " . $conn->error . "\n";
        }
    }
} else {
    echo "All columns exist.\n";
}

$conn->close();
?>
