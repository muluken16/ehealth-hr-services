<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE employees");
$columns = [];
while($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Current columns: " . implode(", ", $columns) . "\n\n";

$required_fields = [
    'photo' => 'VARCHAR(255)',
    'criminal_record_details' => 'TEXT',
    'national_id_details' => 'TEXT',
    'credit_status' => "VARCHAR(50) DEFAULT 'good'",
    'credit_details' => 'TEXT',
    'fin_scan' => 'VARCHAR(255)',
    'loan_lender' => 'VARCHAR(255)',
    'loan_type' => 'VARCHAR(100)',
    'loan_amount' => 'DECIMAL(15,2) DEFAULT 0',
    'remaining_balance' => 'DECIMAL(15,2) DEFAULT 0',
    'monthly_payment' => 'DECIMAL(15,2) DEFAULT 0',
    'loan_end_date' => 'DATE',
    'loan_purpose' => 'TEXT',
    'person_relationship' => 'VARCHAR(100)',
    'warranty_amount' => 'DECIMAL(15,2) DEFAULT 0',
    'warranty_start_date' => 'DATE',
    'warranty_end_date' => 'DATE',
    'warranty_court_status' => "VARCHAR(50) DEFAULT 'clean'",
    'criminal_type' => 'VARCHAR(255)',
    'criminal_date' => 'DATE',
    'criminal_court' => 'VARCHAR(255)',
    'criminal_sentence' => 'VARCHAR(255)',
    'criminal_status_current' => 'VARCHAR(50)'
];

echo "Checking for missing fields...\n";
foreach ($required_fields as $field => $type) {
    if (!in_array($field, $columns)) {
        echo "Missing: $field. Adding...\n";
        $sql = "ALTER TABLE employees ADD COLUMN $field $type";
        if ($conn->query($sql)) {
            echo "Successfully added $field.\n";
        } else {
            echo "Error adding $field: " . $conn->error . "\n";
        }
    } else {
        echo "Present: $field.\n";
    }
}

$conn->close();
?>
